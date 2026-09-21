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
			<div class="eoi-section-header" onclick="loadData(1,'{{ route('resources.html') }}')"
				style="cursor:pointer; padding:8px 8px 16px 8px;">

				<div style="font-size:20px; font-weight:600; color:#2c3e50;">
					Resource Details
				</div>

				<div style="font-size:14px; color:#6c757d; margin:2px 0px; 8px 0">
					Explore resource assignments, roles, and linked orders to see who is deployed where.
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
			<div class="table-responsive" style="overflow-x:auto;">
				<table class="table table-bordered table-striped table-hover resource-details-table css-sticky" id="tablerecords">
				<colgroup class="resource-details-colgroup">
					<col data-column="sno" style="width:70px;">
					<col data-column="resource_name" style="width:170px;">
					<col data-column="project" style="width:360px;">
					<col data-column="position" style="width:150px;">
					<col data-column="sector" style="width:150px;">
					<col data-column="role" style="width:130px;">
					<col data-column="eoi" style="width:180px;">
					<col data-column="order_number" style="width:200px;">
					<col data-column="order_date" style="width:120px;">
					<col data-column="dept" style="width:200px;">
					<col data-column="order_value" style="width:140px;">
					<col data-column="duration" style="width:120px;">
					<col data-column="wo_issue_date" style="width:150px;">
					<col data-column="base_rate" style="width:120px;">
					<col data-column="total_cost" style="width:140px;">
				</colgroup>
				<thead>
				<tr>
					<td colspan="15" class="padding-10">
						<div id="tablehead">
							<select name="pagesize" id="pagesize" class="select2 selectbx" onchange="loadData(1,'{{ route('resources.html') }}')" data-width="70" data-placeholder="RECORDS">
								<option value="300">300</option>
								<option value="500">500</option>
								<option value="1000">1000</option>
							</select>
						
							<select class="select2" name="categoryid" id="categoryid" onKeyPress="return OnKeyPress(this, event)" onchange="loadData(1,'{{ route('resources.html') }}');GetTiersWithId(this.value,'{{ route('tiers.list') }}','tierid')" data-width="150" data-placeholder="Category Name">
								<option value="">Category Name</option>
								@foreach ($category as $itm)
								<option value="{{ $itm->categoryid }}">{{ strtoupper($itm->jobcategory) }}</option>
								@endforeach
							</select>
							<select class="select2" name="vendorid" id="vendorid" onKeyPress="return OnKeyPress(this, event)" onchange="loadData(1,'{{ route('resources.html') }}');" data-width="250" data-placeholder="Firm Name">
								<option value=""></option>
								@foreach ($vendors as $itm)
								<option value="{{ $itm->vendorid }}">{{ strtoupper($itm->companyname) }}</option>
								@endforeach
							</select>
							<select class="select2" name="departmentid" id="departmentid" onKeyPress="return OnKeyPress(this, event)" onchange="loadData(1,'{{ route('resources.html') }}');" data-width="250" data-placeholder="Department Name">
								<option value=""></option>
								@foreach ($departments as $itm)
								<option value="{{ $itm->departmentid }}">{{ strtoupper($itm->shortname) }}-{{ strtoupper($itm->departmentname) }}</option>
								@endforeach
							</select>
						
							<span class="input-icon">
								<input type="text" placeholder="Search ..." class="nav-search-input selectbx" id="pagesearch" name="pagesearch" autocomplete="off" onchange="loadData(1,'{{ route('resources.html') }}')" @if($search!='')value="{{str_replace('-','/',$search)}}"@endif tabindex="<?php echo $t++;?>" />
								<i class="ace-icon fa fa-search nav-search-icon" style="margin-top:-3px;"></i>
							</span>
						</div>
						<div class="column-toggle-chips" id="resource-column-toggles">
							<button type="button" class="column-chip column-chip--all active" data-column="all">Select All</button>
							<button type="button" class="column-chip active" data-column="resource_name">Resource Name</button>
							<button type="button" class="column-chip active" data-column="project">Project Name</button>
							<button type="button" class="column-chip active" data-column="position">Position</button>
							<button type="button" class="column-chip active" data-column="sector">Sector</button>
							<button type="button" class="column-chip active" data-column="role">Role</button>
							<button type="button" class="column-chip active" data-column="eoi">EoI</button>
							<button type="button" class="column-chip active" data-column="order_number">Order Number</button>
							<button type="button" class="column-chip active" data-column="order_date">Order Date</button>
							<button type="button" class="column-chip active" data-column="dept">Depart / Manager</button>
							<button type="button" class="column-chip active" data-column="order_value">Order Value</button>
							<button type="button" class="column-chip active" data-column="duration">Duration</button>
							<button type="button" class="column-chip active" data-column="wo_issue_date">WO Issuance Date</button>
							<button type="button" class="column-chip active" data-column="base_rate">Base Rate</button>
							<button type="button" class="column-chip active" data-column="total_cost">Total Cost</button>
						</div>
					</td>
				</tr>
				<tr class="myhead">
					<td class="padding-8" style="width:25px;" nowrap data-column="sno"><b>S.No.</b></td>
					<td nowrap class="padding-8" data-column="resource_name"><b>Resource Name</b></td>
					<td nowrap class="padding-8" data-column="project"><b>Project Name</b></td>
					<td nowrap class="padding-8" data-column="position"><b>Position</b></td>
					<td nowrap class="padding-8" data-column="sector"><b>Sector</b></td>
					<td nowrap class="padding-8" data-column="role"><b>Role</b></td>
					<td nowrap class="padding-8" data-column="eoi"><b>EoI</b></td>
					<td nowrap class="padding-8" data-column="order_number"><b>Order Number</b></td>
					<td nowrap class="padding-8" data-column="order_date"><b>Order Date</b></td>
					<td nowrap class="padding-8" data-column="dept"><b>Depart / Manager </b></td>
					<td nowrap class="padding-8" data-column="order_value"><b>Order Value</b></td>
					<td nowrap class="padding-8" data-column="duration"><b>Duration</b></td>
					<td nowrap class="padding-8" data-column="wo_issue_date"><b>WO Issuance Date</b></td>
					<td nowrap class="padding-8" data-column="base_rate"><b>Base Rate</b></td>
					<td nowrap class="padding-8" data-column="total_cost"><b>Total Cost</b></td>
				</tr>
				</thead>
				<tbody class="tabledata">
				<tr><td colspan="15" class="center padding-8">--Search Record--</td></tr>
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

	loadData(1,'{{ route('resources.html') }}');
	updateResourceStickyOffsets();

	$(document).on('click', '#resource-column-toggles .column-chip', function () {
		var $chip = $(this);
		var column = $chip.data('column');
		if (column === 'all') {
			$('#resource-column-toggles .column-chip').addClass('active');
		} else {
			$chip.toggleClass('active');
		}
		applyResourceColumnVisibility();
		if (window.refreshStickyTables) {
			window.refreshStickyTables(true);
		}
		updateResourceStickyOffsets();
	});

	$(window).on('resize', function () {
		updateResourceStickyOffsets();
	});
});

