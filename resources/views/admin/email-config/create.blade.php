@extends('layouts.app')
@section('title', 'Add Email Config')
@section('breadcrumb', 'Admin / Settings / Email Config / New')
@section('page-title', 'Add Email Config')
@section('content')
<div style="max-width:680px;">
  <div class="card">
    <div class="card-body" style="padding:28px;">
      @include('admin.email-config._form', ['config' => new \App\Models\EmailConfig(), 'action' => route('admin.email-config.store'), 'method' => 'POST'])
    </div>
  </div>
</div>
@endsection
