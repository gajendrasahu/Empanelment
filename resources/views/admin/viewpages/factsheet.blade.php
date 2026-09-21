<div class="section-block-detail">
	<div class="section-header-detail">
		<span class="material-icons">playlist_add_check</span> <!-- Hamburger menu icon -->
		&nbsp;<h2 class="section-title-detail">Fact Sheet</h2>
	</div>
	<div class="grid-layout-detail cols-2">
		<div class="ui-item-detail">
			<label>EoI reference Number</label>
			<p>@if($data->eoinumber) {{ $data->eoinumber }} @else - @endif</p>
		</div>

		<div class="ui-item-detail">
			<label>Name of Issuer</label>
			<p>{{ $data->issuername }}</p>
		</div>
		<div class="ui-item-detail">
			<label>Name of Engagement*</label>
			<p> @if($data->engagementname) {{$data->engagementname}} @else - @endif</p>
		</div>
		<div class="ui-item-detail">
			<label>Release Date of EoI by CHiPS <i class="fa fa-info-circle show-updates" data-url="{{ route('get.updates',[Crypt::encrypt('releasedate'),Crypt::encrypt($data->requestid)]) }}"></i></label>
			<p><i class="fa fa-calendar"></i> {{ old('releasedate',$data->releasedate ? date('d-m-Y',strtotime($data->releasedate)) : 'To be declared') }}</p>
		</div>
		<div class="ui-item-detail">
			
			<label>Last Date of Pre-bid Query 
			@if($data->isSecondCall==0)<i class="fa fa-info-circle show-updates" data-url="{{ route('get.updates',[Crypt::encrypt('prebidlastdate'),Crypt::encrypt($data->requestid)]) }}"></i>@endif</label>
			@if($data->isSecondCall==0)
			<p><i class="fa fa-calendar"></i> {{ old('prebidlastdate',$data->prebidlastdate ? date('d-m-Y',strtotime($data->prebidlastdate)) : 'To be declared') }}</p>
			@else
				No Pre-Bid
			@endif
		</div>
		<div class="ui-item-detail">
			<label>Last Date for Submission of Proposals <i class="fa fa-info-circle show-updates" data-url="{{ route('get.updates',[Crypt::encrypt('deadlinedate'),Crypt::encrypt($data->requestid)]) }}"></i></label>
			<p><i class="fa fa-calendar"></i> {{ old('deadlinedate',$data->deadlinedate ? date('d-m-Y, h:i A',strtotime($data->deadlinedate)) : 'To be declared') }}</p>
		</div>

		<div class="ui-item-detail">
			<label>Tentative Date of Presentation and Interview <i class="fa fa-info-circle show-updates" data-url="{{ route('get.updates',[Crypt::encrypt('interviewdate'),Crypt::encrypt($data->requestid)]) }}"></i></label>
			<p><i class="fa fa-calendar"></i> {{ old('interviewdate',$data->interviewdate ? date('d-m-Y, h:i A',strtotime($data->interviewdate)) : 'To be declared') }}</p>
		</div>
		<div class="ui-item-detail">
			<label>Place of presentations and Interviews</label>
			<p>@if($data->interview_place) {{ $data->interview_place }} @else To be informed later @endif</p>
		</div>

		<div class="ui-item-detail">
			<label>Method of Selection</label>
			<p>@if($data->selection_method) {{ $data->selection_method }} @else To be informed later @endif</p>
		</div>
		
		<div class="ui-item-detail" style="grid-column: span 2;">
			<label>Address for Communication</label>
			<p>{!! $data->communicationaddress !!}</p>
		</div>
	</div>
</div>
