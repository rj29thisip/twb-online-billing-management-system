@extends('layouts.app')
@section('title', 'Districts')
@section('breadcrumb', 'Admin / Settings / Districts')
@section('page-title', 'Districts')

@section('content')
<div class="section-header">
  <div>
    <h2>Districts &amp; Areas</h2>
    <p>Deactivating a district keeps all existing references intact — staff and customers remain linked.</p>
  </div>
  <a href="{{ route('admin.districts.create') }}" class="btn btn-primary">
    <span class="material-icons">add</span> Add District
  </a>
</div>

<div class="card">
  <div class="table-wrapper">
    <table>
      <thead>
        <tr>
          <th>Name</th><th>Code</th><th>Type</th><th>Staff</th><th>Customers</th><th>Status</th><th>Actions</th>
        </tr>
      </thead>
      <tbody>
        @forelse($districts as $district)
        <tr style="{{ !$district->is_active ? 'opacity:0.55' : '' }}">
          <td>
            <div class="u-name">{{ $district->name }}</div>
            @if($district->description)<div class="u-sub">{{ $district->description }}</div>@endif
          </td>
          <td style="font-family:monospace;font-size:11px;color:var(--accent-teal);">{{ $district->code }}</td>
          <td>
            @if($district->is_headquarters)
              <span class="badge-status badge-active">Headquarters</span>
            @else
              <span class="badge-status" style="background:rgba(255,255,255,0.07);color:var(--text-secondary);">Area</span>
            @endif
          </td>
          <td>{{ $district->users_count }}</td>
          <td>{{ $district->customers_count }}</td>
          <td>
            @if($district->is_active)
              <span class="badge-status badge-active">Active</span>
            @else
              <span class="badge-status badge-inactive">Inactive</span>
            @endif
          </td>
          <td>
            <div style="display:flex;gap:6px;align-items:center;">
              <a href="{{ route('admin.districts.edit', $district) }}" class="action-btn" title="Edit">
                <span class="material-icons">edit</span>
              </a>
              @if(!$district->is_headquarters)
              <form method="POST" action="{{ route('admin.districts.toggle-active', $district) }}"
                    onsubmit="return confirm('{{ $district->is_active ? 'Deactivate' : 'Activate' }} district {{ $district->name }}?')">
                @csrf @method('PATCH')
                <button type="submit" class="action-btn"
                        title="{{ $district->is_active ? 'Deactivate' : 'Activate' }}"
                        style="color:{{ $district->is_active ? 'var(--accent-orange)' : 'var(--accent-green)' }}">
                  <span class="material-icons">{{ $district->is_active ? 'block' : 'check_circle' }}</span>
                </button>
              </form>
              @else
              <span class="action-btn" style="opacity:0.3;cursor:not-allowed;" title="HQ cannot be deactivated">
                <span class="material-icons">lock</span>
              </span>
              @endif
            </div>
          </td>
        </tr>
        @empty
        <tr><td colspan="7"><div class="empty-state"><span class="material-icons">map</span><h3>No districts yet</h3></div></td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
  @if($districts->hasPages())
  <div class="pagination">
    <div class="pagination-info">{{ $districts->total() }} districts</div>
    <div class="pagination-btns">
      @if(!$districts->onFirstPage())<a href="{{ $districts->previousPageUrl() }}" class="pg-btn"><span class="material-icons">chevron_left</span></a>@endif
      @if($districts->hasMorePages())<a href="{{ $districts->nextPageUrl() }}" class="pg-btn"><span class="material-icons">chevron_right</span></a>@endif
    </div>
  </div>
  @endif
</div>
<p style="font-size:12px;color:var(--text-muted);margin-top:12px;">
  <span class="material-icons" style="font-size:13px;vertical-align:middle;">info</span>
  Districts are never permanently deleted. Deactivating hides a district from new assignments while keeping all existing records linked.
</p>
@endsection
