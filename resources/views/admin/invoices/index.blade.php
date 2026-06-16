{{-- resources/views/admin/invoices/index.blade.php --}}
@extends('layouts.app')
@section('title', 'Invoices')
@section('breadcrumb', 'Admin / Billing / Invoices')
@section('page-title', 'Invoices')

@section('content')

<div class="section-header">
  <div>
    <h2>Invoice Management</h2>
    <p>All customer invoices and billing records</p>
  </div>
  <div style="display:flex;gap:10px;">
    <a href="{{ route('admin.billing.check') }}" class="btn btn-primary">
      <span class="material-icons">add_circle</span> Create Invoices
    </a>
  </div>
</div>

<div class="card">
  {{-- FILTER BAR --}}
  <form method="GET" class="filter-bar">
    <input type="text" name="search" class="form-control" placeholder="Search customer / invoice #..."
           value="{{ request('search') }}">
    <select name="status" class="form-control">
      <option value="">All Statuses</option>
      <option value="draft"          {{ request('status') === 'draft'          ? 'selected' : '' }}>Draft</option>
      <option value="issued"         {{ request('status') === 'issued'         ? 'selected' : '' }}>Issued</option>
      <option value="paid"           {{ request('status') === 'paid'           ? 'selected' : '' }}>Paid</option>
      <option value="partially_paid" {{ request('status') === 'partially_paid' ? 'selected' : '' }}>Partially Paid</option>
      <option value="overdue"        {{ request('status') === 'overdue'        ? 'selected' : '' }}>Overdue</option>
      <option value="cancelled"      {{ request('status') === 'cancelled'      ? 'selected' : '' }}>Cancelled</option>
    </select>
    <input type="month" name="period" class="form-control" value="{{ request('period') }}">
    <button type="submit" class="btn btn-outline btn-sm">
      <span class="material-icons">filter_list</span> Filter
    </button>
    @if(request()->hasAny(['search','status','period']))
      <a href="{{ route('admin.invoices.index') }}" class="btn btn-outline btn-sm">
        <span class="material-icons">clear</span> Clear
      </a>
    @endif
  </form>

  {{-- TABLE --}}
  <div class="table-wrapper">
    <table>
      <thead>
        <tr>
          <th>Invoice #</th>
          <th>Customer</th>
          <th>Period</th>
          <th>Usage (m³)</th>
          <th>Total</th>
          <th>Balance Due</th>
          <th>Status</th>
          <th>Due Date</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        @forelse($invoices as $inv)
          <tr>
            <td class="td-primary" style="font-family:monospace;">{{ $inv->invoice_number }}</td>
            <td>
              <div class="user-cell">
                <div class="u-avatar" style="background:var(--gradient-blue);color:#fff;font-size:11px;">
                  {{ substr($inv->customer->name, 0, 2) }}
                </div>
                <div>
                  <div class="u-name">{{ $inv->customer->name }}</div>
                  <div class="u-sub">{{ $inv->customer->account_number }}</div>
                </div>
              </div>
            </td>
            <td>{{ $inv->billing_period_start->format('d M') }} – {{ $inv->billing_period_end->format('d M Y') }}</td>
            <td>{{ number_format($inv->total_usage, 2) }}</td>
            <td class="td-primary">T$ {{ number_format($inv->total_amount, 2) }}</td>
            <td>
              <span style="color: {{ $inv->balance_due > 0 ? 'var(--accent-orange)' : 'var(--accent-green)' }}">
                T$ {{ number_format($inv->balance_due, 2) }}
              </span>
            </td>
            <td>
              @php
                $badgeClass = match($inv->status) {
                  'paid'           => 'badge-paid',
                  'issued'         => 'badge-issued',
                  'overdue'        => 'badge-overdue',
                  'partially_paid' => 'badge-partially',
                  'cancelled'      => 'badge-cancelled',
                  default          => 'badge-draft',
                };
              @endphp
              <span class="badge-status {{ $badgeClass }}">
                {{ ucfirst(str_replace('_',' ',$inv->status)) }}
              </span>
            </td>
            <td>
              <span style="{{ $inv->isOverdue() ? 'color:var(--accent-orange)' : '' }}">
                {{ $inv->due_date?->format('d M Y') ?? '—' }}
              </span>
            </td>
            <td>
              <div style="display:flex;gap:4px;">
                <a href="{{ route('admin.invoices.show', $inv) }}" class="action-btn" title="View">
                  <span class="material-icons">visibility</span>
                </a>
                <a href="{{ route('admin.invoices.pdf', $inv) }}" class="action-btn" title="PDF" target="_blank">
                  <span class="material-icons">picture_as_pdf</span>
                </a>
                @if(in_array($inv->status, ['issued','partially_paid','overdue']))
                  <button class="action-btn" title="Record Payment"
                          onclick="openPaymentModal({{ $inv->id }}, '{{ $inv->invoice_number }}', {{ $inv->balance_due }})">
                    <span class="material-icons">payments</span>
                  </button>
                @endif
              </div>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="9">
              <div class="empty-state">
                <span class="material-icons">receipt_long</span>
                <h3>No invoices found</h3>
                <p>Try adjusting your filters or <a href="{{ route('admin.billing.check') }}" style="color:var(--accent-blue)">create new invoices</a>.</p>
              </div>
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  {{-- PAGINATION --}}
  <div class="pagination">
    <div class="pagination-info">
      Showing {{ $invoices->firstItem() }}–{{ $invoices->lastItem() }} of {{ $invoices->total() }} invoices
    </div>
    <div class="pagination-btns">
      @if($invoices->onFirstPage())
        <span class="pg-btn" style="opacity:0.3;"><span class="material-icons">chevron_left</span></span>
      @else
        <a href="{{ $invoices->previousPageUrl() }}" class="pg-btn"><span class="material-icons">chevron_left</span></a>
      @endif

      @foreach($invoices->getUrlRange(max(1, $invoices->currentPage()-2), min($invoices->lastPage(), $invoices->currentPage()+2)) as $page => $url)
        <a href="{{ $url }}" class="pg-btn {{ $page === $invoices->currentPage() ? 'active' : '' }}">{{ $page }}</a>
      @endforeach

      @if($invoices->hasMorePages())
        <a href="{{ $invoices->nextPageUrl() }}" class="pg-btn"><span class="material-icons">chevron_right</span></a>
      @else
        <span class="pg-btn" style="opacity:0.3;"><span class="material-icons">chevron_right</span></span>
      @endif
    </div>
  </div>
