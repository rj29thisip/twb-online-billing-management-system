@extends('layouts.app')
@section('title', 'Import Meter Readings')
@section('breadcrumb', 'Admin / Readings / Import')
@section('page-title', 'IMPORT METER READINGS')

@section('content')

@php $activeTab = session('active_tab', 'xml-converter'); @endphp

<div class="section-header">
  <div>
    <h2>Import Meter Readings</h2>
    <p>Automatically register meter readings via XML / CSV file uploads or register meter readings via manual input</p>
  </div>
</div>

{{-- ── Tab switcher ────────────────────────────────────────────── --}}
<div class="xml-converter-tabs" style="display:flex;gap:0;margin-bottom:24px;border-bottom:2px solid var(--border);">
  <button class="xml-tab-btn {{ $activeTab==='xml-converter' ? 'active':'' }}"
          onclick="switchTab('xml-converter')" id="xml-converter"
          style="padding:10px 28px;background:none;border:none;cursor:pointer;font-size:14px;font-weight:600;
                 color:{{ $activeTab==='xml-converter' ? 'var(--accent-blue)' : 'var(--text-muted)' }};
                 border-bottom:3px solid {{ $activeTab==='xml-converter' ? 'var(--accent-blue)' : 'transparent' }};
                 margin-bottom:-2px;transition:all .2s;">
    <span class="material-icons" style="vertical-align:middle;font-size:18px;margin-right:6px;">upload_file</span>
    XML / CSV Conversion
  </button>
  <button class="manual-tab-btn {{ $activeTab==='manual-entry' ? 'active':''}}"
          onclick="switchTab('manual-entry')" id="manual-entry"
          style="padding:10px 28px;background:none;border:none;cursor:pointer;font-size:14px;font-weight:600;
                 color:{{ $activeTab==='manual-entry' ? 'var(--accent-blue)' : 'var(--text-muted)' }};
                 border-bottom:3px solid {{ $activeTab==='manual-entry' ? 'var(--accent-blue)' : 'transparent' }};
                 margin-bottom:-2px;transition:all .2s;">
    <span class="material-icons" style="vertical-align:middle;font-size:18px;margin-right:6px;">edit</span>
    Manual Entry
  </button>  
</div>

