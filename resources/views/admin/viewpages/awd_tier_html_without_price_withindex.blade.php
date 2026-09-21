<table class="mytable" border="1" style="text-transform: none!important; width:100%!important; margin-top:10px; border-collapse:collapse;">
	<thead>
	<tr><td class="padding-5" colspan="6">4.1) Resource Detail</td></tr>
	<tr class="">
		<td class="padding-5 center" style="width:50px;">S.No.</td>
		<td class="padding-5" nowrap>Position</td>
		<td class="padding-5" nowrap>Level & Experience <i class="fa fa-info-circle" title="As per empanelment"></i></td>
		<td class="padding-5 text-right" style="width:80px;" nowrap>Duration</td>
	</tr>
	</thead>
	<tbody class="eoidata">
	@php $i=1; $total=0; @endphp
	@foreach($detail as $rec)
	<tr class="font-12">
		<td nowrap class="padding-5 center" rowspan="3">{{$i}}</td>
		<td nowrap class="padding-5">{{ucwords(strtolower($rec->role))}}</td>
		<td nowrap class="padding-5">Level-{{$rec->experiencelevel}}<br>{{$rec->experience}}</td>
		<td nowrap  class="padding-5 text-right" nowrap>{{$rec->duration}} Months</td>
	</tr>
	
	<tr>
		<td class="padding-5 v-top" colspan="3">Qualification : @if($rec->qualification!='') {!! preg_replace('/ style="[^"]*"/i', '', $rec->qualification) !!} @endif</td>
	</tr>
	
	
	<tr>
		<td class="padding-5 v-top" colspan="3">Remark : @if($rec->remark!='') {!! preg_replace('/ style="[^"]*"/i', '', $rec->remark) !!} @endif</td>
	</tr>
	
	
	@php $i=$i+1; @endphp
	@endforeach
	@if($i==1)
	<tr>
		<td colspan="4" class="center">--NO RECORD ADDED--</td>
	</tr>
	@endif
	</tbody>
</table>
