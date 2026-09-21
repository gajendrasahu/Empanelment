@extends('admin.admin_master')
@section('admin')
@php
$t=0;
$preferred[1]	=	'Tier - 1';
$preferred[2]	=	'Tier - 2';
$preferred[3]	=	'Both';
@endphp



<div class="main-content">
	<div class="main-content-inner">

	
			<div class="page-content">

				<div class="ace-settings-container" id="ace-settings-container"></div>
				<div class="row">
					<div class="col-xs-12">

				<!-- PAGE CONTENT BEGINS -->
<div class="tabbable animated fadeInUp delay-02" style="padding:0px 0px!important;">
		<ul class="nav nav-tabs padding-0">
		@if(in_array(1,Session::get('actions')))
			<li class="active">
				<a data-toggle="tab" href="#home" class="form-label">
					<i class="green ace-icon fa fa-plus-circle bigger-120 datalist" style="vertical-align:text-top;"></i> EoI & Draft Details
				</a>
			</li>
		@endif
		</ul>

	
		<div class="tab-content no-border" style="border:1px solid #ddd; min-height:100px; padding:0px 10px;">
@if(in_array(1,Session::get('actions')))
<div id="home" class="tab-pane in active" style="padding:0px 10px!important;">
	<div class="row">
		<div class="row">
			<form name="frm" id="frm" action="{{route('add.pmcommittee')}}" method="post" enctype="multipart/form-data">
				@csrf
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
						<i class="fa fa-hand-o-right icon-animated-bell"></i> {{ Session::get('fail') }}
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
				<div class="form-group">

							<div class="content-detail">
								@if($data->isUpdateRequired>0)
								<div class="ui-card-detail">
									<div>
										<div class="title-detail">
											<i class="fa fa-warning"></i> Update Required
										</div>
										<p class="ui-text-block-detail">
										{!! $data->replywithremark !!}
										<br>
										<h5>Please update the EoI as requested by the admin and click <b>Confirm Button</b> when done.</h5>
									<a href="{{route('update.pmconfirm',Crypt::encrypt($data->requestid))}}" style="">
										<button type="button" class="btn btn-info padding-0-5">
											<i class="fa fa-thumbs-up"></i> Confirm
										</button>
									</a>
										<br>
										</p>
									</div>
									
									
								</div>
								@endif
							
								<div class="ui-card-detail">
									<div>
										<div class="title-detail">
											<i class="material-icons-outlined">topic</i> {{ $data->projecttitle }} 
										</div>
										<div class="details-detail lh-25">
											@if($data->eoistatus!=0)EoI Number: <b>{{ $data->eoinumber }}</b> | @endif Requested on : <b>{{ date('d\-m\-Y, h:i A',strtotime($data->creationdate)) }}</b>
										</div>
									</div>
									
									<a href="{{route('update.pmeoidetail',Crypt::encrypt($data->requestid))}}" style="position:absolute; bottom:5px; right:5px;">
										<button type="button" class="btn btn-info padding-0-5">
											<i class="fa fa-save"></i> Update (if required)
										</button>
									</a>
									
								</div>
								@if($data->issuername!='')
									@include('admin.viewpages.factsheet')
								@endif
								@include('admin.viewpages.objective_without_editor')
								<div class="section-block-detail">
									<div class="section-header-detail">
										<span class="material-icons">group_add</span> <!-- Hamburger menu icon -->
										&nbsp;<h2 class="section-title-detail">Team Requirement [Preferred Firm Tier : {{$preferred[$data->tier_choice]}}]</h2>
									</div>

									@foreach($html as $row)
									<div class="table-responsive mt-10">
									{!! $row !!}
									</div>
									@endforeach							

								</div>
								
								<div class="section-block-detail">
									<div class="section-header-detail">
										<span class="material-icons">attachment</span>
										<h2 class="section-title-detail">Attachments</h2>
									</div>

									<div class="grid-column-layout-detail cols-4">
										<div class="row padding-0-10">
											<div class="col-sm-4 attach">
												<span class="material-icons" style="color:#147E8B;">picture_as_pdf</span>
												<a href="{{ route('view.uploadedfile', Crypt::encrypt($data->authorizationletter)) }}" target="_blank">
													<span class="material-icons download-link">download_for_offline</span>
												</a>
												<div class="file-name-detail">Request letter from authorized signatory</div>
											</div>
											@if(!$attachments->isEmpty())
											@foreach($attachments as $attach)
											<div class="col-sm-4 attach">
												<span class="material-icons" style="color:#147E8B;">picture_as_pdf</span>
												<a href="{{ route('view.uploadedfile', Crypt::encrypt($attach->attachmentfile)) }}" target="_blank">
													<span class="material-icons download-link">download_for_offline</span>
												</a>
												<div class="file-name-detail">{{$attach->attachmenttitle}}</div>
											</div>
											@if($loop->iteration%4==0)
												<div class="col-sm-12">&nbsp;</div>
											@endif
											@endforeach
											@endif
										</div>
									</div>
								</div>
								
								@include('admin.viewpages.evaluation_without_editor')

								<div class="section-block-detail">
									<div class="section-header-detail">
										<span class="material-icons">groups</span>
										<h2 class="section-title-detail">Committee Member</h2>
									</div>
									<div class="grid-layout-detail cols-4" style="padding:0px 0px; gap:0px;">
										<table style="text-transform:none!important; width:100px; margin-top:10px;">
											<tr>
												<td>
												<input type="hidden" name="requestid" id="requestid" value="{{Crypt::encrypt($data->requestid)}}">												
													<select class="select2" name="memberid" id="memberid" required onKeyPress="return OnKeyPress(this, event)"  data-placeholder="Member Name" style="width:250px!important;" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}">
														<option value=""></option>
														@foreach($members as $itm)
														<option value="{{ $itm->memberid }}" {{ intval(old('memberid'))===$itm->memberid ? 'selected' : '' }}>{{ $itm->name }} [{{ strtoupper($itm->mobilenumber) }}]</option>
														@endforeach
													</select>
												</td>
												<td>
													<button type="button" style="text-transform:none!important; margin-left:5px;" class="btn btn-info myfrmbtn submitCommitteeBtn" tabindex="{{$t++}}"><i class="fa fa-plus"></i> Add Committee Member</button>
												</td>
											</tr>
										</table>

										<table class="mytable" border="1" style="text-transform: none!important; margin-top:10px;">
											<tr class="myheadbg"><td class="mytd form-label" colspan="7" style="padding:5px!important;"><b>Committee Member</b></td></tr>
											<tr class="myheadbg">
												<td class="padding-5 form-label center" style="width:30px;">S.No.</td>
												<td class="padding-5 form-label">Name</td>
												<td class="padding-5 form-label">Designation</td>
												<td class="padding-5 form-label">Department</td>
												<td class="padding-5 form-label">Mobile Number</td>
												<td class="padding-5 form-label">Email</td>
												<td class="padding-5 form-label">From</td>
											</tr>
											@if($committee->count()==0)
											<tr><td colspan="7" class="padding-5 center">--No Committee Member Added--</td></tr>
											@else
											<tbody class="committeedata">
											@foreach($committee as $comm)
											<tr>
												<td class="padding-5 center">{{$loop->iteration}}</td>
												<td class="padding-5">{{$comm->name}}</td>
												<td class="padding-5">{{$comm->designation}}</td>
												<td class="padding-5">{{$comm->department}}</td>
												<td class="padding-5">{{$comm->mobilenumber}}</td>
												<td class="padding-5">{{$comm->email}}</td>
												<td class="padding-5">{{$comm->usertype}}</td>
											</tr>
											@endforeach
											</tbody>
											@endif
										</table>

									</div>

								</div>

								
								<div class="section-block-detail">
									<div class="section-header-detail">
										<span class="material-icons">done_all</span>
										&nbsp;<h2 class="section-title-detail">Confirmation of Acceptance</h2>
									</div>			
									@if($committee->count()>2)
									<table class="mytable" border="1" style="text-transform: none!important; margin-top:20px;">
										<tr style="text-transform: none!important;">
											<td class="mytd font-14" style="vertical-align:middle!important; padding:15px 10px!important;">
											@if($data->isupdated==0)
												@if($data->eoistatus==1)
												@if($data->isUpdateRequired==0)
												<input type="checkbox" name="confirmation" id="confirmation" style="vertical-align:top!important;" onclick="ConfirmAcceptance()">
												You have successfully acknowledged and accepted the Expression of Interest (EoI) draft along with the associated Terms and Conditions.
												@else
												<h5><i class="fa fa-hand-o-right"></i> Cannot proceed: Department has not updated the EoI yet.</h5>
												@endif
												@else
												<h5><i class="fa fa-hand-o-right"></i> Please wait while CHiPS reviews or draft the EoI.</h5>
												@endif
											@else
												Your updated Expression of Interest (EoI) has been successfully submitted. Please note that CHiPS has not yet reviewed the revised details.
											@endif
											</td>
										</tr>
									</table>
									@else
									<table class="mytable" border="1" style="text-transform: none!important; margin-top:20px;">
										<tr style="text-transform: none!important;">
											<td class="mytd font-14" style="vertical-align:middle!important; padding:15px 10px!important;">
												Before proceeding with confirmation, a minimum of 3 committee members is required. Please add committee members.
											</td>
										</tr>
									</table>

									@endif
									
								</div>

							</div>


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



