@extends('admin.admin_master')
@section('admin')
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
<div class="tabbable animated fadeInUp delay-02" style="padding:0px 0px!important;">
		<ul class="nav nav-tabs padding-0">
		@if(in_array(1,Session::get('actions')))
			<li class="active">
				<a data-toggle="tab" href="#home" class="form-label font-14">
					<i class="green ace-icon fa fa-folder-o bigger-120 datalist" style="vertical-align:text-top;"></i> Upload Document
				</a>
			</li>
		@endif
		</ul>

	
		<div class="tab-content no-border" style="border:1px solid #ddd; min-height:100px; padding:0px 10px;">
@if(in_array(1,Session::get('actions')))
<div id="home" class="tab-pane in active" style="padding:0px 10px!important;">
	<div class="row">
		
			<form name="participate" id="participate" action="{{ route('upload.ppt',Crypt::encrypt($data->floatid))}}" method="post" enctype="multipart/form-data">
				@if(Session::has('success'))
				<br>
				<div class="col-sm-12">
					<div class="alert alert-block alert-success">
				    	<button type="button" class="close" data-dismiss="alert">
							<i class="ace-icon fa fa-times"></i>
						</button>
						{!! Session::get('success') !!}
					</div>
				</div>
				@endif
				@if(Session::has('fail'))
				<br>
				<div class="col-sm-12">
					<div class="alert alert-block alert-danger">
				    	<button type="button" class="close" data-dismiss="alert">
							<i class="ace-icon fa fa-times"></i>
						</button>
						<i class="fa fa-warning"></i> {{ Session::get('fail') }}
					</div>
				</div>
				@endif
				@if(Session::has('duplicate'))
				<br>
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
	<br>
	<div class="col-sm-12 animated flipInX">
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
                <i class="fa fa-warning"></i> {{ $error }}<br>
            @endforeach
        </ul>
    </div>
	</div>
@endif				
				@csrf
				<div class="form-group">
<div class="content-detail">
@if(!$data->signedcopyofeoi)
<div class="section-block-detail bg-warning">
	<div class="grid-layout-detail cols-2">
	<p><i class="fa fa-info-circle"></i> You are requested to complete your participation for this Expression of Interest (EoI) by submitting the required documents.</p>
	<ul>
		<li>Upload resumes for each proposed resource as per the project requirements.</li>
		<li>Submit the presentation file detailing your approach, methodology, and relevant experience. (if required)</li>
		<li>Upload the signed copy of the EoI document as confirmation of your acceptance.</li>
	</ul>
	</div>