</div>

{{-- ── PAYMENT MODAL ────────────────────────────────────────── --}}
<div class="modal-overlay" id="paymentModal">
  <div class="modal">
    <div class="modal-header">
      <h3 class="modal-title">Record Payment</h3>
      <button class="modal-close" onclick="closePaymentModal()">
        <span class="material-icons">close</span>
      </button>
    </div>
    <form action="{{ route('admin.payments.store') }}" method="POST">
      @csrf
      <input type="hidden" name="invoice_id" id="modal-invoice-id">
      <div class="modal-body">
        <div style="background:rgba(26,115,232,0.08);border:1px solid rgba(26,115,232,0.2);border-radius:8px;padding:12px 16px;margin-bottom:20px;">
          <div style="font-size:12px;color:var(--text-muted);">Invoice</div>
          <div style="font-weight:600;" id="modal-invoice-num">—</div>
          <div style="font-size:13px;color:var(--accent-orange);margin-top:2px;">
            Balance: T$ <span id="modal-balance">0.00</span>
          </div>
        </div>

        <div class="form-grid-2">
          <div class="form-group">
            <label class="form-label">Amount (T$)</label>
            <input type="number" name="amount" id="modal-amount" class="form-control"
                   step="0.01" min="0.01" required>
          </div>
          <div class="form-group">
            <label class="form-label">Payment Date</label>
            <input type="date" name="payment_date" class="form-control"
                   value="{{ now()->format('Y-m-d') }}" required>
          </div>
        </div>

        <div class="form-group">
          <label class="form-label">Payment Method</label>
          <select name="payment_method" class="form-control" required>
            <option value="cash">Cash</option>
            <option value="bank_transfer">Bank Transfer</option>
            <option value="online">Online</option>
            <option value="mobile_money">Mobile Money</option>
          </select>
        </div>

        <div class="form-group">
          <label class="form-label">Reference Code (optional)</label>
          <input type="text" name="reference_code" class="form-control" placeholder="Transaction ref, cheque #, etc.">
        </div>

        <div class="form-group">
          <label class="form-label">Notes (optional)</label>
          <textarea name="notes" class="form-control" rows="2" placeholder="Any additional notes..."></textarea>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline" onclick="closePaymentModal()">Cancel</button>
        <button type="submit" class="btn btn-success">
          <span class="material-icons">check_circle</span> Record Payment
        </button>
      </div>
    </form>
  </div>
</div>

@endsection

@push('scripts')
<script>
function openPaymentModal(invoiceId, invoiceNum, balance) {
  document.getElementById('modal-invoice-id').value  = invoiceId;
  document.getElementById('modal-invoice-num').textContent = invoiceNum;
  document.getElementById('modal-balance').textContent = parseFloat(balance).toFixed(2);
  document.getElementById('modal-amount').value = parseFloat(balance).toFixed(2);
  document.getElementById('modal-amount').max   = parseFloat(balance).toFixed(2);
  document.getElementById('paymentModal').classList.add('open');
}
function closePaymentModal() {
  document.getElementById('paymentModal').classList.remove('open');
}
document.getElementById('paymentModal').addEventListener('click', function(e) {
  if (e.target === this) closePaymentModal();
});
</script>
@endpush
