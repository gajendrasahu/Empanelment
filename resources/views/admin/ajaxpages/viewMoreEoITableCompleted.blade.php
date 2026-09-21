<div class="modal-header" style="padding:5px 15px;">
	<h5 class="modal-title" style="float:left;"><span class="font-bold">{{$data->eoinumber}}</span></h5>

	<div class="eoi-actions" style="float:right; cursor:pointer;">
		<a data-bs-dismiss="modal" onclick="CloseThis()" class="eoi-btn action-a">
			<i class="fa fa-remove"></i> Close
		</a>
	</div>
	
</div>
<div class="modal-body" style="padding:10px 20px;">
<style>
.timeline-step
{
	border-right:1px solid #f1f1f1;
	padding:10px 8px;
	background:linear-gradient(to bottom,#ffffff,#fafafa);
	border-radius:12px;

	box-shadow:
		0 2px 4px rgba(0,0,0,0.04),
		0 8px 20px rgba(0,0,0,0.06);

	transition:all 0.25s ease-in-out;
}

</style>
	<div class="row">
	@php
	$now = \Carbon\Carbon::now();
	$percentage	=	0;
	$flag=0;
	if($data->eoistatus >= 0)
	{
		$flag++;
		$percentage = 10;
	}

	if($data->senton && $flag==1)
	{
		$flag++;
		$percentage = 20;
	}

	if($data->acceptancedatetime && $flag==2)
	{
		$flag++;
		$percentage = 30;
	}

	if($flag== 3 && \Carbon\Carbon::parse($data->releasedate)->lte($now))
	{
		$flag++;
		$percentage = 40;
	}
	if($flag == 4 && \Carbon\Carbon::parse($data->prebidlastdate)->lte($now))
	{
		$flag++;
		$percentage = 50;
	}
	if($flag == 5 && \Carbon\Carbon::parse($data->deadlinedate)->lte($now))
	{
		$flag++;
		$percentage = 60;
	}
	if($flag == 6 && $data->isInterviewDone==1)
	{
		$flag++;
		$percentage = 70;
	}
	if($flag == 7 && $data->isMomReceived==1)
	{
		$flag++;
		$percentage = 80;
	}
	if($flag == 8 && $data->isOrderFileReceived==1)
	{
		$flag++;
		$percentage = 90;
	}
	if($flag == 9 && $data->isordered==1)
	{
		$flag++;
		$percentage = 100;
	}
	@endphp
		<div class="col-sm-12 padding-10" style="border-radius:10px;">
			<div class="eoi-timeline" style="100vw!important; gap:0px!important;">
				<div class="timeline-step @if($data->creationdate) is-done @endif">
					<div class="timeline-icon">
						<i class="fa @if($data->creationdate) fa-check @endif timeline-check-icon"></i>
					</div>
					<div class="timeline-title">Requested on</div>
					<div class="timeline-date">
						{{ date('d\-m\-Y',strtotime($data->creationdate)) }}
					</div>
				</div>
				<div class="timeline-step @if($data->senton && $flag>=1) is-done @else is-pending @endif">
					<div class="timeline-icon">
						<i class="fa @if($data->senton && $flag>=1) fa-check @else fa-clock-o @endif timeline-check-icon"></i>
					</div>
					<div class="timeline-title">Draft Date</div>
					<div class="timeline-date">
						@if($data->senton){{ date('d\-m\-Y',strtotime($data->senton)) }} @else - @endif
					</div>
				</div>

				<div class="timeline-step @if($flag>=2 && $data->acceptancedatetime) is-done @else is-pending @endif">
					<div class="timeline-icon">
						<i class="fa @if($flag>=2 && $data->acceptancedatetime) fa-check @else fa-clock-o @endif timeline-check-icon"></i>
					</div>
					<div class="timeline-title">Approved on</div>
					<div class="timeline-date">
						@if($data->acceptancedatetime){{ date('d\-m\-Y',strtotime($data->acceptancedatetime)) }}@else - @endif
					</div>
				</div>

				<div class="timeline-step @if($flag>=3 && \Carbon\Carbon::parse($data->releasedate)->lte($now)) is-done @else is-pending @endif">
					<div class="timeline-icon">
						<i class="fa @if($flag>=3 && \Carbon\Carbon::parse($data->releasedate)->lte($now)) fa-check @else fa-clock-o @endif timeline-check-icon"></i>
					</div>
					<div class="timeline-title">Release Date</div>
					<div class="timeline-date">
						@if($data->releasedate){{ date('d\-m\-Y',strtotime($data->releasedate)) }} @else TBD @endif
					</div>
				</div>

				<div class="timeline-step @if($flag>=4 && \Carbon\Carbon::parse($data->prebidlastdate)->lte($now)) is-done @else is-pending @endif">
					<div class="timeline-icon">
						<i class="fa @if($flag>=4 && \Carbon\Carbon::parse($data->prebidlastdate)->lte($now)) fa-check @else fa-clock-o @endif timeline-check-icon"></i>
					</div>
					<div class="timeline-title">Query Deadline</div>
					<div class="timeline-date">
						@if($data->prebidlastdate){{ date('d\-m\-Y',strtotime($data->prebidlastdate)) }} @else TBD @endif
					</div>
				</div>

				<div class="timeline-step @if($flag>=5 && \Carbon\Carbon::parse($data->deadlinedate)->lte($now)) is-done @else is-pending @endif">
					<div class="timeline-icon">
						<i class="fa @if($flag>=5 && \Carbon\Carbon::parse($data->deadlinedate)->lte($now)) fa-check @else fa-clock-o @endif timeline-check-icon"></i>
					</div>
					<div class="timeline-title">Submission Deadline</div>
					<div class="timeline-date">
						@if($data->deadlinedate) {{ date('d\-m\-Y',strtotime($data->deadlinedate)) }}<br>{{ date('h:i A',strtotime($data->deadlinedate)) }} @else TBD @endif
					</div>
				</div>

				<div class="timeline-step @if($flag>=6 && $data->isInterviewDone==1) is-done @else is-pending @endif">
					<div class="timeline-icon">
						<i class="fa @if($flag>=6 && $data->isInterviewDone==1) fa-check @else fa-clock-o @endif timeline-check-icon"></i>
					</div>
					<div class="timeline-title">Interview Done?</div>
					<div class="timeline-date">
						@if($flag>=6 && $data->isInterviewDone==1) Yes @else - @endif
					</div>
				</div>

				<div class="timeline-step @if($flag>=7 && $data->isMomReceived==1) is-done @else is-pending @endif">
					<div class="timeline-icon">
						<i class="fa @if($flag>=7 && $data->isMomReceived==1) fa-check @else fa-clock-o @endif timeline-check-icon"></i>
					</div>
					<div class="timeline-title">MoM Received?</div>
					<div class="timeline-date">@if($flag>=7 && $data->isMomReceived==1) Yes @else - @endif</div>
				</div>

				<div class="timeline-step @if($flag>=8 && $data->isOrderFileReceived==1) is-done @else is-pending @endif">
					<div class="timeline-icon">
						<i class="fa @if($flag>=8 && $data->isOrderFileReceived==1) fa-check @else fa-clock-o @endif timeline-check-icon"></i>
					</div>
					<div class="timeline-title">Order Received?</div>
					<div class="timeline-date">@if($flag>=8 && $data->isOrderFileReceived==1) Yes @else - @endif</div>
				</div>

				<div class="timeline-step @if($flag>=9 && $data->isordered==1) is-done @else is-pending @endif">
					<div class="timeline-icon">
						<i class="fa @if($flag>=9 && $data->isordered==1) fa-check @else fa-clock-o @endif timeline-check-icon"></i>
					</div>
					<div class="timeline-title">Order Date</div>
					<div class="timeline-date">@if($flag>=9 && $data->isordered==1) {{ date('d\-m\-Y',strtotime($data->orderdate)) }} @else - @endif</div>
				</div>

			</div>
		</div>
		<div class="col-sm-12">
			<div style="display:flex; flex-wrap:wrap; gap:2px;">
				<div class="eoi-actions">
					<a href="{{route('communication.message', Crypt::encrypt($data->requestid))}}" class="eoi-btn action-a">
						<i class="fa fa-envelope"></i> Send Message
					</a>
				</div>
				<div class="eoi-actions">
					<a href="{{route('view.uploadedfile', Crypt::encrypt($data->authorizationletter))}}" target="_blank" class="eoi-btn action-a">
						<i class="fa fa-hand-o-right"></i> View File
					</a>
				</div>
				@if($data->eoistatus==0)
				<div class="eoi-actions">
					<a href="{{route('prepare.draft', Crypt::encrypt($data->requestid))}}" class="eoi-btn action-a">
						<i class="fa @if($data->isupdated)==1) fa-refresh @else fa-edit @endif"></i> @if($data->isupdated)==1) View Update @else Draft EoI @endif
					</a>
				</div>
				@endif
				@if($data->eoistatus>=1)
				<div class="eoi-actions">
					<a href="{{route('eoipreview', Crypt::encrypt($data->requestid))}}" target="_blank" class="eoi-btn action-a">
						<i class="fa fa-hand-o-right"></i> Preview
					</a>
				</div>
				@endif
				@if($data->eoistatus>=2)
				<div class="eoi-actions">
					<a href="{{route('prepare.draft', Crypt::encrypt($data->requestid))}}" class="eoi-btn action-a">
						<i class="fa fa-edit"></i> Edit EoI
					</a>
				</div>
				<div class="eoi-actions">
					<a href="{{route('update.eoidates', Crypt::encrypt($data->requestid))}}" class="eoi-btn action-a">
						<i class="fa fa-calendar"></i> Update Date
					</a>
				</div>
				@endif
				@if($data->eoistatus==2)
				<div class="eoi-actions">
					<a href="{{route('prepare.float', Crypt::encrypt($data->requestid))}}" class="eoi-btn action-a">
						<i class="fa fa-bullhorn"></i> Publish EoI
					</a>
				</div>
				@endif
				@if($data->eoistatus == 3 && date('Y\-m\-d',strtotime($data->deadlinedate))<$now && $data->isParticipated==0)
				<div class="eoi-actions">
					<a href="{{route('extend.eoidate', Crypt::encrypt($data->requestid))}}" class="eoi-btn action-a">
						<i class="fa fa-calendar"></i> Extend Date
					</a>
				</div>
				@endif
				@if($data->isinprebid)
				<div class="eoi-actions">
					<a href="{{route('prebidenquiry.list', Crypt::encrypt($data->requestid))}}" class="eoi-btn action-a">
						<i class="fa fa-question"></i> Pre-bid Enquiry
					</a>
				</div>
				@endif
				
				@if($data->eoistatus==4)
					@if($data->isordered == 0)
					<div class="eoi-actions">
						<a href="{{route('prepare.committee', Crypt::encrypt($data->requestid))}}" class="eoi-btn action-a">
							<i class="fa fa-plus"></i> Add Committee Member
						</a>
					</div>
					<div class="eoi-actions">
						<a href="{{route('view.pptresumes', Crypt::encrypt($data->requestid))}}" class="eoi-btn action-a">
							<i class="fa fa-users"></i> Vendors
						</a>
					</div>
					@else
						@if($data->isdeployed==1)
						<div class="eoi-actions">
							<a href="{{route('generate.workorder', Crypt::encrypt($data->requestid))}}" class="eoi-btn action-a">
								<i class="fa fa-plus"></i> Add Order
							</a>
						</div>
						@endif
					@endif
				@endif
			</div>			
		</div>		
		<div class="col-sm-12"></div>
		<div class="col-sm-3 padding-10" style="height:500px; max-height:500px; overflow-y:scroll;">
		@if($old_letter->count()>0)
			<label style="display:block; padding:0px 4px; width:100%; margin-bottom:8px; background-color:#FAFAFA !important;">Previous Letters</label>
			<div style="width:100%; padding:5px;" class="lh-25">
			@foreach($old_letter as $attach)
			<a href="{{ route('view.uploadedfile',Crypt::encrypt($attach->auth_letter)) }}" target="_blank" style="text-decoration:none;">
			<i class="fa fa-file-pdf-o"></i> Updated on {{date('d\-m\-Y h:i A',strtotime($attach->updated_on))}}
			</a><br>
			@endforeach
			</div>
		@endif
			<label style="display:block; padding:0px 4px; width:100%; margin-bottom:8px; background-color:#FAFAFA !important;">Uploaded Letter</label>
			<div style="width:100%; padding:5px;" class="lh-25">
				<a href="{{ route('view.uploadedfile', Crypt::encrypt($data->authorizationletter)) }}" target="_blank" style="text-decoration:none;">
					<i class="fa fa-file-pdf-o"></i> Request letter from authorized signatory
				</a>
			</div>
		@if($attachments->count()>0)
			<label style="display:block; padding:0px 4px; width:100%; margin-bottom:8px; background-color:#FAFAFA !important;">Other Attachments</label>
			<div style="width:100%; padding:5px;" class="lh-25">
			@foreach($attachments as $attach)
			<a href="{{ route('view.uploadedfile',Crypt::encrypt($attach->attachmentfile)) }}" target="_blank" style="text-decoration:none;">
			<i class="fa fa-file-pdf-o"></i> {{$attach->attachmenttitle}}
			</a>
			@endforeach
			</div>
		@endif
		@if($data->eoi_file)
			<label style="display:block; padding:0px 4px; width:100%; margin-bottom:8px; background-color:#FAFAFA !important;">Published EoI & Notesheet</label>
			<div style="width:100%; padding:5px;" class="lh-25">
				<a href="{{ route('view.uploadedfile',Crypt::encrypt($data->eoi_file)) }}" target="_blank" style="text-decoration:none;">
					<i class="fa fa-file-pdf-o"></i> Release Date {{date('d\-m\-Y',strtotime($data->releasedate))}}
				</a>
				@if($data->notesheet_file)
				<br>
				<a href="{{ route('view.uploadedfile',Crypt::encrypt($data->notesheet_file)) }}" target="_blank" style="text-decoration:none;">
					<i class="fa fa-file-pdf-o"></i> Notesheet File
				</a>
				@endif
			</div>
		@endif
		@if($data->corrigendum_file)
			<label style="display:block; padding:0px 4px; width:100%; margin-bottom:8px; background-color:#FAFAFA !important;">Corrigendum File</label>
			<div style="width:100%; padding:5px;" class="lh-25">
				<a href="{{ route('view.uploadedfile',Crypt::encrypt($data->corrigendum_file)) }}" target="_blank" style="text-decoration:none;">
					<i class="fa fa-file-pdf-o"></i> Corrigendum Date : {{date('d\-m\-Y',strtotime($data->corrigendum_date))}}
				</a>
			</div>
		@endif
		@if($published->count()>0)
			<label style="display:block; padding:0px 4px; width:100%; margin-bottom:8px; background-color:#FAFAFA !important;">Published to Firms </label>
			<div style="width:100%; padding:5px;" class="lh-25">
			@foreach($published as $firm)
			<i class="fa fa-angle-double-right"></i> {{$firm->companyname}}<br>
			@endforeach
			</div>			
		@endif
		@if($participated->count()>0)
			<label style="display:block; padding:0px 4px; width:100%; margin-bottom:8px; background-color:#FAFAFA !important;">Participated Firms </label>
			<div style="width:100%; padding:5px;" class="lh-25">
			@foreach($participated as $firm)
			<i class="fa fa-angle-double-right"></i> {{$firm->companyname}}<br>
			@endforeach
			</div>			
		@endif
		</div>
		<div class="col-sm-9 padding-10" style="height:500px; max-height:500px; overflow-y:scroll;">
			<div class="ui-card-detail" style="margin-top:-5px;">
				<div>
					<div class="title-detail">
						<i class="material-icons-outlined">topic</i> {{ $data->projecttitle }}
					</div>
					<div class="details-detail lh-30">
						<b>Department / Project Manager : </b>{{ $data->departmentname}}<br>
						 Requested on : <b>{{ date('d\-m\-Y, h:i A',strtotime($data->creationdate)) }}</b> | Project Duration : <b>{{ ucwords(strtolower($data->projectduration)) }} Months</b><br>
						
					</div>
				</div>
			</div>
			<div class="section-block-detail">
				<div class="section-header-detail">
					<span class="material-icons">compare</span> <!-- Hamburger menu icon -->
					&nbsp;<h2 class="section-title-detail">Table of Contents (Optional)</h2>
				</div>
				<table style="width:100%; border:none;">
					<tr>
						<td style="border:none;">{!! $data->page_indexing !!}
						</td>
					</tr>
				</table>							
			</div>
			<div class="section-block-detail">
				<table style="width:100%; border:none;">
					<tr>
						<td style="border:none;" class="lh-25">{!! $data->chips_objective !!}</td>
					</tr>
				</table>							
			</div>
			<div class="section-block-detail">
				<div class="section-header-detail">
					<span class="material-icons">playlist_add_check</span> <!-- Hamburger menu icon -->
					&nbsp;<h2 class="section-title-detail">Fact Sheet</h2>
				</div>
				<div class="grid-layout-detail cols-2">
					<div class="ui-item-detail">
						<label>EoI Reference Number</label>
						{{$data->eoinumber}}
					</div>

					<div class="ui-item-detail">
						<label>Name of Issuer</label>
						Chhattisgarh Infotech Promotion Society (CHiPS)
					</div>
					<div class="ui-item-detail">
						<label>Name of Engagement*</label>
						<b>{{$data->engagementname}}</b>
					</div>
					<div class="ui-item-detail">
						<label>Release Date of EoI By CHiPS</label>
						@if($data->releasedate)<b>{{date('d-m-Y',strtotime($data->releasedate))}}</b>@else TBD @endif
					</div>
					<div class="ui-item-detail">
						<label>Last Date of Pre-bid Query</label>
						@if($data->prebidlastdate)<b>{{date('d-m-Y',strtotime($data->prebidlastdate))}}</b>@else TBD @endif
					</div>
					<div class="ui-item-detail">
						<label>Last Date for Submission of Proposals</label>
						@if($data->deadlinedate)<b>{{date('d-m-Y',strtotime($data->deadlinedate))}}</b>@else TBD @endif
					</div>
					<div class="ui-item-detail">
						<label>Tentative Date of Presentation and Interview</label>
						@if($data->interviewdate)<b>{{date('d-m-Y',strtotime($data->interviewdate))}}</b>@else TBD @endif
					</div>
					<div class="ui-item-detail">
						<label>Place of presentations and Interviews</label>
						{{$data->interview_place}}
					</div>

					<div class="ui-item-detail">
						<label>Method of Selection</label>
						{{$data->selection_method}}
					</div>
					
					<div class="ui-item-detail" style="grid-column: span 2;">
						<label>Address for Communication</label>
						Chief Executive Officer&#10;Chhattisgarh Infotech Promotion Society (CHiPS)&#10;State Data Centre Building, Near Police Control Room, Civil Lines, Raipur, Chhattisgarh-492001&#10;Tel : 91771-404158&#10;Email : ceochips@nic.in, empl.chips@cgchips.in
					</div>
				</div>
				
			</div>
			<div class="section-block-detail">
				<div class="section-header-detail">
					<span class="material-icons">playlist_add_check</span> <!-- Hamburger menu icon -->
					&nbsp;<h2 class="section-title-detail">Project Objective</h2>
				</div>
				<table style="width:100%; border:none;">
					<tr>
						<td style="border:none;" class="lh-25">{!! $data->projectobjective !!}</td>
					</tr>
				</table>
			</div>
			<div class="section-block-detail">
				<div class="section-header-detail">
					<span class="material-icons">playlist_add_check</span> <!-- Hamburger menu icon -->
					&nbsp;<h2 class="section-title-detail">Scope of Work</h2>
				</div>
				<table style="width:100%; border:none;">
					<tr>
						<td style="border:none;" class="lh-25">{!! $data->scopeofwork !!}</td>
					</tr>
				</table>
			</div>
			<div class="section-block-detail">
				<div class="section-header-detail">
					<span class="material-icons">group_add</span> <!-- Hamburger menu icon -->
					&nbsp;<h2 class="section-title-detail">
					
					Team Requirement</h2>
				</div>
				<div class="table-responsive mt-10">
				@if($data->categoryid==2)
				<table class="mytable pd-8" border="1" style="text-transform: none!important;">
					<thead>
					<tr class="">
						<td class="center" style="width:50px;">S.No.</td>
						<td nowrap>Sector & Position</td>
						<td class="width-300">Experience <i class="fa fa-info-circle" title="As per empanelment"></i></td>
						<td style="width:130px;" nowrap>Deployment Type</td>
						<td class="text-right" nowrap>Duration</td>
					</tr>
					</thead>
					<tbody>
					@foreach($resources as $rec)					
					<tr class="">
						<td rowspan="3" nowrap class="center v-top">{{$loop->iteration}}</td>
						<td nowrap>{{ucwords(strtolower($rec->sectorname))}}<br>{{$rec->consultantposition}}</td>
						<td>{{$rec->experience}}</td>
						<td nowrap>{{$rec->employmenttype}}</td>
						<td nowrap class="text-right">{{$rec->duration}} Months</td>
					</tr>
					<tr><td colspan="5"><b>Qualification</b> : @if($rec->qualification!='') {!! preg_replace('/ style="[^"]*"/i', '', $rec->qualification) !!} @endif</td></tr>
					
					
					<tr><td colspan="5"><b>Remark</b> : @if($rec->remark!='') {!! preg_replace('/ style="[^"]*"/i', '', $rec->remark) !!} @endif</td></tr>
					
					
					@endforeach
				
					</tbody>
				</table>
				@endif
				@if($data->categoryid==1)
				<table class="mytable pd-8" border="1" style="text-transform: none!important;">
					<thead>
					<tr class="">
						<td class="center" style="width:50px;">S.No.</td>
						<td nowrap>Position</td>
						<td nowrap>Experience</td>
						<td nowrap>Experience Level</td>
					</tr>
					</thead>
					<tbody>
					@foreach($resources as $rec)					
					<tr class="font-12">
						<td nowrap class="center v-top" rowspan="3">{{$loop->iteration}}</td>
						<td nowrap>{!!$rec->role!!}</td>
						<td nowrap>{{$rec->experience}}</td>						
						<td nowrap>Level-{{$rec->experiencelevel}}</td>
					</tr>
					<tr>
						<td colspan="4">Qualification : @if($rec->qualification) {!! $rec->qualification !!} @endif</td>
					</tr>
					<tr>
						<td colspan="4">Remark : @if($rec->remark) {!! $rec->remark !!} @endif</td>
					</tr>
					@endforeach
					</tbody>
				</table>
				@endif
				</div>

			</div>
			@if($data->anyother!='')
			<div class="section-block-detail" style="font-size:13px!important;">
				
				<div class="section-header-detail">
					<span class="material-icons">playlist_add_check</span>
					&nbsp;<h2 class="section-title-detail">Other Information (if any)</h2>
				</div>
				
				<table style="width:100%; border:none;">
					<tr>
						<td style="border:none;">{!! $data->anyother !!}</td>
					</tr>
				</table>
			</div>
			@endif
			
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
							<td class="padding-5">{{$comm->email}}</td>
							<td class="padding-5">{{$comm->usertype}}</td>
						</tr>
						@endforeach
						</tbody>
						@endif
					</table>

				</div>
				
			</div>
			@endif
			<div class="section-block-detail">
				<div class="section-header-detail">
					<input type="text" name="evaluation_index" id="evaluation_index" value="{{$data->evaluation_index}}" style="width:50px;" placeholder="Index">

					<span class="material-icons">compare</span>
					&nbsp;<h2 class="section-title-detail">Evaluation Process</h2>
				</div>
				<table style="width:100%; border:none;">
					<tr>
						<td style="border:none;">
						{!! $data->evaluationprocess !!}
						</td>
					</tr>
				</table>							
			</div>
			<div class="section-block-detail">
				<div class="section-header-detail">
					<span class="material-icons">playlist_add_check</span> <!-- Hamburger menu icon -->
					&nbsp;<h2 class="section-title-detail">Payment & Penalty Terms</h2>
				</div>
				<table style="width:100%; border:none;">
					<tr>
						<td style="border:none;">
						{!!$data->termsandcondition!!}
						</td>
					</tr>
				</table>
				
			</div>
			<div class="section-block-detail">
				<div class="section-header-detail">
					<span class="material-icons">compare</span> <!-- Hamburger menu icon -->
					&nbsp;<h2 class="section-title-detail">Critical Information</h2>
				</div>
				<table style="width:100%; border:none;">
					<tr>
						<td style="border:none;">
						{!!$data->criticalinformation!!}
						</td>
					</tr>
				</table>							
			</div>
			<div class="section-block-detail">
				<div class="section-header-detail">
					<span class="material-icons">compare</span>
					&nbsp;<h2 class="section-title-detail">Documents Required to Participate in the EoI</h2>
				</div>
				<table style="width:100%; border:none;">
					<tr>
						<td style="border:none;">
						{!!$data->documentrequired!!}
						</td>
					</tr>
				</table>							
			</div>
			
		</div>
		<div class="col-sm-12"></div>
	</div>
</div>