{{--
    ╔══════════════════════════════════════════════════════════════════╗
    ║  x-searchable-select — Reusable AJAX Select2 Dropdown          ║
    ║                                                                  ║
    ║  Usage:                                                          ║
    ║  <x-searchable-select                                            ║
    ║      name="customer_id"                                          ║
    ║      label="Customer"                                            ║
    ║      url="{{ route('admin.api.customers.search') }}"            ║
    ║      :selected="old('customer_id', $meter->customer_id ?? null)"║
    ║      :selected-text="$meter->customer?->name ?? ''"             ║
    ║      placeholder="Search by name or account number..."          ║
    ║      required                                                    ║
    ║  />                                                              ║
    ║                                                                  ║
    ║  Optional props:                                                 ║
    ║    dependent-on="other_field_id"  — clears & refetches when     ║
    ║                                     another field changes        ║
    ║    extra-params="customer_id"     — name of field whose value   ║
    ║                                     is sent as extra query param ║
    ║    :required="true"                                              ║
    ║    class="col-md-6"  (wrapper div class)                        ║
    ╚══════════════════════════════════════════════════════════════════╝
--}}

@props([
    'name',
    'label'        => null,
    'url',
    'selected'     => null,
    'selectedText' => '',
    'placeholder'  => 'Type to search...',
    'required'     => false,
    'dependentOn'  => null,   // field ID that, when changed, clears this select
    'extraParams'  => null,   // field name whose value is sent as ?{name}={value}
    'class'        => 'mb-3',
])

@php
    $uid = 'ss_' . str_replace(['[',']','.'], '_', $name) . '_' . uniqid();
@endphp

<div class="{{ $class }}">
    @if ($label)
        <label for="{{ $uid }}" class="form-label">
            {{ $label }}
            @if ($required) <span class="text-danger">*</span> @endif
        </label>
    @endif

    <select
        id="{{ $uid }}"
        name="{{ $name }}"
        class="form-select select2-ajax @error($name) is-invalid @enderror"
        data-url="{{ $url }}"
        data-placeholder="{{ $placeholder }}"
        data-dependent-on="{{ $dependentOn }}"
        data-extra-params="{{ $extraParams }}"
        {{ $required ? 'required' : '' }}
        style="width:100%"
    >
        {{-- Pre-populate selected value so form re-fills on validation error --}}
        @if ($selected)
            <option value="{{ $selected }}" selected>{{ $selectedText ?: $selected }}</option>
        @else
            <option value=""></option>
        @endif
    </select>

    @error($name)
        <div class="invalid-feedback d-block">{{ $message }}</div>
    @enderror
</div>
