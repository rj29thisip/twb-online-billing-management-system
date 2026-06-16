{{-- resources/views/admin/customers/index.blade.php --}}
@extends('layouts.app')
@section('title', 'Customers')
@section('breadcrumb', 'Admin / Customers')
@section('page-title', 'CUSTOMER MANAGEMENT')

@section('content')

<div class="section-header">
  <div>
    <h2>Customer Accounts</h2>
    <p>View, add, and modify registered customer accounts</p>
  </div>
  <a href="{{ route('admin.customers.create') }}" class="btn btn-primary">
    <span class="material-icons">person_add</span> Add Customer
  </a>
</div>

<div class="card-tight-margin">
  <form method="GET" class="filter-bar">
    <input type="text" name="search" class="form-control" placeholder="Name, account #, phone..."
           value="{{ request('search') }}">
    <select name="block" class="form-control">
      <option value="">All Blocks</option>
      @foreach($blocks as $block)
        <option value="{{ $block }}" {{ request('block') === $block ? 'selected' : '' }}>{{ $block }}</option>
      @endforeach
    </select>
    <select name="status" class="form-control">
      <option value="">All Statuses</option>
      <option value="active"    {{ request('status') === 'active'    ? 'selected' : '' }}>Active</option>
      <option value="inactive"  {{ request('status') === 'inactive'  ? 'selected' : '' }}>Inactive</option>
      <option value="suspended" {{ request('status') === 'suspended' ? 'selected' : '' }}>Suspended</option>
    </select>
    <button type="submit" class="btn btn-outline btn-sm">
      <span class="material-icons">filter_list</span> Filter
    </button>
    @if(request()->hasAny(['search','block','status']))
      <a href="{{ route('admin.customers.index') }}" class="btn btn-outline btn-sm">
        <span class="material-icons">clear</span> Clear
      </a>
    @endif
  </form>

  <div class="table-wrapper">
    <table>
      <thead>
        <tr>
          <th>Customer</th>
          <th>Account #</th>
          <th>Block</th>
          <th>Phone</th>
          <th>Meter ID</th>
          <th>Status</th>
          <th>Outstanding</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        @forelse($customers as $customer)
          <tr>
            <td>
              <div class="user-cell">
                <div class="u-avatar" style="background:var(--gradient-blue);color:#fff;font-size:11px;">
                  {{ substr($customer->name, 0, 2) }}
                </div>
                <div>
                  <div class="u-name">{{ $customer->name }}</div>
                  <div class="u-sub">{{ $customer->email ?? '—' }}</div>
                </div>
              </div>
            </td>
            <td class="td-primary" style="font-family:monospace;">{{ $customer->account_number }}</td>
            <td>{{ $customer->block_number ?? '—' }}</td>
            <td>{{ $customer->phone ?? '—' }}</td>
            <td>
              @if($customer->activeMeter)
                <code style="font-size:14px;color:var(--accent-teal);">{{ $customer->activeMeter->meter_id }}</code>
              @else
                <span style="color:var(--text-muted);">No meter</span>
              @endif
            </td>
            <td>
              <span class="badge-status {{ 'badge-' . $customer->status }}">{{ ucfirst($customer->status) }}</span>
            </td>
            <td>
              @php $balance = $customer->outstandingBalance(); @endphp
              <span style="color: {{ $balance > 0 ? 'var(--accent-orange)' : 'var(--accent-green)' }}">
                T$ {{ number_format($balance, 2) }}
              </span>
            </td>
            <td>
              <div style="display:flex;gap:4px;">
                <a href="{{ route('admin.customers.show', $customer) }}" class="action-btn" title="View">
                  <span class="material-icons">visibility</span>
                </a>
                <a href="{{ route('admin.customers.edit', $customer) }}" class="action-btn" title="Edit">
                  <span class="material-icons">edit</span>
                </a>
              </div>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="8">
              <div class="empty-state">
                <span class="material-icons">people</span>
                <h3>No customers found</h3>
              </div>
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <div class="pagination">
    <div class="pagination-info">
      Showing {{ $customers->firstItem() }}–{{ $customers->lastItem() }} of {{ $customers->total() }} customers
    </div>
    <div class="pagination-btns">
      @if($customers->onFirstPage())
        <span class="pg-btn" style="opacity:0.3;"><span class="material-icons">chevron_left</span></span>
      @else
        <a href="{{ $customers->previousPageUrl() }}" class="pg-btn"><span class="material-icons">chevron_left</span></a>
      @endif
      @foreach($customers->getUrlRange(max(1,$customers->currentPage()-2), min($customers->lastPage(),$customers->currentPage()+2)) as $page => $url)
        <a href="{{ $url }}" class="pg-btn {{ $page === $customers->currentPage() ? 'active' : '' }}">{{ $page }}</a>
      @endforeach
      @if($customers->hasMorePages())
        <a href="{{ $customers->nextPageUrl() }}" class="pg-btn"><span class="material-icons">chevron_right</span></a>
      @else
        <span class="pg-btn" style="opacity:0.3;"><span class="material-icons">chevron_right</span></span>
      @endif
    </div>
  </div>
</div>

@endsection
