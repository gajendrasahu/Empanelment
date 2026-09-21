@extends('admin.admin_master')
@section('admin')
@php
$t=0;
$intDate='';
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
		
			<li class="active">
				<a data-toggle="tab" href="#home" class="form-label font-14">
					<i class="green ace-icon fa fa-list bigger-120 datalist" style="vertical-align:text-top;"></i>Work Order
					
				</a>
			</li>
		
		</ul>

	
		<div class="tab-content no-border" style="border:1px solid #ddd; min-height:100px; padding:0px 10px;">

<div id="home" class="tab-pane in active" style="padding:0px 10px!important;">
	<div class="row">
			<form name="generateworkorder" id="generateworkorder" action="{{ route('generate.workorder',Crypt::encrypt($data->requestid))}}" method="post" enctype="multipart/form-data">
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
				@csrf
				<div class="form-group">
					<div class="content-detail">
						<div class="ui-card-detail">
							<div>
								<div class="title-detail">
									<i class="material-icons-outlined">topic</i> {{ $data->projecttitle }}
								</div>
								<div class="details-detail lh-25">
									<b>Department / Project Manager :</b> {{ $data->departmentname }}<br>
									<b>{{ $data->eoinumber }}</b> | Requested on : <b>{{ date('d\-m\-Y, h:i A',strtotime($data->creationdate)) }}</b> | Project Duration : <b>{{ ucwords(strtolower($data->projectduration)) }} Months</b><br>
									
								</div>
							</div>
						</div>
						
						@include('admin.viewpages.factsheet')

						<div class="section-block-detail">
							<div class="section-header-detail" style="display:flex; gap:5px; border-bottom:1px solid #fff;">
								<i class="fa fa-users"></i> <h5>Committee Member</h5>
							</div>
							<div class="table-responsive">
								<table class="mytable pd-8" border="1" style="text-transform:none!important">
									<tr class="myheadbg">
										<td class="center padding-10 form-label" style="width:35px;">S.No.</td>
										<td class="form-label">Name</td>
										<td class="form-label">Mobile Number</td>
										<td class="form-label">Email</td>
										<td class="form-label">Department</td>
										<td class="form-label">Designation</td>
									</tr>
									@if($members->count()!=0)
									@php $isupdated=0; @endphp
									@foreach($members as $member)
									<tr>
										<td class="center">{{$loop->iteration}}</td>
										<td>{{$member->name}}</td>
										<td>{{$member->mobilenumber}}</td>
										<td>{{$member->email}}</td>
										<td>{{$member->department}}</td>
										<td>{{$member->designation}}</td>
									</tr>
									@endforeach
									@else
									<tr><td colspan="6" class="center">--No Record Found--</td></tr>
									@endif
								</table>
							</div>
						</div>

						
						
						<div class="section-block-detail">
							<div class="section-header-detail" style="display:flex; gap:5px; border-bottom:1px solid #fff;">
								<i class="fa fa-calendar"></i> <h5>Request {{$data->name}} to update the interview date and time.</h5>
							</div>
							<div class="table-responsive">
								<table class="mytable pd-8" border="1" style="text-transform:none!important">
									<tr class="myheadbg">
										<td class="center padding-10 form-label" style="width:35px;">S.No.</td>
										<td class="form-label">Requested by</td>
										<td class="form-label">Remark</td>
										<td class="form-label" nowrap>Requested on</td>										
										<td class="form-label" nowrap>Updated on</td>
										<td class="form-label width-150" nowrap>Interview Date & Time</td>
									</tr>
									@if($interviews->count()!=0)
									@php $isupdated=0; @endphp
									@foreach($interviews as $interview)
									<tr>
										<td class="center">{{$loop->iteration}}</td>
										<td>{{$interview->requestedby}}</td>
										<td>{{$interview->remark}}</td>
										<td nowrap>@if($interview->requested_on){{date('d\-m\-Y, h:i A',strtotime($interview->requested_on))}}@endif</td>
										<td nowrap>@if($interview->updated_on){{date('d\-m\-Y, h:i A',strtotime($interview->updated_on))}}@endif</td>
										<td nowrap>@if($interview->updatedValue){{date('d\-m\-Y, h:i A',strtotime($interview->updatedValue))}}@endif</td>
									</tr>
									@php $isupdated=$interview->isUpdated; @endphp
									@endforeach
									@if($isupdated!=0)
									<tr>
										<td colspan="5">
											<input type="text" name="remark" id="remark" class="form-control" placeholder="Enter remarks" autocomplete="off">
											<span class="text-danger">@error('remark') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
										</td>
										<td style="text-align:right;">
										@permission('updateinterview.daterequest')
											<button type="button" class="btn btn-info gridbtn" style="width:150px;" onclick="sendRequest('{{Crypt::encrypt($data->requestid)}}')">Send Request</button>
										@endpermission
										</td>
									</tr>
									@else
									<tr>
										<td colspan="6" style="text-align:right;">
											<button type="button" class="btn btn-info gridbtn" style="width:150px;" disabled>Request Sent</button>
										</td>
									</tr>
									@endif
									@else
									<tr><td colspan="6" class="center">--No Record Found--</td></tr>
									<tr>
										<td colspan="5">
											<input type="text" name="remark" id="remark" class="form-control" placeholder="Enter remarks" autocomplete="off">
											<span class="text-danger">@error('remark') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
										</td>
										<td style="text-align:right;">
											@permission('updateinterview.daterequest')
											@if($data->iscancelled==0)
											@if($data->isordered==0)
											<button type="button" class="btn btn-info gridbtn" style="width:150px;" onclick="sendRequest('{{Crypt::encrypt($data->requestid)}}')">Send Request</button>
											@else
											<button type="button" class="btn btn-info gridbtn" style="width:150px;" disabled>Send Request</button>
											@endif
											@endif
											@endpermission
										</td>
									</tr>
									@endif
								</table>
							</div>
						</div>

						
						<div class="section-block-detail">
							<div class="section-header-detail">
								<span class="material-icons">playlist_add_check</span> <!-- Hamburger menu icon -->
								&nbsp;<h2 class="section-title-detail">Participated Vendors</h2>
							</div>
							<div class="table-responsive">
								<table class="mytable" border="1" style="text-transform:none!important">
								<!--
									<tr class="myheadbg">
										<td colspan="6" class="padding-10 form-label">
											<input type="text" class="form-control interview_date padding-0" placeholder="dd-mm-YYYY" name="interview_date" id="interview_date" style="width:150px; float:left;">	

											<button type="button" class="btn btn-info padding-3 setInterBtn" onclick="SetInterviewDate1('{{route('update.interviewdate')}}','{{Crypt::encrypt($data->requestid)}}')" style="width:150px; float:left; margin-left:5px;">
												<i class="fa fa-calendar"></i> Set Interview Date
											</button><br>
											
										</td>
									</tr>
									-->
									<tr class="myheadbg">
										<td class="center padding-10 form-label" style="width:35px;">S.No.</td>
										<td class="padding-10 form-label">Firm Name & Submitted by</td>
										<td class="padding-10 form-label center" style="width:150px;">Participation Date</td>
										<td class="padding-10 form-label center" style="width:150px;">Interview Date & Time</td>
										<td class="padding-10 form-label" style="width:250px;">Interview Link</td>
										<td class="padding-10 form-label" style="width:100px;"></td>
									</tr>
									@php $r=0; @endphp
									@foreach($vendors as $vendor)
									@php $r=$r+1; @endphp
									<tr>
										<td class="center padding-10 v-top">{{$r}}
										
										</td>
										<td class="mytdleft padding-10 v-top width-300">
											{{$vendor->companyname}} [{{$vendor->tiername}}]
											@if($vendor->contact_person)
												<br><br>
												<span class="text-small"><i class="fa fa-user"></i> {{$vendor->contact_person}}</span><br>
												<span class="text-small"><i class="fa fa-mobile font-20"></i> {{$vendor->contact_number}}</span><br>
												@if($vendor->contact_email)
												<span class="text-small"><i class="fa fa-envelope font-10"></i> {{$vendor->contact_email}}</span>
												@endif
											@endif
											@if($vendor->contact_person_2)
												<br><br>
												<span class="text-small"><i class="fa fa-user"></i> {{$vendor->contact_person_2}}</span><br>
												<span class="text-small"><i class="fa fa-mobile font-20"></i> {{$vendor->contact_number_2}}</span><br>
												@if($vendor->contact_email_2)
												<span class="text-small"><i class="fa fa-envelope font-10"></i> {{$vendor->contact_email_2}}</span>
												@endif
											@endif
										</td>
										
										<td class="center padding-10 v-top" nowrap>
											@if($vendor->participation_date_time)
												{{date('d\-m\-Y, h:i A',strtotime($vendor->participation_date_time))}}
											@endif
										</td>
										<td class="center padding-10 v-top">
											<input type="text" autocomplete="off" placeholder="dd-mm-YYYY" class="form-control interview_date padding-0" name="interviewdate{{$vendor->participationid}}" id="interviewdate{{$vendor->participationid}}" @if($vendor->interviewdate && $vendor->interviewdate!='2010-00-00 00:00:00') value="{{ date('d\-m\-Y h:i A',strtotime($vendor->interviewdate)) }}" @endif style="width:150px;">											
										</td>
										<td class="center padding-10 v-top">									
											<input type="text" class="form-control" name="interviewlink{{$vendor->participationid}}" id="interviewlink{{$vendor->participationid}}" placeholder="Enter interview link here" @if($vendor->interviewlink) value="{{ $vendor->interviewlink }}" @endif class="padding-0" style="width:100%;">
										</td>
										<td class="padding-10 v-top">
											
											
											@if($vendor->interviewdate!='')
											@if($data->iscancelled==0)
											@if($data->isordered==0)
											@permission('update.interviewlink')
											<button type="button" class="btn btn-info gridbtn" onclick="SetInterviewLink('{{route('update.interviewlink')}}',{{$vendor->participationid}})" style="width:100%;">
												<i class="fa fa-link"></i> Send Link
											</button>
											@endpermission
											<br>
											@endif
											@endif
											@endif
											@permission('view.participations')
											<a href="{{ route('view.participations',Crypt::encrypt($vendor->participationid))}}">
												<button type="button" class="btn btn-info gridbtn" style="margin-top:2px; width:100%;">
													<i class="fa fa-hand-o-right"></i> Resume
												</button>
											</a>
											@endpermission
											
										</td>
									</tr>
									@php
									$intDate='';
									if($vendor->interviewdate!='')
									$intDate	=	date('d\-m\-Y h:i A',strtotime($vendor->interviewdate));
									
									@endphp
									@endforeach
									@if($data->isDeadlinePassed==0)
									<tr>
										<td colspan="6" class="padding-5 font-14">
										<i class="fa fa-hand-o-right"></i> Please note that the proposal submission deadline is still active. Kindly wait until it has passed before proceeding with order preparation.
										</td>
									</tr>									
									@else
									@if($data->iscancelled==0)
