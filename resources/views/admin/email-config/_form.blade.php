@if($errors->any())
  <div class="alert alert-error"><span class="material-icons" style="font-size:16px">error</span> {{ $errors->first() }}</div>
@endif

<form method="POST" action="{{ $action }}">
  @csrf
  @if($method !== 'POST') @method($method) @endif

  <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:14px;">
    <div class="form-group">
      <label class="form-label">Driver <span style="color:var(--accent-pink)">*</span></label>
      <select name="mailer" class="form-control" id="mailerSelect">
        @foreach(['smtp'=>'SMTP','mailgun'=>'Mailgun','ses'=>'Amazon SES','postmark'=>'Postmark','sendmail'=>'Sendmail'] as $val=>$label)
          <option value="{{ $val }}" {{ old('mailer',$config->mailer??'smtp')===$val?'selected':'' }}>{{ $label }}</option>
        @endforeach
      </select>
    </div>
    <div class="form-group" id="hostField">
      <label class="form-label">SMTP Host</label>
      <input type="text" name="host" class="form-control" value="{{ old('host',$config->host??'') }}" placeholder="smtp.example.com">
    </div>
    <div class="form-group" id="portField">
      <label class="form-label">Port</label>
      <input type="number" name="port" class="form-control" value="{{ old('port',$config->port??587) }}" min="1" max="65535">
    </div>
  </div>

  <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-top:14px;">
    <div class="form-group">
      <label class="form-label">Username</label>
      <input type="text" name="username" class="form-control" value="{{ old('username',$config->username??'') }}" autocomplete="off">
    </div>
    <div class="form-group">
      <label class="form-label">Password @isset($config->id)<span style="font-size:11px;color:var(--text-muted);font-weight:400;"> (leave blank to keep current)</span>@endisset</label>
      <input type="password" name="password" class="form-control" autocomplete="new-password" placeholder="••••••••">
    </div>
  </div>

  <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:14px;margin-top:14px;">
    <div class="form-group">
      <label class="form-label">Encryption</label>
      <select name="encryption" class="form-control">
        @foreach(['tls'=>'TLS (recommended)','ssl'=>'SSL','none'=>'None'] as $val=>$label)
          <option value="{{ $val }}" {{ old('encryption',$config->encryption??'tls')===$val?'selected':'' }}>{{ $label }}</option>
        @endforeach
      </select>
    </div>
    <div class="form-group">
      <label class="form-label">From Address <span style="color:var(--accent-pink)">*</span></label>
      <input type="email" name="from_address" class="form-control" required value="{{ old('from_address',$config->from_address??'') }}" placeholder="billing@twb.to">
    </div>
    <div class="form-group">
      <label class="form-label">From Name <span style="color:var(--accent-pink)">*</span></label>
      <input type="text" name="from_name" class="form-control" required value="{{ old('from_name',$config->from_name??'TWB Water Billing') }}">
    </div>
  </div>

  <div class="form-group" style="margin-top:14px;">
    <label class="form-label">Notes</label>
    <textarea name="notes" class="form-control" rows="2" placeholder="Optional notes">{{ old('notes',$config->notes??'') }}</textarea>
  </div>

  <div style="margin-top:16px;">
    <label style="display:flex;align-items:center;gap:10px;cursor:pointer;">
      <input type="checkbox" name="is_active" value="1" {{ old('is_active',$config->is_active??false)?'checked':'' }}>
      <span><strong>Set as Active Configuration</strong> <span style="font-size:12px;color:var(--text-muted);">(will deactivate all others)</span></span>
    </label>
  </div>

  <div style="margin-top:24px;display:flex;gap:10px;">
    <button type="submit" class="btn btn-primary"><span class="material-icons" style="font-size:16px;">save</span> Save Configuration</button>
    <a href="{{ route('admin.email-config.index') }}" class="btn btn-outline">Cancel</a>
  </div>
</form>
<script>
document.getElementById('mailerSelect').addEventListener('change', function() {
  var isSmtp = this.value === 'smtp';
  document.getElementById('hostField').style.opacity = isSmtp ? '1' : '0.3';
  document.getElementById('portField').style.opacity = isSmtp ? '1' : '0.3';
});
</script>
