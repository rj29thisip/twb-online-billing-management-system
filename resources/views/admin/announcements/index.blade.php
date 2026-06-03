@extends('layouts.app')
@section('title', 'Announcements')
@section('breadcrumb', 'Admin / Announcements')
@section('page-title', 'Announcements')

@section('content')

<div class="section-header">
  <div>
    <h2>Announcements</h2>
    <p>News, promotions, and events shown to customers</p>
  </div>
  <a href="{{ route('admin.announcements.create') }}" class="btn btn-primary">
    <span class="material-icons">add</span> New Announcement
  </a>
</div>

<div class="card">
  <div class="table-wrapper">
    <table>
      <thead>
        <tr>
          <th>Title</th>
          <th>Type</th>
          <th>Published</th>
          <th>Publish From</th>
          <th>Publish To</th>
          <th>Created By</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        @forelse($announcements as $ann)
          <tr>
            <td class="td-primary">{{ $ann->title }}</td>
            <td>
              <span class="badge-status {{ match($ann->type) {
                'alert'     => 'badge-overdue',
                'promotion' => 'badge-paid',
                'event'     => 'badge-partially',
                default     => 'badge-issued'
              } }}">
                {{ ucfirst($ann->type) }}
              </span>
            </td>
            <td>
              <span class="badge-status {{ $ann->is_published ? 'badge-active' : 'badge-inactive' }}">
                {{ $ann->is_published ? 'Published' : 'Draft' }}
              </span>
            </td>
            <td>{{ $ann->publish_from?->format('d M Y') ?? '—' }}</td>
            <td>{{ $ann->publish_to?->format('d M Y') ?? '—' }}</td>
            <td style="color:var(--text-muted);">{{ $ann->creator?->name ?? '—' }}</td>
            <td>
              <div style="display:flex;gap:4px;">
                <a href="{{ route('admin.announcements.edit', $ann) }}" class="action-btn">
                  <span class="material-icons">edit</span>
                </a>
                <form action="{{ route('admin.announcements.destroy', $ann) }}" method="POST"
                      onsubmit="return confirm('Delete this announcement?')">
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
            <td colspan="7">
              <div class="empty-state">
                <span class="material-icons">campaign</span>
                <h3>No announcements yet</h3>
              </div>
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <div class="pagination">
    <div class="pagination-info">
      Showing {{ $announcements->firstItem() }}–{{ $announcements->lastItem() }} of {{ $announcements->total() }}
    </div>
    <div class="pagination-btns">
      @if(!$announcements->onFirstPage())
        <a href="{{ $announcements->previousPageUrl() }}" class="pg-btn">
          <span class="material-icons">chevron_left</span>
        </a>
      @endif
      @if($announcements->hasMorePages())
        <a href="{{ $announcements->nextPageUrl() }}" class="pg-btn">
          <span class="material-icons">chevron_right</span>
        </a>
      @endif
    </div>
  </div>
</div>

@endsection
