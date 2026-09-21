@extends('admin.admin_master')
@section('admin')
	@php
		$t = 0;
	@endphp

	<div class="main-content">
		<div class="main-content-inner">
			<div class="page-content" style="padding:10px 15px!important;">
				<div class="ace-settings-container" id="ace-settings-container"></div>
				<div class="row">
					<div class="col-xs-12">
						<form name="frm" id="frm" action="{{ route('store.hiring', 0)}}"
							data-url="{{ route('update.pmeoirecord')}}" method="post" enctype="multipart/form-data">
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
							@if(Session::has('fail'))
								<div class="col-sm-12">
									<div class="alert alert-block alert-danger">
										<button type="button" class="close" data-dismiss="alert">
											<i class="ace-icon fa fa-times"></i>
										</button>
										{{ Session::get('fail') }}
									</div>
								</div>
							@endif
							@if(Session::has('duplicate'))
								<div class="col-sm-12">
									<div class="alert alert-block alert-danger">
										<button type="button" class="close" data-dismiss="alert">
											<i class="ace-icon fa fa-times"></i>
										</button>
										<i class="fa fa-warning"></i> {{ Session::get('duplicate') }}
									</div>
								</div>
							@endif
							@if ($errors->any())
								<div class="col-sm-12">
									<div class="alert alert-block alert-danger">
										<button type="button" class="close" data-dismiss="alert">
											<i class="ace-icon fa fa-times"></i>
										</button>
										<ul>
											@foreach ($errors->all() as $error)
												<li>{{ $error }}</li>
											@endforeach
										</ul>
									</div>
								</div>
							@endif
							@csrf

							<div class="multi-panel multi-panel-default">
								<div class="multi-panel-heading">
									<h3 class="multi-panel-title">Project and Resource Details</h3>
								</div>
								<div class="multi-panel-body">
									<!-- Progress Bar -->
									<div class="progress progress-striped">
										<div class="progress-bar progress-bar-success active" role="progressbar"
											id="progressBar" style="width: 33%; vertical-align:middle; line-height:30px;">
											Step 1 of 3
										</div>
									</div>

									<!-- Step Indicator -->
									<ul class="multi-step-indicator">
										<li class="active completed">
											<span class="multi-step-number">1</span>
											<span class="multi-step-title form-label">Project Details</span>
										</li>
										<li class="active">
											<span class="multi-step-number">2</span>
											<span class="multi-step-title form-label">Resources Requirement</span>
										</li>
										<li>
											<span class="multi-step-number">3</span>
											<span class="multi-step-title form-label">Uploaded Request Letter</span>
										</li>
									</ul>

									<!-- Step 1: Basic Information -->
									<div class="multi-form-section" id="step1">

										<div class="col-sm-9 padding-5">
											<span class="form-label">Project Name <label id="req">*</label></span>
											<input type="text" class="form-control" name="projecttitle" id="projecttitle"
												value="{{old('projecttitle', session('projecttitle'))}}"
												placeholder="Project Name" autofocus required autocomplete="off"
												onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}" />
											<span class="text-danger">@error('projecttitle') <i class="fa fa-hand-o-right">
											{{ strtoupper($message) }} </i> @enderror</span>
										</div>
										<div class="col-sm-3 padding-5">
											<span class="form-label">Project Duration <label id="req">(In
													months)*</label></span>
											<select class="form-control" name="projectduration" id="projectduration"
												required onKeyPress="return OnKeyPress(this, event)"
												data-placeholder="Project Duration"
												onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}"
												@if(Session::has('categoryid')) disabled @endif
												onchange="setDefaultDuration(this.value)">
												<option value="">--Project Duration--</option>
												@for($i = 1; $i <= 60; $i++)
													<option value="{{ $i }}" @if($i == session('projectduration')) selected
													@endif>{{ $i }} Months</option>
												@endfor
											</select>


											<span class="text-danger">@error('projectduration') <i
												class="fa fa-hand-o-right"> {{ strtoupper($message) }} </i>
											@enderror</span>
										</div>
										<div class="col-sm-12">&nbsp;</div>
										<div class="col-sm-12 padding-5">
											<span class="form-label">Project Objective <label id="req">*</label></span>
											<textarea class="form-control tinymce" name="projectobjective" required
												id="projectobjective" placeholder="Project objective"
												onKeyPress="return OnKeyPress(this, event)"
												tabindex="{{$t++}}">{!!old('projectobjective', $eoi->projectobjective)!!}</textarea>

											<span class="text-danger">@error('projectobjective') <i
												class="fa fa-hand-o-right"> {{ strtoupper($message) }} </i>
											@enderror</span>
										</div>
										<div class="col-sm-12">&nbsp;</div>
										<div class="col-sm-12 padding-5">
											<span class="form-label">Scope Of Work <label id="req">*</label></span> <a
												href="#" class="open-scope sample-link"
												data-url="{{ route('read.scopeofwork') }}">(Click here for sample data)</a>
											<textarea class="form-control tinymce" name="scopeofwork" required
												id="scopeofwork" placeholder="Enter scope of work detail"
												onKeyPress="return OnKeyPress(this, event)"
												tabindex="{{$t++}}">{{old('scopeofwork', $eoi->scopeofwork)}}</textarea>

											<span class="text-danger">@error('scopeofwork') <i class="fa fa-hand-o-right">
											{{ strtoupper($message) }} </i> @enderror</span>
										</div>
										<div class="col-sm-12">&nbsp;</div>
										<div class="col-sm-12 padding-5">
											<span class="form-label">Other information (if any) <label
													id="req">&nbsp;</label></span> <a href="#"
												class="open-otherproject sample-link"
												data-url="{{ route('read.otherproject') }}">(Click here for sample data)</a>
											<textarea class="form-control tinymce" name="anyother" id="anyother"
												placeholder="Other information (if any)"
												onKeyPress="return OnKeyPress(this, event)"
												tabindex="{{$t++}}">{{old('anyother', $eoi->anyother)}}</textarea>

											<span class="text-danger">@error('anyother') <i class="fa fa-hand-o-right">
											{{ strtoupper($message) }} </i> @enderror</span>
										</div>
										<div class="col-sm-12">&nbsp;</div>

									</div>

									<!-- Step 2: EoI Details -->
									<div class="multi-form-section active" id="step2" style="height:300px;">
										<div class="col-sm-3 padding-5">
											<span class="form-label">Type of Empanelment <label id="req">*</label></span>
											<input type="hidden" name="form_token" id="form_token" value="{{$token}}">
											<select class="form-control" name="categoryid" id="categoryid" required
												onKeyPress="return OnKeyPress(this, event)"
												data-placeholder="Type of Empanelment"
												onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}"
												onchange="GetManagerForm('{{route('load-pm-form')}}');">
												@foreach ($category as $itm)
													@if($eoi->categoryid == $itm->categoryid)
														<option value="{{ $itm->categoryid }}" {{ intval(old('categoryid', session('categoryid'))) === $itm->categoryid ? 'selected' : '' }}>{{ strtoupper($itm->jobcategory) }}</option>
													@endif
												@endforeach

											</select>
											<input type="hidden" name="tierid" id="tierid" value="1">
											<input type="hidden" name="requestid" id="requestid"
												value="{{Crypt::encrypt($eoi->requestid)}}">
											<span class="text-danger">@error('categoryid') <i class="fa fa-hand-o-right">
											{{ strtoupper($message) }} </i> @enderror</span>
										</div>
										<div class="col-sm-9 mt-30 text-right">
											<button type="button" class="btn btn-info btn-sm add-resource-btn" onclick="AddResource()" tabindex="{{$t++}}" id="" >
												<i class="fa fa-plus"></i> Add Resource
											</button>		
										
										</div>
										
										<div class="col-sm-12 padding-5 displayform">

										</div>


									</div>

									<!-- Step 3: Requirements -->
									<div class="multi-form-section" id="step3"
										style="height:300px; max-height:300px; overflow-y:scroll;">
										<table class="table table-bordered table-striped table-hover resource-table upload-table eoifiles">
											<tr class="myheadbg">
												<td class="padding-8 form-label" colspan="3"
													style="vertical-align:middle!important;">
													Required Documents (Uploaded)
												</td>
											</tr>
											<tr>
												<td class="padding-10 upload-input">
							<input type="text" name="authletter" required placeholder="" readonly
														autocomplete="off" class="form-control full-wdth"
														onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}"
														value="Authorization Letter*">
												</td>

												<td class="padding-10 upload-file" >
							<input type="file" class="form-control" required
														name="authorizationletter" id="authorizationletter"
														data-id="{{Crypt::encrypt($eoi->requestid)}}"
														accept="application/pdf" onKeyPress="return OnKeyPress(this, event)"
														tabindex="{{$t++}}" />
												</td>
												<td class="padding-8 center" style="width:130px;">
													<a href="{{ route('view.uploadedfile',Crypt::encrypt($eoi->authorizationletter)) }}"
														target="_blank">
														<button type="button" class="btn btn-info gridbtn" style="width:100%;">
															<i class="fa fa-eye"></i> View File
														</button>

													</a>

												</td>
											</tr>

										</table>

										<table
											class="mytable table table-bordered table-striped fileattachment committeetable"
											border="1" style="text-transform:none!important;">
											<tr class="myheadbg">
												<td class="padding-8 form-label" colspan="6">
													Upload Documents
												</td>
											</tr>
											@if($attachments->count() > 0)
												@foreach($attachments as $attach)
														<tr>
															<td class="padding-10 upload-input">
							<input type="text" name="attachmenttitle{{$loop->iteration}}"
																	id="attachmenttitle{{$loop->iteration}}" required
																	placeholder="Attachment title" autocomplete="off" class="form-control full-wdth"
																	value="{{$attach->attachmenttitle}}"
																	onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}">
															</td>
															<td class="padding-8 center" style="width:250px;">
																<button type="button" class="btn btn-info" style="width:120px;"
																	onclick="updateAttachmentTitle({{$loop->iteration}},'{{Crypt::encrypt($attach->recordid)}}','{{Crypt::encrypt($eoi->requestid)}}')">
																	<i class="fa fa-eye"></i> Update Title
																</button>

																<a href="{{ route('view.uploadedfile',Crypt::encrypt($attach->attachmentfile)) }}"
																	target="_blank">
																	<button type="button" class="btn btn-info gridbtn" style="width:100%;">
																		<i class="fa fa-eye"></i> View File
																	</button>

																</a>

															</td>
															<td class="padding-8 center" style="width:120px;">
																<!--
														<button type="button" class="btn btn-info" style="width:120px;" onclick="removeAttachment('{{Crypt::encrypt($attach->recordid)}}','{{Crypt::encrypt($eoi->requestid)}}')">
															<i class="fa fa-remove"></i> Remove File
														</button>
													-->
															</td>
														</tr>

												@endforeach
											@endif
											<tr>
												<td class="padding-10 upload-input">
							<input type="text" name="attachmenttitles[]" required
														placeholder="Attachment title" autocomplete="off" class="form-control full-wdth"
														onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}">
												</td>
												<td class="padding-10 upload-file" >
							<input type="file" class="form-control attachment" required
														name="attachmentfiles[]" accept="application/pdf"
														onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}" />
												</td>
												<td class="padding-8" style="width:120px;">
													<button type="button" class="btn btn-info gridbtn addBtn" style="width:120px;">
														<i class="fa fa-plus"></i> ADD
													</button>
												</td>
											</tr>
										</table>

									</div>


									<!-- Navigation Buttons -->

									<div class="multi-form-navigation">
										<!--
								<button type="button" class="multi-btn multi-btn-info frmUpdateOnly" id="pgBtn" tabindex="{{$t++}}" id="" style="width:150px;">
									<i class="fa fa-save"></i> Update
								</button>								
							-->
										<button type="button" class="multi-btn multi-btn-default" id="prevBtn"
											onclick="changeStep(-1)"><i class="fa fa-angle-double-left"></i>
											Previous</button>
										<button type="button" class="multi-btn multi-btn-primary" id="nextBtn"
											onclick="changeStep(1)">Next <i class="fa fa-angle-double-right"></i></button>
										<button type="button" class="multi-btn multi-btn-success" id="submitBtn"
											style="display: none;">Upload New Files</button>
									</div>
								</div>
							</div>



						</form>


					</div><!-- /.col -->
				</div><!-- /.row -->
			</div><!-- /.page-content -->


		</div>
	</div>