</div>
@endif

	@include('admin.viewpages.factsheet')
	
	<div class="section-block-detail">
		<div class="section-header-detail">
			<span class="material-icons">group_add</span> <!-- Hamburger menu icon -->
			&nbsp;<h2 class="section-title-detail">Resource Details</h2>
		</div>
		<div class="table-responsive">
			<table class="mytable" border="1" style="text-transform: capitalize!important;">
				<tr class="myheadbg">
					@if($data->catid==2)
					<td class="mytd form-label" nowrap style="width:200px; padding:4px 5px!important;">Sector</td>
					<td class="mytd form-label" style="width:200px; padding:4px 5px!important;">Position</td>
					@endif
					@if($data->catid==1)
					<td class="mytd form-label" style="width:200px; padding:4px 5px!important;">Position</td>
					@endif
					<td class="mytd form-label" style="width:200px; padding:4px 5px!important;">Experience <i class="fa fa-info-circle" title="As Per Empanelment"></i></td>		
					<td class="mytd form-label" nowrap style="width:100px; padding:4px 5px!important;">Duration</td>
					<td class="mytd form-label" nowrap style="padding:4px 5px!important;">Resumes</td>
					<td class="mytd form-label" nowrap style="width:100px; padding:4px 5px!important;"></td>
				</tr>
				@foreach($eoidata as $eoi)
				@php $r=0; @endphp	
				<tr>
					@if($data->catid==2)
					<td class="mytd text-left" style="width:200px; padding:4px 5px!important; vertical-align:top!important;">{{ucwords(strtolower($eoi->sectorname))}}</td>
					<td class="mytd text-left" style="width:200px; padding:4px 5px!important; vertical-align:top!important;">{{$eoi->consultantposition}}</td>
					@endif
					@if($data->catid==1)
					<td class="mytd text-left" style="width:200px; padding:4px 5px!important; vertical-align:top!important;">
						{{$eoi->role}}
						
					</td>
					@endif
					<td class="mytd text-left" style="width:200px; padding:4px 5px!important; vertical-align:top!important;">
						@if($data->catid==1)
							@if($eoi->experience!='')
							{{$eoi->experience}} [L-{{$eoi->experiencelevel}}]
							@endif
						@elseif($data->catid==2)
						{{$eoi->experience}}
						@endif
					</td>
					<td class="mytd text-left" style="width:100px; padding:4px 5px!important; vertical-align:top!important;">
					{{$eoi->duration}} Months
					</td>
					
					<td rowspan="3" class="" style="vertical-align:top!important;">
					<table class="mytable" style="width:300px!important;" border="1">
						@foreach($eoi->resumes as $res)
						<tr>
							<td class="padding-2" style="width:150px!important;" nowrap>{{$res->name}}</td>
							<td class="padding-2 center width-30" nowrap>
							<a href="{{ route('view.uploadedfile',Crypt::encrypt($res->resume)) }}" title="View Resume" target="_blank">
								<i class="fa fa-file-pdf-o"></i>
							</a>
							</td>
							<td class="padding-2 center width-30" nowrap>
								<a onclick="RemoveResume('{{route('remove.resume')}}','{{Crypt::encrypt($res->resumeid)}}')" title="Remove Resume" target="_blank">
									<i class="fa fa-remove"></i>
								</a>
							</td>
						</tr>
						@endforeach
					</table>
					</td>
					<td rowspan="3" class="mytd text-left padding-2" style="vertical-align:top!important;">
						<input type="hidden" name="records[{{ $eoi->recordid }}][recordid]" value="{{$eoi->recordid}}">
						@if(session('vendorId')!=4 || $data->requestid!=177)
						<button type="button" class="btn btn-info gridbtn" id="uploadResume" style="width:100%;" onclick="UploadResume('{{Crypt::encrypt($eoi->recordid)}}')">
							<i class="fa fa-upload"></i> Add Resume
						</button>				
						@endif
					</td>
				</tr>
				<tr>
					<td colspan="3" class="mytd text-left" style="width:200px; padding:4px 5px!important; vertical-align:top!important;">
@php
$qualification = preg_replace(
    '/font-family\s*:[^;"]*;?|font-size\s*:[^;"]*;?|font-weight\s*:[^;"]*;?|font-style\s*:[^;"]*;?|border\s*:[^;"]*;?|line-height\s*:[^;"]*;?/i',
    '',
    $eoi->qualification
);
@endphp					

						Qualification : {!!$qualification!!}
					</td>

				</tr>
				<tr>
					<td colspan="3" class="mytd text-left" style="width:200px; padding:4px 5px!important; vertical-align:top!important;">
