@if($data->tier_choice>2)
<table class="table table-bordered table-striped table-hover mytable" border="1" style="text-transform: none!important;">
	<thead>
	<tr><td class="padding-5" colspan="8">Resource Details According to Tier -@if($pricing->tierid==1) 1 @else 2 @endif</td></tr>
	<tr class="">
		<td class="padding-5 center" style="width:50px;">S.No.</td>
		<td class="padding-5" nowrap>Sector & Position</td>
		<td class="padding-5 width-300">Experience & Qualification <i class="fa fa-info-circle" title="As per empanelment"></i></td>
		<td class="padding-5" style="width:130px;" nowrap>Deployment Type</td>
		<td class="padding-5 text-right" nowrap>Duration</td>
		<td class="padding-5 text-right" nowrap>Rate</td>
		<td class="padding-5 width-200">Remark <i class="fa fa-info-circle" title="Additional qualifications, depending on experience."></i></td>
	</tr>
	</thead>
	<tbody class="eoidata">
	@php $i=1; $total=0; @endphp
	@foreach($detail as $detail)
	@php
	$withtax	=	number_format(($detail->baseprice+(($detail->baseprice*$pricing->tax)/100)),'2','.','');
	@endphp
	<tr class="">
		<td nowrap class="padding-5 center">{{$i}}</td>
		<td nowrap class="padding-5">{{ucwords(strtolower($detail->sectorname))}}<br>{{$detail->consultantposition}}</td>
		<td class="padding-5">{{$detail->experience}}<br>{!!$detail->qualification!!}</td>
		<td class="padding-5" nowrap>{{$detail->employmenttype}}</td>
		<td nowrap class="padding-5 text-right">{{$detail->duration}} Months</td>
		<td class="padding-5 text-right" nowrap>
			
			<i class="fa fa-info-circle info-icon"
			   data-toggle="tooltip"
			   data-baseprice="{{ number_format($detail->baseprice,2,'.','') }}"
			   data-tax="{{ $pricing->tax }}"
			   data-withtax="{{ $withtax }}"
			   data-duration="{{ $detail->duration }}"
			   data-total="{{ number_format($detail->budget,2,'.','') }}">
			</i>

			<span class="format-indian" data-value="{{ number_format($detail->budget,'2','.','') }}"></span>
		</td>
		
		<td class="padding-5 width-200">{!!$detail->remark!!}</td>
	</tr>
	@php $i=$i+1; @endphp
	@endforeach
	<tr>
		<td class="padding-5 font-14 form-label" colspan="5" style="text-align:right;">
			Cost of Resources (Incuding Tax)
		</td>
		<td class="padding-5 font-14 form-label">{{$data->totalmanmonth}}</td>
		<td class="padding-5 font-14 form-label">&nbsp;</td>
	</tr>
	@if($data->adminchargetotal!=0)
	<tr>
		<td class="padding-5 font-14 form-label" colspan="5" style="text-align:right;">CHiPS Admin Charge ({{$pricing->admincharge}}%)</td>
		<td class="padding-5 font-14 form-label">{{$data->adminchargetotal}}</td>
		<td class="padding-5 font-14 form-label">&nbsp;</td>
	</tr>
	<tr>
		<td class="padding-5 font-14 form-label" colspan="5" style="text-align:right;">Grand Total</td>
		<td class="padding-5 font-14 form-label">{{$data->grandtotal}}</td>
		<td class="padding-5 font-14 form-label">&nbsp;</td>
	</tr>
	@endif
	@if($i==1)
	<tr>
		<td colspan="7" class="center">--NO RECORD ADDED--</td>
	</tr>
	@endif
	</tbody>
