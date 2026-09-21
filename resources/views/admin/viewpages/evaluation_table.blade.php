<table style="width:100%; border-collapse:collapse; margin-top:10px;" border="1">
@if($data->evaluationprocess!='')
	<tr>
		<td colspan="2" class="padding-8 form-label head-bg"><b>Evaluation Process</b></td>
	</tr>
	<tr>
		<td colspan="2" class="padding-8">{!! $data->evaluationprocess !!}</td>
	</tr>
@endif	
@if($data->termsandcondition!='')
	<tr>
		<td colspan="2" class="padding-8 form-label head-bg"><b>Payment & Penalty Terms</b></td>
	</tr>
	<tr>
		<td colspan="2" class="padding-8">{!! $data->termsandcondition !!}</td>
	</tr>
@endif	
@if($data->criticalinformation!='')
	<tr>
		<td colspan="2" class="padding-8 form-label head-bg"><b>Critical Information</b></td>
	</tr>
	<tr>
		<td colspan="2" class="padding-8">{!! $data->criticalinformation !!}</td>
	</tr>
@endif
@if($data->documentrequired!='')
	<tr>
		<td colspan="2" class="padding-8 form-label head-bg"><b>Documents Required To Participate In This EoI</b></td>
	</tr>
	<tr>
		<td colspan="2" class="padding-8">{!! $data->documentrequired !!}</td>
	</tr>
@endif
</table>
