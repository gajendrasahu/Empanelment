<b>4. Team Requirement [@if($data->tier_choice==1) Tier - 1 @elseif($data->tier_choice==2) Tier - 2 @else Both @endif]</b>
<table class="pd-5 border" style="width:100%!important; margin-top:10px; border-collapse:collapse;">
	<thead>
	<tr class="">
		<td class="center" style="width:50px;">S.No.</td>
		<td nowrap>Position</td>
		<td nowrap>Experience <i class="fa fa-info-circle" title="As per empanelment"></i></td>
		<td class="width-300">Qualification <i class="fa fa-info-circle" title="As per empanelment"></i></td>
		<td class="text-right" style="width:80px;" nowrap>Duration</td>
		<td class="width-200">Remark <i class="fa fa-info-circle" title="Additional qualifications, depending on experience."></i></td>
	</tr>
	</thead>
	<tbody class="eoidata">
	@php $i=1; $total=0; @endphp
	@foreach($detail as $detail)
	<tr class="font-12">
		<td nowrap class="center">{{$i}}</td>
		<td nowrap>{{ucwords(strtolower($detail->role))}}</td>
		<td nowrap>Level-{{$detail->experiencelevel}}<br>{{$detail->experience}}</td>
		<td>{!!$detail->qualification!!}</td>
		<td nowrap  class="text-right" nowrap>{{$detail->duration}} Months</td>
		<td style="width:200px;">{!!$detail->remark!!}</td>
	</tr>
	@php $i=$i+1; @endphp
	@endforeach
	@if($i==1)
	<tr>
		<td colspan="6" class="center">--NO RECORD ADDED--</td>
	</tr>
	@endif
	</tbody>
</table>
