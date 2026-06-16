{{-- resources/views/admin/invoices/create.blade.php --}}
@extends('layouts.app')
@section('title', 'Create Invoices')
@section('breadcrumb', 'Admin / Billing / Create Invoices')
@section('page-title', 'CREATE INVOICES')

@section('content')

<div class="section-header">
  <div>
    <h2>Billing Check & Invoice Creation</h2>
    <p>Select billing period and review consumption before generating invoices</p>
  </div>
</div>

<div style="display:grid;grid-template-columns:340px 1fr;gap:24px;align-items:start;">

  {{-- LEFT: Controls --}}
  <div class="card-tight-margin">

    <div class="card-header-float" style="background:var(--gradient-black);">
      <div><h3>Billing Parameters</h3></div>
        <span class="material-icons" style="color:var(--accent-blue)">tune</span>
      </div>
    <div class="card-body">
      <form id="billingCheckForm" action="{{ route('admin.billing.check') }}" method="GET">

        <div class="form-group">
          <label class="form-label">Billing Period Start</label>
          <input type="date" name="period_start" class="form-control"
                 value="{{ request('period_start', now()->startOfMonth()->format('Y-m-d')) }}" required>
        </div>

        <div class="form-group">
          <label class="form-label">Billing Period End</label>
          <input type="date" name="period_end" class="form-control"
                 value="{{ request('period_end', now()->endOfMonth()->format('Y-m-d')) }}" required>
        </div>

        <div class="form-group">
          <label class="form-label">Filter by Block</label>
          <select name="block" class="form-control">
            <option value="">All Blocks</option>
            @foreach($blocks as $block)
              <option value="{{ $block }}" {{ request('block') === $block ? 'selected' : '' }}>{{ $block }}</option>
            @endforeach
          </select>
        </div>

        <div class="form-group">
          <label class="form-label">Customer</label>
          <select name="customer_id" class="form-control">
            <option value="">All Customers</option>
            @foreach($customers as $c)
              <option value="{{ $c->id }}" {{ request('customer_id') == $c->id ? 'selected' : '' }}>
                {{ $c->account_number }} — {{ $c->name }}
              </option>
            @endforeach
          </select>
        </div>

        <button type="submit" class="btn btn-primary" style="width:100%;">
          <span class="material-icons">search</span> Check Billing
        </button>

      </form>
    </div>
  </div>

  {{-- RIGHT: Preview Results --}}
  <div>
    @if(isset($previews) && count($previews) > 0)

      {{-- Summary Banner --}}
      <div class="card-tight-margin" style="margin-bottom:24px;">
        <div class="card-header-float" style="background:var(--gradient-dark);">
            <div>
              <h3>Billing Summary</h3>
              <p>{{ request('period_start') }} to {{ request('period_end') }}</p>
              </div>
              <span class="material-icons" style="color:var(--accent-blue)">bar_chart</span>
            </div>        
          <div class="card-body" style="display:flex;gap:32px;align-items:center;flex-wrap:wrap;">
            <div>
              <div style="font-size:12px;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.5px;">Customers Ready</div>
              <div style="font-size:28px;font-weight:700;color:var(--accent-green);">{{ count($previews) }}</div>
            </div>
            <div>
              <div style="font-size:12px;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.5px;">Total Consumption</div>
              <div style="font-size:28px;font-weight:700;">{{ number_format(collect($previews)->sum('total_usage_m3'), 1) }} m³</div>
            </div>
            <div>
              <div style="font-size:12px;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.5px;">Total Amount</div>
              <div style="font-size:28px;font-weight:700;color:var(--accent-blue);">T$ {{ number_format(collect($previews)->sum('total_amount'), 2) }}</div>
            </div>
            <div style="margin-left:auto;">
              <form action="{{ route('admin.billing.generate') }}" method="POST" id="generateForm">
                @csrf
                <input type="hidden" name="period_start" value="{{ request('period_start') }}">
                <input type="hidden" name="period_end"   value="{{ request('period_end') }}">
                <input type="hidden" name="block"        value="{{ request('block') }}">
                <input type="hidden" name="customer_id"  value="{{ request('customer_id') }}">
                <button type="button" class="btn btn-success" onclick="confirmGenerate()">
                  <span class="material-icons">receipt_long</span>
                  Generate {{ count($previews) }} Invoice{{ count($previews) > 1 ? 's' : '' }}
                </button>
              </form>
          </div>
        </div>
      </div>

      {{-- Preview Table --}}
      <div class="card";>
        <div class="table-card-header">
          <div class="card-header-float" style="background:var(--gradient-dark);">
            <div>
              <h3>Billing Preview</h3>
              <p>{{ request('period_start') }} to {{ request('period_end') }}</p>
            </div>
            <span class="material-icons" style="color:var(--accent-blue)">preview</span>
          </div>
        </div>
        <div class="table-wrapper">
          <table>
            <thead>
              <tr>
                <th>Customer</th>
                <th>Meter ID</th>
                <th>Usage (m³)</th>
                <th>Subtotal</th>
                <th>Tax</th>
                <th>Total</th>
                <th>Readings</th>
                <th>Detail</th>
              </tr>
            </thead>
            <tbody>
              @foreach($previews as $p)
                <tr>
                  <td>
                    <div class="u-name">{{ $p['customer']->name }}</div>
                    <div class="u-sub">{{ $p['customer']->account_number }}</div>
                  </td>
                  <td><code style="font-size:11px;color:var(--accent-teal)">{{ $p['meter']->meter_id }}</code></td>
                  <td class="td-primary">{{ number_format($p['total_usage_m3'], 3) }}</td>
                  <td>T$ {{ number_format($p['subtotal'], 2) }}</td>
                  <td style="color:var(--text-muted);">T$ {{ number_format($p['tax_amount'], 2) }}</td>
                  <td class="td-primary" style="font-weight:700;">T$ {{ number_format($p['total_amount'], 2) }}</td>
                  <td style="color:var(--text-muted);">{{ $p['reading_count'] }}</td>
                  <td>
                    <button class="action-btn" title="View tier breakdown"
                            onclick="showTierDetail({{ json_encode($p['items']) }}, '{{ $p['customer']->name }}')">
                      <span class="material-icons">layers</span>
                    </button>
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>

    @elseif(request()->hasAny(['period_start','period_end']))
      <div class="card">
        <div class="empty-state">
          <span class="material-icons">search_off</span>
          <h3>No billable customers found</h3>
          <p style="color:var(--text-muted);font-size:13px;margin-top:4px;">
            All customers in this period may already have invoices, or there are no meter readings available.
          </p>
        </div>
      </div>
    @else
      <div class="card-tight-margin">
        <div class="empty-state" style="padding:80px 20px;">
          <span class="material-icons" style="font-size:64px;color:var(--accent-blue);opacity:0.5;">receipt_long</span>
          <h3>Select billing parameters</h3>
          <p style="color:var(--text-muted);font-size:13px;margin-top:4px;">
            Choose a period on the left and click "Check Billing" to preview invoices.
          </p>
        </div>
      </div>
    @endif
  </div>

