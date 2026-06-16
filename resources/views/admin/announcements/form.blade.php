@extends('layouts.app')
@section('title', isset($announcement) ? 'Edit Announcement' : 'New Announcement')
@section('breadcrumb', 'Admin / Announcements / ' . (isset($announcement) ? 'Edit' : 'New'))
@section('page-title', isset($announcement) ? 'Edit Announcement' : 'New Announcement')

@section('content')

<div style="max-width:900px;margin:0 auto;">

  <div style="margin-bottom:20px;">
    <a href="{{ route('admin.announcements.index') }}" class="btn btn-outline btn-sm">
      <span class="material-icons">arrow_back</span> Back
    </a>
  </div>

  <div class="card">
    <div class="table-card-header">
      <div class="card-header-float" style="background:var(--gradient-dark);">
        <div>
          <h3>{{ isset($announcement) ? 'Edit Announcement' : 'New Announcement' }}</h3>
          <p>{{ isset($announcement) ? 'Update existing announcement' : 'Broadcast an announcement to the customers' }}</p>
        </div>
        <span class="material-icons" style="color:var(--accent-blue)">campaign</span>
      </div>
    </div>

    <div class="card-body" style="padding-top:0;">
      <form
        action="{{ isset($announcement) ? route('admin.announcements.update', $announcement) : route('admin.announcements.store') }}"
        method="POST"
      >
        @csrf
        @if(isset($announcement)) @method('PUT') @endif

        <div class="form-group">
          <label class="form-label">Title <span style="color:var(--accent-pink)">*</span></label>
          <input type="text" name="title" class="form-control @error('title') is-invalid @enderror"
                 value="{{ old('title', $announcement->title ?? '') }}"
                 placeholder="Announcement title" required>
          @error('title') <div class="form-error">{{ $message }}</div> @enderror
        </div>

        <div class="form-grid-2">
          <div class="form-group">
            <label class="form-label">Type <span style="color:var(--accent-pink)">*</span></label>
            <select name="type" class="form-control @error('type') is-invalid @enderror" required>
              <option value="news"      {{ old('type', $announcement->type ?? '') === 'news'      ? 'selected' : '' }}>News</option>
              <option value="promotion" {{ old('type', $announcement->type ?? '') === 'promotion' ? 'selected' : '' }}>Promotion</option>
              <option value="event"     {{ old('type', $announcement->type ?? '') === 'event'     ? 'selected' : '' }}>Event</option>
              <option value="alert"     {{ old('type', $announcement->type ?? '') === 'alert'     ? 'selected' : '' }}>Alert</option>
            </select>
            @error('type') <div class="form-error">{{ $message }}</div> @enderror
          </div>

          <div class="form-group">
            <label class="form-label">Status</label>
            <div style="padding:10px 0;">
              <label class="checkbox-label" style="cursor:pointer;font-size:13px;color:var(--text-secondary);display:flex;align-items:center;gap:8px;">
                <input type="checkbox" name="is_published" value="1"
                       {{ old('is_published', $announcement->is_published ?? false) ? 'checked' : '' }}>
                Publish immediately
              </label>
            </div>
          </div>
        </div>

        <div class="form-grid-2">
          <div class="form-group">
            <label class="form-label">Publish From (optional)</label>
            <input type="date" name="publish_from" class="form-control"
                   value="{{ old('publish_from', isset($announcement) ? $announcement->publish_from?->format('Y-m-d') : '') }}">
          </div>
          <div class="form-group">
            <label class="form-label">Publish To (optional)</label>
            <input type="date" name="publish_to" class="form-control"
                   value="{{ old('publish_to', isset($announcement) ? $announcement->publish_to?->format('Y-m-d') : '') }}">
          </div>
        </div>

        <div class="form-group">
          <label class="form-label">Content <span style="color:var(--accent-pink)">*</span></label>
          <textarea name="content" rows="6"
                    class="form-control @error('content') is-invalid @enderror"
                    placeholder="Write the full announcement content here..." required>{{ old('content', $announcement->content ?? '') }}</textarea>
          @error('content') <div class="form-error">{{ $message }}</div> @enderror
        </div>

        <div style="display:flex;gap:12px;justify-content:flex-end;">
          <a href="{{ route('admin.announcements.index') }}" class="btn btn-outline">Cancel</a>
          <button type="submit" class="btn btn-primary">
            <span class="material-icons">save</span>
            {{ isset($announcement) ? 'Update' : 'Post' }} Announcement
          </button>
        </div>

      </form>
    </div>
  </div>

</div>

@endsection