{{-- ── XML-CONVERTER TAB ─────────────────────────────────────────── --}}
<div id="pane-xml-converter" class="import-readings-pane" style="{{ $activeTab!=='xml-converter' ? 'display:none;' : '' }}">
  <div style="max-width:760px;margin:0 auto;">

    <div class="card">
      <div class="table-card-header">
        <div class="card-header-float" style="background:var(--gradient-dark);">
          <div>
            <h3>Upload File</h3>
            <p>Supported formats: TWB XML (Itron OpenWay) and CSV</p>
          </div>
          <span class="material-icons" style="color:var(--accent-blue)">upload_file</span>
        </div>
      </div>

      <div class="card-body" style="padding-top:0;">
        <form action="{{ route('admin.readings.import.post') }}" method="POST"
              enctype="multipart/form-data" id="importForm">
          @csrf

          {{-- Drop zone --}}
          <div id="dropZone" style="
            border: 2px dashed rgba(0, 0, 0, 0.15);
            border-radius: 12px;
            padding: 40px 20px;
            text-align: center;
            cursor: pointer;
            transition: border-color 0.2s, background 0.2s;
            margin-bottom: 20px;
          ">
            <span class="material-icons" style="font-size:48px;color:var(--text-muted);display:block;margin-bottom:12px;">
              cloud_upload
            </span>
            <div style="font-size:16px;font-weight:500;color:var(--text-secondary);margin-bottom:6px;">
              Drag & drop your file here
            </div>
            <div style="font-size:14px;color:var(--text-muted);margin-bottom:16px;">
              or click to browse
            </div>
            <input type="file" name="file" id="fileInput" accept=".xml,.csv,.txt"
                  style="display:none;" required>
            <button type="button" class="btn btn-outline btn-sm" onclick="document.getElementById('fileInput').click()">
              <span class="material-icons">folder_open</span> Browse File
            </button>
            <div id="fileName" style="margin-top:12px;font-size:14px;color:var(--accent-teal);display:none;"></div>
          </div>

          {{-- File type info --}}
          <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:20px;">

            <div style="background:rgba(26,115,232,0.08);border:1px solid rgba(26,115,232,0.2);
                        border-radius:10px;padding:16px;">
              <div style="font-size:14px;font-weight:700;color:var(--accent-blue);
                          text-transform:uppercase;letter-spacing:0.5px;margin-bottom:8px;">
                <span class="material-icons" style="font-size:14px;vertical-align:middle;">code</span>
                XML Format (Itron OpenWay AMR)
              </div>
              <div style="font-size:12px;color:var(--text-muted);line-height:1.8;">
                ✓ Auto-detected from file extension<br>
                ✓ Supports multiple channels per file<br>
                ✓ `endpoint_id` matched to meter<br>
                ✓ Leading zeros stripped automatically<br>
                ✓ Timezone: UTC (from XML header)
              </div>
            </div>

            <div style="background:rgba(0,201,167,0.08);border:1px solid rgba(0,201,167,0.2);
                        border-radius:10px;padding:16px;">
              <div style="font-size:12px;font-weight:700;color:var(--accent-teal);
                          text-transform:uppercase;letter-spacing:0.5px;margin-bottom:8px;">
                <span class="material-icons" style="font-size:14px;vertical-align:middle;">table_chart</span>
                CSV Format
              </div>
              <div style="font-size:12px;color:var(--text-muted);line-height:1.8;">
                Columns (in order):<br>
                <code style="color:var(--accent-teal);">meter_id, capture_time, value, usage</code><br><br>
                Example:<br>
                <code style="color:var(--text-secondary);font-size:12px;">
                  I19VA001800,2026-04-24 08:00:00,4301969,6109
                </code>
              </div>
            </div>

          </div>

          {{-- Safety info --}}
          <div style="background:rgba(255,152,0,0.08);border:1px solid rgba(255,152,0,0.2);
                      border-radius:10px;padding:14px 16px;margin-bottom:20px;
                      display:flex;align-items:flex-start;gap:12px;">
            <span class="material-icons" style="color:var(--accent-amber);font-size:20px;flex-shrink:0;margin-top:1px;">
              shield
            </span>
            <div style="font-size:12px;color:var(--text-muted);line-height:1.8;">
              <strong style="color:var(--text-secondary);">Import safety features:</strong><br>
              Duplicate readings (same meter + same timestamp) are automatically skipped —
              you can safely re-upload the same file without creating duplicate data.
              Each channel is processed independently — a crash on one channel does not
              affect channels already imported.
            </div>
          </div>

          <div style="display:flex;gap:12px;align-items:center;">
            <button type="submit" class="btn btn-primary" id="submitBtn" disabled>
              <span class="material-icons">upload</span>
              Import Readings
            </button>
            <a href="{{ route('admin.readings.index') }}" class="btn btn-outline">
              Cancel
            </a>
            <span id="loadingMsg" style="display:none;font-size:12px;color:var(--text-muted);">
              <span class="material-icons" style="font-size:14px;vertical-align:middle;animation:spin 1s linear infinite;">
                refresh
              </span>
              Importing, please wait…
            </span>
          </div>

        </form>
      </div>
    </div>
  </div>
</div>


