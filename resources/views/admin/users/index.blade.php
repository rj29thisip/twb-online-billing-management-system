@extends('layouts.app')
@section('title', 'User Management')
@section('breadcrumb', 'Admin / System / Users')
@section('page-title', 'User Management')

@section('content')

<div class="section-header">
  <div>
    <h2>System Users</h2>
    <p>Manage admin, officer, and customer portal accounts</p>
  </div>
  <button class="btn btn-primary" onclick="openModal()">
    <span class="material-icons">person_add</span> Add Staff User
  </button>
</div>

<div class="card">
  <form method="GET" class="filter-bar">
    <input type="text" name="search" class="form-control" placeholder="Name, email..."
           value="{{ request('search') }}">
    <select name="role" class="form-control">
      <option value="">All Roles</option>
      <option value="admin"    {{ request('role') === 'admin'    ? 'selected' : '' }}>Admin</option>
      <option value="officer"  {{ request('role') === 'officer'  ? 'selected' : '' }}>Officer</option>
      <option value="customer" {{ request('role') === 'customer' ? 'selected' : '' }}>Customer</option>
    </select>
    <button type="submit" class="btn btn-outline btn-sm">
      <span class="material-icons">filter_list</span> Filter
    </button>
    @if(request()->hasAny(['search','role']))
      <a href="{{ route('admin.users.index') }}" class="btn btn-outline btn-sm">
        <span class="material-icons">clear</span> Clear
      </a>
    @endif
  </form>

  <div class="table-wrapper">
    <table>
      <thead>
        <tr>
          <th>User</th>
          <th>Role</th>
          <th>Customer Account</th>
          <th>Status</th>
          <th>Created</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        @forelse($users as $user)
          <tr>
            <td>
              <div class="user-cell">
                <div class="u-avatar" style="background:var(--gradient-{{ $user->isAdmin() ? 'pink' : ($user->isOfficer() ? 'blue' : 'dark') }});color:#fff;font-size:11px;">
                  {{ substr($user->name, 0, 2) }}
                </div>
                <div>
                  <div class="u-name">{{ $user->name }}</div>
                  <div class="u-sub">{{ $user->email }}</div>
                </div>
              </div>
            </td>
            <td>
              <span class="badge-status {{ match($user->role) {
                'admin'    => 'badge-overdue',
                'officer'  => 'badge-issued',
                'customer' => 'badge-active',
                default    => 'badge-draft'
              } }}">
                {{ ucfirst($user->role) }}
              </span>
            </td>
            <td>
              @if($user->customer)
                <a href="{{ route('admin.customers.show', $user->customer) }}" style="color:var(--accent-blue);font-size:13px;">
                  {{ $user->customer->account_number }}
                </a>
              @else
                <span style="color:var(--text-muted);">—</span>
              @endif
            </td>
            <td>
              <span class="badge-status {{ $user->is_active ? 'badge-active' : 'badge-inactive' }}">
                {{ $user->is_active ? 'Active' : 'Inactive' }}
              </span>
            </td>
            <td style="color:var(--text-muted);font-size:12px;">
              {{ $user->created_at->format('d M Y') }}
            </td>
            <td>
              <div style="display:flex;gap:4px;">
                @if($user->id !== auth()->id() && $user->isStaff())
                  <form action="{{ route('admin.users.destroy', $user) }}" method="POST"
                        onsubmit="return confirm('{{ $user->is_active ? 'Deactivate' : 'Activate' }} this user?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="action-btn"
                            title="{{ $user->is_active ? 'Deactivate' : 'Activate' }}"
                            style="{{ $user->is_active ? 'color:var(--accent-pink)' : 'color:var(--accent-green)' }}">
                      <span class="material-icons">{{ $user->is_active ? 'block' : 'check_circle' }}</span>
                    </button>
                  </form>
                @elseif($user->id === auth()->id())
                  <span style="font-size:11px;color:var(--text-muted);padding:4px 8px;">(you)</span>
                @endif
              </div>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="6">
              <div class="empty-state">
                <span class="material-icons">manage_accounts</span>
                <h3>No users found</h3>
              </div>
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <div class="pagination">
    <div class="pagination-info">
      Showing {{ $users->firstItem() }}–{{ $users->lastItem() }} of {{ $users->total() }}
    </div>
    <div class="pagination-btns">
      @if(!$users->onFirstPage())
        <a href="{{ $users->previousPageUrl() }}" class="pg-btn"><span class="material-icons">chevron_left</span></a>
      @endif
      @foreach($users->getUrlRange(max(1,$users->currentPage()-2), min($users->lastPage(),$users->currentPage()+2)) as $page => $url)
        <a href="{{ $url }}" class="pg-btn {{ $page === $users->currentPage() ? 'active' : '' }}">{{ $page }}</a>
      @endforeach
      @if($users->hasMorePages())
        <a href="{{ $users->nextPageUrl() }}" class="pg-btn"><span class="material-icons">chevron_right</span></a>
      @endif
    </div>
  </div>
