@extends('admin.admin_master')
@section('admin')
	@php
	$t = 1;
	@endphp
	<div class="main-content">
	<style>
	.select2-results__group {
		background-color:#f0f0f0;
		color: #000;
		font-weight:bold;
		padding:5px;
	}	
	</style>
		<div class="main-content-inner">
			<div class="page-content" style="padding:10px 15px!important;">
				<div class="ace-settings-container" id="ace-settings-container"></div>
				<div class="row">
					<div class="col-xs-12">
						<!-- PAGE CONTENT BEGINS -->
						<div class="tabbable animated bounceInUp" style="padding:0px 0px!important;">
							@if(in_array(2, Session::get('actions')) || in_array(3, Session::get('actions')) || in_array(12, Session::get('actions')))
								<div class="eoi-section-header" onclick="loadData(1,'{{ route('eoirequest.html') }}')" style="position:relative;">

									<div style="font-size:20px; font-weight:600; color:#2c3e50;">
										EoI Request List
									</div>
									
									<div style="font-size:14px; color:#6c757d; margin:2px 0px; 8px 0">
										View, filter, and manage Expressions of Interests.
									</div>
									
								</div>
							@endif
							
							<div class="tab-content no-border" style="min-height:100px; padding:0px 10px;">

								@if(in_array(2, Session::get('actions')) || in_array(3, Session::get('actions')) || in_array(12, Session::get('actions')))
									<div id="recordlist" class="tab-pane in active" style="padding:0px 0px!important;">
										<div class="row" style="padding:0px;">
											@if(Session::has('success'))
												<div class="col-sm-12" style="padding:10px;">
													<div class="alert alert-block alert-success"
														style="border-radius:0px!important;">
														<button type="button" class="close" data-dismiss="alert">
															<i class="ace-icon fa fa-times"></i>
														</button>
														<i class="fa fa-thumbs-up"></i> {{ Session::get('success') }}
													</div>
												</div>
											@endif
											@if(Session::has('fail'))
												<div class="col-sm-12" style="padding:10px;">
													<div class="alert alert-block alert-warning"
														style="border-radius:0px!important;">
														<button type="button" class="close" data-dismiss="alert">
															<i class="ace-icon fa fa-times"></i>
														</button>
														<i class="fa fa-warning"></i> {{ Session::get('fail') }}
													</div>
												</div>
											@endif
											@if($errors->any())
												<div class="col-sm-12 padding-10">
													<div class="alert alert-block alert-danger">
														<button type="button" class="close" data-dismiss="alert">
															<i class="ace-icon fa fa-times"></i>
														</button>
														@foreach ($errors->all() as $error)
															<i class="fa fa-warning"></i> {{ $error }}<br>
														@endforeach
													</div>
												</div>
											@endif

											<div class="col-xs-12">
												<form name="pagedata" id="pagedata" action="#" method="post"
													onsubmit="return false;">
													@csrf
													<div class="table-responsive">
														<table class="table table-bordered table-striped pd-10" id="tablerecords">
<thead>			
																<tr>
																	<td colspan="6" style="border:1px solid #fff; position:relative;">


<input type="hidden" name="firm_type" id="firm_type" value="{{$firm_type}}">
<input type="hidden" name="record_type" id="record_type" value="{{$record_type}}">

<select name="pagesize" id="pagesize" class="select2" onchange="loadData(1,'{{ route('eoirequest.html') }}')" data-width="90" data-placeholder="RECORDS">
	<option value="100">100</option>
	<option value="300">300</option>
	<option value="500">500</option>
</select>

<select class="select2" name="categoryid" id="categoryid" onchange="loadData(1,'{{ route('eoirequest.html') }}');GetTiersWithId(this.value,'{{route('tiers.list') }}','tierid')" data-width="180" data-placeholder="Select Category">
<option value=""></option>
@if(!is_null($record_type))
@foreach ($category as $itm)
	<option value="{{ $itm->categoryid }}" @if($firm_type==$itm->categoryid) selected @endif>
		{{ strtoupper($itm->jobcategory) }}
	</option>
@endforeach
@else
@foreach ($category as $itm)
	<option value="{{ $itm->categoryid }}" @if($itm->categoryid==2) selected @endif>
		{{ strtoupper($itm->jobcategory) }}
	</option>
@endforeach
@endif
</select>

<select class="select2" name="departmentid" id="departmentid" onchange="loadData(1,'{{ route('eoirequest.html') }}');" data-width="180"	data-placeholder="Departments">
	<option value=""></option>
	@foreach ($departments as $itm)
		<option value="{{ $itm->userid }}">
			{{ strtoupper($itm->shortname) }}-{{ $itm->departmentname }}
		</option>
	@endforeach
