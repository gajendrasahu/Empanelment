
@extends('admin.admin_master')
@section('admin')

@php
	$userType = Session::get('userType');
@endphp


@if($userType === 'ADMIN')
	@include('admin.dashboard.admin')

@elseif($userType === 'DEPARTMENT')
	@include('admin.dashboard.department')

@elseif($userType === 'VENDOR')
	@include('admin.dashboard.vendor')
@elseif($userType === 'IMPELLENT')
	@include('admin.dashboard.impanelment')
@elseif($userType === 'PROJECT MANAGER')
	@include('admin.dashboard.projectmanager')
@elseif($userType === 'RESOURCE')
	@include('admin.dashboard.resource')
@else
	<div class="col-sm-12">
		<div class="alert alert-warning">
			<i class="fa fa-exclamation-triangle"></i> Unknown user type. Please contact support.
		</div>
	</div>
@endif

@endsection