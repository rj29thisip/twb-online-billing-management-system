@extends('layouts.app')
@section('title', 'Payment Detail')
@section('breadcrumb', 'Admin / Payments / Detail')
@section('page-title', 'PAYMENT DETAILS')

@section('content')

<div style="max-width:600px;margin:0 auto;">
  <div style="margin-bottom:20px;display:flex;gap:10px;">
    <a href="{{ route('admin.payments.index') }}" class="btn btn-outline btn-sm">
      <span class="material-icons">arrow_back</span> Back
    </a>
    <a href="{{ route('admin.payments.receipt', $payment) }}" class="btn btn-outline btn-sm" target="_blank">
      <span class="material-icons">receipt</span> Print Receipt
    </a>
  </div>

  <div class="card">
    <div class="table-card-header">
      <div class="card-header-float" style="background:var(--gradient-green);">
        <div>
          <h3>{{ $payment->receipt_number }}</h3>
          <p>{{ $payment->payment_date->format('d M Y') }}</p>
        </div>
        <span class="material-icons" style="font-size:36px;color:rgba(255,255,255,0.8);">check_circle</span>
      </div>
    </div>
    <div class="card-body" style="padding-top:0;">
      <div class="detail-row"><span class="detail-label">Customer</span><span class="detail-value">{{ $payment->customer->name }}</span></div>
      <div class="detail-row"><span class="detail-label">Account #</span><span class="detail-value" style="font-family:monospace;">{{ $payment->customer->account_number }}</span></div>
      <div class="detail-row"><span class="detail-label">Invoice</span>
        <span class="detail-value">
          <a href="{{ route('admin.invoices.show', $payment->invoice) }}" style="color:var(--accent-blue);">
            {{ $payment->invoice->invoice_number }}
          </a>
        </span>
      </div>
      <div class="detail-row"><span class="detail-label">Amount Paid</span><span class="detail-value" style="font-size:20px;font-weight:700;color:var(--accent-green);">T$ {{ number_format($payment->amount, 2) }}</span></div>
      <div class="detail-row"><span class="detail-label">Payment Method</span><span class="detail-value">{{ ucfirst(str_replace('_',' ',$payment->payment_method)) }}</span></div>
      <div class="detail-row"><span class="detail-label">Reference Code</span><span class="detail-value">{{ $payment->reference_code ?? '—' }}</span></div>
      <div class="detail-row"><span class="detail-label">Recorded By</span><span class="detail-value">{{ $payment->recorder?->name ?? '—' }}</span></div>
      @if($payment->notes)
        <div class="detail-row"><span class="detail-label">Notes</span><span class="detail-value">{{ $payment->notes }}</span></div>
      @endif
    </div>
  </div>
</div>

@endsection
