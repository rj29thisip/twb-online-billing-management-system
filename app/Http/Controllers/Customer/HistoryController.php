<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use Illuminate\Http\Request;

class HistoryController extends Controller
{
    public function index(Request $request)
    {
        $customer = auth()->user()->customer;

        // Get all years that have invoices for this customer
        $availableYears = Invoice::where('customer_id', $customer->id)
            ->selectRaw('YEAR(billing_period_start) as year')
            ->distinct()
            ->orderByDesc('year')
            ->pluck('year')
            ->toArray();

        // Default to current year if no year selected or no records exist
        $currentYear  = now()->year;
        $selectedYear = (int) $request->get('year', $currentYear);

        // If selected year not in available years but there are records, use the most recent
        if (!empty($availableYears) && !in_array($selectedYear, $availableYears)) {
            $selectedYear = $availableYears[0];
        }

        // If no records at all, still show current year (empty state)
        if (empty($availableYears)) {
            $availableYears = [$currentYear];
        }

        $invoices = Invoice::where('customer_id', $customer->id)
            ->whereYear('billing_period_start', $selectedYear)
            ->orderByDesc('billing_period_start')
            ->paginate(24);

        return view('customer.history', compact('invoices', 'selectedYear', 'availableYears'));
    }
}
