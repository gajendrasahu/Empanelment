@if($data->tier_choice>2)
<table class="table table-bordered table-striped table-hover mytable" border="1" style="text-transform: none!important;">
	<thead>
	<tr><td class="padding-5" colspan="10">Resource Details According to Tier -@if($pricing->tierid==1) 1 @else 2 @endif</td></tr>
	<tr class="">
		<td class="padding-5 center" style="width:50px;">S.No.</td>
		<td class="padding-5" nowrap>Position</td>
		<td class="padding-5" nowrap>Experience <i class="fa fa-info-circle" title="As per empanelment"></i></td>
		<td class="padding-5 width-300">Qualification <i class="fa fa-info-circle" title="As per empanelment"></i></td>
		<td class="padding-5 text-right" style="width:120px;" nowrap>Base Price<br>(A)</td>
		<td class="padding-5 text-right" style="width:120px;" nowrap>Operating Charge<br>(B=A+A*{{$pricing->operatingmargin}}%)</td>
		<td class="padding-5 text-right" style="width:120px;" nowrap>Tax<br>(C=B+B*{{$pricing->tax}}%)</td>
		<td class="padding-5 text-right" style="width:80px;" nowrap>Duration<br>(D)</td>
		<td class="padding-5 text-right" style="width:120px;" nowrap>Rate <i class="fa fa-info-circle" title="Man Month Rate Including Tax"></i><br>(E=C*D)</td>
		<td class="padding-5 width-200">Remark <i class="fa fa-info-circle" title="Additional qualifications, depending on experience."></i></td>
	</tr>
	</thead>
	<tbody class="eoidata">
	@php $i=1; $total=0; @endphp
	@foreach($detail as $detail)
	@php
		$withoperating	=	number_format(($detail->baseprice+(($detail->baseprice*$pricing->operatingmargin)/100)),'2','.','');
		$withtax		=	number_format(($withoperating+(($withoperating*$pricing->tax)/100)),'2','.','');	
	@endphp
	<tr class="font-12">
		<td nowrap class="padding-5 center">{{$i}}</td>
		<td nowrap class="padding-5">{!!$detail->role!!}</td>
		<td nowrap class="padding-5">Level-{{$detail->experiencelevel}}<br>{{$detail->experience}}</td>
		<td class="padding-5">{!!$detail->qualification!!}</td>
		<td nowrap  class="padding-5 format-indian text-right" data-value="{{ number_format($detail->baseprice,'2','.','') }}" nowrap></td>
		<td nowrap  class="padding-5 format-indian text-right" data-value="{{ $withoperating }}" nowrap></td>
		<td nowrap  class="padding-5 format-indian text-right" data-value="{{ $withtax }}" nowrap></td>
		<td nowrap  class="padding-5 text-right" nowrap>{{$detail->duration}} Months</td>
		<td nowrap  class="padding-5 format-indian text-right" data-value="{{ number_format($detail->budget,'2','.','') }}" nowrap></td>
		<td class="padding-5" style="width:200px;">{!!$detail->remark!!}</td>
	</tr>
	@php $i=$i+1; @endphp
	@endforeach
	<tr>
		<td class="padding-5 font-14 form-label" colspan="8" style="text-align:right;">
			Cost of Resources (Incuding Operating Margin+Tax)
		</td>
		<td class="padding-5 font-14 form-label text-right">{{$data->totalmanmonth}}</td>
		<td class="padding-5 font-14 form-label">&nbsp;</td>
	</tr>
	@if($data->adminchargetotal!=0)
	<tr>
		<td class="padding-5 font-14 form-label" colspan="8" style="text-align:right;">CHiPS Admin Charge ({{$pricing->admincharge}}%)</td>
		<td class="padding-5 font-14 form-label text-right">{{$data->adminchargetotal}}</td>
		<td class="padding-5 font-14 form-label">&nbsp;</td>
	</tr>
	<tr>
		<td class="padding-5 font-14 form-label" colspan="8" style="text-align:right;">Grand Total</td>
		<td class="padding-5 font-14 form-label text-right">{{$data->grandtotal}}</td>
		<td class="padding-5 font-14 form-label">&nbsp;</td>
	</tr>
	@endif
	@if($i==1)
	<tr>
		<td colspan="10" class="center">--NO RECORD ADDED--</td>
	</tr>
	@endif
	</tbody>
