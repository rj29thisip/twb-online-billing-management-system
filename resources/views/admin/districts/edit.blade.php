@extends('layouts.app')
@section('title', 'Edit District')
@section('breadcrumb', 'Admin / Settings / Districts / Edit')
@section('page-title', 'EDIT DISTRICT')
@section('content')
<div style="max-width:480px;margin:0 auto;">
  <div class="card">
    <div class="table-card-header">
      <div class="card-header-float" style="background:var(--gradient-dark);">
        <div>
          <h3>Edit Existing District</h3>
        </div>
        <span class="material-icons" style="color:var(--accent-blue)">edit
      </span>
    </div>
    <div class="card-body" style="padding:28px;">
      @include('admin.districts._form', ['district' => $district, 'action' => route('admin.districts.update', $district), 'method' => 'PUT'])
    </div>
  </div>
</div>
@endsection