@permission('workorder.confirmation')										
<tr class="myheadbg"><td class="padding-10" colspan="6"><b>Mandatory Pre-Work Order Confirmations</b></td></tr>
<tr>
	<td class="padding-10" colspan="6">
		<label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
		  <input type="checkbox" name="isInterviewDone" id="isInterviewDone" class="precheck" data-key="isInterviewDone" data-url="{{route('workorder.confirmation',Crypt::encrypt($data->requestid))}}" onclick="handlePrecheck(this)" @if($data->isInterviewDone) checked disabled @endif style="margin: 0;">
		  <span style="display: flex; align-items: center;">Is Interview Done?</span>
		</label>
		<label class="mt-10" style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
		  <input type="checkbox" name="isMomReceived" id="isMomReceived" class="precheck" data-key="isMomReceived" data-url="{{route('workorder.confirmation',Crypt::encrypt($data->requestid))}}" onclick="handlePrecheck(this)" style="margin: 0;" @if($data->isMomReceived) checked disabled @endif>
		  <span style="display: flex; align-items: center;">Is Evaluation Sheet Received?</span>
		</label>
		<label class="mt-10" style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
		  <input type="checkbox" name="isOrderFileReceived" id="isOrderFileReceived" class="precheck" data-key="isOrderFileReceived" data-url="{{route('workorder.confirmation',Crypt::encrypt($data->requestid))}}" onclick="handlePrecheck(this)" style="margin: 0;" @if($data->isOrderFileReceived) checked disabled @endif>
		  <span style="display: flex; align-items: center;">Is Order File Received?</span>
		</label>
	</td>
