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
.width-200
{
	background-color:#
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
</style>
</head>
<body>
  
<table style="width:100%; height:11.69in; text-align:center; border:none!important; background-color:#95b3d7;">
	<tr>
		<td style="width:40%;"></td>
		<td style="width:50%; background-color:white; padding:10px; text-align:left;">
			<div class="address">
<p style="vertical-align:top;">Chhattisgarh Infotech Promotion Society
(CHiPS), State Data Centre Building, Near
Police Control Room, Civil Lines, Raipur,
Chhattisgarh–492001<br>Tel.: +91-771-
4014158<br>Email: ceochips@nic.in				
</p>
			</div>
			<div class="expression">
			<h3>Expression of Interest (EoI)</h3>
			<span style="font-size:14px; font-weight:550;">{{$data->projecttitle}}</span>
			</div>
		</td>
		<td style="width:10%;">
		
		</td>
	</tr>
</table>

<div class="page-break"></div>
 
@if($data->page_indexing!='')
<table style="width:100%; border-collapse:collapse; margin-top:10px;" border="1">
	
	<tr>
		<td colspan="2" class="padding-8 form-label head-bg"><b>Table of Contents</b></td>
	</tr>
	<tr>
		<td colspan="2" class="padding-8">{!! $data->page_indexing !!}</td>
	</tr>
</table>
@endif

@if($data->chips_objective!='')
<table style="width:100%; border-collapse:collapse; margin-top:10px; line-height:25px;" border="1">
	<tr>
		<td colspan="2" class="padding-8 center" style="border:none!important;">
			<b>Expression of Interest (EoI) for </b>{!! $data->engagementname !!}
		</td>
	</tr>
	<tr>
		<td colspan="2" style="position:relative; border:none!important;" class="padding-8">
			<span style="position:absolute;"><b>Ref. No</b> : {{ $data->eoinumber }}</span>
			<span style="position:absolute; right:10px;">Date : @if($data->releasedate) {{date('d\-m\-Y',strtotime($data->releasedate))}} @else dd-mm-YYYY @endif</span><br>
		</td>
	</tr>
	<tr>
		<td colspan="2" class="padding-8" style="border:none!important;">
			{!! $data->chips_objective !!}
		</td>
	</tr>
</table>
@endif

<table style="width:100%; border-collapse:collapse; margin-top:10px;" border="1">
	<tr>
		<td colspan="2" class="padding-8 form-label head-bg"><b>1) Fact Sheet</b></td>
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
		<td class="padding-8 form-label">Release Date of EoI by CHiPS</td>
		<td class="padding-8">@if($data->releasedate!=''){{ date('d-m-Y',strtotime($data->releasedate)) }}@endif</td>
	</tr>
	<tr>
		<td class="padding-8 form-label">Last Date of Pre-bid Query</td>
		<td class="padding-8">@if($data->prebidlastdate!=''){{ date('d-m-Y',strtotime($data->prebidlastdate)) }}@endif</td>
	</tr>
	<tr>
		<td class="padding-8 form-label">Last Date for Submission of Proposals</td>
		<td class="padding-8">@if($data->deadlinedate!=''){{ date('d-m-Y, h:i A',strtotime($data->deadlinedate))}}@endif</td>
	</tr>
	<tr>
		<td class="padding-8 form-label" nowrap>Tentative Date of Presentation and Interview</td>
		<td class="padding-8">@if($data->interviewdate!='' && $data->interviewdate!='1970-01-01'){{ date('d-m-Y',strtotime($data->interviewdate)) }}@endif</td>
	</tr>
	<tr>
		<td class="padding-8 form-label">Address for Communication</td>
		<td class="padding-8">{!! $data->communicationaddress !!}</td>
	</tr>
</table>

<table style="width:100%; border-collapse:collapse; margin-top:10px;" border="1">
	@if($data->projectobjective!='')
	<tr>
		<td colspan="2" class="padding-8 form-label head-bg"><b>2) Project Objective</b></td>
	</tr>
	<tr>
		<td colspan="2" class="padding-8">{!! $data->projectobjective !!}</td>
	</tr>
	@endif
	<tr>
		<td colspan="2" class="padding-8 form-label head-bg"><b>3) Scope of Work</b></td>
	</tr>
	<tr>
		<td colspan="2" class="padding-8">{!! $data->scopeofwork !!}</td>
	</tr>
</table>


<table style="width:100%; border-collapse:collapse; margin-top:10px;" border="1">
	@if($tier_html!='')
	<tr>
		<td colspan="2" class="padding-8 form-label head-bg"><b>4) Team Requirement</b></td>
	</tr>
	<tr>
		<td colspan="2" style="padding:0px;">{!! $team_composition !!}</td>
	</tr>
	<tr>
		<td colspan="2" style="padding:0px;">{!! $tier_html !!}</td>
	</tr>
	
	@endif
	@if($data->anyother!='')
	<tr>
		<td colspan="2" class="padding-8 form-label head-bg"><b>5) Other Information (If Any)</b></td>
	</tr>
	<tr>
		<td colspan="2" class="padding-8">{!! $data->anyother !!}</td>
	</tr>
	@endif
@if($data->evaluationprocess!='')
	<tr>
		<td colspan="2" class="padding-8 form-label head-bg"><b>{{$data->evaluation_index++}}) Evaluation Process</b></td>
	</tr>
	<tr>
		<td colspan="2" class="padding-8">{!! $data->evaluationprocess !!}</td>
	</tr>
@endif	
@if($data->termsandcondition!='')
	<tr>
		<td colspan="2" class="padding-8 form-label head-bg"><b>{{$data->evaluation_index++}}) Payment & Penalty Terms</b></td>
	</tr>
	<tr>
		<td colspan="2" class="padding-8">
		{!! str_replace(
			'Please provide project name',
			$data->engagementname,
			$data->termsandcondition
		) !!}
		
		</td>
	</tr>
@endif	
@if($data->criticalinformation!='')
	<tr>
		<td colspan="2" class="padding-8 form-label head-bg"><b>{{$data->evaluation_index++}}) Critical Information</b></td>
	</tr>
	<tr>
		<td colspan="2" class="padding-8">
		{!! str_replace(
			'dd/mm/YYYY',
			'<b>'.date('d-m-Y h:i A',strtotime($data->deadlinedate)).'</b>',
			$data->criticalinformation
		) !!}
		
		</td>
	</tr>
@endif
@if($data->documentrequired!='')
	<tr>
		<td colspan="2" class="padding-8 form-label head-bg"><b>{{$data->evaluation_index++}}) Documents Required To Participate In This EoI</b></td>
	</tr>
	<tr>
		<td colspan="2" class="padding-8">{!! $data->documentrequired !!}</td>
	</tr>
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