@extends('admin.admin_master')
@section('admin')
<link rel="stylesheet" href="{{ asset('panel/assets/css/finance.css') }}" />
@php
$t=0;
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
	<li class="active">
		<a data-toggle="tab" href="#departmentinvoicelist">
			<i class="green ace-icon mt-3 fa fa-list-alt bigger-120"></i> Department Invoice List
		</a>
	</li>
</ul>

<div class="tab-content no-border" style="min-height:100px; border-radius:0px!important;">
<div id="departmentinvoicelist" class="tab-pane in active">
	<form name="invoice_data" id="invoice_data" action="#" method="post" onsubmit="return false;">
		@if(Session::has('success'))
		<div class="col-sm-12 mt-10">
			<div class="alert alert-block alert-success">
				<button type="button" class="close" data-dismiss="alert">
					<i class="ace-icon fa fa-times"></i>
				</button>
				{{ Session::get('success') }}
			</div>
		</div>
		@endif
		@if(Session::has('error'))
		<div class="col-sm-12 mt-10">
			<div class="alert alert-block alert-danger">
				<button type="button" class="close" data-dismiss="alert">
					<i class="ace-icon fa fa-times"></i>
				</button>
				{{ Session::get('error') }}
			</div>
		</div>
		@endif
		<div class="table-responsive">
			<table class="table table-bordered table-striped table-hover" id="tablerecords">
			<thead>
			<tr>
				<td colspan="10">
					<select name="page_size" id="page_size" class="select2" data-width="95">
						<option value="100">100</option>
						<option value="200">200</option>
						<option value="300">300</option>
						<option value="500">500</option>														
					</select>
					<select class="select2" name="project_id" id="project_id" data-width="200"	data-placeholder="Project Name">
						<option value=""></option>
						@foreach ($projects as $project)
						<option value="{{ $project->projectid }}">{{ $project->project_name }}</option>
						@endforeach
					</select>
					
					<select class="select2" name="department_id" id="department_id" data-width="200"	data-placeholder="Departments">
						<option value=""></option>
						@foreach ($departments as $itm)
							<option value="{{ $itm->departmentid }}">
								{{ strtoupper($itm->shortname) }}-{{ $itm->departmentname }}
							</option>
						@endforeach
					</select>
					
					<span class="input-icon" style="width:250px!important; height:38px!important; line-height:38px!important;">
						<input type="text" placeholder="Search ..." class="" id="pagesearch" name="pagesearch" autocomplete="off" tabindex="<?php echo $t++;?>" style="width:250px!important; border-radius:5px; height:38px!important; line-height:38px!important;" />
						<i class="ace-icon fa fa-search nav-search-icon padding-0"></i>
					</span>
					<button type="submit" class="btn btn-info gridbtn invoiceBtn width-100" onclick="loadInvoiceData(1,'{{ route('finance.departmentinvoice.invoicelist') }}')" tabindex="{{$t++}}">
						<i class="fa fa-search"></i> Search
					</button>
				</td>
			</tr>
			<tr class="myheadbg">
				<td style="width:25px;" nowrap><b>S.No.</b></td>
				<td class="no_wrap"><b>Invoice Number</b></td>
				<td class="text-center"><b>Date</b></td>
				<td><b>Project & Department</b></td>
				<td class="text-right"><b>Taxable</b></td>
				<td class="text-right"><b>CGST</b></td>
				<td class="text-right"><b>SGST</b></td>
				<td class="text-right"><b>IGST</b></td>
				<td class="no_wrap text-right"><b>Invoice Amount</b></td>
				<td nowrap class="center"><b>Action</b></td>
			</tr>
			</thead>
			<tbody class="tabledata">
			<tr><td class="center" colspan="10">--Department Invoice List--</td></tr>
			</tbody>
			</table>
		</div>
	</form>
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
function loadInvoiceData(page, r1)
{
	var pagesize 	= document.getElementById("page_size").value;
	var projectid 	= document.getElementById("project_id").value;
	var departmentid= document.getElementById("department_id").value;
	var pagesearch 	= document.getElementById("pagesearch").value;
	
	$.get(""+r1,
	{
		page:page,
		pagesize:pagesize,
		pagesearch:pagesearch,
		departmentid:departmentid,
		projectid:projectid
	},
	function (data, status) {
		$(".tabledata").html(data);
		
		const paginationLinks	=	document.querySelectorAll('.pagination a');
		
		paginationLinks.forEach(link => {
			link.onclick = function (event) {
				event.preventDefault(); // Prevent default link behavior
				const page = this.getAttribute('href').split('page=')[1]; // Extract page number from URL
				const url = '{{ route('finance.departmentinvoice.invoicelist') }}'; // URL generated by Laravel route
				loadInvoiceData(page, url); // Call custom function with page number and URL
			};
		});	
		
	});
}

</script>

<script src="{{ asset('panel/assets/js/datatablePaginationWithFilter.js') }}"></script>
@endsection