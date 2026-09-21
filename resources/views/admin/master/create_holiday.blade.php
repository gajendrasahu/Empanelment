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
					<i class="green ace-icon fa fa-plus-circle bigger-120" style="vertical-align:bottom;"></i>Add Holiday
				</a>
			</li>

			<li @if(!in_array(1,Session::get('actions'))) class="active" @endif>
				<a data-toggle="tab" href="#recordlist" onclick="loadData(1,'{{ route('holiday.html') }}')">
					<i class="green ace-icon fa fa-list bigger-120 datalist" style="vertical-align:bottom;"></i> Holiday List
				</a>
			</li>

			<li>
				<a data-toggle="tab" href="#calendardata" onclick="calendarView('{{ route('calendar.view') }}')">
					<i class="green ace-icon fa fa-calendar bigger-120 calendarview" style="vertical-align:bottom;"></i> Calendar View
				</a>
			</li>
		</ul>

	
		<div class="tab-content no-border" style="border:1px solid #ddd; min-height:100px; padding:0px 10px;">
@if(in_array(1,Session::get('actions')))
<div id="home" class="tab-pane in active" style="padding:0px 10px!important;">
	<div class="row">
		<div class="row">
			<form name="frm" id="frm" action="{{ route('store.holiday')}}" method="post" enctype="multipart/form-data">
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
						<input type="text" class="form-control" name="holiday_title" id="holiday_title" value="{{old('holiday_title')}}" placeholder="Holiday Title" autofocus required autocomplete="off" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}"/>
						
						<span class="text-danger">@error('holiday_title') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
					</div>
					<div class="col-sm-3">
						Start Date <label id="req">*</label>
						<input type="text" class="form-control todays_dt_blank" name="start_date" id="start_date" value="{{old('start_date')}}" placeholder="dd-mm-YYYY" required autocomplete="off" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}"/>
						
						<span class="text-danger">@error('start_date') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
					</div>
					<div class="col-sm-3">
						End Date <label id="req">*</label>
						<input type="text" class="form-control todays_dt_blank" name="end_date" id="end_date" value="{{old('end_date')}}" placeholder="dd-mm-YYYY" required autocomplete="off" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}"/>
						
						<span class="text-danger">@error('end_date') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
					</div>
					<div class="col-sm-12">&nbsp;</div>
					<div class="col-sm-2">
						<label style="margin-top:15px;">
							<input type="checkbox" name="is_recurring" value="1"> Is Recurring?
						</label>					
					</div>
					<div class="col-sm-3">
						<select name="recurring_type" id="recurring_type" class="form-control">
							<option value="">--Select--</option>
							<option value="yearly">Yearly</option>
							<option value="monthly">Monthly</option>
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
@if(in_array(2,Session::get('actions')) || in_array(3,Session::get('actions')) || in_array(12,Session::get('actions')))
<div id="recordlist" class="tab-pane @if(!in_array(1,Session::get('actions'))) in active @endif" style="padding:0px 0px!important;">
	<div class="row" style="padding:0px;">
		<div class="col-xs-12">
		<form name="pagedata" id="pagedata" action="#" method="post" onsubmit="return false;">
			<div class="table-responsive">			
				<table class="table table-bordered table-striped pd-5" id="tablerecords">
				<thead>
				<tr>
					<td colspan="6">
					<div id="tablehead">
						<select class="select2" name="pagesize" id="pagesize" onchange="loadData(1,'{{ route('holiday.html') }}')">
							<option value="50">50</option>
							<option value="100">100</option>
							<option value="300">300</option>
							<option value="500">500</option>														
						</select>
						<select class="select2" name="holiday_month" id="holiday_month" onchange="loadData(1,'{{ route('holiday.html') }}')" data-placeholder="Month">
							<option value=""></option>
							@foreach($months as $key => $month)
							<option value="{{ $key }}" @if($key == (int) date('m')) selected @endif>{{ $month }}</option>
							@endforeach
						</select>

						<span class="input-icon" style="float:right;">
							<input type="text" placeholder="Search ..." class="nav-search-input" id="pagesearch" name="pagesearch" autocomplete="off" onchange="loadData(1,'{{ route('holiday.html') }}')" tabindex="<?php echo $t++;?>" />
							<i class="ace-icon fa fa-search nav-search-icon" style="margin-top:-3px;"></i>
						</span>
						<input type="checkbox" name="is_recurring" id="is_recurring" onchange="loadData(1,'{{ route('holiday.html') }}')"> Recurring Holiday Only
					</div>
					</td>
				</tr>
				<tr class="myhead">
					<td style="width:25px;" nowrap><b>S.No.</b></td>
					<td nowrap style=""><b>Title</b></td>
					<td nowrap style=""><b>Start Date</b></td>
					<td nowrap style=""><b>End Date</b></td>
					<td style="width:25px;"></td>
					<td style="width:25px;"></td>
				</tr>
				</thead>
				<tbody class="tabledata">
				</tbody>
				</table>
			</div>
		</form>
		</div>
	</div>
</div>
@endif


<div id="calendardata" class="tab-pane">
	<div class="row" style="padding:0px;">
		<div class="col-xs-12">
		<form name="calendar" id="calendar" action="#" method="post" onsubmit="return false;">
			
		<table class="mytable pd-10" style="background-color:white!important;">
		<tbody class="calendarViewData">
		</tbody>
		</table>

		</form>
		</div>
	</div>
</div>


</div>
</div>


						
				</div><!-- /.col -->
			</div><!-- /.row -->
		</div><!-- /.page-content -->
	
		
	</div>
</div>
<script src="{{ asset('panel/assets/js/jquery-2.1.4.min.js') }}"></script>
<script>
@if(!in_array(1,Session::get('actions')))
	setTimeout(function() { $('.datalist').trigger('click'); },1000);
@endif

document.getElementById('start_date').addEventListener('change', function () {
    document.getElementById('end_date').value = this.value;
});

function loadData(page,r1)
{
	var pagesize		=	document.getElementById("pagesize").value;
	var pagesearch		=	document.getElementById("pagesearch").value;
	var holiday_month	=	document.getElementById("holiday_month").value;
	var is_recurring 	= document.getElementById("is_recurring").checked ? 1 : 0;
	$.get(""+r1,
	{
		page:page,
		pagesize:pagesize,
		pagesearch:pagesearch,
		holiday_month:holiday_month,
		is_recurring:is_recurring
	},
	function(data, status){
		$(".tabledata").html(data);
	});
}

function calendarView(r1)
{
	$.get(""+r1,
	{
	},
	function(data, status){
		$(".calendarViewData").html(data);
	});
}

</script>

<script src="{{ asset('panel/assets/js/datatablePaginationWithFilter.js') }}"></script>
@endsection