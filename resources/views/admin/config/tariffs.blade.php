{{-- resources/views/admin/config/tariffs.blade.php --}}
@extends('layouts.app')
@section('title', 'Tariff Configuration')
@section('breadcrumb', 'Admin / Configuration / Tariffs')
@section('page-title', 'TARIFF MANAGEMENT')

@section('content')

@php $activeTab = session('active_tab', 'residential'); @endphp

<div class="section-header">
  <div>
    <h2>Tariff Tiers</h2>
    <p>Add and configure tariff tiers applied during billing fore Residential and Commercial account types</p>
  </div>
  <button class="btn btn-primary" onclick="openAddModal()">
    <span class="material-icons">add</span> Add Tier
  </button>
</div>

{{-- ── Tab switcher ────────────────────────────────────────────── --}}
<div class="tariff-tabs" style="display:flex;gap:0;margin-bottom:24px;border-bottom:2px solid var(--border);">
  <button class="tariff-tab-btn {{ $activeTab==='residential' ? 'active' : '' }}"
          onclick="switchTab('residential')" id="tab-residential"
          style="padding:10px 28px;background:none;border:none;cursor:pointer;font-size:14px;font-weight:600;
                 color:{{ $activeTab==='residential' ? 'var(--accent-blue)' : 'var(--text-muted)' }};
                 border-bottom:3px solid {{ $activeTab==='residential' ? 'var(--accent-blue)' : 'transparent' }};
                 margin-bottom:-2px;transition:all .2s;">
    <span class="material-icons" style="vertical-align:middle;font-size:18px;margin-right:6px;">home</span>
    Residential
    <span style="margin-left:8px;padding:2px 8px;border-radius:12px;font-size:14px;
                 background:rgba(56,189,248,.15);color:var(--accent-blue);">
      {{ $residential->count() }}
    </span>
  </button>
  <button class="tariff-tab-btn {{ $activeTab==='commercial' ? 'active' : '' }}"
          onclick="switchTab('commercial')" id="tab-commercial"
          style="padding:10px 28px;background:none;border:none;cursor:pointer;font-size:14px;font-weight:600;
                 color:{{ $activeTab==='commercial' ? 'var(--accent-teal)' : 'var(--text-muted)' }};
                 border-bottom:3px solid {{ $activeTab==='commercial' ? 'var(--accent-teal)' : 'transparent' }};
                 margin-bottom:-2px;transition:all .2s;">
    <span class="material-icons" style="vertical-align:middle;font-size:18px;margin-right:6px;">business</span>
    Commercial
    <span style="margin-left:8px;padding:2px 8px;border-radius:12px;font-size:14px;
                 background:rgba(20,184,166,.15);color:var(--accent-teal);">
      {{ $commercial->count() }}
    </span>
  </button>
</div>

