@extends('layouts.app')
@section('title', 'Invoice ' . $invoice->invoice_number)
@section('breadcrumb', 'Admin / Invoices / Detail')
@section('page-title', 'Invoice Detail')

@section('content')

<div style="margin-bottom:20px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;">
  <a href="{{ route('admin.invoices.index') }}" class="btn btn-outline btn-sm">
    <span class="material-icons">arrow_back</span> Back
  </a>
  <div style="display:flex;gap:8px;flex-wrap:wrap;">
    <a href="{{ route('admin.invoices.pdf', $invoice) }}" class="btn btn-outline btn-sm" target="_blank">
      <span class="material-icons">picture_as_pdf</span> PDF
    </a>
    @if($invoice->customer->email)
      <form action="{{ route('admin.invoices.send-email', $invoice) }}" method="POST" style="display:inline;">
        @csrf
        <button type="submit" class="btn btn-outline btn-sm">
          <span class="material-icons">email</span> Send Email
        </button>
      </form>
    @endif
    @if(in_array($invoice->status, ['issued','partially_paid','overdue']))
      <button class="btn btn-success btn-sm"
              onclick="openPaymentModal({{ $invoice->id }}, '{{ $invoice->invoice_number }}', {{ $invoice->balance_due }})">
        <span class="material-icons">payments</span> Record Payment
      </button>
    @endif
    @if($invoice->status !== 'paid' && $invoice->status !== 'cancelled')
      <form action="{{ route('admin.invoices.destroy', $invoice) }}" method="POST" style="display:inline;"
            onsubmit="return confirm('Cancel this invoice?')">
        @csrf @method('DELETE')
        <button type="submit" class="btn btn-danger btn-sm">
          <span class="material-icons">cancel</span> Cancel
        </button>
      </form>
    @endif
  </div>
</div>

<div class="detail-grid">

  {{-- LEFT: Invoice Details --}}
  <div style="display:flex;flex-direction:column;gap:24px;">

    <div class="card">
      <div class="table-card-header">
        <div class="card-header-float" style="background:var(--gradient-dark);">
          <div>
            <h3>{{ $invoice->invoice_number }}</h3>
            <p>{{ $invoice->billing_period_start->format('d M Y') }} – {{ $invoice->billing_period_end->format('d M Y') }}</p>
          </div>
          @php
            $bc = match($invoice->status) {
              'paid' => 'badge-paid', 'issued' => 'badge-issued', 'overdue' => 'badge-overdue',
              'partially_paid' => 'badge-partially', 'cancelled' => 'badge-cancelled', default => 'badge-draft'
            };
          @endphp
          <span class="badge-status {{ $bc }}" style="font-size:13px;padding:6px 14px;">
            {{ ucfirst(str_replace('_',' ',$invoice->status)) }}
          </span>
        </div>
      </div>
      <div class="card-body" style="padding-top:0;">
        <div class="detail-row">
          <span class="detail-label">Customer</span>
          <span class="detail-value">
            <a href="{{ route('admin.customers.show', $invoice->customer) }}" style="color:var(--accent-blue);">
              {{ $invoice->customer->name }}
            </a>
            <span style="color:var(--text-muted);font-size:12px;margin-left:6px;">{{ $invoice->customer->account_number }}</span>
          </span>
        </div>
        <div class="detail-row">
          <span class="detail-label">Meter ID</span>
          <span class="detail-value" style="font-family:monospace;color:var(--accent-teal);">{{ $invoice->meter->meter_id }}</span>
        </div>
        <div class="detail-row">
          <span class="detail-label">Total Consumption</span>
          <span class="detail-value">{{ number_format($invoice->total_usage, 3) }} m³</span>
        </div>
        <div class="detail-row">
          <span class="detail-label">Issued Date</span>
          <span class="detail-value">{{ $invoice->issued_at?->format('d M Y') ?? '—' }}</span>
        </div>
        <div class="detail-row">
          <span class="detail-label">Due Date</span>
          <span class="detail-value" style="{{ $invoice->isOverdue() ? 'color:var(--accent-orange)' : '' }}">
            {{ $invoice->due_date?->format('d M Y') ?? '—' }}
            @if($invoice->isOverdue()) <span style="font-size:11px;">(OVERDUE)</span> @endif
          </span>
        </div>
      </div>
    </div>

    {{-- Tier Breakdown --}}
    <div class="card">
      <div class="table-card-header">
        <div class="card-header-float" style="background:var(--gradient-blue);">
          <div>
            <h3>Charge Breakdown</h3>
            <p>Tiered billing calculation
              @php $mType = strtolower(optional($invoice->meter)->meter_type ?? 'residential'); @endphp
              @if($mType === 'commercial')
                &nbsp;·&nbsp;
                <span style="padding:2px 8px;border-radius:10px;font-size:11px;font-weight:600;
                             background:rgba(20,184,166,.2);color:var(--accent-teal);">
                  <span class="material-icons" style="font-size:12px;vertical-align:middle;">business</span>
                  Commercial Tariff
                </span>
              @else
                &nbsp;·&nbsp;
                <span style="padding:2px 8px;border-radius:10px;font-size:11px;font-weight:600;
                             background:rgba(56,189,248,.2);color:var(--accent-blue);">
                  <span class="material-icons" style="font-size:12px;vertical-align:middle;">home</span>
                  Residential Tariff
                </span>
              @endif
            </p>
          </div>
          <span class="material-icons">layers</span>
        </div>
      </div>
      <div class="table-wrapper">
        <table>
          <thead>
            <tr><th>Description</th><th>Qty (m³)</th><th>Rate</th><th>Amount</th></tr>
          </thead>
          <tbody>
            @foreach($invoice->items as $item)
              <tr>
                <td class="td-primary">{{ $item->description }}</td>
                <td>{{ number_format($item->quantity, 3) }}</td>
                <td>T$ {{ number_format($item->unit_rate, 4) }}</td>
                <td class="td-primary">T$ {{ number_format($item->line_total, 2) }}</td>
              </tr>
            @endforeach
          </tbody>
          <tfoot>
            <tr style="border-top:2px solid var(--border);">
              <td colspan="3" style="padding:10px 14px;color:var(--text-secondary);">Subtotal</td>
              <td style="padding:10px 14px;font-weight:600;">T$ {{ number_format($invoice->subtotal, 2) }}</td>
            </tr>
            <tr>
              <td colspan="3" style="padding:6px 14px;color:var(--text-muted);">Tax</td>
              <td style="padding:6px 14px;color:var(--text-muted);">T$ {{ number_format($invoice->tax_amount, 2) }}</td>
            </tr>
            @if($invoice->discount_amount > 0)
              <tr>
                <td colspan="3" style="padding:6px 14px;color:var(--accent-green);">Discount</td>
                <td style="padding:6px 14px;color:var(--accent-green);">- T$ {{ number_format($invoice->discount_amount, 2) }}</td>
              </tr>
            @endif
            <tr style="background:rgba(26,115,232,0.08);">
              <td colspan="3" style="padding:12px 14px;font-weight:700;font-size:15px;">Total Amount</td>
              <td style="padding:12px 14px;font-weight:700;font-size:15px;color:var(--accent-blue);">T$ {{ number_format($invoice->total_amount, 2) }}</td>
            </tr>
            <tr>
              <td colspan="3" style="padding:8px 14px;color:var(--accent-green);">Amount Paid</td>
              <td style="padding:8px 14px;color:var(--accent-green);">T$ {{ number_format($invoice->amount_paid, 2) }}</td>
            </tr>
            <tr>
              <td colspan="3" style="padding:8px 14px;font-weight:600;color:var(--accent-orange);">Balance Due</td>
              <td style="padding:8px 14px;font-weight:700;color:var(--accent-orange);">T$ {{ number_format($invoice->balance_due, 2) }}</td>
            </tr>
          </tfoot>
        </table>
      </div>
    </div>

  </div>

  {{-- RIGHT: Payment History --}}
  <div>
    <div class="card">
      <div class="table-card-header">
        <div class="card-header-float" style="background:var(--gradient-green);">
          <div><h3>Payment History</h3><p>{{ $invoice->payments->count() }} payment(s) recorded</p></div>
          <span class="material-icons">receipt</span>
        </div>
      </div>
      <div class="table-wrapper">
        <table>
          <thead>
            <tr><th>Receipt #</th><th>Date</th><th>Method</th><th>Amount</th></tr>
          </thead>
          <tbody>
            @forelse($invoice->payments as $pay)
              <tr>
                <td style="font-family:monospace;font-size:11px;">{{ $pay->receipt_number }}</td>
                <td>{{ $pay->payment_date->format('d M Y') }}</td>
                <td>{{ ucfirst(str_replace('_',' ',$pay->payment_method)) }}</td>
                <td class="td-primary">T$ {{ number_format($pay->amount, 2) }}</td>
              </tr>
            @empty
              <tr><td colspan="4" style="text-align:center;color:var(--text-muted);padding:24px;">No payments yet</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>

