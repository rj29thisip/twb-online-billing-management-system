@extends('layouts.app')
@section('title', 'Tax Configuration')
@section('breadcrumb', 'Admin / Configuration / Taxes')
@section('page-title', 'Tax Rate Management')

@section('content')

<div class="section-header">
  <div>
    <h2>Tax Rates</h2>
    <p>Configure tax rates applied during billing</p>
  </div>
  <button class="btn btn-primary" onclick="openModal()">
    <span class="material-icons">add</span> Add Tax Rate
  </button>
</div>

<div class="card">
  <div class="table-wrapper">
    <table>
      <thead>
        <tr>
          <th>Name</th>
          <th>Rate (%)</th>
          <th>Effective From</th>
          <th>Status</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        @forelse($taxes as $tax)
          <tr>
            <td class="td-primary">{{ $tax->name }}</td>
            <td>{{ number_format($tax->rate_percent, 2) }}%</td>
            <td>{{ $tax->effective_from->format('d M Y') }}</td>
            <td>
              <span class="badge-status {{ $tax->is_active ? 'badge-active' : 'badge-inactive' }}">
                {{ $tax->is_active ? 'Active' : 'Inactive' }}
              </span>
            </td>
            <td>
              <div style="display:flex;gap:4px;">
                <button class="action-btn" onclick='editTax(@json($tax))'>
                  <span class="material-icons">edit</span>
                </button>
                <form action="{{ route('admin.config.taxes.destroy', $tax) }}" method="POST"
                      onsubmit="return confirm('Delete this tax rate?')">
                  @csrf @method('DELETE')
                  <button type="submit" class="action-btn" style="color:var(--accent-pink);">
                    <span class="material-icons">delete</span>
                  </button>
                </form>
              </div>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="5">
              <div class="empty-state">
                <span class="material-icons">percent</span>
                <h3>No tax rates defined</h3>
              </div>
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>

{{-- MODAL --}}
<div class="modal-overlay" id="taxModal">
  <div class="modal">
    <div class="modal-header">
      <h3 class="modal-title" id="modal-title">Add Tax Rate</h3>
      <button class="modal-close" onclick="closeModal()">
        <span class="material-icons">close</span>
      </button>
    </div>
    <form id="taxForm" action="{{ route('admin.config.taxes.store') }}" method="POST">
      @csrf
      <input type="hidden" name="_method" id="form-method" value="POST">
      <div class="modal-body">
        <div class="form-group">
          <label class="form-label">Tax Name</label>
          <input type="text" name="name" id="f-name" class="form-control"
                 placeholder="e.g. VAT, GST" required>
        </div>
        <div class="form-grid-2">
          <div class="form-group">
            <label class="form-label">Rate (%)</label>
            <input type="number" name="rate_percent" id="f-rate" class="form-control"
                   step="0.01" min="0" max="100" required>
          </div>
          <div class="form-group">
            <label class="form-label">Effective From</label>
            <input type="date" name="effective_from" id="f-from" class="form-control" required>
          </div>
        </div>
        <div class="form-group">
          <label class="checkbox-label" style="cursor:pointer;">
            <input type="checkbox" name="is_active" id="f-active" value="1" checked>
            <span style="font-size:13px;color:var(--text-secondary);">Active</span>
          </label>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline" onclick="closeModal()">Cancel</button>
        <button type="submit" class="btn btn-primary">
          <span class="material-icons">save</span> Save
        </button>
      </div>
    </form>
  </div>
</div>

@endsection

@push('scripts')
<script>
function openModal() {
  document.getElementById('modal-title').textContent = 'Add Tax Rate';
  document.getElementById('taxForm').action = '{{ route("admin.config.taxes.store") }}';
  document.getElementById('form-method').value = 'POST';
  document.getElementById('taxForm').reset();
  document.getElementById('f-active').checked = true;
  document.getElementById('taxModal').classList.add('open');
}
function editTax(tax) {
  document.getElementById('modal-title').textContent = 'Edit Tax Rate';
  document.getElementById('taxForm').action = `/admin/config/taxes/${tax.id}`;
  document.getElementById('form-method').value = 'PUT';
  document.getElementById('f-name').value   = tax.name;
  document.getElementById('f-rate').value   = tax.rate_percent;
  document.getElementById('f-from').value   = tax.effective_from;
  document.getElementById('f-active').checked = !!tax.is_active;
  document.getElementById('taxModal').classList.add('open');
}
function closeModal() {
  document.getElementById('taxModal').classList.remove('open');
}
document.getElementById('taxModal').addEventListener('click', function(e) {
  if (e.target === this) closeModal();
});
</script>
@endpush