{{-- ── RESIDENTIAL TAB ─────────────────────────────────────────── --}}
<div id="pane-residential" class="tariff-pane" style="{{ $activeTab!=='residential' ? 'display:none;' : '' }}">

  <div style="display:grid;grid-template-columns:1fr 360px;gap:24px;align-items:start;">

    {{-- Table --}}
    <div class="card">
      <div style="padding:16px 20px 0;display:flex;align-items:center;gap:12px;">
        <span class="material-icons" style="color:var(--accent-blue);">home</span>
        <div>
          <h3 style="margin:0;font-size:16px;">Residential Tariff Tiers</h3>
          <p style="margin:2px 0 0;font-size:14px;color:var(--text-muted);">Applied to meters with type: Residential</p>
        </div>
      </div>
      <div class="table-wrapper">
        <table>
          <thead>
            <tr>
              <th>Tier Name</th><th>Min (m³)</th><th>Max (m³)</th>
              <th>Rate / m³</th><th>Effective From</th><th>Effective To</th>
              <th>Status</th><th>Actions</th>
            </tr>
          </thead>
          <tbody>
            @forelse($residential as $tier)
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
                    <button class="action-btn" title="Edit" onclick='editTariff(@json($tier))'>
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
              <tr><td colspan="8">
                <div class="empty-state">
                  <span class="material-icons">layers</span>
                  <h3>No residential tiers defined</h3>
                  <p>Click "Add Tier" and choose Residential</p>
                </div>
              </td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>

    {{-- Residential preview card --}}
    <div class="card" style="padding:20px;">
      <h3 style="font-size:16px;margin:0 0 16px;display:flex;align-items:center;gap:8px;">
        <span class="material-icons" style="font-size:18px;color:var(--accent-blue);">calculate</span>
        Residential Billing Preview
      </h3>
      @php
        $resTiers = $residential->where('is_active', true)->sortBy('min_units');
        $examples = [50, 150, 350];
      @endphp
      @foreach($examples as $usage)
        @php
          $charge    = 0;
          $remaining = $usage;
          foreach ($resTiers as $t) {
              if ($remaining <= 0) break;
              $tMax  = $t->max_units ? (float)$t->max_units : PHP_INT_MAX;
              $units = min($remaining, $tMax - (float)$t->min_units);
              if ($units <= 0) continue;
              $charge    += $units * (float)$t->rate_per_unit;
              $remaining -= $units;
          }
        @endphp
        <div style="display:flex;justify-content:space-between;align-items:center;
                    padding:10px 14px;margin-bottom:8px;border-radius:8px;
                    background:rgba(56,189,248,.07);border:1px solid rgba(56,189,248,.15);">
          <span style="font-size:14px;color:var(--text-muted);">{{ $usage }} m³</span>
          <span style="font-weight:700;color:var(--accent-blue);">T$ {{ number_format($charge, 2) }}</span>
        </div>
      @endforeach
      <p style="font-size:12px;color:var(--text-muted);margin:12px 0 0;">
        Preview is before tax. Based on active tiers only.
      </p>
    </div>

  </div>
</div>

{{-- ── COMMERCIAL TAB ──────────────────────────────────────────── --}}
<div id="pane-commercial" class="tariff-pane" style="{{ $activeTab!=='commercial' ? 'display:none;' : '' }}">

  <div style="display:grid;grid-template-columns:1fr 360px;gap:24px;align-items:start;">

    {{-- Table --}}
    <div class="card">
      <div style="padding:16px 20px 0;display:flex;align-items:center;gap:12px;">
        <span class="material-icons" style="color:var(--accent-teal);">business</span>
        <div>
          <h3 style="margin:0;font-size:16px;">Commercial Tariff Tiers</h3>
          <p style="margin:2px 0 0;font-size:14px;color:var(--text-muted);">Applied to meters with type: Commercial or Industrial</p>
        </div>
      </div>
      <div class="table-wrapper">
        <table>
          <thead>
            <tr>
              <th>Tier Name</th><th>Min (m³)</th><th>Max (m³)</th>
              <th>Rate / m³</th><th>Effective From</th><th>Effective To</th>
              <th>Status</th><th>Actions</th>
            </tr>
          </thead>
          <tbody>
            @forelse($commercial as $tier)
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
                    <button class="action-btn" title="Edit" onclick='editTariff(@json($tier))'>
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
              <tr><td colspan="8">
                <div class="empty-state">
                  <span class="material-icons">layers</span>
                  <h3>No commercial tiers defined</h3>
                  <p>Click "Add Tier" and choose Commercial</p>
                </div>
              </td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>

    {{-- Commercial preview card --}}
    <div class="card" style="padding:20px;">
      <h3 style="font-size:16px;margin:0 0 16px;display:flex;align-items:center;gap:8px;">
        <span class="material-icons" style="font-size:18px;color:var(--accent-teal);">calculate</span>
        Commercial Billing Preview
      </h3>
      @php $comTiers = $commercial->where('is_active', true)->sortBy('min_units'); @endphp
      @foreach($examples as $usage)
        @php
          $charge    = 0;
          $remaining = $usage;
          foreach ($comTiers as $t) {
              if ($remaining <= 0) break;
              $tMax  = $t->max_units ? (float)$t->max_units : PHP_INT_MAX;
              $units = min($remaining, $tMax - (float)$t->min_units);
              if ($units <= 0) continue;
              $charge    += $units * (float)$t->rate_per_unit;
              $remaining -= $units;
          }
        @endphp
        <div style="display:flex;justify-content:space-between;align-items:center;
                    padding:10px 14px;margin-bottom:8px;border-radius:8px;
                    background:rgba(20,184,166,.07);border:1px solid rgba(20,184,166,.15);">
          <span style="font-size:14px;color:var(--text-muted);">{{ $usage }} m³</span>
          <span style="font-weight:700;color:var(--accent-teal);">T$ {{ number_format($charge, 2) }}</span>
        </div>
      @endforeach
      <p style="font-size:12px;color:var(--text-muted);margin:12px 0 0;">
        Preview is before tax. Based on active tiers only.
      </p>
    </div>

  </div>
