@extends('layouts.app')
@section('title', 'My Dashboard')
@section('breadcrumb', 'Customer / Dashboard')
@section('page-title', 'My Water Account')

@push('head')
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
@endpush

@section('content')

{{-- ── STAT CARDS ──────────────────────────────────────────── --}}
<div class="stat-cards">

  <div class="stat-card">
    <div class="stat-icon" style="background:var(--gradient-blue);">
      <span class="material-icons">water_drop</span>
    </div>
    <div class="stat-body">
      <div>
        <div class="stat-label">Usage This Month</div>
        <div class="stat-value">{{ number_format($usageThisMonth / 1000, 2) }} m³</div>
      </div>
    </div>
    <hr class="stat-divider">
    <div class="stat-footer">
      <span class="material-icons">straighten</span>
      <span>{{ number_format($usageThisMonth, 0) }} litres</span>
    </div>
  </div>

  <div class="stat-card">
    <div class="stat-icon" style="background:var(--gradient-info);">
      <span class="material-icons">speed</span>
    </div>
    <div class="stat-body">
      <div>
        <div class="stat-label">Meter ID</div>
        <div class="stat-value" style="font-size:16px;font-family:monospace;">
          {{ $meter?->meter_id ?? '—' }}
        </div>
      </div>
    </div>
    <hr class="stat-divider">
    <div class="stat-footer">
      <span class="material-icons">fiber_manual_record</span>
      <span style="color:{{ $meter ? 'var(--accent-green)' : 'var(--text-muted)' }}">
        {{ $meter ? ucfirst($meter->status) : 'No meter registered' }}
      </span>
    </div>
  </div>

  <div class="stat-card">
    <div class="stat-icon" style="background:var(--gradient-green);">
      <span class="material-icons">account_circle</span>
    </div>
    <div class="stat-body">
      <div>
        <div class="stat-label">Account Number</div>
        <div class="stat-value" style="font-size:18px;font-family:monospace;">
          {{ $customer->account_number }}
        </div>
      </div>
    </div>
    <hr class="stat-divider">
    <div class="stat-footer">
      <span class="material-icons">location_on</span>
      <span>{{ $customer->block_number ?? 'No block assigned' }}</span>
    </div>
  </div>

  <div class="stat-card">
    <div class="stat-icon" style="background:var(--gradient-dark);">
      <span class="material-icons">calendar_today</span>
    </div>
    <div class="stat-body">
      <div>
        <div class="stat-label">Last Reading</div>
        <div class="stat-value" style="font-size:18px;">
          {{ $latestReading ? $latestReading->capture_time->format('d M Y') : '—' }}
        </div>
      </div>
    </div>
    <hr class="stat-divider">
    <div class="stat-footer">
      <span class="material-icons">data_usage</span>
      <span>
        @if($latestReading)
          {{ number_format($latestReading->value, 0) }} L (meter value)
        @else
          No readings yet
        @endif
      </span>
    </div>
  </div>

</div>

{{-- ── USAGE CHART ─────────────────────────────────────────── --}}
<div class="card">
  <div class="table-card-header">
    <div class="card-header-float" style="background:var(--gradient-blue);">
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

{{-- ── ACCOUNT INFO ────────────────────────────────────────── --}}
<div class="card">
  <div class="table-card-header">
    <div class="card-header-float" style="background:var(--gradient-dark);">
      <div><h3>Account Details</h3><p>Your water service information</p></div>
      <span class="material-icons">person</span>
    </div>
  </div>
  <div class="card-body" style="padding-top:0;display:grid;grid-template-columns:1fr 1fr;gap:0 32px;">
    <div>
      <div class="detail-row"><span class="detail-label">Full Name</span><span class="detail-value">{{ $customer->name }}</span></div>
      <div class="detail-row"><span class="detail-label">Account Number</span><span class="detail-value" style="font-family:monospace;">{{ $customer->account_number }}</span></div>
      <div class="detail-row"><span class="detail-label">Block / Zone</span><span class="detail-value">{{ $customer->block_number ?? '—' }}</span></div>
    </div>
    <div>
      <div class="detail-row"><span class="detail-label">Phone</span><span class="detail-value">{{ $customer->phone ?? '—' }}</span></div>
      <div class="detail-row"><span class="detail-label">Address</span><span class="detail-value">{{ $customer->address ?? '—' }}</span></div>
      <div class="detail-row"><span class="detail-label">Status</span><span class="detail-value"><span class="badge-status {{ $customer->status === 'active' ? 'badge-active' : 'badge-inactive' }}">{{ ucfirst($customer->status ?? 'active') }}</span></span></div>
    </div>
  </div>
</div>

@endsection

@push('scripts')
<script>
Chart.defaults.color = 'rgba(255,255,255,0.5)';
const dailyLabels = @json($usageChart['labels']);
const dailyData   = @json($usageChart['data']);
let chartInst;

function buildChart(labels, data, label, color) {
  if (chartInst) chartInst.destroy();
  const ctx = document.getElementById('usageChart').getContext('2d');
  chartInst = new Chart(ctx, {
    type: 'bar',
    data: {
      labels,
      datasets: [{ label, data, backgroundColor: color + '99', borderColor: color, borderWidth: 1, borderRadius: 4 }]
    },
    options: {
      responsive: true, maintainAspectRatio: false,
      onClick: (e, els) => {
        if (els.length && document.getElementById('backBtn').style.display === 'none') {
          const rawDates = @json($usageChart['rawDates'] ?? []);
          if (rawDates.length) loadHourly(rawDates[els[0].index]);
        }
      },
      plugins: { legend: { display: false } },
      scales: {
        x: { grid: { color: 'rgba(255,255,255,0.05)' } },
        y: { grid: { color: 'rgba(255,255,255,0.05)' }, beginAtZero: true }
      }
    }
  });
}

function showDaily() {
  document.getElementById('backBtn').style.display = 'none';
  document.getElementById('chart-subtitle').textContent = 'Daily consumption — click a bar to see hourly breakdown';
  buildChart(dailyLabels, dailyData, 'Usage (L)', '#1a73e8');
}

async function loadHourly(date) {
  document.getElementById('backBtn').style.display = '';
  document.getElementById('chart-subtitle').textContent = 'Hourly usage — ' + date;
  const res  = await fetch(`{{ route('customer.usage.hourly') }}?date=${date}`);
  const json = await res.json();
  buildChart(json.labels, json.data, 'Usage (L)', '#26c6da');
}

buildChart(dailyLabels, dailyData, 'Usage (L)', '#1a73e8');
</script>
@endpush
