@extends('admin.admin_master')
@section('admin')
@php($t=1)
<div class="main-content">
	<div class="main-content-inner">
			<div class="page-content" style="padding:10px 15px!important;">
				<div class="ace-settings-container" id="ace-settings-container"></div>
				<div class="row">
					<div class="col-xs-12">
					<!-- PAGE CONTENT BEGINS -->
<div class="tabbable" style="padding:0px 0px!important;">
		<ul class="nav nav-tabs padding-0">
			<li class="active"><a data-toggle="tab" href="#home"><i class="green ace-icon fa fa-plus-circle bigger-120" style="vertical-align:bottom;"></i> UPDATE BLOCK</a></li>

		</ul>

	
		<div class="tab-content no-border" style="border:1px solid #ddd; min-height:100px;">
			<div id="home" class="tab-pane in active" style="padding:0px 10px!important;">
				<div class="row">
<div class="row">
	<form name="frm" id="frm" action="{{ route('update.block',$data->blockid) }}" method="post" enctype="multipart/form-data">
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
				TEHSIL NAME<label id="req">*</label>
				<input type="hidden" name="form_token" id="form_token" value="{{$token}}">
				<select class="chosen-select form-control" name="tehsilid" id="tehsilid" autofocus onKeyPress="return OnKeyPress(this, event)"  data-placeholder="TEHSIL NAME">
					<option value=""></option>
					@foreach ($tehsil as $itm)
					<option value="{{ $itm->tehsilid }}" {{ intval(old('tehsilid',$data->tehsilid))===$itm->tehsilid ? 'selected' : '' }}>{{ strtoupper($itm->tehsilname) }}</option>
					@endforeach
				</select>

				<span class="text-danger">@error('tehsilid') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }} </i> @enderror</span>
			</div>

			<div class="col-sm-3">
				<span id="charCounter">0/50</span>			
				BLOCK NAME<label id="req">*</label>
				<input type="text" class="form-control distname" name="blockname" id="blockname" value="{{old('blockname',$data->blockname)}}" placeholder="BLOCK NAME" autocomplete="off" maxlength="50"/>

				<span class="text-danger">@error('blockname') {{ $message }} @enderror</span>
			</div>
			<div class="col-sm-2">
				<span id="charCounter1">0/5</span>
				BLOCK CODE <label id="req">*</label>
				<input type="text" class="form-control distcode" name="blockcode" id="blockcode" value="{{old('blockcode',$data->blockcode)}}" placeholder="BLOCK CODE" required autocomplete="off" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}" maxlength="5"/>

				<span class="text-danger">@error('blockcode') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
			</div>
			
			<div class="col-sm-2" align="left">
				<button type="submit" class="btn btn-info myfrmbtn">SUBMIT</button>
			</div>
			<div class="col-sm-2" align="left">
				<button type="button" class="btn btn-info myfrmbtn" onclick="history.back()">GO BACK</button>
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
<script src="{{ asset('panel/assets/js/jquery-2.1.4.min.js') }}"></script>
<script src="{{ asset('panel/assets/js/datatablePaginationWithFilter.js') }}"></script>
@endsection