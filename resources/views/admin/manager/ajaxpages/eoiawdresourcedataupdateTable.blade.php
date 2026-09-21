@php
$i=1;
$t=0;
@endphp
@if($data)
	@php $r=0; $total_manmonth=0; @endphp
	@foreach($data as $rec)
	@php 
		$r=$r+1;
		$withoperating	=	number_format(($rec->baseprice+(($rec->baseprice*$pricing->operatingmargin)/100)),'2','.','');
		$withtax		=	number_format(($withoperating+(($withoperating*$pricing->tax)/100)),'2','.','');
		$total_manmonth	=	$total_manmonth+($withtax*$rec->duration);
	@endphp
	<tr class="mytr" id="item-{{ $r }}">
		<td class="mytd center v-top padding-10" nowrap>{{$r}}</td>
		<td class="mytd v-top padding-10" nowrap>{{ucwords(strtolower($rec->role))}}<br>Level-{{$rec->experiencelevel}}</td>
		<td class="mytd v-top padding-10">@if($rec->qualification!=''){{$rec->experience}}<br>{!!$rec->qualification!!}@endif</td>
		<td class="mytd v-top padding-10" nowrap>{{$rec->employmenttype}}</td>
		<td class="mytd v-top padding-10 text-right" nowrap>{{$rec->duration}} Months</td>

		<td class="mytd v-top padding-10 text-right" nowrap>

			<i class="fa fa-info-circle info-icon"
			   data-toggle="tooltip"
			   data-baseprice="{{ number_format($rec->baseprice,2,'.','') }}"
			   data-operating="{{ $withoperating }}"
			   data-tax="{{ $withtax }}"
			   data-duration="{{ $rec->duration }}"
			   data-total="{{ number_format($withtax*$rec->duration,2,'.','') }}"
			   data-operatingmargin="{{ $pricing->operatingmargin }}"
			   data-taxpercent="{{ $pricing->tax }}">
			</i>
			
			<span class="format-indian" data-value="{{ number_format($withtax*$rec->duration,'2','.','') }}"></span>
		</td>
		
		<td class="mytd v-top padding-10">{!!$rec->remark!!}</td>
		<td class="mytd v-top padding-10 center" nowrap><i class="fa fa-trash" onclick="deletePmEois('delete_pmeois','{{ Crypt::encrypt($rec->recordid) }}',{{$r}},'{{ __('messages.confirmation') }}','{{ __('messages.deleted') }}')" title=""></i>
		
		
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
	<td class="padding-5" style="text-align:right;" colspan="5"><b>Cost of Resources (Incuding Operating Margin+Tax)</b></td>
	<td class="padding-5 text-right"><b class="format-indian" data-value="{{number_format($total_manmonth,'2','.','')}}"></b></td>
	<td class="padding-5" colspan="2">&nbsp;</td>
</tr>
@if($adminchargetotal!=0)
<tr>
	<td class="padding-5" style="text-align:right;" colspan="5"><b>CHiPS Admin Charge ({{$admincharge}}%)</b></td>
	<td class="padding-5"><b>{{$adminchargetotal}}</b></td>
	<td class="padding-5" colspan="2">&nbsp;</td>
</tr>
<tr>
	<td class="padding-5" style="text-align:right;" colspan="5"><b>Grand Total</b></td>
	<td class="padding-5"><b>{{$grandtotal}}</b></td>
	<td class="padding-5" colspan="2">
		<input type="hidden" name="grandtotal" id="grandtotal" value="{{$grandtotal}}">
		<input type="hidden" name="recs" id="recs" value="{{$r}}">
	
	</td>
</tr>
@endif
@if($tier2==1)
<tr>
	<td class="padding-10 form-label tier-choice-cell" colspan="8">
		<div class="tier-choice-wrap">
		<span class="tier-choice-label">Select Tier of Vendors to Float EoI</span>
		<input type="hidden" name="recs" id="recs" value="{{$r}}">
		<select class="form-control tier-choice-select" name="tier_choice" id="tier_choice">
			<option value="">--Select Tier--</option>
			<option value="1" @if($eoi->tier_choice==1) selected @endif>Tier I</option>
			<option value="2" @if($eoi->tier_choice==2) selected @endif>Tier II</option>
			<option value="3" @if($eoi->tier_choice==3) selected @endif>Both</option>
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
    let base    = $(this).data('baseprice');
    let operating = $(this).data('operating');
    let tax     = $(this).data('tax');
    let dur     = $(this).data('duration');
    let total   = $(this).data('total');
    let operMargin = $(this).data('operatingmargin');
    let taxPercent = $(this).data('taxpercent');

    // Build tooltip table HTML
    let html =
      '<table>' +
        '<tr><td class="padding-5 text-left">Base Price (A)</td>' +
            '<td class="padding-5 text-left format-indian" data-value="' + base + '"></td></tr>' +

        '<tr><td class="padding-5 text-left">Operating Margin (B=A+(A*' + operMargin + ')%)</td>' +
            '<td class="padding-5 text-left format-indian" data-value="' + operating + '"></td></tr>' +

        '<tr><td class="padding-5 text-left">Tax (C=B+(B*' + taxPercent + '%))</td>' +
            '<td class="padding-5 text-left format-indian" data-value="' + tax + '"></td></tr>' +

        '<tr><td class="padding-5 text-left">Duration (D)</td>' +
            '<td class="text-left">' + dur + '</td></tr>' +

        '<tr><td class="padding-5 text-left">Total (E=C*D)</td>' +
            '<td class="padding-5 text-left format-indian" data-value="' + total + '"></td></tr>' +
      '</table>';

    $(this)
      .attr('data-original-title', html)
      .tooltip({
        html: true,
        boundary: 'window',
        placement: 'auto'
      });
  });
});


document.querySelectorAll('.format-indian').forEach(function(el) {
    el.innerText = formatIndianNumber(el.dataset.value);
});

$(document).on('shown.bs.tooltip', '.info-icon', function () {
    let tooltipId = $(this).attr('aria-describedby');
    let tooltipEl = document.getElementById(tooltipId);
    if (!tooltipEl) return;

    tooltipEl.querySelectorAll('.format-indian').forEach(function(el) {
        el.innerText = formatIndianNumber(el.dataset.value);
    });
});

</script>