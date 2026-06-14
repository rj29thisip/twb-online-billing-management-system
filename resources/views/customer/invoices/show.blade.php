@extends('layouts.app')
@section('title', 'Invoice ' . $invoice->invoice_number)
@section('breadcrumb', 'Customer / Invoices / Detail')
@section('page-title', 'INVOICE DETAILS')

@section('content')

<div style="margin-bottom:20px;display:flex;align-items:center;justify-content:space-between;">
  <a href="{{ route('customer.invoices.index') }}" class="btn btn-outline btn-sm">
    <span class="material-icons">arrow_back</span> Back
  </a>
  <a href="{{ route('customer.invoices.pdf', $invoice) }}" class="btn btn-outline btn-sm" target="_blank">
    <span class="material-icons">picture_as_pdf</span> Download PDF
  </a>
</div>

<div class="detail-grid">
  <div style="display:flex;flex-direction:column;gap:24px;">

    <div class="card-tight-margin">
      <div class="table-card-header">
        <div class="card-header-float" style="background:var(--gradient-black);">
          <div>
            <h3>{{ $invoice->invoice_number }}</h3>
            <p>{{ $invoice->billing_period_start->format('d M Y') }} – {{ $invoice->billing_period_end->format('d M Y') }}</p>
          </div>
          @php
            $bc = match($invoice->status) {
              'paid' => 'badge-paid', 'issued' => 'badge-issued', 'overdue' => 'badge-overdue',
              'partially_paid' => 'badge-partially', default => 'badge-draft'
            };
          @endphp
          <span class="badge-status {{ $bc }}" style="font-size:14px;padding:6px 14px;">
            {{ ucfirst(str_replace('_',' ',$invoice->status)) }}
          </span>
        </div>
      </div>
      <div class="card-body" style="padding-top:0;">
        <div class="detail-row"><span class="detail-label">Account</span><span class="detail-value" style="font-family:monospace;">{{ $invoice->customer->account_number }}</span></div>
        <div class="detail-row"><span class="detail-label">Meter</span><span class="detail-value" style="font-family:monospace;color:var(--accent-teal);">{{ $invoice->meter->meter_id }}</span></div>
        <div class="detail-row"><span class="detail-label">Consumption</span><span class="detail-value">{{ number_format($invoice->total_usage, 3) }} m³</span></div>
        <div class="detail-row"><span class="detail-label">Due Date</span>
          <span class="detail-value" style="{{ $invoice->isOverdue() ? 'color:var(--accent-orange)' : '' }}">
            {{ $invoice->due_date?->format('d M Y') ?? '—' }}
          </span>
        </div>
      </div>
    </div>

    <div class="card">
      <div class="table-card-header">
        <div class="card-header-float" style="background:var(--gradient-dark);">
          <div><h3>Charge Breakdown</h3></div>
          <span class="material-icons" style="color:var(--accent-blue)">layers</span>
        </div>
      </div>
      <div class="table-wrapper">
        <table>
          <thead><tr><th>Description</th><th>Qty (m³)</th><th>Rate</th><th>Amount</th></tr></thead>
          <tbody>
            @foreach($invoice->items as $item)
              <tr>
                <td class="td-primary">{{ $item->description }}</td>
                <td>{{ number_format($item->quantity, 3) }}</td>
                <td>T$ {{ number_format($item->unit_rate, 4) }}</td>
                <td>T$ {{ number_format($item->line_total, 2) }}</td>
              </tr>
            @endforeach
          </tbody>
          <tfoot>
            <tr><td colspan="3" style="padding:10px 14px;color:var(--text-secondary);">Subtotal</td><td style="padding:10px 14px;font-weight:600;">T$ {{ number_format($invoice->subtotal, 2) }}</td></tr>
            <tr><td colspan="3" style="padding:6px 14px;color:var(--text-muted);">Tax</td><td style="padding:6px 14px;color:var(--text-muted);">T$ {{ number_format($invoice->tax_amount, 2) }}</td></tr>
            <tr style="background:rgba(26,115,232,0.08);">
              <td colspan="3" style="padding:12px 14px;font-weight:700;font-size:15px;">Total</td>
              <td style="padding:12px 14px;font-weight:700;font-size:15px;color:var(--accent-blue);">T$ {{ number_format($invoice->total_amount, 2) }}</td>
            </tr>
            @if($invoice->amount_paid > 0)
              <tr><td colspan="3" style="padding:8px 14px;color:var(--accent-green);">Paid</td><td style="padding:8px 14px;color:var(--accent-green);">T$ {{ number_format($invoice->amount_paid, 2) }}</td></tr>
              <tr><td colspan="3" style="padding:8px 14px;font-weight:600;">Balance Due</td><td style="padding:8px 14px;font-weight:700;color:var(--accent-orange);">T$ {{ number_format($invoice->balance_due, 2) }}</td></tr>
            @endif
          </tfoot>
        </table>
      </div>
    </div>

  </div>

  <div>
    <div class="card-tight-margin">
      <div class="table-card-header">
        <div class="card-header-float" style="background:var(--gradient-black);">
          <div><h3>Payment History</h3></div>
          <span class="material-icons" style="color:var(--accent-blue)">receipt</span>
        </div>
      </div>
      <div class="table-wrapper">
        <table>
          <thead><tr><th>Date</th><th>Method</th><th>Amount</th></tr></thead>
          <tbody>
            @forelse($invoice->payments as $pay)
              <tr>
                <td>{{ $pay->payment_date->format('d M Y') }}</td>
                <td>{{ ucfirst(str_replace('_',' ',$pay->payment_method)) }}</td>
                <td class="td-primary">T$ {{ number_format($pay->amount, 2) }}</td>
              </tr>
            @empty
              <tr><td colspan="3" style="text-align:center;color:var(--text-muted);padding:24px;">No payments recorded</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
      @if($invoice->balance_due > 0)
        <div class="card-body">
          <div style="background:rgba(255,152,0,0.08);border:1px solid rgba(255,152,0,0.2);border-radius:8px;padding:14px;font-size:13px;color:var(--accent-orange);">
            <span class="material-icons" style="font-size:16px;vertical-align:middle;margin-right:6px;">info</span>
            Please visit our office or bank to pay your outstanding balance of
            <strong>T$ {{ number_format($invoice->balance_due, 2) }}</strong>.
          </div>
        </div>
      @endif
    </div>
  </div>
</div>

@endsection
