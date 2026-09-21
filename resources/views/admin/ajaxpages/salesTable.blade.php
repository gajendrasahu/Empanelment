@php
$i=1
@endphp
@foreach ($data as $item)
<tr class="mytr" id="item-{{ $i }}">
    <td style="width:30px; text-align:center;" nowrap>{{ $i++ }}</td>
	<td nowrap style="text-align:center;">{{ date('d\-m\-Y',strtotime($item->salesdate)) }}</td>
	<td nowrap style="text-align:center;">{{ $item->salesid }}</td>
	<td nowrap style="text-align:center;">{{ $item->voucherstring }}</td>
	<td nowrap style="text-align:left;">{{ $item->name }}</td>
	<td nowrap style="text-align:center;">{{ number_format($item->freight,'2','.','') }}</td>
	<td nowrap style="text-align:center;">{{ number_format($item->packing,'2','.','') }}</td>
	<td nowrap style="text-align:center;">{{ number_format($item->taxable,'2','.','') }}</td>
	<td nowrap style="text-align:center;">{{ number_format($item->gstvalue,'2','.','') }}</td>
	<td nowrap style="text-align:center;">{{ number_format($item->roundup,'2','.','') }}</td>
	<td nowrap style="text-align:center;">{{ number_format($item->netamount,'2','.','') }}</td>
	<td nowrap style="text-align:center;">{{ number_format($item->balanceamount,'2','.','') }}</td>
	<td nowrap style="text-align:center;">{{ $item->orderstatus}}</td>
    <td nowrap style="text-align:center;">
	@if(in_array(2,Session::get('actions')))
		<a href="{{ route('change.sales',$item->salesid) }}" title="" class="myactionlink"><i class="fa fa-edit"></i></a></td>
	@endif
    <td nowrap style="text-align:center;">
	@if(in_array(3,Session::get('actions')))
        <a title="" class="myactionlink" onclick="deleteItem('sales',{{ $item->salesid}},{{ $i }},'ARE YOU SURE YOU WANT TO DELETE THIS RECORD?','SALES RECORD DELETED SUCCESSFULLY')" title=""><i class="fa fa-trash"></i>
        </a>
	@endif
    </td>
    <td nowrap style="text-align:center;">
	@if(in_array(4,Session::get('actions')))
        <a href="{{ route('print.sales', $item->salesid) }}" target="_blank" title="" class="myactionlink" title=""><i class="fa fa-print"></i></a>
	@endif
    </td>
    <td nowrap style="text-align:center;">
	@if(in_array(22,Session::get('actions')))
		<a href="{{ route('add.salespayment',$item->salesid) }}"><i class="fa fa-money" title="ADD SALES PAYMENT"></i></a>
	@endif
    </td>
</tr>
@endforeach
@if($i==1)
<tr>
    <td colspan="17" style="text-align:center;">
        <br>
        <i class="fa fa-warning blue nodata"></i><br>
        SORRY!<br>
        THE SYSTEM DID NOT FIND THE DATA YOU ARE LOOKING FOR.
    </td>
</tr>
@else
@endif


