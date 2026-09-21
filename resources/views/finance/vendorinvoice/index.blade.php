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
			<i class="green ace-icon mt-3 fa fa-list-alt bigger-120"></i> Vendor Invoice List
		</a>
	</li>
</ul>

<div class="tab-content no-border" style="min-height:100px; border-radius:0px!important;">
<div id="vendorinvoicelist" class="tab-pane in active">
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
			
			<tr>
				<td colspan="11" style="padding:5px!important;">
					<select name="page_size" id="page_size" class="select2" data-width="85">
						<option value="10">10</option>
						<option value="20">20</option>
						<option value="30">30</option>
						<option value="50">50</option>
						<option value="100">100</option>														
					</select>
					<select class="select2" name="project_id" id="project_id" data-width="190"	data-placeholder="Project Name">
						<option value=""></option>
						@foreach ($projects as $project)
						<option value="{{ $project->projectid }}">{{ $project->project_name }}</option>
						@endforeach
					</select>
					
					<select class="select2" name="department_id" id="department_id" data-width="190"	data-placeholder="Departments">
						<option value=""></option>
						@foreach ($departments as $itm)
							<option value="{{ $itm->departmentid }}">
								{{ strtoupper($itm->shortname) }}-{{ $itm->departmentname }}
							</option>
						@endforeach
					</select>

					<select class="select2" name="manager_id" id="manager_id" data-width="190"	data-placeholder="Managers">
						<option value=""></option>
						@foreach ($managers as $itm)
							<option value="{{ $itm->departmentid }}">
								{{ $itm->departmentname }}
							</option>
						@endforeach
					</select>

					<select class="select2" name="vendor_id" id="vendor_id" data-width="190"	data-placeholder="Firm Name">
						<option value=""></option>
						@foreach ($vendors as $itm)
							<option value="{{ $itm->vendorid }}">
								{{ strtoupper($itm->shortname) }}
							</option>
						@endforeach
					</select>
					
					<button type="submit" class="btn btn-info gridbtn invoiceBtn width-100" onclick="loadInvoiceData(1,'{{ route('finance.vendorinvoice.list') }}')" tabindex="{{$t++}}">
						<i class="fa fa-search"></i> Search
					</button>
				</td>
			</tr>
			<tr>
				<td colspan="11" style="padding:2px!important;">
					<span class="input-icon" style="height:38px!important; line-height:38px!important; width:280px!important;">
						<input type="text" placeholder="Search ..." id="pagesearch" name="pagesearch" autocomplete="off" tabindex="<?php echo $t++;?>" style="border-radius:5px; height:38px!important; line-height:38px!important; width:280px!important;" />
						<i class="ace-icon fa fa-search nav-search-icon padding-0"></i>
					</span>
					<select class="select2" name="invoice_status" id="invoice_status" data-width="190"	data-placeholder="Invoice Status">
						<option value=""></option>
						<option value="Submitted">Submitted</option>
						<option value="Approved">Approved</option>
						<option value="Partially Paid">Partially Paid</option>
						<option value="Paid">Paid</option>
					</select>
					<select class="select2" name="finance_status" id="finance_status" data-width="190"	data-placeholder="Finance Status">
						<option value=""></option>
						<option value="Pending">Pending</option>
						<option value="Verified">Verified</option>
						<option value="Rejected">Rejected</option>
					</select>

					
				</td>
			</tr>
			
			</table>
			<table class="table table-bordered table-striped table-hover" id="tablerecords">
			<thead>
			<tr class="myheadbg">
				<td style="width:25px;" nowrap><b>S.No.</b></td>
				<td class="no_wrap"><b>Invoice Number</b></td>
				<td class="no_wrap text-center"><b>Date</b></td>
				<td class="no_wrap"><b>Vendor</b></td>
				<td class="no_wrap"><b>Work Order</b></td>
				<td class="no_wra"><b>EoI</b></td>
				<td class="no_wrap text-right"><b>Net Invoice Value</b></td>
				<td class="no_wrap text-center"><b>Finance Status</b></td>
				<td class="no_wrap text-center"><b>Payment Status</b></td>
				<td class="no_wrap text-right"><b>Balance</b></td>
				<td class="no_wrap center"><b>Action</b></td>
			</tr>
			</thead>
			<tbody class="tabledata">
			<tr><td class="center" colspan="11">--Vendor Invoice List--</td></tr>
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
	var pagesize 		= 	document.getElementById("page_size").value;
	var projectid 		= 	document.getElementById("project_id").value;
	var departmentid	= 	document.getElementById("department_id").value;
	var managerid		= 	document.getElementById("manager_id").value;
	var vendor_id		= 	document.getElementById("vendor_id").value;
	var pagesearch 		= 	document.getElementById("pagesearch").value;
	var financeStatus	= 	document.getElementById("finance_status").value;
	var invoiceStatus	= 	document.getElementById("invoice_status").value;
	
	$.get(""+r1,
	{
		page:page,
		pagesize:pagesize,
		pagesearch:pagesearch,
		projectid:projectid,		
		departmentid:departmentid,
		managerid:managerid,
		vendor_id:vendor_id,
		financeStatus:financeStatus,
		invoiceStatus:invoiceStatus
	},
	function (data, status) {
		$(".tabledata").html(data);
		
		const paginationLinks	=	document.querySelectorAll('.pagination a');
		
		paginationLinks.forEach(link => {
			link.onclick = function (event) {
				event.preventDefault();
				const page = this.getAttribute('href').split('page=')[1];
				const url = '{{ route('finance.vendorinvoice.list') }}';
				loadInvoiceData(page, url);
			};
		});	
		
	});
}

</script>

<script src="{{ asset('panel/assets/js/datatablePaginationWithFilter.js') }}"></script>
@endsection