</select>

<select class="select2" name="managerid" id="managerid" onchange="loadData(1,'{{ route('eoirequest.html') }}');" data-width="180"	data-placeholder="Project Managers">
	<option value=""></option>
	@foreach ($managers as $itm)
		<option value="{{ $itm->userid }}">
			{{ $itm->departmentname }}
		</option>
	@endforeach
</select>


<select class="select2" name="eoi_status" id="eoi_status" onchange="loadData(1,'{{ route('eoirequest.html') }}');" data-width="180"	data-placeholder="EoI Status">
	<option value=""></option>
	<option value="0">New Request</option>
	<option value="1">Drafted</option>
	<option value="2">Approved</option>
	<option value="3">Published</option>
	<option value="6">In Pre-Bid</option>
	<option value="34">Participated</option>
	<option value="43">No Participation</option>
	<option value="5">Interview</option>
	<option value="4">Ordered</option>
</select>
@permission('export.eoidata')
<button type="button" class="eoi-btn action-a" onclick="ExportData('{{route('export.eoidata')}}')" style="position:absolute; right:5px; top:10px;">
	<i class="fa fa-file-o"></i> Export Data
</button>
@endpermission
																	</td>
																</tr>
<tr>
	<td colspan="6" style="border:1px solid #fff; position:relative;">
<input type="text" placeholder="Search EoI detail" class="nav-search-input" id="pagesearch" name="pagesearch" autocomplete="off" onchange="loadData(1,'{{ route('eoirequest.html') }}')" tabindex="<?php echo $t++;?>" style="width:275px!important;"/>								

<select class="select2" name="projectid" id="projectid" onchange="loadData(1,'{{ route('eoirequest.html') }}');" data-width="180"	data-placeholder="Project Name">
	<option value=""></option>
	@foreach ($projects as $project)
	<option value="{{ $project->projectid }}">{{ $project->project_name }}</option>
	@endforeach
</select>


<span style="position:absolute; right:10px;">
  <input type="checkbox" name="in_house" id="in_house" onchange="loadData(1,'{{ route('eoirequest.html') }}')" style="vertical-align:text-top;">
  <label for="in_house">In House Consultants</label>&nbsp;&nbsp;&nbsp;&nbsp;

  <input type="checkbox" name="is_cancelled" id="is_cancelled" onchange="loadData(1,'{{ route('eoirequest.html') }}')" style="vertical-align:text-top;">
  <label for="is_cancelled">Cancelled EoIs</label>&nbsp;&nbsp;&nbsp;&nbsp;

  <input type="checkbox" name="is_closed" id="is_closed" onchange="loadData(1,'{{ route('eoirequest.html') }}')" style="vertical-align:text-top;">
  <label for="is_closed">Closed EoIs</label>

</span>
	
	</td>
</tr>
<tr>
	<td class="center" nowrap>S. No.</td>
	<td>EoI Number & Department / Project Manager</td>
	<td class="center">CHiPS EoI Release Date</td>
	<td class="center">Pre-bid Query Deadline</td>
	<td class="center">Proposal Submission Deadline</td>
	<td class="center"></td>
</tr>												
</thead>
														{{-- Card-style rows only (no column headers) --}}
															
															<tbody class="tabledata">
																<tr>
																	<td colspan="6" class="center">--Search Record--
																	</td>
																</tr>
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
			<div class="modal-content"
				style="font-size:12px; width:80%; margin:0 auto; margin-top:10px; background-color:white;">

			</div>
		</div>
	</div>

<div class="modal fade" id="approvalModal">
  <div class="modal-dialog modal-xl" style="width:95%; margin-top:0px;">
    <div class="modal-content">
	
      <div class="modal-header">
        <h5 class="modal-title" style="float:left;">Submission for Approval</h5>
        <!-- Proper close button for BS3 -->
        <button type="button" class="close" style="float:right;" data-dismiss="modal">&times;</button>
      </div>
      <div class="modal-body" id="approvalData" style="max-height:600px; overflow:auto;">
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-info myfrmbtn sendApprovalBtn width-100">Send</button>
      </div>
	</form>
    </div>
  </div>
</div>


<div class="modal fade" id="firmModal">
  <div class="modal-dialog modal-xl" style="width:95%; margin-top:0px;">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" style="float:left;">List of Firms to Whom EoI Is Published</h5>
        <button type="button" class="btn btn-close" style="float:right;" data-bs-dismiss="modal" onclick="ClsFirmList()">Close</button>
      </div>
      <div class="modal-body" id="firmContent">
        Loading...
      </div>
      <div class="modal-footer">
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="valueModal">
  <div class="modal-dialog modal-xl" style="width:95%; margin-top:0px;">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" style="float:left;">Order Value</h5>
        <button type="button" class="btn btn-close" style="float:right;" data-bs-dismiss="modal" onclick="ClsFirmList()">Close</button>
      </div>
      <div class="modal-body" id="valueContent">Loading...</div>
      <div class="modal-footer"></div>
    </div>
  </div>