@php
$remark = preg_replace(
    '/font-family\s*:[^;"]*;?|font-size\s*:[^;"]*;?|font-weight\s*:[^;"]*;?|font-style\s*:[^;"]*;?|border\s*:[^;"]*;?|line-height\s*:[^;"]*;?/i',
    '',
    $eoi->remark
);
@endphp					
						Remark : {!!$remark!!}
					</td>
				</tr>
				@endforeach
				
			</table>
		</div>
	</div>
	<div class="section-block-detail">
		<div class="section-header-detail">
			<span class="material-icons">drive_folder_upload</span> <!-- Hamburger menu icon -->
			@if($data->categoryid==2)
			&nbsp;<h2 class="section-title-detail">Upload PPT / Signed EoI File</h2>
			@else
			&nbsp;<h2 class="section-title-detail">Upload Signed EoI File</h2>
			@endif
		</div>

		<table class="mytable" style="text-transform: none!important; border:none!important;">
			@if($data->categoryid==2)
			<tr>
				<td class="padding-8" nowrap> @if($data->presentationfile=='') Upload Presentation File* @else Replace File @endif</td>
				<td class="padding-8" style="width:300px;">
				<input type="file" class="form-control ppt" required name="ppt" accept=".pdf,.ppt,.pptx" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}"/>
				</td>
				<td class="padding-8">
				@if($data->categoryid==2)
					@if($data->presentationfile!='')
					<button type="button" class="btn btn-info gridbtn replacePresentationFile" style="width:130px;">
						<i class="fa fa-file-o"></i> Replace File
					</button>


					<a href="{{ asset('storage/' . $data->presentationfile) }}" target="_blank">
						<button type="button" class="btn btn-info gridbtn" style="width:100px;">
							<i class="fa fa-file-o"></i> View File
						</button>
					</a>
					@endif
				@endif
				</td>
			</tr>
			@endif
			@if($data->categoryid==2 || $data->categoryid==1)
			<tr>
				<td class="padding-8" style="width:200px;" nowrap>@if($data->signedcopyofeoi=='') Upload Signed EoI File* @else Replace File @endif</td>
				<td class="padding-8" style="width:300px;">
				<input type="file" class="form-control signedcopyofeoi" required name="signedcopyofeoi" accept=".pdf" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}" />
				</td>
				<td class="padding-8">
				@if($data->categoryid==2 || $data->categoryid==1)
					@if($data->signedcopyofeoi!='')
					<button type="button" class="btn btn-info gridbtn replaceSignedCopyOfEoi" style="width:130px;">
						<i class="fa fa-file-o"></i> Replace File
					</button>

					<a href="{{ asset('storage/' . $data->signedcopyofeoi) }}" target="_blank">
						<button type="button" class="btn btn-info gridbtn" style="width:100px;">
							<i class="fa fa-file-o"></i> View File
						</button>
					</a>
					@endif
				@endif
				</td>
				
			</tr>
			@endif
		</table>
	</div>
	
	<div class="section-block-detail">
		<div class="section-header-detail">
			<span class="fa fa-user font-20"></span> <!-- Hamburger menu icon -->
			&nbsp;<h2 class="section-title-detail">Please provide the contact person’s details so that we can reach out regarding participation in this EoI.</h2>
			
		</div>
		<table class="mytable pd-8" style="text-transform: none!important; border:none!important;">
			<tr class="myheadbg font-bold">
				<td colspan="2">Primary Contact Person</td>
			</tr>
			<tr>
				<td class="width-200">Full Name*</td>
				<td>
					<input type="text" name="contact_name" id="contact_name" class="form-control width-300" value="{{old('contact_name',$participation->contact_person)}}" placeholder="Full Name">
				</td>
			</tr>
			<tr>
				<td>Contact Number*</td>
				<td>
					<input type="text" name="contact_number" id="contact_number" value="{{old('contact_number',$participation->contact_number)}}" class="form-control width-300" placeholder="Contact number">
				</td>
			</tr>
			<tr>
				<td>Email Address*</td>
				<td>
					<input type="text" name="contact_email" id="contact_email" value="{{old('contact_email',$participation->contact_email)}}" class="form-control width-300" placeholder="Email Address">
				</td>
			</tr>
			<tr>
				<td style="text-align:right;" colspan="2" class="padding-8">
					<input type="hidden" name="floatid" id="floatid" value="{{Crypt::encrypt($data->floatid)}}">
					<input type="hidden" name="categoryid" id="categoryid" value="{{$data->categoryid}}">
				</td>
			</tr>
		</table>
		<table class="mytable pd-8" style="text-transform: none!important; border:none!important;">
			<tr class="myheadbg font-bold">
				<td colspan="2">Secondary Contact Person (Optional)</td>
			</tr>
			<tr>
				<td class="width-200">Full Name</td>
				<td>
					<input type="text" name="contact_name_2" id="contact_name_2" class="form-control width-300" value="{{old('contact_person_2',$participation->contact_person_2)}}" placeholder="Full Name">
				</td>
			</tr>
			<tr>
				<td>Contact Number</td>
				<td>
					<input type="text" name="contact_number_2" id="contact_number_2" value="{{old('contact_number_2',$participation->contact_number_2)}}" class="form-control width-300" placeholder="Contact number">
				</td>
			</tr>
			<tr>
				<td>Email Address</td>
				<td>
					<input type="text" name="contact_email_2" id="contact_email_2" value="{{old('contact_email_2',$participation->contact_email_2)}}" class="form-control width-300" placeholder="Email Address">
				</td>
			</tr>
		</table>
