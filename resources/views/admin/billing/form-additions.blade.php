{{--
    ═══════════════════════════════════════════════════════════════
    BILLING PARAMETERS — FORM ADDITIONS
    resources/views/admin/billing/create.blade.php (or _form partial)
    ═══════════════════════════════════════════════════════════════

    Replace any static Customer or Meter dropdowns
    in the billing parameters form with searchable versions.

    Typical billing param form fields that benefit from search:
      - customer_id      (could be thousands of entries)
      - meter_id         (cascades off customer)
      - tariff/rate zone (if per-customer override)
--}}

{{-- Customer search --}}
<x-searchable-select
    name="customer_id"
    label="Customer"
    url="{{ route('admin.api.customers.search') }}"
    :selected="old('customer_id', $billingParam->customer_id ?? null)"
    :selected-text="isset($billingParam->customer)
        ? $billingParam->customer->account_number . ' — ' . $billingParam->customer->name
        : ''"
    placeholder="Search by account number or name..."
    required
    class="col-md-6"
/>

{{-- Meter search (cascades off customer) --}}
<x-searchable-select
    name="meter_id"
    label="Meter / Service Point"
    url="{{ route('admin.api.meters.search') }}"
    :selected="old('meter_id', $billingParam->meter_id ?? null)"
    :selected-text="isset($billingParam->meter)
        ? $billingParam->meter->meter_number
        : ''"
    placeholder="Search meter number..."
    extra-params="customer_id"
    class="col-md-6"
/>

{{--
    ════════════════════════════════════════════════════════
    CONTROLLER CLEANUP
    ════════════════════════════════════════════════════════

    In any controller that previously did:
        $customers = Customer::orderBy('name')->get();
        return view('...', compact('customers'));

    You can now REMOVE the $customers query entirely — the
    searchable component fetches data via AJAX from the
    /admin/api/customers/search endpoint.

    The only thing you still need in the controller:
        - Validation: 'customer_id' => 'required|exists:customers,id'
        - For edit forms: pass the existing $model so selected-text
          can pre-populate the selected option label.

    ════════════════════════════════════════════════════════
    JAVASCRIPT DEPENDENCY PATTERN (meter cascades on customer)
    ════════════════════════════════════════════════════════

    The meter dropdown uses extra-params="customer_id".
    When the customer dropdown changes, the meter dropdown
    will include ?customer_id=X in its AJAX request, and
    the /admin/api/meters/search endpoint filters accordingly.

    For a hard reset (clear meter when customer changes),
    add this snippet to your page's <script>:

    $(document).ready(function () {
        // When customer changes, clear and refresh meter select
        $(document).on('change', '[name="customer_id"]', function () {
            var $meter = $('[name="meter_id"]');
            if ($meter.data('select2')) {
                $meter.val(null).trigger('change');
            }
        });
    });
--}}
