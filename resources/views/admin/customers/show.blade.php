@extends('layouts.app')
@section('title', 'Customer Detail')
@section('breadcrumb', 'Admin / Customers / Detail')
@section('page-title', 'Customer Detail')

@section('content')

<div style="margin-bottom:20px;display:flex;align-items:center;justify-content:space-between;">
  <a href="{{ route('admin.customers.index') }}" class="btn btn-outline btn-sm">
    <span class="material-icons">arrow_back</span> Back
  </a>
  <a href="{{ route('admin.customers.edit', $customer) }}" class="btn btn-primary btn-sm">
    <span class="material-icons">edit</span> Edit Customer
  </a>
</div>

<div class="detail-grid">

  {{-- LEFT: Customer Info --}}
  <div style="display:flex;flex-direction:column;gap:24px;">

    <div class="card">
      <div class="table-card-header">
        <div class="card-header-float" style="background:var(--gradient-blue);">
          <div>
            <h3>{{ $customer->name }}</h3>
            <p>Account: {{ $customer->account_number }}</p>
          </div>
          <div class="avatar" style="width:48px;height:48px;font-size:18px;">
            {{ substr($customer->name, 0, 2) }}
          </div>
        </div>
      </div>
      <div class="card-body" style="padding-top:0;">
        <div class="detail-row">
          <span class="detail-label">Status</span>
          <span class="detail-value">
            <span class="badge-status badge-{{ $customer->status }}">{{ ucfirst($customer->status) }}</span>
          </span>
        </div>
        <div class="detail-row">
          <span class="detail-label">Block</span>
          <span class="detail-value">{{ $customer->block_number ?? '—' }}</span>
        </div>
        <div class="detail-row">
          <span class="detail-label">Phone</span>
          <span class="detail-value">{{ $customer->phone ?? '—' }}</span>
        </div>
        <div class="detail-row">
          <span class="detail-label">Email</span>
          <span class="detail-value">{{ $customer->email ?? '—' }}</span>
        </div>
        <div class="detail-row">
          <span class="detail-label">Address</span>
          <span class="detail-value">{{ $customer->address ?? '—' }}</span>
        </div>
        <div class="detail-row">
          <span class="detail-label">Outstanding Balance</span>
          <span class="detail-value" style="color:var(--accent-orange);font-weight:600;font-size:16px;">
            T$ {{ number_format($customer->outstandingBalance(), 2) }}
          </span>
        </div>
      </div>
    </div>

    {{-- Meters --}}
    <div class="card">
      <div class="table-card-header">
        <div class="card-header-float" style="background:var(--gradient-dark);">
          <div><h3>Meters</h3><p>Registered meter devices</p></div>
          <span class="material-icons">speed</span>
        </div>
      </div>
      <div class="table-wrapper">
        <table>
          <thead>
            <tr><th>Meter ID</th><th>Endpoint ID</th><th>Type</th><th>Status</th><th>Installed</th></tr>
          </thead>
          <tbody>
            @forelse($customer->meters as $meter)
              <tr>
                <td class="td-primary" style="font-family:monospace;">{{ $meter->meter_id }}</td>
                <td style="font-family:monospace;font-size:11px;color:var(--accent-teal);">{{ $meter->endpoint_id }}</td>
                <td>{{ ucfirst($meter->meter_type) }}</td>
                <td><span class="badge-status badge-{{ $meter->status }}">{{ ucfirst($meter->status) }}</span></td>
                <td>{{ $meter->installation_date?->format('d M Y') ?? '—' }}</td>
              </tr>
            @empty
              <tr><td colspan="5" style="text-align:center;color:var(--text-muted);padding:20px;">No meters assigned</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>

  </div>

  {{-- RIGHT: Invoices & Payments --}}
  <div style="display:flex;flex-direction:column;gap:24px;">

    <div class="card">
      <div class="table-card-header">
        <div class="card-header-float" style="background:var(--gradient-orange);">
          <div><h3>Recent Invoices</h3><p>Last 12 billing periods</p></div>
          <span class="material-icons">receipt_long</span>
        </div>
      </div>
      <div class="table-wrapper">
        <table>
          <thead>
            <tr><th>Invoice #</th><th>Period</th><th>Total</th><th>Status</th><th></th></tr>
          </thead>
          <tbody>
            @forelse($customer->invoices as $inv)
              <tr>
                <td style="font-family:monospace;font-size:11px;">{{ $inv->invoice_number }}</td>
                <td>{{ $inv->billing_period_start->format('M Y') }}</td>
                <td>T$ {{ number_format($inv->total_amount, 2) }}</td>
                <td>
                  <span class="badge-status badge-{{ str_replace('_','-',$inv->status) }}">
                    {{ ucfirst(str_replace('_',' ',$inv->status)) }}
                  </span>
                </td>
                <td>
                  <a href="#" class="action-btn">
                    <span class="material-icons">open_in_new</span>
                  </a>
                </td>
              </tr>
            @empty
              <tr><td colspan="5" style="text-align:center;color:var(--text-muted);padding:20px;">No invoices yet</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>

    <div class="card">
      <div class="table-card-header">
        <div class="card-header-float" style="background:var(--gradient-green);">
          <div><h3>Recent Payments</h3><p>Last 10 payments received</p></div>
          <span class="material-icons">payments</span>
        </div>
      </div>
      <div class="table-wrapper">
        <table>
          <thead>
            <tr><th>Receipt #</th><th>Date</th><th>Method</th><th>Amount</th></tr>
          </thead>
          <tbody>
            @forelse($customer->payments as $pay)
              <tr>
                <td style="font-family:monospace;font-size:11px;">{{ $pay->receipt_number }}</td>
                <td>{{ $pay->payment_date->format('d M Y') }}</td>
                <td>{{ ucfirst(str_replace('_',' ',$pay->payment_method)) }}</td>
                <td class="td-primary">T$ {{ number_format($pay->amount, 2) }}</td>
              </tr>
            @empty
              <tr><td colspan="4" style="text-align:center;color:var(--text-muted);padding:20px;">No payments yet</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>

  </div>
</div>

@endsection
