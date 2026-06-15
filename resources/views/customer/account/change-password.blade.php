@extends('layouts.app')
@section('title', 'Change Password')
@section('breadcrumb', 'Account / Change Password')
@section('page-title', 'Change Password')

@section('content')
<div style="max-width:480px;margin:0 auto;">
  <div class="card">
    <div class="table-card-header">
      <div class="card-header-float" style="background:var(--gradient-blue);">
        <div>
          <h3>Change Password</h3>
          <p>Keep your account secure</p>
        </div>
        <span class="material-icons">lock_reset</span>
      </div>
    </div>
    <div class="card-body" style="padding:24px;">

      @if(session('success'))
        <div class="alert alert-success" style="margin-bottom:16px;">
          <span class="material-icons" style="font-size:16px;">check_circle</span>
          {{ session('success') }}
        </div>
      @endif

      @if($errors->any())
        <div class="alert alert-error" style="margin-bottom:16px;">
          <span class="material-icons" style="font-size:16px;">error</span>
          {{ $errors->first() }}
        </div>
      @endif

      <form method="POST" action="{{ route('customer.account.password.update') }}">
        @csrf @method('PUT')

        <div class="form-group">
          <label class="form-label">Current Password <span style="color:var(--accent-pink)">*</span></label>
          <input type="password" name="current_password" class="form-control"
                 required autocomplete="current-password" placeholder="Your current password">
        </div>

        <div class="form-group" style="margin-top:14px;">
          <label class="form-label">New Password <span style="color:var(--accent-pink)">*</span></label>
          <input type="password" name="password" id="newPass" class="form-control"
                 required minlength="8" autocomplete="new-password" placeholder="Minimum 8 characters">
          <div id="strengthBar" style="height:4px;border-radius:2px;margin-top:6px;background:var(--surface-2);overflow:hidden;">
            <div id="strengthFill" style="height:100%;width:0;transition:width .3s,background .3s;border-radius:2px;"></div>
          </div>
          <div id="strengthText" style="font-size:11px;color:var(--text-muted);margin-top:3px;"></div>
        </div>

        <div class="form-group" style="margin-top:14px;">
          <label class="form-label">Confirm New Password <span style="color:var(--accent-pink)">*</span></label>
          <input type="password" name="password_confirmation" class="form-control"
                 required minlength="8" autocomplete="new-password" placeholder="Repeat new password">
        </div>

        <div style="margin-top:24px;display:flex;gap:10px;">
          <button type="submit" class="btn btn-primary">
            <span class="material-icons" style="font-size:16px;">save</span> Update Password
          </button>
          <a href="{{ route('customer.dashboard') }}" class="btn btn-outline">Cancel</a>
        </div>
      </form>
    </div>
  </div>
</div>

@push('scripts')
<script>
document.getElementById('newPass').addEventListener('input', function() {
  var v = this.value, score = 0;
  if (v.length >= 8) score++;
  if (/[A-Z]/.test(v)) score++;
  if (/[0-9]/.test(v)) score++;
  if (/[^A-Za-z0-9]/.test(v)) score++;
  var colors = ['','#e74c3c','#f39c12','#3498db','#1abc9c'];
  var labels = ['','Weak','Fair','Good','Strong'];
  var widths = ['0%','25%','50%','75%','100%'];
  document.getElementById('strengthFill').style.width      = widths[score] || '0%';
  document.getElementById('strengthFill').style.background = colors[score] || 'transparent';
  document.getElementById('strengthText').textContent      = v.length ? labels[score] : '';
});
</script>
@endpush
@endsection