</tr>
@endpermission
										@if($data->isInterviewDone && $data->isMomReceived && $data->isOrderFileReceived)
										@if($data->isordered==0)
										<tr>
											<td colspan="6" class="padding-10">
											@permission('create.finalworkorder')
												<button type="button" class="btn btn-info gridbtn no-hover animated flipInX delay-02 prepareorderBtn" onclick="prepareWorkOrder('{{route('prepare.workorder')}}','{{Crypt::encrypt($data->requestid)}}')" style="width:auto; max-width:400px;">
													Create Work Order
												</button>
											@endpermission
										@if($data->iscommittee==0)
										<a href="{{ route('prepare.committee',Crypt::encrypt($data->requestid))}}" style="float:right;">
											<button type="button" class="btn btn-info gridbtn" style="width:250px;">
												<i class="fa fa-plus"></i> Add Committee Member (Optional)
											</button>
										</a>
										@endif

											</td>
										</tr>
										@else
									<tr>
										<td colspan="6" class="padding-5 font-16">
											<i class="fa fa-warning"></i> Please note that a work order has already been generated for this EoI.
										</td>
									</tr>									
										@endif
										@endif
									@else
									<tr>
										<td colspan="6" class="padding-5 font-16">
											<i class="fa fa-warning"></i> Please note that the EoI has been cancelled and the process is now closed.
										</td>
									</tr>									
									@endif
									@endif
									
								</table>
							</div>
						</div>
					</div>
				
					<div class="col-sm-12">&nbsp;</div>
				</div>		
			</form>
	</div>
