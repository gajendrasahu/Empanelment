@extends('admin.admin_master')
@section('admin')
@php
$t=0;
$preferred[1]	=	'Tier - I';
$preferred[2]	=	'Tier - II';
$preferred[3]	=	'Both';

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
				<a data-toggle="tab" href="#home" class="form-label">
					<i class="green ace-icon fa fa-thumbs-up bigger-120 datalist" style="vertical-align:text-top;"></i> EoI Approval
				</a>
			</li>
		@endif
		</ul>

	
		<div class="tab-content no-border" style="border:1px solid #ddd; min-height:100px; padding:0px 10px;">
@if(in_array(1,Session::get('actions')))
<div id="home" class="tab-pane in active" style="padding:0px 10px!important;">
	<div class="row">
		<div class="row">
				
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
				
				@csrf
				<div class="form-group">
					<div class="content-detail">
					
						<div class="ui-card-detail">
							<div>
								<div class="title-detail">
									<i class="material-icons-outlined">topic</i> {{ $data->projecttitle }}
								</div>
								<div class="details-detail lh-30">
									<b>Department / Project Manager : </b>{{ $data->departmentname}}<br>
									EoI Number: <b>{{$data->eoinumber}}</b> | Requested on : <b>{{ date('d\-m\-Y, h:i A',strtotime($data->creationdate)) }}</b> | Project Duration : <b>{{ $data->projectduration }} Months</b><br>
									
								</div>
							</div>
						</div>
						@if(!$old_letter->isEmpty())
						<div class="section-block-detail">
							<div class="section-header-detail">
								<span class="material-icons">attachment</span>
								<h2 class="section-title-detail">Previous Letters</h2>
							</div>

							<div class="grid-column-layout-detail cols-4">
								<div class="row padding-0-10">
									@php
									$files=0;
									@endphp
									@foreach($old_letter as $attach)
									@php $files++; @endphp
									<div class="col-sm-4 attach">
										<span class="material-icons" style="color:#147E8B;">picture_as_pdf</span>
										<a href="{{ route('view.uploadedfile', Crypt::encrypt($attach->auth_letter)) }}" target="_blank">
											<span class="material-icons download-link">download_for_offline</span>
										</a>
										<div class="file-name-detail">Updated on {{date('d\-m\-Y h:i A',strtotime($attach->updated_on))}}</div>
									</div>
									@if($files%4==0)
										<div class="col-sm-12">&nbsp;</div>
									@endif
									@endforeach
								</div>
							</div>
						
						</div>
							
						@endif
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
									@php
									$files=1;
									@endphp
									@foreach($attachments as $attach)
									@php $files++; @endphp
									<div class="col-sm-4 attach">
										<span class="material-icons" style="color:#147E8B;">picture_as_pdf</span>
										<a href="{{ route('view.uploadedfile', Crypt::encrypt($attach->attachmentfile)) }}" target="_blank">
											<span class="material-icons download-link">download_for_offline</span>
										</a>
										<div class="file-name-detail">{{$attach->attachmenttitle}}</div>
									</div>
									@if($files%4==0)
										<div class="col-sm-12">&nbsp;</div>
									@endif
									@endforeach
									@endif
								</div>
							</div>
							
						</div>

						@php
							$page_indexing = "";
							$page_indexing = old('page_indexing') ?: ($data->page_indexing ?: $page_indexing);
						@endphp
						@if($page_indexing!='')
						<div class="section-block-detail">
							<div class="section-header-detail">
								<span class="material-icons">compare</span> <!-- Hamburger menu icon -->
								&nbsp;<h2 class="section-title-detail">Table of Contents (Optional)</h2>
							</div>
							<table style="width:100%; border:none;">
								<tr>
									<td style="border:none;">{!! $page_indexing !!}</td>
								</tr>
							</table>							
						</div>
						@endif
						
						@include('admin.viewpages.factsheet')
						<!-- INCLUDE OBJECTIVE ABOUT SCOPE CONTENT -->
						@include('admin.viewpages.objective_about_scope_approval')
						
						<div class="section-block-detail">
							<div class="section-header-detail">
								<span class="material-icons">group_add</span> <!-- Hamburger menu icon -->
								&nbsp;<h2 class="section-title-detail">Team Requirement [Preferred Firm Tier : {{$preferred[$data->tier_choice]}}]</h2>
							</div>
							<div class="table-responsive">
							@if($tier1_html!='')
								{!!$tier1_html!!}
							@endif
							</div>

							<div class="table-responsive">
							@if($tier2_html!='')
								{!!$tier2_html!!}
							@endif
							</div>

						</div>

						@if($committee->count()>0)
						<div class="section-block-detail">
							<div class="section-header-detail">
								<span class="material-icons">groups</span>
								<h2 class="section-title-detail">&nbsp;Committee Member</h2>
							</div>
							
							<div class="grid-layout-detail cols-4" style="padding:0px 0px; gap:0px;">
								<table class="mytable" border="1" style="text-transform: none!important; margin-top:0px;">
									<tr class="myheadbg">
										<td class="mytd form-label padding-5" colspan="7" style="padding:5px!important;">
											<b>Committee Member</b>
										</td>
									</tr>
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
										
										<td class="padding-5">{{$comm->remark}}</td>
										<td class="padding-5">{{$comm->usertype}}</td>
									</tr>
									@endforeach
									</tbody>
									@endif
								</table>

							</div>
							
						</div>
						@endif
						
						@include('admin.viewpages.evaluation_without_editor')


						<div class="section-block-detail">
							<div class="section-header-detail">
								<span class="material-icons">playlist_add_check</span>
								<h2 class="section-title-detail">&nbsp;EoI Approval Log</h2>
							</div>
							
							<div class="grid-layout-detail cols-4" style="padding:0px 0px; gap:0px;">
								<table class="mytable pd-8 width-full" border="1">
									<thead>
									<tr class="myheadbg">
										<td class="center width-30 form-label">S.No.</td>
										<td class="form-label" nowrap>Project Title</td>
										<td class="center form-label" nowrap>Request Date</td>
										<td class="form-label">From</td>
										<td class="form-label">To</td>
										<td>Remark</td>
										<td>Approval Date & Time</td>
									</tr>
									</thead>
									<tbody>
									@foreach($requests as $request)
									<tr>
										<td class="center v-top">{{$loop->iteration}}</td>
										<td class="width-200 v-top">{{$request->projecttitle}}</td>
										<td class="center v-top" nowrap>@if($request->requestdate){{date('d\-m\-Y, h:i A',strtotime($request->requestdate))}}@endif</td>
										<td class="v-top">{{$request->fromname}}</td>
										<td class="v-top">{{$request->toname}}</td>
										<td class="width-200 v-top">{{$request->remark}}</td>
										<td class="center v-top" nowrap>@if($request->closingdate){{date('d\-m\-Y, h:i A',strtotime($request->closingdate))}}@endif</td>
									</tr>
									@endforeach
									@if($requests->count()==0)
									<tr><td colspan="7" class="center">--No record Found--</td></tr>
									@endif
									</tbody>
								</table>
							</div>
							
						</div>

						@if($data->approvalClosed==0)
						@if($lastRecord->touserid==session('userId'))
						<div class="section-block-detail">
							<div class="section-header-detail">
								<span class="material-icons">playlist_add_check</span>
								<h2 class="section-title-detail">&nbsp;EoI Approval</h2>
							</div>
							
							<div class="grid-layout-detail" style="padding:0px 0px; gap:0px;">
								<form name="sendtoapproval" id="sendtoapproval" method="post" action="">
								@csrf
									<table class="mytable pd-8 width-800">
										<tr>
											<td colspan="2">
												
											<div class="alert alert-block alert-info">
												<i class="fa fa-info-circle"></i> To proceed without forwarding this EoI approval, click “Approve & Close.”
											</div>
												
											</td>
										</tr>
										<tr>
											<td class="v-top" nowrap>Select Name</td>
											<td class="width-200 v-top">
												<input type="hidden" name="request_id" id="request_id" value="{{Crypt::encrypt($data->requestid)}}">
												<input type="hidden" name="form_token" id="form_token" value="{{$token}}">
												<select name="user_id" id="user_id" class="width-300">
													<option value="">--Select Name--</option>
													@foreach($approvalUsers as $usrs)
													<option value="{{$usrs->userid}}">{{$usrs->name}}</option>
													@endforeach
												</select>
											</td>
										</tr>
										<tr>
											<td class="v-top width-200" nowrap>Remark</td>
											<td class="v-top">
												<textarea name="approval_remark" id="approval_remark" rows="5" class="width-full" placeholder="Remark..."></textarea>
											</td>
										</tr>
										<tr>
											<td class=""></td>
											<td class="width-full">
												<button type="button" style="float:left;" class="btn btn-info myfrmbtn width-200 apprAndClsBtn">
													<i class="fa fa-thumbs-up"></i> Approve & Close
												</button>													
												<button type="button" style="float:right;" class="btn btn-info myfrmbtn width-200 sendApprovalBtn">
													<i class="fa fa-mail-forward"></i> Send
												</button>													
											</td>
											<td></td>
										</tr>
									</table>
									<br><br>
								</form>
							</div>
						</div>
						@endif
						@endif



				

