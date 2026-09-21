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
		@if(in_array(1,Session::get('actions')))
			<li class="active">
				<a data-toggle="tab" href="#home" class="form-label">
					<i class="green ace-icon fa fa-users bigger-120 datalist" style="vertical-align:bottom;"></i> Empanelled Firms
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
				<tr>
					<td class="padding-5" colspan="3">
						<select name="pagesize" id="pagesize" class="select2" data-width="90" onchange="loadData(1,'{{ route('pmvendors.html') }}')">
							<option value="100">100</option>
							<option value="300">300</option>
							<option value="500">500</option>														
						</select>
						<select name="categoryid" id="categoryid" class="select2" onKeyPress="return OnKeyPress(this, event)" data-width="200" data-placeholder="Empanelment Type" onchange="loadData(1,'{{ route('pmvendors.html') }}')">
							<option value="">All</option>
							@foreach ($category as $itm)
							<option value="{{ $itm->categoryid }}">{{ strtoupper($itm->jobcategory) }}</option>
							@endforeach
						</select>						
						<select name="tierid" id="tierid" class="select2" onKeyPress="return OnKeyPress(this, event)" data-width="150" data-placeholder="Tier" onchange="loadData(1,'{{ route('pmvendors.html') }}')">
							<option value=""></option>
							@foreach ($tier as $itm)
							<option value="{{ $itm->tierid }}">{{ strtoupper($itm->tiername) }}</option>
							@endforeach
						</select>						
					</td>
				</tr>
			
				<tr class="myhead">
					<td class="padding-10" style="width:25px;" nowrap><b>S.No.</b></td>
					<td class="padding-10" nowrap style=""><b>Firm Name</b></td>
					<td class="padding-10" nowrap style=""><b>Tier</b></td>
				</tr>
				</thead>
				<tbody class="tabledata">
					<tr><td class="padding-10 center" colspan="3">--Search Record--</td></tr>
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
setTimeout(function() { loadData(1,'{{ route('pmvendors.html') }}'); },1000);

function loadData(page,r1)
{
	var pagesize		=	document.getElementById("pagesize").value;
	var tierid			=	document.getElementById("tierid").value;
	var categoryid		=	document.getElementById("categoryid").value;
	$.get(""+r1,
	{
		page:page,
		pagesize:pagesize,
		tierid:tierid,
		categoryid:categoryid
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