<div class="modal fade" id="otpverification" aria-hidden="true" data-backdrop="static">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content otp-modal-content">
      <form name="otpfrm" id="otpfrm" action="" method="post">
      <div class="modal-header" style="z-index: 1101 !important;">
        <h5 class="modal-title w-100 text-center">OTP Verification</h5>
        
      </div>

      <div class="modal-body text-center" style="z-index: 1101 !important;">
	  
        <div class="otp-container mx-auto" style="z-index: 1101 !important;">
         
          <!-- OTP Inputs -->
          <div class="otp-input-group" style="z-index:9!important;">
			<input type="hidden" name="reqid" id="reqid" value="{{Crypt::encrypt($data->requestid)}}">
            <input type="text" id="first" name="first" class="form-control text-center otp-input" maxlength="1" onkeypress="return OnKeyPress(this, event)" tabindex="{{$t++}}" autocomplete="off" />
            <input type="text" id="second" name="second" class="form-control text-center otp-input" maxlength="1" onkeypress="return OnKeyPress(this, event)" tabindex="{{$t++}}" autocomplete="off" />
            <input type="text" id="third" name="third" class="form-control text-center otp-input" maxlength="1" onkeypress="return OnKeyPress(this, event)" tabindex="{{$t++}}" autocomplete="off" />
            <input type="text" id="fourth" name="fourth" class="form-control text-center otp-input" maxlength="1" onkeypress="return OnKeyPress(this, event)" tabindex="{{$t++}}" autocomplete="off" />
            <input type="text" id="fifth" name="fifth" class="form-control text-center otp-input" maxlength="1" onkeypress="return OnKeyPress(this, event)" tabindex="{{$t++}}" autocomplete="off" />
            <input type="text" id="sixth" name="sixth" class="form-control text-center otp-input" maxlength="1" onkeypress="return OnKeyPress(this, event)" tabindex="{{$t++}}" autocomplete="off" />
          </div>
			
          <!-- Button -->
          <button type="button" class="btn btn-info otp-button" id="verifyOtp" tabindex="{{$t++}}">
            Verify OTP
          </button>

          <!-- Message -->
          <div class="msg text-danger mt-2">&nbsp;</div>

          <!-- Resend -->
          <p class="text-center mt-2 mb-0">
            <small>Didn't receive the code? <a id="resendOtp" onclick="ConfirmAcceptance()">Resend</a></small>
          </p>
        </div>
      </div>
		</form>
    </div>
  </div>
