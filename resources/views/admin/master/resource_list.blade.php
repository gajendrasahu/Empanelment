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
		<ul class="nav nav-tabs padding-0">
			@permission('resources.html')
			<li class="active">
				<a data-toggle="tab" class="form-label font-14" href="#recordlist" onclick="loadData(1,'{{ route('resources.html') }}')">
					<i class="green ace-icon fa fa-list bigger-120 datalist"></i> Resource Details
				</a>
			</li>
			@endpermission
		</ul>

	
		<div class="tab-content no-border" style="border:1px solid #ddd; min-height:100px; padding:0px 10px;">
@permission('resources.html')
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
				<table class="table table-bordered table-hover">
				<tr>
					<td style="position:relative;">
						<select name="pagesize" id="pagesize" class="select2 selectbx" onchange="loadData(1,'{{ route('resources.html') }}')" data-width="100" data-placeholder="RECORDS">
							<option value="300">300</option>
							<option value="500">500</option>
							<option value="1000">1000</option>
						</select>
					
						<select class="select2" name="categoryid" id="categoryid" onKeyPress="return OnKeyPress(this, event)" onchange="loadData(1,'{{ route('resources.html') }}');GetTiersWithId(this.value,'{{ route('tiers.list') }}','tierid')" data-width="200" data-placeholder="Category Name">
							<option value="">Category Name</option>
							@foreach ($category as $itm)
							<option value="{{ $itm->categoryid }}">{{ strtoupper($itm->jobcategory) }}</option>
							@endforeach
						</select>
						<select class="select2" name="vendorid" id="vendorid" onKeyPress="return OnKeyPress(this, event)" onchange="loadData(1,'{{ route('resources.html') }}');" data-width="200" data-placeholder="Firm Name">
							<option value=""></option>
							<optgroup label="Consultancy Firms">
							@foreach ($vendors as $itm)
							@if($itm->categoryid==2)
							<option value="{{ $itm->vendorid }}">{{ $itm->companyname }}</option>
							@endif
							@endforeach
							</optgroup>
							<optgroup label="Application & Web Designin">
							@foreach ($vendors as $itm)
							@if($itm->categoryid==1)
							<option value="{{ $itm->vendorid }}">{{ $itm->companyname }}</option>
							@endif
							@endforeach
							</optgroup>
						</select>
						<select class="select2" name="departmentid" id="departmentid" onKeyPress="return OnKeyPress(this, event)" onchange="loadData(1,'{{ route('resources.html') }}');" data-width="200" data-placeholder="Department Name">
							<option value=""></option>
							@foreach ($departments as $itm)
							<option value="{{ $itm->departmentid }}">{{ strtoupper($itm->shortname) }}-{{ strtoupper($itm->departmentname) }}</option>
							@endforeach
						</select>
@permission('export.resources')
<button type="button" class="eoi-btn action-a" style="position:absolute; right:5px;" onclick="ExportData('{{route('export.resources')}}')">
	<i class="fa fa-file-o"></i> Export Data
</button>
@endpermission
					</td>
				</tr>
				<tr>
					<td>
						<span class="input-icon" style="width:100%!important;">
							<input type="text" placeholder="Search ..." class="nav-search-input selectbx" id="pagesearch" name="pagesearch" autocomplete="off" onchange="loadData(1,'{{ route('resources.html') }}')" @if($search!='')value="{{str_replace('-','/',$search)}}"@endif tabindex="<?php echo $t++;?>" style="width:100%!important;" />
							<i class="ace-icon fa fa-search nav-search-icon" style="margin-top:-3px;"></i>
						</span>
					</td>
				</tr>
				<tbody class="tabledata">
				<tr><td class="center padding-8">--Search Record--</td></tr>
				</tbody>
				</table>
			</div>
		</form>
		</div>
	</div>
</div>
@endpermission


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

<div class="modal fade" id="releaseModal">
  <div class="modal-dialog modal-xl" style="width:95%; margin-top:0px;">
    <div class="modal-content">
	
      <div class="modal-header">
        <h5 class="modal-title" style="float:left;">Resource Release Form</h5>
        <!-- Proper close button for BS3 -->
        <button type="button" class="close" style="float:right;" data-dismiss="modal">&times;</button>
      </div>
      <div class="modal-body" id="releaseData" style="max-height:600px; overflow:auto;">
      </div>
      <div class="modal-footer">
        
      </div>
	</form>
    </div>
  </div>
</div>


<script src="{{ asset('panel/assets/js/jquery-2.1.4.min.js') }}"></script>
<script>
setTimeout(function() { $('.datalist').trigger('click'); },1000);
$(document).ready(function() {
	$.ajaxSetup({
		headers: {
			'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
		}
	});	
});
function ExportData(url)
{
    var categoryid   = document.getElementById("categoryid").value;
    var vendorid     = document.getElementById("vendorid").value;
    var departmentid = document.getElementById("departmentid").value;
    var pagesearch   = document.getElementById("pagesearch").value;

    var params	=	new URLSearchParams({
						pagesearch: pagesearch,
						categoryid: categoryid,
						vendorid: vendorid,
						departmentid: departmentid
					});

    window.location.href = url + '?' + params.toString();
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
	});
}

</script>
<script src="{{ asset('panel/assets/js/master.js') }}"></script>
<script src="{{ asset('panel/assets/js/datatablePaginationWithFilter.js') }}"></script>
@endsection