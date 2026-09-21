<table class="mytable pd-8" border="1" style="text-transform: none!important;">
	<thead>
	<tr><td colspan="6"><b>The resource details are based on the Tier - {{$tierid}} pricing structure.</b></td></tr>
	<tr class="">
		<td class="center" style="width:50px;">S.No.</td>
		<td nowrap>Sector & Position</td>
		<td class="width-300">Experience & Qualification <i class="fa fa-info-circle" title="As per empanelment"></i></td>
		<td style="width:130px;" nowrap>Deployment Type</td>
		<td class="text-left" nowrap>Duration</td>
		<!--<td class="text-right" nowrap>Rate</td>-->
	</tr>
	</thead>
	<tbody class="eoidata">
	@php $i=1; $total=0; @endphp
	@foreach($detail as $rec)
	@php
	$withtax	=	number_format(($rec->baseprice+(($rec->baseprice*$pricing->tax)/100)),'2','.','');
	@endphp
	<tr class="">
		<td rowspan="3" nowrap class="center">{{$i}}</td>
		<td nowrap>{{ucwords(strtolower($rec->sectorname))}}<br>{{$rec->consultantposition}}</td>
		<td>{{$rec->experience}}</td>
		<td nowrap>{{$rec->employmenttype}}</td>
		<td nowrap class="text-left">{{$rec->duration}} Months</td>
		<!--
		<td class="text-right" nowrap>
			
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
		-->
		
	</tr>
	
	<tr><td colspan="4"><b>Qualification</b> : @if($rec->qualification!='') {!! preg_replace('/ style="[^"]*"/i', '', $rec->qualification) !!} @endif</td></tr>
	
	
	<tr><td colspan="4"><b>Remark</b> : @if($rec->remark!='') {!! preg_replace('/ style="[^"]*"/i', '', $rec->remark) !!} @endif</td></tr>
	
	
	@php $i=$i+1; @endphp
	@endforeach
	<!--
	<tr>
		<td class="font-14 form-label" colspan="5" style="text-align:right;">
			Cost of Resources (Incuding Tax)
		</td>
		<td class="font-14 form-label text-right">{{$data->totalmanmonth}}</td>
	</tr>
	@if($data->adminchargetotal!=0)
	<tr>
		<td class="font-14 form-label" colspan="5" style="text-align:right;">CHiPS Admin Charge ({{$pricing->admincharge}}%)</td>
		<td class="font-14 form-label text-right">{{$data->adminchargetotal}}</td>
		
	</tr>
	<tr>
		<td class="font-14 form-label" colspan="5" style="text-align:right;">Grand Total</td>
		<td class="font-14 form-label text-right">{{$data->grandtotal}}</td>
		
	</tr>
	@endif
	-->
	@if($i==1)
	<tr>
		<td colspan="5" class="center">--NO RECORD ADDED--</td>
	</tr>
	@endif
	</tbody>
	@if(session('departmentId'))
	<tr>
		<td colspan="5" class="text-right">
			<a href="{{ route('showprice.comparison',[Crypt::encrypt('No'), Crypt::encrypt($data->requestid)]) }}" target="_blank">
				<button type="button" class="btn btn-info" style="width:120px;">
					<i class="fa fa-table"></i> View Price
				</button>
			</a>
		</td>
	</tr>
		
	@endif
</table>
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
        '<tr><td class="text-left" nowrap>Base Price (A)</td>' +
            '<td nowrap class="text-left format-indian" data-value="' + base + '">' + base + '</td></tr>' +

        '<tr><td nowrap class="text-left" nowrap>Tax (B=A+(A*' + tax + '%))</td>' +
            '<td nowrap class="text-left format-indian" data-value="' + withTx + '">' + withTx + '</td></tr>' +

        '<tr><td nowrap class="text-left">Duration (in months) (C)</td>' +
            '<td nowrap class="text-left">' + dur + '</td></tr>' +

        '<tr><td nowrap class="text-left">Total (D=B*C)</td>' +
            '<td nowrap class="text-left format-indian" data-value="' + total + '">' + total + '</td></tr>' +
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