</div>



<script src="{{ asset('panel/assets/js/jquery-2.1.4.min.js') }}"></script>
<script src="{{ asset('panel/assets/tinymce/tinymce.min.js') }}"></script>
<script src="{{ asset('panel/assets/js/ace-elements.min.js') }}"></script>
<script src="{{ asset('panel/assets/js/ace.min.js') }}"></script>

<script>
    tinymce.init({
        selector: '#draftdetail',
        promotion: false,
        branding: false,
        height: 350,
        autoresize_max_height: 490,
        toolbar_mode: 'floating',
        plugins: 'code advlist autolink lists charmap preview table searchreplace save',
        toolbar: 'undo redo | formatselect | bold italic underline strikethrough | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link | searchreplace',
        setup: function (editor) {
                // Add custom CSS to the editor content area
                editor.on('init', function () {
                    var contentArea = editor.getContainer().querySelector('.tox-edit-area__iframe');
                    if (contentArea) {
                        contentArea.style.overflow = 'auto';
                    }
                });
            }
    });

    tinymce.init({
        selector: '#termsandcondition',
        promotion: false,
        branding: false,
        height: 350,
        autoresize_max_height: 490,
        toolbar_mode: 'floating',
        plugins: 'code advlist autolink lists charmap preview table searchreplace save',
        toolbar: 'undo redo | formatselect | bold italic underline strikethrough | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link | searchreplace',
        setup: function (editor) {
                // Add custom CSS to the editor content area
                editor.on('init', function () {
                    var contentArea = editor.getContainer().querySelector('.tox-edit-area__iframe');
                    if (contentArea) {
                        contentArea.style.overflow = 'auto';
                    }
                });
            }
    });


