@extends('layouts.app')
@section('title', 'Billing History')
@section('breadcrumb', 'Customer / History')
@section('page-title', 'MY BILLING HISTORY')

@section('content')

{{-- YEAR FILTER BAR --}}
<div style="display:flex;align-items:center;gap:12px;margin-bottom:24px;flex-wrap:wrap;">
  <div style="font-size:14px;color:var(--text-secondary);">Filter by year:</div>
  <div style="display:flex;gap:8px;flex-wrap:wrap;">
    @foreach($availableYears as $y)
      <a href="{{ route('customer.history') }}?year={{ $y }}"
         class="pg-btn {{ $selectedYear == $y ? 'active' : '' }}"
         style="width:auto;padding:0 16px;font-size:13px;">
        {{ $y }}
      </a>
    @endforeach
  </div>
  <div style="margin-left:auto;font-size:14px;color:var(--text-muted);">
    {{ $invoices->total() }} record(s) for {{ $selectedYear }}
  </div>
</div>

<div class="card-tight-margin">
  <div class="table-card-header">
    <div class="card-header-float" style="background:var(--gradient-dark);">
      <div><h3>Bills</h3><p>Review past and current billing records</p></div>
      <span class="material-icons" style="color:var(--accent-blue)">picture_as_pdf</span>
    </div>
  </div>  
  <div class="table-wrapper">
    <table>
      <thead>
        <tr>
          <th>Period</th>
          <th>Invoice #</th>
          <th>Usage (m³)</th>
          <th>Total</th>
          <th>Paid</th>
          <th>Balance</th>
          <th>Status</th>
          <th>Due Date</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        @forelse($invoices as $inv)
          <tr>
            <td class="td-primary">{{ $inv->billing_period_start->format('F Y') }}</td>
            <td style="font-family:monospace;font-size:14px;color:var(--text-muted);">{{ $inv->invoice_number }}</td>
            <td>{{ number_format($inv->total_usage, 2) }}</td>
            <td>T$ {{ number_format($inv->total_amount, 2) }}</td>
            <td style="color:var(--accent-green);">T$ {{ number_format($inv->amount_paid, 2) }}</td>
            <td style="color:{{ $inv->balance_due > 0 ? 'var(--accent-orange)' : 'var(--accent-green)' }};font-weight:{{ $inv->balance_due > 0 ? '600' : '400' }}">
              T$ {{ number_format($inv->balance_due, 2) }}
            </td>
            <td>
              @php
                $bc = match($inv->status) {
                  'paid'           => 'badge-paid',
                  'issued'         => 'badge-issued',
                  'overdue'        => 'badge-overdue',
                  'partially_paid' => 'badge-partially',
                  'cancelled'      => 'badge-cancelled',
                  default          => 'badge-draft',
                };
              @endphp
              <span class="badge-status {{ $bc }}">{{ ucfirst(str_replace('_', ' ', $inv->status)) }}</span>
            </td>
            <td style="{{ $inv->isOverdue() ? 'color:var(--accent-orange)' : 'color:var(--text-muted)' }};font-size:14px;">
              {{ $inv->due_date?->format('d M Y') ?? '—' }}
            </td>
            <td>
              <a href="{{ route('customer.invoices.show', $inv) }}" class="action-btn" title="View Invoice">
                <span class="material-icons">open_in_new</span>
              </a>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="9">
              <div class="empty-state">
                <span class="material-icons">history</span>
                <h3>No billing records for {{ $selectedYear }}</h3>
              </div>
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  @if($invoices->hasPages())
    <div class="pagination">
      <div class="pagination-info">Showing {{ $invoices->firstItem() }}–{{ $invoices->lastItem() }} of {{ $invoices->total() }}</div>
      <div class="pagination-btns">
        @if(!$invoices->onFirstPage())
          <a href="{{ $invoices->previousPageUrl() }}&year={{ $selectedYear }}" class="pg-btn">
            <span class="material-icons">chevron_left</span>
          </a>
        @endif
        @if($invoices->hasMorePages())
          <a href="{{ $invoices->nextPageUrl() }}&year={{ $selectedYear }}" class="pg-btn">
            <span class="material-icons">chevron_right</span>
          </a>
        @endif
      </div>
    </div>
  @endif
</div>

@endsection
