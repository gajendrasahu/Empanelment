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
<div class="tabbable animated bounceInUp" style="padding:0px 0px!important;">
		<ul class="nav nav-tabs padding-0">
		@if(in_array(2,Session::get('actions')) || in_array(3,Session::get('actions')) || in_array(12,Session::get('actions')))
			<li @if(!in_array(1,Session::get('actions'))) class="active" @endif>
				<a class="form-label" data-toggle="tab" href="#recordlist" onclick="loadData(1,'{{ route('participatedeoi.html') }}')">
					<i class="green ace-icon fa fa-list bigger-120 datalist" style="vertical-align:bottom;"></i> Participated EoI List
				</a>
			</li>
		@endif
		</ul>

	
		<div class="tab-content no-border" style="border:1px solid #ddd; min-height:100px; padding:0px 10px;">

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
				<table class="table table-bordered table-striped table-hover">
				<thead>
				<tr>
					<td class="padding-8">
						<select name="pagesize" id="pagesize" class="select2" onchange="loadData(1,'{{ route('participatedeoi.html') }}')" data-width="90">
							<option value="100">100</option>
							<option value="300">300</option>
							<option value="500">500</option>														
						</select>

						<select class="select2" name="departmentid" id="departmentid" onKeyPress="return OnKeyPress(this, event)" onchange="loadData(1,'{{ route('participatedeoi.html') }}');" data-width="350" data-placeholder="Department / Project Manager">
							<option value=""></option>
							@foreach ($departments as $itm)
							<option value="{{ $itm->userid }}">{{ strtoupper($itm->shortname) }}-{{ $itm->departmentname }}</option>
							@endforeach
						</select>
						@if(!session('subUserId'))
						<select class="select2" name="subuserid" id="subuserid" onKeyPress="return OnKeyPress(this, event)" onchange="loadData(1,'{{ route('floatedeoi.html') }}');" data-width="200" data-placeholder="Sub User">
							<option value=""></option>
							@foreach ($subusers as $itm)
							<option value="{{ $itm->userid }}">{{ $itm->name }}</option>
							@endforeach
						</select>
						@else
						<input type="hidden" name="subuserid" id="subuserid" value="">
						@endif
					
						<span class="input-icon">
							<input type="text" placeholder="Search ..." class="nav-search-input" id="pagesearch" name="pagesearch" autocomplete="off" onchange="loadData(1,'{{ route('participatedeoi.html') }}')" tabindex="<?php echo $t++;?>" />
							<i class="ace-icon fa fa-search nav-search-icon"></i>
						</span>
					</td>
				</tr>
				</thead>
				<tbody class="tabledata">
				<tr><td class="center">--Search Record--</td></tr>
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
setTimeout(function() { $('.datalist').trigger('click'); },1000);

function loadData(page,r1)
{
	var pagesize	=	document.getElementById("pagesize").value;
	var departmentid	=	document.getElementById("departmentid").value;
	var subuser_id	=	document.getElementById("subuserid").value;
	var pagesearch	=	document.getElementById("pagesearch").value;
	$.get(""+r1,
	{
		page:page,
		pagesize:pagesize,
		pagesearch:pagesearch,
		departmentid:departmentid,
		subuser_id:subuser_id
	},
	function(data, status){
		$(".tabledata").html(data);
		$(".mytr").click(function(){
			$(".mytr").removeClass("selected-color").addClass("reset-color");
			$(this).removeClass("reset-color").addClass("selected-color");
		});
	});
}

function viewEoiRequestData(r1,requestid)
{
	$.get(""+r1,
	{
		requestid:requestid,
	},
	function(data, status){
		$("#myModal").modal("show");
		$(".modal-content").html(data);
	});
}
function Cls()
{
	$("#myModal").modal("hide");
	$(".modal-content").html("");	
}

</script>
<script src="{{ asset('panel/assets/js/master.js') }}"></script>
<script src="{{ asset('panel/assets/js/datatablePaginationWithFilter.js') }}"></script>
@endsection