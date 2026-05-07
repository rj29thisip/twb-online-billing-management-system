<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Services\InvoicePdfService;

class InvoiceController extends Controller
{
    public function __construct(protected InvoicePdfService $pdfService) {}

    public function index()
    {
        $customer = auth()->user()->customer;
        $invoices = Invoice::where('customer_id', $customer->id)
            ->with('meter')
            ->orderByDesc('billing_period_start')
            ->paginate(12);
        return view('customer.invoices.index', compact('customer', 'invoices'));
    }

    public function show(Invoice $invoice)
    {
        abort_if($invoice->customer_id !== auth()->user()->customer_id, 403);
        $invoice->load(['customer', 'meter', 'items.tariffTier', 'payments']);
        return view('customer.invoices.show', compact('invoice'));
    }

    public function pdf(Invoice $invoice)
    {
        abort_if($invoice->customer_id !== auth()->user()->customer_id, 403);
        return $this->pdfService->stream($invoice);
    }
}
