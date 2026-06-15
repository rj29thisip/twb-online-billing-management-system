{{--
    ╔══════════════════════════════════════════════════════════════════╗
    ║  x-searchable-select-init                                       ║
    ║                                                                  ║
    ║  Include ONCE in your layout, just before </body>.              ║
    ║  Loads Select2 from CDN and wires up all .select2-ajax elements.║
    ║                                                                  ║
    ║  Usage in layout:                                                ║
    ║    <x-searchable-select-init />                                  ║
    ╚══════════════════════════════════════════════════════════════════╝
--}}

{{-- Select2 CSS --}}
<link rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css"/>
<link rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/select2-bootstrap-5-theme/1.3.0/select2-bootstrap-5-theme.min.css"/>

{{-- Select2 JS (requires jQuery — assumes Bootstrap bundle already loaded) --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>

<script>
(function ($) {
    'use strict';

    /**
     * Initialize all .select2-ajax elements on the page.
     * Safe to call multiple times (idempotent).
     */
    function initSearchableSelects() {
        $('.select2-ajax').each(function () {
            var $el = $(this);

            // Don't re-init
            if ($el.data('select2')) return;

            var ajaxUrl     = $el.data('url');
            var placeholder = $el.data('placeholder') || 'Type to search...';
            var dependentOn = $el.data('dependent-on');   // ID of another field
            var extraParams = $el.data('extra-params');   // name of another field to send

            $el.select2({
                theme: 'bootstrap-5',
                placeholder: placeholder,
                allowClear: true,
                minimumInputLength: 0,   // shows results even on empty (first 20)
                ajax: {
                    url: ajaxUrl,
                    dataType: 'json',
                    delay: 250,
                    data: function (params) {
                        var extra = {};
                        if (extraParams) {
                            var $dep = $('#' + extraParams) .length
                                     ? $('#' + extraParams)
                                     : $('[name="' + extraParams + '"]');
                            if ($dep.length) {
                                extra[extraParams] = $dep.val();
                            }
                        }
                        return Object.assign({ q: params.term || '' }, extra);
                    },
                    processResults: function (data) {
                        return { results: data.results };
                    },
                    cache: true,
                },
            });

            // When the field this depends on changes, clear and reset this select
            if (dependentOn) {
                $('#' + dependentOn + ', [name="' + dependentOn + '"]')
                    .on('change', function () {
                        $el.val(null).trigger('change');
                    });
            }
        });
    }

    // Init on DOM ready
    $(document).ready(function () {
        initSearchableSelects();
    });

    // Re-init if content is loaded dynamically (modals, Livewire, etc.)
    $(document).on('shown.bs.modal', function () {
        initSearchableSelects();
    });

    // Expose globally for manual re-init if needed
    window.initSearchableSelects = initSearchableSelects;

})(window.jQuery || window.$);
</script>

<style>
/* Ensure Select2 dropdown appears above Bootstrap modals */
.select2-container { z-index: 9999; }

/* Align height with Bootstrap form-control */
.select2-bootstrap-5-theme .select2-selection {
    min-height: calc(1.5em + .75rem + 2px);
}

/* Invalid feedback styling for Select2 */
.is-invalid + .select2-container .select2-selection {
    border-color: #dc3545 !important;
}
</style>
