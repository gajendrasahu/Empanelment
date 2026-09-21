<table class="mytable" border="1" style="text-transform: none!important; width:100%!important; margin-top:10px; border-collapse:collapse;">
	<thead>
	<tr><td class="padding-5" colspan="5"><b>4.2) Resource Detail</b></td></tr>
	<tr class="">
		<td class="padding-5 center" style="width:50px;"><b>S.No.</b></td>
		<td class="padding-5" nowrap><b>Sector</b></td>
		<td class="padding-5" nowrap><b>Position</b></td>
		<td class="padding-5 width-300"><b>Experience</b></td>
		<td class="padding-5 text-right" nowrap><b>Duration</b></td>
	</tr>
	</thead>
	<tbody class="eoidata">
	@php $i=1; @endphp
	@foreach($detail as $rec)
	@php
	
	@endphp
	<tr class="font-14">
		<td nowrap class="padding-5 center" style="vertical-align:top!important;" rowspan="3">{{$i}}</td>
		<td nowrap class="padding-5">{{ucwords(strtolower($rec->sectorname))}}</td>
		<td nowrap class="padding-5">{{$rec->consultantposition}}</td>
		<td class="padding-5">{{$rec->experience}}</td>
		<td nowrap class="padding-5 text-right">{{$rec->duration}} Months</td>
	</tr>
	
	<tr>
		<td class="padding-5 v-top" colspan="4">Qualification : @if($rec->qualification!='') {!! preg_replace('/ style="[^"]*"/i', '', $rec->qualification) !!} @endif</td>
	</tr>
	
	
	<tr>
		<td class="padding-5 v-top" colspan="4">Remark : @if($rec->remark!='') {!! preg_replace('/ style="[^"]*"/i', '', $rec->remark) !!} @endif</td>
	</tr>
	
	
	@php $i=$i+1; @endphp
	@endforeach
	@if($i==1)
	<tr>
		<td colspan="5" class="center">--NO RECORD ADDED--</td>
	</tr>
	@endif
	</tbody>
</table>