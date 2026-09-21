<table class="pd-5" border="1" width="100%" cellpadding="6">
<thead>
<style>
table
{
	border:1px solid #000;
	border-collapse:collapse;
	margin-bottom:20px;
}
th
{
	padding:5px;
}
td
{
	padding:5px;
}
.text-left
{
	text-align:left;
}
.text-center
{
	text-align:center;
}
.text-right
{
	text-align:right;
}
.v-top
{
	vertical-align:top;
}
</style>
<tr>
	<th colspan="8" class="text-left">Order Value Based on Sector, Position, and Experience Level</th>
</tr>
<tr>
	@if($vendor->categoryid==2)
	<th class="text-left">Sector</th>
	<th class="text-left">Position</th>
	@endif
	@if($vendor->$categoryid==1)
	<th class="text-left">Role</th>
	<th class="text-left">Experience</th>
	@endif
	<th class="text-center">Duration</th>
	<th class="text-right">Base Price</th>
	@if($vendor->categoryid==2)
	<th class="text-right">Resource Cost (A)</th>
	<th class="text-right">GST (B=A*{{$pricing->tax}}%)</th>
	<th class="text-right">Admin Charge (C=((A+B)*{{$pricing->admincharge}}%)</th>
	<th class="text-right">Total (D=A+B+C)</th>
	@else	
	<th class="text-right">Resource Cost (A)</th>
	<th class="text-right">Operating Cost (B=A*{{$pricing->operatingmargin}}%)</th>
	<th class="text-right">GST (C=(A+B)*{{$pricing->tax}}%)</th>
	<th class="text-right">Total (D=A+B+C)</th>
	@endif
</tr>
</thead>
<tbody>
@php
$total_resource	=	0;
$total_operating=	0;
$total_gst		=	0;
$total_admin	=	0;
$total_resource	=	0;

@endphp
@foreach($data['resources'] as $resource)
@php

$rowCount	=	count($resource['rows']);


@endphp
@foreach($resource['rows'] as $index => $row)
<tr>
	@if($index == 0)
	@if($vendor->categoryid==2)
	<td class="v-top text-left" rowspan="{{ $rowCount }}">{{ ucwords(strtolower($resource['sector'])) }}</td>
	<td class="v-top text-left" rowspan="{{ $rowCount }}">{{ $resource['position'] }}</td>
	@endif
	@if($vendor->categoryid==1)
	<td class="v-top text-left" rowspan="{{ $rowCount }}">{{ ucwords(strtolower($resource['role'])) }}</td>
	<td class="v-top text-left" rowspan="{{ $rowCount }}">{{ $resource['experience'] }} [Level - {{ $resource['experiencelevel'] }}]</td>
	@endif
	@endif
	<td class="text-center">{{$row['slab']}}</td>
	<td class="text-right format-indian" data-value="{{$row['basePrice']}}"></td>
	@if($vendor->categoryid==2)
	<td class="text-right format-indian" data-value="{{$row['salary']}}"></td>
	<td class="text-right format-indian" data-value="{{$row['gst']}}"></td>
	<td class="text-right format-indian" data-value="{{$row['admin']}}"></td>
	<td class="text-right format-indian" data-value="{{$row['total']}}"></td>
	@else
	<td class="text-right format-indian" data-value="{{$row['salary']}}"></td>
	<td class="text-right format-indian" data-value="{{$row['operating']}}"></td>
	<td class="text-right format-indian" data-value="{{$row['gst']}}"></td>
	<td class="text-right format-indian" data-value="{{$row['total']}}"></td>
	@endif
</tr>

@php
$total_resource	=	$total_resource+$row['salary'];
$total_operating=	$total_operating+$row['operating'];
$total_gst		=	$total_gst+$row['gst'];
$total_admin	=	$total_admin+$row['admin'];

@endphp

@endforeach

@endforeach

<tr>
<td colspan="4"><b>Grand Total for {{$tier->tiername}}</b></td>
@if($vendor->categoryid==2)
<td class="text-right"><b class="format-indian" data-value="{{$total_resource}}"></b></td>
<td class="text-right"><b class="format-indian" data-value="{{$total_gst}}"></b></td>
<td class="text-right"><b class="format-indian" data-value="{{$total_admin}}"></b></td>
<td class="text-right"><b class="format-indian" data-value="{{ $data['grand_total'] }}"></b></td>
@endif
@if($vendor->categoryid==1)
<td class="text-right"><b class="format-indian" data-value="{{$total_resource}}"></b></td>
<td class="text-right"><b class="format-indian" data-value="{{$total_operating}}"></b></td>
<td class="text-right"><b class="format-indian" data-value="{{$total_gst}}"></b></td>
<td class="text-right"><b class="format-indian" data-value="{{ $data['grand_total'] }}"></b></td>
@endif
</tr>

</tbody>

</table>
<script src="{{ asset('panel/assets/js/master.js') }}"></script>
<script src="{{ asset('panel/assets/js/datatablePaginationWithFilter.js') }}"></script>

<script>
document.querySelectorAll('.format-indian').forEach(function(el) {
    el.innerText = formatIndianNumber(el.dataset.value);
});
</script>