@extends('layouts.app')
@section('title', isset($meter) ? 'Edit Meter' : 'Add Meter')
@section('breadcrumb', 'Admin / Meters / ' . (isset($meter) ? 'Edit' : 'Add'))
@section('page-title', isset($meter) ? 'Edit Meter' : 'Add Meter')

@section('content')
<div style="max-width:700px;margin:0 auto;">
  <div style="margin-bottom:20px;">
    <a href="{{ route('admin.meters.index') }}" class="btn btn-outline btn-sm">
      <span class="material-icons">arrow_back</span> Back
    </a>
  </div>

  <div class="card">
    <div class="table-card-header">
      <div class="card-header-float" style="background:var(--gradient-info);">
        <div>
          <h3>{{ isset($meter) ? 'Edit Meter' : 'Register New Meter' }}</h3>
          <p>{{ isset($meter) ? $meter->meter_id : 'Fill in meter device details' }}</p>
        </div>
        <span class="material-icons">speed</span>
      </div>
    </div>
    <div class="card-body" style="padding-top:0;">
      <form
        action="{{ isset($meter) ? route('admin.meters.update', $meter) : route('admin.meters.store') }}"
        method="POST"
      >
        @csrf
        @if(isset($meter)) @method('PUT') @endif

        <div class="form-group">
          <label class="form-label">Customer <span style="color:var(--accent-pink)">*</span></label>
          <select name="customer_id" class="form-control @error('customer_id') is-invalid @enderror"
                  {{ isset($meter) ? 'disabled' : 'required' }}>
            <option value="">— Select Customer —</option>
            @foreach($customers as $c)
              <option value="{{ $c->id }}"
                {{ old('customer_id', $meter->customer_id ?? '') == $c->id ? 'selected' : '' }}>
                {{ $c->account_number }} — {{ $c->name }}
              </option>
            @endforeach
          </select>
          @error('customer_id') <div class="form-error">{{ $message }}</div> @enderror
        </div>

        <div class="form-grid-2">
          <div class="form-group">
            <label class="form-label">Meter ID <span style="color:var(--accent-pink)">*</span></label>
            <input type="text" name="meter_id" class="form-control @error('meter_id') is-invalid @enderror"
                   value="{{ old('meter_id', $meter->meter_id ?? '') }}"
                   placeholder="e.g. I18VA001347"
                   {{ isset($meter) ? 'readonly style=opacity:0.6' : 'required' }}>
            @error('meter_id') <div class="form-error">{{ $message }}</div> @enderror
          </div>
          <div class="form-group">
            <label class="form-label">Endpoint ID</label>
            <input type="text" name="endpoint_id" class="form-control @error('endpoint_id') is-invalid @enderror"
                   value="{{ old('endpoint_id', $meter->endpoint_id ?? '') }}"
                   placeholder="e.g. 120206576"
                   {{ isset($meter) ? 'readonly style=opacity:0.6' : '' }}>
            @error('endpoint_id') <div class="form-error">{{ $message }}</div> @enderror
          </div>
        </div>

        <div class="form-grid-2">
          <div class="form-group">
            <label class="form-label">Meter Type</label>
            <select name="meter_type" class="form-control">
              <option value="residential" {{ old('meter_type', $meter->meter_type ?? 'residential') === 'residential' ? 'selected' : '' }}>Residential</option>
              <option value="commercial"  {{ old('meter_type', $meter->meter_type ?? '') === 'commercial'  ? 'selected' : '' }}>Commercial</option>
              <option value="industrial"  {{ old('meter_type', $meter->meter_type ?? '') === 'industrial'  ? 'selected' : '' }}>Industrial</option>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label">Status</label>
            <select name="status" class="form-control">
              <option value="active"   {{ old('status', $meter->status ?? 'active')   === 'active'   ? 'selected' : '' }}>Active</option>
              <option value="inactive" {{ old('status', $meter->status ?? '') === 'inactive' ? 'selected' : '' }}>Inactive</option>
              <option value="replaced" {{ old('status', $meter->status ?? '') === 'replaced' ? 'selected' : '' }}>Replaced</option>
              <option value="faulty"   {{ old('status', $meter->status ?? '') === 'faulty'   ? 'selected' : '' }}>Faulty</option>
            </select>
          </div>
        </div>

        <div class="form-grid-2">
          <div class="form-group">
            <label class="form-label">Brand</label>
            <input type="text" name="brand" class="form-control"
                   value="{{ old('brand', $meter->brand ?? '') }}" placeholder="e.g. Itron">
          </div>
          <div class="form-group">
            <label class="form-label">Model</label>
            <input type="text" name="model" class="form-control"
                   value="{{ old('model', $meter->model ?? '') }}" placeholder="e.g. CF-51">
          </div>
        </div>

        <div class="form-grid-2">
          <div class="form-group">
            <label class="form-label">Installation Date</label>
            <input type="date" name="installation_date" class="form-control"
                   value="{{ old('installation_date', isset($meter) ? $meter->installation_date?->format('Y-m-d') : '') }}">
          </div>
          <div class="form-group">
            <label class="form-label">Last Maintenance Date</label>
            <input type="date" name="last_maintenance_date" class="form-control"
                   value="{{ old('last_maintenance_date', isset($meter) ? $meter->last_maintenance_date?->format('Y-m-d') : '') }}">
          </div>
        </div>

        <div style="display:flex;gap:12px;justify-content:flex-end;margin-top:8px;">
          <a href="{{ route('admin.meters.index') }}" class="btn btn-outline">Cancel</a>
          <button type="submit" class="btn btn-primary">
            <span class="material-icons">save</span>
            {{ isset($meter) ? 'Update Meter' : 'Register Meter' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection
