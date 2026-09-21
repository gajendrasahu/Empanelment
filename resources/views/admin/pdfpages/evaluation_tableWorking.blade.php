@if($data->evaluationprocess!='')
<table class="pd-10" style="width:100%; bottom:10px;">

	<tr>
		<td colspan="2" class="form-label"><b>6. Evaluation Process</b></td>
	</tr>
	<tr>
		<td colspan="2">{!! $data->evaluationprocess !!}</td>
	</tr>
</table>
@endif	
@if($data->termsandcondition!='')
<table class="pd-10" style="width:100%; bottom:10px;">	
	<tr>
		<td colspan="2" class="form-label"><b>7. Payment & Penalty Terms</b></td>
	</tr>
	<tr>
		<td colspan="2">{!! str_replace('Please provide project name',$data->engagementname,$data->termsandcondition) !!}</td>
	</tr>
</table>
@endif	
@if($data->criticalinformation!='')
@php
if($data->deadlinedate!='')
{
	$data->criticalinformation	=	str_replace('dd/mm/YYYY','<b>'.date('d\-m\-Y, h:i A',strtotime($data->deadlinedate)).'</b>',$data->criticalinformation);
}
@endphp
	
<table class="pd-10" style="width:100%; bottom:10px;">
	<tr>
		<td colspan="2" class="form-label"><b>8. Critical Information</b></td>
	</tr>
	<tr>
		<td colspan="2">{!! $data->criticalinformation !!}</td>
	</tr>
</table>
@endif
@if($data->documentrequired!='')
<table class="pd-10" style="width:100%; bottom:10px;">	
	<tr>
		<td colspan="2" class="form-label"><b>9. Documents Required To Participate In This EoI</b></td>
	</tr>
	<tr>
		<td colspan="2">{!! $data->documentrequired !!}</td>
	</tr>
</table>
@endif
</table>
