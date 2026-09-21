<table class="table table-bordered table-striped table-hover mytable" border="1" style="text-transform: none!important;">
	<thead>
	<tr><td class="padding-5" colspan="8">Resource Details According to Tier-1 Pricing Structure</td></tr>
	<tr class="">
		<td class="padding-5 center" style="width:50px;">S.No.</td>
		<td class="padding-5" nowrap>Sector</td>
		<td class="padding-5" nowrap>Position</td>
		<td class="padding-5" nowrap>Experience & Qualification <i class="fa fa-info-circle" title="As per empanelment"></i></td>
		<td class="padding-5" nowrap>Base Price</td>
		<td class="padding-5" nowrap>Duration</td>
		<td class="padding-5" nowrap>Rate <i class="fa fa-info-circle" title="Man Month Rate Including Tax"></i></td>
		<td class="padding-5" nowrap>Remark <i class="fa fa-info-circle" title="Additional qualifications, depending on experience."></i></td>
	</tr>
	</thead>
	<tbody class="eoidata">
	@php $i=1; $total=0; @endphp
	@foreach($detail as $detail)
	
	<tr class="font-12">
		<td nowrap class="padding-5 center">{{$i}}</td>
		<td nowrap class="padding-5">{{ucwords(strtolower($detail->sectorname))}}</td>
		<td nowrap class="padding-5">{{$detail->consultantposition}}</td>
		<td nowrap class="padding-5">{{$detail->experience}}<br>{{$detail->qualification}}</td>
		<td nowrap class="padding-5 format-indian" data-value="{{ $detail->baseprice }}"></td>
		<td nowrap class="padding-5">{{$detail->duration}} Months</td>
		<td nowrap class="padding-5 format-indian" data-value="{{ $detail->budget }}"></td>
		<td class="padding-5" style="width:250px;">{{$detail->remark}}</td>
	</tr>
	@php $i=$i+1; @endphp
	@endforeach
	<tr>
		<td class="padding-5 font-14 form-label" colspan="6" style="text-align:right;">
			Cost of Resources (Incuding Tax)
		</td>
		<td class="padding-5 font-14 form-label">{{$data->totalmanmonth}}</td>
		<td class="padding-5 font-14 form-label">&nbsp;</td>
	</tr>
	@if($data->adminchargetotal!=0)
	<tr>
		<td class="padding-5 font-14 form-label" colspan="6" style="text-align:right;">CHiPS Admin Charge ({{$pricing->admincharge}}%)</td>
		<td class="padding-5 font-14 form-label">{{$data->adminchargetotal}}</td>
		<td class="padding-5 font-14 form-label">&nbsp;</td>
	</tr>
	<tr>
		<td class="padding-5 font-14 form-label" colspan="6" style="text-align:right;">Grand Total</td>
		<td class="padding-5 font-14 form-label">{{$data->grandtotal}}</td>
		<td class="padding-5 font-14 form-label">&nbsp;</td>
	</tr>
	@endif
	@if($i==1)
	<tr>
		<td colspan="8" class="center">--NO RECORD ADDED--</td>
	</tr>
	@endif
	</tbody>
</table>
