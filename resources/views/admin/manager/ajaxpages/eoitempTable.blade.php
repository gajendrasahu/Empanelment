@php
$i=1;
$grandtotal=0;
@endphp
<tr class="myheadbg">
	<td class="mytdleftwhite center" style="width:50px;">S.No.</td>
	<td class="mytdleftwhite" nowrap>Category</td>
	@if($categoryid==2)
	<td class="mytdleftwhite" nowrap>Sector</td>
	<td class="mytdleftwhite" nowrap>Position</td>
	@else
	<td class="mytdleftwhite" nowrap>Role</td>
	@endif
	<td class="mytdleftwhite" nowrap>Tier</td>
	<td class="mytdleftwhite" nowrap>Experience</td>
	<td class="mytdleftwhite" nowrap>Qualification</td>
	<td class="mytdleftwhite" nowrap>Duration</td>
	<td class="mytdleftwhite" nowrap>Budget</td>
	<td class="mytdleftwhite" nowrap>Admin Charge</td>
	<td class="mytdleftwhite" nowrap>Total</td>
	<td class="mytdleftwhite" nowrap>Grand Total</td>
</tr>

@foreach ($data as $item)
<tr class="mytr" id="item-{{ $i }}">
    <td style="width:30px; text-align:center;" nowrap>{{ $i++ }}</td>
    <td nowrap>{{ $item->jobcategory }}</td>
	@if($categoryid==2)
	<td nowrap>{{ $item->sectorname }}</td>
	<td nowrap>{{ $item->consultantposition }}</td>
	@else
	<td nowrap>{{ $item->role }}</td>
	@endif
	<td nowrap>{{ $item->tiername }}</td>
	@if($categoryid==1)
	<td nowrap>{{ $item->workexperience }}[Level {{$item->experiencelevel}}]</td>
	@else
	<td nowrap>{{ $item->experience }}</td>
	@endif
	<td nowrap>{{ $item->qualification }}</td>
	<td nowrap>{{ $item->duration }}</td>
	<td nowrap>{{ $item->budget }}</td>
	<td nowrap>{{ $item->admincharge }}</td>
	<td nowrap>{{ $item->total }}</td>
	<td class="center"
	<a title="" onclick="deleteEois('deleteeois','{{ Crypt::encrypt($item->recordid) }}','{{ __('messages.confirmation') }}','{{ __('messages.deleted') }}')" title=""><i class="fa fa-trash"></i>
	</td>
</tr>
@php $grandtotal	=	$grandtotal+$item->grandtotal; @endphp
@endforeach
@if($i==1)
<tr>
    <td colspan="14" style="text-align:center;">--NO RECORD ADDED--</td>
	
</tr>
@else
<tr>
	<td colspan="14" class="text-left" style="font-size:14px; font-weight:bold;">
		GRAND TOTAL : {{$grandtotal}} INR
		<input type="hidden" name="grandtotal" id="grandtotal" value="{{$grandtotal}}">
		<input type="hidden" name="recs" id="recs" value="{{$i}}">
	</td>
</tr>	
@endif

