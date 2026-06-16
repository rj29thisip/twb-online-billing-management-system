{{-- resources/views/customer/invoices/index.blade.php --}}
@extends('layouts.app')
@section('title', 'My Invoices')
@section('breadcrumb', 'Customer / Invoices')
@section('page-title', 'MY INVOICES')

@section('content')

<div class="card">
  <div class="table-card-header">
    <div class="card-header-float" style="background:var(--gradient-dark);">
      <div><h3>Invoices</h3><p>Your billing documents</p></div>
      <span class="material-icons" style="color:var(--accent-blue)">receipt</span>
    </div>
  </div>
  <div class="table-wrapper">
    <table>
      <thead>
        <tr><th>Invoice #</th><th>Period</th><th>Usage</th><th>Total</th><th>Status</th><th>Due Date</th><th></th></tr>
      </thead>
      <tbody>
        @forelse($invoices as $inv)
          <tr>
            <td class="td-primary" style="font-family:monospace;">{{ $inv->invoice_number }}</td>
            <td>{{ $inv->billing_period_start->format('M Y') }}</td>
            <td>{{ number_format($inv->total_usage, 2) }} m³</td>
            <td>T$ {{ number_format($inv->total_amount, 2) }}</td>
            <td>
              @php
                $bc = match($inv->status) {
                  'paid' => 'badge-paid', 'issued' => 'badge-issued', 'overdue' => 'badge-overdue',
                  'partially_paid' => 'badge-partially', default => 'badge-draft'
                };
              @endphp
              <span class="badge-status {{ $bc }}">{{ ucfirst(str_replace('_',' ',$inv->status)) }}</span>
            </td>
            <td style="{{ $inv->isOverdue() ? 'color:var(--accent-orange)' : '' }}">
              {{ $inv->due_date?->format('d M Y') ?? '—' }}
            </td>
            <td>
              <div style="display:flex;gap:4px;">
                <a href="{{ route('customer.invoices.show', $inv) }}" class="action-btn" title="View">
                  <span class="material-icons">visibility</span>
                </a>
                <a href="{{ route('customer.invoices.pdf', $inv) }}" class="action-btn" title="PDF" target="_blank">
                  <span class="material-icons">picture_as_pdf</span>
                </a>
              </div>
            </td>
          </tr>
        @empty
          <tr><td colspan="7"><div class="empty-state"><span class="material-icons">receipt</span><h3>No invoices yet</h3></div></td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
  <div class="pagination">
    <div class="pagination-info">Showing {{ $invoices->firstItem() }}–{{ $invoices->lastItem() }} of {{ $invoices->total() }}</div>
    <div class="pagination-btns">
      @if(!$invoices->onFirstPage())
        <a href="{{ $invoices->previousPageUrl() }}" class="pg-btn"><span class="material-icons">chevron_left</span></a>
      @endif
      @if($invoices->hasMorePages())
        <a href="{{ $invoices->nextPageUrl() }}" class="pg-btn"><span class="material-icons">chevron_right</span></a>
      @endif
    </div>
  </div>
</div>

@endsection
