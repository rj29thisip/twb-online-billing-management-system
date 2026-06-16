@extends('layouts.app')
@section('title', isset($customer) ? 'Edit Customer' : 'Add Customer')
@section('breadcrumb', 'Admin / Customers / ' . (isset($customer) ? 'Edit' : 'Add'))
@section('page-title', isset($customer) ? 'Edit Customer' : 'Add Customer')

@section('content')
<div style="max-width:860px;margin:0 auto;">

  <div style="margin-bottom:20px;">
    <a href="{{ route('admin.customers.index') }}" class="btn btn-outline btn-sm">
      <span class="material-icons">arrow_back</span> Back to Customers
    </a>
  </div>

  @if($errors->any())
    <div class="alert alert-error" style="margin-bottom:16px;">
      <span class="material-icons" style="font-size:16px;">error</span>
      {{ $errors->first() }}
  <div class="card">
    <div class="table-card-header">
      <div class="card-header-float" style="background:var(--gradient-dark);">
        <div>
          <h3>{{ isset($customer) ? 'Edit Customer Record' : 'New Customer' }}</h3>
          <p>{{ isset($customer) ? 'Account: ' . $customer->account_number : 'Fill in all required fields below' }}</p>
        </div>
        <span class="material-icons" style="color:var(--accent-blue)">{{ isset($customer) ? 'edit' : 'person_add' }}</span>
      </div>
    </div>
  @endif

  <form
    action="{{ isset($customer) ? route('admin.customers.update', $customer) : route('admin.customers.store') }}"
    method="POST" novalidate>
    @csrf
    @if(isset($customer)) @method('PUT') @endif

    {{-- ══════════════════════════════════════════════════════════════
         SECTION 1 — Personal Details
         ══════════════════════════════════════════════════════════════ --}}
    <div class="card" style="margin-bottom:20px;">
      <div class="table-card-header">
        <div class="card-header-float" style="background:var(--gradient-blue);padding:16px 20px;">
          <div>
            <h3 style="font-size:14px;margin:0;">Personal Details</h3>
            <p style="margin:2px 0 0;font-size:12px;opacity:.7;">Customer identity information</p>
          </div>
          <span class="material-icons">person</span>
        </div>
      </div>
      <div class="card-body" style="padding:20px 24px;">
        {{-- ACCOUNT INFO --}}
        <h4 style="font-size:14px;font-weight:600;text-transform:uppercase;letter-spacing:0.8px;color:var(--text-muted);margin-bottom:16px;display:flex;align-items:center;gap:8px;">
          <span class="material-icons" style="font-size:16px;">badge</span> Account Information
        </h4>

        <div class="form-grid-2">
          <div class="form-group">
            <label class="form-label">Given Name <span style="color:var(--accent-pink)">*</span></label>
            <input type="text" name="given_name" class="form-control @error('given_name') is-invalid @enderror"
                   value="{{ old('given_name', $customer->given_name ?? '') }}"
                   required placeholder="e.g. Havea">
            @error('given_name')<div class="form-error">{{ $message }}</div>@enderror
          </div>
          <div class="form-group">
            <label class="form-label">Family Name <span style="color:var(--accent-pink)">*</span></label>
            <input type="text" name="family_name" class="form-control @error('family_name') is-invalid @enderror"
                   value="{{ old('family_name', $customer->family_name ?? '') }}"
                   required placeholder="e.g. Fonua">
            @error('family_name')<div class="form-error">{{ $message }}</div>@enderror
          </div>
        </div>

        <div class="form-grid-2" style="margin-top:14px;">
          <div class="form-group">
            <label class="form-label">Date of Birth</label>
            <input type="date" name="date_of_birth" class="form-control @error('date_of_birth') is-invalid @enderror"
                   value="{{ old('date_of_birth', isset($customer->date_of_birth) ? $customer->date_of_birth->format('Y-m-d') : '') }}"
                   max="{{ now()->subYears(1)->format('Y-m-d') }}">
            @error('date_of_birth')<div class="form-error">{{ $message }}</div>@enderror
          </div>
          <div class="form-group">
            <label class="form-label">Gender</label>
            <select name="gender" class="form-control @error('gender') is-invalid @enderror">
              <option value="">— Select —</option>
              <option value="male"              {{ old('gender', $customer->gender ?? '') === 'male'              ? 'selected' : '' }}>Male</option>
              <option value="female"            {{ old('gender', $customer->gender ?? '') === 'female'            ? 'selected' : '' }}>Female</option>
              <option value="other"             {{ old('gender', $customer->gender ?? '') === 'other'             ? 'selected' : '' }}>Other</option>
              <option value="prefer_not_to_say" {{ old('gender', $customer->gender ?? '') === 'prefer_not_to_say' ? 'selected' : '' }}>Prefer not to say</option>
            </select>
            @error('gender')<div class="form-error">{{ $message }}</div>@enderror
          </div>
        </div>

      </div>
    </div>

    {{-- ══════════════════════════════════════════════════════════════
         SECTION 2 — Contact Information
         ══════════════════════════════════════════════════════════════ --}}
    <div class="card" style="margin-bottom:20px;">
      <div class="table-card-header">
        <div class="card-header-float" style="background:var(--gradient-dark);padding:16px 20px;">
          <div>
            <h3 style="font-size:14px;margin:0;">Contact Information</h3>
            <p style="margin:2px 0 0;font-size:12px;opacity:.7;">Email and phone details</p>
          </div>
          <span class="material-icons">contact_phone</span>
        </div>
      </div>
      <div class="card-body" style="padding:20px 24px;">

        <div class="form-grid-2">
          <div class="form-group">
            <label class="form-label">Email Address</label>
            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                   value="{{ old('email', $customer->email ?? '') }}"
                   placeholder="e.g. haveahfonua@yahoo.com">
            @if(!isset($customer))
              <div style="font-size:11px;color:var(--text-muted);margin-top:4px;">
                <span class="material-icons" style="font-size:12px;vertical-align:middle;">info</span>
                A portal account will be auto-created and credentials emailed if provided.
              </div>
            @endif
            @error('email')<div class="form-error">{{ $message }}</div>@enderror
          </div>
          <div class="form-group">
            <label class="form-label">Contact Number</label>
            <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror"
                   value="{{ old('phone', $customer->phone ?? '') }}"
                   placeholder="e.g. 06421979">
            @error('phone')<div class="form-error">{{ $message }}</div>@enderror
          </div>
        </div>

      </div>
    </div>

    {{-- ══════════════════════════════════════════════════════════════
         SECTION 3 — Residential Address
         ══════════════════════════════════════════════════════════════ --}}
    <div class="card" style="margin-bottom:20px;">
      <div class="table-card-header">
        <div class="card-header-float" style="background:var(--gradient-dark);padding:16px 20px;">
          <div>
            <h3 style="font-size:14px;margin:0;">Residential Address</h3>
            <p style="margin:2px 0 0;font-size:12px;opacity:.7;">Property location details</p>
          </div>
          <span class="material-icons">home</span>
        </div>
      </div>
      <div class="card-body" style="padding:20px 24px;">

        <div class="form-group">
          <label class="form-label">Address Line</label>
          <input type="text" name="address_line" class="form-control @error('address_line') is-invalid @enderror"
                 value="{{ old('address_line', $customer->address_line ?? '') }}"
                 placeholder="e.g. 42 Tupoulahi Road">
          @error('address_line')<div class="form-error">{{ $message }}</div>@enderror
        </div>

        <div class="form-grid-2" style="margin-top:14px;">
          <div class="form-group">
            <label class="form-label">Suburb / Town</label>
            <input type="text" name="suburb" class="form-control @error('suburb') is-invalid @enderror"
                   value="{{ old('suburb', $customer->suburb ?? '') }}"
                   placeholder="e.g. Ngele'ia">
            @error('suburb')<div class="form-error">{{ $message }}</div>@enderror
          </div>
          <div class="form-group">
            <label class="form-label">Island</label>
            <select name="island" id="islandSelect" class="form-control @error('island') is-invalid @enderror"
                    onchange="updateIslandCode(this)">
              <option value="">— Select Island —</option>
              @foreach($islands as $name => $code)
                <option value="{{ $name }}" data-code="{{ $code }}"
                  {{ old('island', $customer->island ?? '') === $name ? 'selected' : '' }}>
                  {{ $name }} ({{ $code }})
                </option>
              @endforeach
            </select>
            @error('island')<div class="form-error">{{ $message }}</div>@enderror
          </div>
        </div>

        <input type="hidden" name="island_code" id="islandCode"
               value="{{ old('island_code', $customer->island_code ?? '') }}">

      </div>
    </div>

    {{-- ══════════════════════════════════════════════════════════════
         SECTION 4 — Account Details
         ══════════════════════════════════════════════════════════════ --}}
    <div class="card" style="margin-bottom:20px;">
      <div class="table-card-header">
        <div class="card-header-float" style="background:var(--gradient-dark);padding:16px 20px;">
          <div>
            <h3 style="font-size:14px;margin:0;">Account Details</h3>
            <p style="margin:2px 0 0;font-size:12px;opacity:.7;">Billing account and district</p>
          </div>
          <span class="material-icons">receipt_long</span>
        </div>
      </div>
      <div class="card-body" style="padding:20px 24px;">

        <div class="form-grid-2">
          <div class="form-group">
            <label class="form-label">Account Number <span style="color:var(--accent-pink)">*</span></label>
            <input type="text" name="account_number"
                   class="form-control @error('account_number') is-invalid @enderror"
                   value="{{ old('account_number', $customer->account_number ?? '') }}"
                   required placeholder="e.g. 1922101"
                   {{ isset($customer) ? 'readonly style="opacity:0.6"' : '' }}>
            @error('account_number')<div class="form-error">{{ $message }}</div>@enderror
          </div>
          <div class="form-group">
            <label class="form-label">Block</label>
            <input type="text" name="block_number" class="form-control @error('block_number') is-invalid @enderror"
                   value="{{ old('block_number', $customer->block_number ?? '') }}"
                   placeholder="e.g. 19">
            @error('block_number')<div class="form-error">{{ $message }}</div>@enderror
          </div>
        </div>

        <div class="form-grid-2" style="margin-top:14px;">
          <div class="form-group">
            <label class="form-label">District / Area</label>
            <select name="district_id" class="form-control @error('district_id') is-invalid @enderror">
              <option value="">— No District —</option>
              @foreach($districts ?? [] as $dist)
                <option value="{{ $dist->id }}"
                  {{ old('district_id', $customer->district_id ?? $defaultDistrictId ?? '') == $dist->id ? 'selected' : '' }}>
                  {{ $dist->name }}{{ $dist->is_headquarters ? ' (HQ)' : '' }}
                </option>
              @endforeach
            </select>
            @error('district_id')<div class="form-error">{{ $message }}</div>@enderror
          </div>
          <div class="form-group">
            <label class="form-label">Status</label>
            <select name="status" class="form-control @error('status') is-invalid @enderror">
              <option value="active"    {{ old('status', $customer->status ?? 'active') === 'active'    ? 'selected' : '' }}>Active</option>
              <option value="inactive"  {{ old('status', $customer->status ?? '') === 'inactive'         ? 'selected' : '' }}>Inactive</option>
              <option value="suspended" {{ old('status', $customer->status ?? '') === 'suspended'        ? 'selected' : '' }}>Suspended</option>
            </select>
          </div>
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
          <h4 style="font-size:14px;font-weight:600;text-transform:uppercase;letter-spacing:0.8px;color:var(--text-muted);margin-bottom:16px;display:flex;align-items:center;gap:8px;">
            <span class="material-icons" style="font-size:16px;">manage_accounts</span> Portal Access
          </h4>
          <div style="background:rgba(26,115,232,0.06);border:1px solid rgba(26,115,232,0.15);border-radius:8px;padding:14px 16px;margin-bottom:20px;font-size:14px;color:var(--text-secondary);">
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

    {{-- ══════════════════════════════════════════════════════════════
         SECTION 5 — Property Details
         ══════════════════════════════════════════════════════════════ --}}
    <div class="card" style="margin-bottom:20px;">
      <div class="table-card-header">
        <div class="card-header-float" style="background:var(--gradient-dark);padding:16px 20px;">
          <div>
            <h3 style="font-size:14px;margin:0;">Property Details</h3>
            <p style="margin:2px 0 0;font-size:12px;opacity:.7;">Land title and survey information</p>
          </div>
          <span class="material-icons">landscape</span>
        </div>
      </div>
      <div class="card-body" style="padding:20px 24px;">

        <div class="form-grid-2">
          <div class="form-group">
            <label class="form-label">Deed Number</label>
            <input type="text" name="deed_number" class="form-control @error('deed_number') is-invalid @enderror"
                   value="{{ old('deed_number', $customer->deed_number ?? '') }}"
                   placeholder="e.g. 123">
            @error('deed_number')<div class="form-error">{{ $message }}</div>@enderror
          </div>
          <div class="form-group">
            <label class="form-label">Surveyed Date</label>
            <input type="date" name="surveyed_date" class="form-control @error('surveyed_date') is-invalid @enderror"
                   value="{{ old('surveyed_date', isset($customer->surveyed_date) ? $customer->surveyed_date->format('Y-m-d') : '') }}">
            @error('surveyed_date')<div class="form-error">{{ $message }}</div>@enderror
          </div>
        </div>

        <div class="form-group" style="margin-top:14px;">
          <label class="form-label">Property Notes</label>
          <textarea name="property_notes" class="form-control" rows="2"
                    placeholder="Any additional notes about the property...">{{ old('property_notes', $customer->property_notes ?? '') }}</textarea>
        </div>

      </div>
    </div>

    {{-- ══════════════════════════════════════════════════════════════
         SECTION 6 — Smart Meter Details (create only)
         ══════════════════════════════════════════════════════════════ --}}
    @if(!isset($customer))
    <div class="card" style="margin-bottom:20px;">
      <div class="table-card-header">
        <div class="card-header-float" style="background:var(--gradient-dark);padding:16px 20px;">
          <div>
            <h3 style="font-size:14px;margin:0;">Smart Meter Details <span style="font-weight:400;opacity:.6;">(optional)</span></h3>
            <p style="margin:2px 0 0;font-size:12px;opacity:.7;">Register a meter at the same time as the customer</p>
          </div>
          <span class="material-icons">speed</span>
        </div>
      </div>
      <div class="card-body" style="padding:20px 24px;">

        <div class="form-grid-2">
          <div class="form-group">
            <label class="form-label">Meter ID</label>
            <input type="text" name="meter_id" class="form-control @error('meter_id') is-invalid @enderror"
                   value="{{ old('meter_id') }}" placeholder="e.g. 1029304-11">
            @error('meter_id')<div class="form-error">{{ $message }}</div>@enderror
          </div>
          <div class="form-group">
            <label class="form-label">Endpoint ID</label>
            <input type="text" name="endpoint_id" class="form-control @error('endpoint_id') is-invalid @enderror"
                   value="{{ old('endpoint_id') }}" placeholder="e.g. 120206576">
            @error('endpoint_id')<div class="form-error">{{ $message }}</div>@enderror
          </div>
        </div>

        <div class="form-grid-2" style="margin-top:14px;">
          <div class="form-group">
            <label class="form-label">Serial Number</label>
            <input type="text" name="serial_number" class="form-control"
                   value="{{ old('serial_number') }}" placeholder="e.g. SN-2025-00312">
          </div>
          <div class="form-group">
            <label class="form-label">Meter Type</label>
            <select name="meter_type" class="form-control">
              <option value="residential" selected>Residential</option>
              <option value="commercial">Commercial</option>
              <option value="industrial">Industrial</option>
            </select>
          </div>
        </div>

        <div class="form-grid-2" style="margin-top:14px;">
          <div class="form-group">
            <label class="form-label">Brand</label>
            <input type="text" name="brand" class="form-control"
                   value="{{ old('brand') }}" placeholder="e.g. Itron">
          </div>
          <div class="form-group">
            <label class="form-label">Model</label>
            <input type="text" name="model" class="form-control"
                   value="{{ old('model') }}" placeholder="e.g. OpenWay Centron">
          </div>
        </div>

        <div class="form-grid-2" style="margin-top:14px;">
          <div class="form-group">
            <label class="form-label">Manufacturer</label>
            <input type="text" name="manufacturer" class="form-control"
                   value="{{ old('manufacturer') }}" placeholder="e.g. Itron Inc.">
          </div>
          <div class="form-group">
            <label class="form-label">Installation Date</label>
            <input type="date" name="installation_date" class="form-control"
                   value="{{ old('installation_date') }}">
          </div>
        </div>

        <div class="form-group" style="margin-top:14px;">
          <label class="form-label">Meter Notes</label>
          <textarea name="meter_notes" class="form-control" rows="2"
                    placeholder="Any notes about this meter installation...">{{ old('meter_notes') }}</textarea>
        </div>

      </div>
    </div>
    @endif

    {{-- ══════════════════════════════════════════════════════════════
         SECTION 7 — Record Info (edit mode)
         ══════════════════════════════════════════════════════════════ --}}
    @if(isset($customer) && $customer->createdBy)
    <div class="card" style="margin-bottom:20px;">
      <div class="card-body" style="padding:16px 24px;">
        <div style="display:flex;align-items:center;gap:8px;font-size:13px;color:var(--text-secondary);">
          <span class="material-icons" style="font-size:16px;">history</span>
          Record created by
          <strong style="color:var(--text-primary);">{{ $customer->createdBy->name }}</strong>
          on {{ $customer->created_at->format('d M Y, H:i') }}
        </div>
      </div>
    </div>
    @endif

    {{-- Submit --}}
    <div style="display:flex;gap:12px;justify-content:flex-end;margin-top:4px;">
      <a href="{{ route('admin.customers.index') }}" class="btn btn-outline">Cancel</a>
      <button type="submit" class="btn btn-primary">
        <span class="material-icons" style="font-size:16px;">save</span>
        {{ isset($customer) ? 'Update Customer' : 'Create Customer' }}
      </button>
    </div>

  </form>
</div>

@push('scripts')
<script>
function updateIslandCode(sel) {
  var opt = sel.options[sel.selectedIndex];
  document.getElementById('islandCode').value = opt.dataset.code || '';
}
// Set island code on page load for edit mode
(function() {
  var sel = document.getElementById('islandSelect');
  if (sel) updateIslandCode(sel);
})();
</script>
@endpush
@endsection
