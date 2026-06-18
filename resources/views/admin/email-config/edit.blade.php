@extends('layouts.app')
@section('title', 'Edit Email Config')
@section('breadcrumb', 'Admin / Settings / Email Config / Edit')
@section('page-title', 'Edit Email Config')
@section('content')
<div style="max-width:680px;margin:0 auto;">
  <div class="card">
    <div class="table-card-header">
      <div class="card-header-float" style="background:var(--gradient-dark);">
        <div>
          <h3>Edit Existing Email Config</h3>
        </div>
        <span class="material-icons" style="color:var(--accent-blue)">edit
      </span>
    </div>
    <div class="card-body" style="padding:28px;">
      @include('admin.email-config._form', ['config' => $emailConfig, 'action' => route('admin.email-config.update', $emailConfig), 'method' => 'PUT'])
    </div>
  </div>
</div>
@endsection
