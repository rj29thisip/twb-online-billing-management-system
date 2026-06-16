@extends('layouts.app')
@section('title', 'Email Configuration')
@section('breadcrumb', 'Admin / Settings / Email Config')
@section('page-title', 'Email Configuration')

@section('content')
<div class="section-header">
  <div>
    <h2>Email Configuration</h2>
    <p>Manage outgoing email settings for all system notifications.</p>
  </div>
  <a href="{{ route('admin.email-config.create') }}" class="btn btn-primary">
    <span class="material-icons">add</span> Add Config
  </a>
</div>

<div class="alert" style="background:rgba(52,152,219,0.1);border:1px solid rgba(52,152,219,0.3);border-radius:8px;padding:12px 16px;margin-bottom:20px;font-size:13px;color:var(--text-secondary);display:flex;gap:8px;align-items:flex-start;">
  <span class="material-icons" style="font-size:16px;color:var(--accent-blue);margin-top:1px;">info</span>
  Only <strong>one</strong> configuration can be active at a time. The active config is used for all emails: password resets, customer portal credentials, and notifications.
</div>

<div class="card">
  <div class="table-wrapper">
    <table>
      <thead>
        <tr><th>From</th><th>Host / Driver</th><th>Port</th><th>Encryption</th><th>Status</th><th>Actions</th></tr>
      </thead>
      <tbody>
        @forelse($configs as $config)
        <tr style="{{ $config->is_active ? 'background:rgba(26,188,156,0.05);' : '' }}">
          <td>
            <div class="u-name">{{ $config->from_name }}</div>
            <div class="u-sub">{{ $config->from_address }}</div>
          </td>
          <td>
            <div style="font-family:monospace;font-size:11px;color:var(--accent-teal);">{{ $config->host ?: '—' }}</div>
            <div class="u-sub">{{ strtoupper($config->mailer) }}</div>
          </td>
          <td>{{ $config->port }}</td>
          <td><span style="font-family:monospace;font-size:11px;">{{ strtoupper($config->encryption) }}</span></td>
          <td>
            @if($config->is_active)
              <span class="badge-status badge-active">Active</span>
            @else
              <span class="badge-status badge-inactive">Inactive</span>
            @endif
          </td>
          <td>
            <div style="display:flex;gap:6px;align-items:center;">
              {{-- Test button --}}
              <button class="action-btn" style="color:var(--accent-blue);" title="Send test email"
                      onclick="openTestModal({{ $config->id }}, '{{ $config->from_address }}')">
                <span class="material-icons">send</span>
              </button>
              <a href="{{ route('admin.email-config.edit', $config) }}" class="action-btn" title="Edit">
                <span class="material-icons">edit</span>
              </a>
              @if(!$config->is_active)
              <form method="POST" action="{{ route('admin.email-config.destroy', $config) }}"
                    onsubmit="return confirm('Delete this email config?')">
                @csrf @method('DELETE')
                <button type="submit" class="action-btn" title="Delete" style="color:var(--accent-pink);">
                  <span class="material-icons">delete</span>
                </button>
              </form>
              @endif
            </div>
          </td>
        </tr>
        @empty
        <tr><td colspan="6">
          <div class="empty-state">
            <span class="material-icons">email</span>
            <h3>No email configs yet</h3>
            <p>Add one to enable system email notifications.</p>
          </div>
        </td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>

{{-- Test Email Modal --}}
<div id="testEmailModal" style="display:none;position:fixed;inset:0;z-index:9999;background:rgba(0,0,0,0.55);align-items:center;justify-content:center;">
  <div style="background:var(--surface,#1e2a3a);border:1px solid var(--border,rgba(255,255,255,0.1));border-radius:12px;padding:28px 32px;width:100%;max-width:400px;">
    <h3 style="margin:0 0 16px;font-size:15px;">Send Test Email</h3>
    <form id="testEmailForm" method="POST">
      @csrf
      <div class="form-group">
        <label class="form-label">Send test to</label>
        <input type="email" name="test_email" class="form-control" required placeholder="your@email.com">
      </div>
      <div style="display:flex;gap:10px;margin-top:20px;justify-content:flex-end;">
        <button type="button" onclick="document.getElementById('testEmailModal').style.display='none'"
                class="btn btn-outline">Cancel</button>
        <button type="submit" class="btn btn-primary">
          <span class="material-icons" style="font-size:16px;">send</span> Send Test
        </button>
      </div>
    </form>
  </div>
</div>

@push('scripts')
<script>
function openTestModal(configId, fromAddress) {
  var baseUrl = '{{ url("admin/email-config") }}/' + configId + '/test';
  document.getElementById('testEmailForm').action = baseUrl;
  document.getElementById('testEmailModal').style.display = 'flex';
}
document.getElementById('testEmailModal').addEventListener('click', function(e) {
  if (e.target === this) this.style.display = 'none';
});
</script>
@endpush
@endsection
