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
			<li class="active">
				<a data-toggle="tab" class="form-label font-14" href="#recordlist" onclick="loadData(1,'{{ route('resumes.html') }}')">
					<i class="green ace-icon fa fa-list bigger-120 datalist"></i> EoI Resumes
				</a>
			</li>
		</ul>

	
		<div class="tab-content no-border" style="border:1px solid #ddd; min-height:100px; padding:0px 10px;">

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
				<table class="table-bordered pd-5" style="border:1px solid #eee!important; border-collapse:collapse;" border="1">
				<tr>
					<td colspan="5" style="position:relative;">
						<select name="pagesize" id="pagesize" class="select2 selectbx" onchange="loadData(1,'{{ route('resumes.html') }}')" data-width="100" data-placeholder="RECORDS">
							<option value="25">25</option>
							<option value="50">50</option>
							<option value="100">100</option>
							<option value="300">300</option>
						</select>
					
						<select class="select2" name="categoryid" id="categoryid" onKeyPress="return OnKeyPress(this, event)" onchange="loadData(1,'{{ route('resumes.html') }}')" data-width="185" data-placeholder="Category Name">
							<option value="">Category Name</option>
							@foreach ($category as $itm)
							<option value="{{ $itm->categoryid }}">{{ strtoupper($itm->jobcategory) }}</option>
							@endforeach
						</select>
						<select class="select2" name="vendorid" id="vendorid" onKeyPress="return OnKeyPress(this, event)" onchange="loadData(1,'{{ route('resumes.html') }}');" data-width="185" data-placeholder="Firm Name">
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
						<select class="select2" name="departmentid" id="departmentid" onKeyPress="return OnKeyPress(this, event)" onchange="loadData(1,'{{ route('resumes.html') }}');" data-width="185" data-placeholder="Department Name">
							<option value=""></option>
							@foreach ($departments as $itm)
							<option value="{{ $itm->departmentid }}">{{ strtoupper($itm->shortname) }}-{{ strtoupper($itm->departmentname) }}</option>
							@endforeach
						</select>
						<select class="select2" name="managerid" id="managerid" onKeyPress="return OnKeyPress(this, event)" onchange="loadData(1,'{{ route('resumes.html') }}');" data-width="185" data-placeholder="Manager Name">
							<option value=""></option>
							@foreach ($managers as $itm)
							<option value="{{ $itm->departmentid }}">{{ strtoupper($itm->shortname) }}-{{ strtoupper($itm->departmentname) }}</option>
							@endforeach
						</select>
						<button type="button" class="btn btn-info gridbtn width-100" onclick="loadData(1,'{{ route('resumes.html') }}');"><i class="fa fa-list"></i> Get List</button>
						
					</td>
				</tr>
				<tr>
					<td colspan="5" class="v-middle">
						<div style="display: flex; align-items: center; gap: 3px; flex-wrap: wrap;">
						<input type="text" name="page_search" id="page_search" autocomplete="off" onchange="loadData(1,'{{ route('resumes.html') }}')" style="padding:8px; border-radius:5px;" placeholder="Search name">
						
						<input type="text" name="eoi_number" id="eoi_number" autocomplete="off" onchange="loadData(1,'{{ route('resumes.html') }}')" style="padding:8px; border-radius:5px;" placeholder="Search EoI number">
						

						<select class="select2" name="sectorid" id="sectorid" onKeyPress="return OnKeyPress(this, event)" onchange="loadData(1,'{{ route('resumes.html') }}')" data-width="185" data-placeholder="Sector">
							<option value="">Sector</option>
							@foreach ($sectors as $itm)
							<option value="{{ $itm->sectorid }}">{{ ucwords(strtolower($itm->sectorname)) }}</option>
							@endforeach
						</select>
						<select class="select2" name="positionid" id="positionid" onKeyPress="return OnKeyPress(this, event)" onchange="loadData(1,'{{ route('resumes.html') }}')" data-width="185" data-placeholder="Position">
							<option value="">Position</option>
							@foreach ($positions as $itm)
							<option value="{{ $itm->positionid }}">{{ $itm->consultantposition }}</option>
							@endforeach
						</select>

						<select class="select2" name="experiencelevel" id="experiencelevel" onKeyPress="return OnKeyPress(this, event)" onchange="loadData(1,'{{ route('resumes.html') }}')" data-width="185" data-placeholder="Experience Level">
							<option value="">Experience Level</option>
							@foreach ($experiencelevels as $itm)
							<option value="{{ $itm->experiencelevel }}">Level - {{ $itm->experiencelevel }}</option>
							@endforeach
						</select>
						</div>
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



</div>
</div>


						
				</div><!-- /.col -->
			</div><!-- /.row -->
		</div><!-- /.page-content -->
	
		
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

function loadData(page,r1)
{
	var pagesize	=	document.getElementById("pagesize").value;
	var categoryid	=	document.getElementById("categoryid").value;
	var vendorid	=	document.getElementById("vendorid").value;
	var departmentid=	document.getElementById("departmentid").value;
	var managerid	=	document.getElementById("managerid").value;
	var sectorid	=	document.getElementById("sectorid").value;
	var positionid	=	document.getElementById("positionid").value;
	var experiencelevel=	document.getElementById("experiencelevel").value;
	var pagesearch	=	document.getElementById("page_search").value;
	var eoi_number	=	document.getElementById("eoi_number").value;
	
	$.get(""+r1,
	{
		page:page,
		pagesize:pagesize,
		pagesearch:pagesearch,
		categoryid:categoryid,
		vendorid:vendorid,
		departmentid:departmentid,
		managerid:managerid,
		sectorid:sectorid,
		positionid:positionid,
		experiencelevel:experiencelevel,
		eoi_number:eoi_number
	},
	function(data, status){
		$(".tabledata").html(data);

		const paginationLinks = document.querySelectorAll('.pagination a');
				paginationLinks.forEach(link => {
					link.onclick = function(event) {
						event.preventDefault(); // Prevent default link behavior
						const page = this.getAttribute('href').split('page=')[1]; // Extract page number from URL
						const url = '{{ route('resumes.html') }}'; // URL generated by Laravel route
						loadData(page, url); // Call custom function with page number and URL
					};
				});

		
	});
}

</script>
<script src="{{ asset('panel/assets/js/master.js') }}"></script>
<script src="{{ asset('panel/assets/js/datatablePaginationWithFilter.js') }}"></script>
@endsection