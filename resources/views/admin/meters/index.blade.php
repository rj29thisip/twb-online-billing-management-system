@extends('layouts.app')
@section('title', 'Meters')
@section('breadcrumb', 'Admin / Meters')
@section('page-title', 'METER REGISTRY')

@section('content')

<div class="section-header">
  <div><h2>Meter Devices</h2><p>View, add, and modify customers' meter devices</p></div>
  <a href="{{ route('admin.meters.create') }}" class="btn btn-primary">
    <span class="material-icons">add</span> Add Meter
  </a>
</div>

<div class="card-tight-margin">
  <form method="GET" class="filter-bar">
    <input type="text" name="search" class="form-control" placeholder="Meter ID, Endpoint ID, customer..." value="{{ request('search') }}">
    <select name="status" class="form-control">
      <option value="">All Statuses</option>
      <option value="active"   {{ request('status') === 'active'   ? 'selected' : '' }}>Active</option>
      <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
      <option value="replaced" {{ request('status') === 'replaced' ? 'selected' : '' }}>Replaced</option>
      <option value="faulty"   {{ request('status') === 'faulty'   ? 'selected' : '' }}>Faulty</option>
    </select>
    <select name="type" class="form-control">
      <option value="">All Types</option>
      <option value="residential"  {{ request('type') === 'residential'  ? 'selected' : '' }}>Residential</option>
      <option value="commercial"   {{ request('type') === 'commercial'   ? 'selected' : '' }}>Commercial</option>
    </select>
    <button type="submit" class="btn btn-outline btn-sm"><span class="material-icons">filter_list</span> Filter</button>
    @if(request()->hasAny(['search','status','type']))
      <a href="{{ route('admin.meters.index') }}" class="btn btn-outline btn-sm"><span class="material-icons">clear</span> Clear</a>
    @endif
  </form>

  <div class="table-wrapper">
    <table>
      <thead>
        <tr><th>Meter ID</th><th>Endpoint ID</th><th>Customer</th><th>District</th><th>Type</th><th>Status</th><th>Installed</th><th>Actions</th></tr>
      </thead>
      <tbody>
        @forelse($meters as $meter)
          <tr>
            <td class="td-primary" style="font-family:monospace;">{{ $meter->meter_id }}</td>
            <td style="font-family:monospace;font-size:14px;color:var(--accent-teal);">{{ $meter->endpoint_id ?? '—' }}</td>
            <td>
              <a href="{{ route('admin.customers.show', $meter->customer) }}" style="color:var(--accent-blue);">
                {{ $meter->customer->name }}
              </a>
              <div style="font-size:14px;color:var(--text-muted);">{{ $meter->customer->account_number }}</div>
            </td>
            <td>
              @if($meter->customer->district)
                <span style="font-size:14px;background:rgba(26,188,156,0.12);color:var(--accent-teal);padding:2px 8px;border-radius:10px;">
                  {{ $meter->customer->district->name }}
                </span>
              @else
                <span style="color:var(--text-muted);font-size:14px;">—</span>
              @endif
            </td>
            <td>{{ ucfirst($meter->meter_type) }}</td>
            <td><span class="badge-status badge-{{ $meter->status }}">{{ ucfirst($meter->status) }}</span></td>
            <td>{{ $meter->installation_date?->format('d M Y') ?? '—' }}</td>
            <td>
              <div style="display:flex;gap:4px;">
                <a href="{{ route('admin.meters.show', $meter) }}" class="action-btn"><span class="material-icons">visibility</span></a>
                <a href="{{ route('admin.meters.edit', $meter) }}" class="action-btn"><span class="material-icons">edit</span></a>
              </div>
            </td>
          </tr>
        @empty
          <tr><td colspan="8"><div class="empty-state"><span class="material-icons">speed</span><h3>No meters found</h3></div></td></tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <div class="pagination">
    <div class="pagination-info">Showing {{ $meters->firstItem() }}–{{ $meters->lastItem() }} of {{ $meters->total() }}</div>
    <div class="pagination-btns">
      @if(!$meters->onFirstPage())
        <a href="{{ $meters->previousPageUrl() }}" class="pg-btn"><span class="material-icons">chevron_left</span></a>
      @endif
      @foreach($meters->getUrlRange(max(1,$meters->currentPage()-2), min($meters->lastPage(),$meters->currentPage()+2)) as $page => $url)
        <a href="{{ $url }}" class="pg-btn {{ $page === $meters->currentPage() ? 'active' : '' }}">{{ $page }}</a>
      @endforeach
      @if($meters->hasMorePages())
        <a href="{{ $meters->nextPageUrl() }}" class="pg-btn"><span class="material-icons">chevron_right</span></a>
      @endif
    </div>
  </div>
</div>

@endsection