function updateResourceStickyOffsets() {
	var $table = $('#tablerecords');
	if (!$table.length) {
		return;
	}
	var $filterRow = $table.find('thead tr').first();
	var height = $filterRow.outerHeight() || 0;
	$table[0].style.setProperty('--resource-filters-height', height + 'px');
}
function loadData(page,r1)
{
	var pagesize	=	document.getElementById("pagesize").value;

	var categoryid	=	document.getElementById("categoryid").value;
	var vendorid	=	document.getElementById("vendorid").value;
	var departmentid=	document.getElementById("departmentid").value;

	var pagesearch	=	document.getElementById("pagesearch").value;
	$.get(""+r1,
	{
		page:page,
		pagesize:pagesize,
		pagesearch:pagesearch,
		categoryid:categoryid,
		vendorid:vendorid,
		departmentid:departmentid,
	},
	function(data, status){
		$(".tabledata").html(data);
		applyResourceColumnVisibility();
		if (window.refreshStickyTables) {
			window.refreshStickyTables(true);
		}
	});
}

function applyResourceColumnVisibility() {
	var $table = $('#tablerecords');
	if (!$table.length) {
		return;
	}
	var allActive = true;
	$('#resource-column-toggles .column-chip').each(function () {
		var $chip = $(this);
		var column = $chip.data('column');
		if (column === 'all') {
			return;
		}
		var $cells = $table.find('th[data-column="' + column + '"], td[data-column="' + column + '"], col[data-column="' + column + '"]');
		if ($chip.hasClass('active')) {
			$cells.removeClass('hidden-column');
			$cells.filter('col').each(function () {
				var $col = $(this);
				var orig = $col.data('orig-width');
				if (orig !== undefined) {
					if (orig) {
						$col.attr('style', orig);
					} else {
						$col.removeAttr('style');
					}
				}
			});
		} else {
			$cells.addClass('hidden-column');
			$cells.filter('col').each(function () {
				var $col = $(this);
				if ($col.data('orig-width') === undefined) {
					$col.data('orig-width', $col.attr('style') || '');
				}
				$col.css({
					width: '0px',
					minWidth: '0px',
					maxWidth: '0px'
				});
			});
			allActive = false;
		}
	});
	var $allChip = $('#resource-column-toggles .column-chip--all');
	if (allActive) {
		$allChip.addClass('active');
	} else {
		$allChip.removeClass('active');
	}
}

</script>
<script src="{{ asset('panel/assets/js/master.js') }}"></script>
<script src="{{ asset('panel/assets/js/datatablePaginationWithFilter.js') }}"></script>
@endsection
