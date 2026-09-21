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
<div class="tabbable animated fadeInUp delay-02" style="padding:0px 0px!important;">
		@if(in_array(2,Session::get('actions')) || in_array(3,Session::get('actions')) || in_array(12,Session::get('actions')))
			<div class="eoi-section-header" onclick="loadData(1,'{{ route('invoicehistory.html') }}')"
				style="cursor:pointer; padding:8px 8px 16px 8px;">

				<div style="font-size:20px; font-weight:600; color:#2c3e50;">
					Monthly Progress Report
				</div>
			</div>
		@endif

		<div class="tab-content no-border" style="min-height:100px; padding:0px 10px;">

@if(in_array(2,Session::get('actions')) || in_array(3,Session::get('actions')) || in_array(12,Session::get('actions')))
<div id="recordlist" class="tab-pane in active" style="padding:0px 0px!important;">
	<div class="row" style="padding:0px;">
		@if(Session::has('success'))
		<div class="col-sm-12" style="padding:0px;">
			<div class="alert alert-block alert-success">
				<button type="button" class="close" data-dismiss="alert">
					<i class="ace-icon fa fa-times"></i>
				</button>
				{{ Session::get('success') }}
			</div>
		</div>
		@endif
		<div class="col-xs-12">
		<form name="pagedata" id="pagedata" action="#" method="post" onsubmit="return false;">
			@csrf
			<div class="table-responsive">
				<table class="table table-bordered table-hover" id="tablerecords">
				<thead>
				<tr>
					<td colspan="4">
						<select class="select2" name="project_id" id="project_id" onKeyPress="return OnKeyPress(this, event)" onchange="loadData(1,'{{ route('invoicehistory.html') }}');" data-width="100%" data-placeholder="Project Name">
							<option value=""></option>
							@foreach ($projects as $itm)
							<option value="{{ $itm->projectid }}">{{ $itm->project_name }}</option>
							@endforeach
						</select>
					
					</td>
				</tr>
				<tr>
					<td colspan="4">
						<select class="select2" name="orderid_id" id="orderid_id" onKeyPress="return OnKeyPress(this, event)" onchange="loadData(1,'{{ route('invoicehistory.html') }}');" data-width="100%" data-placeholder="Work Order Number">
							<option value=""></option>
							@foreach ($orders as $itm)
							<option value="{{ $itm->orderid }}">{{ $itm->ordernumber }}</option>
							@endforeach
						</select>
					
					</td>
				</tr>
				<tr>
					<td colspan="4">
					<div id="tablehead">
						<select name="pagesize" id="pagesize" class="select2" onchange="loadData(1,'{{ route('invoicehistory.html') }}')" data-width="70" data-placeholder="RECORDS">
							<option value="50">50</option>
							<option value="100">100</option>
							<option value="300">300</option>
							<option value="500">500</option>														
						</select>
						<select class="select2" name="mpr_month" id="mpr_month" onKeyPress="return OnKeyPress(this, event)" onchange="loadData(1,'{{ route('invoicehistory.html') }}');" data-width="350" data-placeholder="Month">
							<option value=""></option>
							@foreach ($months as $itm)
							<option value="{{ $itm['value'] }}">{{ $itm['label'] }}</option>
							@endforeach
						</select>

						<select class="select2" name="mpr_year" id="mpr_year" onKeyPress="return OnKeyPress(this, event)" onchange="loadData(1,'{{ route('invoicehistory.html') }}');" data-width="350" data-placeholder="Year">
							<option value=""></option>
							@foreach ($years as $itm)
							<option value="{{ $itm['value'] }}">{{ $itm['label'] }}</option>
							@endforeach
						</select>

						<span class="input-icon">
							<input type="text" placeholder="Search ..." class="nav-search-input selectbx" id="pagesearch" name="pagesearch" autocomplete="off" onchange="loadData(1,'{{ route('invoicehistory.html') }}')" tabindex="<?php echo $t++;?>" />
							<i class="ace-icon fa fa-search nav-search-icon" style="margin-top:-3px;"></i>
						</span>
					</div>
					
					</td>
				</tr>
				<tr class="myhead">
					<td style="width:25px;" nowrap><b>S.No.</b></td>
					<td nowrap><b>Project Name / Invoice Documents</b></td>
					<td nowrap class="center"><b>Invoice Detail</b></td>
					<td nowrap class="center"><b>Amount Detail</b></td>
				</tr>
				</thead>
				<tbody class="tabledata">
				<tr><td colspan="4" class="center">--Search Record--</td></tr>
				</tbody>
				</table>
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

$(document).ready(function() {
	$.ajaxSetup({
		headers: {
			'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
		}
	});

	//loadData(1,'{{ route('invoicehistory.html') }}');
});

function loadData(page,r1)
{
	var pagesize	=	document.getElementById("pagesize").value;
	var pagesearch	=	document.getElementById("pagesearch").value;
	var mpr_month	=	document.getElementById("mpr_month").value;
	var mpr_year	=	document.getElementById("mpr_year").value;
	var subuser_id	=	document.getElementById("subuserid").value;
	var order_id	=	document.getElementById("order_id").value;
	$.get(""+r1,
	{
		page:page,
		pagesize:pagesize,
		pagesearch:pagesearch,
		mpr_month:mpr_month,
		mpr_year:mpr_year,
		subuser_id:subuser_id,
		order_id:order_id
	},
	function(data, status){
		$(".tabledata").html(data);
	});
}

</script>
<script src="{{ asset('panel/assets/js/master.js') }}"></script>
<script src="{{ asset('panel/assets/js/datatablePaginationWithFilter.js') }}"></script>
@endsection