</div>

{{-- PAYMENT MODAL --}}
<div class="modal-overlay" id="paymentModal">
  <div class="modal">
    <div class="modal-header">
      <h3 class="modal-title">Record Payment</h3>
      <button class="modal-close" onclick="closePaymentModal()"><span class="material-icons">close</span></button>
    </div>
    <form action="{{ route('admin.payments.store') }}" method="POST">
      @csrf
      <input type="hidden" name="invoice_id" id="modal-invoice-id">
      <div class="modal-body">
        <div style="background:rgba(26,115,232,0.08);border:1px solid rgba(26,115,232,0.2);border-radius:8px;padding:12px 16px;margin-bottom:20px;">
          <div style="font-size:12px;color:var(--text-muted);">Invoice</div>
          <div style="font-weight:600;" id="modal-invoice-num">—</div>
          <div style="font-size:13px;color:var(--accent-orange);margin-top:2px;">Balance: T$ <span id="modal-balance">0.00</span></div>
        </div>
        <div class="form-grid-2">
          <div class="form-group">
            <label class="form-label">Amount (T$)</label>
            <input type="number" name="amount" id="modal-amount" class="form-control" step="0.01" min="0.01" required>
          </div>
          <div class="form-group">
            <label class="form-label">Payment Date</label>
            <input type="date" name="payment_date" class="form-control" value="{{ now()->format('Y-m-d') }}" required>
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
          <input type="text" name="reference_code" class="form-control" placeholder="Transaction ref, cheque #...">
        </div>
        <div class="form-group">
          <label class="form-label">Notes (optional)</label>
          <textarea name="notes" class="form-control" rows="2"></textarea>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline" onclick="closePaymentModal()">Cancel</button>
        <button type="submit" class="btn btn-success"><span class="material-icons">check_circle</span> Record Payment</button>
      </div>
    </form>
  </div>
</div>

@endsection

@push('scripts')
<script>
function openPaymentModal(id, num, balance) {
  document.getElementById('modal-invoice-id').value = id;
  document.getElementById('modal-invoice-num').textContent = num;
  document.getElementById('modal-balance').textContent = parseFloat(balance).toFixed(2);
  document.getElementById('modal-amount').value = parseFloat(balance).toFixed(2);
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
