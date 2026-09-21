<b>4. Team Requirement [@if($data->tier_choice==1) Tier - 1 @elseif($data->tier_choice==2) Tier - 2 @else Both @endif]</b>
<table class="pd-5 border" style="width:100%!important; margin-top:10px; border-collapse:collapse;">
	<thead>
	<tr class="">
		<td class="center" style="width:50px;"><b>S.No.</b></td>
		<td nowrap><b>Sector</b></td>
		<td nowrap><b>Position</b></td>
		<td class="width-300"><b>Experience</b></td>
		<td class="text-right" nowrap><b>Duration</b></td>
	</tr>
	</thead>
	<tbody class="eoidata">
	@php $i=1; @endphp
	@foreach($detail as $detail)
	@php
	
	@endphp
	<tr class="font-12">
		<td nowrap class="center">{{$i}}</td>
		<td nowrap>{{ucwords(strtolower($detail->sectorname))}}</td>
		<td nowrap>{{$detail->consultantposition}}</td>
		<td>{{$detail->experience}}</td>
		<td nowrap class="text-right">{{$detail->duration}} Months</td>
	</tr>
	@if($detail->qualification!='')
	<tr>
		<td class="v-top" nowrap>Qualification :</td>
		<td class="v-top" colspan="4">{!! preg_replace('/ style="[^"]*"/i', '', $detail->qualification) !!}</td>
	</tr>
	@endif
	@if($detail->remark!='')
	<tr>
		<td class="v-top" nowrap>Remark :</td>
		<td class="v-top" colspan="4">{!! preg_replace('/ style="[^"]*"/i', '', $detail->remark) !!}</td>
	</tr>
	@endif
	
	@php $i=$i+1; @endphp
	@endforeach
	@if($i==1)
	<tr>
		<td colspan="5" class="center">--NO RECORD ADDED--</td>
	</tr>
	@endif
	</tbody>
</table>