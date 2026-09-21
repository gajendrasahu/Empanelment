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
		<a data-toggle="tab" href="#vendorinvoicelist">
			<i class="green ace-icon mt-3 fa fa-list-alt bigger-120"></i> Finance-Verified Vendor Invoices
		</a>
	</li>
	<li>
		<a data-toggle="tab" href="#vendorpaymentlist">
			<i class="green ace-icon mt-3 fa fa-list-alt bigger-120"></i> Vendor Payment List
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
			<thead>
			<tr>
				<td colspan="11">
					<select name="page_size" id="page_size" class="select2" data-width="95">
						<option value="10">10</option>
						<option value="50">50</option>
						<option value="100">100</option>
						<option value="300">300</option>
						<option value="500">500</option>														
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
								{{ strtoupper($itm->departmentname) }}
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
					
					<button type="submit" class="btn btn-info gridbtn invoiceBtn width-100" onclick="loadVerifiedInvoiceData(1,'{{ route('finance.vendorpayment.verifiedinvoicelist') }}')" tabindex="{{$t++}}">
						<i class="fa fa-search"></i> Search
					</button>
				</td>
			</tr>
			<tr>
				<td colspan="11" style="padding:2px!important;">
					<span class="input-icon" style="width:100%!important; height:38px!important; line-height:38px!important;">
						<input type="text" placeholder="Search ..." class="" id="pagesearch" name="pagesearch" autocomplete="off" tabindex="<?php echo $t++;?>" style="width:100%!important; border-radius:5px; height:38px!important; line-height:38px!important;" />
						<i class="ace-icon fa fa-search nav-search-icon padding-0"></i>
					</span>
				</td>
			</tr>
			</thead>
			</table>
			<table class="table table-bordered table-striped table-hover" id="tablerecords">
			<thead>
			<tr class="myheadbg">
				<td style="width:25px;" nowrap><b>S.No.</b></td>
				<td class="no_wrap"><b>Invoice Number</b></td>
				<td class="text-center"><b>Date</b></td>
				<td class="no_wrap"><b>Firm Name</b></td>
				<td class="no_wrap"><b>Work Order</b></td>
				<td class="no_wrap"><b>EoI</b></td>
				<td class="no_wrap text-right"><b>Net Invoice Value</b></td>
				<td class="text-right"><b>Paid</b></td>
				<td class="no_wrap text-right"><b>Balance</b></td>
				<td class="no_wrap text-right"><b>Payment Status</b></td>
				<td nowrap class="center"><b>Action</b></td>
			</tr>
			</thead>
			<tbody class="tabledata">
			<tr><td class="center" colspan="11">--Finance Verified Vendor Invoice List--</td></tr>
			</tbody>
			</table>
		</div>
	</form>
</div>


