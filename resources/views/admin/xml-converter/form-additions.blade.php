{{--
    ═══════════════════════════════════════════════════════════════
    XML CONVERTER — PER-CUSTOMER MANUAL FORM
    resources/views/admin/xml-converter/manual.blade.php  (or similar)
    ═══════════════════════════════════════════════════════════════

    Full example of the manual XML-converter form with
    searchable customer select + meter select that filters
    based on the chosen customer.
--}}

@extends('admin.layouts.app')
@section('title', 'Manual XML Converter')

@section('content')
<div class="d-flex align-items-center mb-4 gap-2">
    <h4 class="mb-0">Manual XML / Smart Meter Import</h4>
    <span class="text-muted">— per customer</span>
</div>

@include('admin.partials.alerts')

<div class="card shadow-sm">
    <div class="card-body">
        <form method="POST"
              action="{{ route('admin.xml-converter.manual.store') }}"
              enctype="multipart/form-data">
            @csrf

            <div class="row g-3">

                {{-- ① Customer search --}}
                <x-searchable-select
                    name="customer_id"
                    label="Customer"
                    url="{{ route('admin.api.customers.search') }}"
                    :selected="old('customer_id')"
                    placeholder="Search by account number or name..."
                    required
                    class="col-md-6"
                />

                {{--
                    ② Meter search — filters by selected customer.
                    Uses:
                      dependent-on="ss_customer_id_..."  (auto-cleared when customer changes)
                      extra-params="customer_id"          (sends ?customer_id=X to the API)

                    NOTE: dependent-on must match the rendered <select> id,
                    which is "ss_customer_id_XXXX". Because this is dynamic,
                    we rely on extra-params only and the API handles the filter.
                    The meter list will repopulate after the customer is chosen.
                --}}
                <x-searchable-select
                    name="meter_id"
                    label="Meter"
                    url="{{ route('admin.api.meters.search') }}"
                    :selected="old('meter_id')"
                    placeholder="Select customer first, then search meter..."
                    extra-params="customer_id"
                    class="col-md-6"
                />

                {{-- ③ XML File upload --}}
                <div class="col-md-6">
                    <label class="form-label">
                        XML File <span class="text-danger">*</span>
                        <small class="text-muted">(Itron OpenWay AMR format)</small>
                    </label>
                    <input type="file"
                           name="xml_file"
                           accept=".xml"
                           class="form-control @error('xml_file') is-invalid @enderror"
                           required>
                    @error('xml_file')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                {{-- ④ Reading date override --}}
                <div class="col-md-3">
                    <label class="form-label">Reading Date Override
                        <small class="text-muted">(optional)</small>
                    </label>
                    <input type="date"
                           name="reading_date"
                           class="form-control"
                           value="{{ old('reading_date') }}">
                    <div class="form-text">Leave blank to use date from XML file.</div>
                </div>

                {{-- ⑤ Dry run toggle --}}
                <div class="col-md-3 d-flex align-items-end pb-1">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox"
                               id="dry_run" name="dry_run" value="1"
                               {{ old('dry_run') ? 'checked' : '' }}>
                        <label class="form-check-label" for="dry_run">
                            <strong>Dry Run</strong>
                            <small class="text-muted d-block">Preview without saving</small>
                        </label>
                    </div>
                </div>

            </div>

            <hr class="my-4">
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-file-import me-1"></i> Import XML
                </button>
                <a href="{{ route('admin.xml-converter.index') }}"
                   class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