$(document).ready(function() {

$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});	
	$('#frmSubmit').on('click', function(e) {
		e.preventDefault();
		bootbox.confirm('DO YOU WANT TO SUBMIT AND SEND DRAFT TO DEPARTMENT?',function(result){
			if(result)
			{
				document.forms['frm'].submit();
			}
		});
	});

	
});	

function ConfirmAcceptance()
{
	if ($('#confirmation').is(':checked'))
	{
		bootbox.confirm({
			message: 'By proceeding, you acknowledge that you have read and agreed to the terms outlined, and confirm your request in the Expression of Interest (EoI) process.<br><br><b>Do you want to proceed with OTP verification?</b>',
			buttons: {
				confirm: {
					label: 'Yes',
					className: 'btn-primary'
				},
				cancel: {
					label: 'No',
					className: 'btn-secondary'
				}
			},
			callback: function(result) {
				if(result)
				{
					var formData = new FormData(document.getElementById('frm'));
					formData.append('_token', '{{ csrf_token() }}');
					$.ajax({
						url: '{{route("generate_pmconfirmation_otp")}}',
						type: 'POST',
						data: formData,
						processData: false,
						contentType: false,
						success: function (response)
						{
							if(response.status===200)
							{
								$("#otpverification").modal("show");
								setTimeout(function() { $("#first").focus(); },1000);
							}
							if(response.status===400)
							{
								bootbox.alert(response.message);
							}
						},
						error: function (xhr) {
							if (xhr.responseJSON && xhr.responseJSON.errors) {
								var errors = xhr.responseJSON.errors;
								var allMessages = '';

								$.each(errors, function(field, messages) {
									$.each(messages, function(index, msg) {
										allMessages += '<i class="fa fa-hand-o-right"></i> '+ msg + '<br>';
									});
								});
								bootbox.alert(allMessages);
								$('#confirmation').prop('checked', false);
							}			
						}
					});
				}
				else
				{
					$('#confirmation').prop('checked', false);
				}
			}
		});
	}
}