</div>

<div class="modal fade" id="moreModal">
	<div class="modal-dialog modal-xl" style="width:95%; margin-top:0px;">
		<div class="modal-content moreContent"></div>
	</div>
</div>

<div class="modal fade" id="comparisionModal">
  <div class="modal-dialog modal-xl" style="width:95%; margin-top:0px;">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" style="float:left;">Resource Cost by Duration</h5>
        <button type="button" class="btn btn-close" style="float:right;" data-bs-dismiss="modal" onclick="ClsResource()">Close</button>
      </div>
      <div class="modal-body" id="comparisionContent">
        Loading...
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-close" data-bs-dismiss="modal" onclick="ClsResource()">Close</button>
      </div>
    </div>
  </div>
</div>

<script src="{{ asset('panel/assets/js/jquery-2.1.4.min.js') }}"></script>

<script>
	function showPrice(rl)
	{
		let formData = new FormData();
		formData.append('_token','{{ csrf_token() }}');
		$.ajax({
			url: ''+rl,
			method: 'POST',
			data: formData,
			processData:false,
			contentType:false,
			success: function(response) {
				$('#comparisionModal').modal('show');
				$('#comparisionContent').html(response.formhtml);
			},
			error: function(xhr) {
				bootbox.alert('An error occurred while saving the data.');
			}
		});
	}


	$(document).ready(function () {
		$.ajaxSetup({
			headers: {
				'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
			}
		});

		// FIX: load table data on first page load
		loadData(1, '{{ route('eoirequest.html') }}');
	});

	function viewMoreData(r1,requestid)
	{
		$.get(r1,{ requestid: requestid }, function (response) {
			if(response.status === 200)
			{
				$("#moreModal").modal("show");
				$(".moreContent").html(response.data);
			}
			else
			{
				console.error(response.message);
			}
		}, 'json');
	}

	function ExportData(url)
	{
		var categoryid 	= 	document.getElementById("categoryid").value;
		var projectid 	= 	document.getElementById("projectid").value;
		var departmentid= 	document.getElementById("departmentid").value;
		var managerid	=	document.getElementById("managerid").value;
		var eoi_status 	= 	document.getElementById("eoi_status").value;
		var pagesearch 	= 	document.getElementById("pagesearch").value;
		var firm_type 	= 	document.getElementById("firm_type").value;
		var record_type = 	document.getElementById("record_type").value;
		var in_house 	= 	document.getElementById("in_house").checked;
		var is_cancelled= 	document.getElementById("is_cancelled").checked;
		var is_closed 	= 	document.getElementById("is_closed").checked;

		var params	=	new URLSearchParams({
							categoryid:categoryid,
							projectid:projectid,
							departmentid:departmentid,
							managerid:managerid,
							eoi_status:eoi_status,
							pagesearch:pagesearch,
							firm_type:firm_type,
							record_type:record_type,
							in_house:in_house,
							is_cancelled:is_cancelled,
							is_closed:is_closed
						});
		window.location.href = url + '?' + params.toString();		
	}
	
	function loadData(page, r1) {
		var pagesize 	= document.getElementById("pagesize").value;
		var categoryid 	= document.getElementById("categoryid").value;
		var projectid 	= document.getElementById("projectid").value;
		var departmentid= document.getElementById("departmentid").value;
		var managerid	= document.getElementById("managerid").value;
		var eoi_status 	= document.getElementById("eoi_status").value;
		var pagesearch 	= document.getElementById("pagesearch").value;
		var firm_type 	= document.getElementById("firm_type").value;
		var record_type = document.getElementById("record_type").value;
		var in_house 	= document.getElementById("in_house").checked;
		var is_cancelled= document.getElementById("is_cancelled").checked;
		var is_closed	= document.getElementById("is_closed").checked;
		/*
		var frmdate		=	document.getElementById("frmdate").value;
		var todate		=	document.getElementById("todate").value;
		frmdate 		= 	convertToMMDDYYYY(frmdate);
		todate 			= 	convertToMMDDYYYY(todate);
		*/
		$.get("" + r1,
			{
				page: page,
				pagesize: pagesize,
				pagesearch: pagesearch,
				categoryid: categoryid,
				departmentid: departmentid,
				managerid:managerid,
				eoi_status:eoi_status,
				firm_type:firm_type,
				record_type:record_type,
				in_house:in_house,
				is_cancelled:is_cancelled,
				is_closed:is_closed,
				projectid:projectid
			},
			function (data, status) {
				$(".tabledata").html(data);
				$(".mytr").click(function () {
					$(".mytr").removeClass("selected-color").addClass("reset-color");
					$(this).removeClass("reset-color").addClass("selected-color");
				});
				$(document).off("click", ".floateoiBtn").on("click", ".floateoiBtn", function () {
					var requestid = $(this).data("requestid");
					var ind = $(this).data("ind");
					FloatEoi(requestid, ind);
				});
			});
	}

	function viewEoiRequestData(r1, requestid)
	{
		$.get("" + r1,
			{
				requestid: requestid,
			},
			function (data, status) {
				$("#myModal").modal("show");
				$(".modal-content").html(data);
			});
	}
	function Cls() {
		$("#myModal").modal("hide");
		$(".modal-content").html("");

		$("#moreModal").modal("hide");
		$(".moreContent").html("");
	}

	function CloseThis() {
		$("#moreModal").modal("hide");
		$(".moreContent").html("");
	}

	function FloatEoi(requestid, ind) {
		var floatdate = $("#floatdate" + ind).val();
		var expirydate = $("#expirydate" + ind).val();
		if (floatdate === '' || expirydate === '') {
			bootbox.alert("Please select floating date and expirydate");
			return false;
		}
		bootbox.confirm('Do you confirm that you want to float the EoI to vendors?<br><br>This action is irreversible once confirmed.', function (result) {
			if (result) {
				$(".floateoiBtn").prop("disabled", "disabled");
				$.ajax({
					url: '{{route("float.eoi")}}',
					type: 'POST',
					data: { 'requestid': requestid, '_token': '{{ csrf_token() }}', 'floatdate': floatdate, 'expirydate': expirydate },
					success: function (response) {
						if (response.status === 200) {
							bootbox.alert(response.message);
							setTimeout(function () { loadData(1, '{{ route('eoirequest.html') }}'); $(".floateoiBtn").prop("disabled", ""); }, 2000);
						}
						if (response.status === 400) {
							bootbox.alert(response.message);
							return false;
						}
					},
					error: function (xhr) {
						$(".floateoiBtn").prop("disabled", "");
						if (xhr.responseJSON && xhr.responseJSON.errors) {
							var errors = xhr.responseJSON.errors;
							var allMessages = '';

							$.each(errors, function (field, messages) {
								$.each(messages, function (index, msg) {
									allMessages += '<i class="fa fa-hand-o-right"></i> ' + msg + '<br>';
								});
							});
							bootbox.alert(allMessages);
						}
					}
				});

			}
		});
	}

