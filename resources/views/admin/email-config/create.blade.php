@extends('layouts.app')
@section('title', 'Add Email Config')
@section('breadcrumb', 'Admin / Settings / Email Config / New')
@section('page-title', 'Add Email Config')
@section('content')
<div style="max-width:680px;margin:0 auto;">
  <div class="card">
    <div class="table-card-header">
      <div class="card-header-float" style="background:var(--gradient-dark);">
        <div>
          <h3>Add a New Email Config</h3>
        </div>
        <span class="material-icons" style="color:var(--accent-blue)">email
      </span>
    </div>
    <div class="card-body" style="padding:28px;">
      @include('admin.email-config._form', ['config' => new \App\Models\EmailConfig(), 'action' => route('admin.email-config.store'), 'method' => 'POST'])
    </div>
  </div>
</div>
@endsection