</div>



</div>
</div>


						
				</div><!-- /.col -->
			</div><!-- /.row -->
		</div><!-- /.page-content -->
	
		
	</div>
</div>
<script src="{{ asset('panel/assets/js/jquery-2.1.4.min.js') }}"></script>
<script src="{{ asset('panel/assets/js/ace-elements.min.js') }}"></script>
<script src="{{ asset('panel/assets/js/ace.min.js') }}"></script>


<script>
@if($intDate!='' && $intDate!=NULL)
	document.getElementById("interview_date").value='{{$intDate}}';
	$(".setInterBtn").prop("disabled","disabled");
@endif
function prepareWorkOrder(rl,requestid)
{
	/*
	if($('input[name="vendorid"]:checked').length==0)
	{
		bootbox.alert("Please select vendor name.");
		return false;
	}
	*/
	bootbox.confirm('<br><i class="fa fa-angle-double-right"></i> Have you reviewed all resumes submitted by all vendors?<br><i class="fa fa-angle-double-right"></i> You are about to prepare a work order for the selected vendor.<br><i class="fa fa-angle-double-right"></i> Is the MoM prepared?<br><i class="fa fa-angle-double-right"></i> Has the Marking Sheet been prepared?<br><br><b>Do you wish to continue?</b>',function(result){
		if(result)
		{
			//var vendorid			= 	$('input[name="vendorid"]:checked').val();
			$.ajax({
				url: rl,
				type: 'POST',
				data: {'requestid':requestid},
				headers: {
					'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') // CSRF token
				},
				success: function (response) {
					if(response.status==200)
					{
						//$("#generateworkorder").submit();
						window.location.href = response.redirect_url;
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
						bootbox.alert(allMessages);
						setTimeout(function() { $(".no-skin").css("padding-right",""); },5000);
					}			
				}
			});
		}
	});	
}


jQuery(function($) {	

	$('.attachment').ace_file_input({
		no_file:'No File ...',
		btn_choose:'Resume',
		btn_change:'Change',
		btn_name:'fourthimage',
		thumbnail:false //| true | large
	});

});


