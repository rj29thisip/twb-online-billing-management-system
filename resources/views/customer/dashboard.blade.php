@extends('layouts.app')
@section('title', 'My Dashboard')
@section('breadcrumb', 'Customer / Dashboard')
@section('page-title', 'MY TWB ACCOUNT')

@push('head')
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
@endpush

@section('content')

{{-- ── ALERTS ──────────────────────────────────────────────── --}}
@if($overdueInvoice)
  <div class="alert alert-warning" role="alert">
    <span class="material-icons" style="font-size:18px">warning_amber</span>
    You have an overdue invoice ({{ $overdueInvoice->invoice_number }}) of
    <strong>T$ {{ number_format($overdueInvoice->balance_due, 2) }}</strong>
    — due {{ $overdueInvoice->due_date->format('d M Y') }}.
    <a href="{{ route('customer.invoices.show', $overdueInvoice) }}"
       style="color:inherit;text-decoration:underline;margin-left:6px;">View Invoice →</a>
    <button class="alert-close" onclick="this.parentElement.remove()">
      <span class="material-icons" style="font-size:16px">close</span>
    </button>
  </div>
@endif

@if(isset($anomalyAlert) && $anomalyAlert)
  <div class="alert alert-error" role="alert">
    <span class="material-icons" style="font-size:18px">error</span>
    Unusual water usage detected on {{ $anomalyAlert->capture_time->format('d M Y H:i') }}.
    Please check for leaks or contact us if this is unexpected.
    <button class="alert-close" onclick="this.parentElement.remove()">
      <span class="material-icons" style="font-size:16px">close</span>
    </button>
  </div>
@endif

{{-- ── STAT CARDS ──────────────────────────────────────────── --}}
<div class="stat-cards">

  <div class="stat-card">
    <div class="stat-icon" style="background:var(--gradient-dark);">
      <span class="material-icons">water_drop</span>
    </div>
    <div class="stat-body">
      <div>
        <div class="stat-label">Usage this Month</div>
        <div class="stat-value">{{ number_format($billing['usage_m3'], 2) }} m³</div>
      </div>
    </div>
    <hr class="stat-divider">
    <div class="stat-footer">
      <span class="material-icons">straighten</span>
      <span>{{ number_format($billing['usage_liters'], 0) }} liters</span>
    </div>
  </div>

  <div class="stat-card">
    <div class="stat-icon" style="background:var(--gradient-dark);">
      <span class="material-icons">calculate</span>
    </div>
    <div class="stat-body">
      <div>
        <div class="stat-label">Current Bill (MTD)</div>
        <div class="stat-value">T$ {{ number_format($billing['current_bill'], 2) }}</div>
      </div>
    </div>
    <hr class="stat-divider">
    <div class="stat-footer">
      <span class="material-icons">trending_up</span>
      <span>~T$ {{ number_format($billing['estimated_full_bill'], 2) }} estimated</span>
    </div>
  </div>

  <div class="stat-card">
    <div class="stat-icon" style="background:var(--gradient-pink);">
      <span class="material-icons">receipt_long</span>
    </div>
    <div class="stat-body">
      <div>
        <div class="stat-label">Outstanding Balance</div>
        <div class="stat-value">T$ {{ number_format($outstandingBalance, 2) }}</div>
      </div>
    </div>
    <hr class="stat-divider">
    <div class="stat-footer">
      <a href="{{ route('customer.invoices.index') }}" style="color:var(--accent-blue);display:flex;align-items:center;gap:4px;font-size:12px;">
        <span class="material-icons" style="font-size:13px">open_in_new</span>View invoices
      </a>
    </div>
  </div>

  <div class="stat-card">
    <div class="stat-icon" style="background:var(--gradient-dark);">
      <span class="material-icons">check_circle</span>
    </div>
    <div class="stat-body">
      <div>
        <div class="stat-label">Last Payment</div>
        <div class="stat-value" style="font-size:18px;">
          {{ $lastPayment ? 'T$ ' . number_format($lastPayment->amount, 2) : '—' }}
        </div>
      </div>
    </div>
    <hr class="stat-divider">
    <div class="stat-footer">
      <span class="material-icons">calendar_today</span>
      <span>{{ $lastPayment ? $lastPayment->payment_date->format('d M Y') : 'No payments recorded' }}</span>
    </div>
  </div>

</div>

