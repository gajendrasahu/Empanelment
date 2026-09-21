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
			<div class="eoi-section-header" onclick="loadData(1,'{{ route('departmentorderlist.html') }}')"
				style="cursor:pointer; padding:8px 8px 16px 8px;">

				<div style="font-size:20px; font-weight:600; color:#2c3e50;">
					Work Order List
				</div>

				<div style="font-size:14px; color:#6c757d; margin:2px 0px; 8px 0">
					Track active and completed work orders, review key dates, and open details when needed.
				</div>

			</div>
		@endif

		<div class="tab-content no-border" style="min-height:100px; padding:0px 10px;">

@if(in_array(2,Session::get('actions')) || in_array(3,Session::get('actions')) || in_array(12,Session::get('actions')))
<div id="recordlist" class="tab-pane in active" style="padding:0px 0px!important;">
	<div class="row" style="padding:0px;">
		@if(Session::has('success'))
		<div class="col-sm-12 padding-8">
			<div class="alert alert-block alert-success">
				<button type="button" class="close" data-dismiss="alert">
					<i class="ace-icon fa fa-times"></i>
				</button>
				{{ Session::get('success') }}
			</div>
		</div>
		@endif
		@if(Session::has('fail'))
		<div class="col-sm-12 padding-8">
			<div class="alert alert-block alert-warning">
				<button type="button" class="close" data-dismiss="alert">
					<i class="ace-icon fa fa-times"></i>
				</button>
				{{ Session::get('fail') }}
			</div>
		</div>
		@endif
		<div class="col-xs-12">
		<form name="pagedata" id="pagedata" action="#" method="post" onsubmit="return false;">
			@csrf
			<div class="table-responsive">
				<table class="table table-bordered table-striped">
				<thead>
				<tr>
					<td class="padding-5" colspan="5">
						<select name="pagesize" id="pagesize" class="select2" onchange="loadData(1,'{{ route('departmentorderlist.html') }}')" data-width="90" data-placeholder="RECORDS">
							<option value="50">50</option>
							<option value="100">100</option>
							<option value="300">300</option>
							<option value="500">500</option>														
						</select>

						<select class="select2" name="vendorid" id="vendorid" onKeyPress="return OnKeyPress(this, event)" onchange="loadData(1,'{{ route('departmentorderlist.html') }}');" data-width="250" data-placeholder="Firm Name">
							<option value=""></option>
							@foreach ($vendors as $itm)
							<option value="{{ $itm->vendorid }}">{{ $itm->companyname }}</option>
							@endforeach
						</select>
						<select name="is_expired" id="is_expired" class="select2" onchange="loadData(1,'{{ route('departmentorderlist.html') }}')"  data-width="175" data-placeholder="All Order">
							<option value="">All Order</option>
							<option value="1">Expired Only</option>
							<option value="2">Expiring Within 60 Days</option>
							<option value="3" selected>Active Orders</option>
							<option value="4">Extended Orders</option>
							<option value="5">Cancelled Orders</option>
						</select>

					
						<span class="input-icon">
							<input type="text" placeholder="Search ..." class="nav-search-input" id="pagesearch" name="pagesearch" autocomplete="off" onchange="loadData(1,'{{ route('departmentorderlist.html') }}')" tabindex="<?php echo $t++;?>" @if($search!='')value="{{str_replace('-','/',$search)}}"@endif />
							<i class="ace-icon fa fa-search nav-search-icon" style="margin-top:-3px;"></i>
						</span>
					</td>
				</tr>
				</thead>
				<tbody class="tabledata">
				<tr><td class="center padding-6">--Search Record--</td></tr>
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

<div class="modal fade left" id="myModal" role="dialog" data-backdrop="static">
	<div class="modal-lg-dialog">
		<div class="modal-content" style="font-size:12px; width:80%; margin:0 auto; margin-top:10px; background-color:white;">
		
		</div>
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

	loadData(1,'{{ route('departmentorderlist.html') }}');
});

function loadData(page,r1)
{
	var pagesize	=	document.getElementById("pagesize").value;
	var pagesearch	=	document.getElementById("pagesearch").value;
	var vendorid	=	document.getElementById("vendorid").value;
	var is_expired	=	document.getElementById("is_expired").value;
	
	$.get(""+r1,
	{
		page:page,
		pagesize:pagesize,
		pagesearch:pagesearch,
		vendorid:vendorid,
		is_expired:is_expired,
	},
	function(data, status){
		$(".tabledata").html(data);
		$(".mytr").click(function(){
			$(".mytr").removeClass("selected-color").addClass("reset-color");
			$(this).removeClass("reset-color").addClass("selected-color");
		});
	});
}

</script>
<script src="{{ asset('panel/assets/js/master.js') }}"></script>
<script src="{{ asset('panel/assets/js/datatablePaginationWithFilter.js') }}"></script>
@endsection