</table>
@elseif($data->tier_choice==$pricing->tierid)
<table class="table table-bordered table-striped table-hover" border="1" style="text-transform: none!important;">
	<thead>
	<tr><td class="padding-5" colspan="8">Resource Details According to Tier -@if($pricing->tierid==1) 1 @else 2 @endif</td></tr>
	<tr class="">
		<td class="padding-5 center" style="width:50px;">S.No.</td>
		<td class="padding-5" nowrap>Sector & Position</td>
		<td class="padding-5 width-300">Experience & Qualification <i class="fa fa-info-circle" title="As per empanelment"></i></td>
		<td class="padding-5" nowrap>Deployment Type</td>
		<td class="padding-5 text-right" nowrap>Duration</td>
		<td class="padding-5 text-right" nowrap>Rate</td>
		<td class="padding-5 width-200">Remark <i class="fa fa-info-circle" title="Additional qualifications, depending on experience."></i></td>
	</tr>
	</thead>
	<tbody class="eoidata">
	@php $i=1; $total=0; @endphp
	@foreach($detail as $detail)
	@php
	$withtax	=	number_format(($detail->baseprice+(($detail->baseprice*$pricing->tax)/100)),'2','.','');
	@endphp
	<tr class="">
		<td nowrap class="padding-5 center">{{$i}}</td>
		<td nowrap class="padding-5">{{ucwords(strtolower($detail->sectorname))}}<br>{{$detail->consultantposition}}</td>
		<td class="padding-5">{{$detail->experience}}<br>{!!$detail->qualification!!}</td>
		<td class="padding-5" nowrap>{{$detail->employmenttype}}</td>
		<td nowrap class="padding-5 text-right">{{$detail->duration}} Months</td>
		<td class="padding-5 text-right" nowrap>
			
			<i class="fa fa-info-circle info-icon"
			   data-toggle="tooltip"
			   data-baseprice="{{ number_format($detail->baseprice,2,'.','') }}"
			   data-tax="{{ $pricing->tax }}"
			   data-withtax="{{ $withtax }}"
			   data-duration="{{ $detail->duration }}"
			   data-total="{{ number_format($detail->budget,2,'.','') }}">
			</i>

			<span class="format-indian" data-value="{{ number_format($detail->budget,'2','.','') }}"></span>
		</td>
		
		<td class="padding-5 width-200">{!!$detail->remark!!}</td>
	</tr>
	@php $i=$i+1; @endphp
	@endforeach
	<tr>
		<td class="padding-5 font-14 form-label" colspan="5" style="text-align:right;">
			Cost of Resources (Incuding Tax)
		</td>
		<td class="padding-5 font-14 form-label">{{$data->totalmanmonth}}</td>
		<td class="padding-5 font-14 form-label">&nbsp;</td>
	</tr>
	@if($data->adminchargetotal!=0)
	<tr>
		<td class="padding-5 font-14 form-label" colspan="5" style="text-align:right;">CHiPS Admin Charge ({{$pricing->admincharge}}%)</td>
		<td class="padding-5 font-14 form-label">{{$data->adminchargetotal}}</td>
		<td class="padding-5 font-14 form-label">&nbsp;</td>
	</tr>
	<tr>
		<td class="padding-5 font-14 form-label" colspan="5" style="text-align:right;">Grand Total</td>
		<td class="padding-5 font-14 form-label">{{$data->grandtotal}}</td>
		<td class="padding-5 font-14 form-label">&nbsp;</td>
	</tr>
	@endif
	@if($i==1)
	<tr>
		<td colspan="7" class="center">--NO RECORD ADDED--</td>
	</tr>
	@endif
	</tbody>
</table>
@endif
<script src="{{ asset('panel/assets/js/datatablePaginationWithFilter.js') }}"></script>
<script>
setTimeout(function(){

$(this).tooltip({
  html: true,
  boundary: 'window',
  placement: 'auto'
});
    

$(function () {
  $('.info-icon').each(function () {

    let base   = formatIndianNumber($(this).data('baseprice') || 0);
    let tax    = $(this).data('tax');
    let withTx = formatIndianNumber($(this).data('withtax') || 0);
    let dur    = $(this).data('duration');
    let total  = formatIndianNumber($(this).data('total') || 0);

    let html =
      '<table>' +
        '<tr><td class="padding-5 text-left" nowrap>Base Price (A)</td>' +
            '<td nowrap class="padding-5 text-left format-indian" data-value="' + base + '">' + base + '</td></tr>' +

        '<tr><td nowrap class="padding-5 text-left" nowrap>Tax (B=A+(A*' + tax + '%))</td>' +
            '<td nowrap class="padding-5 text-left format-indian" data-value="' + withTx + '">' + withTx + '</td></tr>' +

        '<tr><td nowrap class="padding-5 text-left">Duration (in months) (C)</td>' +
            '<td nowrap class="padding-5 text-left">' + dur + '</td></tr>' +

        '<tr><td nowrap class="padding-5 text-left">Total (D=B*C)</td>' +
            '<td nowrap class="padding-5 text-left format-indian" data-value="' + total + '">' + total + '</td></tr>' +
      '</table>';

		$(this).tooltip({
		  html: true,
		  container: 'body',
		  placement: 'auto',
		  title: html
		});
  });
  
  
});
	
	
},2000);

</script>
