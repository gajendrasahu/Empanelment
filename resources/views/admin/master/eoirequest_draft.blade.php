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
			<form name="frm" id="frm" action="{{ route('update.draft',Crypt::encrypt($data->requestid))}}" method="post" enctype="multipart/form-data">
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

						@if($updates->count()>0)
						<div class="section-block-detail">
						
							<div class="grid-layout-detail cols-4" style="padding:0px 0px; gap:0px;">
								<div id="accordion" class="accordion-style1 panel-group">
									<div class="panel panel-default">
										<div class="panel-heading">
											<h4 class="panel-title">
												<a class="accordion-toggle give-weight" data-toggle="collapse" data-parent="#accordion" href="#collapseOne">
													<i class="ace-icon fa fa-angle-right bigger-110 pull-right" data-icon-hide="ace-icon fa fa-angle-down" data-icon-show="ace-icon fa fa-angle-right"></i>
													&nbsp;<i class="fa fa-refresh"></i> {{$updatedBy}}
												</a>
											</h4>
										</div>
										<div class="panel-collapse collapse in" id="collapseOne">
											<div class="panel-body" style="height:300px; max-height:300px; overflow-y:scroll;">
												<table class="mytable" border="1" style="text-transform: none!important; margin-top:0px;">
													<tr><td colspan="3" class="padding-5">Note : All checkboxes must be checked as confirmation of reading before the EoI can be floated.</td></tr>
													<tr class="myheadbg">
														<td class="padding-5 form-label center" style="width:30px;"></td>
														<td class="padding-5 form-label width-100 text-center">Date & Time</td>
														<td class="padding-5 form-label">Update Details</td>
													</tr>
													<tbody>
													@foreach($updates as $update)
													<tr>
														<td class="padding-5 center">
														@if($update->isviewed==0)
									<input type="checkbox" onclick="MarkAsRead('{{route('mark.asread')}}','{{Crypt::encrypt($update->recordid)}}')">
														@else
														<input type="checkbox" disabled checked>
														@endif
														</td>
														<td class="padding-5 center" nowrap>{{date('d\-m\-Y, h:i A',strtotime($update->updatedon))}}</td>
														<td class="padding-5">{!!$update->particular!!}</td>
													</tr>
													@endforeach
													</tbody>
												</table>
											</div>
										</div>
										
									</div>
								</div>

							</div>
							
						</div>
						@endif

					
						<div class="ui-card-detail">
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
							<table style="width:100%; border:none;">
								<tr>
									<td style="border:none;">
							@php
								$replywithremark = "";
								$replywithremark = old('replywithremark') ?: ($data->replywithremark ?: $replywithremark);
							@endphp
							<textarea class="form-control tinymce" name="replywithremark" required id="replywithremark" placeholder="Please revise EoI as needed" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}">{!! $replywithremark !!}</textarea>
							<span class="text-danger">@error('replywithremark') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
									</td>
								</tr>
								@if($data->eoistatus<2)
								<tr>
									<td style="text-align:right;">
										@permission('updateremark.eoi')
<button type="button" style="text-transform:none!important; width:150px!important;" class="btn btn-info myfrmbtn" id="updateRemarkBtn">
	<i class="fa fa-save"></i> Update Remark
</button>
										@endpermission
									</td>
								</tr>
								@endif
							</table>							
							
						</div>

						<div class="section-block-detail">
							<div class="section-header-detail">
								<span class="material-icons">compare</span> <!-- Hamburger menu icon -->
								&nbsp;<h2 class="section-title-detail">Table of Contents (Optional)</h2>
							</div>
							<table style="width:100%; border:none;">
								<tr>
									<td style="border:none;">
							@php
								$page_indexing = "";
								$page_indexing = old('page_indexing') ?: ($data->page_indexing ?: $page_indexing);
							@endphp
							<textarea class="form-control tinymce" name="page_indexing" required id="page_indexing" placeholder="Page indexing" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}">{!! $page_indexing !!}</textarea>
							<span class="text-danger">@error('page_indexing') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
									</td>
								</tr>
							</table>							
						</div>

						<div class="section-block-detail">
							<!--
							<div class="section-header-detail">
								<span class="material-icons">compare</span>
								&nbsp;<h2 class="section-title-detail">Table of Contents (Optional)</h2>
							</div>
							-->
							<table style="width:100%; border:none;">
								<tr><td><i class="fa fa-info-circle"></i> Please do not add the Expression of Interest (EOI) reference number or date. These will be added automatically.</td></tr>
								<tr>
									<td style="border:none;">
							@php
							
								$chips_objective = '
								<p>Chhattisgarh Infotech Promotion Society (CHiPS), the nodal agency of Department of Electronics &Information Technology, Government of Chhattisgarh, intends to appoint Consultant for the <b>'.$data->departmentname.'</b>.</p>
								<p>This document provides general information about the issuer, important dates and the overall evaluation
