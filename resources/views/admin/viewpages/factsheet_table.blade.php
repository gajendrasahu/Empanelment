<table style="width:100%; border-collapse:collapse; margin-top:10px;" border="1">
	<tr>
		<td colspan="2" class="padding-8 form-label head-bg"><b>Fact Sheet</b></td>
	</tr>
	<tr>
		<td class="padding-8 form-label">Name of Issuer</td>
		<td class="padding-8">{{ $data->issuername }}</td>
	</tr>
	<tr>
		<td class="padding-8 form-label">Name of Engagement</td>
		<td class="padding-8">{{ $data->engagementname }}</td>
	</tr>
	<tr>
		<td class="padding-8 form-label" nowrap>Release Date of EoI by CHiPS</td>
		<td class="padding-8">@if($data->releasedate){{ date('d-m-Y',strtotime($data->releasedate)) }}@else To be declared @endif</td>
	</tr>
	<tr>
		<td class="padding-8 form-label">Last Date of Pre-bid Query</td>
		<td class="padding-8">
		@if($data->isSecondCall==0)
			@if($data->prebidlastdate){{ date('d-m-Y',strtotime($data->prebidlastdate)) }}@else To be declared @endif
		@else
			No Pre-Bid
		@endif
		</td>
	</tr>
	<tr>
		<td class="padding-8 form-label" nowrap>Last Date for Submission of Proposals</td>
		<td class="padding-8">@if($data->deadlinedate){{ date('d-m-Y, h:i A',strtotime($data->deadlinedate))}}@else To be declared @endif</td>
	</tr>
	<tr>
		<td class="padding-8 form-label" nowrap>Tentative Date of Presentation and Interview</td>
		<td class="padding-8">@if($data->interviewdate){{ date('d-m-Y, h:i A',strtotime($data->interviewdate)) }}@else To be declared @endif</td>
	</tr>
	<tr>
		<td class="padding-8 form-label" nowrap>Place of presentations and Interviews</td>
		<td class="padding-8">@if($data->interview_place){{ $data->interview_place }}@else To be declared @endif</td>
	</tr>
	<tr>
		<td class="padding-8 form-label" nowrap>Method of Selection</td>
		<td class="padding-8">@if($data->selection_method){{ $data->selection_method }}@else To be declared @endif</td>
	</tr>
	<tr>
		<td class="padding-8 form-label">Address for Communication</td>
		<td class="padding-8">{!! $data->communicationaddress !!}</td>
	</tr>
</table>