$(document).ready(function () {
  var inputs = $('.otp-input');

  inputs.on('keyup', function (e) {
    var key = e.keyCode || e.which;
    var $this = $(this);

    if ($this.val().length === 1 && key !== 8 && key !== 37) {
      // Move to next input
      var index = inputs.index(this);
      if (index !== -1 && index < inputs.length - 1) {
        inputs.eq(index + 1).focus();
      }
    } else if ((key === 8 || key === 37)) {
      // Move to previous input
      var index = inputs.index(this);
      if (index > 0) {
        inputs.eq(index - 1).focus();
      }
    }
  });

  // Optional: handle right arrow key
  inputs.on('keydown', function (e) {
    var key = e.keyCode || e.which;
    if (key === 39) {
      var index = inputs.index(this);
      if (index < inputs.length - 1) {
        inputs.eq(index + 1).focus();
      }
    }
  });
});


$('#verifyOtp').click(function (e) {
	$("#verifyOtp").prop("disabled","disabled");
	e.preventDefault();
	var allFilled = true;
	$('.otp-input').each(function() {
		if ($.trim($(this).val()) === '') {
			allFilled = false;
			return false;
		}
	});		
	if(!allFilled)
	{
		$("#verifyOtp").prop("disabled","");
		$("#generateOtp").prop("disabled","");
		$(".msg").html('Please enter OTP.');
		return false;
	}
	var formData = new FormData(document.getElementById('otpfrm'));
	formData.append('_token', '{{ csrf_token() }}');
	$.ajax({
		url: '{{route("verify_pmconfirmation_otp")}}',
		type: 'POST',
		data: formData,
		processData: false,
		contentType: false,
		success: function (response)
		{
			if(response.redirect_url)
			{
				$("#verifyOtp").prop("disabled","disabled");
				$("#generateOtp").prop("disabled","disabled");
				$(".msg").html('<i class="fa fa-check-circle"></i> VERIFIED');
				setTimeout(function() { window.location.href = response.redirect_url; },2000);
			}
			if(response.status===400)
			{
				$("#verifyOtp").prop("disabled","");
				$(".msg").html(response.message);
			}
		},
		error: function (xhr) {
			if (xhr.responseJSON && xhr.responseJSON.errors) {
				var errors = xhr.responseJSON.errors;
				var allMessages = '';

				$.each(errors, function(field, messages) {
					$.each(messages, function(index, msg) {
						allMessages += msg + '<br>';
					});
				});
				$(".msg").html(allMessages);
				//bootbox.alert(allMessages);
				$("#verifyOtp").prop("disabled","");
			}			
		}
	});
});


jQuery(function($) {	

	$('#authletter').ace_file_input({
		no_file:'No File ...',
		btn_choose:'Authorization Letter*',
		btn_change:'Change',
		btn_name:'fourthimage',
		thumbnail:false //| true | large
	});


});



$('.submitCommitteeBtn').on('click', function () {
	bootbox.confirm('Do you want to add committe member?',function(result){
		if(result)
		{
			$("#submitCommitteeBtn").prop("disabled","disabled");
			let form = $('#frm')[0];
			let formData = new FormData(form);

			$.ajax({
				url: $('#frm').attr('action'),
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
						$('#memberid').val('').trigger("chosen:updated");
						setTimeout(function(){ location.reload();  },2000);
					}
				},
				error: function (xhr) {
					if (xhr.responseJSON && xhr.responseJSON.errors) {
						var errors = xhr.responseJSON.errors;
						var allMessages = '';

						$.each(errors, function(field, messages) {
							$.each(messages, function(index, msg) {
								allMessages += msg + '<br>';
							});
						});
						$("#submitCommitteeBtn").prop("disabled","");
						bootbox.alert(allMessages);
					}			
				}
			});
		}
	});
});

</script>


<script src="{{ asset('panel/assets/js/datatablePaginationWithFilter.js') }}"></script>
<script>
document.querySelectorAll('.format-indian').forEach(function(el) {
    el.innerText = formatIndianNumber(el.dataset.value);
});

</script>

@endsection