process for the selection of the Consulting Firm.</p>
								<p><b>Objective</b></p>
								<p>The objective of engaging the Consultant is to provide technical expertise and advisory support to the Technical Services Branch in the areas of cyber security technologies, cybercrime prevention mechanisms, and monitor implementation of innovative technology interventions.</p>
								
								<p><b>Issuer/Address for business query and Correspondence</b></p>

								<p>
The CEO,<br>
Chhattisgarh Infotech Promotion Society, Raipur<br>
State Data Centre Building, Near Police Control Room, Civil Lines, Raipur,<br>
Chhattisgarh–492001<br>
Tel: +91-771-4014158; Fax: +91 771-40141581<br>
Email: Empl.chips@cgchips.in, ceochips@nic.in							
								</p>								
								';
								$chips_objective = old('chips_objective') ?: ($data->chips_objective ?: $chips_objective);
							@endphp
									
							<textarea class="form-control tinymce" name="chips_objective" required id="chips_objective" placeholder="" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}">{!! $chips_objective !!}</textarea>
							<span class="text-danger">@error('chips_objective') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
									</td>
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
									<input type="text" name="project_name" id="project_name" value="{{old('project_name',$data->eoinumber)}}" required placeholder="EoI number" style="font-weight:normal; width:100%;" autocomplete="off">
									<span class="text-danger">@error('project_name') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
								</div>

								<div class="ui-item-detail">
									<label>Name of Issuer</label>
									<input type="text" name="issuername" readonly id="issuername" required value="Chhattisgarh Infotech Promotion Society (CHiPS)" style="width:100%;" placeholder="Name of Issuer">
									<span class="text-danger">@error('issuername') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
								</div>
								<div class="ui-item-detail">
									<label>Name of Engagement*</label>
									<input type="text" name="engagementname" id="engagementname" required value="{{ old('engagementname',$data->engagementname) }}" style="width:100%;" placeholder="Name of Engagement" autocomplete="off">
									<span class="text-danger">@error('engagementname') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
								</div>
								<div class="ui-item-detail">
									<label>Release Date of EoI By CHiPS <i class="fa fa-info-circle show-updates" data-url="{{ route('get.updates',[Crypt::encrypt('releasedate'),Crypt::encrypt($data->requestid)]) }}"></i></label>
									<div class="input-group" style="width:100%;">
									<input type="text" class="date-picker" data-date-format="dd-mm-yyyy" name="releasedate" id="releasedate" required value="{{ old('releasedate',$data->releasedate ? date('d-m-Y',strtotime($data->releasedate)) : '') }}" placeholder="dd-mm-YYYY" style="width:100%;">
									<span class="input-group-addon"><i class="fa fa-calendar bigger-110"></i></span>
									
									</div>
									<span class="text-danger">@error('releasedate') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
								</div>
								<div class="ui-item-detail">
									<label>Last Date of Pre-bid Query <i class="fa fa-info-circle show-updates" data-url="{{ route('get.updates',[Crypt::encrypt('prebidlastdate'),Crypt::encrypt($data->requestid)]) }}"></i></label>
									<div class="input-group" style="width:100%;">
									<input type="text" name="prebidlastdate" id="prebidlastdate" placeholder="dd-mm-YYYY" required value="{{ old('prebidlastdate',$data->prebidlastdate ? date('d-m-Y',strtotime($data->prebidlastdate)) : '') }}" style="width:100%;">
									<span class="input-group-addon"><i class="fa fa-calendar bigger-110"></i></span>
									
									</div>
									<span class="text-danger">@error('prebidlastdate') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
								</div>
								<div class="ui-item-detail">
									<label>Last Date for Submission of Proposals <i class="fa fa-info-circle show-updates" data-url="{{ route('get.updates',[Crypt::encrypt('deadlinedate'),Crypt::encrypt($data->requestid)]) }}"></i></label>
									<div class="input-group" style="width:100%;">
									<input type="text" name="deadlinedate" id="deadlinedate" placeholder="dd-mm-YYYY hh:mm AM/PM" required value="{{ old('deadlinedate', $data->deadlinedate ? date('d-m-Y h:i A', strtotime($data->deadlinedate)) : '') }}" style="width:100%;">
									<span class="input-group-addon"><i class="fa fa-calendar bigger-110"></i></span>
									
									</div>
									<span class="text-danger">@error('deadlinedate')<i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
								</div>
								<div class="ui-item-detail">
									<label>Tentative Date of Presentation and Interview <i class="fa fa-info-circle show-updates" data-url="{{ route('get.updates',[Crypt::encrypt('interviewdate'),Crypt::encrypt($data->requestid)]) }}"></i></label>
									<div class="input-group" style="width:100%;">
									
									<input type="text" name="interviewdate" id="interviewdate" placeholder="dd-mm-YYYY hh:mm AM/PM" required value="@if($data->interviewdate!='' && $data->interviewdate!='1970-01-01'){{ old('interviewdate',$data->interviewdate ? date('d-m-Y h:i A',strtotime($data->interviewdate)) : '') }}@endif" style="width:100%;">
									
									<span class="input-group-addon"><i class="fa fa-calendar bigger-110"></i></span>
									<span class="text-danger">@error('interviewdate') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
									</div>
								</div>
								<div class="ui-item-detail">
									<label>Place of presentations and Interviews</label>
									<input type="text" name="interview_place" id="interview_place" value="{{old('interview_place',$data->interview_place)}}" required placeholder="Place of presentations and Interviews" style="font-weight:normal; width:100%;" autocomplete="off">
									<span class="text-danger">@error('interview_place') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
								</div>

								<div class="ui-item-detail">
									<label>Method of Selection</label>
									<input type="text" name="selection_method" id="selection_method" value="{{old('selection_method',$data->selection_method)}}" required placeholder="Method of Selection" style="font-weight:normal; width:100%;" autocomplete="off">
									<span class="text-danger">@error('selection_method') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
								</div>
								
								<div class="ui-item-detail" style="grid-column: span 2;">
									<label>Address for Communication</label>
									<textarea name="communicationaddress" required readonly id="communicationaddress" rows="3" style="width:100%;" placeholder="Address For Communication">Chief Executive Officer&#10;Chhattisgarh Infotech Promotion Society (CHiPS)&#10;State Data Centre Building, Near Police Control Room, Civil Lines, Raipur, Chhattisgarh-492001&#10;Tel : 91771-404158&#10;Email : ceochips@nic.in, empl.chips@cgchips.in</textarea>
									<span class="text-danger">@error('communicationaddress') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
								</div>
							</div>
							
						</div>
						<!-- INCLUDE OBJECTIVE ABOUT SCOPE CONTENT -->
						
						<div class="section-block-detail">
							<div class="section-header-detail">
								<span class="material-icons">playlist_add_check</span> <!-- Hamburger menu icon -->
								&nbsp;<h2 class="section-title-detail">Project Objective</h2>
							</div>
							<table style="width:100%; border:none;">
								<tr>
									<td style="border:none;">
							<textarea class="form-control tinymce" name="projectobjective" required id="projectobjective" placeholder="Project objective" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}">{!! $data->projectobjective !!}</textarea>
							<span class="text-danger">@error('projectobjective') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
									</td>
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
									<td style="border:none;">
							<textarea class="form-control tinymce" name="scopeofwork" required id="scopeofwork" placeholder="Scope of work" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}">{!! $data->scopeofwork !!}</textarea>
							<span class="text-danger">@error('scopeofwork') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
									</td>
								</tr>
							</table>
							
						</div>

						
						<div class="section-block-detail">
							<div class="section-header-detail">
								<span class="material-icons">group_add</span> <!-- Hamburger menu icon -->
								&nbsp;<h2 class="section-title-detail">
								
								Team Requirement [Preferred Firm Tier : {{$preferred[$data->tier_choice]}}]</h2>
							</div>

							@foreach($html as $row)
							<div class="table-responsive mt-10">
							{!! $row !!}
							</div>
							@endforeach							

						</div>
						@include('admin.viewpages.other_condition')
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
							@php
								$evaluationprocess = '
								<ul>
								  <li>
									CHiPS will constitute an Evaluation Committee whose decision will be final in the evaluation of the responses to the Expression of Interest. No correspondence will be entertained outside the process of negotiation/discussion with the Committee.
								  </li>
								  <li>
									A presentation will be made by the bidding company about their understanding of the proposed area of work and their experience, if any, in a similar area of expertise (via Video Conference).
								  </li>
								  <li>
									During the presentation by the companies, the Evaluation Committee will interview the proposed candidates (via Video Conference).
								  </li>
								  <li>
									The marking for presentation and interview will be done as follows:
								  </li>
								</ul>

								<table border="1" cellpadding="8" cellspacing="0" width="100%">
								  <thead>
									<tr>
									  <th>Key Evaluation Criteria</th>
									  <th>Bidder Presentation<br>(50 Marks)</th>
									  <th>Candidate Interview<br>(50 Marks)</th>
									</tr>
								  </thead>
								  <tbody>
									<tr>
									  <td><strong>Criteria</strong></td>
									  <td>
										<ul>
										  <li>Approach and Methodology</li>
										  <li>Understanding of the Project Framework</li>
										  <li>Quality of proposed methodology for such a specific kind of project</li>
										  <li>Prior experience in project interaction with senior Government officials</li>
										  <li>Prior exposure to Auditing Government projects</li>
										  <li>Resource deployment at multiple government offices in multiple locations</li>
										  <li>Understanding of key SLAs of project management</li>
										</ul>
									  </td>
									  <td>
										<ul>
										  <li>Suitability of profile</li>
										  <li>Years of relevant experience</li>
										  <li>Good understanding of project management best practices framework</li>
										  <li>Ability to Negotiate / Persuade / Influence</li>
										  <li>Strong ability to understand, deliberate, and report / monitor the key SLAs</li>
										  <li>Excellent communication abilities</li>
										</ul>
									  </td>
									</tr>
								  </tbody>
								</table>
								';
								$evaluationprocess = old('evaluationprocess') ?: ($data->evaluationprocess ?: $evaluationprocess);
							@endphp
							<textarea class="form-control tinymce" name="evaluationprocess" required id="evaluationprocess" placeholder="Evaluation Process" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}">{!! $evaluationprocess !!}</textarea>
							<span class="text-danger">@error('evaluationprocess') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
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
									
							@php

								$defaultTerms = '
								<ul>
								  <li>
									Payment to the selected firm for the audit of <b>Please provide project name</b>, CHiPS, Government of Chhattisgarh, will be released upon submission of the invoice and corresponding deliverables on a quarterly basis.
								  </li>
								  <li>
									The successful bidder shall submit the invoice for payment on a quarterly basis.
								  </li>
								  <li>
									Each payment will be made only after the delivery of the corresponding deliverable and its acceptance by CHiPS.
								  </li>
								  <li>
									Taxes will be applicable as per prevailing government norms.
								  </li>
								</ul>

								<strong>PENALTY TERMS</strong>
								<ul>
								  <li>
									Penalty, if any, shall be governed as per the terms and conditions mentioned in Tender No. 165081/CEO/CHiPS/Empanelment/Consultancy Firm/2025, Raipur dated 20/02/2025.
								  </li>
								</ul>
								';

								$terms = old('termsandcondition') ?: ($data->termsandcondition ?: $defaultTerms);
							@endphp

							<input type="hidden" name="requestid" id="requestid" value="{{Crypt::encrypt($data->requestid)}}">				
							<textarea class="form-control tinymce" name="termsandcondition" required id="termsandcondition" placeholder="Payment & Penalty Terms" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}">{!! $terms !!}</textarea>
							<span class="text-danger">@error('termsandcondition') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
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
							@php
								if($data->deadlinedate)
								{
									$subDate	=	date('d\-m\-Y, h:i A',strtotime($data->deadlinedate));
								}
								else
								{
									$subDate	=	'dd/mm/YYYY';
								}
								$criticalinformation = '
								<ul>
								  <li>
									Bidder agencies are advised to study this EoI document carefully before submitting their proposals in response to this EoI Notice. Submission of a proposal in response to this notice shall be deemed to have been done after careful study and examination of this document with full understanding of its terms, conditions, and implications.
								  </li>
								  <li>
									The last date for submission of response is '.$subDate.'. Any response received after the above deadline will be rejected.
								  </li>
								  <li>
									The preferred mode of communication for query resolution is through email to:
									<br><br>
									<strong>CEO, CHiPS</strong><br>
									State Data Centre, Civil Lines,<br>
									Raipur – 492001<br>
									Email: ceochips@nic.in, empl.chips@cgchips.in<br>
									Tel: +91-771-4014158 | Fax: +91-771-4014158
								  </li>
								</ul>
								';
								$criticalinformation = old('criticalinformation') ?: ($data->criticalinformation ?: $criticalinformation);
							@endphp
							<textarea class="form-control tinymce" name="criticalinformation" required id="criticalinformation" placeholder="Critical information" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}">{!! $criticalinformation !!}</textarea>
							<span class="text-danger">@error('criticalinformation') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
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
							@php
								$documentrequired = '<ul><li>Signed copy of this EoI</li>
								<li>Resumes of the resources who will appear for the interview shall be submitted as per Empanelment Tender No. 165081/CEO/CHiPS/Empanelment/Consultancy Firms/2025, Raipur, dated 20/02/2025.</li>
								<li>Presentation showing strategy and action plan for conducting Impact Assessment</li></ul>';
								
								$documentrequired = old('documentrequired') ?: ($data->documentrequired ?: $documentrequired);
							@endphp
							<textarea class="form-control tinymce" name="documentrequired" required id="documentrequired" placeholder="Documents Required To Participate In This EoI" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}">{!! $documentrequired !!}</textarea>
							<span class="text-danger">@error('documentrequired') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
									</td>
								</tr>
								<tr><td class="text-center">&nbsp;</td></tr>
								@if($data->eoistatus<3)
								<tr class="finalsubmit">
									<td class="text-center">
										<table style="width:100%;">
											<tr><td>&nbsp;</td></tr>
											<tr>
												<td>
													@permission('show.draftpreview')
													<button type="button" style="text-transform:none!important; width:100px!important; float:left;" class="btn btn-info myfrmbtn show-preview" data-url="{{ route('show.draftpreview',Crypt::encrypt($data->requestid)) }}">
														<i class="fa fa-refresh"></i> Preview
													</button>
													@endpermission
													@permission('save.draft')
													<button type="button" style="text-transform:none!important; margin-left:10px; width:100px!important; float:left;" class="btn btn-info myfrmbtn" id="updateDraftBtn">
														<i class="fa fa-save"></i> Update
													</button>		
													@endpermission

													<button type="button" style="text-transform:none!important; margin-left:10px; width:150px!important; float:left;" class="btn btn-info myfrmbtn download-eoi-doc" data-url="{{ route('show.previeweoi',Crypt::encrypt($data->requestid)) }}">
														<i class="fa fa-refresh"></i> Download EoI
													</button>


													@if($data->isUpdateRequired==0)
													@if($data->isupdated==0)
													@permission('update.draft')
													<button type="button" style="text-transform:none!important; width:350px!important; float:right;" class="btn btn-info myfrmbtn" id="frmSubmit">
														<i class="fa fa-save"></i> Submit to @if($data->ispm==0) Department (if completed) @else Project Manager (if completed) @endif
													</button>
													@endpermission
													@else
													<button type="button" disabled style="text-transform:none!important; width:370px!important; float:right;" class="btn btn-info myfrmbtn">
														<i class="fa fa-warning"></i> All updates must be watched before you can submit.
													</button>
													@endif
													@else
													<button type="button" disabled style="text-transform:none!important; width:420px!important; float:right;" class="btn btn-info myfrmbtn">
														<i class="fa fa-warning"></i> Please note: Department/Project Manager update required
													</button>
													@endif
													
												</td>
											</tr>
											<tr><td>&nbsp;</td></tr>
											<tr>
												<td>
										
												</td>
											</tr>
										</table>
									</td>
								</tr>
								@endif
								
							</table>							
						</div>


