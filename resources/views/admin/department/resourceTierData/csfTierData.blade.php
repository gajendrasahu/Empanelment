@php
$t=1;
$selectedTier = $selectedTier ?? null;
$req_id = $req_id ?: 0;
@endphp
<div class="table-responsive" style="margin-top:20px;">
<table class="table table-bordered font-13 resource-table">
<tr class="myheadbg">
	<td colspan="8" class="padding-10 form-label resource-table-title">
		The resource details are based on the {{$tiername}} pricing structure.
	</td>
</tr>
<tr class="myheadbg">
    <td class="padding-10 form-label center" nowrap style="width:30px;">S.No.</td>
	<td class="padding-10 form-label" style="width:130px;" nowrap>Sector</td>
	<td class="padding-10 form-label" style="width:130px;" nowrap>Position</td>
	<td class="padding-10 form-label" style="width:130px;" nowrap>Experience</td>
	<td class="padding-10 form-label" style="width:130px;" nowrap>Deployment Type</td>
	<td class="padding-10 form-label text-right" style="width:80px;" nowrap>Duration</td>
	<td class="padding-10 form-label" style="width:30px;" nowrap></td>
</tr>
<tbody>
@if($data)
	@php $r=0; @endphp
	@foreach($data as $rec)
	@php 
		$r=$r+1; 
		$withtax	=	number_format($rec->includingtax,'2','.','');
	@endphp
	<tr class="mytr" id="item-{{ $r }}">
		<td class="mytd v-top center padding-10" rowspan="3" nowrap>{{$r}}</td>
		<td class="mytd v-top padding-10" nowrap>{{ucwords(strtolower($rec->sectorname))}}</td>
		<td class="mytd v-top padding-10" nowrap>{{$rec->consultantposition}}</td>
		<td class="mytd v-top padding-10">{{$rec->experience}}</td>
		<td class="mytd v-top padding-10" nowrap>{{ucwords(strtolower($rec->employmenttype))}}</td>
		<td class="mytd v-top padding-10 text-right" nowrap>{{$rec->duration}} Months</td>
	
		<td class="mytd v-top padding-10 center" nowrap>
		@if($selectedTier)
		<i class="fa fa-trash" onclick="deleteEois('delete_eois','{{ Crypt::encrypt($rec->recordid) }}',{{$r}},'{{ __('messages.confirmation') }}','{{ __('messages.deleted') }}')" title=""></i>
		@else
		<i class="fa fa-trash" onclick="deleteEois('deleteeois','{{ Crypt::encrypt($rec->recordid) }}',{{$r}},'{{ __('messages.confirmation') }}','{{ __('messages.deleted') }}')" title=""></i>
		@endif
		
		@if($selectedTier)
		<i class="fa fa-edit" onclick="editEois('edit_eoi','{{ Crypt::encrypt($rec->recordid) }}')" style="margin-left:10px;"></i>
		@else
		<i class="fa fa-edit" onclick="editEois('edit_eoi','{{ Crypt::encrypt($rec->recordid) }}')" style="margin-left:10px;"></i>
		@endif
		
		</td>
	</tr>
	<tr>
		<td>
			<i class="fa fa-info-circle" title="As per empanelment"></i> Qualification <i class="fa fa-angle-double-right"></i>
		</td>
		<td colspan="5">@if($rec->qualification!='') {!!$rec->qualification!!} @endif</td>
	</tr>
	<tr>
		<td>
			<i class="fa fa-info-circle" title="Additional qualification"></i> Remark <i class="fa fa-angle-double-right"></i>
		</td>
		<td colspan="5">@if($rec->remark!=''){!!$rec->remark!!} @endif</td>	
	</tr>
	@php
	@endphp
	@endforeach
@endif
@if($data->count()==0)
<tr><td class="text-center" colspan="8">--No Record Found--</td></tr>
@endif
<tr>
	<td class="padding-10 form-label tier-choice-cell" colspan="2">
		@if($data->count()!=0)
		<button type="button" class="btn btn-info" style="width:100%;" onclick="showPrice('{{ route('showprice.comparison',[Crypt::encrypt($isTemp), Crypt::encrypt($req_id)]) }}')">
			<i class="fa fa-table"></i> View Price
		</button>
		@endif
	</td>
	<td class="padding-10 form-label tier-choice-cell" colspan="6">
		<div class="tier-choice-wrap">
		<span class="tier-choice-label">Select Tier of Firms to Float EoI</span>
		<input type="hidden" name="recs" id="recs" value="{{$r}}">
		<select class="form-control tier-choice-select" name="tier_choice" id="tier_choice">
			<option value="">--Select Tier--</option>
			<option value="1" @if($selectedTier) @if($selectedTier==1) selected @endif @endif>Tier I</option>
			@if(round(str_replace(",","",$grandtotal))<50000000)
			<option value="2" @if($selectedTier) @if($selectedTier==2) selected @endif @endif>Tier II</option>
			<option value="3" @if($selectedTier) @if($selectedTier==3) selected @endif @endif>Both</option>
			@endif
		</select>
	
		</div>
	</td>
</tr>
</tbody>

</table>
</div>


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
        '<tr><td nowrap class="padding-5 text-left" nowrap>Man Month Rate (Inc. Tax) (A)</td>' +
            '<td nowrap class="padding-5 text-left format-indian" data-value="' + withTx + '"></td></tr>' +

        '<tr><td nowrap class="padding-5 text-left">Duration (in months) (B)</td>' +
            '<td nowrap class="padding-5 text-left">' + dur + '</td></tr>' +

        '<tr><td nowrap class="padding-5 text-left">Total (C=A*B)</td>' +
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