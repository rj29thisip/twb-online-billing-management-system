@extends('layouts.app')
@section('title', 'Meter Readings')
@section('breadcrumb', 'Admin / Meters / Readings')
@section('page-title', 'Meter Readings')

@section('content')

@if($anomalyCount > 0)
  <div class="alert alert-error">
    <span class="material-icons" style="font-size:18px;">warning_amber</span>
    {{ $anomalyCount }} anomalous reading(s) detected.
    <a href="{{ route('admin.readings.index') }}?anomaly=1" style="color:inherit;text-decoration:underline;margin-left:6px;">View anomalies →</a>
    <button class="alert-close" onclick="this.parentElement.remove()"><span class="material-icons" style="font-size:16px;">close</span></button>
  </div>
@endif

<div class="section-header">
  <div><h2>Meter Readings</h2><p>All AMR and manual readings</p></div>
  <a href="{{ route('admin.readings.import') }}" class="btn btn-primary">
    <span class="material-icons">upload_file</span> Import CSV
  </a>
</div>

<div class="card">
  <form method="GET" class="filter-bar">
    <input type="text" name="meter" class="form-control" placeholder="Meter ID..." value="{{ request('meter') }}">
    <input type="date" name="date" class="form-control" value="{{ request('date') }}">
    <label style="display:flex;align-items:center;gap:6px;font-size:13px;color:var(--text-secondary);cursor:pointer;">
      <input type="checkbox" name="anomaly" value="1" {{ request('anomaly') ? 'checked' : '' }}>
      Anomalies only
    </label>
    <button type="submit" class="btn btn-outline btn-sm"><span class="material-icons">filter_list</span> Filter</button>
    @if(request()->hasAny(['meter','date','anomaly']))
      <a href="{{ route('admin.readings.index') }}" class="btn btn-outline btn-sm"><span class="material-icons">clear</span> Clear</a>
    @endif
  </form>

  <div class="table-wrapper">
    <table>
      <thead>
        <tr><th>Customer</th><th>Meter ID</th><th>Capture Time</th><th>Value (L)</th><th>Usage (L)</th><th>Source</th><th>Anomaly</th><th>Actions</th></tr>
      </thead>
      <tbody>
        @forelse($readings as $r)
          <tr style="{{ $r->is_anomaly ? 'background:rgba(233,30,99,0.05)' : '' }}">
            <td>
              <div class="u-name">{{ $r->meter->customer->name }}</div>
              <div class="u-sub">{{ $r->meter->customer->account_number }}</div>
            </td>
            <td style="font-family:monospace;font-size:11px;color:var(--accent-teal);">{{ $r->meter->meter_id }}</td>
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
          <tr><td colspan="8"><div class="empty-state"><span class="material-icons">data_usage</span><h3>No readings found</h3></div></td></tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <div class="pagination">
    <div class="pagination-info">Showing {{ $readings->firstItem() }}–{{ $readings->lastItem() }} of {{ $readings->total() }}</div>
    <div class="pagination-btns">
      @if(!$readings->onFirstPage())
        <a href="{{ $readings->previousPageUrl() }}" class="pg-btn"><span class="material-icons">chevron_left</span></a>
      @endif
      @if($readings->hasMorePages())
        <a href="{{ $readings->nextPageUrl() }}" class="pg-btn"><span class="material-icons">chevron_right</span></a>
      @endif
    </div>
  </div>
</div>

@endsection
