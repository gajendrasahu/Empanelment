<table class="requirements-table">
	<thead>
		<tr><th colspan="8">Resource Details According to Tier-@if($pricing->tierid==1) 1 @else 2 @endif Pricing Structure</th></tr>
		</tr>
		<tr>
			<th class="center">S.No.</th>
			<th>Sector</th>
			<th>Position</th>
			<th>Experience & Qualification</th>
			<th>Remark</th>
			<th>Base Price</th>
			<th>Duration</th>
			<th class="f-right">Man Month Rate (Including tax)</th>
		</tr>
	</thead>
	<tbody>
	@php
	$total	=	0;
	@endphp
	@foreach($detail as $eoi)
		<tr>
			<td class="center">{{$loop->iteration}}</td>
			<td>{{ucwords(strtolower($eoi->sectorname))}}</td>
			<td>{{$eoi->consultantposition}}</td>
			<td>{{$eoi->experience}}<br>{!!$eoi->qualification!!}</td>
			<td>{!!$eoi->remark!!}</td>
			<td class="f-right format-indian" data-value="{{ $eoi->baseprice }}"></td>
			<td>{{$eoi->duration}} Months</td>
			<td class="f-right format-indian" data-value="{{ $eoi->budget }}"></td>
		</tr>
		@php
		@endphp
	@endforeach
	@php
	@endphp
	</tbody>
	<tfoot>
		<tr>
			<td class="f-right info-label" colspan="7">Cost of Resources (Incuding Tax)</td>
			<td id="total-cell" class="f-right info-label"><i class="fa fa-inr"></i> {{$data->totalmanmonth}}</td>
		</tr>
		<tr>
			<td class="f-right info-label" colspan="7">CHiPS Admin Charge ({{$pricing->admincharge}}%)</td>
			<td id="admincost-cell" class="f-right info-label">{{$data->adminchargetotal}}</td>
		</tr>
		<tr class="total-budget">
			<td class="f-right info-label" colspan="7">Grand Total</td>
			<td id="grand-cell" class="f-right info-label">{{$data->grandtotal}}</td>
		</tr>
	</tfoot>
</table>
