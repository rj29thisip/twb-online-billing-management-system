@extends('layouts.app')
@section('title', 'Audit Logs')
@section('breadcrumb', 'Admin / System / Audit Logs')
@section('page-title', 'Audit Logs')

@section('content')

{{-- STATS --}}
<div class="stat-cards" style="grid-template-columns:repeat(4,1fr);margin-bottom:24px;">
  <div class="stat-card">
    <div class="stat-icon" style="background:var(--gradient-blue);"><span class="material-icons">login</span></div>
    <div class="stat-body"><div><div class="stat-label">Logins Today</div><div class="stat-value">{{ $loginsToday }}</div></div></div>
    <hr class="stat-divider">
    <div class="stat-footer"><span class="material-icons">calendar_today</span><span>{{ now()->format('d M Y') }}</span></div>
  </div>
  <div class="stat-card">
    <div class="stat-icon" style="background:var(--gradient-pink);"><span class="material-icons">receipt_long</span></div>
    <div class="stat-body"><div><div class="stat-label">Invoices Generated</div><div class="stat-value">{{ $invoicesToday }}</div></div></div>
    <hr class="stat-divider">
    <div class="stat-footer"><span class="material-icons">calendar_today</span><span>Today</span></div>
  </div>
  <div class="stat-card">
    <div class="stat-icon" style="background:var(--gradient-green);"><span class="material-icons">payments</span></div>
    <div class="stat-body"><div><div class="stat-label">Payments Recorded</div><div class="stat-value">{{ $paymentsToday }}</div></div></div>
    <hr class="stat-divider">
    <div class="stat-footer"><span class="material-icons">calendar_today</span><span>Today</span></div>
  </div>
  <div class="stat-card">
    <div class="stat-icon" style="background:var(--gradient-dark);"><span class="material-icons">history</span></div>
    <div class="stat-body"><div><div class="stat-label">Total Events</div><div class="stat-value">{{ number_format($totalLogs) }}</div></div></div>
    <hr class="stat-divider">
    <div class="stat-footer"><span class="material-icons">all_inclusive</span><span>All time</span></div>
  </div>
</div>

<div class="card">
  {{-- FILTERS --}}
  <form method="GET" class="filter-bar">
    <input type="date" name="date" class="form-control" value="{{ request('date') }}" title="Filter by date">
    <select name="action" class="form-control">
      <option value="">All Actions</option>
      @foreach($availableActions as $action)
        <option value="{{ $action }}" {{ request('action') === $action ? 'selected' : '' }}>
          {{ ucfirst(str_replace('_', ' ', $action)) }}
        </option>
      @endforeach
    </select>
    <select name="model_type" class="form-control">
      <option value="">All Models</option>
      @foreach($availableModels as $model)
        <option value="{{ $model }}" {{ request('model_type') === $model ? 'selected' : '' }}>{{ $model }}</option>
      @endforeach
    </select>
    <select name="user_id" class="form-control">
      <option value="">All Users</option>
      @foreach($users as $u)
        <option value="{{ $u->id }}" {{ request('user_id') == $u->id ? 'selected' : '' }}>
          {{ $u->name }} ({{ ucfirst($u->role) }})
        </option>
      @endforeach
    </select>
    <button type="submit" class="btn btn-outline btn-sm"><span class="material-icons">filter_list</span> Filter</button>
    @if(request()->hasAny(['date','action','model_type','user_id']))
      <a href="{{ route('admin.audit.index') }}" class="btn btn-outline btn-sm"><span class="material-icons">clear</span> Clear</a>
    @endif
  </form>

  {{-- TABLE --}}
  <div class="table-wrapper">
    <table>
      <thead>
        <tr>
          <th>Time</th>
          <th>User</th>
          <th>Action</th>
          <th>Subject</th>
          <th>Details</th>
          <th>IP Address</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        @forelse($logs as $log)
          <tr>
            <td style="font-size:12px;white-space:nowrap;">
              <div style="color:var(--text-primary);">{{ $log->created_at->format('d M Y') }}</div>
              <div style="color:var(--text-muted);">{{ $log->created_at->format('H:i:s') }}</div>
            </td>
            <td>
              @if($log->user)
                <div class="u-name">{{ $log->user->name }}</div>
                <div class="u-sub">{{ ucfirst($log->user->role) }}</div>
              @else
                <span style="color:var(--text-muted);">System</span>
              @endif
            </td>
            <td>
              @php
                $actionStyle = match($log->action) {
                  'login'              => 'badge-issued',
                  'logout'             => 'badge-draft',
                  'created'            => 'badge-active',
                  'updated'            => 'badge-partially',
                  'deleted','deactivated'  => 'badge-cancelled',
                  'invoice_generated'  => 'badge-paid',
                  'payment_recorded'   => 'badge-active',
                  'invoice_cancelled'  => 'badge-overdue',
                  'email_sent'         => 'badge-issued',
                  default              => 'badge-draft',
                };
              @endphp
              <span class="badge-status {{ $actionStyle }}">
                {{ ucfirst(str_replace('_', ' ', $log->action)) }}
              </span>
            </td>
            <td>
              <div style="font-size:13px;color:var(--text-primary);">{{ $log->model_type }}</div>
              @if($log->model_id)
                <div style="font-size:11px;color:var(--text-muted);font-family:monospace;">#{{ $log->model_id }}</div>
              @endif
            </td>
            <td style="max-width:280px;">
              @php
                $details = $log->new_values ?? $log->old_values ?? [];
                $note    = $details['_note'] ?? null;
                unset($details['_note'], $details['password'], $details['remember_token']);
              @endphp
              @if($note)
                <div style="font-size:12px;color:var(--text-primary);margin-bottom:4px;">{{ $note }}</div>
              @endif
              @if(!empty($details))
                <div style="font-size:11px;color:var(--text-muted);line-height:1.6;">
                  @foreach(array_slice($details, 0, 3) as $key => $val)
                    <span style="color:var(--accent-teal);">{{ $key }}</span>:
                    <span>{{ is_array($val) ? json_encode($val) : Str::limit((string)$val, 40) }}</span><br>
                  @endforeach
                  @if(count($details) > 3)
                    <span style="color:var(--text-muted);">+{{ count($details) - 3 }} more</span>
                  @endif
                </div>
              @endif
              @if(!empty($log->old_values) && !empty($log->new_values) && $log->action === 'updated')
                <div style="font-size:11px;margin-top:4px;">
                  @foreach(array_slice($log->old_values, 0, 2) as $key => $oldVal)
                    @if(isset($log->new_values[$key]))
                      <span style="color:var(--accent-pink);">{{ $key }}:</span>
                      <span style="color:var(--text-muted);">{{ Str::limit((string)$oldVal, 20) }}</span>
                      → <span style="color:var(--accent-green);">{{ Str::limit((string)$log->new_values[$key], 20) }}</span><br>
                    @endif
                  @endforeach
                </div>
              @endif
            </td>
            <td style="font-size:11px;color:var(--text-muted);font-family:monospace;">
              {{ $log->ip_address ?? '—' }}
            </td>
            <td>
              @if($log->new_values || $log->old_values)
                <button class="action-btn" title="View full details"
                        onclick='showDetail(@json($log))'>
                  <span class="material-icons">info</span>
                </button>
              @endif
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="7">
              <div class="empty-state">
                <span class="material-icons">history</span>
                <h3>No audit logs found</h3>
                <p style="color:var(--text-muted);font-size:13px;margin-top:4px;">
                  Logs will appear here as users interact with the system.
                </p>
              </div>
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <div class="pagination">
    <div class="pagination-info">
      Showing {{ $logs->firstItem() }}–{{ $logs->lastItem() }} of {{ $logs->total() }} events
    </div>
    <div class="pagination-btns">
      @if(!$logs->onFirstPage())
        <a href="{{ $logs->previousPageUrl() }}" class="pg-btn"><span class="material-icons">chevron_left</span></a>
      @endif
      @foreach($logs->getUrlRange(max(1,$logs->currentPage()-2), min($logs->lastPage(),$logs->currentPage()+2)) as $page => $url)
        <a href="{{ $url }}" class="pg-btn {{ $page === $logs->currentPage() ? 'active' : '' }}">{{ $page }}</a>
      @endforeach
      @if($logs->hasMorePages())
        <a href="{{ $logs->nextPageUrl() }}" class="pg-btn"><span class="material-icons">chevron_right</span></a>
      @endif
    </div>
  </div>
