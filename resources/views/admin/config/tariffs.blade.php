{{-- resources/views/admin/config/tariffs.blade.php --}}
@extends('layouts.app')
@section('title', 'Tariff Configuration')
@section('breadcrumb', 'Admin / Configuration / Tariffs')
@section('page-title', 'Tariff Management')

@section('content')

<div class="section-header">
  <div>
    <h2>Tariff Tiers</h2>
    <p>Configure tiered water pricing rates</p>
  </div>
  <button class="btn btn-primary" onclick="openTariffModal()">
    <span class="material-icons">add</span> Add Tier
  </button>
</div>

<div style="display:grid;grid-template-columns:1fr 380px;gap:24px;align-items:start;">

  {{-- TARIFF TABLE --}}
  <div class="card">
    <div class="table-wrapper">
      <table>
        <thead>
          <tr>
            <th>Tier Name</th>
            <th>Min Units (m³)</th>
            <th>Max Units (m³)</th>
            <th>Rate / m³</th>
            <th>Effective From</th>
            <th>Effective To</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          @forelse($tiers as $tier)
            <tr>
              <td class="td-primary">{{ $tier->name }}</td>
              <td>{{ number_format($tier->min_units, 2) }}</td>
              <td>{{ $tier->max_units ? number_format($tier->max_units, 2) : '∞' }}</td>
              <td class="td-primary">T$ {{ number_format($tier->rate_per_unit, 4) }}</td>
              <td>{{ $tier->effective_from->format('d M Y') }}</td>
              <td>{{ $tier->effective_to?->format('d M Y') ?? '—' }}</td>
              <td>
                <span class="badge-status {{ $tier->is_active ? 'badge-active' : 'badge-inactive' }}">
                  {{ $tier->is_active ? 'Active' : 'Inactive' }}
                </span>
              </td>
              <td>
                <div style="display:flex;gap:4px;">
                  <button class="action-btn" title="Edit"
                          onclick='editTariff(@json($tier))'>
                    <span class="material-icons">edit</span>
                  </button>
                  <form action="{{ route('admin.config.tariffs.destroy', $tier) }}" method="POST"
                        onsubmit="return confirm('Delete this tariff tier?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="action-btn" title="Delete" style="color:var(--accent-pink)">
                      <span class="material-icons">delete</span>
                    </button>
                  </form>
                </div>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="8">
                <div class="empty-state">
                  <span class="material-icons">layers</span>
                  <h3>No tariff tiers defined</h3>
                </div>
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

  {{-- HOW TIERS WORK --}}
  <div class="card">
    <div class="card-body">
      <h3 style="font-size:14px;font-weight:600;margin-bottom:16px;display:flex;align-items:center;gap:8px;">
        <span class="material-icons" style="color:var(--accent-blue)">info</span>
        How Tiered Billing Works
      </h3>

      <div style="font-size:13px;color:var(--text-secondary);line-height:1.7;margin-bottom:16px;">
        Consumption is billed progressively. Lower tiers have cheaper rates for essential usage;
        higher tiers have higher rates for greater consumption.
      </div>

      {{-- Example --}}
      <div style="background:rgba(26,115,232,0.06);border:1px solid rgba(26,115,232,0.15);border-radius:8px;padding:14px;">
        <div style="font-size:12px;font-weight:600;text-transform:uppercase;letter-spacing:0.5px;color:var(--text-muted);margin-bottom:10px;">Example: 25 m³ consumption</div>

        @php
          $exampleTiers = $tiers->where('is_active', true)->values();
          $remaining = 25;
          $exTotal = 0;
        @endphp

        @foreach($exampleTiers as $t)
          @php
            if ($remaining <= 0) break;
            $tMin = (float) $t->min_units;
            $tMax = $t->max_units ? (float)$t->max_units : PHP_FLOAT_MAX;
            $tRange = $tMax - $tMin;
            $units = min($remaining, $tRange);
            $amount = $units * (float)$t->rate_per_unit;
            $exTotal += $amount;
            $remaining -= $units;
          @endphp
          @if($units > 0)
            <div style="display:flex;justify-content:space-between;padding:6px 0;border-bottom:1px solid var(--border);font-size:12px;">
              <span style="color:var(--text-secondary);">{{ $t->name }} ({{ number_format($units,2) }} m³ × T${{ $t->rate_per_unit }})</span>
              <span style="color:var(--text-primary);font-weight:500;">T$ {{ number_format($amount, 2) }}</span>
            </div>
          @endif
        @endforeach

        @if($exampleTiers->isEmpty())
          <div style="color:var(--text-muted);font-size:12px;">Add tiers to see an example calculation.</div>
        @else
          <div style="display:flex;justify-content:space-between;padding:8px 0 0;font-size:13px;font-weight:600;">
            <span>Total (before tax)</span>
            <span style="color:var(--accent-blue);">T$ {{ number_format($exTotal, 2) }}</span>
          </div>
        @endif
      </div>
    </div>
  </div>

