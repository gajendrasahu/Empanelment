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
.left-panel
{
	background:#fff;
	border:1px solid #ececec;
	border-radius:8px;
	height:500px;
	max-height:500px;
	overflow-y:auto;
	padding:8px;
	box-shadow:0 1px 4px rgba(0,0,0,0.06);
}

.info-section
{
	margin-bottom:10px;
	border:1px solid #f1f1f1;
	border-radius:6px;
	overflow:hidden;
}

.info-title
{
	background:#f8f9fa;
	padding:5px 8px;
	font-size:12px;
	font-weight:600;
	color:#000;
	border-bottom:1px solid #ededed;
}

.info-body
{
	padding:6px 8px;
	font-size:12px;
	line-height:22px;
}

.info-link
{
	display:block;
	text-decoration:none;
	color:#000;
	padding:2px 0;
	transition:0.2s;
}

.info-link:hover
{
	color:#0d6efd;
	padding-left:2px;
}

.info-link i
{
	width:16px;
	color:#d9534f;
}

.firm-item
{
	padding:2px 0;
	font-size:12px;
	color:#000;
}

.firm-item i
{
	color:#000;
	width:14px;
}

.right-panel
{
	background:#fff;
	border:1px solid #ececec;
	border-radius:8px;
	height:500px;
	max-height:500px;
	overflow-y:auto;
	padding:8px;
	box-shadow:0 1px 4px rgba(0,0,0,0.06);
}

.main-card
{
	border:1px solid #efefef;
	border-radius:6px;
	padding:8px 10px;
	margin-bottom:10px;
	background:#fcfcfc;
}

.main-title
{
	font-size:18px;
	font-weight:600;
	color:#000;
	margin-bottom:4px;
}

.main-title i
{
	font-size:18px;
	vertical-align:middle;
	color:#000;
}

.main-info
{
	font-size:12px;
	color:#000;
	line-height:22px;
}

.section-card
{
	border:1px solid #eeeeee;
	border-radius:6px;
	margin-bottom:10px;
	background:#fff;
	overflow:hidden;
}

.section-header
{
	background:#f8f9fa;
	padding:6px 10px;
	border-bottom:1px solid #eeeeee;
	display:flex;
	align-items:center;
	gap:6px;
}

.section-header i,
.section-header span
{
	font-size:16px;
	color:#000;
}

.section-title
{
	font-size:13px;
	font-weight:600;
	margin:0;
	color:#000;
}

.section-body
{
	padding:8px 10px;
	font-size:12px;
	line-height:22px;
	color:#000;
}

.fact-grid
{
	display:grid;
	grid-template-columns:repeat(2,1fr);
	gap:8px;
}

.fact-item
{
	border:1px solid #f1f1f1;
	border-radius:5px;
	padding:6px 8px;
	background:#fcfcfc;
	font-size:12px;
	line-height:20px;
}

.fact-item label
{
	display:block;
	font-size:11px;
	font-weight:600;
	color:#000;
	margin-bottom:2px;
}

.full-width
{
	grid-column:span 2;
}

.simple-table
{
	width:100%;
	border-collapse:collapse;
	font-size:12px;
}

.simple-table th
{
	background:#f7f7f7;
	padding:6px;
	border:1px solid #ececec;
	font-weight:600;
}

.simple-table td
{
	padding:6px;
	border:1px solid #f0f0f0;
	vertical-align:top;
}

.committee-table td,
.committee-table th
{
	font-size:12px;
	padding:5px;
}

.index-box
{
	width:60px;
	height:28px;
	border:1px solid #ddd;
	border-radius:4px;
	padding:2px 6px;
	font-size:12px;
}

