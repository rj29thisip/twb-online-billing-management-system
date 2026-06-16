{{--
    ═══════════════════════════════════════════════════════════════
    METER REGISTRY — FORM ADDITIONS
    resources/views/admin/meters/create.blade.php  (and edit.blade.php)
    ═══════════════════════════════════════════════════════════════

    Replace your static Customer <select> with x-searchable-select.

    BEFORE (static list — breaks at scale):
    ────────────────────────────────────────
    <select name="customer_id" class="form-select">
        @foreach ($customers as $c)
            <option value="{{ $c->id }}">{{ $c->account_number }} — {{ $c->name }}</option>
        @endforeach
    </select>

    AFTER:
    ──────
--}}

{{-- Customer search --}}
<x-searchable-select
    name="customer_id"
    label="Customer"
    url="{{ route('admin.api.customers.search') }}"
    :selected="old('customer_id', $meter->customer_id ?? null)"
    :selected-text="isset($meter->customer)
        ? $meter->customer->account_number . ' — ' . $meter->customer->name
        : ''"
    placeholder="Search by account number or customer name..."
    required
    class="col-md-6"
/>

{{--
    In the controller, also REMOVE:
      $customers = Customer::orderBy('name')->get();

    And replace with nothing — the component fetches via AJAX.
    The customer_id validation stays: 'customer_id' => 'required|exists:customers,id'
--}}
