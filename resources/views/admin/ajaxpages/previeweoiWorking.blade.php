<!DOCTYPE html>
<html>
<head>
    <title>EoI</title>
<style>
.padding-5
{
	padding:5px;
}
.padding-8
{
	padding:8px;
}
.form-label
{
	font-weight:500;
	color:#000000;
}
.center
{
	text-align:center;
}
.head-bg
{
	background-color:#e4e6e9;
}
td
{
	vertical-align:top!important;
}

.a4-table-container {
  width: 8.27in;
  height: 11.69in;
  background-image: url('https://empl.cgstate.gov.in/panel/assets/images/basic/template-bg.png');
  background-repeat: no-repeat;
  background-position: top left;
  background-size: auto 100%;
  border:none!important;
}

.a4-table-container table, 
.a4-table-container th, 
.a4-table-container td {
  border: 1px solid #333;
}

.a4-table-container th, 
.a4-table-container td {
  padding: 8px;
  text-align: left;
}
.address
{
	width:100%;
	background-color:#1f487c;
	color:white;
	text-align:center;
	height: 300px;          /* required */
	border: 1px solid #000;
	display: flex;
	align-items: flex-end;	
}
.expression
{
	
}
.page-break {
  page-break-after: always;
}
.pd-10 td
{
	padding:10px!important;
}
.pd-5 td
{
	padding:5px!important;
}
.border td
{
	border: 1px solid #eee;
	border-collapse: collapse;		
}

</style>
</head>
<body>
<table style="width:100%; height:11.69in; text-align:center; border:none!important; background-color:#95b3d7;">
	<tr>
		<td style="width:40%;"></td>
		<td style="width:50%; background-color:white; padding:10px; text-align:left;">
			<div class="address">
<p style="vertical-align:bottom;">Chhattisgarh Infotech Promotion Society
(CHiPS), State Data Centre Building, Near
Police Control Room, Civil Lines, Raipur,
Chhattisgarh–492001<br>Tel.: +91-771-
4014158<br>Email: ceochips@nic.in				
</p>
			</div>
			<div class="expression">
			<br>
			<span style="font-size:30px; color:#4f80bb;">Expression of Interest<br>(EoI)</span><br><br>
			<span style="font-size:30px; font-weight:700; line-height:40px;"><br>{{$data->projecttitle}}</span>
			</div>
		</td>
		<td style="width:10%;">
		
		</td>
	</tr>
</table>

<table style="width:100%;">
	<tr>
		<td colspan="2" class="padding-8 form-label"><b>Table of Contents</b></td>
	</tr>
	<tr>
		<td colspan="2" class="padding-8">{!! $data->page_indexing !!}</td>
	</tr>
</table>
<table style="width:100%;">
	@if($data->engagementname!='')
	<tr>
		<td colspan="2" class="padding-8 form-label">Expression of Interest (EOI) for {!!$data->engagementname!!}</td>
	</tr>
	@endif
	@if($data->eoinumber!='')
	<tr>
		<td class="padding-8 text-left">Ref. No. <b>{!!$data->eoinumber!!}</b></td>
		<td class="padding-8" style="text-align:right;">Dated : @if($data->releasedate) <b>{{date('d\-m\-Y',strtotime($data->releasedate))}}</b> @else dd-mm-YYYY @endif</td>
	</tr>
	@endif
	<tr>
		<td colspan="2" class="padding-8">{!! $data->chips_objective !!}</td>
	</tr>
</table>

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
		<td>@if($data->interview_place){{ $data->interview_place }}@else To be declared @endif</td>
	</tr>
	<tr>
		<td class="form-label" nowrap>Method of Selection</td>
		<td>@if($data->selection_method){{ $data->selection_method }}@else To be declared @endif</td>
	</tr>
	<tr>
		<td class="form-label">Address for Communication</td>
		<td>{!! $data->communicationaddress !!}</td>
	</tr>
</table>

<table class="pd-10" style="width:100%; bottom:10px;">
	<tr>
		<td colspan="2" class="form-label"><b>2. Project Introduction / Objective</b></td>
	</tr>
	<tr>
		<td colspan="2">{!! $data->projectobjective !!}</td>
	</tr>
</table>
<table class="pd-10" style="width:100%; bottom:10px;">
	<tr>
		<td colspan="2" class="form-label"><b>3. Scope of Work</b></td>
	</tr>
	<tr>
		<td colspan="2">{!! $data->scopeofwork !!}</td>
	</tr>
</table>



@if($tier_html!=''){!!$tier_html!!}@endif

<table class="pd-10" style="width:100%; bottom:10px;">
	<tr>
		<td colspan="2" class="form-label">
			<b>5. Other Information</b>
		</td>
	</tr>
	<tr>
		<td colspan="2">{!! $data->anyother !!}</td>
	</tr>
	
</table>



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



<div style="width:100%; text-align:center;">
***End of Document***
</div>
<script src="{{ asset('panel/assets/js/datatablePaginationWithFilter.js') }}"></script>
<script>
document.querySelectorAll('.format-indian').forEach(function(el) {
    el.innerText = formatIndianNumber(el.dataset.value);
});

</script>

</body>
</html>