<table class="mytable" border="1" style="text-transform: none!important; width:100%!important; margin-top:10px; border-collapse:collapse;">
	<thead>
	<tr><td class="padding-5" colspan="5"><b>4.1) Resource Summary</b></td></tr>
	<tr class="">
		<td class="padding-5 center" style="width:50px;"><b>S.No.</b></td>
		<td class="padding-5" nowrap><b>Role</b></td>
		<td class="padding-5" nowrap><b>Experience</b></td>
		<td class="padding-5 width-300"><b>Experience Level</b></td>
		<td class="padding-5 text-right" nowrap><b>Duration</b></td>
		<td class="padding-5 text-right" nowrap><b>Count</b></td>
	</tr>
	</thead>
	<tbody class="eoidata">
	@php $i=1; $total=0; @endphp
	@foreach($resource_summary as $rec)
	@php
	$total=$total+$rec->resource_count;
	@endphp
	<tr class="font-14">
		<td nowrap class="padding-5 center" style="vertical-align:middle!important;">{{$loop->iteration}}</td>
		<td nowrap class="padding-5">{{ucwords(strtolower($rec->role))}}</td>
		<td nowrap class="padding-5">{{$rec->experience}}</td>
		<td class="padding-5">{{$rec->experiencelevel}}</td>
		<td nowrap class="padding-5 text-right">{{$rec->duration}} Months</td>
		<td nowrap class="padding-5 text-right">{{$rec->resource_count}}</td>
	</tr>
	@endforeach
	<tr class="font-14"><td class="text-right padding-5" colspan="5"><b>Total</b></td><td class="padding-5 text-right"><b>{{$total}}</b></td></tr>
	</tbody>
</table>