<div class="col-sm-12">
	

	
</div>
						
						
						
						
					</div>
				

<div class="col-sm-12">&nbsp;</div>
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

<div class="modal fade" id="eoiModal">
  <div class="modal-dialog modal-xl" style="width:95%;">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" style="float:left;">EoI Detail</h5>
        <!-- Proper close button for BS3 -->
        <button type="button" class="close" style="float:right;" data-dismiss="modal">&times;</button>
      </div>
      <div class="modal-body" id="eoiContent">
        Loading...
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>


<div class="modal fade" id="eoiPreviewModal">
  <div class="modal-dialog modal-xl" style="width:95%;">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" style="float:left;">EoI Detail</h5>
        <!-- Proper close button for BS3 -->
        <button type="button" class="close" style="float:right;" data-dismiss="modal">&times;</button>
      </div>
      <div class="modal-body" id="eoiPreviewContent">
        Loading...
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<script src="{{ asset('panel/assets/js/jquery-2.1.4.min.js') }}"></script>
<script src="{{ asset('panel/assets/tinymce/tinymce.min.js') }}"></script>

<script>

$('.download-eoi-doc').on('click', function () {

    var btn = $(this); // store clicked button

    bootbox.confirm(
        'Before creating the EoI file, please ensure that all contents are updated properly.',
        function(result){

            if(result)
            {
                var docUrl = btn.data('url');

                var params = {
                    releasedate: $('input[name="releasedate"]').val(),
                    prebidlastdate: $('input[name="prebidlastdate"]').val(),
                    deadlinedate: $('input[name="deadlinedate"]').val(),
                    interviewdate: $('input[name="interviewdate"]').val(),
                    interview_place: $('input[name="interview_place"]').val(),
                    selection_method: $('input[name="selection_method"]').val(),

                    page_indexing: tinymce.get('page_indexing').getContent(),
                    chips_objective: tinymce.get('chips_objective').getContent(),
                    termsandcondition: tinymce.get('termsandcondition').getContent(),
                    criticalinformation: tinymce.get('criticalinformation').getContent(),
                    isPreview: 0,
                };

                // Create dynamic form
                var form = $('<form method="POST" action="' + docUrl + '"></form>');

                // CSRF token
                form.append(
                    '<input type="hidden" name="_token" value="' +
                    $('meta[name="csrf-token"]').attr('content') +
                    '">'
                );

                // Append params
                $.each(params, function (key, value) {
                    form.append(
                        '<textarea name="' + key + '">' + value + '</textarea>'
                    );
                });

                $('body').append(form);
                form.submit();
            }
        }
    );
});


