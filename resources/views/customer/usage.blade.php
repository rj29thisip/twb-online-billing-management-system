@extends('layouts.app')
@section('title', 'Water Usage')
@section('breadcrumb', 'Customer / Usage')
@section('page-title', 'WATER USAGE')

@push('head')
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
@endpush

@section('content')

<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px;flex-wrap:wrap;gap:12px;">
  <div>
    <h2 style="font-size:18px;font-weight:600;">Usage for {{ $month->format('F Y') }}</h2>
    <p style="color:var(--text-muted);font-size:14px;">Meter: {{ $meter?->meter_id ?? 'No meter assigned' }}</p>
  </div>
  <form method="GET" style="display:flex;gap:8px;align-items:center;">
    <select name="month" class="form-control" onchange="this.form.submit()" style="max-width:160px;">
      @foreach($months as $m)
        <option value="{{ $m['value'] }}" {{ $month->format('Y-m') === $m['value'] ? 'selected' : '' }}>
          {{ $m['label'] }}
        </option>
      @endforeach
    </select>
  </form>
</div>

{{-- SUMMARY CARDS --}}
<div class="stat-cards" style="grid-template-columns:repeat(3,1fr);margin-bottom:24px;">
  <div class="stat-card">
    <div class="stat-icon" style="background:var(--gradient-black);">
      <span class="material-icons">water_drop</span>
    </div>
    <div class="stat-body">
      <div>
        <div class="stat-label">Total Usage</div>
        <div class="stat-value">{{ number_format($dailyReadings->sum('total') / 1000, 2) }} m³</div>
      </div>
    </div>
    <hr class="stat-divider">
    <div class="stat-footer">
      <span class="material-icons">straighten</span>
      <span>{{ number_format($dailyReadings->sum('total'), 0) }} liters</span>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon" style="background:var(--gradient-black);">
      <span class="material-icons">today</span>
    </div>
    <div class="stat-body">
      <div>
        <div class="stat-label">Avg Per Day</div>
        @php $days = $dailyReadings->count() ?: 1; @endphp
        <div class="stat-value">{{ number_format($dailyReadings->sum('total') / $days / 1000, 2) }} m³</div>
      </div>
    </div>
    <hr class="stat-divider">
    <div class="stat-footer">
      <span class="material-icons">bar_chart</span>
      <span>{{ $dailyReadings->count() }} days recorded</span>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon" style="background:var(--gradient-pink);">
      <span class="material-icons">trending_up</span>
    </div>
    <div class="stat-body">
      <div>
        <div class="stat-label">Peak Day</div>
        @php $peak = $dailyReadings->sortByDesc('total')->first(); @endphp
        <div class="stat-value" style="font-size:28px;">
          {{ $peak ? number_format($peak->total / 1000, 2) . ' m³' : '—' }}
        </div>
      </div>
    </div>
    <hr class="stat-divider">
    <div class="stat-footer">
      <span class="material-icons">calendar_today</span>
      <span>{{ $peak ? \Carbon\Carbon::parse($peak->date)->format('d M') : '—' }}</span>
    </div>
  </div>
</div>

{{-- CHART --}}
<div class="card-tight-margin">
  <div class="table-card-header">
    <div class="card-header-float" style="background:var(--gradient-black);">
      <div>
        <h3 id="chart-title">Daily Usage — {{ $month->format('F Y') }}</h3>
        <p id="chart-sub">Click a bar to see hourly breakdown</p>
      </div>
      <div><span class="material-icons" style="color:var(--accent-blue)">bar_chart</span></div>
      <button class="btn btn-outline btn-sm" id="backBtn" style="display:none;" onclick="showDaily()">
        ← Back to Daily
      </button>
    </div>
  </div>
  <div class="card-body" style="padding-top:0;">
    <div class="chart-container" style="height:320px;">
      <canvas id="usageChart"></canvas>
    </div>
  </div>
</div>

{{-- DAILY TABLE --}}
<div class="card">
  <div class="table-card-header">
    <div class="card-header-float" style="background:var(--gradient-dark);">
      <div><h3>Daily Readings</h3><p>{{ $month->format('F Y') }}</p></div>
      <span class="material-icons" style="color:var(--accent-blue)">table_chart</span>
    </div>
  </div>
  <div class="table-wrapper">
    <table>
      <thead>
        <tr><th>Date</th><th>Usage (Liters)</th><th>Usage (m³)</th><th>Meter Value</th></tr>
      </thead>
      <tbody>
        @forelse($dailyReadings as $row)
          <tr>
            <td class="td-primary">{{ \Carbon\Carbon::parse($row->date)->format('d M Y') }}</td>
            <td>{{ number_format($row->total, 0) }} L</td>
            <td>{{ number_format($row->total / 1000, 3) }} m³</td>
            <td style="font-family:monospace;font-size:14px;color:var(--text-muted);">{{ number_format($row->max_value, 0) }}</td>
          </tr>
        @empty
          <tr><td colspan="4"><div class="empty-state"><span class="material-icons">bar_chart</span><h3>No readings for this period</h3></div></td></tr>
        @endforelse
      </tbody>
    </table>
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

var dailyLabels = @json($dailyReadings->pluck('date')->map(fn($d) => \Carbon\Carbon::parse($d)->format('d M'))->values());
var dailyData   = @json($dailyReadings->pluck('total')->map(fn($v) => round((float)$v / 1000, 3))->values());
var rawDates    = @json($dailyReadings->pluck('date')->values());
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
          loadHourly(rawDates[els[0].index]);
        }
      },
      plugins: { legend: { display: false } },
      scales: {
        x: { grid: { color: c.grid }, ticks: { color: c.text } },
        y: { grid: { color: c.grid }, ticks: { color: c.text }, beginAtZero: true },
      }
    }
  });
}

function showDaily() {
  document.getElementById('backBtn').style.display = 'none';
  document.getElementById('chart-title').textContent = 'Daily Usage — {{ $month->format("F Y") }}';
  document.getElementById('chart-sub').textContent = 'Click a bar to see hourly breakdown';
  buildChart(dailyLabels, dailyData, 'Usage (m³)', '#1a73e8');
}

function loadHourly(date) {
  document.getElementById('backBtn').style.display = '';
  document.getElementById('chart-title').textContent = 'Hourly Usage — ' + date;
  document.getElementById('chart-sub').textContent = 'Consumption per hour';
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

buildChart(dailyLabels, dailyData, 'Usage (m³)', '#1a73e8');

// Rebuild chart when theme toggles
var _origToggleThemeUsage = window.toggleTheme;
window.toggleTheme = function() {
  _origToggleThemeUsage();
  if (chartInst) { showDaily(); }
};
</script>
@endpush
