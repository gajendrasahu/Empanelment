@php
$i=1;
$t=0;
$grandtotal=0;
@endphp
@if($data)
	@php $r=0; @endphp
	@foreach($data as $rec)
	@php $r=$r+1; @endphp
	<tr class="mytr" id="item-{{ $r }}">
		<td class="mytd v-top center padding-10" nowrap>{{$r}}</td>
		<td class="mytd v-top padding-10" nowrap>{{ucwords(strtolower($rec->sectorname))}}</td>
		<td class="mytd v-top padding-10" nowrap>{{$rec->consultantposition}}<br>{{$rec->experience}}@if($rec->qualification!='')<br>{{$rec->qualification}} @endif</td>
		<td class="mytd v-top padding-10" nowrap>{{ucwords(strtolower($rec->employmenttype))}}</td>
		<td style="text-align:right;" class="mytd v-top padding-10 format-indian" nowrap data-value="{{ $rec->remuneration }}"></td>
		<td class="mytd v-top padding-10" nowrap>{{$rec->duration}} Months</td>
		<td style="text-align:right;" class="mytd v-top padding-10 format-indian" nowrap data-value="{{ $rec->totalremuneration }}"></td>
		<td style="text-align:right;" class="mytd v-top padding-10 format-indian" nowrap data-value="{{ $rec->withtax }}"></td>
		<td style="text-align:right;" class="mytd v-top padding-10 format-indian" nowrap data-value="{{ $rec->grandtotal }}"></td>
		<td class="mytd v-top padding-10" nowrap>{{$rec->remark}}</td>
		<td class="mytd v-top padding-10 center" nowrap><i class="fa fa-trash" onclick="deleteEois('deleteeois','{{ Crypt::encrypt($rec->recordid) }}',{{$r}},'{{ __('messages.confirmation') }}','{{ __('messages.deleted') }}')" title=""></i></td>
	</tr>
	@php
	$grandtotal	=	$grandtotal+$rec->grandtotal;
	@endphp
	@endforeach
@endif
@if($r==0)
@endif
	<tr>
		<td class="mytd v-top padding-10 form-label" colspan="8" style="text-align:right;">Grand Total</td>
		<td class="mytd v-top padding-10 form-label format-indian" style="text-align:right;" data-value="{{$grandtotal}}"></td>
		<td colspan="2">&nbsp;</td>
	</tr>
<script>
document.querySelectorAll('.format-indian').forEach(function(el) {
    el.innerText = formatIndianNumber(el.dataset.value);
});

</script>