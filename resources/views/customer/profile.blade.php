@extends('layouts.app')
@section('title', 'My Profile')
@section('breadcrumb', 'Customer / Profile')
@section('page-title', 'My Profile')

@section('content')
<div style="max-width:860px;margin:0 auto;">

  <div class="card">
    <div class="table-card-header">
      <div class="card-header-float" style="background:var(--gradient-dark);">
        <div>
          <h3>Account Information</h3>
          <p>{{ $customer->account_number }} — {{ ucfirst($customer->status ?? 'active') }}</p>
        </div>
        <div class="avatar" style="width:52px;height:52px;font-size:20px;flex-shrink:0;">
          {{ substr($customer->name, 0, 2) }}
        </div>
      </div>
    </div>
    <div class="card-body" style="padding-top:0;">

      {{-- Two column layout --}}
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:0 32px;">

        {{-- Left: read-only info --}}
        <div>
          <div style="font-size:11px;text-transform:uppercase;color:var(--text-muted);font-weight:600;letter-spacing:0.8px;margin-bottom:12px;">
            Account Details
          </div>
          <div class="detail-row">
            <span class="detail-label">Full Name</span>
            <span class="detail-value">{{ $customer->name }}</span>
          </div>
          <div class="detail-row">
            <span class="detail-label">Account Number</span>
            <span class="detail-value" style="font-family:monospace;">{{ $customer->account_number }}</span>
          </div>
          <div class="detail-row">
            <span class="detail-label">Block / Zone</span>
            <span class="detail-value">{{ $customer->block_number ?? '—' }}</span>
          </div>
          <div class="detail-row">
            <span class="detail-label">Meter ID</span>
            <span class="detail-value" style="font-family:monospace;color:var(--accent-teal);">
              {{ $customer->activeMeter?->meter_id ?? '—' }}
            </span>
          </div>
          <div class="detail-row">
            <span class="detail-label">Email</span>
            <span class="detail-value">{{ auth()->user()->email }}</span>
          </div>
          <div class="detail-row">
            <span class="detail-label">Status</span>
            <span class="detail-value">
              <span class="badge-status {{ $customer->status === 'active' ? 'badge-active' : 'badge-inactive' }}">
                {{ ucfirst($customer->status ?? 'active') }}
              </span>
            </span>
          </div>
        </div>

        {{-- Right: editable form --}}
        <div>
          <div style="font-size:11px;text-transform:uppercase;color:var(--text-muted);font-weight:600;letter-spacing:0.8px;margin-bottom:12px;">
            Update Details
          </div>
          <form action="{{ route('customer.profile.update') }}" method="POST">
            @csrf @method('PUT')

            <div class="form-group">
              <label class="form-label">Phone Number</label>
              <input type="text" name="phone" class="form-control"
                     value="{{ old('phone', $customer->phone) }}"
                     placeholder="+676 12345">
              @error('phone') <div class="form-error">{{ $message }}</div> @enderror
            </div>

            <div class="form-group">
              <label class="form-label">Address</label>
              <textarea name="address" class="form-control" rows="4"
                        placeholder="Your service address">{{ old('address', $customer->address) }}</textarea>
              @error('address') <div class="form-error">{{ $message }}</div> @enderror
            </div>

            <div style="display:flex;gap:10px;margin-top:4px;">
              <button type="submit" class="btn btn-primary">
                <span class="material-icons">save</span> Save Changes
              </button>
            </div>
          </form>
        </div>

      </div>
    </div>
  </div>

</div>
@endsection