function ClsResource()
{
	$('#comparisionModal').modal('hide');
	$('#comparisionContent').html("");

}

function ClsFirmList()
{
	$('#firmModal').modal('hide');
	$('#firmContent').html("");

	$('#valueModal').modal('hide');
	$('#valueContent').html("");
}


function ShowFirms(requestid)
{
	let formData = new FormData();
	formData.append('_token', '{{ csrf_token() }}');
	formData.append('requestid', requestid);
	$.ajax({
		url: '{{ route("published.firms") }}',
		method: 'POST',
		data: formData,
		processData: false,
		contentType: false,
		success: function(response) {
			if (response.status === 200)
			{
				$('#firmModal').modal('show');
				$('#firmContent').html(response.formhtml);
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

function CalculateValue(requestid,tierid,categoryid)
{
	let formData = new FormData();
	formData.append('_token', '{{ csrf_token() }}');
	formData.append('requestid',requestid);
	formData.append('tierid',tierid);
	formData.append('categoryid',categoryid);
	$.ajax({
		url: '{{ route("get.calculation") }}',
		method: 'POST',
		data: formData,
		processData: false,
		contentType: false,
		success: function(response) {
			if (response.status === 200)
			{
				$('#valueModal').modal('show');
				$('#valueContent').html(response.formhtml);
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

function updateProjectName(projectid,requestid)
{
	let formData = new FormData();
	formData.append('_token', '{{ csrf_token() }}');
	formData.append('requestid',requestid);
	formData.append('projectid',projectid);
	
	$.ajax({
        url: '{{ route("update.projectname") }}',
		method: 'POST',
		data: formData,
		processData: false,
		contentType: false,
		success: function(response) {
			if(response.status===200)
			{
				bootbox.alert(response.message);
			}
			else
			{
				bootbox.alert(response.message);
			}
		},
		error: function(xhr) {
			bootbox.alert('An error occurred while saving the data.');
		}
    });
}

</script>
<script src="{{ asset('panel/assets/js/master.js') }}"></script>
<script src="{{ asset('panel/assets/js/datatablePaginationWithFilter.js') }}"></script>
@endsection