<div class="modal fade" id="docModal" tabindex="-1">
	<div class="modal-dialog modal-xl" style="width:95%;">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" style="float:left;">Sample Data</h5>
				<!-- Proper close icon button -->
				<button type="button" class="btn btn-close" style="float:right;" data-bs-dismiss="modal">Close</button>
			</div>
			<div class="modal-body" id="docContent">
				Loading...
			</div>
			<div class="modal-footer">
				<!-- Text button to close -->
				<button type="button" class="btn btn-close" data-bs-dismiss="modal">Close</button>
			</div>
		</div>
	</div>
</div>


<div class="modal fade" id="editModal">
	<div class="modal-dialog modal-xl" style="width:95%; margin-top:0px;">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" style="float:left;">Update Resource Data</h5>
				<button type="button" class="btn btn-close" style="float:right;" data-bs-dismiss="modal"
					onclick="ClsResource()">Close</button>
			</div>
			<div class="modal-body" id="editContent">
				Loading...
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-close" data-bs-dismiss="modal"
					onclick="ClsResource()">Close</button>
			</div>
		</div>
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
<script src="{{ asset('panel/assets/js/ace-elements.min.js') }}"></script>
<script src="{{ asset('panel/assets/js/ace.min.js') }}"></script>

<script>
function showPrice(rl)
{
	let formData = new FormData();
	formData.append('_token', '{{ csrf_token() }}');
	$.ajax({
		url: ''+rl,
		method: 'POST',
		data: formData,
		processData: false,
		contentType: false,
		success: function(response) {
			$('#comparisionModal').modal('show');
			$('#comparisionContent').html(response.formhtml);
		},
		error: function(xhr) {
			bootbox.alert('An error occurred while saving the data.');
		}
	});
}

	function editEois(rl, recordid)
	{
		let formData = new FormData();
		formData.append('_token', '{{ csrf_token() }}');
		formData.append('recordid', recordid);
		$.ajax({
			url: '{{ route("update.pmresourcedetail") }}',
			method: 'POST',
			data: formData,
			processData: false,
			contentType: false,
			success: function (response) {
				if (response.status === 200) {
					$('#editModal').modal('show');
					$('#editContent').html(response.formhtml);
				}
				else {
					bootbox.alert('Something went wrong: ' + response.message);
				}
			},
			error: function (xhr) {
				bootbox.alert('An error occurred while saving the data.');
			}
		});

	}

	function ClsResource()
	{
		$('#editModal').modal('hide');
		$('#editContent').html("");
		$('#comparisionModal').modal('hide');
		$('#comparisionContent').html("");
	}
	function AddResource()
	{
		$('#resourceModal').modal('show');
	}
	
	$(document).ready(function () {

		$('.open-vendor').on('click', function (e) {
			e.preventDefault();
			var docUrl = $(this).data('url');
			$(".modal-xl").css("width", "70%")
			$(".modal-title").html("Vendors List");
			$('#docContent').html('Loading...');
			$('#docModal').modal('show');

			$.get(docUrl, function (data) {
				$('#docContent').html(data);
			}).fail(function () {
				$('#docContent').html('<p class="text-danger">Failed to load document.</p>');
			});
		});

		$('.open-scope').on('click', function (e) {
			e.preventDefault();
			var docUrl = $(this).data('url');
			$(".modal-xl").css("width", "95%")
			$(".modal-title").html("Sample Scope Of Work Content");
			$('#docContent').html('Loading...');
			$('#docModal').modal('show');

			$.get(docUrl, function (data) {
				$('#docContent').html(data);
			}).fail(function () {
				$('#docContent').html('<p class="text-danger">Failed to load document.</p>');
			});
		});

		$('.open-about').on('click', function (e) {
			e.preventDefault();
			var docUrl = $(this).data('url');
			$(".modal-xl").css("width", "95%")
			$(".modal-title").html("Sample About Project Content");
			$('#docContent').html('Loading...');
			$('#docModal').modal('show');

			$.get(docUrl, function (data) {
				$('#docContent').html(data);
			}).fail(function () {
				$('#docContent').html('<p class="text-danger">Failed to load document.</p>');
			});
		});

		$('.open-otherproject').on('click', function (e) {
			e.preventDefault();
			var docUrl = $(this).data('url');
			$(".modal-xl").css("width", "95%")
			$(".modal-title").html("Sample Other Project Requirement Content");
			$('#docContent').html('Loading...');
			$('#docModal').modal('show');

			$.get(docUrl, function (data) {
				$('#docContent').html(data);
			}).fail(function () {
				$('#docContent').html('<p class="text-danger">Failed to load document.</p>');
			});
		});

		$('.btn-close').on('click', function (e) {

			$('#docContent').html('');
			$('#docModal').modal('hide');
			$('#resourceModal').modal('hide');
		});

	});



	$(".con").css("display", "none");

	setTimeout(function () { GetPmUpdateForm('{{route("load-pmupdateform")}}'); }, 2000);

	$.ajaxSetup({
		headers: {
			'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
		}
	});

