@extends('admin.admin_master')
@section('admin')
@php
@endphp
<div class="main-content">
<style>
.month_view
{
	width:100px!important;
	height:90px;
	margin-right:20px!important;
	margin:0 auto;
	margin-bottom:15px;
	border-radius:0px 15px 0px 15px;
	cursor:pointer;
}
</style>
	<div class="main-content-inner">
		<div class="col-sm-12 height-space"></div>
		<div class="col-sm-12">
			@foreach($months as $month)
				<span class="label label-white middle animated flipInX delay-02 f-s-20 form-label span-content month_view">
					<i class="fa fa-calendar"></i>
					<div class="year">{{$month['year']}}</div>
					<div class="month">{{$month['month']}}</div>
					<div class="year">Generate</div>
				</span>			
			@endforeach
		</div>
		<div class="col-sm-12">&nbsp;</div>
		<div class="col-sm-12">&nbsp;</div>
	</div>
</div>
@endsection