{{-- ── USAGE CHART ─────────────────────────────────────────── --}}
<div class="card">
  <div class="table-card-header">
    <div class="card-header-float" style="background:var(--gradient-dark);">
      <div>
        <h3>Water Usage — {{ now()->format('F Y') }}</h3>
        <p id="chart-subtitle">Daily consumption — click a bar to see hourly breakdown</p>
      </div>
      <button class="btn btn-outline btn-sm" id="backBtn" style="display:none;" onclick="showDaily()">
        ← Back to Daily
      </button>
    </div>
  </div>
  <div class="card-body" style="padding-top:0;">
    <div class="chart-container" style="height:300px;">
      <canvas id="usageChart"></canvas>
    </div>
  </div>
</div>

{{-- ── BOTTOM ROW ──────────────────────────────────────────── --}}
<div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;">

  {{-- Recent Invoices --}}
  <div class="card">
    <div class="table-card-header">
      <div class="card-header-float" style="background:var(--gradient-dark);">
        <div><h3>Recent Invoices</h3><p>Last 5 billing periods</p></div>
        <span class="material-icons" style="color:var(--accent-blue)">receipt</span>
      </div>
    </div>
    <div class="table-wrapper">
      <table>
        <thead>
          <tr><th>Period</th><th>Amount</th><th>Status</th><th></th></tr>
        </thead>
        <tbody>
          @forelse($recentInvoices as $inv)
            <tr>
              <td class="td-primary">{{ $inv->billing_period_start->format('M Y') }}</td>
              <td>T$ {{ number_format($inv->total_amount, 2) }}</td>
              <td>
                @php
                  $bc = match($inv->status) {
                    'paid'           => 'badge-paid',
                    'issued'         => 'badge-issued',
                    'overdue'        => 'badge-overdue',
                    'partially_paid' => 'badge-partially',
                    'cancelled'      => 'badge-cancelled',
                    default          => 'badge-draft',
                  };
                @endphp
                <span class="badge-status {{ $bc }}">{{ ucfirst(str_replace('_', ' ', $inv->status)) }}</span>
              </td>
              <td>
                <a href="{{ route('customer.invoices.show', $inv) }}" class="action-btn">
                  <span class="material-icons">open_in_new</span>
                </a>
              </td>
            </tr>
          @empty
            <tr><td colspan="4" style="text-align:center;color:var(--text-muted);padding:24px;">No invoices yet</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

  {{-- Announcements --}}
  <div class="card">
    <div class="table-card-header">
      <div class="card-header-float" style="background:var(--gradient-orange);">
        <div><h3>News & Announcements</h3><p>Latest updates from TWB</p></div>
        <span class="material-icons" style="color:var(--accent-blue)">campaign</span>
      </div>
    </div>
    <div class="card-body" style="padding-top:0;">
      @forelse($announcements as $ann)
        <div style="padding:14px 0;border-bottom:1px solid var(--border);">
          <div style="display:flex;align-items:center;gap:8px;margin-bottom:6px;">
            <span class="badge-status {{ $ann->type === 'alert' ? 'badge-overdue' : ($ann->type === 'promotion' ? 'badge-paid' : ($ann->type === 'event' ? 'badge-partially' : 'badge-issued')) }}">
              {{ ucfirst($ann->type) }}
            </span>
            <span style="font-size:11px;color:var(--text-muted);">
              {{ $ann->publish_from?->format('d M Y') ?? $ann->created_at->format('d M Y') }}
            </span>
          </div>
          <div style="font-weight:500;color:var(--text-primary);margin-bottom:6px;">{{ $ann->title }}</div>
          <div style="font-size:13px;color:var(--text-secondary);line-height:1.6;margin-bottom:8px;">
            {{ Str::limit($ann->content, 100) }}
          </div>
          @if(strlen($ann->content) > 100)
            <button
              onclick='openAnnouncement(@json($ann->title), @json($ann->content), @json($ann->type), @json(optional($ann->publish_from)->format("d M Y") ?? $ann->created_at->format("d M Y")))'
              style="font-size:12px;color:var(--accent-blue);background:none;border:none;cursor:pointer;padding:0;">
              Read more →
            </button>
          @endif
        </div>
      @empty
        <div class="empty-state" style="padding:32px 0;">
          <span class="material-icons">campaign</span>
          <h3>No announcements</h3>
        </div>
      @endforelse
    </div>
  </div>

</div>