{{-- ── MANUAL-ENTRY TAB ─────────────────────────────────────────── --}}
<div id="pane-manual-entry" class="import-readings-pane" style="{{ $activeTab!=='manual-entry' ? 'display:none;' : '' }}">
  <div style="max-width:760px;margin:0 auto;">
    <div class="card-tight-margin">
      <div class="table-card-header">
        <div class="card-header-float" style="background:var(--gradient-dark);">
          <div><h3>Manual Entry</h3><p>Record a single meter reading</p></div>
          <span class="material-icons" style="color:var(--accent-blue)">edit</span>
        </div>
      </div>

      <div class="card-body" style="padding-top:0;">
        <form action="{{ route('admin.readings.manual') }}" method="POST">
          @csrf
          <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px;">
            <div class="form-group">
              <label class="form-label">Meter</label>
              <select name="meter_id" class="form-control" required>
                <option value="">— Select meter —</option>
                @foreach(\App\Models\Meter::with('customer')->where('status','active')->orderBy('meter_id')->get() as $m)
                  <option value="{{ $m->id }}">{{ $m->meter_id }} — {{ $m->customer?->name }}</option>
                @endforeach
              </select>
            </div>
            <div class="form-group">
              <label class="form-label">Reading Date & Time</label>
              <input type="datetime-local" name="capture_time" class="form-control" required>
            </div>
            <div class="form-group">
              <label class="form-label">Meter Value (litres)</label>
              <input type="number" name="value" class="form-control" min="0" placeholder="e.g. 4301969" required>
            </div>
          </div>
        
          {{-- Safety info --}}
          <div style="background:rgba(255,152,0,0.08);border:1px solid rgba(255,152,0,0.2);
                    border-radius:10px;padding:14px 16px;margin-bottom:20px;
                    display:flex;align-items:flex-start;gap:12px;">
          <span class="material-icons" style="color:var(--accent-amber);font-size:20px;flex-shrink:0;margin-top:1px;">
            shield
          </span>
          <div style="font-size:12px;color:var(--text-muted);line-height:1.8;">
            <strong style="color:var(--text-secondary);">Important Reminder:</strong><br>
            Record only approved meter reading. 
            Carefully review filled out items before clicking the "Save Reading" button.  
            Incorrect input reading may only be fixed by database updates.
            </div> 
          </div>          
          <button type="submit" class="btn btn-primary btn-sm">
            <span class="material-icons">save</span> Save Reading
          </button>
        </form>
    </div>
  </div>
</div>

@endsection

@push('scripts')
<style>
@keyframes spin { from { transform:rotate(0deg); } to { transform:rotate(360deg); } }
#dropZone.dragover { border-color:var(--accent-blue); background:rgba(26,115,232,0.06); }
</style>
<script>
var dropZone   = document.getElementById('dropZone');
var fileInput  = document.getElementById('fileInput');
var submitBtn  = document.getElementById('submitBtn');
var fileNameEl = document.getElementById('fileName');
var importForm = document.getElementById('importForm');
var loadingMsg = document.getElementById('loadingMsg');

function switchTab(tabId) {
    document.querySelectorAll('.import-readings-pane').forEach(pane => {
        pane.style.display = 'none';
    });

    const targetPane = document.getElementById('pane-' + tabId);
    if (targetPane) {
        targetPane.style.display = 'block';
    }

    const tabs = ['xml-converter', 'manual-entry'];
    tabs.forEach(id => {
        const btn = document.getElementById(id);
        if (btn) {
            btn.style.color = 'var(--text-muted)';
            btn.style.borderBottom = '3px solid transparent';
        }
    });

    const activeBtn = document.getElementById(tabId);
    if (activeBtn) {
        activeBtn.style.color = 'var(--accent-blue)';
        activeBtn.style.borderBottom = '3px solid var(--accent-blue)';
    }
}

function setFile(file) {
  if (!file) return;
  fileNameEl.textContent = '📄 ' + file.name + ' (' + (file.size / 1024).toFixed(1) + ' KB)';
  fileNameEl.style.display = 'block';
  submitBtn.disabled = false;
}

fileInput.addEventListener('change', function () {
  setFile(this.files[0]);
});

dropZone.addEventListener('click', function (e) {
  if (e.target === dropZone || e.target.tagName !== 'BUTTON') {
    fileInput.click();
  }
});

dropZone.addEventListener('dragover', function (e) {
  e.preventDefault();
  dropZone.classList.add('dragover');
});

dropZone.addEventListener('dragleave', function () {
  dropZone.classList.remove('dragover');
});

dropZone.addEventListener('drop', function (e) {
  e.preventDefault();
  dropZone.classList.remove('dragover');
  var file = e.dataTransfer.files[0];
  if (file) {
    // Inject the file into the input
    var dt = new DataTransfer();
    dt.items.add(file);
    fileInput.files = dt.files;
    setFile(file);
  }
});

importForm.addEventListener('submit', function () {
  submitBtn.disabled = true;
  loadingMsg.style.display = 'inline-flex';
  submitBtn.textContent = 'Importing…';
});
</script>
@endpush
