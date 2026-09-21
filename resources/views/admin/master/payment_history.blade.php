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
			<div class="eoi-section-header" onclick="loadData(1,'{{ route('adminpaymenthistory.html') }}')"
				style="cursor:pointer; padding:8px 8px 16px 8px;">

				<div style="font-size:20px; font-weight:600; color:#2c3e50;">
					Payment History
				</div>
			</div>
		@endif

		<div class="tab-content no-border" style="min-height:100px; padding:0px 10px;">

@if(in_array(2,Session::get('actions')) || in_array(3,Session::get('actions')) || in_array(12,Session::get('actions')))
<div id="recordlist" class="tab-pane in active" style="padding:0px 0px!important;">
	<div class="row" style="padding:0px;">
		@if(Session::has('success'))
		<div class="col-sm-12">
			<div class="alert alert-block alert-success">
				<button type="button" class="close" data-dismiss="alert">
					<i class="ace-icon fa fa-times"></i>
				</button>
				{{ Session::get('success') }}
			</div>
		</div>
		@endif
		@if(Session::has('duplicate'))
		<div class="col-sm-12">
			<div class="alert alert-block alert-warning">
				<button type="button" class="close" data-dismiss="alert">
					<i class="ace-icon fa fa-times"></i>
				</button>
				<i class="fa fa-warning"></i> {{ Session::get('duplicate') }}
			</div>
		</div>
		@endif
		@if ($errors->any())
		<br>
		<div class="col-sm-12">
			<div class="alert alert-block alert-danger">
				<button type="button" class="close" data-dismiss="alert">
					<i class="ace-icon fa fa-times"></i>
				</button>
				<ul>
					@foreach ($errors->all() as $error)
						<i class="fa fa-warning"></i> {{ $error }}<br>
					@endforeach
				</ul>
			</div>
		</div>
		@endif				
		
		<div class="col-xs-12">
		<form name="add_payment" id="add_payment" action="#" method="post" onSubmit="return false;">
			@csrf
			<div class="table-responsive">
				<table class="table table-bordered table-striped table-hover pd-5" id="tablerecords" style="width:100%;">
				<thead>
				
				<tr>
					<td colspan="6">
					<div class="table-filters-row">
						<div class="filters-group">
												
						<select name="pagesize" id="pagesize" class="select2" onchange="loadData(1,'{{ route('adminpaymenthistory.html') }}')" data-width="95" data-placeholder="RECORDS">
							<option value="300">300</option>
							<option value="500">500</option>
							<option value="1000">1000</option>
						</select>

						<select class="select2" name="vendor_id" id="vendor_id" onKeyPress="return OnKeyPress(this, event)" onchange="loadData(1,'{{ route('adminpaymenthistory.html') }}');" data-placeholder="Firm Name" data-width="250">
							<option value=""></option>
							@foreach ($firms as $itm)
							<option value="{{ $itm->vendorid }}">{{ $itm->companyname }} [{{ $itm->shortname }}]</option>
							@endforeach
						</select>

						<select class="select2" name="payment_method" id="payment_method" onKeyPress="return OnKeyPress(this, event)" onchange="loadData(1,'{{ route('adminpaymenthistory.html') }}');" data-placeholder="Payment Method" data-width="175">
							<option Value=""></option>
							<option Value="Bank Transfer">Bank Transfer</option>
							<option Value="Cheque">Cheque</option>
							<option Value="Demand Draft">Demand Draft</option>
						</selected>

						<span class="input-icon">
							<input type="text" placeholder="Search ..." class="nav-search-input selectbx" id="pagesearch" name="pagesearch" autocomplete="off" onchange="loadData(1,'{{ route('adminpaymenthistory.html') }}')" tabindex="<?php echo $t++;?>" />
							
						</span>
						</div>
					</div>
					
					</td>
				</tr>
				<tr class="myhead">
					<td class="center width-30" nowrap><b>S.No.</b></td>
					<td nowrap class="center width-100"><b>Payment Date</b></td>
					<td nowrap class="width-250"><b>Firm Name</b></td>
					<td nowrap class="width-150"><b>Payment Method</b></td>
					<td nowrap><b>Transaction Detail & Remark</b></td>
					<td nowrap class="width-100 text-right"><b>Paid Amount</b></td>
				</tr>
			
				</thead>
				<tbody class="tabledata" style="height:100px; max-height:100px; overflow:scroll;">
				<tr><td colspan="6" class="center">--Search Record--</td></tr>
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

$(document).ready(function() {
	$.ajaxSetup({
		headers: {
			'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
		}
	});

	loadData(1,'{{ route('adminpaymenthistory.html') }}');


});

function loadData(page,r1)
{
	var pagesize		=	document.getElementById("pagesize").value;
	var pagesearch		=	document.getElementById("pagesearch").value;
	var vendor_id		=	document.getElementById("vendor_id").value;

	$.get(""+r1,
	{
		page:page,
		pagesize:pagesize,
		pagesearch:pagesearch,
		vendor_id:vendor_id,
	},
	function(data, status){
		$(".tabledata").html(data);
	});
}

</script>
<script src="{{ asset('panel/assets/js/master.js') }}"></script>
<script src="{{ asset('panel/assets/js/datatablePaginationWithFilter.js') }}"></script>
@endsection
