@extends('layouts.app')
@section('title', 'Edit District')
@section('breadcrumb', 'Admin / Settings / Districts / Edit')
@section('page-title', 'Edit District')
@section('content')
<div style="max-width:560px;">
  <div class="card">
    <div class="card-body" style="padding:28px;">
      @include('admin.districts._form', ['district' => $district, 'action' => route('admin.districts.update', $district), 'method' => 'PUT'])
    </div>
  </div>
</div>
@endsection
