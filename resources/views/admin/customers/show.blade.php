@extends('layouts.app')
@section('title', 'Customer: ' . $customer->full_name)
@section('breadcrumb', 'Admin / Customers / ' . $customer->account_number)
@section('page-title', 'CUSTOMER DETAILS')

@section('content')

{{-- Header bar --}}
<div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:24px;gap:16px;flex-wrap:wrap;">
  <div style="display:flex;align-items:center;gap:12px;">
    <a href="{{ route('admin.customers.index') }}" class="btn btn-outline btn-sm">
      <span class="material-icons">arrow_back</span>
    </a>
    <div>
      <h2 style="margin:0;font-size:20px;">{{ $customer->full_name }}</h2>
      <div style="font-size:13px;color:var(--text-muted);margin-top:2px;">
        Account #{{ $customer->account_number }}
        @if($customer->district)
          &nbsp;·&nbsp;
          <span style="color:var(--accent-teal);">{{ $customer->district->name }}</span>
        @endif
      </div>
    </div>
  </div>
  <div style="display:flex;gap:8px;">
    <a href="{{ route('admin.customers.edit', $customer) }}" class="btn btn-primary btn-sm">
      <span class="material-icons" style="font-size:15px;">edit</span> Edit
    </a>
  </div>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">

  {{-- ── LEFT COLUMN ─────────────────────────────────────────────────── --}}
  <div style="display:flex;flex-direction:column;gap:20px;">

    <div class="card-tight-margin">
      <div class="table-card-header">
        <div style="padding:14px 20px;display:flex;justify-content:space-between;align-items:center;">
          <h4 style="margin:0;font-size:13px;display:flex;align-items:center;gap:6px;">
            <span class="material-icons" style="font-size:16px;color:var(--accent-blue);">person</span>
            Personal Details
          </h4>
          <span class="badge-status {{ match($customer->status) {
            'active'    => 'badge-active',
            'suspended' => 'badge-overdue',
            default     => 'badge-inactive'
          } }}">{{ ucfirst($customer->status) }}</span>
        </div>
      </div>
      <div class="card-body" style="padding:0 20px 16px;">
        @php
          $personalRows = [
            ['Given Name',   $customer->given_name  ?? '—'],
            ['Family Name',  $customer->family_name ?? '—'],
            ['Date of Birth',$customer->date_of_birth ? $customer->date_of_birth->format('d M Y') : '—'],
            ['Gender',       $customer->gender ? ucwords(str_replace('_', ' ', $customer->gender)) : '—'],
            ['Email',        $customer->email ?? '—'],
            ['Contact No.',  $customer->phone ?? '—'],
          ];
        @endphp
        @foreach($personalRows as [$label, $value])
        <div class="detail-row">
          <span class="detail-label">{{ $label }}</span>
          <span class="detail-value">{{ $value }}</span>
        </div>
        @endforeach
      </div>
    </div>

    {{-- Residential Address --}}
    <div class="card">
      <div class="table-card-header">
        <div style="padding:14px 20px;">
          <h4 style="margin:0;font-size:13px;display:flex;align-items:center;gap:6px;">
            <span class="material-icons" style="font-size:16px;color:var(--accent-blue);">home</span>
            Residential Address
          </h4>
        </div>
      </div>
      <div class="card-body" style="padding:0 20px 16px;">
        <div class="detail-row">
          <span class="detail-label">Address Line</span>
          <span class="detail-value">{{ $customer->address_line ?? '—' }}</span>
        </div>
        <div class="detail-row">
          <span class="detail-label">Suburb / Town</span>
          <span class="detail-value">{{ $customer->suburb ?? '—' }}</span>
        </div>
        <div class="detail-row">
          <span class="detail-label">Island</span>
          <span class="detail-value">{{ $customer->island_label }}</span>
        </div>
      </div>
    </div>

    {{-- Record Info --}}
    <div class="card">
      <div class="table-card-header">
        <div style="padding:14px 20px;">
          <h4 style="margin:0;font-size:13px;display:flex;align-items:center;gap:6px;">
            <span class="material-icons" style="font-size:16px;color:var(--accent-blue);">history</span>
            Record Information
          </h4>
        </div>
      </div>
      <div class="card-body" style="padding:0 20px 16px;">
        <div class="detail-row">
          <span class="detail-label">Created By</span>
          <span class="detail-value">{{ $customer->createdBy?->name ?? '—' }}</span>
        </div>
        <div class="detail-row">
          <span class="detail-label">Created On</span>
          <span class="detail-value">{{ $customer->created_at->format('d M Y, H:i') }}</span>
        </div>
        <div class="detail-row">
          <span class="detail-label">Last Updated</span>
          <span class="detail-value">{{ $customer->updated_at->format('d M Y, H:i') }}</span>
        </div>
      </div>
    </div>

  </div>

  {{-- ── RIGHT COLUMN ────────────────────────────────────────────────── --}}
  <div style="display:flex;flex-direction:column;gap:20px;">

    {{-- Account Details --}}
    <div class="card-tight-margin">
      <div class="table-card-header">
        <div style="padding:14px 20px;">
          <h4 style="margin:0;font-size:13px;display:flex;align-items:center;gap:6px;">
            <span class="material-icons" style="font-size:16px;color:var(--accent-blue);">receipt_long</span>
            Account Details
          </h4>
        </div>
      </div>
      <div class="card-body" style="padding:0 20px 16px;">
        <div class="detail-row">
          <span class="detail-label">Account #</span>
          <span class="detail-value td-primary" style="font-family:monospace;">{{ $customer->account_number }}</span>
        </div>
        <div class="detail-row">
          <span class="detail-label">Block</span>
          <span class="detail-value">{{ $customer->block_number ?? '—' }}</span>
        </div>
        <div class="detail-row">
          <span class="detail-label">District / Area</span>
          <span class="detail-value">
            @if($customer->district)
              <span style="background:rgba(26,188,156,0.12);color:var(--accent-teal);padding:2px 10px;border-radius:10px;font-size:12px;">
                {{ $customer->district->name }}{{ $customer->district->is_headquarters ? ' (HQ)' : '' }}
              </span>
            @else
              <span style="color:var(--text-muted);">Not assigned</span>
            @endif
          </span>
        </div>
        <div class="detail-row">
          <span class="detail-label">Outstanding</span>
          <span class="detail-value" style="color:{{ $customer->outstandingBalance() > 0 ? 'var(--accent-pink)' : 'var(--accent-green)' }};font-weight:600;">
            T$ {{ number_format($customer->outstandingBalance(), 2) }}
          </span>
        </div>
      </div>
    </div>

    {{-- Property Details --}}
    <div class="card">
      <div class="table-card-header">
        <div style="padding:14px 20px;">
          <h4 style="margin:0;font-size:13px;display:flex;align-items:center;gap:6px;">
            <span class="material-icons" style="font-size:16px;color:var(--accent-blue);">landscape</span>
            Property Details
          </h4>
        </div>
      </div>
      <div class="card-body" style="padding:0 20px 16px;">
        <div class="detail-row">
          <span class="detail-label">Deed #</span>
          <span class="detail-value">{{ $customer->deed_number ?? '—' }}</span>
        </div>
        <div class="detail-row">
          <span class="detail-label">Surveyed Date</span>
          <span class="detail-value">{{ $customer->surveyed_date ? $customer->surveyed_date->format('d M Y') : '—' }}</span>
        </div>
        @if($customer->property_notes)
        <div class="detail-row" style="align-items:flex-start;">
          <span class="detail-label">Notes</span>
          <span class="detail-value" style="font-size:12px;color:var(--text-secondary);white-space:pre-wrap;">{{ $customer->property_notes }}</span>
        </div>
        @endif
      </div>
    </div>

    {{-- Smart Meters --}}
    <div class="card">
      <div class="table-card-header">
        <div style="padding:14px 20px;display:flex;justify-content:space-between;align-items:center;">
          <h4 style="margin:0;font-size:13px;display:flex;align-items:center;gap:6px;">
            <span class="material-icons" style="font-size:16px;color:var(--accent-blue);">speed</span>
            Smart Meters ({{ $customer->meters->count() }})
          </h4>
          <a href="{{ route('admin.meters.create') }}?customer_id={{ $customer->id }}"
             class="btn btn-outline btn-sm">
            <span class="material-icons" style="font-size:14px;">add</span> Add Meter
          </a>
        </div>
      </div>
      @forelse($customer->meters as $meter)
      <div style="padding:12px 20px;border-bottom:1px solid var(--border);font-size:13px;">
        <div style="display:flex;justify-content:space-between;align-items:flex-start;">
          <div>
            <div style="font-family:monospace;color:var(--accent-teal);font-weight:600;">
              {{ $meter->meter_id }}
            </div>
            <div style="color:var(--text-secondary);font-size:12px;margin-top:2px;">
              @if($meter->brand || $meter->model)
                {{ implode(' ', array_filter([$meter->brand, $meter->model])) }}
              @endif
              @if($meter->serial_number)
                &nbsp;·&nbsp; SN: {{ $meter->serial_number }}
              @endif
            </div>
            @if($meter->installation_date)
            <div style="color:var(--text-muted);font-size:11px;margin-top:2px;">
              Installed: {{ $meter->installation_date->format('d M Y') }}
            </div>
            @endif
          </div>
          <span class="badge-status {{ $meter->status === 'active' ? 'badge-active' : 'badge-inactive' }}">
            {{ ucfirst($meter->status) }}
          </span>
        </div>
      </div>
      @empty
      <div class="empty-state" style="padding:24px;">
        <span class="material-icons" style="font-size:28px;">speed</span>
        <p style="margin:8px 0 0;font-size:13px;">No meters registered</p>
      </div>
      @endforelse
    </div>

  </div>