function getTinyContent(id) {
    return tinymce.get(id) ? tinymce.get(id).getContent() : '';
}

$('.show-preview').on('click', function () {
    var docUrl = $(this).data('url');
	
	var project_name	=	$("#project_name").val();
	var engagementname	=	$("#engagementname").val();
	var evaluation_index=	$("#evaluation_index").val();
	if(project_name=='')
	{
		bootbox.alert('Enter EoI Refrence number.');
		return false;
	}
	if(engagementname=='')
	{
		bootbox.alert('Enter engagement name.');
		return false;
	}
	if(evaluation_index=='')
	{
		bootbox.alert('Enter evaluation index number for EoI.');
		return false;
	}
	
    var params = {
        releasedate:$('input[name="releasedate"]').val(),
        prebidlastdate:$('input[name="prebidlastdate"]').val(),
        deadlinedate:$('input[name="deadlinedate"]').val(),
        interviewdate:$('input[name="interviewdate"]').val(),
        eoinumber:$('input[name="project_name"]').val(),
        issuername:$('input[name="issuername"]').val(),
        engagementname:$('input[name="engagementname"]').val(),
		evaluation_index:$('input[name="evaluation_index"]').val(),
        communicationaddress:$('textarea[name="communicationaddress"]').val(),
		tableofcontent: getTinyContent('page_indexing'),
		chips_objective: getTinyContent('chips_objective'),
		projectobjective: getTinyContent('projectobjective'),
		scopeofwork: getTinyContent('scopeofwork'),
		anyother: getTinyContent('anyother'),
		evaluationprocess: getTinyContent('evaluationprocess'),
		termsandcondition: getTinyContent('termsandcondition'),
		criticalinformation: getTinyContent('criticalinformation'),
		documentrequired: getTinyContent('documentrequired'),
    };
    $(".modal-xl").css("width", "90%");
    $(".modal-title").html("Preview");
    $('#eoiPreviewContent').html('Loading...');
    $('#eoiPreviewModal').modal('show');

    $.get(docUrl, params, function (data) {
        setTimeout(function () {
            $('#eoiPreviewContent').html(data.formhtml);
        }, 2000);
    }).fail(function (xhr) {
		console.log(xhr.responseText);
        $('#eoiPreviewContent').html('<p class="text-danger">Failed to load EoI.</p>');
    });
});