</div>

{{-- DETAIL MODAL --}}
<div class="modal-overlay" id="detailModal">
  <div class="modal" style="max-width:560px;">
    <div class="modal-header">
      <h3 class="modal-title">Audit Log Detail</h3>
      <button class="modal-close" onclick="closeDetail()"><span class="material-icons">close</span></button>
    </div>
    <div class="modal-body" id="detail-body" style="font-size:13px;"></div>
    <div class="modal-footer">
      <button class="btn btn-outline" onclick="closeDetail()">Close</button>
    </div>
  </div>
</div>

@endsection

@push('scripts')
<script>
function showDetail(log) {
  const body = document.getElementById('detail-body');
  const fmt  = (obj) => obj ? `<pre style="background:rgba(255,255,255,0.05);border-radius:6px;padding:10px;font-size:11px;overflow-x:auto;white-space:pre-wrap;color:var(--text-secondary);">${JSON.stringify(obj, null, 2)}</pre>` : '<span style="color:var(--text-muted);">—</span>';

  body.innerHTML = `
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-bottom:16px;">
      <div><span style="font-size:11px;text-transform:uppercase;color:var(--text-muted);">Time</span><div style="margin-top:4px;">${new Date(log.created_at).toLocaleString()}</div></div>
      <div><span style="font-size:11px;text-transform:uppercase;color:var(--text-muted);">Action</span><div style="margin-top:4px;">${log.action}</div></div>
      <div><span style="font-size:11px;text-transform:uppercase;color:var(--text-muted);">Model</span><div style="margin-top:4px;">${log.model_type} #${log.model_id ?? '—'}</div></div>
      <div><span style="font-size:11px;text-transform:uppercase;color:var(--text-muted);">IP Address</span><div style="margin-top:4px;font-family:monospace;">${log.ip_address ?? '—'}</div></div>
    </div>
    ${log.old_values ? `<div style="margin-bottom:12px;"><div style="font-size:11px;text-transform:uppercase;color:var(--accent-pink);margin-bottom:6px;">Before</div>${fmt(log.old_values)}</div>` : ''}
    ${log.new_values ? `<div><div style="font-size:11px;text-transform:uppercase;color:var(--accent-green);margin-bottom:6px;">After</div>${fmt(log.new_values)}</div>` : ''}
  `;
  document.getElementById('detailModal').classList.add('open');
}
function closeDetail() {
  document.getElementById('detailModal').classList.remove('open');
}
document.getElementById('detailModal').addEventListener('click', function(e) {
  if (e.target === this) closeDetail();
});
</script>
@endpush
