@extends('layouts.app')
@section('title', 'Change Password Required')
@section('breadcrumb', 'Admin / Change Password')
@section('page-title', 'Change Password')

@section('content')
<div style="max-width:460px;margin:0 auto;">
  <div class="card" style="border:1px solid var(--accent-orange);border-top:3px solid var(--accent-orange);">
    <div class="card-body" style="padding:28px;">
      <div style="display:flex;align-items:center;gap:10px;margin-bottom:8px;">
        <span class="material-icons" style="color:var(--accent-orange);">lock_reset</span>
        <h3 style="margin:0;font-size:16px;">Password Change Required</h3>
      </div>
      <p style="font-size:13px;color:var(--text-secondary);margin-bottom:20px;">
        Welcome, <strong>{{ auth()->user()->name }}</strong>! You must set a new password before accessing the system.
      </p>

      @if($errors->any())
        <div class="alert alert-error" style="margin-bottom:16px;">
          <span class="material-icons" style="font-size:16px">error</span> {{ $errors->first() }}
        </div>
      @endif

      <form method="POST" action="{{ route('admin.password.change.update') }}">
        @csrf
        <div class="form-group">
          <label class="form-label">New Password</label>
          <input type="password" name="password" class="form-control" required minlength="8" autofocus placeholder="Minimum 8 characters">
        </div>
        <div class="form-group" style="margin-top:12px;">
          <label class="form-label">Confirm New Password</label>
          <input type="password" name="password_confirmation" class="form-control" required minlength="8" placeholder="Repeat your password">
        </div>
        <button type="submit" class="btn btn-primary" style="width:100%;margin-top:16px;">
          <span class="material-icons" style="font-size:16px;">check_circle</span> Set Password & Continue
        </button>
      </form>
    </div>
  </div>
</div>
@endsection
