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
			<li><a data-toggle="tab" href="#recordlist" onclick="loadData(1,'{{ route('permission.html') }}')"><i class="green ace-icon fa fa-list bigger-120" style="vertical-align:bottom;"></i> Permission List</a></li>
		</ul>

	
		<div class="tab-content no-border" style="border:1px solid #ddd; min-height:100px; padding:0px 10px;">

<div id="recordlist" class="tab-pane in active" style="padding:0px 0px!important;">
	<div class="row" style="padding:0px;">
		<div class="col-xs-12" style="padding:0px 2px;">
			<div class="table-responsive">			
				<table class="table table-bordered table-striped">
				<thead>
				<tr>
					<td colspan="20">
					<div id="tablehead">
						<select name="pagesize" id="pagesize" class="selectbx" onchange="loadData(1,'{{ route('permission.html') }}')">
							<option value="100">100</option>
							<option value="200">200</option>
							<option value="300">300</option>
							<option value="500">500</option>														
						</select>
						<select class="select2" name="userid" id="userid" onKeyPress="return OnKeyPress(this, event)"  data-placeholder="USER LIST" onchange="loadData(1,'{{ route('permission.html') }}')">
							<option value=""></option>
							@foreach ($users as $itm)
							<option value="{{ $itm->userid }}">{{$itm->name}}</option>
							@endforeach
						</select>
						<select class="select2" name="parentid" id="parentid" onKeyPress="return OnKeyPress(this, event)"  data-placeholder="MENU LIST" onchange="loadData(1,'{{ route('permission.html') }}')">
							<option value=""></option>
							@foreach ($menus as $itm)
							<option value="{{ $itm->menuid }}">{{ __($itm->mastermenu) }}</option>
							@endforeach
						</select>
						<span class="input-icon" style="float:right;">
							<input type="text" placeholder="Search ..." class="nav-search-input" id="pagesearch" name="pagesearch" autocomplete="off" onchange="loadData(1,'{{ route('permission.html') }}')" tabindex="<?php echo $t++;?>" />
							<i class="ace-icon fa fa-search nav-search-icon" style="margin-top:-3px;"></i>
						</span>
					</div>
					</td>
				</tr>
				</thead>
				<tbody class="tabledata">
				</tbody>
				</table>
			</div>
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




	
	</div>
</div>
<script src="{{ asset('panel/assets/js/jquery-2.1.4.min.js') }}"></script>
<script src="{{ asset('panel/assets/js/ace-elements.min.js') }}"></script>
<script src="{{ asset('panel/assets/js/ace.min.js') }}"></script>
<script src="{{ asset('panel/assets/js/bootstrap-multiselect.min.js') }}"></script>	
<script>

function loadData(page,r1)
{
	var userid	=	document.getElementById("userid").value;
	var parentid	=	document.getElementById("parentid").value;
	var pagesize	=	document.getElementById("pagesize").value;
	var pagesearch	=	document.getElementById("pagesearch").value;
	if(userid!='')
	{
		$.get(""+r1,
		{
			page:page,
			pagesize:pagesize,
			pagesearch:pagesearch,
			userid:userid,
			parentid:parentid
		},
		function(data, status){
			$(".tabledata").html(data);
		});
	}
	else
	{
		$(".tabledata").html('<tr><td colspan="4" style="text-align:center;">--NO RECORD FOUND--</td></tr>');
	}
}
</script>
<script src="{{ asset('panel/assets/js/master.js') }}"></script>
<script src="{{ asset('panel/assets/js/multiselectfunctions.js') }}"></script>
<script src="{{ asset('panel/assets/js/datatablePaginationWithFilter.js') }}"></script>
@endsection