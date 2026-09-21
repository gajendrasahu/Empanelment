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
			<div class="eoi-section-header" onclick="loadData(1,'{{ route('adminpaymentsummary.html') }}')"
				style="cursor:pointer; padding:8px 8px 16px 8px;">

				<div style="font-size:20px; font-weight:600; color:#2c3e50;">
					Payment Summary
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
					<td>
					<div class="table-filters-row">
						<div class="filters-group">
												
						<select name="pagesize" id="pagesize" class="select2" onchange="loadData(1,'{{ route('adminpaymentsummary.html') }}')" data-width="95" data-placeholder="RECORDS">
							<option value="15">15</option>
							<option value="50">50</option>
							<option value="100">100</option>
						</select>

						<select class="select2" name="vendor_id" id="vendor_id" onKeyPress="return OnKeyPress(this, event)" onchange="loadData(1,'{{ route('adminpaymentsummary.html') }}');" data-placeholder="Firm Name" data-width="250">
							<option value=""></option>
							@foreach ($firms as $itm)
							<option value="{{ $itm->vendorid }}">{{ $itm->companyname }} [{{ $itm->shortname }}]</option>
							@endforeach
						</select>

						<select class="select2" name="summary_type" id="summary_type" onKeyPress="return OnKeyPress(this, event)" onchange="loadData(1,'{{ route('adminpaymentsummary.html') }}');" data-placeholder="Firm Name" data-width="250">
							<option value="FIRM">Firm-wise</option>
							<option value="PROJECT">Project-wise</option>
						</select>

						<span class="input-icon">
							<input type="text" placeholder="Search ..." class="nav-search-input selectbx" id="pagesearch" name="pagesearch" autocomplete="off" onchange="loadData(1,'{{ route('adminpaymentsummary.html') }}')" tabindex="<?php echo $t++;?>" />
							
						</span>
						</div>
					</div>
					
					</td>
				</tr>
			
				</thead>
				<tbody class="tabledata" style="height:100px; max-height:100px; overflow:scroll;">
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

<div class="modal fade" id="recordModal">
  <div class="modal-dialog modal-xl" style="width:95%; margin-top:0px;">
    <div class="modal-content" id="recordContent">
	<!--
	<div class="modal-header">
		<h5 class="modal-title" style="float:left;">Order Value</h5>
		<button type="button" class="btn btn-close" style="float:right;" data-bs-dismiss="modal" onclick="ClsFirmList()">Close</button>
	</div>
	<div class="modal-body"></div>
	<div class="modal-footer"></div>
	-->
    </div>
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

	loadData(1,'{{ route('adminpaymentsummary.html') }}');


});

function loadData(page,r1)
{
	var pagesearch		=	document.getElementById("pagesearch").value;
	var vendor_id		=	document.getElementById("vendor_id").value;
	var summary_type		=	document.getElementById("summary_type").value;

	$.get(""+r1,
	{
		pagesearch:pagesearch,
		vendor_id:vendor_id,
		summary_type:summary_type
	},
	function(data, status){
		$(".tabledata").html(data);
	});
}

function viewInvoices(vendorid,rectype)
{
	let formData = new FormData();
	formData.append('_token', '{{ csrf_token() }}');
	formData.append('vendorid',vendorid);
	formData.append('rectype',rectype);
	$.ajax({
		url: '{{ route("view.invoices") }}',
		method: 'POST',
		data: formData,
		processData: false,
		contentType: false,
		success: function(response) {
			if (response.status === 200)
			{
				$('#recordModal').modal('show');
				$('#recordContent').html(response.formhtml);
			}
			else
			{
				bootbox.alert('Something went wrong: ' + response.message);
			}
		},
		error: function(xhr) {
			bootbox.alert('An error occurred while saving the data.');
		}
	});

}
function ClsRecords()
{
	$('#recordModal').modal('hide');
	$('#recordContent').html("");
}

</script>
<script src="{{ asset('panel/assets/js/master.js') }}"></script>
<script src="{{ asset('panel/assets/js/datatablePaginationWithFilter.js') }}"></script>
@endsection
