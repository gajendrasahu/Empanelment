@extends('admin.admin_master')
@section('admin')
@php
$t=0;
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
			<li class="active">
				<a data-toggle="tab" href="#home">
					<i class="green ace-icon fa fa-plus-circle bigger-120" style="vertical-align:bottom;"></i>Update Holiday
				</a>
			</li>
		</ul>

	
		<div class="tab-content no-border" style="border:1px solid #ddd; min-height:100px; padding:0px 10px;">
@if(in_array(1,Session::get('actions')))
<div id="home" class="tab-pane in active" style="padding:0px 10px!important;">
	<div class="row">
		<div class="row">
			<form name="frm" id="frm" action="{{ route('store.holiday',Crypt::encrypt($data->id))}}" method="post" enctype="multipart/form-data">
				@if(Session::has('success'))
				<div class="col-sm-12">
					<div class="alert alert-block alert-success">
				    	<button type="button" class="close" data-dismiss="alert">
							<i class="ace-icon fa fa-times"></i>
						</button>
						{{ Session::get('success') }}
					</div>
				</div>
				@endif
				@if(Session::has('fail'))
				<div class="col-sm-12">
					<div class="alert alert-block alert-danger">
				    	<button type="button" class="close" data-dismiss="alert">
							<i class="ace-icon fa fa-times"></i>
						</button>
						{{ Session::get('fail') }}
					</div>
				</div>
				@endif
				@if(Session::has('duplicate'))
				<div class="col-sm-12">
				    <div class="alert alert-block alert-danger">
				    	<button type="button" class="close" data-dismiss="alert">
							<i class="ace-icon fa fa-times"></i>
						</button>
				    	<i class="fa fa-warning"></i> {{ Session::get('duplicate') }}
				    </div>
				</div>
				@endif			
@if ($errors->has('fail'))
    <div class="alert alert-danger">
        {{ $errors->first('fail') }}
    </div>
@endif				
				@csrf
				<div class="form-group">
					
					<div class="col-sm-6">
						Title <label id="req">*</label>
						<input type="hidden" name="form_token" id="form_token" value="{{$token}}">
						<input type="text" class="form-control" name="holiday_title" id="holiday_title" value="{{old('holiday_title',$data->title)}}" placeholder="Holiday Title" autofocus required autocomplete="off" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}"/>
						
						<span class="text-danger">@error('holiday_title') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
					</div>
					<div class="col-sm-3">
						Start Date <label id="req">*</label>
						<input type="text" class="form-control todays_dt_blank" name="start_date" id="start_date" value="{{old('start_date',date('d\-m\-Y',strtotime($data->start_date)))}}" placeholder="dd-mm-YYYY" required autocomplete="off" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}"/>
						
						<span class="text-danger">@error('start_date') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
					</div>
					<div class="col-sm-3">
						End Date <label id="req">*</label>
						<input type="text" class="form-control todays_dt_blank" name="end_date" id="end_date" value="{{old('end_date',date('d\-m\-Y',strtotime($data->end_date)))}}" placeholder="dd-mm-YYYY" required autocomplete="off" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}"/>
						
						<span class="text-danger">@error('end_date') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
					</div>
					<div class="col-sm-12">&nbsp;</div>
					<div class="col-sm-2">
						<label style="margin-top:15px;">
							<input type="checkbox" name="is_recurring" @if($data->is_recurring) checked @endif value="1"> Is Recurring?
						</label>					
					</div>
					<div class="col-sm-3">
						<select name="recurring_type" id="recurring_type" class="form-control">
							<option value="">--Select--</option>
							<option value="yearly" @if($data->recurring_type=='yearly') selected @endif>Yearly</option>
							<option value="monthly" @if($data->recurring_type=='monthly') selected @endif>Monthly</option>
						</select>						
						
						<span class="text-danger">@error('end_date') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
					</div>
					<div class="col-sm-2" style="text-align:left;">
						<button type="submit" class="btn btn-info width-full width-100" tabindex="{{$t++}}">Submit</button>
					</div>
				</div>		
			</form>
		</div>
	</div>
</div>
@endif

</div>
</div>


						
				</div><!-- /.col -->
			</div><!-- /.row -->
		</div><!-- /.page-content -->
	
		
	</div>
</div>
<script src="{{ asset('panel/assets/js/jquery-2.1.4.min.js') }}"></script>
<script>
document.getElementById('start_date').addEventListener('change', function () {
    document.getElementById('end_date').value = this.value;
});

</script>

<script src="{{ asset('panel/assets/js/datatablePaginationWithFilter.js') }}"></script>
@endsection