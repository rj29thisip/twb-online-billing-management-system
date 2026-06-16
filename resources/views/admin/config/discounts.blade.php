@extends('layouts.app')
@section('title', 'Discount Configuration')
@section('breadcrumb', 'Admin / Configuration / Discounts')
@section('page-title', 'Discount Management')

@section('content')

<div class="section-header">
  <div>
    <h2>Discounts</h2>
    <p>Configure discounts applied during billing</p>
  </div>
  <button class="btn btn-primary" onclick="openModal()">
    <span class="material-icons">add</span> Add Discount
  </button>
</div>

<div class="card">
  <div class="table-wrapper">
    <table>
      <thead>
        <tr>
          <th>Name</th>
          <th>Type</th>
          <th>Value</th>
          <th>Applies To</th>
          <th>Status</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        @forelse($discounts as $discount)
          <tr>
            <td class="td-primary">{{ $discount->name }}</td>
            <td>{{ ucfirst($discount->discount_type) }}</td>
            <td>
              @if($discount->discount_type === 'percent')
                {{ number_format($discount->value, 2) }}%
              @else
                T$ {{ number_format($discount->value, 2) }}
              @endif
            </td>
            <td>{{ ucfirst($discount->applies_to) }}</td>
            <td>
              <span class="badge-status {{ $discount->is_active ? 'badge-active' : 'badge-inactive' }}">
                {{ $discount->is_active ? 'Active' : 'Inactive' }}
              </span>
            </td>
            <td>
              <div style="display:flex;gap:4px;">
                <button class="action-btn" onclick='editDiscount(@json($discount))'>
                  <span class="material-icons">edit</span>
                </button>
                <form action="{{ route('admin.config.discounts.destroy', $discount) }}" method="POST"
                      onsubmit="return confirm('Delete this discount?')">
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
            <td colspan="6">
              <div class="empty-state">
                <span class="material-icons">discount</span>
                <h3>No discounts defined</h3>
              </div>
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>

{{-- MODAL --}}
<div class="modal-overlay" id="discountModal">
  <div class="modal">
    <div class="modal-header">
      <h3 class="modal-title" id="modal-title">Add Discount</h3>
      <button class="modal-close" onclick="closeModal()">
        <span class="material-icons">close</span>
      </button>
    </div>
    <form id="discountForm" action="{{ route('admin.config.discounts.store') }}" method="POST">
      @csrf
      <input type="hidden" name="_method" id="form-method" value="POST">
      <div class="modal-body">
        <div class="form-group">
          <label class="form-label">Discount Name</label>
          <input type="text" name="name" id="f-name" class="form-control"
                 placeholder="e.g. Senior Citizen Discount" required>
        </div>
        <div class="form-grid-2">
          <div class="form-group">
            <label class="form-label">Type</label>
            <select name="discount_type" id="f-type" class="form-control" required>
              <option value="percent">Percent (%)</option>
              <option value="fixed">Fixed Amount (T$)</option>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label">Value</label>
            <input type="number" name="value" id="f-value" class="form-control"
                   step="0.01" min="0" required>
          </div>
        </div>
        <div class="form-group">
          <label class="form-label">Applies To</label>
          <select name="applies_to" id="f-applies" class="form-control" required>
            <option value="all">All Customers</option>
            <option value="individual">Individual Customer</option>
          </select>
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
  document.getElementById('modal-title').textContent = 'Add Discount';
  document.getElementById('discountForm').action = '{{ route("admin.config.discounts.store") }}';
  document.getElementById('form-method').value = 'POST';
  document.getElementById('discountForm').reset();
  document.getElementById('f-active').checked = true;
  document.getElementById('discountModal').classList.add('open');
}
function editDiscount(d) {
  document.getElementById('modal-title').textContent = 'Edit Discount';
  document.getElementById('discountForm').action = `/admin/config/discounts/${d.id}`;
  document.getElementById('form-method').value = 'PUT';
  document.getElementById('f-name').value    = d.name;
  document.getElementById('f-type').value    = d.discount_type;
  document.getElementById('f-value').value   = d.value;
  document.getElementById('f-applies').value = d.applies_to;
  document.getElementById('f-active').checked = !!d.is_active;
  document.getElementById('discountModal').classList.add('open');
}
function closeModal() {
  document.getElementById('discountModal').classList.remove('open');
}
document.getElementById('discountModal').addEventListener('click', function(e) {
  if (e.target === this) closeModal();
});
</script>
@endpush