</table>
@elseif($data->tier_choice==$pricing->tierid)
<table class="table table-bordered table-striped table-hover mytable" border="1" style="text-transform: none!important;">
	<thead>
	<tr><td class="padding-5" colspan="10">Resource Details According to Tier -@if($pricing->tierid==1) 1 @else 2 @endif</td></tr>
	<tr class="">
		<td class="padding-5 center" style="width:50px;">S.No.</td>
		<td class="padding-5" nowrap>Position</td>
		<td class="padding-5" nowrap>Experience <i class="fa fa-info-circle" title="As per empanelment"></i></td>
		<td class="padding-5 width-300">Qualification <i class="fa fa-info-circle" title="As per empanelment"></i></td>
		<td class="padding-5 text-right" style="width:120px;" nowrap>Base Price<br>(A)</td>
		<td class="padding-5 text-right" style="width:120px;" nowrap>Operating Charge<br>(B=A+A*{{$pricing->operatingmargin}}%)</td>
		<td class="padding-5 text-right" style="width:120px;" nowrap>Tax<br>(C=B+B*{{$pricing->tax}}%)</td>
		<td class="padding-5 text-right" style="width:80px;" nowrap>Duration<br>(D)</td>
		<td class="padding-5 text-right" style="width:120px;" nowrap>Rate <i class="fa fa-info-circle" title="Man Month Rate Including Tax"></i><br>(E=C*D)</td>
		<td class="padding-5 width-200">Remark <i class="fa fa-info-circle" title="Additional qualifications, depending on experience."></i></td>
	</tr>
	</thead>
	<tbody class="eoidata">
	@php $i=1; $total=0; @endphp
	@foreach($detail as $detail)
	@php
		$withoperating	=	number_format(($detail->baseprice+(($detail->baseprice*$pricing->operatingmargin)/100)),'2','.','');
		$withtax		=	number_format(($withoperating+(($withoperating*$pricing->tax)/100)),'2','.','');	
	@endphp
	<tr class="font-12">
		<td nowrap class="padding-5 center">{{$i}}</td>
		<td nowrap class="padding-5">{!!$detail->role!!}</td>
		<td nowrap class="padding-5">Level-{{$detail->experiencelevel}}<br>{{$detail->experience}}</td>
		<td class="padding-5">{!!$detail->qualification!!}</td>
		<td nowrap  class="padding-5 format-indian text-right" data-value="{{ number_format($detail->baseprice,'2','.','') }}" nowrap></td>
		<td nowrap  class="padding-5 format-indian text-right" data-value="{{ $withoperating }}" nowrap></td>
		<td nowrap  class="padding-5 format-indian text-right" data-value="{{ $withtax }}" nowrap></td>
		<td nowrap  class="padding-5 text-right" nowrap>{{$detail->duration}} Months</td>
		<td nowrap  class="padding-5 format-indian text-right" data-value="{{ number_format($detail->budget,'2','.','') }}" nowrap></td>
		<td class="padding-5" style="width:200px;">{!!$detail->remark!!}</td>
	</tr>
	@php $i=$i+1; @endphp
	@endforeach
	<tr>
		<td class="padding-5 font-14 form-label" colspan="8" style="text-align:right;">
			Cost of Resources (Incuding Operating Margin+Tax)
		</td>
		<td class="padding-5 font-14 form-label text-right">{{$data->totalmanmonth}}</td>
		<td class="padding-5 font-14 form-label">&nbsp;</td>
	</tr>
	@if($data->adminchargetotal!=0)
	<tr>
		<td class="padding-5 font-14 form-label" colspan="8" style="text-align:right;">CHiPS Admin Charge ({{$pricing->admincharge}}%)</td>
		<td class="padding-5 font-14 form-label text-right">{{$data->adminchargetotal}}</td>
		<td class="padding-5 font-14 form-label">&nbsp;</td>
	</tr>
	<tr>
		<td class="padding-5 font-14 form-label" colspan="8" style="text-align:right;">Grand Total</td>
		<td class="padding-5 font-14 form-label text-right">{{$data->grandtotal}}</td>
		<td class="padding-5 font-14 form-label">&nbsp;</td>
	</tr>
	@endif
	@if($i==1)
	<tr>
		<td colspan="10" class="center">--NO RECORD ADDED--</td>
	</tr>
	@endif
	</tbody>
</table>

@endif