@if(!$data->otp_verified_on && $data->deadlinedate->lt(now()))
<table class="mytable pd-8" style="text-transform:none!important; border:none!important; width:100%;">
    <tr class="myheadbg font-bold">
        <td colspan="2">OTP Verification</td>
    </tr>

    <tr>
        <td class="width-200" style="vertical-align:middle;">
            Email
        </td>

        <td style="vertical-align:middle;">

            <div style="display:flex; align-items:center; gap:8px; flex-wrap:nowrap;">

                <!-- Email -->
                <input type="text"
                       name="otp_email"
                       id="otp_email"
                       class="form-control"
                       style="width:300px; display:inline-block;"
                       value=""
                       placeholder="Email">

                <!-- Send OTP -->
                <button type="button" id="sendOtpBtn" class="btn btn-info gridbtn" style="width:100px; white-space:nowrap;">Send OTP</button>

                <!-- OTP + Verify -->
                <div id="otpSection" style="display:none; align-items:center; gap:8px; flex-wrap:nowrap;">

                    <input type="text" name="otp" id="otp" class="form-control" style="width:150px; display:inline-block;" maxlength="6" inputmode="numeric" placeholder="6 digit OTP">

                    <button type="button" id="verifyOtpBtn" class="btn btn-info gridbtn" style="width:100px; white-space:nowrap;">
                        Verify
                    </button>

                </div>

                <!-- Message -->
                <span id="otpMessage"
                      style="white-space:nowrap;">
                </span>

            </div>

        </td>
    </tr>
</table>
@endif

	</div>
		@if($data->isinprebid==1 && $prebidPublished==0)
			<button type="button" class="btn btn-info" disabled id="participateBtn" style="width:400px; float:right; margin-top:20px; margin-bottom:20px;">
				<i class="fa fa-warning"></i> Please wait until the pre-bid response is published.
			</button>		
		@else
			
			@if($data->isresumeuploaded==0)
			<button type="submit" class="btn btn-info partBtn" @if(!$data->otp_verified_on) disabled @endif id="participateBtn" style="width:200px; float:right; margin-top:20px; margin-bottom:20px;">
				<i class="fa fa-save"></i> Submit & Participate
			</button>
			@else
			<button type="button" class="btn btn-info" disabled style="width:100px; float:right; margin-top:20px; margin-bottom:20px;">
				Uploaded
			</button>		
			@endif
			
			<br><br><br><br>
		@endif
</div>