<div class="col-sm-12">&nbsp;</div>
<div class="col-sm-12">&nbsp;</div>
						
						
						
						
					</div>
				

<div class="col-sm-12">&nbsp;</div>
				</div>		
			
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


<div class="modal fade" id="otpapprovalModal" data-backdrop="static" data-keyboard="false">
  <div class="modal-dialog modal-xl" style="width:40%; margin-top:100px;">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" style="float:left;">OTP Verification</h5>
        <!-- Proper close button for BS3 -->
        <button type="button" class="close" style="float:right;" data-dismiss="modal">&times;</button>
      </div>
      <div class="modal-body" id="otpapprovalData" style="max-height:600px; overflow:auto;">
		<form name="otpverification" id="otpverification" action="#" method="post" onsubmit="return false;">	  
		@csrf
		<table class="pd-8 width-full">
			<tr class="otptr">
				<td class="v-middle" nowrap>Enter the OTP</td>
				<td class="v-top">
					<input type="hidden" name="req_id" id="req_id" value="">
					<input type="text" name="approval_otp" id="approval_otp" class="form-control" placeholder="OTP">
				</td>
				<td>
					<button type="button" class="btn btn-info myfrmbtn width-100 approveAndClsBtn">
						<i class="fa fa-thumbs-up"></i> Verify
					</button>													
				</td>
			</tr>
		</table>
		</form>		
	  </div>
	</form>
    </div>
  </div>
