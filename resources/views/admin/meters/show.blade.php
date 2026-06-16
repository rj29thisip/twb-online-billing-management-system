@extends('layouts.app')
@section('title', 'Meter Detail')
@section('breadcrumb', 'Admin / Meters / Detail')
@section('page-title', 'Meter Detail')

@push('head')
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
@endpush

@section('content')

<div style="margin-bottom:20px;display:flex;align-items:center;justify-content:space-between;">
  <a href="{{ route('admin.meters.index') }}" class="btn btn-outline btn-sm">
    <span class="material-icons">arrow_back</span> Back
  </a>
  <a href="{{ route('admin.meters.edit', $meter) }}" class="btn btn-primary btn-sm">
    <span class="material-icons">edit</span> Edit Meter
  </a>
</div>

<div class="detail-grid">

  <div style="display:flex;flex-direction:column;gap:24px;">
    <div class="card">
      <div class="table-card-header">
        <div class="card-header-float" style="background:var(--gradient-dark);">
          <div>
            <h3>{{ $meter->meter_id }}</h3>
            <p>Endpoint: {{ $meter->endpoint_id ?? '—' }}</p>
          </div>
          <span class="badge-status badge-{{ $meter->status }}" style="font-size:13px;padding:6px 14px;">
            {{ ucfirst($meter->status) }}
          </span>
        </div>
      </div>
      <div class="card-body" style="padding-top:0;">
        <div class="detail-row">
          <span class="detail-label">Customer</span>
          <span class="detail-value">
            <a href="{{ route('admin.customers.show', $meter->customer) }}" style="color:var(--accent-blue);">
              {{ $meter->customer->name }}
            </a>
          </span>
        </div>
        <div class="detail-row">
          <span class="detail-label">Account #</span>
          <span class="detail-value" style="font-family:monospace;">{{ $meter->customer->account_number }}</span>
        </div>
        <div class="detail-row">
          <span class="detail-label">Meter Type</span>
          <span class="detail-value">{{ ucfirst($meter->meter_type) }}</span>
        </div>
        <div class="detail-row">
          <span class="detail-label">Brand / Model</span>
          <span class="detail-value">{{ $meter->brand ?? '—' }} / {{ $meter->model ?? '—' }}</span>
        </div>
        <div class="detail-row">
          <span class="detail-label">Installed</span>
          <span class="detail-value">{{ $meter->installation_date?->format('d M Y') ?? '—' }}</span>
        </div>
        <div class="detail-row">
          <span class="detail-label">Last Maintenance</span>
          <span class="detail-value">{{ $meter->last_maintenance_date?->format('d M Y') ?? '—' }}</span>
        </div>
        @if($meter->lastReading())
          <div class="detail-row">
            <span class="detail-label">Last Reading</span>
            <span class="detail-value">
              {{ number_format($meter->lastReading()->value, 0) }} L
              <span style="color:var(--text-muted);font-size:12px;margin-left:6px;">
                ({{ $meter->lastReading()->capture_time->format('d M Y H:i') }})
              </span>
            </span>
          </div>
        @endif
      </div>
    </div>
  </div>

  <div>
    <div class="card">
      <div class="table-card-header">
        <div class="card-header-float" style="background:var(--gradient-dark);">
          <div><h3>Recent Readings</h3><p>Last 48 hourly readings</p></div>
          <span class="material-icons">data_usage</span>
        </div>
      </div>
      <div class="table-wrapper" style="max-height:500px;overflow-y:auto;">
        <table>
          <thead>
            <tr><th>Capture Time</th><th>Value (L)</th><th>Usage (L)</th><th>Source</th><th>Flag</th></tr>
          </thead>
          <tbody>
            @forelse($meter->readings as $r)
              <tr style="{{ $r->is_anomaly ? 'background:rgba(233,30,99,0.05)' : '' }}">
                <td style="font-size:12px;">{{ $r->capture_time->format('d M H:i') }}</td>
                <td style="font-family:monospace;font-size:11px;">{{ number_format($r->value, 0) }}</td>
                <td class="td-primary">{{ number_format($r->usage, 0) }}</td>
                <td style="font-size:11px;color:var(--text-muted);">{{ ucfirst(str_replace('_',' ',$r->source)) }}</td>
                <td>
                  @if($r->is_anomaly)
                    <span class="badge-status badge-overdue" style="font-size:10px;padding:2px 6px;">!</span>
                  @endif
                </td>
              </tr>
            @empty
              <tr><td colspan="5" style="text-align:center;color:var(--text-muted);padding:20px;">No readings yet</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>

</div>

@endsection