</script>
<script>
	$(document).ready(function () {

		$('#clrBtn').on('click', function (e) {
			e.preventDefault();
			bootbox.confirm('DO YOU WANT TO CLEAR LIST?', function (result) {
				if (result) {
					var form = $('#frm')[0];
					var formData = new FormData(form);
					$.ajax({
						url: '{{route("clear.eois")}}',
						method: 'POST',
						data: formData,
						processData: false,
						contentType: false,
						success: function (response) {
							if (response.status === 200) {
								window.location.reload();
							}
							else {
								bootbox.alert('Something went wrong: ' + response.message);
							}
						},
						error: function (xhr) {
						}
					});
				}
			});
		});


	});


	function updateAttachmentTitle(ind, recordid, requestid) {
		const attachmenttitle = document.getElementById('attachmenttitle' + ind).value.trim();

		let formData = new FormData();
		formData.append('_token', '{{ csrf_token() }}');
		formData.append('attachmenttitle', attachmenttitle);
		formData.append('recordid', recordid);
		formData.append('requestid', requestid);

		$.ajax({
			url: '{{ route("update.pmattachmenttitle") }}',
			method: 'POST',
			data: formData,
			processData: false,
			contentType: false,
			success: function (response) {
				if (response.status === 200) {
					bootbox.alert('Attachment title updated successfully!');
				}
				else {
					bootbox.alert('Something went wrong: ' + response.message);
				}
			},
			error: function (xhr) {
				bootbox.alert('An error occurred while saving the data.');
			}
		});
	}

	function removeAttachment(recordid, requestid) {
		bootbox.confirm('Are you sure you want to remove the attached file? This cannot be undone.', function (result) {
			if (result) {
				let formData = new FormData();
				formData.append('_token', '{{ csrf_token() }}');
				formData.append('recordid', recordid);
				formData.append('requestid', requestid);

				$.ajax({
					url: '{{ route("remove.pmattachedfile") }}',
					method: 'POST',
					data: formData,
					processData: false,
					contentType: false,
					success: function (response) {
						if (response.status === 200) {
							setTimeout(function () {
								$(".fileattachment").html(response.formhtml);
								$(".no-skin").css("padding-right", "");
							}, 2000);
						}
						else if (response.status === 400) {
							bootbox.alert(response.message);
						}
						else {
							bootbox.alert('Something went wrong: ' + response.message);
						}
					},
					error: function (xhr) {
						bootbox.alert('An error occurred while saving the data.');
					}
				});

			}
		});
	}

	jQuery(function ($) {

		$('#draftformat').ace_file_input({
			no_file: 'No File ...',
			btn_choose: 'Upload Draft Format with Seal & Signature (Optional) max file size : 5 mb, PDF only',
			btn_change: 'Change',
			btn_name: 'fourthimage',
			thumbnail: false //| true | large
		});

		$('#eoiformat').ace_file_input({
			no_file: 'No File ...',
			btn_choose: 'Upload Complete EoI Format (Optional) max file size : 5 mb, PDF only',
			btn_change: 'Change',
			btn_name: 'fourthimage',
			thumbnail: false //| true | large
		});

		$('.attachment').ace_file_input({
			no_file: 'No File ...',
			btn_choose: 'Attachment',
			btn_change: 'Change',
			btn_name: 'fourthimage',
			thumbnail: false //| true | large
		});

		$('#authorizationletter').ace_file_input({
			no_file: 'No File ...',
			btn_choose: 'Update file (if required)',
			btn_change: 'Change',
			btn_name: 'fourthimage',
			thumbnail: false //| true | large
		});
		$('#draftedeoi').ace_file_input({
			no_file: 'No File ...',
			btn_choose: 'Update file (if required)',
			btn_change: 'Change',
			btn_name: 'fourthimage',
			thumbnail: false //| true | large
		});


	});
	$('#authorizationletter').on('change', function (e) {
		const requestid = $(this).data('id');
		const authorizationletter = document.getElementById("authorizationletter").files[0];
		// Check if a file is selected
		if (authorizationletter) {
			// Show confirmation dialog
			if (confirm('Are you sure you want to upload this file?')) {
				let formData = new FormData();
				formData.append('_token', '{{ csrf_token() }}');
				formData.append('requestid', requestid);
				formData.append('authorizationletter', authorizationletter);
				$.ajax({
					url: '{{ route("update.pmeoifile") }}',
					method: 'POST',
					data: formData,
					processData: false,
					contentType: false,
					success: function (response) {
						if (response.status === 200) {
							bootbox.alert(response.message);
							setTimeout(function () {
								$(".eoifiles").html(response.formhtml);
								$(".no-skin").css("padding-right", "");
							}, 2000);
						}
						else if (response.status === 400) {
							bootbox.alert(response.message);
						}
						else {
							bootbox.alert('Something went wrong: ' + response.message);
						}
						$('#authorizationletter').ace_file_input('reset_input');
					},
					error: function (xhr) {
						$('#authorizationletter').ace_file_input('reset_input');
						if (xhr.responseJSON && xhr.responseJSON.errors) {
							var errors = xhr.responseJSON.errors;
							var allMessages = '';

							$.each(errors, function (field, messages) {
								$.each(messages, function (index, msg) {
									allMessages += msg + '<br>';
								});
							});
							bootbox.alert(allMessages);
						}

					}
				});

			} else {
				// If not confirmed, reset the file input
				$('#authorizationletter').ace_file_input('reset_input');
			}
		}
		else {
			alert("Please select authorization letter");
			return false;
		}
	});

	$('#draftedeoi').on('change', function (e) {
		const requestid = $(this).data('id');
		const draftedeoi = document.getElementById("draftedeoi").files[0];
		// Check if a file is selected
		if (draftedeoi) {
			// Show confirmation dialog
			if (confirm('Are you sure you want to upload this file?')) {
				let formData = new FormData();
				formData.append('_token', '{{ csrf_token() }}');
				formData.append('requestid', requestid);
				formData.append('draftedeoi', draftedeoi);
				$.ajax({
					url: '{{ route("update.pmeoifile") }}',
					method: 'POST',
					data: formData,
					processData: false,
					contentType: false,
					success: function (response) {
						if (response.status === 200) {
							setTimeout(function () {
								$(".eoifiles").html(response.formhtml);
								$(".no-skin").css("padding-right", "");
							}, 2000);
						}
						else if (response.status === 400) {
							bootbox.alert(response.message);
						}
						else {
							bootbox.alert('Something went wrong: ' + response.message);
						}
						$('#authorizationletter').ace_file_input('reset_input');
					},
					error: function (xhr) {
						$('#authorizationletter').ace_file_input('reset_input');
						if (xhr.responseJSON && xhr.responseJSON.errors) {
							var errors = xhr.responseJSON.errors;
							var allMessages = '';

							$.each(errors, function (field, messages) {
								$.each(messages, function (index, msg) {
									allMessages += msg + '<br>';
								});
							});
							bootbox.alert(allMessages);
						}

					}
				});

			} else {
				// If not confirmed, reset the file input
				$('#authorizationletter').ace_file_input('reset_input');
			}
		}
		else {
			alert("Please select authorization letter");
			return false;
		}
	});


	function deletePmEois(rl, recordid, ind, asking, msg) {
		var ind = ind - 1;
		var flag = 0;
		bootbox.confirm(asking, function (result) {
			if (result) {
				$.ajax({
					url: '/delete/' + rl + '/' + encodeURIComponent(recordid),
					type: 'GET',
					headers: {
						'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
					},
					success: function (response) {
						if (response.fail == '') {
							bootbox.alert({
								message: msg,
								callback: function () {
									setTimeout(function () {
										$(".no-skin").css("padding-right", "");
										GetForm('{{route("load-pmupdateform")}}');
									}, 1000);
								}
							});
						}
						else {
							bootbox.alert(response.fail);
						}
					},
					error: function (xhr) {
						//console.log(xhr);
					}
				});
			}
		});
	}

	$(document).ready(function () {

		$('.addBtn').on('click', function () {
			let newRow = `
		<tr class="datarow">
			<td class="padding-10 upload-input">
						<input type="text" name="attachmenttitles[]" required placeholder="Attachment title" autocomplete="off" class="form-control full-wdth" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}">
			</td>
			<td class="padding-10 upload-file" >
						<input type="file" class="form-control attachment" required name="attachmentfiles[]" accept="application/pdf" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}" />
			</td>
			<td class="padding-10 upload-actions" >
						<button type="button" class="btn btn-info removeRow"><i class="fa fa-trash"></i> Remove</button>
			</td>
		</tr>`;

			// Append the new row
			$('.committeetable').append(newRow);

			// Re-initialize ace_file_input on the newly added file input
			$('.committeetable .attachment').last().ace_file_input({
				no_file: 'No File ...',
				btn_choose: 'Attachment',
				btn_change: 'Change',
				btn_name: 'fourthimage',
				thumbnail: false
			});
		});

		$('.mytable').on('click', '.removeRow', function () {
			$(this).closest('tr').remove();
		});


	});

	function setDefaultDuration(val) {
		var $duration = $('#duration');
		$duration.empty();
		if (!isNaN(val)) {
			for (var i = 1; i <= val; i++) {
				$duration.append('<option value="' + i + '">' + i + ' Months</option>');
			}
		}
		$("#duration").val(val);
	}