<div id="vendorpaymentlist" class="tab-pane">
	<form name="payment_data" id="payment_data" action="#" method="post" onsubmit="return false;">
		<div class="table-responsive">
			<table class="table table-bordered table-striped table-hover" id="tablerecords">
			<thead>
			<tr>
				<td colspan="11">
					<select name="payment_page_size" id="payment_page_size" class="select2" data-width="95">
						<option value="10">10</option>
						<option value="50">50</option>
						<option value="100">100</option>
						<option value="300">300</option>
						<option value="500">500</option>														
					</select>
					<select class="select2" name="payment_project_id" id="payment_project_id" data-width="190"	data-placeholder="Project Name">
						<option value=""></option>
						@foreach ($projects as $project)
						<option value="{{ $project->projectid }}">{{ $project->project_name }}</option>
						@endforeach
					</select>
					
					<select class="select2" name="payment_department_id" id="payment_department_id" data-width="190"	data-placeholder="Departments">
						<option value=""></option>
						@foreach ($departments as $itm)
							<option value="{{ $itm->departmentid }}">
								{{ strtoupper($itm->shortname) }}-{{ $itm->departmentname }}
							</option>
						@endforeach
					</select>

					<select class="select2" name="payment_manager_id" id="payment_manager_id" data-width="190"	data-placeholder="Managers">
						<option value=""></option>
						@foreach ($managers as $itm)
							<option value="{{ $itm->departmentid }}">
								{{ strtoupper($itm->departmentname) }}
							</option>
						@endforeach
					</select>
					<select class="select2" name="payment_vendor_id" id="payment_vendor_id" data-width="190"	data-placeholder="Firm Name">
						<option value=""></option>
						@foreach ($vendors as $itm)
							<option value="{{ $itm->vendorid }}">
								{{ strtoupper($itm->shortname) }}
							</option>
						@endforeach
					</select>
					
					<button type="submit" class="btn btn-info gridbtn width-100" onclick="loadVendorPaymentData(1,'{{ route('finance.vendorpayment.vendorpaymentlist') }}')" tabindex="{{$t++}}">
						<i class="fa fa-search"></i> Search
					</button>
				</td>
			</tr>
			<tr>
				<td colspan="11" style="padding:2px!important;">
					<span class="input-icon" style="width:100%!important; height:38px!important; line-height:38px!important;">
						<input type="text" placeholder="Search ..." class="" id="payment_pagesearch" name="payment_pagesearch" autocomplete="off" tabindex="<?php echo $t++;?>" style="width:100%!important; border-radius:5px; height:38px!important; line-height:38px!important;" />
						<i class="ace-icon fa fa-search nav-search-icon padding-0"></i>
					</span>
				</td>
			</tr>
			</thead>
			</table>
			<table class="table table-bordered table-striped table-hover" id="tablerecords">
			<thead>
			<tr class="myheadbg">
				<td style="width:25px;" nowrap><b>S.No.</b></td>
				<td class="no_wrap"><b>Payment Date</b></td>
				<td class="no_wrap"><b>Invoice Number</b></td>
				<td class="no_wrap"><b>Vendor</b></td>
				<td class="no_wrap"><b>Gross Payment</b></td>
				<td class="no_wrap"><b>Admin Charges</b></td>
				<td class="no_wrap"><b>Approval Amount</b></td>
				<td class="no_wrap"><b>TDS</b></td>
				<td class="no_wrap"><b>GST TDS</b></td>
				<td class="no_wrap"><b>Net Paid</b></td>
				<td class="no_wrap"><b>Payment Mode</b></td>
				<td class="no_wrap"><b>Transaction / UTR</b></td>
				<td nowrap class="center"><b>Action</b></td>
			</tr>
			</thead>
			<tbody class="paymenttabledata">
			<tr><td class="center" colspan="13">--Vendor Payment List--</td></tr>
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
function loadVerifiedInvoiceData(page, r1)
{
	var pagesize 	= document.getElementById("page_size").value;
	var projectid 	= document.getElementById("project_id").value;
	var departmentid= document.getElementById("department_id").value;
	var managerid= document.getElementById("manager_id").value;
	var vendorid= document.getElementById("vendor_id").value;
	var pagesearch 	= document.getElementById("pagesearch").value;
	$.get(""+r1,
	{
		page:page,
		pagesize:pagesize,
		pagesearch:pagesearch,
		departmentid:departmentid,
		projectid:projectid,
		managerid:managerid,
		vendorid:vendorid
	},
	function (data, status) {
		$(".tabledata").html(data);
		
		const paginationLinks	=	document.querySelectorAll('.pagination a');
		
		paginationLinks.forEach(link => {
			link.onclick = function (event) {
				event.preventDefault(); // Prevent default link behavior
				const page = this.getAttribute('href').split('page=')[1]; // Extract page number from URL
				const url = '{{ route('finance.vendorpayment.verifiedinvoicelist') }}'; // URL generated by Laravel route
				loadVerifiedInvoiceData(page, url); // Call custom function with page number and URL
			};
		});	
		
	});
}

function loadVendorPaymentData(page, r1)
{
	var pagesize 	= 	document.getElementById("payment_page_size").value;
	var projectid 	= 	document.getElementById("payment_project_id").value;
	var departmentid= 	document.getElementById("payment_department_id").value;
	var managerid	= 	document.getElementById("payment_manager_id").value;
	var vendorid	= 	document.getElementById("payment_vendor_id").value;
	var pagesearch 	= 	document.getElementById("payment_pagesearch").value;
	$.get(""+r1,
	{
		page:page,
		pagesize:pagesize,
		pagesearch:pagesearch,
		departmentid:departmentid,
		projectid:projectid,
		managerid:managerid,
		vendorid:vendorid
	},
	function (data, status) {
		$(".paymenttabledata").html(data);
		
		const paginationLinks	=	document.querySelectorAll('.pagination a');
		
		paginationLinks.forEach(link => {
			link.onclick = function (event) {
				event.preventDefault(); // Prevent default link behavior
				const page = this.getAttribute('href').split('page=')[1]; // Extract page number from URL
				const url = '{{ route('finance.vendorpayment.vendorpaymentlist') }}'; // URL generated by Laravel route
				loadVendorPaymentData(page, url); // Call custom function with page number and URL
			};
		});	
		
	});
}

</script>

<script src="{{ asset('panel/assets/js/datatablePaginationWithFilter.js') }}"></script>
@endsection