</div>

{{-- ── INVOICES + PAYMENTS ──────────────────────────────────────────────── --}}
<div style="margin-top:20px;">
  <div class="card">
    <div class="table-card-header">
      <div style="padding:14px 20px;">
        <h4 style="margin:0;font-size:13px;display:flex;align-items:center;gap:6px;">
          <span class="material-icons" style="font-size:16px;color:var(--accent-blue);">receipt</span>
          Recent Invoices
        </h4>
      </div>
    </div>
    <div class="table-wrapper">
      <table>
        <thead>
          <tr><th>Invoice #</th><th>Period</th><th>Amount</th><th>Status</th><th>Due</th><th></th></tr>
        </thead>
        <tbody>
          @forelse($customer->invoices->take(10) as $inv)
          <tr>
            <td style="font-family:monospace;font-size:12px;color:var(--accent-teal);">{{ $inv->invoice_number }}</td>
            <td style="font-size:12px;">{{ \Carbon\Carbon::parse($inv->billing_period_start)->format('M Y') }}</td>
            <td class="td-primary">T$ {{ number_format($inv->total_amount, 2) }}</td>
            <td><span class="badge-status badge-{{ $inv->status }}">{{ ucfirst($inv->status) }}</span></td>
            <td style="font-size:12px;color:var(--text-muted);">
              {{ $inv->due_date ? \Carbon\Carbon::parse($inv->due_date)->format('d M Y') : '—' }}
            </td>
            <td>
              <a href="{{ route('admin.invoices.show', $inv) }}" class="action-btn">
                <span class="material-icons">open_in_new</span>
              </a>
            </td>
          </tr>
          @empty
          <tr><td colspan="6"><div class="empty-state" style="padding:20px;">No invoices yet</div></td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>

@endsection
