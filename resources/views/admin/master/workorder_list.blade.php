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
			<div class="eoi-section-header" onclick="loadData(1,'{{ route('orderlist.html') }}')"
				style="cursor:pointer; padding:8px 8px 16px 8px; position:relative;">

				<div style="font-size:20px; font-weight:600; color:#2c3e50;">
					Work Order List
				</div>

				<div style="font-size:14px; color:#6c757d; margin:2px 0px; 8px 0">
					Track active and completed work orders, review key dates, and open details when needed.
				</div>

				<button type="button" class="eoi-btn action-a" onclick="ExportData('{{route('export.orderdata')}}')" style="position:absolute; right:5px; top:15px;">
					<i class="fa fa-file-o"></i> Export Data
				</button>

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
				<table class="table table-bordered table-striped">
				<thead>
				<tr>
					<td style="position:relative; padding:5px!important;">
						<select name="pagesize" id="pagesize" class="select2 selectbx" onchange="loadData(1,'{{ route('orderlist.html') }}')" data-width="90" data-placeholder="RECORDS">
							<option value="100">100</option>
							<option value="300">300</option>
							<option value="500">500</option>														
						</select>
					
						<select class="select2" name="categoryid" id="categoryid" onKeyPress="return OnKeyPress(this, event)" onchange="loadData(1,'{{ route('orderlist.html') }}');GetTiersWithId(this.value,'{{ route('tiers.list') }}','tierid')" data-width="175" data-placeholder="Category Name">
							<option value="">Category Name</option>
							@if($firm_type)
							@foreach ($category as $itm)
							<option value="{{ $itm->categoryid }}" @if($firm_type==$itm->categoryid) selected @endif>{{ $itm->jobcategory }}</option>
							@endforeach
							@else
							@foreach ($category as $itm)
							<option value="{{ $itm->categoryid }}" @if($itm->categoryid==2) selected @endif>{{ $itm->jobcategory }}</option>
							@endforeach								
							@endif
						</select>
						<select class="select2" name="vendorid" id="vendorid" onKeyPress="return OnKeyPress(this, event)" onchange="loadData(1,'{{ route('orderlist.html') }}');" data-width="175" data-placeholder="Firm Name">
							<option value=""></option>
							@foreach ($vendors as $itm)
							<option value="{{ $itm->vendorid }}">{{ $itm->companyname }}</option>
							@endforeach
						</select>
						<select class="select2" name="departmentid" id="departmentid" onKeyPress="return OnKeyPress(this, event)" onchange="loadData(1,'{{ route('orderlist.html') }}');" data-width="175" data-placeholder="Departments">
							<option value=""></option>
							@foreach ($departments as $itm)
							<option value="{{ $itm->departmentid }}">{{ $itm->shortname }}-{{ $itm->departmentname }}</option>
							@endforeach
						</select>

						<select class="select2" name="managerid" id="managerid" onKeyPress="return OnKeyPress(this, event)" onchange="loadData(1,'{{ route('orderlist.html') }}');" data-width="175" data-placeholder="Project Managers">
							<option value=""></option>
							@foreach ($managers as $itm)
							<option value="{{ $itm->departmentid }}">{{ $itm->departmentname }}</option>
							@endforeach
						</select>

						<select name="is_expired" id="is_expired" class="select2 selectbx" onchange="loadData(1,'{{ route('orderlist.html') }}')"  data-width="175" data-placeholder="All Order">
							<option value="">All Order</option>
							<option value="1">Expired Only</option>
							<option value="6">Expiring Within 30 Days</option>
							<option value="5">Expiring Within 45 Days</option>
							<option value="2">Expiring Within 60 Days</option>
							<option value="3" selected>Active Orders</option>
							<option value="4">Extended Orders</option>
						</select>


						
					</td>
				</tr>
				<tr>
					<td style="position:relative; padding:5px!important;">
						<span class="input-icon" style="width:450px;">
							<input type="text" placeholder="Search ..." class="nav-search-input" id="pagesearch" name="pagesearch" autocomplete="off" onchange="loadData(1,'{{ route('orderlist.html') }}')" @if($search!='')value="{{$search}}"@endif tabindex="<?php echo $t++;?>" style="width:100%!important;" />
							<i class="ace-icon fa fa-search nav-search-icon" style="margin-top:0px;"></i>
						</span>

						<select class="select2" name="projectid" id="projectid" onKeyPress="return OnKeyPress(this, event)" onchange="loadData(1,'{{ route('orderlist.html') }}');" data-width="175" data-placeholder="Project Name">
							<option value=""></option>
							@foreach($projects as $project)
							<option value="{{ $project->projectid }}">{{ $project->project_name }}</option>
							@endforeach
						</select>
						
						<span style="margin-left:5px;">
						  <input type="checkbox" name="in_house" id="in_house" onchange="loadData(1,'{{ route('orderlist.html') }}')" style="vertical-align:text-top;">
						  <label for="in_house">In House Consultants</label>
						</span>

						<span style="margin-left:5px;">
						  <input type="checkbox" name="is_cancelled" id="is_cancelled" onchange="loadData(1,'{{ route('orderlist.html') }}')" style="vertical-align:text-top;">
						  <label for="is_cancelled">Cancelled Order</label>
						</span>
					
					</td>
				</tr>
				</thead>
				<tbody class="tabledata">
				<tr><td class="center padding-8">--Search Record--</td></tr>
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

	loadData(1,'{{ route('orderlist.html') }}');
});

