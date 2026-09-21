@extends('admin.admin_master')
@section('admin')
@php
$t=1;
@endphp
<div class="main-content">
	<div class="main-content-inner">
			<div class="page-content" style="padding:10px 15px!important;">
				<div class="ace-settings-container" id="ace-settings-container"></div>
				<div class="row">
					<div class="col-xs-12">
					<!-- PAGE CONTENT BEGINS -->
<div class="tabbable" style="padding:0px 0px!important;">
		<ul class="nav nav-tabs padding-0">
			<li class="active"><a data-toggle="tab" href="#home"><i class="green ace-icon fa fa-plus-circle bigger-120" style="vertical-align:bottom;"></i> UPDATE PAYOUT OBJECTIVE</a></li>

		</ul>

	
		<div class="tab-content no-border" style="border:1px solid #ddd; min-height:100px;">
			<div id="home" class="tab-pane in active" style="padding:0px 10px!important;">
				<div class="row">
<div class="row">
	<form name="frm" id="frm" action="{{ route('update.objective',$data->objectiveid) }}" method="post">
		@if(Session::has('success'))
		<div class="col-sm-12">
			<div class="alert alert-success">{{ Session::get('success') }}</div>
		</div>
		@endif
		@if(Session::has('fail'))
		<div class="col-sm-12">
			<div class="alert alert-danger">{{ Session::get('fail') }}</div>
		</div>
		@endif
		@csrf
		<div class="form-group">

			<div class="col-sm-3">
				PAYOUT OBJECTIVE<label id="req">*</label>
				<input type="text" class="form-control" name="objective" id="objective" value="{{ $data->objective }}" placeholder="PAYOUT OBJECTIVE" autocomplete="off"/>

				<span class="text-danger">@error('objective') {{ $message }} @enderror</span>
			</div>
			<div class="col-sm-2" align="left">
				<button type="submit" class="btn btn-info myfrmbtn">{{ __('common.update') }}</button>
			</div>
			<div class="col-sm-2" align="left">
				<button type="button" class="btn btn-info myfrmbtn" onclick="history.back()">{{ __('common.goback') }}</button>
			</div>
			
		</div>		
	</form>
</div>
			</div>
	</div>
</div>
</div>


						<div class="hr hr32 hr-dotted"></div>
				</div><!-- /.col -->
			</div><!-- /.row -->
		</div><!-- /.page-content -->
	
		
	</div>
</div>
@endsection