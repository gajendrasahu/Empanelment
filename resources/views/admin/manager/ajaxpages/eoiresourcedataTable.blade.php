@php
$i=1;
$t=0;
@endphp
@if($data)
	@php $r=0; @endphp
	@foreach($data as $rec)
	@php 
		$r=$r+1;
		$withtax	=	number_format(($rec->baseprice+(($rec->baseprice*$pricing->tax)/100)),'2','.','');
	@endphp
	<tr class="mytr" id="item-{{ $r }}">
		<td class="mytd center v-top padding-10" nowrap>{{$r}}</td>
		<td class="mytd v-top padding-10" nowrap>{{ucwords(strtolower($rec->sectorname))}}<br>{{$rec->consultantposition}}</td>
		<td class="mytd v-top padding-10">{{$rec->experience}}@if($rec->qualification!='')<br>{!!$rec->qualification!!}@endif</td>
		<td class="mytd v-top padding-10" nowrap>{{$rec->employmenttype}}</td>
		<td class="mytd v-top padding-10 text-right" nowrap>{{$rec->duration}} Months</td>
		<td class="mytd v-top padding-10 text-right" nowrap>
			
			<i class="fa fa-info-circle info-icon"
			   data-toggle="tooltip"
			   data-baseprice="{{ number_format($rec->baseprice,2,'.','') }}"
			   data-tax="{{ $pricing->tax }}"
			   data-withtax="{{ $withtax }}"
			   data-duration="{{ $rec->duration }}"
			   data-total="{{ number_format($rec->budget,2,'.','') }}">
			</i>

			<span class="format-indian" data-value="{{ number_format($rec->budget,'2','.','') }}"></span>
		</td>
		
		<td class="mytd v-top padding-10">{!!$rec->remark!!}</td>
		<td class="mytd v-top padding-10 center" nowrap><i class="fa fa-trash" onclick="deletePmEois('deletepmeois','{{ Crypt::encrypt($rec->recordid) }}',{{$r}},'{{ __('messages.confirmation') }}','{{ __('messages.deleted') }}')" title=""></i>
		
		
		<i class="fa fa-edit" onclick="editEois('edit_eoi','{{ Crypt::encrypt($rec->recordid) }}')" style="margin-left:10px;"></i>
		</td>
	</tr>
	@php
	@endphp
	@endforeach
@endif
@if($r==0)
@endif
<tr>
	<td class="padding-5" style="text-align:right;" colspan="5"><b>Cost of Resources (Incuding Tax)</b></td>
	<td class="padding-5 text-right"><b>{{$totalmanmonth}}</b></td>
	<td class="padding-5" colspan="2">&nbsp;</td>
</tr>
<tr>
	<td class="padding-5" style="text-align:right;" colspan="5"><b>CHiPS Admin Charge ({{$pricing->admincharge}}%)</b></td>
	<td class="padding-5 text-right"><b>{{$adminchargetotal}}</b></td>
	<td class="padding-5" colspan="2">&nbsp;</td>
</tr>
<tr>
	<td class="padding-5" style="text-align:right;" colspan="5"><b>Grand Total</b></td>
	<td class="padding-5 text-right"><b>{{$grandtotal}}</b></td>
	<td class="padding-5" colspan="2">
		<input type="hidden" name="grandtotal" id="grandtotal" value="{{$grandtotal}}">
		<input type="hidden" name="recs" id="recs" value="{{$r}}">
	
	</td>
</tr>
@if($tier2==1)
<tr>
	<td class="padding-10 form-label tier-choice-cell" colspan="8">
		<div class="tier-choice-wrap">
		<span class="tier-choice-label">Select Tier of Vendors to Float EoI</span>
		<select class="form-control tier-choice-select" name="tier_choice" id="tier_choice">
			<option value="">--Select Tier--</option>
			<option value="1">Tier I</option>
			@if(round(str_replace(",","",$grandtotal))<50000000)
			<option value="2">Tier II</option>
			<option value="3">Both</option>
			@endif
		</select>
	
		</div>
	</td>
</tr>
@endif


<script>

$(this).tooltip({
  html: true,
  boundary: 'window',
  placement: 'auto'
});
$(function () {
  $('.info-icon').each(function () {

    let base   = $(this).data('baseprice');
    let tax    = $(this).data('tax');
    let withTx = $(this).data('withtax');
    let dur    = $(this).data('duration');
    let total  = $(this).data('total');

    let html =
      '<table>' +
        '<tr><td class="padding-5 text-left" nowrap>Base Price (A)</td>' +
            '<td nowrap class="padding-5 text-left format-indian" data-value="' + base + '"></td></tr>' +

        '<tr><td nowrap class="padding-5 text-left" nowrap>Tax (B=A+(A*' + tax + '%))</td>' +
            '<td nowrap class="padding-5 text-left format-indian" data-value="' + withTx + '"></td></tr>' +

        '<tr><td nowrap class="padding-5 text-left">Duration (in months) (C)</td>' +
            '<td nowrap class="padding-5 text-left">' + dur + '</td></tr>' +

        '<tr><td nowrap class="padding-5 text-left">Total (D=B*C)</td>' +
            '<td nowrap class="padding-5 text-left format-indian" data-value="' + total + '"></td></tr>' +
      '</table>';

    $(this)
      .attr('data-original-title', html)
      .tooltip({ html: true, boundary: 'window', placement: 'auto' });
  });
});


document.querySelectorAll('.format-indian').forEach(function(el) {
    el.innerText = formatIndianNumber(el.dataset.value);
});

$(document).on('shown.bs.tooltip', '.info-icon', function () {
    let tooltipId = $(this).attr('aria-describedby');
    let tooltipEl = document.getElementById(tooltipId);

    if (!tooltipEl) return;

    tooltipEl.querySelectorAll('.format-indian').forEach(function (el) {
        el.innerText = formatIndianNumber(el.dataset.value);
    });
});

</script>