function ExportData(url)
{

	var is_expired  = 	document.getElementById("is_expired").value;
	var categoryid	=	document.getElementById("categoryid").value;
	var projectid	=	document.getElementById("projectid").value;
	var vendorid	=	document.getElementById("vendorid").value;
	var departmentid=	document.getElementById("departmentid").value;
	var managerid=	document.getElementById("managerid").value;
	var pagesearch	=	document.getElementById("pagesearch").value;
	var is_cancelled = 	document.getElementById("is_cancelled").checked;
	var in_house 	= 	document.getElementById("in_house").checked;
	var params	=	new URLSearchParams({
						is_expired: is_expired,
						categoryid: categoryid,
						projectid:projectid,
						vendorid:vendorid,
						departmentid:departmentid,
						managerid:managerid,
						pagesearch:pagesearch,
						is_cancelled:is_cancelled,
						in_house:in_house
					});
	window.location.href = url + '?' + params.toString();		
}

function loadData(page,r1)
{
	var is_expired  = 	document.getElementById("is_expired").value;
	var pagesize	=	document.getElementById("pagesize").value;
	var categoryid	=	document.getElementById("categoryid").value;
	var projectid	=	document.getElementById("projectid").value;
	var vendorid	=	document.getElementById("vendorid").value;
	var departmentid=	document.getElementById("departmentid").value;
	var managerid=	document.getElementById("managerid").value;
	var pagesearch	=	document.getElementById("pagesearch").value;
	var is_cancelled = document.getElementById("is_cancelled").checked;
	var in_house 	= 	document.getElementById("in_house").checked;
	$.get(""+r1,
	{
		page:page,
		pagesize:pagesize,
		pagesearch:pagesearch,
		categoryid:categoryid,
		projectid:projectid,
		vendorid:vendorid,
		departmentid:departmentid,
		managerid:managerid,
		is_expired:is_expired,
		is_cancelled:is_cancelled,
		in_house:in_house
	},
	function(data, status){
		$(".tabledata").html(data);
		$(".mytr").click(function(){
			$(".mytr").removeClass("selected-color").addClass("reset-color");
			$(this).removeClass("reset-color").addClass("selected-color");
		});
		$(document).off("click", ".floateoiBtn").on("click", ".floateoiBtn", function() {
			var requestid = $(this).data("requestid");
			var ind = $(this).data("ind");
			FloatEoi(requestid, ind);
		});		
	});
}

</script>
<script src="{{ asset('panel/assets/js/master.js') }}"></script>
<script src="{{ asset('panel/assets/js/datatablePaginationWithFilter.js') }}"></script>
@endsection
