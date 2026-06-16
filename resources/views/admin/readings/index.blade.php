@extends('layouts.app')
@section('title', 'Meter Readings')
@section('breadcrumb', 'Admin / Meters / Readings')
@section('page-title', 'METER READINGS')

@section('content')

@if($anomalyCount > 0)
  <div class="alert alert-error">
    <span class="material-icons" style="font-size:18px;">warning_amber</span>
    {{ $anomalyCount }} anomalous reading(s) in the system.
    <a href="{{ route('admin.readings.index') }}?anomaly=1" style="color:inherit;text-decoration:underline;margin-left:6px;">View anomalies →</a>
    <button class="alert-close" onclick="this.parentElement.remove()"><span class="material-icons" style="font-size:16px;">close</span></button>
  </div>
@endif

<div class="section-header">
  <div><h2>Consumption Readings</h2><p>View Automated Meter Readings (AMR) or Manual Meter Readings and Import Manual Meter Readings.</p></div>
  <a href="{{ route('admin.readings.import') }}" class="btn btn-primary">
    <span class="material-icons">upload_file</span> Import Meter Readings
  </a>
</div>

<div class="card-tight-margin">
  <form method="GET" action="{{ route('admin.readings.index') }}" class="filter-bar" style="flex-wrap:wrap;gap:8px;">

    {{-- Meter ID --}}
    <input type="text" name="meter" class="form-control" placeholder="Meter ID..."
           value="{{ request('meter') }}" style="min-width:140px;flex:1;">

    {{-- Date From --}}
    <div style="display:flex;align-items:center;gap:6px;">
      <span style="font-size:12px;color:var(--text-muted);white-space:nowrap;">From</span>
      <input type="date" name="date_from" class="form-control"
             value="{{ request('date_from') }}" style="min-width:140px;">
    </div>

    {{-- Date To --}}
    <div style="display:flex;align-items:center;gap:6px;">
      <span style="font-size:12px;color:var(--text-muted);white-space:nowrap;">To</span>
      <input type="date" name="date_to" class="form-control"
             value="{{ request('date_to') }}" style="min-width:140px;">
    </div>

    {{-- Anomalies only — FIX: value="1", checked by boolean() --}}
    <label style="display:flex;align-items:center;gap:6px;font-size:13px;color:var(--text-secondary);cursor:pointer;white-space:nowrap;">
      <input type="checkbox" name="anomaly" value="1"
             {{ request()->boolean('anomaly') ? 'checked' : '' }}>
      Anomalies only
    </label>

    <button type="submit" class="btn btn-outline btn-sm">
      <span class="material-icons">filter_list</span> Filter
    </button>

    @if($hasFilter)
      <a href="{{ route('admin.readings.index') }}" class="btn btn-outline btn-sm">
        <span class="material-icons">clear</span> Clear
      </a>
    @endif
  </form>

  {{-- Active filter chips --}}
  @if($hasFilter)
  <div style="padding:0 20px 12px;display:flex;flex-wrap:wrap;gap:6px;align-items:center;">
    <span style="font-size:11px;color:var(--text-muted);">Active filters:</span>
    @if(request('meter'))
      <span style="font-size:11px;background:rgba(52,152,219,0.15);color:var(--accent-blue);padding:2px 8px;border-radius:10px;border:1px solid rgba(52,152,219,0.3);">
        Meter: {{ request('meter') }}
      </span>
    @endif
    @if(request('date_from') || request('date_to'))
      <span style="font-size:11px;background:rgba(52,152,219,0.15);color:var(--accent-blue);padding:2px 8px;border-radius:10px;border:1px solid rgba(52,152,219,0.3);">
        Date: {{ request('date_from','—') }} → {{ request('date_to','now') }}
      </span>
    @endif
    @if(request()->boolean('anomaly'))
      <span style="font-size:11px;background:rgba(233,30,99,0.15);color:var(--accent-pink);padding:2px 8px;border-radius:10px;border:1px solid rgba(233,30,99,0.3);">
        ⚠ Anomalies only
      </span>
    @endif
  </div>
  @endif

  <div class="table-wrapper">
    <table>
      <thead>
        <tr>
          <th>Customer</th><th>Meter ID</th><th>Capture Time</th>
          <th>Value (L)</th><th>Usage (L)</th><th>Source</th><th>Anomaly</th><th>Actions</th>
        </tr>
      </thead>
      <tbody>
        @forelse($readings as $r)
          <tr style="{{ $r->is_anomaly ? 'background:rgba(233,30,99,0.05)' : '' }}">
            <td>
              <div class="u-name">{{ $r->meter->customer->name ?? '—' }}</div>
              <div class="u-sub">{{ $r->meter->customer->account_number ?? '' }}</div>
            </td>
            <td style="font-family:monospace;font-size:11px;color:var(--accent-teal);">
              {{ $r->meter->meter_id ?? '—' }}
            </td>
            <td>{{ $r->capture_time->format('d M Y H:i') }}</td>
            <td>{{ number_format($r->value, 0) }}</td>
            <td class="td-primary">{{ number_format($r->usage, 0) }}</td>
            <td>{{ ucfirst(str_replace('_',' ',$r->source)) }}</td>
            <td>
              @if($r->is_anomaly)
                <span class="badge-status badge-overdue" title="{{ $r->anomaly_note }}">Anomaly</span>
              @else
                <span style="color:var(--text-muted);font-size:12px;">—</span>
              @endif
            </td>
            <td>
              @if($r->is_anomaly)
                <form action="{{ route('admin.readings.anomaly.resolve', $r) }}" method="POST" style="display:inline;">
                  @csrf @method('PATCH')
                  <button type="submit" class="action-btn" title="Mark as resolved" style="color:var(--accent-green);">
                    <span class="material-icons">check_circle</span>
                  </button>
                </form>
              @endif
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="8">
              <div class="empty-state" style="padding:48px 0;">
                <span class="material-icons" style="font-size:40px;opacity:0.3;">
                  {{ $hasFilter ? 'search_off' : 'data_usage' }}
                </span>
                @if($hasFilter)
                  <h3>No readings match your filters</h3>
                  <p style="font-size:13px;color:var(--text-muted);margin-top:6px;">
                    Try adjusting the date range, meter ID, or uncheck "Anomalies only".
                  </p>
                  <a href="{{ route('admin.readings.index') }}" class="btn btn-outline btn-sm" style="margin-top:12px;">
                    <span class="material-icons">clear</span> Clear all filters
                  </a>
                @else
                  <h3>No readings recorded yet</h3>
                  <p style="font-size:13px;color:var(--text-muted);margin-top:6px;">
                    Import a CSV or XML file to get started.
                  </p>
                @endif
              </div>
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  @if($readings->total() > 0)
  <div class="pagination">
    <div class="pagination-info">
      Showing {{ $readings->firstItem() }}–{{ $readings->lastItem() }}
      of {{ $readings->total() }} reading(s)
      @if($hasFilter)<span style="color:var(--accent-orange);margin-left:4px;">(filtered)</span>@endif
    </div>
    <div class="pagination-btns">
      @if(!$readings->onFirstPage())
        <a href="{{ $readings->previousPageUrl() }}" class="pg-btn"><span class="material-icons">chevron_left</span></a>
      @endif
      @if($readings->hasMorePages())
        <a href="{{ $readings->nextPageUrl() }}" class="pg-btn"><span class="material-icons">chevron_right</span></a>
      @endif
    </div>
  </div>
  @endif
</div>

@endsection