<div class="col-sm-12">&nbsp;</div>
<div class="col-sm-12">
<br>
</div>
<div class="col-sm-12">&nbsp;</div>
				</div>		
			</form>
		
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
		<div class="modal-content" style="font-size:12px; width:60%; margin:0 auto; margin-top:10px; background-color:white; text-transform:none!important; padding:20px;">
			<form name="resume" id="resume" action="{{ route('upload.resumes',Crypt::encrypt($data->floatid))}}" method="post" enctype="multipart/form-data" onsubmit="return false;">
				<table class="mytable resumetable" border="1" style="text-transform:none!important">
					<tr class="myheadbg">
						<td class="padding-5 form-label" colspan="4">
							<i class="fa fa-upload"></i> Upload Resume
							<input type="hidden" name="recid" id="recid">
						</td>
					</tr>
					@if($data->categoryid==2)
					@if($data->requestid!=145)
					<tr>
						<td class="padding-8 bg-warning" @if($data->requestid!=145) colspan="3" @else colspan="4" @endif>
							Note*<br>In CSF, only one resume can be uploaded for a position. If a new resume is uploaded, it will replace the previously uploaded resume, and the latest resume details will be maintained in the system.
						</td>
					</tr>
					@endif
					@endif
					<tr>
						<td class="padding-8" style="width:250px;">
							<input type="text" name="name[]" placeholder="Enter name" autocomplete="off" class="selectbx full-wdth">
						</td>
						<td class="padding-8" style="width:250px;">
							  <input type="file" class="file-input resume" name="resume[]" accept=".pdf">
						</td>
						@if($data->categoryid!=2)
						<td class="padding-8">
							
							<button type="button" class="btn btn-info addBtn gridbtn" style="width:100px;">
								<i class="fa fa-plus"></i> ADD
							</button>						
							
						</td>
						@elseif($data->requestid==145)
						<td class="padding-8">
							
							<button type="button" class="btn btn-info addBtn gridbtn" style="width:100px;">
								<i class="fa fa-plus"></i> ADD
							</button>						
							
						</td>
						@endif
						<td class="padding-8" style="text-align:right;">
							<button type="button" class="btn btn-info clsBtn gridbtn" style="width:100px;">
								<i class="fa fa-remove"></i> CLOSE
							</button>
						</td>
					</tr>
				</table>
				<table class="mytable" border="1" style="text-transform:none!important">
					<tr>
						<td class="padding-8" style="text-align:right;">
						<button type="button" class="btn btn-info submitBtn gridbtn" style="width:100px;"><i class="fa fa-save"></i> SUBMIT</button>
						</td>
					</tr>
				</table>
				<div class="trmsg myheadbg padding-8" style="display:none; width:100%; text-align:center;"></div>
			</form>
		</div>
	</div>
</div>

<script src="{{ asset('panel/assets/js/jquery-2.1.4.min.js') }}"></script>
<script src="{{ asset('panel/assets/js/ace-elements.min.js') }}"></script>
<script src="{{ asset('panel/assets/js/ace.min.js') }}"></script>

<script>
	$(document).ready(function () {

		$('#contact_email').on('input', function () {
			$('#otp_email').val($(this).val());
		});

	});

    $('.replacePresentationFile').on('click', function () {
		let fileInput = $('input[name="ppt"]')[0];

		if (!fileInput || fileInput.files.length === 0) {
			bootbox.alert('Please select a presentation file before proceeding.');
			return;
		}		
		bootbox.confirm('Do you want to replace presentation file?',function(result){
			if(result)
			{
				$(".replacePresentationFile").prop("disabled","disabled");
				let form = $('#participate')[0];
				let formData = new FormData(form);

				$.ajax({
					url: '{{route("replace.presentationfile")}}',
					type: 'POST',
					data: formData,
					contentType: false,
					processData: false,
					headers: {
						'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') // CSRF token
					},
					success: function (response) {
						if(response.status==200)
						{
							bootbox.alert(response.message);
							setTimeout(function(){ window.location.reload();  },5000);
						}
						if(response.status==400)
						{
							alert(response.message);
						}
					},
					error: function (xhr) {
						$(".replacePresentationFile").prop("disabled","");
						if (xhr.responseJSON && xhr.responseJSON.errors) {
							var errors = xhr.responseJSON.errors;
							var allMessages = '';

							$.each(errors, function(field, messages) {
								$.each(messages, function(index, msg) {
									allMessages += msg + '<br>';
								});
							});
							bootbox.alert(allMessages);
						}			
					}
				});
			}
		});
    });

    $('.replaceSignedCopyOfEoi').on('click', function () {
		let fileInput = $('input[name="signedcopyofeoi"]')[0];

		if (!fileInput || fileInput.files.length === 0) {
			bootbox.alert('Please select a signed EoI file before proceeding.');
			return;
		}		
		bootbox.confirm('Are you sure you want to replace the signed EoI file?',function(result){
			if(result)
			{
				$(".replaceSignedCopyOfEoi").prop("disabled","disabled");
				let form = $('#participate')[0];
				let formData = new FormData(form);

				$.ajax({
					url: '{{route("replace.signedcopyofeoi")}}',
					type: 'POST',
					data: formData,
					contentType: false,
					processData: false,
					headers: {
						'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') // CSRF token
					},
					success: function (response) {
						if(response.status==200)
						{
							bootbox.alert(response.message,function(){
								window.location.reload();
							});
							
						}
						if(response.status==400)
						{
							alert(response.message);
						}
					},
					error: function (xhr) {
						$(".replaceSignedCopyOfEoi").prop("disabled","");
						if (xhr.responseJSON && xhr.responseJSON.errors) {
							var errors = xhr.responseJSON.errors;
							var allMessages = '';

							$.each(errors, function(field, messages) {
								$.each(messages, function(index, msg) {
									allMessages += msg + '<br>';
								});
							});
							bootbox.alert(allMessages);
						}			
					}
				});
			}
		});
    });


    $('.submitBtn').on('click', function () {
		bootbox.confirm('Do you want to upload selected resumes?',function(result){
			if(result)
			{
				$(".submitBtn").prop("disabled","disabled");
				let form = $('#resume')[0];
				let formData = new FormData(form);

				$.ajax({
					url: $('#resume').attr('action'),
					type: 'POST',
					data: formData,
					contentType: false,
					processData: false,
					headers: {
						'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') // CSRF token
					},
					success: function (response) {
						if(response.status==200)
						{
							$(".trmsg").css("display","");
							$(".trmsg").html(response.message);
							setTimeout(function(){ $(".trmsg").css("display","none"); $(".trmsg").html(""); window.location.reload();  },5000);
						}
						if(response.status==400)
						{
							alert(response.message);
						}
					},
					error: function (xhr) {
						$(".submitBtn").prop("disabled","");
						if (xhr.responseJSON && xhr.responseJSON.errors) {
							var errors = xhr.responseJSON.errors;
							var allMessages = '';

							$.each(errors, function(field, messages) {
								$.each(messages, function(index, msg) {
									allMessages += msg + '<br>';
								});
							});
							bootbox.alert(allMessages);
						}			
					}
				});
			}
		});
    });