</div>

{{-- ── ADD/EDIT MODAL ───────────────────────────────────────── --}}
<div class="modal-overlay" id="tariffModal">
  <div class="modal">
    <div class="modal-header">
      <h3 class="modal-title" id="tariff-modal-title">Add Tariff Tier</h3>
      <button class="modal-close" onclick="closeTariffModal()">
        <span class="material-icons">close</span>
      </button>
    </div>
    <form id="tariffForm" action="{{ route('admin.config.tariffs.store') }}" method="POST">
      @csrf
      <input type="hidden" name="_method" id="tariff-method" value="POST">
      <input type="hidden" name="tier_id" id="tariff-id">

      <div class="modal-body">
        <div class="form-group">
          <label class="form-label">Tier Name</label>
          <input type="text" name="name" id="t-name" class="form-control"
                 placeholder="e.g. Tier 1 – Basic Usage" required>
        </div>
        <div class="form-grid-2">
          <div class="form-group">
            <label class="form-label">Min Units (m³)</label>
            <input type="number" name="min_units" id="t-min" class="form-control" step="0.0001" min="0" value="0" required>
          </div>
          <div class="form-group">
            <label class="form-label">Max Units (m³) — leave blank for unlimited</label>
            <input type="number" name="max_units" id="t-max" class="form-control" step="0.0001" min="0" placeholder="∞">
          </div>
        </div>
        <div class="form-grid-2">
          <div class="form-group">
            <label class="form-label">Rate per m³ (T$)</label>
            <input type="number" name="rate_per_unit" id="t-rate" class="form-control" step="0.0001" min="0" required>
          </div>
          <div class="form-group">
            <label class="form-label">Unit Type</label>
            <select name="unit_type" id="t-unit" class="form-control">
              <option value="cubicmeter">Cubic Meter (m³)</option>
              <option value="liter">Liter (L)</option>
            </select>
          </div>
        </div>
        <div class="form-grid-2">
          <div class="form-group">
            <label class="form-label">Effective From</label>
            <input type="date" name="effective_from" id="t-from" class="form-control" required>
          </div>
          <div class="form-group">
            <label class="form-label">Effective To (optional)</label>
            <input type="date" name="effective_to" id="t-to" class="form-control">
          </div>
        </div>
        <div class="form-group">
          <label class="checkbox-label" style="cursor:pointer;">
            <input type="checkbox" name="is_active" id="t-active" value="1" checked>
            <span style="font-size:13px;color:var(--text-secondary);">Active (used in billing calculations)</span>
          </label>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline" onclick="closeTariffModal()">Cancel</button>
        <button type="submit" class="btn btn-primary">
          <span class="material-icons">save</span> Save Tier
        </button>
      </div>
    </form>
  </div>
</div>

@endsection

@push('scripts')
<script>
function openTariffModal() {
  document.getElementById('tariff-modal-title').textContent = 'Add Tariff Tier';
  document.getElementById('tariffForm').action = '{{ route("admin.config.tariffs.store") }}';
  document.getElementById('tariff-method').value = 'POST';
  document.getElementById('tariffForm').reset();
  document.getElementById('t-active').checked = true;
  document.getElementById('tariffModal').classList.add('open');
}
function editTariff(tier) {
  document.getElementById('tariff-modal-title').textContent = 'Edit Tariff Tier';
  document.getElementById('tariffForm').action = `/admin/config/tariffs/${tier.id}`;
  document.getElementById('tariff-method').value = 'PUT';
  document.getElementById('tariff-id').value    = tier.id;
  document.getElementById('t-name').value        = tier.name;
  document.getElementById('t-min').value         = tier.min_units;
  document.getElementById('t-max').value         = tier.max_units || '';
  document.getElementById('t-rate').value        = tier.rate_per_unit;
  document.getElementById('t-unit').value        = tier.unit_type;
  document.getElementById('t-from').value        = tier.effective_from;
  document.getElementById('t-to').value          = tier.effective_to || '';
  document.getElementById('t-active').checked    = !!tier.is_active;
  document.getElementById('tariffModal').classList.add('open');
}
function closeTariffModal() {
  document.getElementById('tariffModal').classList.remove('open');
}
document.getElementById('tariffModal').addEventListener('click', function(e) {
  if (e.target === this) closeTariffModal();
});
</script>
@endpush
