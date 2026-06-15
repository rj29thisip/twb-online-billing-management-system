{{-- resources/views/admin/customers/_form.blade.php --}}
{{-- Updated: replaces static district list with x-searchable-select --}}

@include('admin.partials.alerts')

<form method="POST" action="{{ $action }}">
    @csrf
    @if ($method !== 'POST') @method($method) @endif

    <div class="row g-3">

        <div class="col-md-6">
            <label class="form-label">Account Number <span class="text-danger">*</span></label>
            <input type="text" name="account_number"
                   class="form-control @error('account_number') is-invalid @enderror"
                   value="{{ old('account_number', $customer->account_number ?? '') }}" required>
            @error('account_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="col-md-6">
            <label class="form-label">Full Name <span class="text-danger">*</span></label>
            <input type="text" name="name"
                   class="form-control @error('name') is-invalid @enderror"
                   value="{{ old('name', $customer->name ?? '') }}" required>
            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="col-md-6">
            <label class="form-label">Email Address</label>
            <input type="email" name="email"
                   class="form-control @error('email') is-invalid @enderror"
                   value="{{ old('email', $customer->email ?? '') }}"
                   placeholder="Leave blank if no portal access needed">
            <div class="form-text">
                <i class="fas fa-info-circle text-info"></i>
                If provided, portal login credentials will be emailed automatically on creation.
            </div>
            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="col-md-6">
            <label class="form-label">Phone</label>
            <input type="text" name="phone"
                   class="form-control"
                   value="{{ old('phone', $customer->phone ?? '') }}">
        </div>

        <div class="col-12">
            <label class="form-label">Address</label>
            <textarea name="address" class="form-control" rows="2">{{ old('address', $customer->address ?? '') }}</textarea>
        </div>

        {{-- District: searchable dropdown --}}
        <x-searchable-select
            name="district_id"
            label="District / Area"
            url="{{ route('admin.api.districts.search') }}"
            :selected="old('district_id', $customer->district_id ?? $defaultDistrictId ?? null)"
            :selected-text="isset($customer->district)
                ? $customer->district->name
                : (isset($defaultDistrict) ? $defaultDistrict->name : '')"
            placeholder="Search district or area..."
            class="col-md-6"
        />

        {{-- ... add other existing customer fields below (tariff type, meter number, etc.) --}}

    </div>

    <hr class="my-4">
    <div class="d-flex gap-2">
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-save me-1"></i> Save Customer
        </button>
        <a href="{{ route('admin.customers.index') }}" class="btn btn-outline-secondary">Cancel</a>
    </div>
</form>