$(document).ready(function () {

	
    $('.addBtn').on('click', function () {
        let newRow = `
        <tr class="datarow">
            <td class="padding-8">
                <input type="text" name="name[]" placeholder="Enter name" autocomplete="off" class="selectbx full-wdth">
            </td>
            <td class="padding-8">
                    <input type="file" class="file-input resume" name="resume[]" accept=".pdf">
            </td>
            <td class="padding-8">
                <button type="button" class="btn btn-info removeRow"><i class="fa fa-trash"></i> Remove</button>
            </td>
        </tr>`;
        
        $('.resumetable').append(newRow);
		$('.resumetable .resume').last().ace_file_input({
			no_file: 'No File ...',
			btn_choose: 'Resume',
			btn_change: 'Change',
			btn_name: 'fourthimage',
			thumbnail: false
		});
		
    });

    $('.mytable').on('click', '.removeRow', function () {
        $(this).closest('tr').remove();
    });		

    $('.clsBtn').on('click', function () {
		$("#myModal").modal("hide");

    });

});

//setTimeout(function() { UploadResume(1); },2000);

function UploadResume(recordid)
{
	$("#recid").val(recordid);
	$("#myModal").modal("show");
}


jQuery(function($) {	

	$('.resume').ace_file_input({
		no_file:'No File ...',
		btn_choose:'Resume',
		btn_change:'Change',
		btn_name:'fourthimage',
		thumbnail:false //| true | large
	});


	$('.ppt').ace_file_input({
		no_file:'No File ...',
		btn_choose:'Upload PPT*',
		btn_change:'Change',
		btn_name:'fourthimage',
		thumbnail:false //| true | large
	});
	$('.signedcopyofeoi').ace_file_input({
		no_file:'No File ...',
		btn_choose:'Signed EoI File*',
		btn_change:'Change',
		btn_name:'fourthimage',
		thumbnail:false //| true | large
	});

		
});

const participateBtn = document.getElementById("participateBtn");

