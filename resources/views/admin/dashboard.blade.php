@extends('layouts.app')
@section('title', 'Admin Dashboard')
@section('breadcrumb', 'Admin / Dashboard')
@section('page-title', 'Dashboard')

@push('head')
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
@endpush

@section('content')

{{-- ── STAT CARDS ──────────────────────────────────────────── --}}
<div class="stat-cards">

  <div class="stat-card">
    <div class="stat-icon" style="background:var(--gradient-blue);">
      <span class="material-icons">people</span>
    </div>
    <div class="stat-body">
      <div>
        <div class="stat-label">Total Customers</div>
        <div class="stat-value">{{ number_format($stats['total_customers']) }}</div>
      </div>
    </div>
    <hr class="stat-divider">
    <div class="stat-footer">
      <span class="material-icons stat-up">trending_up</span>
      <span class="stat-up">+{{ $stats['new_customers_month'] }} this month</span>
    </div>
  </div>

  <div class="stat-card">
    <div class="stat-icon" style="background:var(--gradient-info);">
      <span class="material-icons">water_drop</span>
    </div>
    <div class="stat-body">
      <div>
        <div class="stat-label">Consumption This Month</div>
        <div class="stat-value">{{ number_format($stats['consumption_this_month'], 1) }} m³</div>
      </div>
    </div>
    <hr class="stat-divider">
    <div class="stat-footer">
      <span class="material-icons">bar_chart</span>
      <span>{{ $stats['reading_count_today'] }} readings today</span>
    </div>
  </div>

  <div class="stat-card">
    <div class="stat-icon" style="background:var(--gradient-green);">
      <span class="material-icons">payments</span>
    </div>
    <div class="stat-body">
      <div>
        <div class="stat-label">Collected This Month</div>
        <div class="stat-value">T$ {{ number_format($stats['collected_this_month'], 0) }}</div>
      </div>
    </div>
    <hr class="stat-divider">
    <div class="stat-footer">
      <span class="material-icons">receipt_long</span>
      <span>T$ {{ number_format($stats['invoiced_this_month'], 0) }} invoiced</span>
    </div>
  </div>

  <div class="stat-card">
    <div class="stat-icon" style="background:var(--gradient-orange);">
      <span class="material-icons">pending_actions</span>
    </div>
    <div class="stat-body">
      <div>
        <div class="stat-label">Outstanding Invoices</div>
        <div class="stat-value">{{ $stats['outstanding_count'] }}</div>
      </div>
    </div>
    <hr class="stat-divider">
    <div class="stat-footer">
      <span class="material-icons stat-down">warning_amber</span>
      <span class="stat-down">T$ {{ number_format($stats['outstanding_amount'], 0) }} balance due</span>
    </div>
  </div>

</div>

{{-- ── CHARTS ROW ──────────────────────────────────────────── --}}
<div style="display:grid;grid-template-columns:2fr 1fr;gap:24px;">

  <div class="card">
    <div class="table-card-header">
      <div class="card-header-float" style="background:var(--gradient-blue);">
        <div><h3>Daily Consumption</h3><p>Water usage (m³) — this month</p></div>
        <span class="material-icons">show_chart</span>
      </div>
    </div>
    <div class="card-body" style="padding-top:0;">
      <div class="chart-container" style="height:280px;">
        <canvas id="consumptionChart"></canvas>
      </div>
    </div>
  </div>

  <div class="card">
    <div class="table-card-header">
      <div class="card-header-float" style="background:var(--gradient-green);">
        <div><h3>Revenue (6 months)</h3><p>Invoiced vs Collected</p></div>
        <span class="material-icons">bar_chart</span>
      </div>
    </div>
    <div class="card-body" style="padding-top:0;">
      <div class="chart-container" style="height:280px;">
        <canvas id="revenueChart"></canvas>
      </div>
    </div>
  </div>

</div>

