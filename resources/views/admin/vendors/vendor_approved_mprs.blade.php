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
			<div class="eoi-section-header" onclick="loadData(1,'{{ route('vendor.approved.mprs.html') }}')"
				style="cursor:pointer; padding:8px 8px 16px 8px;">

				<div style="font-size:20px; font-weight:600; color:#2c3e50;">
					Approved MPR
				</div>
			</div>

		<div class="tab-content no-border" style="min-height:100px; padding:0px 10px;">

<div id="recordlist" class="tab-pane in active" style="padding:0px 0px!important;">
	<div class="row" style="padding:0px;">
		<div class="col-xs-12">
		@if(Session::has('success'))
		<div class="col-sm-12 pd-10 mt-10">
			<div class="alert alert-block alert-success">
				<button type="button" class="close" data-dismiss="alert">
					<i class="ace-icon fa fa-times"></i>
				</button>
				{{ Session::get('success') }}
			</div>
		</div>
		@endif

		<form name="pagedata" id="pagedata" action="#" method="post" onsubmit="return false;">
			@csrf
			<div class="table-responsive">
				<table class="table-bordered mytable pd-8">
				
				<tr>
					<td colspan="9" class="padding-5" style="position:relative;">

						<select name="page_size" id="page_size" class="select2" onchange="loadData(1,'{{ route('vendor.approved.mprs.html') }}')" data-width="90" data-placeholder="RECORDS">
							<option value="50">50</option>
							<option value="100">100</option>
							<option value="300">300</option>
							<option value="500">500</option>														
						</select>
						<select class="select2" name="department_id" id="department_id" onKeyPress="return OnKeyPress(this, event)" onchange="loadData(1,'{{ route('vendor.approved.mprs.html') }}');" data-width="250" data-placeholder="Department /  Manager">
							<option value=""></option>
							@if($records->contains('isprojectmanager', 0))							
							<optgroup label="Department">
							@foreach($records as $usr)
							@if($usr->isprojectmanager==0)
								<option value="{{ $usr->departmentid }}">
									{{ $usr->departmentname }}
								</option>
							@endif
							@endforeach
							</optgroup>
							@endif
							@if($records->contains('isprojectmanager', 1))
							<optgroup label="Project Manager">
							@foreach($records as $usr)
							@if($usr->isprojectmanager==1)
								<option value="{{ $usr->departmentid }}">
									{{ $usr->departmentname }}
								</option>
							@endif
							@endforeach
							</optgroup>
							@endif
						</select>

						<select class="select2" name="mpr_month" id="mpr_month" onKeyPress="return OnKeyPress(this, event)" onchange="loadData(1,'{{ route('vendor.approved.mprs.html') }}');" data-width="140" data-placeholder="MPR Month">
							<option value=""></option>
							@for($month = 1; $month <= 12; $month++)
								<option value="{{ $month }}">
									{{ date('F', mktime(0, 0, 0, $month, 1)) }}
								</option>
							@endfor
						</select>

						<select class="select2" name="mpr_year" id="mpr_year" onKeyPress="return OnKeyPress(this, event)" onchange="loadData(1,'{{ route('vendor.approved.mprs.html') }}');" data-width="130" data-placeholder="MPR Year">
							<option value=""></option>
							@for($year = 2026; $year >= 2025; $year--)
								<option value="{{ $year }}">{{ $year }}</option>
							@endfor
						</select>
						
						<input type="text" placeholder="Search ..." class="padding-5 br-5" id="page_search" name="page_search" autocomplete="off" onchange="loadData(1,'{{ route('vendor.approved.mprs.html') }}')" style="width:250px!important; line-height:28px; position:absolute; right:5px; font-weight:normal;" tabindex="<?php echo $t++;?>" />
						
					</td>
				</tr>
				
				<tbody class="tabledata">
				<tr><td colspan="9" class="center padding-6">--Search Record--</td></tr>
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

<div class="modal fade" id="invoiceHistory" style="margin-top:-20px;">
<form name="invoicing" id="invoicing" action="{{route('prepare.vendorinvoice')}}" method="post" enctype="multipart/form-data">
@csrf
  <div class="modal-dialog modal-xl" style="width:95%;">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title form-label invoice-title" style="float:left;"><i class="fa fa-calendar"></i> Monthly Progress Report — Generate or Update MPRs</h5>    
      </div>
      <div class="modal-body" id="invoiceContent" style="min-height:200px; max-height:450px; overflow-y:scroll;">Loading...</div>
      <div class="modal-footer invoice-footer">
        <button type="button" class="btn btn-close btn-info gridbtn" style="width:80px;" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</form>
</div>


<script src="{{ asset('panel/assets/js/jquery-2.1.4.min.js') }}"></script>
<script>

$(document).ready(function() {
	$.ajaxSetup({
		headers: {
			'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
		}
	});

	loadData(1,'{{ route('vendor.approved.mprs.html') }}');
});

function loadData(page,r1)
{
	var pagesize	=	document.getElementById("page_size").value;
	var pagesearch	=	document.getElementById("page_search").value;
	var mpr_month	=	document.getElementById("mpr_month").value;
	var mpr_year	=	document.getElementById("mpr_year").value;
	var departmentid	=	document.getElementById("department_id").value;
	$.get(""+r1,
	{
		page:page,
		pagesize:pagesize,
		pagesearch:pagesearch,
		mpr_month:mpr_month,
		mpr_year:mpr_year,
		departmentid:departmentid,
	},
	function(data, status){
		$(".tabledata").html(data);
	});
}

$('.btn-close').on('click', function (e)
{
	$('#paymentContent').html('');
	$('#paymentHistory').modal('hide');

	$('#invoiceContent').html('');
	$('#invoiceHistory').modal('hide');
});

function loadPendingInvoices(r1,orderid)
{
	$.get(""+r1,
	{
		orderid:orderid
	},
	function(data, status){
		if(data.status==400)
		{
			bootbox.alert(data.message);
		}
		else if(data.status==500)
		{
			bootbox.alert(data.message);
		}
		else
		{
			$('#invoiceHistory').modal('show');
			$('#invoiceContent').html(data.data);
		}
	});
}
function viewMprDetail(r1,mprid)
{
	$.get(""+r1,
	{
		mprid:mprid
	},
	function(data, status){
		if(data.status==400)
		{
			bootbox.alert(data.message);
		}
		else if(data.status==500)
		{
			bootbox.alert(data.message);
		}
		else
		{
			$('#invoiceHistory').modal('show');
			$('#invoiceContent').html(data.data);
		}
	});
}
</script>
<script src="{{ asset('panel/assets/js/master.js') }}"></script>
<script src="{{ asset('panel/assets/js/datatablePaginationWithFilter.js') }}"></script>
@endsection