</div>

{{-- ── ADD STAFF MODAL — 2x wider with 2-column layout ──────────── --}}
<div class="modal-overlay" id="userModal">
  <div class="modal" style="max-width:700px;">
    <div class="modal-header">
      <div>
        <h3 class="modal-title">Add Staff User</h3>
        <p style="font-size:12px;color:var(--text-muted);margin-top:3px;">
          Create a new admin or officer account
        </p>
      </div>
      <button class="modal-close" onclick="closeModal()">
        <span class="material-icons">close</span>
      </button>
    </div>

    <form action="{{ route('admin.users.store') }}" method="POST">
      @csrf
      <div class="modal-body">

        {{-- ROW 1 --}}
        <div class="form-grid-2">
          <div class="form-group">
            <label class="form-label">Full Name <span style="color:var(--accent-pink)">*</span></label>
            <input type="text" name="name" class="form-control" required
                   placeholder="e.g. John Smith"
                   style="font-size:14px;padding:11px 14px;">
          </div>
          <div class="form-group">
            <label class="form-label">Role <span style="color:var(--accent-pink)">*</span></label>
            <select name="role" class="form-control" required
                    style="font-size:14px;padding:11px 14px;">
              <option value="officer">Billing Officer</option>
              <option value="admin">Administrator</option>
            </select>
          </div>
        </div>

        {{-- ROW 2 --}}
        <div class="form-grid-2">
          <div class="form-group">
            <label class="form-label">Email Address <span style="color:var(--accent-pink)">*</span></label>
            <input type="email" name="email" class="form-control" required
                   placeholder="staff@twb.to"
                   style="font-size:14px;padding:11px 14px;">
          </div>
          <div class="form-group">
            <label class="form-label">Password <span style="color:var(--accent-pink)">*</span></label>
            <input type="password" name="password" class="form-control" required minlength="8"
                   placeholder="Minimum 8 characters"
                   style="font-size:14px;padding:11px 14px;">
          </div>
        </div>

        {{-- Info box --}}
        <div style="background:rgba(26,115,232,0.07);border:1px solid rgba(26,115,232,0.2);border-radius:8px;padding:12px 16px;font-size:12px;color:rgba(255,255,255,0.6);line-height:1.6;">
          <span class="material-icons" style="font-size:15px;vertical-align:middle;margin-right:5px;color:var(--accent-blue);">info</span>
          <strong style="color:rgba(255,255,255,0.8);">Officers</strong> can manage customers, meters, invoices and payments.
          <strong style="color:rgba(255,255,255,0.8);">Administrators</strong> have full access including user management and audit logs.
        </div>

      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline" onclick="closeModal()">Cancel</button>
        <button type="submit" class="btn btn-primary">
          <span class="material-icons">person_add</span> Create User
        </button>
      </div>
    </form>
  </div>
</div>

@endsection

@push('scripts')
<script>
function openModal() {
  document.getElementById('userModal').classList.add('open');
}
function closeModal() {
  document.getElementById('userModal').classList.remove('open');
}
document.getElementById('userModal').addEventListener('click', function(e) {
  if (e.target === this) closeModal();
});
</script>
@endpush