if (participateBtn) {
    participateBtn.addEventListener("click", function (e) {

        const fileInput = document.querySelector('.ppt');
        const fileInput1 = document.querySelector('.signedcopyofeoi');
        const contact_name = document.getElementById("contact_name").value;
        const contact_number = document.getElementById("contact_number").value;
        const contact_email = document.getElementById("contact_email").value;

        @if($data->categoryid==2)
        if (!fileInput || !fileInput.value) {
            e.preventDefault();
            bootbox.alert("Please select a presentation file before submitting.");
            return false;
        }
        @endif

        if (!fileInput1 || !fileInput1.value) {
            e.preventDefault();
            bootbox.alert("Please select a Signed Copy of EoI file before submitting.");
            return false;
        }

        if (contact_name == '') {
            e.preventDefault();
            bootbox.alert("Please provide submitted by name.");
            return false;
        }

        if (contact_number == '') {
            e.preventDefault();
            bootbox.alert("Please provide contact number.");
            return false;
        }

        if (contact_email == '') {
            e.preventDefault();
            bootbox.alert("Please provide email address.");
            return false;
        }

    });
}


$(document).on('click', '#sendOtpBtn', function () {

    let email = $('#otp_email').val().trim();
    let floatid = $('#floatid').val().trim();
    let btn = $(this);

    if (!email) {
        $('#otpMessage').html('<span style="color:red;">Please enter your email.</span>');
        return;
    }

    btn.prop('disabled', true).text('Sending...');

    $.ajax({
        url: "{{ route('participation.otp.verification') }}",
        type: "POST",
        data: {
            email:email,
            floatid:floatid,
            _token: "{{ csrf_token() }}"
        },

        success: function (response) {

            if (response.success) {
                $('#otpMessage').html('<span style="color:green;">' + response.message + '</span>');
                $('#sendOtpBtn').remove();
                $('#otpSection').css('display', 'flex');
                $('#otp_email').prop('readonly', true);
                $('#otp').focus();
            }
        },

        error: function (xhr) {

            btn.prop('disabled', false).text('Send OTP');

            let message = 'Something went wrong.';

            if (xhr.responseJSON)
			{
                if(xhr.responseJSON.message)
				{
                    message = xhr.responseJSON.message;
                }
				
                if(xhr.responseJSON.errors)
				{
                    message = Object.values(xhr.responseJSON.errors).flat().join('<br>');
                }
            }

            $('#otpMessage').html('<span style="color:red;">' + message + '</span>');
        }
    });
});


$(document).on('click', '#verifyOtpBtn', function () {

    let email 	= 	$('#otp_email').val().trim();
    let floatid = 	$('#floatid').val().trim();
    let otp 	= 	$('#otp').val().trim();
    let btn 	= 	$(this);
	alert(email);
	alert(floatid);
    // Validate OTP
    if (!otp) {
        $('#otpMessage').html(
            '<span style="color:red;">Please enter the OTP.</span>'
        );
        $('#otp').focus();
        return;
    }

    if (!/^\d{6}$/.test(otp)) {
        $('#otpMessage').html('<span style="color:red;">Please enter a valid 6 digit OTP.</span>');
        $('#otp').focus();
        return;
    }

    btn.prop('disabled', true).text('Verifying...');

    $.ajax({
        url: "{{ route('verify.participation.otp') }}",
        type: "POST",

        data: {
            email: email,
            floatid: floatid,
            otp: otp,
            _token: "{{ csrf_token() }}"
        },

        success: function (response) {

            if (response.success) {

                $('#otpMessage').html(
                    '<span style="color:green;">' +
                    response.message +
                    '</span>'
                );

                $('#otpSection').remove();

                $('#otp_verified').val('1');

                $('#participateBtn').prop('disabled', false);
            }
        },

        error: function (xhr) {

            btn.prop('disabled', false).text('Verify');

            let message = 'Something went wrong.';

            if (xhr.responseJSON) {

                if (xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                }

                if (xhr.responseJSON.errors) {
                    message = Object.values(xhr.responseJSON.errors).flat().join('<br>');
                }
            }

            $('#otpMessage').html('<span style="color:red;">' + message + '</span>');

            $('#otp').focus();
        }
    });
});
</script>
<script src="{{ asset('panel/assets/js/master.js') }}"></script>
<script src="{{ asset('panel/assets/js/datatablePaginationWithFilter.js') }}"></script>
@endsection