</script>
<script src="{{ asset('panel/assets/js/master.js') }}"></script>
<script src="{{ asset('panel/assets/js/multi-form-update.js') }}"></script>
<script src="{{ asset('panel/assets/js/datatablePaginationWithFilter.js') }}"></script>

<script src="{{ asset('panel/assets/tinymce/tinymce.min.js') }}"></script>


<script>
setTimeout(function(){
	
jQuery(function($) {

    // -----------------------------
    // TinyMCE editors configuration
    // -----------------------------
    const editors = [
        { selector: 'textarea.tinymce', minHeight: 180 },
        { selector: '#scopeofwork', height: 250 },
        { selector: '#anyother', height: 250 },
        { selector: '#projectobjective', height: 250 }
    ];

    editors.forEach(cfg => {
        tinymce.init({
            selector: cfg.selector,
            promotion: false,
            branding: false,
            height: cfg.height || cfg.minHeight,
            min_height: cfg.minHeight || 0,
            autoresize_min_height: cfg.minHeight || 0,
            autoresize_max_height: 700,
            autoresize_bottom_margin: 10,
            toolbar_mode: 'floating',
            plugins: 'autoresize code advlist autolink lists charmap preview table searchreplace save',
            toolbar: 'undo redo | formatselect | bold italic underline strikethrough | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link | searchreplace',
            menubar: false,
            statusbar: false,
            resize: true,
            setup: function(editor) {
                editor.on('init', function() {
                    editor.getBody().style.fontSize = '14px';
                    editor.getBody().style.lineHeight = '1.5';
                    const contentArea = editor.getContainer().querySelector('.tox-edit-area__iframe');
                    if (contentArea) contentArea.style.overflow = 'auto';
                });
                editor.on('change keyup', function() {
                    editor.save();
                });
            }
        });
    });
	

	$('#submitBtn').on('click', function (e) {
		e.preventDefault();
		$("#submitBtn").prop("disabled", "disabled");
		let formData = new FormData();
		formData.append('_token', '{{ csrf_token() }}');
		formData.append('requestid', '{{ Crypt::encrypt($eoi->requestid) }}');

		let isValid = true;
		let hasAtLeastOneFile = false; // Optional: check if at least one file is added

		$('input[name="attachmenttitles[]"]').each(function (index) {
			let title = $(this).val()?.trim();
			let fileInput = $(this).closest('tr').find('input[name="attachmentfiles[]"]');
			let file = fileInput.length > 0 ? fileInput[0].files[0] : null;

			if (title === '' && !file) {
				return;
			}

			if (title && file) {
				formData.append('attachmenttitles[' + index + ']', title);
				formData.append('attachmentfiles[' + index + ']', file);
				hasAtLeastOneFile = true;
			} else {
				isValid = false;
				return false;
			}
		});

		if (!isValid) {
			bootbox.alert('Please fill in both attachment title and file for all rows.');
			$("#submitBtn").prop("disabled", "");
			return;
		}

		if (!hasAtLeastOneFile) {
			bootbox.alert('Please add at least one attachment.');
			$("#submitBtn").prop("disabled", "");
			return;
		}

		$.ajax({
			url: '{{ route("upload.pmattachments") }}',  // Replace with your actual route
			method: 'POST',
			data: formData,
			processData: false,
			contentType: false,
			success: function (response) {
				if (response.status === 200) {
					$("#submitBtn").prop("disabled", "");
					$(".fileattachment").html(response.formhtml);
				}
				else if (response.status === 400) {
					bootbox.alert(response.message);
					$("#submitBtn").prop("disabled", "");
				}
				else {
					bootbox.alert('Upload failed: ' + response.message);
					$("#submitBtn").prop("disabled", "");
				}
			},
			error: function (xhr) {
				if (xhr.responseJSON && xhr.responseJSON.errors) {
					var errors = xhr.responseJSON.errors;
					var allMessages = '';

					$.each(errors, function (field, messages) {
						$.each(messages, function (index, msg) {
							allMessages += msg + '<br>';
						});
					});
					bootbox.alert(allMessages);
				}
				$("#submitBtn").prop("disabled", "");
			}
		});
	});

	$('#frmPrint').on('click', function (e) {
		e.preventDefault();
		tinymce.triggerSave();

		const projectobjective = document.getElementById('projectobjective').value.trim();
		const aboutproject = document.getElementById('aboutproject').value.trim();
		const scopeofwork = document.getElementById('scopeofwork').value.trim();
		const anyother = document.getElementById('anyother').value.trim();

		// Create a form dynamically
		let form = document.createElement("form");
		form.method = "POST";
		form.action = "{{ route('print.eoi') }}";
		form.target = "_blank";

		// Add CSRF token
		let csrf = document.createElement("input");
		csrf.type = "hidden";
		csrf.name = "_token";
		csrf.value = "{{ csrf_token() }}";
		form.appendChild(csrf);

		// Add form fields
		const fields = {
			scope: scopeofwork,
			aboutproject: aboutproject,
			anyother: anyother,
			projectobjective: projectobjective
		};

		for (let key in fields) {
			let input = document.createElement("input");
			input.type = "hidden";
			input.name = key;
			input.value = fields[key];
			form.appendChild(input);
		}

		document.body.appendChild(form);
		form.submit();
		form.remove();
	});


	$('.frmUpdateOnly').on('click', function (e) {
		e.preventDefault();
		tinymce.triggerSave();

		const aboutproject = document.getElementById('aboutproject').value.trim();
		const scopeofwork = document.getElementById('scopeofwork').value.trim();
		const anyother = document.getElementById('anyother').value.trim();
		const projecttitle = document.getElementById('projecttitle').value.trim();
		const projectobjective = document.getElementById('projectobjective').value.trim();

		let formData = new FormData();
		formData.append('_token', '{{ csrf_token() }}');
		formData.append('scopeofwork', scopeofwork);
		formData.append('aboutproject', aboutproject);
		formData.append('anyother', anyother);
		formData.append('projecttitle', projecttitle);
		formData.append('projectobjective', projectobjective);

		$.ajax({
			url: '{{ route("updateonly.pmeoi") }}',
			method: 'POST',
			data: formData,
			processData: false,
			contentType: false,
			success: function (response) {
				if (response.status === 200) {
					// You can customize what happens after a successful save
					bootbox.alert('Data saved successfully!');
				} else {
					bootbox.alert('Something went wrong: ' + response.message);
				}
			},
			error: function (xhr) {
				bootbox.alert('An error occurred while saving the data.');
			}
		});
	});

	$('#nextBtn').on('click', function (e) {
		e.preventDefault();
		tinymce.triggerSave();
		const scopeofwork      = tinymce.get('scopeofwork').getContent({ format: 'html' }).trim();
		const anyother         = tinymce.get('anyother').getContent({ format: 'html' }).trim();
		const projecttitle     = document.getElementById('projecttitle').value.trim();
		const projectobjective = tinymce.get('projectobjective').getContent({ format: 'html' }).trim();


		let formData = new FormData();
		formData.append('_token', '{{ csrf_token() }}');
		formData.append('scopeofwork', scopeofwork);
		formData.append('anyother', anyother);
		formData.append('projecttitle', projecttitle);
		formData.append('projectobjective', projectobjective);

		$.ajax({
			url: '{{ route("updateonly.pmeoi") }}',
			method: 'POST',
			data: formData,
			processData: false,
			contentType: false,
			success: function (response) {
				if (response.status === 200) {
					bootbox.alert('Data saved successfully!');
				} else {
					bootbox.alert('Something went wrong: ' + response.message);
				}
			},
			error: function (xhr) {
				bootbox.alert('An error occurred while saving the data.');
			}
		});
	});
	
	
});	
	
},3000);
</script>
@endsection