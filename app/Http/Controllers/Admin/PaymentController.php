<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Payment;
use App\Services\AuditLogger;
use App\Services\BillingService;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function __construct(protected BillingService $billing) {}


    private function districtScope()
    {
        $user = auth()->user();
        if ($user->isAdmin() || $user->isHeadquarters() || !$user->district_id) {
            return Payment::query();
        }
        return Payment::whereHas('customer', fn ($q) =>
            $q->where(fn ($s) =>
                $s->where('district_id', $user->district_id)->orWhereNull('district_id')
            )
        );
    }

    private function authorizePaymentDistrict(Payment $payment): void
    {
        $user = auth()->user();
        if ($user->isAdmin() || $user->isHeadquarters() || !$user->district_id) return;
        $custDistrict = optional($payment->customer)->district_id;
        if ($custDistrict !== null && $custDistrict !== $user->district_id) {
            abort(403, 'You do not have access to this payment.');
        }
    }

    public function index(Request $request)
    {
        $payments = $this->districtScope()->with(['customer', 'invoice'])
            ->when($request->search, fn ($q) => $q->where(fn ($q) =>
                $q->where('receipt_number', 'like', '%' . $request->search . '%')
                  ->orWhereHas('customer', fn ($q) =>
                      $q->where('name', 'like', '%' . $request->search . '%')
                  )
            ))
            ->orderByDesc('payment_date')
            ->paginate(20)
            ->withQueryString();

        return view('admin.payments.index', compact('payments'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'invoice_id'     => 'required|exists:invoices,id',
            'amount'         => 'required|numeric|min:0.01',
            'payment_method' => 'required|in:cash,bank_transfer,online,mobile_money',
            'reference_code' => 'nullable|string|max:100',
            'payment_date'   => 'required|date',
            'notes'          => 'nullable|string',
        ]);

        $invoice = Invoice::findOrFail($validated['invoice_id']);

        abort_if(
            ! in_array($invoice->status, ['issued', 'partially_paid', 'overdue']),
            422,
            'This invoice cannot receive payments.'
        );

        $payment = $this->billing->recordPayment($invoice, $validated);

        // ── Audit ──────────────────────────────────────────────────
        AuditLogger::paymentRecorded($payment->load(['invoice.customer', 'customer']));

        return redirect()->route('admin.invoices.show', $invoice)
            ->with('success', "Payment recorded. Receipt: {$payment->receipt_number}");
    }

    public function show(Payment $payment)
    {
        $this->authorizePaymentDistrict($payment);
        $payment->load(['invoice', 'customer', 'recorder']);
        return view('admin.payments.show', compact('payment'));
    }

    public function receipt(Payment $payment)
    {
        $this->authorizePaymentDistrict($payment);
        $payment->load(['invoice.customer', 'recorder']);
        return view('admin.payments.receipt', compact('payment'));
    }
}
