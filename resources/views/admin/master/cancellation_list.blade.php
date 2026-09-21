@extends('admin.admin_master')
@section('admin')
	@php
		$t = 1;
	@endphp
	<div class="main-content">
		<div class="main-content-inner">
			<div class="page-content" style="padding:10px 15px!important;">
				<div class="ace-settings-container" id="ace-settings-container"></div>

				<div class="row">
					<div class="col-xs-12">

						<div class="tabbable animated bounceInUp" style="padding:0!important;">
							@if(in_array(2, Session::get('actions')) || in_array(3, Session::get('actions')) || in_array(12, Session::get('actions')))
								<div class="eoi-section-header" onclick="loadData(1,'{{ route('eoicancel.html') }}')"
									style="cursor:pointer; padding:8px 8px 16px 8px;">

									<div style="font-size:20px; font-weight:600; color:#2c3e50;">
										EoI Cancellation Requests
									</div>

									<div style="font-size:14px; color:#6c757d; margin:2px 0px; 8px 0">
										Review cancellation requests, understand the reasons, and keep EoI records accurate.
									</div>

								</div>
							@endif

							<div class="tab-content no-border" style="min-height:100px;">

								@if(in_array(2, Session::get('actions')) || in_array(3, Session::get('actions')) || in_array(12, Session::get('actions')))
									<div id="recordlist" class="tab-pane in active" style="padding:0px 0px!important;">

										{{-- Alerts --}}
										@if(Session::has('success'))
											<div class="alert alert-success">
												<i class="fa fa-thumbs-up"></i> {{ Session::get('success') }}
											</div>
										@endif

										@if(Session::has('fail'))
											<div class="alert alert-warning">
												<i class="fa fa-warning"></i> {{ Session::get('fail') }}
											</div>
										@endif

										<form id="pagedata" onsubmit="return false;">
											@csrf

											<div class="table-responsive">
												<table class="table table-bordered table-striped table-hover" id="tablerecords">
													<thead>
														<tr>
															<td class="padding-10">
																<div class="filters-group">

																	<select id="pagesize" name="pagesize"
																		class="select2" data-width="90"
																		onchange="loadData(1,'{{ route('eoicancel.html') }}')">
																		<option value="50">50</option>
																		<option value="100">100</option>
																		<option value="300">300</option>
																		<option value="500">500</option>
																	</select>

																	<select id="departmentid" name="departmentid"
																		class="select2" data-width="280"
																		data-placeholder="Department / Project Manager"
																		onchange="loadData(1,'{{ route('eoicancel.html') }}')">
																		<option value=""></option>
																		@foreach ($departments as $itm)
																			<option value="{{ $itm->userid }}">
																				{{ strtoupper($itm->shortname) }} -
																				{{ strtoupper($itm->departmentname) }}
																			</option>
																		@endforeach
																	</select>

																<span class="input-icon">
<input type="text" id="pagesearch" name="pagesearch" class="nav-search-input" placeholder="Search EoI and press enter" onchange="loadData(1,'{{ route('eoicancel.html') }}')" tabindex="{{ $t++ }}">
																	<i class="ace-icon fa fa-search nav-search-icon"></i>
																</span>															

																</div>
															</td>
														</tr>

													</thead>

													<tbody class="tabledata">
														<tr>
															<td class="center">-- Search Record --</td>
														</tr>
													</tbody>
												</table>
											</div>
										</form>

									</div>
								@endif
							</div>
						</div>

					</div>
				</div>
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

	{{-- jQuery FIRST --}}
	<script src="{{ asset('panel/assets/js/jquery-2.1.4.min.js') }}"></script>

	<script>
		$(document).ready(function () {

			$.ajaxSetup({
				headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
			});

			loadData(1, '{{ route('eoicancel.html') }}');
		});

		function loadData(page, r1) {
			$.get(r1, {
				page: page,
				pagesize: $('#pagesize').val(),
				departmentid: $('#departmentid').val(),
				pagesearch: $('#pagesearch').val()
			}, function (data) {
				$('.tabledata').html(data);
			});
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
function ClsFirmList()
{
	$('#firmModal').modal('hide');
	$('#firmContent').html("");
}

	</script>

	<script src="{{ asset('panel/assets/js/master.js') }}"></script>
	<script src="{{ asset('panel/assets/js/datatablePaginationWithFilter.js') }}"></script>
@endsection