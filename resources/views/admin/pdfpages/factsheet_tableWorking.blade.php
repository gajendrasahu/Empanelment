<table class="pd-10 border" style="width:100%; border:1px solid #eee; border-collapse:collapse;">
	<tr>
		<td colspan="2" class="form-label"><b>1. Fact Sheet</b></td>
	</tr>
	<tr>
		<td class="form-label">Name of Issuer</td>
		<td>{{ $data->issuername }}</td>
	</tr>
	<tr>
		<td class="form-label">Name of Engagement</td>
		<td>{{ $data->engagementname }}</td>
	</tr>
	<tr>
		<td class="form-label" nowrap>Release Date of EoI by CHiPS</td>
		<td>@if($data->releasedate){{ date('d-m-Y',strtotime($data->releasedate)) }}@else To be declared @endif</td>
	</tr>
	<tr>
		<td class="form-label">Last Date of Pre-bid Query</td>
		<td>@if($data->prebidlastdate){{ date('d-m-Y',strtotime($data->prebidlastdate)) }}@else To be declared @endif</td>
	</tr>
	<tr>
		<td class="form-label" nowrap>Last Date for Submission of Proposals</td>
		<td>@if($data->deadlinedate){{ date('d-m-Y, h:i A',strtotime($data->deadlinedate))}}@else To be declared @endif</td>
	</tr>
	<tr>
		<td class="form-label" nowrap>Tentative Date of Presentation and Interview</td>
		<td>@if($data->interviewdate){{ date('d-m-Y, h:i A',strtotime($data->interviewdate)) }}@else To be declared @endif</td>
	</tr>
	<tr>
		<td class="form-label" nowrap>Place of presentations and Interviews</td>
		<td>@if($data->interview_place) {{ $data->interview_place }} @else To be declared @endif</td>
	</tr>
	<tr>
		<td class="form-label" nowrap>Method of Selection</td>
		<td>@if($data->selection_method) {{ $data->selection_method }} @else To be declared @endif</td>
	</tr>
	<tr>
		<td class="form-label">Address for Communication</td>
		<td>{!! $data->communicationaddress !!}</td>
	</tr>
</table>
