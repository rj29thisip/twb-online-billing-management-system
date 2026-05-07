<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use Illuminate\Http\JsonResponse;

class NotificationController extends Controller
{
    /**
     * Return notifications for CUSTOMER portal only.
     * Admin/officer do not use this — their topbar has no bell.
     * Only shows: overdue invoices + unpaid issued invoices.
     * No announcements/news (removed per request).
     */
    public function index(): JsonResponse
    {
        $user          = auth()->user();
        $notifications = [];

        if ($user->isStaff()) {
            // Admin/officer have no notification bell — return empty
            return response()->json(['count' => 0, 'notifications' => []]);
        }

        $customer = $user->customer;

        if (! $customer) {
            return response()->json(['count' => 0, 'notifications' => []]);
        }

        // ── Overdue invoices ─────────────────────────────────────────────
        $overdue = Invoice::where('customer_id', $customer->id)
            ->where('status', 'overdue')
            ->orderByDesc('due_date')
            ->limit(5)
            ->get();

        foreach ($overdue as $inv) {
            $notifications[] = [
                'type'    => 'overdue',
                'icon'    => 'warning_amber',
                'color'   => 'orange',
                'title'   => 'Overdue Invoice',
                'message' => $inv->invoice_number . ' — T$ ' . number_format($inv->balance_due, 2) . ' past due',
                'url'     => route('customer.invoices.show', $inv->id),
                'time'    => $inv->due_date?->diffForHumans() ?? '',
            ];
        }

        // ── Unpaid issued invoices ────────────────────────────────────────
        $unpaid = Invoice::where('customer_id', $customer->id)
            ->where('status', 'issued')
            ->orderByDesc('created_at')
            ->limit(3)
            ->get();

        foreach ($unpaid as $inv) {
            $notifications[] = [
                'type'    => 'unpaid',
                'icon'    => 'receipt_long',
                'color'   => 'blue',
                'title'   => 'Invoice Due',
                'message' => $inv->invoice_number . ' — T$ ' . number_format($inv->total_amount, 2)
                           . ' due ' . ($inv->due_date?->format('d M Y') ?? 'soon'),
                'url'     => route('customer.invoices.show', $inv->id),
                'time'    => $inv->created_at->diffForHumans(),
            ];
        }

        // ── Partially paid invoices ───────────────────────────────────────
        $partial = Invoice::where('customer_id', $customer->id)
            ->where('status', 'partially_paid')
            ->orderByDesc('created_at')
            ->limit(3)
            ->get();

        foreach ($partial as $inv) {
            $notifications[] = [
                'type'    => 'partial',
                'icon'    => 'payments',
                'color'   => 'purple',
                'title'   => 'Partial Payment',
                'message' => $inv->invoice_number . ' — T$ ' . number_format($inv->balance_due, 2) . ' remaining',
                'url'     => route('customer.invoices.show', $inv->id),
                'time'    => $inv->created_at->diffForHumans(),
            ];
        }

        return response()->json([
            'count'         => count($notifications),
            'notifications' => $notifications,
        ]);
    }
}
