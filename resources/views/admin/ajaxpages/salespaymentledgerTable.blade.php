@php
$i=1
@endphp
@foreach ($data as $item)
<tr class="mytr" id="item-{{ $i }}">
    <td style="width:30px; text-align:center;" nowrap>{{ $i++ }}</td>
	<td nowrap style="text-align:left;">@if($item->transtype=='SALES') {{ $item->transtype }} @else <label style="width:75px;">&nbsp;</label>{{ $item->transtype }} @endif</td>
	<td nowrap style="text-align:center;">{{ date('d\-m\-Y',strtotime($item->transactiondate)) }}</td>
	<td nowrap style="text-align:center;">{{ $opening }}</td>
	<td nowrap style="text-align:center;">@if($item->transtype=='SALES') {{ $item->amount }} @endif</td>
	<td nowrap style="text-align:center;">@if($item->transtype=='PAYMENT') {{ $item->amount }} @endif</td>
	<td nowrap style="text-align:center;">@if($item->transtype=='SALES') {{ $opening+$item->amount }} @else {{ $opening-$item->amount }} @endif</td>
</tr>
@if($item->transtype=='SALES')
	@php $opening = $opening+$item->amount; @endphp
@else
	@php $opening = $opening-$item->amount; @endphp
@endif
@endforeach
@if($i==1)
<tr>
    <td colspan="7" style="text-align:center;">
        <br>
        <i class="fa fa-warning blue nodata"></i><br>
        SORRY!<br>
        THE SYSTEM DID NOT FIND THE DATA YOU ARE LOOKING FOR.
    </td>
</tr>
@else
@endif