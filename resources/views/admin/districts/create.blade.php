@extends('layouts.app')
@section('title', 'Add District')
@section('breadcrumb', 'Admin / Settings / Districts / New')
@section('page-title', 'Add District')
@section('content')
<div style="max-width:560px;">
  <div class="card">
    <div class="card-body" style="padding:28px;">
      @include('admin.districts._form', ['district' => new \App\Models\District(), 'action' => route('admin.districts.store'), 'method' => 'POST'])
    </div>
  </div>
</div>
@endsection
