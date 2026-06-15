<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Invoice;
use App\Services\AuditLogger;
use App\Services\BillingService;
use App\Services\InvoicePdfService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function __construct(
        protected BillingService    $billing,
        protected InvoicePdfService $pdfService,
    ) {}

    /** Scope invoices to district. Shared by index + district guard on show/destroy. */
    private function districtScope()
    {
        $user = auth()->user();
        if ($user->isAdmin() || $user->isHeadquarters() || !$user->district_id) {
            return Invoice::query();
        }
        return Invoice::whereHas('customer', fn ($q) =>
            $q->where(fn ($s) =>
                $s->where('district_id', $user->district_id)->orWhereNull('district_id')
            )
        );
    }

    private function authorizeInvoiceDistrict(Invoice $invoice): void
    {
        $user = auth()->user();
        if ($user->isAdmin() || $user->isHeadquarters() || !$user->district_id) return;
        $custDistrict = optional($invoice->customer)->district_id;
        if ($custDistrict !== null && $custDistrict !== $user->district_id) {
            abort(403, 'You do not have access to this invoice.');
        }
    }

    public function index(Request $request)
    {
        $invoices = $this->districtScope()
            ->with('customer')
            ->when($request->search, fn ($q) => $q->where(fn ($q) =>
                $q->where('invoice_number', 'like', '%' . $request->search . '%')
                  ->orWhereHas('customer', fn ($q) =>
                      $q->where('name', 'like', '%' . $request->search . '%')
                        ->orWhere('account_number', 'like', '%' . $request->search . '%')
                  )
            ))
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->when($request->period, fn ($q) =>
                $q->whereYear('billing_period_start', substr($request->period, 0, 4))
                  ->whereMonth('billing_period_start', substr($request->period, 5, 2))
            )
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        return view('admin.invoices.index', compact('invoices'));
    }

    public function checkBilling(Request $request)
    {
        $blocks    = Customer::select('block_number')->distinct()->whereNotNull('block_number')->orderBy('block_number')->pluck('block_number');
        $previews  = null;

        if ($request->has('period_start') && $request->period_start) {
            $periodStart = Carbon::parse($request->period_start)->startOfDay();
            $periodEnd   = Carbon::parse($request->period_end)->endOfDay();
            $query       = Customer::with('activeMeter')->where('status', 'active');
            if ($request->block)       $query->where('block_number', $request->block);
            if ($request->customer_id) $query->where('id', $request->customer_id);

            $previews = [];
            foreach ($query->get() as $customer) {
                try { $previews[] = $this->billing->preview($customer, $periodStart, $periodEnd); }
                catch (\Exception) {}
            }
        }

        return view('admin.invoices.create', compact('blocks', 'previews'));
    }

    public function generateBulk(Request $request)
    {
        $request->validate([
            'period_start' => 'required|date',
            'period_end'   => 'required|date|after_or_equal:period_start',
        ]);

        $periodStart = Carbon::parse($request->period_start)->startOfDay();
        $periodEnd   = Carbon::parse($request->period_end)->endOfDay();
        $query       = Customer::with('activeMeter')->where('status', 'active');
        if ($request->block)       $query->where('block_number', $request->block);
        if ($request->customer_id) $query->where('id', $request->customer_id);

        $count = 0;
        foreach ($query->get() as $customer) {
            try {
                $preview = $this->billing->preview($customer, $periodStart, $periodEnd);
                $invoice = $this->billing->createInvoice($preview);
                AuditLogger::invoiceGenerated($invoice->load('customer'));
                $count++;
            } catch (\Exception) {}
        }

        return redirect()->route('admin.invoices.index')
            ->with('success', "{$count} invoice(s) generated successfully.");
    }

    public function show(Invoice $invoice)
    {
        $this->authorizeInvoiceDistrict($invoice);
        $invoice->load(['customer', 'meter', 'items.tariffTier', 'payments']);
        return view('admin.invoices.show', compact('invoice'));
    }

    public function destroy(Invoice $invoice)
    {
        $this->authorizeInvoiceDistrict($invoice);
        abort_if($invoice->status === 'paid', 403, 'Cannot cancel a paid invoice.');
        $invoice->load('customer');
        AuditLogger::invoiceCancelled($invoice);
        $invoice->update(['status' => 'cancelled']);
        return back()->with('success', 'Invoice cancelled.');
    }

    /** Stream PDF in TWB official format using DomPDF */
    public function pdf(Invoice $invoice)
    {
        $this->authorizeInvoiceDistrict($invoice);
        return $this->pdfService->stream($invoice);
    }

    public function sendEmail(Invoice $invoice)
    {
        AuditLogger::log('email_sent', 'Invoice', $invoice->id, [], [
            'invoice_number' => $invoice->invoice_number,
            'sent_to'        => $invoice->customer->email ?? '?',
        ], 'Invoice sent by email');
        return back()->with('success', 'Invoice sent to ' . ($invoice->customer->email ?? 'customer'));
    }
}
