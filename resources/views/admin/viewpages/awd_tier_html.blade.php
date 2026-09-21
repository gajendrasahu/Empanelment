<table class="mytable pd-8" border="1" style="text-transform: none!important;">
	<thead>
	<tr><td colspan="5"><b>Resource Detail</b></td></tr>
	<tr class="">
		<td class="center" style="width:50px;"><b>S.No.</b></td>
		<td nowrap><b>Position</td>
		<td nowrap><b>Experience</b> <i class="fa fa-info-circle" title="As per empanelment"></i></td>
		<td nowrap><b>Experience Level</b></td>
		<td class="text-right" style="width:80px;" nowrap><b>Duration (D)</b></td>
	</tr>
	</thead>
	<tbody class="eoidata">
	@php $i=1; $total=0; @endphp
	@foreach($detail as $rec)
	@php
		$withoperating	=	number_format(($rec->baseprice+(($rec->baseprice*$pricing->operatingmargin)/100)),'2','.','');
		$withtax		=	number_format(($withoperating+(($withoperating*$pricing->tax)/100)),'2','.','');	
	@endphp
	<tr class="font-12">
		<td nowrap class="center v-top" rowspan="3">{{$i}}</td>
		<td nowrap class="v-top">{!!$rec->role!!}</td>
		<td nowrap>{{$rec->experience}}</td>
		<td nowrap>Level-{{$rec->experiencelevel}}</td>
		<td nowrap class="text-right" nowrap>{{$rec->duration}} Months</td>
	</tr>
	<tr>
		<td colspan="5">Qualification : @if($rec->qualification) {!! $rec->qualification !!} @endif</td>
	</tr>
	<tr>
		<td colspan="5">Remark : @if($rec->remark) {!! $rec->remark !!} @endif</td>
	</tr>
	@php $i=$i+1; @endphp
	@endforeach
	@if($i==1)
	<tr>
		<td colspan="5" class="center">--NO RECORD ADDED--</td>
	</tr>
	@endif
	</tbody>
	@if(session('departmentId'))
	<tr>
		<td colspan="5" class="text-right">
			<a href="{{ route('price.comparison',[Crypt::encrypt('No'), Crypt::encrypt($data->requestid)]) }}" target="_blank">
				<button type="button" class="btn btn-info" style="width:120px;">
					<i class="fa fa-table"></i> View Price
				</button>
			</a>
		</td>
	</tr>
		
	@endif
	
</table>