</div>

{{-- ── ADD / EDIT MODAL ────────────────────────────────────────── --}}
<div id="tariffModal" class="modal-overlay" style="display:none;">
  <div class="modal" style="max-width:520px;">
    <div class="modal-header">
      <h3 id="modalTitle">Add Tariff Tier</h3>
      <button class="modal-close" onclick="closeTariffModal()">
        <span class="material-icons">close</span>
      </button>
    </div>

    <form id="tariffForm" method="POST" action="{{ route('admin.config.tariffs.store') }}">
      @csrf
      <span id="methodField"></span>

      <div class="modal-body" style="display:grid;gap:16px;">

        {{-- Category toggle --}}
        <div class="form-group">
          <label class="form-label">Category <span style="color:var(--accent-pink)">*</span></label>
          <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;" id="categoryToggle">
            <label id="lbl-residential" style="display:flex;align-items:center;gap:10px;padding:12px 16px;
                   border-radius:8px;cursor:pointer;border:2px solid var(--accent-blue);
                   background:rgba(56,189,248,.08);transition:all .2s;">
              <input type="radio" name="category" value="residential" checked
                     onchange="onCategoryChange('residential')" style="accent-color:var(--accent-blue);">
              <span class="material-icons" style="font-size:18px;color:var(--accent-blue);">home</span>
              <span style="font-weight:600;font-size:14px;">Residential</span>
            </label>
            <label id="lbl-commercial" style="display:flex;align-items:center;gap:10px;padding:12px 16px;
                   border-radius:8px;cursor:pointer;border:2px solid var(--border);
                   background:transparent;transition:all .2s;">
              <input type="radio" name="category" value="commercial"
                     onchange="onCategoryChange('commercial')" style="accent-color:var(--accent-teal);">
              <span class="material-icons" style="font-size:18px;color:var(--text-muted);" id="ico-commercial">business</span>
              <span style="font-weight:600;font-size:14px;">Commercial</span>
            </label>
          </div>
        </div>

        <div class="form-group">
          <label class="form-label">Tier Name <span style="color:var(--accent-pink)">*</span></label>
          <input type="text" name="name" id="f_name" class="form-control"
                 placeholder="e.g. Residential — Tier 1 (0–100 m³)" required>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
          <div class="form-group">
            <label class="form-label">Min Units (m³) <span style="color:var(--accent-pink)">*</span></label>
            <input type="number" name="min_units" id="f_min" class="form-control"
                   step="0.0001" min="0" value="0" required>
          </div>
          <div class="form-group">
            <label class="form-label">Max Units (m³)</label>
            <input type="number" name="max_units" id="f_max" class="form-control"
                   step="0.0001" min="0" placeholder="Leave blank = unlimited">
          </div>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
          <div class="form-group">
            <label class="form-label">Rate per m³ (T$) <span style="color:var(--accent-pink)">*</span></label>
            <input type="number" name="rate_per_unit" id="f_rate" class="form-control"
                   step="0.0001" min="0" required>
          </div>
          <div class="form-group">
            <label class="form-label">Unit Type</label>
            <select name="unit_type" id="f_unit" class="form-control">
              <option value="cubicmeter" selected>Cubic Meter (m³)</option>
              <option value="liter">Liter (L)</option>
            </select>
          </div>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
          <div class="form-group">
            <label class="form-label">Effective From <span style="color:var(--accent-pink)">*</span></label>
            <input type="date" name="effective_from" id="f_from" class="form-control" required>
          </div>
          <div class="form-group">
            <label class="form-label">Effective To</label>
            <input type="date" name="effective_to" id="f_to" class="form-control">
          </div>
        </div>

        <div class="form-group">
          <label style="display:flex;align-items:center;gap:10px;cursor:pointer;">
            <input type="checkbox" name="is_active" id="f_active" value="1" checked
                   style="width:16px;height:16px;accent-color:var(--accent-blue);">
            <span class="form-label" style="margin:0;">Active</span>
          </label>
        </div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" onclick="closeTariffModal()">Cancel</button>
        <button type="submit" class="btn btn-primary" id="modalSubmitBtn">Save Tier</button>
      </div>
    </form>
  </div>