</div>


<script src="{{ asset('panel/assets/js/jquery-2.1.4.min.js') }}"></script>
<script src="{{ asset('panel/assets/js/master.js') }}"></script>
<script src="{{ asset('panel/assets/js/datatablePaginationWithFilter.js') }}"></script>

<script>
document.querySelectorAll('.format-indian').forEach(function(el) {
    el.innerText = formatIndianNumber(el.dataset.value);
});


$('.approveAndClsBtn').on('click', function(e) {
	
	e.preventDefault();
	var form = $('#otpverification')[0];
	var formData = new FormData(form);
	$.ajax({
		url: '{{route("sendto.approve")}}',
		method: 'POST',
		data: formData,
		processData: false,
		contentType: false,
		success: function(response) {
			if(response.status===200)
			{
				bootbox.alert(response.message);
				setTimeout(function(){ window.location.reload(); },3000);
				return false;
			}
			else
			{
				bootbox.alert(response.message);
				return false;
			}
		},
		error: function(xhr) {
			let errors = xhr.responseJSON?.errors;
			let message = '';
			$.each(errors, function(key, val) {
				message += '<i class="fa fa-hand-o-right"></i> '+ val + '<br>';
			});
			bootbox.alert(message);
		}
	});
	
});


$('.sendApprovalBtn').on('click', function(e) {
	
	bootbox.confirm('Do you confirm this action?',function(result){
		if(result)
		{
			e.preventDefault();
			var form = $('#sendtoapproval')[0];
			var formData = new FormData(form);
			$.ajax({
				url: '{{route("sendto.approval")}}',
				method: 'POST',
				data: formData,
				processData: false,
				contentType: false,
				success: function(response) {
					if(response.status===200)
					{
						$("#user_id").val("");
						$("#approval_remark").val("");
						bootbox.alert(response.message);
						setTimeout(function(){ window.location.reload(); },3000);
						return false;
					}
					else
					{
						bootbox.alert('<span style="color:red;">'+response.message+'</span>');
						return false;
					}
				},
				error: function(xhr) {
					let errors = xhr.responseJSON?.errors;
					let message = '';
					$.each(errors, function(key, val) {
						message += '<i class="fa fa-hand-o-right"></i> '+ val + '<br>';
					});
					bootbox.alert(message);
				}
			});
		}
	});
});

$('.apprAndClsBtn').on('click', function(e) {
	
	bootbox.confirm('Are you sure you want to approve this EoI for publication to firms?<br><br>Upon confirmation, a One-Time Password (OTP) will be sent to your registered email address.<br><br><b>Please confirm to continue.</b>',function(result){
		if(result)
		{
			e.preventDefault();
			var form = $('#sendtoapproval')[0];
			var formData = new FormData(form);
			$.ajax({
				url: '{{route("send.approvalotp")}}',
				method: 'POST',
				data: formData,
				processData: false,
				contentType: false,
				success: function(response) {
					if(response.status===200)
					{
						$("#req_id").val($("#request_id").val());
						$('#otpapprovalModal').modal('show');
					}
					else
					{
						bootbox.alert(response.message);
						return false;
					}
				},
				error: function(xhr) {
					let errors = xhr.responseJSON?.errors;
					let message = '';
					$.each(errors, function(key, val) {
						message += '<i class="fa fa-hand-o-right"></i> '+ val + '<br>';
					});
					bootbox.alert(message);
				}
			});
			
			/*
			var form = $('#sendtoapproval')[0];
			var formData = new FormData(form);
			$.ajax({
				url: '{{route("sendto.approve")}}',
				method: 'POST',
				data: formData,
				processData: false,
				contentType: false,
				success: function(response) {
					if(response.status===200)
					{
						$("#user_id").val("");
						$("#approval_remark").val("");
						bootbox.alert(response.message);
						setTimeout(function(){ window.location.reload(); },3000);
						return false;
					}
					else
					{
						bootbox.alert(response.message);
						return false;
					}
				},
				error: function(xhr) {
					let errors = xhr.responseJSON?.errors;
					let message = '';
					$.each(errors, function(key, val) {
						message += '<i class="fa fa-hand-o-right"></i> '+ val + '<br>';
					});
					bootbox.alert(message);
				}
			});
			*/
		}
	});
});



</script>
@endsection