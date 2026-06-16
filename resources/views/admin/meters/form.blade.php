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
          <select
            name="customer_id"
            id="customerSelect"
            class="form-control select2-customer @error('customer_id') is-invalid @enderror"
            {{ isset($meter) ? 'disabled' : 'required' }}
            style="width:100%">
            @if(isset($meter) && $meter->customer)
              <option value="{{ $meter->customer->id }}" selected>
                {{ $meter->customer->account_number }} — {{ $meter->customer->name }}
              </option>
            @elseif(old('customer_id'))
              <option value="{{ old('customer_id') }}" selected>{{ old('customer_id') }}</option>
            @else
              <option value=""></option>
            @endif
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
@push('scripts')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2-bootstrap-5-theme/1.3.0/select2-bootstrap-5-theme.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<style>
  .select2-container { z-index: 9999; }
  .select2-container .select2-selection--single {
    height: auto !important;
    min-height: 42px;
    background: var(--surface-2, rgba(255,255,255,0.06)) !important;
    border: 1px solid var(--border, rgba(255,255,255,0.12)) !important;
    border-radius: 8px !important;
  }
  .select2-container .select2-selection--single .select2-selection__rendered {
    color: var(--text-primary, #fff) !important;
    line-height: 40px !important;
    padding-left: 12px !important;
  }
  .select2-container .select2-selection--single .select2-selection__arrow {
    height: 42px !important;
  }
  .select2-dropdown {
    background: var(--surface, #1e2a3a) !important;
    border: 1px solid var(--border, rgba(255,255,255,0.12)) !important;
    border-radius: 8px !important;
  }
  .select2-container--default .select2-results__option {
    color: var(--text-primary, #fff) !important;
    padding: 8px 12px !important;
    font-size: 13px !important;
  }
  .select2-container--default .select2-results__option--highlighted {
    background: var(--accent-blue, #1a73e8) !important;
  }
  .select2-container--default .select2-search--dropdown .select2-search__field {
    background: var(--surface-2, rgba(255,255,255,0.06)) !important;
    border: 1px solid var(--border, rgba(255,255,255,0.15)) !important;
    color: var(--text-primary, #fff) !important;
    border-radius: 6px !important;
    padding: 6px 10px !important;
  }
</style>
<script>
$(document).ready(function() {
  $('#customerSelect').select2({
    placeholder: 'Search by account number or name...',
    allowClear: true,
    minimumInputLength: 0,
    ajax: {
      url: '{{ route("admin.api.customers.search") }}',
      dataType: 'json',
      delay: 200,
      data: function(params) { return { q: params.term || '' }; },
      processResults: function(data) { return { results: data.results }; },
      cache: true
    }
  });
});
</script>
@endpush
@endsection