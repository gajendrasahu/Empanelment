@extends('admin.admin_master')
@section('admin')
@php
$t=1;
@endphp

<div class="main-content" style="background-color:white!important;">
	<div class="main-content-inner">
			<div class="page-content" style="padding:10px 15px!important;">
				<div class="ace-settings-container" id="ace-settings-container"></div>
				<div class="row">
					<div class="col-xs-12">
					<!-- PAGE CONTENT BEGINS -->
<div class="tabbable" style="padding:0px 0px!important;">
		<ul class="nav nav-tabs padding-0">
		@if(in_array(1,Session::get('actions')))
			<li class="active">
				<a data-toggle="tab" href="#home" class="form-label">
					<i class="green ace-icon fa fa-plus-circle bigger-120 datalist" style="vertical-align:bottom;"></i> Rate List
				</a>
			</li>
		@endif
		</ul>

	
		<div class="tab-content no-border" style="border:1px solid #ddd; min-height:100px; padding:0px 10px;">
@if(in_array(2,Session::get('actions')) || in_array(3,Session::get('actions')) || in_array(12,Session::get('actions')))
<div id="recordlist" class="tab-pane in active" style="padding:0px 0px!important;">
	<div class="row" style="padding:0px;">
		<div class="col-xs-12">
		<form name="pagedata" id="pagedata" action="#" method="post" onsubmit="return false;">
			<div class="table-responsive">			
				<table class="table table-bordered table-striped table-hover" id="tablerecords">
				<thead>
				<!--
				<tr>
					<td colspan="7">
					<div class="bg-info padding-10 font-13">
						<span class="font-bold"><i class="fa fa-info-circle"></i> Notice: Annual Price Revision Policy</span><br>
						As per policy, resource prices will be subject to an annual revision of 5% increase effective from 1st June each year. This revision will be automatically applicable to all applicable rate list items unless otherwise specified.<br><br>
						All departments are requested to take note of this update and plan their budgets and resource allocations accordingly.
					</div>
					</td>
				</tr>
				-->
				<tr>
					<td class="padding-5" colspan="7">
						<select name="pagesize" id="pagesize" data-width="90" class="select2" onchange="loadData(1,'{{ route('deptratelist.html') }}')">
							<option value="100">100</option>
							<option value="300">300</option>
							<option value="500">500</option>														
						</select>
						<select class="select2" name="sectid" id="sectid" onKeyPress="return OnKeyPress(this, event)" data-width="220"  data-placeholder="SECTOR NAME" onchange="loadData(1,'{{ route('deptratelist.html') }}')">
							<option value="">--Sector Name--</option>
							@foreach ($sector as $itm)
							<option value="{{ $itm->sectorid }}">{{ strtoupper($itm->sectorname) }}</option>
							@endforeach
						</select>
						<select class="select2" name="posid" id="posid" onKeyPress="return OnKeyPress(this, event)" data-width="220"  data-placeholder="POSITION NAME" onchange="loadData(1,'{{ route('deptratelist.html') }}')">
							<option value="">--Position Name--</option>
							@foreach ($position as $itm)
							<option value="{{ $itm->positionid }}">{{ strtoupper($itm->consultantposition) }}</option>
							@endforeach
						</select>
						<select class="select2" name="trid" id="trid" onKeyPress="return OnKeyPress(this, event)" data-width="220" data-placeholder="TIER NAME" onchange="loadData(1,'{{ route('deptratelist.html') }}')">
							<option value="">--Select Tier--</option>
							@foreach ($tier as $itm)
							<option value="{{ $itm->tierid }}">{{ strtoupper($itm->tiername) }}</option>
							@endforeach
						</select>
						
						<button type="button" class="btn btn-info gridbtn width-100" onclick="loadData(1,'{{ route('deptratelist.html') }}')">
							<i class="fa fa-list"></i> Get List
						</button>
					</td>
				</tr>
			
				<tr class="myhead">
					<td class="padding-8" style="width:25px;" nowrap><b>S.No.</b></td>
					<td class="padding-8" nowrap style=""><b>Sector Name</b></td>
					<td class="padding-8" nowrap style=""><b>Position</b></td>
					<td class="padding-8" nowrap style=""><b>Tier</b></td>
					<td class="padding-8" nowrap style=""><b>Remuneration (A)</b></td>
					<td class="padding-8" nowrap style=""><b>Including Tax(B)</b></td>
					<td class="padding-8" nowrap style=""><b>Including Admin Charge (C)</b></td>
				</tr>
				</thead>
				<tbody class="tabledata">
					<tr><td class="padding-10 center" colspan="7">--Search Record--</td></tr>
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

function loadData(page,r1)
{
	var pagesize		=	document.getElementById("pagesize").value;
	var tierid			=	document.getElementById("trid").value;
	var sectorid		=	document.getElementById("sectid").value;
	var positionid		=	document.getElementById("posid").value;
	$.get(""+r1,
	{
		page:page,
		pagesize:pagesize,
		tierid:tierid,
		sectorid:sectorid,
		positionid:positionid
	},
	function(data, status){
		$(".tabledata").html(data);
		$(".mytr").click(function(){
			$(".mytr").removeClass("selected-color").addClass("reset-color");
			$(this).removeClass("reset-color").addClass("selected-color");
		});
	});
}

function GetForm(url)
{
	var categoryid		=	document.getElementById("categoryid").value;
	var tierid			=	document.getElementById("trid").value;
	$.get(""+url,
	{
		categoryid:categoryid,
		tierid:tierid,
	},
	function(data, status){
		$(".remunerationform").html(data);
		$('.chosen-select').chosen({allow_single_deselect:true}); 	
	});
}

</script>
<script src="{{ asset('panel/assets/js/master.js') }}"></script>
<script src="{{ asset('panel/assets/js/datatablePaginationWithFilter.js') }}"></script>
@endsection