function ShowUpdates() {
    if ($(".update_content").css("display") === "none")
	{
        $(".update_content").css("display", "");
		$(".update").html('<i class="fa fa-refresh"></i> Close');
    }
	else 
	{
        $(".update_content").css("display", "none");
		$(".update").html('<i class="fa fa-refresh"></i> View Updates');
    }
}
</script>

<script>


tinymce.init({
    selector: 'textarea.tinymce',
    promotion: false,
    branding: false,
    plugins: 'autoresize code advlist autolink lists charmap preview table searchreplace save',
    toolbar_mode: 'floating',
    toolbar: 'undo redo | formatselect | bold italic underline strikethrough | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link | searchreplace',
    menubar: true,
    statusbar: false,
    min_height: 60,
    autoresize_min_height: 60,
    autoresize_max_height: 490,
    autoresize_bottom_margin: 10,

    resize: false,

    setup: function (editor) {
        editor.on('init', function () {
            editor.getBody().style.fontSize = '14px';
            editor.getBody().style.lineHeight = '1.5';
        });
    }
});


    tinymce.init({
        selector: '#page_indexing',
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

tinymce.init({
    selector: '#evaluationprocess',
    promotion: false,
    branding: false,
    plugins: 'autoresize code advlist autolink lists charmap preview table searchreplace save',
    toolbar_mode: 'floating',
    toolbar: 'undo redo | formatselect | bold italic underline strikethrough | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link | searchreplace',
    menubar: true,
    statusbar: false,
    min_height: 60,                 // ~2 lines
    autoresize_min_height: 60,
    autoresize_max_height: 490,
    autoresize_bottom_margin: 10,
    resize: false,                  // disable manual resize

    setup: function (editor) {
        editor.on('init', function () {
            // Optional: match your app’s text styling
            editor.getBody().style.fontSize = '14px';
            editor.getBody().style.lineHeight = '1.5';
        });
    }
});

    tinymce.init({
        selector: '#replywithremark',
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
        selector: '#criticalinformation',
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
        selector: '#documentrequired',
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
        selector: '#anyother',
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

	$('#updateDraftBtn').on('click', function(e) {
		e.preventDefault();
		tinymce.triggerSave();
		var form = $('#frm')[0];
		var formData = new FormData(form);
		$.ajax({
			url: '{{route("save.draft")}}',
			method: 'POST',
			data: formData,
			processData: false,
			contentType: false,
			success: function(response) {
				if(response.status === 200)
				{
					setTimeout(function() { $(".finalsubmit").css("display",""); },2000);
					bootbox.alert(response.message);
					return false;
				}
				if(response.status === 300)
				{
					bootbox.alert(response.message);
					return false;
				}
				else
				{
					bootbox.alert('Something went wrong: ' + response.message);
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

	$('#updateRemarkBtn').on('click', function(e) {
		e.preventDefault();
		tinymce.triggerSave();
		var form = $('#frm')[0];
		var formData = new FormData(form);
		$.ajax({
			url: '{{route("updateremark.eoi")}}',
			method: 'POST',
			data: formData,
			processData: false,
			contentType: false,
			success: function(response) {
				if(response.status === 200)
				{
					setTimeout(function() { window.location.reload(); },2000);
					bootbox.alert(response.message);
					return false;
				}
				else
				{
					bootbox.alert('Something went wrong: ' + response.message);
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

	
	$('#frmSubmit').on('click', function(e) {
		e.preventDefault();
		
		if($("#releasedate").val()!='')
		{
			const releasedate 	= 	new parseDMY($("#releasedate").val());
			const prebidlastdate= 	new parseDMY($("#prebidlastdate").val());
			const deadlinedate 	= 	new parseDMY($("#deadlinedate").val());
			const interviewdate = 	new parseDMY($("#interviewdate").val());

			// Clear previous error messages if any
			$(".date-error").remove();

			// Validation logic
			if(!(releasedate < prebidlastdate && prebidlastdate < deadlinedate))
			{
				
				bootbox.alert('<b style="color:red;">The dates must follow a valid chronological order: Release Date < Pre-bid Query Last Date < Deadline < Interview Date.</b>');
				return false;
			}
		}
		var evaluation_index	=	document.getElementById("evaluation_index").value;
		var engagementname	=	document.getElementById("engagementname").value;
		if(evaluation_index=='')
		{
			bootbox.alert('Please provide evaluation index number for EoI.');
			return false;
		}
		if(engagementname=='')
		{
			bootbox.alert('Please provide the engagement name.');
			return false;
		}
		bootbox.confirm('Do you want to submit and send draft to department?',function(result){
			if(result)
			{
				document.forms['frm'].submit();
			}
		});
	});

	
});	

function parseDMY(dateStr) {
	// Expects input as "dd-mm-yyyy"
	const [datePart, timePart] = dateStr.split(" ");
	const parts = datePart.split("-");
	const day = parseInt(parts[0], 10);
	const month = parseInt(parts[1], 10) - 1; // Month is 0-indexed
	const year = parseInt(parts[2], 10);
	return new Date(year, month, day);
}





</script>
<script src="{{ asset('panel/assets/js/master.js') }}"></script>
<script src="{{ asset('panel/assets/js/datatablePaginationWithFilter.js') }}"></script>

<script>
document.querySelectorAll('.format-indian').forEach(function(el) {
    el.innerText = formatIndianNumber(el.dataset.value);
});


$(document).ready(function() {
  $('.info-icon').each(function () {

    let base   = $(this).data('baseprice');
    let tax    = $(this).data('tax');
    let withTx = $(this).data('withtax');
    let dur    = $(this).data('duration');
    let total  = $(this).data('total');

    let html =
      '<table>' +
        '<tr><td class="padding-5 text-left" nowrap>Base Price (A)</td>' +
            '<td nowrap class="padding-5 text-left format-indian" data-value="' + base + '"></td></tr>' +

        '<tr><td nowrap class="padding-5 text-left" nowrap>Tax (B=A+(A*' + tax + '%))</td>' +
            '<td nowrap class="padding-5 text-left format-indian" data-value="' + withTx + '"></td></tr>' +

        '<tr><td nowrap class="padding-5 text-left">Duration (in months) (C)</td>' +
            '<td nowrap class="padding-5 text-left">' + dur + '</td></tr>' +

        '<tr><td nowrap class="padding-5 text-left">Total (D=B*C)</td>' +
            '<td nowrap class="padding-5 text-left format-indian" data-value="' + total + '"></td></tr>' +
      '</table>';

    $(this)
      .attr('title', html)
      .tooltip({ html: true, boundary: 'window', placement: 'auto' });
  });

  $(document).on('shown.bs.tooltip', '.info-icon', function () {
      let tooltipId = $(this).attr('aria-describedby');
      let tooltipEl = document.getElementById(tooltipId);

      if (!tooltipEl) return;

      tooltipEl.querySelectorAll('.format-indian').forEach(function (el) {
          el.innerText = formatIndianNumber(el.dataset.value);
      });
  });
});
</script>
@endsection