{{-- ── BOTTOM ROW ──────────────────────────────────────────── --}}
<div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;">

  {{-- Overdue Invoices --}}
  <div class="card">
    <div class="table-card-header">
      <div class="card-header-float" style="background:var(--gradient-orange);">
        <div><h3>Overdue Invoices</h3><p>Requires immediate attention</p></div>
        <span class="material-icons">warning_amber</span>
      </div>
    </div>
    <div class="table-wrapper">
      <table>
        <thead>
          <tr><th>Customer</th><th>Invoice #</th><th>Days Overdue</th><th>Balance</th><th></th></tr>
        </thead>
        <tbody>
          @forelse($overdueInvoices as $inv)
            <tr>
              <td class="td-primary">{{ $inv->customer->name }}</td>
              <td style="font-size:11px;font-family:monospace;">{{ $inv->invoice_number }}</td>
              <td>
                <span style="color:var(--accent-orange);">
                  {{ now()->diffInDays($inv->due_date) }} days
                </span>
              </td>
              <td>T$ {{ number_format($inv->balance_due, 2) }}</td>
              <td>
                <a href="{{ route('admin.invoices.show', $inv) }}" class="action-btn" title="View invoice">
                  <span class="material-icons">open_in_new</span>
                </a>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="5" style="text-align:center;color:var(--text-muted);padding:24px;">
                No overdue invoices
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
    @if($overdueInvoices->count() > 0)
      <div style="padding:12px 20px;border-top:1px solid var(--border);">
        <a href="{{ route('admin.invoices.index') }}?status=overdue" style="font-size:12px;color:var(--accent-blue);">
          View all overdue invoices →
        </a>
      </div>
    @endif
  </div>

  {{-- Anomaly Alerts --}}
  <div class="card">
    <div class="table-card-header">
      <div class="card-header-float" style="background:var(--gradient-pink);">
        <div><h3>Anomaly Alerts</h3><p>Unusual meter readings detected</p></div>
        <span class="material-icons">notifications_active</span>
      </div>
    </div>
    <div class="table-wrapper">
      <table>
        <thead>
          <tr><th>Customer</th><th>Meter ID</th><th>Time</th><th>Usage (L)</th><th></th></tr>
        </thead>
        <tbody>
          @forelse($anomalyReadings as $reading)
            <tr>
              <td>
                <div class="u-name">{{ $reading->meter->customer->name ?? '—' }}</div>
                <div class="u-sub">{{ $reading->meter->customer->account_number ?? '—' }}</div>
              </td>
              <td>
                <code style="font-size:11px;color:var(--accent-teal);">
                  {{ $reading->meter->meter_id }}
                </code>
              </td>
              <td style="font-size:12px;color:var(--text-muted);">
                {{ $reading->capture_time->format('d M H:i') }}
              </td>
              <td>
                <span style="color:var(--accent-pink);font-weight:600;">
                  {{ number_format($reading->usage, 0) }}
                </span>
              </td>
              <td>
                {{-- Fixed: link ke readings index filter by meter_id --}}
                <a href="{{ route('admin.readings.index') }}?meter={{ $reading->meter->meter_id }}"
                   class="action-btn" title="View readings for this meter">
                  <span class="material-icons">open_in_new</span>
                </a>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="5" style="text-align:center;color:var(--text-muted);padding:24px;">
                No anomalies detected
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
    @if($anomalyReadings->count() > 0)
      <div style="padding:12px 20px;border-top:1px solid var(--border);">
        <a href="{{ route('admin.readings.index') }}?anomaly=1" style="font-size:12px;color:var(--accent-blue);">
          View all anomalies →
        </a>
      </div>
    @endif
  </div>

</div>

@endsection

@push('scripts')
<script>
// ── Chart colour helper — reads current theme ─────────────────────
function chartColors() {
  var isLight = document.documentElement.classList.contains('light-mode');
  return {
    text:     isLight ? 'rgba(74,85,104,0.9)'    : 'rgba(255,255,255,0.65)',
    legend:   isLight ? 'rgba(74,85,104,0.9)'    : 'rgba(255,255,255,0.65)',
    grid:     isLight ? 'rgba(0,0,0,0.07)'        : 'rgba(255,255,255,0.06)',
    tickLine: isLight ? 'rgba(0,0,0,0.10)'        : 'rgba(255,255,255,0.10)',
  };
}

Chart.defaults.font = { family: 'Roboto', size: 12 };

var consumptionData = {
  labels: @json($consumptionChart['labels']),
  data:   @json($consumptionChart['data']),
};
var revenueData = {
  labels:    @json($revenueChart['labels']),
  invoiced:  @json($revenueChart['invoiced']),
  collected: @json($revenueChart['collected']),
};

var consumptionChart = null;
var revenueChart     = null;

function buildCharts() {
  var c = chartColors();
  Chart.defaults.color = c.text;

  // Destroy existing instances before rebuilding
  if (consumptionChart) { consumptionChart.destroy(); consumptionChart = null; }
  if (revenueChart)     { revenueChart.destroy();     revenueChart = null; }

  // ── Daily Consumption ────────────────────────────────────────────
  var consumptionCtx = document.getElementById('consumptionChart');
  if (consumptionCtx) {
    consumptionChart = new Chart(consumptionCtx.getContext('2d'), {
      type: 'bar',
      data: {
        labels: consumptionData.labels,
        datasets: [{
          label: 'Usage (m³)',
          data: consumptionData.data,
          backgroundColor: 'rgba(26,115,232,0.60)',
          borderColor:     'rgba(26,115,232,0.90)',
          borderWidth: 1,
          borderRadius: 4,
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
          x: {
            grid:  { color: c.grid, borderColor: c.tickLine },
            ticks: { color: c.text, maxRotation: 0 }
          },
          y: {
            grid:       { color: c.grid, borderColor: c.tickLine },
            ticks:      { color: c.text },
            beginAtZero: true
          }
        }
      }
    });
  }

  // ── Revenue 6-month ──────────────────────────────────────────────
  var revenueCtx = document.getElementById('revenueChart');
  if (revenueCtx) {
    revenueChart = new Chart(revenueCtx.getContext('2d'), {
      type: 'bar',
      data: {
        labels: revenueData.labels,
        datasets: [
          {
            label: 'Invoiced',
            data:  revenueData.invoiced,
            backgroundColor: 'rgba(26,115,232,0.55)',
            borderRadius: 4,
          },
          {
            label: 'Collected',
            data:  revenueData.collected,
            backgroundColor: 'rgba(76,175,80,0.65)',
            borderRadius: 4,
          }
        ]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            labels: { color: c.legend, boxWidth: 12, font: { size: 12 } }
          }
        },
        scales: {
          x: {
            grid:  { color: c.grid, borderColor: c.tickLine },
            ticks: { color: c.text }
          },
          y: {
            grid:       { color: c.grid, borderColor: c.tickLine },
            ticks:      { color: c.text },
            beginAtZero: true
          }
        }
      }
    });
  }
}

// Build on first load
buildCharts();

// Rebuild when theme toggle is clicked — hook into the global toggleTheme()
var _origToggleTheme = window.toggleTheme;
window.toggleTheme = function() {
  _origToggleTheme();   // run the original toggle (class swap + localStorage)
  buildCharts();        // then redraw charts with new colours
};
</script>
@endpush
