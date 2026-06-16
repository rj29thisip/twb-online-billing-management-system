@extends('layouts.app')
@section('title', 'Edit Email Config')
@section('breadcrumb', 'Admin / Settings / Email Config / Edit')
@section('page-title', 'Edit Email Config')
@section('content')
<div style="max-width:680px;">
  <div class="card">
    <div class="card-body" style="padding:28px;">
      @include('admin.email-config._form', ['config' => $emailConfig, 'action' => route('admin.email-config.update', $emailConfig), 'method' => 'PUT'])
    </div>
  </div>
</div>
@endsection