@media(max-width:768px)
{
	.fact-grid
	{
		grid-template-columns:1fr;
	}

	.full-width
	{
		grid-column:span 1;
	}
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
		@if($data->iscancelled==0 && $data->isClosed==0)
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
				@if($data->eoi_file!='')
				<div class="eoi-actions">
					<a href="{{route('view.uploadedfile', Crypt::encrypt($data->eoi_file))}}" target="_blank" class="eoi-btn action-a">
						<i class="fa fa-file-pdf-o"></i> Download EoI
					</a>
				</div>
				@endif
				@if($data->corrigendum_file)
				<div class="eoi-actions">
					<a href="{{route('view.uploadedfile', Crypt::encrypt($data->corrigendum_file))}}" target="_blank" class="eoi-btn action-a">
						<i class="fa fa-file-pdf-o"></i> Download Corrigendum
					</a>
				</div>
				@endif
				@if($data->eoistatus == 3 && date('Y\-m\-d',strtotime($data->deadlinedate))<$now && $data->isParticipated==0)
				<div class="eoi-actions">
					<a href="{{route('extend.eoidate',Crypt::encrypt($data->requestid))}}" class="eoi-btn action-a">
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
				
				@if(date('Y\-m\-d',strtotime($data->deadlinedate))<$now && $data->isParticipated==1)
				<div class="eoi-actions">
					<a href="{{route('view.pptresumes', Crypt::encrypt($data->requestid))}}" class="eoi-btn action-a">
						<i class="fa fa-file-pdf-o"></i> Resumes
					</a>
				</div>
				@endif
				
				@if(date('Y\-m\-d',strtotime($data->deadlinedate))<$now)
				@if($data->allowResourceAdd==0)
				<div class="eoi-actions">
					<a href="{{ route('allow.addition', ['requestid' => Crypt::encrypt($data->requestid),'allow_type' => 'Enable']) }}" class="eoi-btn action-a">
						<i class="fa fa-check"></i> Enable Additional Resources (EoI)
					</a>
				</div>
				@else
				<div class="eoi-actions">
					<a href="{{ route('allow.addition', ['requestid' => Crypt::encrypt($data->requestid),'allow_type' => 'Disable']) }}" class="eoi-btn action-a">
						<i class="fa fa-remove"></i> Disable Additional Resources (EoI)
					</a>
				</div>
				@endif
				@endif

				@if($data->addition_resource_letter)
				<div class="eoi-actions">
					<a href="{{route('view.uploadedfile', Crypt::encrypt($data->addition_resource_letter))}}" target="_blank" class="eoi-btn action-a">
						<i class="fa fa-check-circle"></i> Approval Letter
					</a>
				</div>
				@endif
				
			</div>
		@else
			<div style="display:flex; flex-wrap:wrap; gap:2px;">
				<div class="eoi-actions">
					<a href="{{route('view.uploadedfile', Crypt::encrypt($data->authorizationletter))}}" target="_blank" class="eoi-btn action-a">
						<i class="fa fa-hand-o-right"></i> View File
					</a>
				</div>
				@if($data->eoistatus>=1)
				<div class="eoi-actions">
					<a href="{{route('eoipreview', Crypt::encrypt($data->requestid))}}" target="_blank" class="eoi-btn action-a">
						<i class="fa fa-hand-o-right"></i> Preview
					</a>
				</div>
				@endif
				@if($data->eoi_file!='')
				<div class="eoi-actions">
					<a href="{{route('view.uploadedfile', Crypt::encrypt($data->eoi_file))}}" target="_blank" class="eoi-btn action-a">
						<i class="fa fa-file-pdf-o"></i> Download EoI
					</a>
				</div>
				@endif
				@if($data->corrigendum_file)
				<div class="eoi-actions">
					<a href="{{route('view.uploadedfile', Crypt::encrypt($data->corrigendum_file))}}" target="_blank" class="eoi-btn action-a">
						<i class="fa fa-file-pdf-o"></i> Download Corrigendum
					</a>
				</div>
				@endif
				
			</div>			
		@endif		
		</div>		
		<div class="col-sm-12"></div>
		<div class="col-sm-3 padding-10">

			<div class="left-panel">

				@if($old_letter->count()>0)
				<div class="info-section">
					<div class="info-title">
						<i class="fa fa-history"></i> Previous Letters
					</div>

					<div class="info-body">
						@foreach($old_letter as $attach)
						<a href="{{ route('view.uploadedfile',Crypt::encrypt($attach->auth_letter)) }}" 
						   target="_blank" 
						   class="info-link">
							<i class="fa fa-file-pdf-o"></i>
							Updated on {{date('d-m-Y h:i A',strtotime($attach->updated_on))}}
						</a>
						@endforeach
					</div>
				</div>
				@endif


				<div class="info-section">
					<div class="info-title">
						<i class="fa fa-upload"></i> Uploaded Letter
					</div>

					<div class="info-body">
						<a href="{{ route('view.uploadedfile', Crypt::encrypt($data->authorizationletter)) }}" 
						   target="_blank" 
						   class="info-link">
							<i class="fa fa-file-pdf-o"></i>
							Request letter from authorized signatory
						</a>
					</div>
				</div>


				@if($attachments->count()>0)
				<div class="info-section">
					<div class="info-title">
						<i class="fa fa-paperclip"></i> Other Attachments
					</div>

					<div class="info-body">
						@foreach($attachments as $attach)
						<a href="{{ route('view.uploadedfile',Crypt::encrypt($attach->attachmentfile)) }}" 
						   target="_blank" 
						   class="info-link">
							<i class="fa fa-file-pdf-o"></i>
							{{$attach->attachmenttitle}}
						</a>
						@endforeach
					</div>
				</div>
				@endif


				@if($data->eoi_file)
				<div class="info-section">
					<div class="info-title">
						<i class="fa fa-bullhorn"></i> Published EoI & Notesheet
					</div>

					<div class="info-body">

						<a href="{{ route('view.uploadedfile',Crypt::encrypt($data->eoi_file)) }}" 
						   target="_blank" 
						   class="info-link">
							<i class="fa fa-file-pdf-o"></i>
							Release Date {{date('d-m-Y',strtotime($data->releasedate))}}
						</a>

						@if($data->notesheet_file)
						<a href="{{ route('view.uploadedfile',Crypt::encrypt($data->notesheet_file)) }}" 
						   target="_blank" 
						   class="info-link">
							<i class="fa fa-file-pdf-o"></i>
							Notesheet File
						</a>
						@endif

					</div>
				</div>
				@endif


				@if($data->corrigendum_file)
				<div class="info-section">
					<div class="info-title">
						<i class="fa fa-refresh"></i> Corrigendum File
					</div>

					<div class="info-body">
						<a href="{{ route('view.uploadedfile',Crypt::encrypt($data->corrigendum_file)) }}" 
						   target="_blank" 
						   class="info-link">
							<i class="fa fa-file-pdf-o"></i>
							Corrigendum Date : {{date('d-m-Y',strtotime($data->corrigendum_date))}}
						</a>
					</div>
				</div>
				@endif


				@if($published->count()>0)
				<div class="info-section">
					<div class="info-title">
						<i class="fa fa-building"></i> Published to Firms
					</div>

					<div class="info-body">
						@foreach($published as $firm)
						<div class="firm-item">
							<i class="fa fa-angle-double-right"></i>
							{{$firm->companyname}}
						</div>
						@endforeach
					</div>
				</div>
				@endif


				@if($participated->count()>0)
				<div class="info-section">
					<div class="info-title">
						<i class="fa fa-users"></i> Participated Firms
					</div>

					<div class="info-body">
						@foreach($participated as $firm)
						<div class="firm-item">
							<i class="fa fa-angle-double-right"></i>
							{{$firm->companyname}}
						</div>
						@endforeach
					</div>
					
					
				</div>
				@endif

			</div>

		</div>

		<div class="col-sm-9 padding-10">

			<div class="right-panel">
				<select name="project_id" id="project_id" class="select2" onchange="updateProjectName(this.value,'{{Crypt::encrypt($data->requestid)}}')">
					<option value="">--Project Name--</option>
					@foreach($projects as $project)
					<option value="{{$project->projectid}}" @if($project->projectid==$data->projectid) selected @endif>{{$project->project_name}}</option>
					@endforeach
				</select><br><br>
				<!-- TOP CARD -->
				<div class="main-card">
					<div class="main-title">
						<i class="material-icons-outlined">topic</i>
						{{ $data->projecttitle }}
					</div>

					<div class="main-info">
						<b>Department / Project Manager :</b> {{ $data->departmentname }}<br>

						Requested on :
						<b>{{ date('d-m-Y, h:i A',strtotime($data->creationdate)) }}</b>

						|

						Project Duration :
						<b>{{ ucwords(strtolower($data->projectduration)) }} Months</b>
					</div>
				</div>


				<!-- TABLE OF CONTENT -->
				@if($data->page_indexing)
				<div class="section-card">
					<div class="section-header">
						<span class="material-icons">compare</span>
						<h2 class="section-title">Table of Contents (Optional)</h2>
					</div>

					<div class="section-body">
						{!! $data->page_indexing !!}
					</div>
				</div>
				@endif

				<!-- CHIPS OBJECTIVE -->
				<div class="section-card">
					<div class="section-body">
						{!! $data->chips_objective !!}
					</div>
				</div>


				<!-- FACT SHEET -->
				<div class="section-card">

					<div class="section-header">
						<span class="material-icons">playlist_add_check</span>
						<h2 class="section-title">Fact Sheet</h2>
					</div>

					<div class="section-body">

						<div class="fact-grid">

							<div class="fact-item">
								<label>EoI Reference Number</label>
								{{$data->eoinumber}}
							</div>

							<div class="fact-item">
								<label>Name of Issuer</label>
								Chhattisgarh Infotech Promotion Society (CHiPS)
							</div>

							<div class="fact-item">
								<label>Name of Engagement</label>
								<b>{{$data->engagementname}}</b>
							</div>

							<div class="fact-item">
								<label>Release Date</label>
								@if($data->releasedate)
								<b>{{date('d-m-Y',strtotime($data->releasedate))}}</b>
								@else
								TBD
								@endif
							</div>

							<div class="fact-item">
								<label>Last Date of Pre-bid Query</label>
								@if($data->prebidlastdate)
								<b>{{date('d-m-Y',strtotime($data->prebidlastdate))}}</b>
								@else
								TBD
								@endif
							</div>

							<div class="fact-item">
								<label>Last Date for Submission</label>
								@if($data->deadlinedate)
								<b>{{date('d-m-Y',strtotime($data->deadlinedate))}}</b>
								@else
								TBD
								@endif
							</div>

							<div class="fact-item">
								<label>Interview Date</label>
								@if($data->interviewdate)
								<b>{{date('d-m-Y',strtotime($data->interviewdate))}}</b>
								@else
								TBD
								@endif
							</div>

							<div class="fact-item">
								<label>Interview Place</label>
								{{$data->interview_place}}
							</div>

							<div class="fact-item">
								<label>Method of Selection</label>
								{{$data->selection_method}}
							</div>

							<div class="fact-item full-width">
								<label>Address for Communication</label>

								Chief Executive Officer<br>
								Chhattisgarh Infotech Promotion Society (CHiPS)<br>
								State Data Centre Building, Near Police Control Room,<br>
								Civil Lines, Raipur, Chhattisgarh-492001<br>
								Tel : 91771-404158<br>
								Email : ceochips@nic.in, empl.chips@cgchips.in
							</div>

						</div>

					</div>

				</div>


				<!-- PROJECT OBJECTIVE -->
				<div class="section-card">

					<div class="section-header">
						<span class="material-icons">playlist_add_check</span>
						<h2 class="section-title">Project Objective</h2>
					</div>

					<div class="section-body">
						{!! $data->projectobjective !!}
					</div>

				</div>


				<!-- SCOPE OF WORK -->
				<div class="section-card">

					<div class="section-header">
						<span class="material-icons">playlist_add_check</span>
						<h2 class="section-title">Scope of Work</h2>
					</div>

					<div class="section-body">
						{!! $data->scopeofwork !!}
					</div>

				</div>


				<!-- TEAM REQUIREMENT -->
				<div class="section-card">

					<div class="section-header">
						<span class="material-icons">group_add</span>
						<h2 class="section-title">Team Requirement</h2>
					</div>

					<div class="section-body">

						@if($data->categoryid==2)

						<div class="table-responsive">

							<table class="simple-table">

								<thead>
									<tr>
										<th style="width:50px;">S.No.</th>
										<th>Sector & Position</th>
										<th>Experience</th>
										<th>Deployment Type</th>
										<th>Duration</th>
									</tr>
								</thead>

								<tbody>

									@foreach($resources as $rec)

									<tr>
										<td rowspan="3" class="text-center">{{$loop->iteration}}</td>

										<td>
											{{ucwords(strtolower($rec->sectorname))}}<br>
											{{$rec->consultantposition}}
										</td>

										<td>{{$rec->experience}}</td>

										<td>{{$rec->employmenttype}}</td>

										<td>{{$rec->duration}} Months</td>
									</tr>

									<tr>
										<td colspan="5">
											<b>Qualification :</b>
											@if($rec->qualification!='')
											{!! preg_replace('/ style="[^"]*"/i', '', $rec->qualification) !!}
											@endif
										</td>
									</tr>

									<tr>
										<td colspan="5">
											<b>Remark :</b>
											@if($rec->remark!='')
											{!! preg_replace('/ style="[^"]*"/i', '', $rec->remark) !!}
											@endif
										</td>
									</tr>

									@endforeach

								</tbody>

							</table>

						</div>

						@endif


						@if($data->categoryid==1)

						<div class="table-responsive">

							<table class="simple-table">

								<thead>
									<tr>
										<th style="width:50px;">S.No.</th>
										<th>Position</th>
										<th>Experience</th>
										<th>Experience Level</th>
									</tr>
								</thead>

								<tbody>

									@foreach($resources as $rec)

									<tr>
										<td rowspan="3">{{$loop->iteration}}</td>

										<td>{!!$rec->role!!}</td>

										<td>{{$rec->experience}}</td>

										<td>Level-{{$rec->experiencelevel}}</td>
									</tr>

									<tr>
										<td colspan="4">
											<b>Qualification :</b>
											@if($rec->qualification)
											{!! $rec->qualification !!}
											@endif
										</td>
									</tr>

									<tr>
										<td colspan="4">
											<b>Remark :</b>
											@if($rec->remark)
											{!! $rec->remark !!}
											@endif
										</td>
									</tr>

									@endforeach

								</tbody>

							</table>

						</div>

						@endif

					</div>

				</div>


				<!-- OTHER INFO -->
				@if($data->anyother!='')

				<div class="section-card">

					<div class="section-header">
						<span class="material-icons">playlist_add_check</span>
						<h2 class="section-title">Other Information</h2>
					</div>

					<div class="section-body">
						{!! $data->anyother !!}
					</div>

				</div>

				@endif


				<!-- COMMITTEE -->
				@if($committee->count()>0)

				<div class="section-card">

					<div class="section-header">
						<span class="material-icons">groups</span>
						<h2 class="section-title">Committee Member</h2>
					</div>

					<div class="section-body">

						<div class="table-responsive">

							<table class="simple-table committee-table">

								<thead>
									<tr>
										<th>S.No.</th>
										<th>Name</th>
										<th>Designation</th>
										<th>Department</th>
										<th>Mobile</th>
										<th>Email</th>
										<th>From</th>
									</tr>
								</thead>

								<tbody>

									@foreach($committee as $comm)

									<tr>
										<td>{{$loop->iteration}}</td>
										<td>{{$comm->name}}</td>
										<td>{{$comm->designation}}</td>
										<td>{{$comm->department}}</td>
										<td>{{$comm->mobilenumber}}</td>
										<td>{{$comm->email}}</td>
										<td>{{$comm->usertype}}</td>
									</tr>

									@endforeach

								</tbody>

							</table>

						</div>

					</div>

				</div>

				@endif


				<!-- EVALUATION -->
				<div class="section-card">

					<div class="section-header">
						<span class="material-icons">compare</span>

						<h2 class="section-title">Evaluation Process</h2>

					</div>

					<div class="section-body">
						{!! $data->evaluationprocess !!}
					</div>

				</div>


				<!-- PAYMENT TERMS -->
				<div class="section-card">

					<div class="section-header">
						<span class="material-icons">playlist_add_check</span>
						<h2 class="section-title">Payment & Penalty Terms</h2>
					</div>

					<div class="section-body">
						{!!$data->termsandcondition!!}
					</div>

				</div>


				<!-- CRITICAL INFO -->
				<div class="section-card">

					<div class="section-header">
						<span class="material-icons">compare</span>
						<h2 class="section-title">Critical Information</h2>
					</div>

					<div class="section-body">
						{!!$data->criticalinformation!!}
					</div>

				</div>


				<!-- DOCUMENT REQUIRED -->
				<div class="section-card">

					<div class="section-header">
						<span class="material-icons">compare</span>
						<h2 class="section-title">
							Documents Required to Participate in the EoI
						</h2>
					</div>

					<div class="section-body">
						{!!$data->documentrequired!!}
					</div>

				</div>

			</div>

		</div>
		<div class="col-sm-12"></div>
	</div>
</div>