function SetInterviewLink(rl,participationid)
{
	bootbox.confirm('Would you like to set the interview link?<br><br>Upon confirmation, all committee members will be notified regarding the interview date and meeting link.',function(result){
		if(result)
		{
			var interviewlink	=	$("#interviewlink"+participationid).val();
			var interviewdate	=	$("#interviewdate"+participationid).val();
			$.ajax({
				url: rl,
				type: 'POST',
				data: {'participationid':participationid,'interviewlink':interviewlink,'interviewdate':interviewdate},
				headers: {
					'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') // CSRF token
				},
				success: function (response) {
					if(response.status==200)
					{
						bootbox.alert({
							message: "Interview date and link have been updated successfully.<br><br>Notification emails have been sent to all committee members.",
							callback: function () {
								setTimeout(function() { location.reload(); },2000);
							}
						});
						setTimeout(function() { location.reload(); },10000);
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
						bootbox.alert(allMessages);
						setTimeout(function() { $(".no-skin").css("padding-right",""); },5000);
					}			
				}
			});
		}
	});	
}

function SetInterviewDate1(rl,requestid)
{
	var interviewdate = $("#interview_date").val();
	if(interviewdate=='')
	{
		bootbox.alert('Please enter interview date.');
		return false;
	}
	bootbox.confirm('Do you want to set interview date?',function(result){
		if(result)
		{
			$.ajax({
				url: rl,
				type: 'POST',
				data: {'requestid':requestid,'interviewdate':interviewdate},
				headers: {
					'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') // CSRF token
				},
				success: function (response) {
					if(response.status==200)
					{
						bootbox.alert({
							message: "Interview date updated successfully",
							callback: function () {
								setTimeout(function() { location.reload(); },2000);
							}
						});
						setTimeout(function() { location.reload(); },2000);
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
						bootbox.alert(allMessages);
						setTimeout(function() { $(".no-skin").css("padding-right",""); },5000);
					}			
				}
			});
		}
	});	
}

function EnableButton()
{
	$(".prepareorderBtn").prop("disabled","");
	$(".prepareorderBtn").html('Proceed to Work Order <i class="fa fa-arrow-right"></i>');
}

function parseDMY(dateStr) {
	const parts = dateStr.split("-");
	const day = parseInt(parts[0], 10);
	const month = parseInt(parts[1], 10) - 1;
	const year = parseInt(parts[2], 10);
	return new Date(year, month, day);
}


function sendRequest(requestid)
{
	var remark	=	$("#remark").val();
	if(!remark)
	{
		bootbox.alert("Please enter a remark.");
		return false;
	}
	bootbox.confirm('Do you confirm this action?.',function(result){
		if(result)
		{
			$.ajax({
				url: '{{route('updateinterview.daterequest')}}',
				type: 'POST',
				data: {'requestid':requestid,'remark':remark},
				headers: {
					'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') // CSRF token
				},
				success: function (response) {
					if(response.status==200)
					{
						bootbox.alert({
							message: response.message,
							callback: function () {
								setTimeout(function() { location.reload(); },1000);
							}
						});
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
						bootbox.alert(allMessages);
						setTimeout(function() { $(".no-skin").css("padding-right",""); },5000);
					}			
				}
			});
		}
	});	
}



function handlePrecheck(element) {
	bootbox.confirm('Are you sure you want to update this confirmation?<br><br>This action will be saved and cannot be undone.',function(result){
		if(result)
		{
			const key = element.dataset.key;
			const url = element.dataset.url;
			const value = element.checked;

			fetch(url, {
				method: 'POST',
				headers: {
					'Content-Type': 'application/json',
					'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
				},
				body: JSON.stringify({ key, value })
			})
			.then(async (response) => {
				if (response.status === 200) {
					location.reload();
				} else {
					const msg = await response.text();
					element.checked = !value;
					bootbox.alert(msg || 'Something went wrong');
				}
			})
			.catch(() => {
				element.checked = !value;
				bootbox.alert('Server error');
			});			
		}
	});
}
</script>


<script src="{{ asset('panel/assets/js/datatablePaginationWithFilter.js') }}"></script>
@endsection