@extends('layouts.app')
@section('title', 'Add District')
@section('breadcrumb', 'Admin / Settings / Districts / New')
@section('page-title', 'ADD DISTRICT')
@section('content')
<div style="max-width:480px;margin:0 auto;">
  <div class="card">
    <div class="table-card-header">
      <div class="card-header-float" style="background:var(--gradient-dark);">
        <div>
          <h3>Add a New District</h3>
        </div>
        <span class="material-icons" style="color:var(--accent-blue)">location_on
      </span>
    </div>
    <div class="card-body" style="padding:28px;">
      @include('admin.districts._form', ['district' => new \App\Models\District(), 'action' => route('admin.districts.store'), 'method' => 'POST'])
    </div>
  </div>
</div>
@endsection
