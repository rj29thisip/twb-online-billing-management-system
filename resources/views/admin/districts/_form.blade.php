@if($errors->any())
  <div class="alert alert-error"><span class="material-icons" style="font-size:16px">error</span> {{ $errors->first() }}</div>
@endif

<form method="POST" action="{{ $action }}">
  @csrf
  @if($method !== 'POST') @method($method) @endif

  <div class="form-group">
    <label class="form-label">District Name <span style="color:var(--accent-pink)">*</span></label>
    <input type="text" name="name" class="form-control" required
           value="{{ old('name', $district->name) }}" placeholder="e.g. Area 1">
    @error('name')<div class="form-error">{{ $message }}</div>@enderror
  </div>

  <div class="form-group" style="margin-top:14px;">
    <label class="form-label">Code <span style="color:var(--accent-pink)">*</span></label>
    <input type="text" name="code" class="form-control" required
           value="{{ old('code', $district->code) }}" placeholder="e.g. AREA1"
           style="text-transform:uppercase" maxlength="20">
    <div class="form-hint">Short unique identifier. Automatically uppercased.</div>
    @error('code')<div class="form-error">{{ $message }}</div>@enderror
  </div>

  <div class="form-group" style="margin-top:14px;">
    <label class="form-label">Description</label>
    <textarea name="description" class="form-control" rows="2"
              placeholder="Optional notes">{{ old('description', $district->description) }}</textarea>
  </div>

  <div style="margin-top:16px;">
    <label class="toggle-label" style="display:flex;align-items:center;gap:10px;cursor:pointer;">
      <input type="checkbox" name="is_headquarters" value="1"
             {{ old('is_headquarters', $district->is_headquarters) ? 'checked' : '' }}
             class="toggle-input">
      <span>This is the <strong>Headquarters</strong> district</span>
    </label>
    <div class="form-hint" style="margin-top:4px;">HQ staff can see all customers across all areas.</div>
  </div>

  <div style="margin-top:28px;display:flex;gap:10px;">
    <button type="submit" class="btn btn-primary">
      <span class="material-icons" style="font-size:16px;">save</span> Save District
    </button>
    <a href="{{ route('admin.districts.index') }}" class="btn btn-outline">Cancel</a>
  </div>
</form>
