{{--
    resources/views/admin/customers/index.blade.php — ADDITIONS ONLY
    ─────────────────────────────────────────────────────────────────
    Merge these snippets into your existing customer index view.
    Look for the comments marking WHERE to insert each block.
--}}


{{-- ① FILTER BAR — Add district filter for HQ/Admin users.
     Place inside your existing search/filter form. --}}
@if (auth()->user()->isAdministrator() || auth()->user()->isHeadquarters())
<div class="col-md-3">
    <select name="district_id" class="form-select form-select-sm">
        <option value="">All Districts</option>
        @foreach ($districts as $district)
            <option value="{{ $district->id }}"
                {{ request('district_id') == $district->id ? 'selected' : '' }}>
                {{ $district->name }}
            </option>
        @endforeach
    </select>
</div>
@endif


{{-- ② TABLE COLUMN HEADER — Add after existing headers.
     Place in <thead> row. --}}
<th>District</th>


{{-- ③ TABLE COLUMN VALUE — Add inside @foreach ($customers as $customer).
     Place in <tbody> row alongside other <td> cells. --}}
<td>
    @if ($customer->district)
        <span class="badge bg-light text-dark border">{{ $customer->district->name }}</span>
    @else
        <span class="text-muted small">—</span>
    @endif
</td>


{{-- ④ RESEND CREDENTIALS BUTTON — Add inside the action buttons column
     for each customer row. --}}
@if ($customer->email)
<form method="POST"
      action="{{ route('admin.customers.resend-credentials', $customer) }}"
      class="d-inline"
      onsubmit="return confirm('Resend portal credentials to {{ $customer->email }}?')">
    @csrf
    <button type="submit"
            class="btn btn-sm btn-outline-info"
            title="Resend portal credentials">
        <i class="fas fa-envelope"></i>
    </button>
</form>
@endif