{{-- ── ANNOUNCEMENT MODAL ───────────────────────────────────── --}}
<div class="modal-overlay" id="annModal">
  <div class="modal" style="max-width:560px;">
    <div class="modal-header">
      <div>
        <span id="ann-badge" class="badge-status badge-issued" style="font-size:11px;margin-bottom:6px;display:inline-block;"></span>
        <h3 class="modal-title" id="ann-title" style="margin-top:4px;"></h3>
        <div id="ann-date" style="font-size:12px;color:var(--text-muted);margin-top:2px;"></div>
      </div>
      <button class="modal-close" onclick="closeAnnouncement()">
        <span class="material-icons">close</span>
      </button>
    </div>
    <div class="modal-body">
      <div id="ann-content"
           style="font-size:14px;color:var(--text-secondary);line-height:1.8;white-space:pre-wrap;"></div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-outline" onclick="closeAnnouncement()">Close</button>
    </div>
  </div>
</div>

@endsection

@push('scripts')
<script>
// ES5 compatible — works on Safari iOS 12+

function chartColors() {
  var isLight = document.documentElement.classList.contains('light-mode');
  return {
    text: isLight ? 'rgba(74,85,104,0.9)' : 'rgba(255,255,255,0.65)',
    grid: isLight ? 'rgba(0,0,0,0.07)'    : 'rgba(255,255,255,0.06)',
  };
}

var dailyLabels = @json($usageChart['labels']);
var dailyData   = @json($usageChart['data']);
var rawDates    = @json($usageChart['rawDates'] ?? []);
var chartInst;

function buildChart(labels, data, label, color) {
  if (chartInst) { chartInst.destroy(); }
  var c = chartColors();
  Chart.defaults.color = c.text;
  var ctx = document.getElementById('usageChart').getContext('2d');
  chartInst = new Chart(ctx, {
    type: 'bar',
    data: {
      labels: labels,
      datasets: [{
        label: label,
        data: data,
        backgroundColor: color + '99',
        borderColor: color,
        borderWidth: 1,
        borderRadius: 4,
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      onClick: function(e, els) {
        if (els.length && document.getElementById('backBtn').style.display === 'none') {
          if (rawDates.length) { loadHourly(rawDates[els[0].index]); }
        }
      },
      plugins: { legend: { display: false } },
      scales: {
        x: { grid: { color: c.grid }, ticks: { color: c.text } },
        y: { grid: { color: c.grid }, ticks: { color: c.text }, beginAtZero: true },
      },
    },
  });
}

function showDaily() {
  document.getElementById('backBtn').style.display = 'none';
  document.getElementById('chart-subtitle').textContent = 'Daily consumption — click a bar to see hourly breakdown';
  buildChart(dailyLabels, dailyData, 'Usage (L)', '#1a73e8');
}

function loadHourly(date) {
  document.getElementById('backBtn').style.display = '';
  document.getElementById('chart-subtitle').textContent = 'Hourly usage — ' + date;
  var url = '{{ route("customer.usage.hourly") }}?date=' + date;
  var xhr = new XMLHttpRequest();
  xhr.open('GET', url, true);
  xhr.setRequestHeader('Accept', 'application/json');
  xhr.onload = function() {
    if (xhr.status >= 200 && xhr.status < 300) {
      try {
        var json = JSON.parse(xhr.responseText);
        buildChart(json.labels, json.data, 'Usage (L)', '#26c6da');
      } catch(ex) {}
    }
  };
  xhr.send();
}

buildChart(dailyLabels, dailyData, 'Usage (L)', '#1a73e8');

// Rebuild chart when theme toggles
var _origToggleThemeCustDash = window.toggleTheme;
window.toggleTheme = function() {
  _origToggleThemeCustDash();
  if (chartInst) { buildChart(dailyLabels, dailyData, 'Usage (L)', '#1a73e8'); }
};

// Announcement modal
function openAnnouncement(title, content, type, date) {
  var badgeMap = { alert:'badge-overdue', promotion:'badge-paid', event:'badge-partially', news:'badge-issued' };
  document.getElementById('ann-badge').className    = 'badge-status ' + (badgeMap[type] || 'badge-issued');
  document.getElementById('ann-badge').textContent  = type.charAt(0).toUpperCase() + type.slice(1);
  document.getElementById('ann-title').textContent  = title;
  document.getElementById('ann-date').textContent   = date;
  document.getElementById('ann-content').textContent = content;
  document.getElementById('annModal').classList.add('open');
}
function closeAnnouncement() {
  document.getElementById('annModal').classList.remove('open');
}
document.getElementById('annModal').addEventListener('click', function(e) {
  if (e.target === this) { closeAnnouncement(); }
});
</script>
@endpush
