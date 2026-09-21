@php
$t=0;
$preferred[1]	=	'Tier - I';
$preferred[2]	=	'Tier - II';
$preferred[3]	=	'Both';

@endphp


<div class="content-detail">
	<div class="ui-card-detail">
		<div>
			<div class="title-detail">
				<i class="material-icons-outlined">topic</i> {{ $data->projecttitle }}
			</div>
			<div class="details-detail lh-25">
				<b>Department/Project Manager : </b>{{ ucwords(strtolower($data->departmentname)) }}<br>
				@php
				$eoi	=	explode("/",$data->eoinumber);
				@endphp
				EoI Number: <b>{{$data->eoinumber}}</b> | Requested On : <b>{{ date('d\-m\-Y, h:i A',strtotime($data->creationdate)) }}</b> | Project Duration : <b>{{ ucwords(strtolower($data->projectduration)) }} Months</b><br>
				
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
	@include('admin.viewpages.page_indexing')
	@include('admin.viewpages.factsheet')
	@include('admin.viewpages.objective_without_editor')
	<div class="section-block-detail">
		<div class="section-header-detail">
			<span class="material-icons">group_add</span> <!-- Hamburger menu icon -->
			&nbsp;<h2 class="section-title-detail">Team Requirement [Preferred Vendor Tier : {{$preferred[$data->tier_choice]}}]</h2>
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

	<div class="section-block-detail">
		<div class="section-header-detail">
			<span class="material-icons">groups</span>
			<h2 class="section-title-detail">&nbsp;Committee Member</h2>
		</div>
		@if($committee->count()>0)
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
		@endif
	</div>
	
	@include('admin.viewpages.evaluation_without_editor')

<div class="col-sm-12">



</div>
	
	
	
	
</div>

<script src="{{ asset('panel/assets/js/datatablePaginationWithFilter.js') }}"></script>
<script>
document.querySelectorAll('.format-indian').forEach(function(el) {
    el.innerText = formatIndianNumber(el.dataset.value);
});


</script>
