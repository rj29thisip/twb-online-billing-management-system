{{-- resources/views/admin/staff/_form.blade.php --}}
{{-- Updated: district uses x-searchable-select --}}

@include('admin.partials.alerts')

<form method="POST" action="{{ $action }}">
    @csrf
    @if ($method !== 'POST') @method($method) @endif

    <div class="row g-3">

        <div class="col-md-6">
            <label class="form-label">Full Name <span class="text-danger">*</span></label>
            <input type="text" name="name"
                   class="form-control @error('name') is-invalid @enderror"
                   value="{{ old('name', $staff->name ?? '') }}" required>
            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="col-md-6">
            <label class="form-label">Email Address <span class="text-danger">*</span></label>
            <input type="email" name="email"
                   class="form-control @error('email') is-invalid @enderror"
                   value="{{ old('email', $staff->email ?? '') }}" required>
            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        {{-- Role --}}
        <div class="col-md-6">
            <label class="form-label">Role <span class="text-danger">*</span></label>
            <select name="role" class="form-select @error('role') is-invalid @enderror" id="roleSelect" required>
                <option value="">-- Select Role --</option>
                @foreach ($roles as $value => $label)
                    <option value="{{ $value }}"
                        {{ old('role', $staff->role ?? '') === $value ? 'selected' : '' }}>
                        {{ $label }}
                    </option>
                @endforeach
            </select>
            <div id="roleHint" class="form-text text-muted mt-1"></div>
            @error('role')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        {{-- District: searchable --}}
        <x-searchable-select
            name="district_id"
            label="District / Area"
            url="{{ route('admin.api.districts.search') }}"
            :selected="old('district_id', $staff->district_id ?? null)"
            :selected-text="isset($staff->district)
                ? $staff->district->name . ($staff->district->is_headquarters ? ' (HQ)' : '')
                : ''"
            placeholder="Search district..."
            class="col-md-6"
        />

        <div class="col-12">
            <div class="form-text text-muted">
                <i class="fas fa-info-circle me-1"></i>
                HQ staff see all customers. Area staff see only their district + unassigned customers.
            </div>
        </div>

        {{-- Active toggle --}}
        <div class="col-12">
            <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox"
                       name="is_active" id="is_active" value="1"
                       {{ old('is_active', $staff->is_active ?? true) ? 'checked' : '' }}>
                <label class="form-check-label" for="is_active">Account Active</label>
            </div>
        </div>

        @isset($staff->id)
        <div class="col-12">
            <div class="alert alert-light border mb-0">
                <i class="fas fa-key me-1 text-warning"></i>
                To reset this staff member's password, use the
                <strong>Reset Password</strong> button on the staff list.
            </div>
        </div>
        @endisset

    </div>

    <hr class="my-4">
    <div class="d-flex gap-2">
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-save me-1"></i> Save Staff
        </button>
        <a href="{{ route('admin.staff.index') }}" class="btn btn-outline-secondary">Cancel</a>
    </div>
</form>

<script>
const roleHints = {
    administrator:    'Full access to all modules.',
    cashier:          'Customers + Payments only.',
    account_employee: 'Customers, Meter Readings, and Invoices.',
    ceo:              'Dashboard view only.',
    accountant:       'Dashboard view only.',
    manager:          'Dashboard view only.',
};

document.getElementById('roleSelect').addEventListener('change', function () {
    document.getElementById('roleHint').textContent = roleHints[this.value] || '';
});
document.getElementById('roleSelect').dispatchEvent(new Event('change'));
</script>