</div>

{{-- ── TIER DETAIL MODAL ──────────────────────────────────────── --}}
<div class="modal-overlay" id="tierModal">
  <div class="modal" style="max-width:480px;">
    <div class="modal-header">
      <h3 class="modal-title">Tariff Tier Breakdown — <span id="tier-customer-name"></span></h3>
      <button class="modal-close" onclick="closeTierModal()">
        <span class="material-icons">close</span>
      </button>
    </div>
    <div class="modal-body">
      <table style="width:100%;font-size:13px;">
        <thead>
          <tr>
            <th style="text-align:left;padding:8px 0;border-bottom:1px solid var(--border);color:var(--text-muted);font-size:11px;text-transform:uppercase;">Tier</th>
            <th style="text-align:right;padding:8px 0;border-bottom:1px solid var(--border);color:var(--text-muted);font-size:11px;text-transform:uppercase;">Qty (m³)</th>
            <th style="text-align:right;padding:8px 0;border-bottom:1px solid var(--border);color:var(--text-muted);font-size:11px;text-transform:uppercase;">Rate</th>
            <th style="text-align:right;padding:8px 0;border-bottom:1px solid var(--border);color:var(--text-muted);font-size:11px;text-transform:uppercase;">Amount</th>
          </tr>
        </thead>
        <tbody id="tier-rows"></tbody>
      </table>
    </div>
  </div>
</div>

@endsection

@push('scripts')
<script>
function confirmGenerate() {
  const count = {{ isset($previews) ? count($previews) : 0 }};
  if (confirm(`Generate ${count} invoice(s) for this billing period? This action cannot be undone.`)) {
    document.getElementById('generateForm').submit();
  }
}

function showTierDetail(items, customerName) {
  document.getElementById('tier-customer-name').textContent = customerName;
  const tbody = document.getElementById('tier-rows');
  let total = 0;
  tbody.innerHTML = items.map(item => {
    total += parseFloat(item.line_total);
    return `<tr>
      <td style="padding:10px 0;border-bottom:1px solid rgba(255,255,255,0.04);color:var(--text-primary);">${item.description}</td>
      <td style="padding:10px 0;border-bottom:1px solid rgba(255,255,255,0.04);text-align:right;">${parseFloat(item.quantity).toFixed(3)}</td>
      <td style="padding:10px 0;border-bottom:1px solid rgba(255,255,255,0.04);text-align:right;">T$ ${parseFloat(item.unit_rate).toFixed(4)}</td>
      <td style="padding:10px 0;border-bottom:1px solid rgba(255,255,255,0.04);text-align:right;font-weight:500;">T$ ${parseFloat(item.line_total).toFixed(2)}</td>
    </tr>`;
  }).join('') + `<tr>
    <td colspan="3" style="padding:12px 0 0;font-weight:600;color:var(--text-primary);">Subtotal</td>
    <td style="padding:12px 0 0;text-align:right;font-weight:700;color:var(--accent-blue);">T$ ${total.toFixed(2)}</td>
  </tr>`;
  document.getElementById('tierModal').classList.add('open');
}
function closeTierModal() {
  document.getElementById('tierModal').classList.remove('open');
}
document.getElementById('tierModal').addEventListener('click', function(e) {
  if (e.target === this) closeTierModal();
});
</script>
@endpush
