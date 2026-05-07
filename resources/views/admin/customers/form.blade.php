{{-- resources/views/admin/customers/form.blade.php --}}
{{-- Used for both create and edit. $customer is null for create. --}}
@extends('layouts.app')
@section('title', isset($customer) ? 'Edit Customer' : 'Add Customer')
@section('breadcrumb', 'Admin / Customers / ' . (isset($customer) ? 'Edit' : 'Add'))
@section('page-title', isset($customer) ? 'Edit Customer' : 'Add Customer')

@section('content')

<div style="max-width:800px;margin:0 auto;">

  <div style="margin-bottom:24px;">
    <a href="{{ route('admin.customers.index') }}" class="btn btn-outline btn-sm">
      <span class="material-icons">arrow_back</span> Back to Customers
    </a>
  </div>

  <div class="card">
    <div class="table-card-header">
      <div class="card-header-float" style="background:var(--gradient-blue);">
        <div>
          <h3>{{ isset($customer) ? 'Edit Customer Record' : 'New Customer' }}</h3>
          <p>{{ isset($customer) ? 'Account: ' . $customer->account_number : 'Fill in all required fields below' }}</p>
        </div>
        <span class="material-icons">{{ isset($customer) ? 'edit' : 'person_add' }}</span>
      </div>
    </div>

    <div class="card-body" style="padding-top:0;">
      <form
        action="{{ isset($customer) ? route('admin.customers.update', $customer) : route('admin.customers.store') }}"
        method="POST"
        novalidate
      >
        @csrf
        @if(isset($customer)) @method('PUT') @endif

        {{-- ACCOUNT INFO --}}
        <h4 style="font-size:13px;font-weight:600;text-transform:uppercase;letter-spacing:0.8px;color:var(--text-muted);margin-bottom:16px;display:flex;align-items:center;gap:8px;">
          <span class="material-icons" style="font-size:16px;">badge</span> Account Information
        </h4>

        <div class="form-grid-2">
          <div class="form-group">
            <label class="form-label">Full Name <span style="color:var(--accent-pink)">*</span></label>
            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                   value="{{ old('name', $customer->name ?? '') }}" required placeholder="e.g. Sione Vaka">
            @error('name') <div class="form-error">{{ $message }}</div> @enderror
          </div>

          <div class="form-group">
            <label class="form-label">Account Number <span style="color:var(--accent-pink)">*</span></label>
            <input type="text" name="account_number"
                   class="form-control @error('account_number') is-invalid @enderror"
                   value="{{ old('account_number', $customer->account_number ?? '') }}"
                   required placeholder="e.g. 1911800"
                   {{ isset($customer) ? 'readonly style=opacity:0.6' : '' }}>
            @error('account_number') <div class="form-error">{{ $message }}</div> @enderror
          </div>
        </div>

        <div class="form-grid-2">
          <div class="form-group">
            <label class="form-label">Email Address</label>
            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                   value="{{ old('email', $customer->email ?? '') }}" placeholder="customer@example.com">
            @error('email') <div class="form-error">{{ $message }}</div> @enderror
          </div>

          <div class="form-group">
            <label class="form-label">Phone Number</label>
            <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror"
                   value="{{ old('phone', $customer->phone ?? '') }}" placeholder="e.g. +676 12345">
            @error('phone') <div class="form-error">{{ $message }}</div> @enderror
          </div>
        </div>

        <div class="form-grid-2">
          <div class="form-group">
            <label class="form-label">Block Number</label>
            <input type="text" name="block_number" class="form-control @error('block_number') is-invalid @enderror"
                   value="{{ old('block_number', $customer->block_number ?? '') }}" placeholder="e.g. BLK 19">
            @error('block_number') <div class="form-error">{{ $message }}</div> @enderror
          </div>

          <div class="form-group">
            <label class="form-label">Status</label>
            <select name="status" class="form-control @error('status') is-invalid @enderror">
              <option value="active"    {{ old('status', $customer->status ?? 'active') === 'active'    ? 'selected' : '' }}>Active</option>
              <option value="inactive"  {{ old('status', $customer->status ?? '') === 'inactive'   ? 'selected' : '' }}>Inactive</option>
              <option value="suspended" {{ old('status', $customer->status ?? '') === 'suspended'  ? 'selected' : '' }}>Suspended</option>
            </select>
            @error('status') <div class="form-error">{{ $message }}</div> @enderror
          </div>
        </div>

        <div class="form-group">
          <label class="form-label">Address</label>
          <textarea name="address" class="form-control @error('address') is-invalid @enderror"
                    rows="3" placeholder="Full property address">{{ old('address', $customer->address ?? '') }}</textarea>
          @error('address') <div class="form-error">{{ $message }}</div> @enderror
        </div>

        @if(!isset($customer))
          {{-- METER (only on create) --}}
          <hr style="border:none;border-top:1px solid var(--border);margin:24px 0;">
          <h4 style="font-size:13px;font-weight:600;text-transform:uppercase;letter-spacing:0.8px;color:var(--text-muted);margin-bottom:16px;display:flex;align-items:center;gap:8px;">
            <span class="material-icons" style="font-size:16px;">speed</span> Meter Assignment (optional)
          </h4>
          <div class="form-grid-2">
            <div class="form-group">
              <label class="form-label">Meter ID</label>
              <input type="text" name="meter_id" class="form-control @error('meter_id') is-invalid @enderror"
                     value="{{ old('meter_id') }}" placeholder="e.g. I18VA001347">
              @error('meter_id') <div class="form-error">{{ $message }}</div> @enderror
            </div>
            <div class="form-group">
              <label class="form-label">Endpoint ID</label>
              <input type="text" name="endpoint_id" class="form-control @error('endpoint_id') is-invalid @enderror"
                     value="{{ old('endpoint_id') }}" placeholder="e.g. 120206576">
              @error('endpoint_id') <div class="form-error">{{ $message }}</div> @enderror
            </div>
          </div>
          <div class="form-grid-2">
            <div class="form-group">
              <label class="form-label">Meter Type</label>
              <select name="meter_type" class="form-control">
                <option value="residential">Residential</option>
                <option value="commercial">Commercial</option>
                <option value="industrial">Industrial</option>
              </select>
            </div>
            <div class="form-group">
              <label class="form-label">Installation Date</label>
              <input type="date" name="installation_date" class="form-control" value="{{ old('installation_date') }}">
            </div>
          </div>

          {{-- PORTAL ACCESS --}}
          <hr style="border:none;border-top:1px solid var(--border);margin:24px 0;">
          <h4 style="font-size:13px;font-weight:600;text-transform:uppercase;letter-spacing:0.8px;color:var(--text-muted);margin-bottom:16px;display:flex;align-items:center;gap:8px;">
            <span class="material-icons" style="font-size:16px;">manage_accounts</span> Portal Access
          </h4>
          <div style="background:rgba(26,115,232,0.06);border:1px solid rgba(26,115,232,0.15);border-radius:8px;padding:14px 16px;margin-bottom:20px;font-size:13px;color:var(--text-secondary);">
            <span class="material-icons" style="font-size:16px;vertical-align:middle;margin-right:6px;color:var(--accent-blue);">info</span>
            If an email is provided, a customer portal account will be auto-created and login credentials emailed to the customer.
          </div>
        @endif

        <div style="display:flex;gap:12px;justify-content:flex-end;margin-top:8px;">
          <a href="{{ route('admin.customers.index') }}" class="btn btn-outline">Cancel</a>
          <button type="submit" class="btn btn-primary">
            <span class="material-icons">save</span>
            {{ isset($customer) ? 'Update Customer' : 'Create Customer' }}
          </button>
        </div>

      </form>
    </div>
  </div>
</div>

@endsection
