@extends('layouts.app')
@section('title', 'Payments')
@section('breadcrumb', 'Admin / Billing / Payments')
@section('page-title', 'PAYMENT RECORDS')

@section('content')


<div class="section-header">
  <div>
    <h2>Payment Collections</h2>
    <p>View payments data as recorded in the invoice feature</p>
  </div>
</div>

<div class="card-tight-margin">
  <form method="GET" class="filter-bar">
    <input type="text" name="search" class="form-control" placeholder="Receipt # or Customer Name..." value="{{ request('search') }}">
    <button type="submit" class="btn btn-outline btn-sm"><span class="material-icons">filter_list</span> Filter</button>
    @if(request('search'))
      <a href="{{ route('admin.payments.index') }}" class="btn btn-outline btn-sm"><span class="material-icons">clear</span> Clear</a>
    @endif
  </form>

  <div class="table-wrapper">
    <table>
      <thead>
        <tr><th>Receipt #</th><th>Customer</th><th>Invoice</th><th>Method</th><th>Reference</th><th>Date</th><th>Amount</th></tr>
      </thead>
      <tbody>
        @forelse($payments as $pay)
          <tr>
            <td class="td-primary" style="font-family:monospace;">{{ $pay->receipt_number }}</td>
            <td>{{ $pay->customer->name }}</td>
            <td style="font-family:monospace;font-size:14px;">{{ $pay->invoice->invoice_number }}</td>
            <td>{{ ucfirst(str_replace('_',' ',$pay->payment_method)) }}</td>
            <td style="color:var(--text-muted);font-size:14px;">{{ $pay->reference_code ?? '—' }}</td>
            <td>{{ $pay->payment_date->format('d M Y') }}</td>
            <td class="td-primary" style="font-weight:600;">T$ {{ number_format($pay->amount, 2) }}</td>
          </tr>
        @empty
          <tr><td colspan="7"><div class="empty-state"><span class="material-icons">payments</span><h3>No payments found</h3></div></td></tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <div class="pagination">
    <div class="pagination-info">Showing {{ $payments->firstItem() }}–{{ $payments->lastItem() }} of {{ $payments->total() }}</div>
    <div class="pagination-btns">
      @if(!$payments->onFirstPage())
        <a href="{{ $payments->previousPageUrl() }}" class="pg-btn"><span class="material-icons">chevron_left</span></a>
      @endif
      @if($payments->hasMorePages())
        <a href="{{ $payments->nextPageUrl() }}" class="pg-btn"><span class="material-icons">chevron_right</span></a>
      @endif
    </div>
  </div>
</div>

@endsection
