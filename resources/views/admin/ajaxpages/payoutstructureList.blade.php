@php
$i=1
@endphp
<div class="row">
	<table style="width:100%; border:1px solid #eee; border-collapse:collapse;" border="1">
		<tr class="myheadbg">
			<td class="center" style="width:40px;">S.NO.</td>
			<td class="mytdleftwhite">SUB CATEGORY</td>
			<td class="mytdleftwhite">OBJECTIVE</td>
			<td class="mytdcenterwhite" style="width:140px;">PAYOUT / INCENTIVE</td>
			<td class="mytdcenterwhite" style="width:100px;">DEDUCTION</td>
			<td class="mytdleftwhite" style="width:40px;"></td>
		</tr>
		@php $i=0; @endphp
		@foreach ($data as $item)
		@php $i=$i+1; @endphp
		<tr>
			<td class="center">{{$i}}</td>
			<td class="mytdleft">{{$item->category}}</td>
			<td class="mytdleft">{{$item->objective}}</td>
			<td class="center">{{$item->cramount}}</td>
			<td class="center">{{$item->dramount}}</td>
			<td class="center">
				<input type="checkbox" id="{{$item->subcategoryid}}" onclick="RemoveStructure({{$item->tierid}},{{$item->categoryid}},{{$item->subcategoryid}},{{$item->payouttypeid}},{{$item->objectiveid}},'{{ route('remove.payoutstructure') }}')" style="vertical-align:text-top; font-size:14px;" checked>
			</td>
		</tr>
		@endforeach
		@if($i==0)
		<tr class="">
			<td class="center" colspan="5">--NO RECORD FOUND--</td>
		</tr>
		@endif
	</table>
</div>