</div>

@endsection

@push('scripts')
<script>
const STORE_URL  = "{{ route('admin.config.tariffs.store') }}";
const UPDATE_BASE = "{{ url('admin/config/tariffs') }}";

function switchTab(tab) {
  ['residential','commercial'].forEach(t => {
    document.getElementById('pane-' + t).style.display = t === tab ? '' : 'none';
    const btn = document.getElementById('tab-' + t);
    const isActive = t === tab;
    btn.style.color        = isActive ? (t==='residential' ? 'var(--accent-blue)' : 'var(--accent-teal)') : 'var(--text-muted)';
    btn.style.borderBottom = isActive ? '3px solid ' + (t==='residential' ? 'var(--accent-blue)' : 'var(--accent-teal)') : '3px solid transparent';
  });
  // Pre-select category in modal
  document.querySelector(`input[name="category"][value="${tab}"]`).checked = true;
  onCategoryChange(tab);
}

function onCategoryChange(cat) {
  const isRes = cat === 'residential';
  const lblR  = document.getElementById('lbl-residential');
  const lblC  = document.getElementById('lbl-commercial');
  lblR.style.borderColor  = isRes ? 'var(--accent-blue)' : 'var(--border)';
  lblR.style.background   = isRes ? 'rgba(56,189,248,.08)' : 'transparent';
  lblC.style.borderColor  = !isRes ? 'var(--accent-teal)' : 'var(--border)';
  lblC.style.background   = !isRes ? 'rgba(20,184,166,.08)' : 'transparent';
  document.getElementById('ico-commercial').style.color = !isRes ? 'var(--accent-teal)' : 'var(--text-muted)';
}

function openAddModal() {
  document.getElementById('modalTitle').textContent = 'Add Tariff Tier';
  document.getElementById('tariffForm').action = STORE_URL;
  document.getElementById('methodField').innerHTML = '';
  document.getElementById('modalSubmitBtn').textContent = 'Save Tier';
  // Reset fields
  ['f_name','f_max','f_to'].forEach(id => document.getElementById(id).value = '');
  document.getElementById('f_min').value  = '0';
  document.getElementById('f_rate').value = '';
  document.getElementById('f_from').value = new Date().toISOString().slice(0,10);
  document.getElementById('f_unit').value = 'cubicmeter';
  document.getElementById('f_active').checked = true;
  // Pre-select active tab's category
  const activeTab = document.querySelector('.tariff-pane:not([style*="display:none"])');
  const cat = activeTab ? activeTab.id.replace('pane-', '') : 'residential';
  document.querySelector(`input[name="category"][value="${cat}"]`).checked = true;
  onCategoryChange(cat);
  document.getElementById('tariffModal').style.display = 'flex';
}

function editTariff(tier) {
  document.getElementById('modalTitle').textContent = 'Edit Tariff Tier';
  document.getElementById('tariffForm').action      = `${UPDATE_BASE}/${tier.id}`;
  document.getElementById('methodField').innerHTML  = '@csrf<input type="hidden" name="_method" value="PUT">';
  document.getElementById('modalSubmitBtn').textContent = 'Update Tier';
  document.getElementById('f_name').value  = tier.name;
  document.getElementById('f_min').value   = tier.min_units;
  document.getElementById('f_max').value   = tier.max_units ?? '';
  document.getElementById('f_rate').value  = tier.rate_per_unit;
  document.getElementById('f_unit').value  = tier.unit_type;
  document.getElementById('f_from').value  = tier.effective_from ? tier.effective_from.slice(0,10) : '';
  document.getElementById('f_to').value    = tier.effective_to   ? tier.effective_to.slice(0,10)   : '';
  document.getElementById('f_active').checked = !!tier.is_active;
  document.querySelector(`input[name="category"][value="${tier.category}"]`).checked = true;
  onCategoryChange(tier.category);
  document.getElementById('tariffModal').style.display = 'flex';
}

function closeTariffModal() {
  document.getElementById('tariffModal').style.display = 'none';
}

document.getElementById('tariffModal').addEventListener('click', function(e) {
  if (e.target === this) closeTariffModal();
});
</script>
@endpush
