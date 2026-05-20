@php
  // Set back route for customer portal
  $backRoute = route('customer.invoices.show', $invoice);
@endphp
@include('admin.invoices.pdf')
