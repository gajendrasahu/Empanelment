@php
$i = 1;
$now = \Carbon\Carbon::now();
@endphp
@foreach($data as $item)

<tr>
	<td class="no_wrap text-center">{{$loop->iteration}}</td>
	<td class="no_wrap">{{ $item->invoice_number ?: '-' }}</td>
	<td class="no_wrap text-center">{{ $item->invoice_date ? date('d-m-Y',strtotime($item->invoice_date)) : '-' }}</td>
	<td class="no_wrap">{{ $item->companyname ?: '-' }}</td>
	<td class="no_wrap">{{ $item->ordernumber ?: '-' }}</td>
	<td class="no_wrap">{{ $item->eoinumber ?: '-' }}</td>
	<td class="no_wrap text-right"><span class="format-indian" data-value="{{ $item->net_invoice_value }}"></span></td>
	<td class="no_wrap text-right"><span class="format-indian" data-value="{{ $item->total_paid ?? 0 }}"></span></td>
	<td class="no_wrap text-right"><span class="format-indian" data-value="{{ $item->balance ?? 0 }}"></span></td>
	<td class="no_wrap text-center">
		@if($item->net_invoice_value == $item->balance)
			<span class="label label-warning br-5 width-full">
				Unpaid
			</span>
		@elseif(($item->net_invoice_value!=$item->balance) && $item->balance!=0)
			<span class="label label-info br-5 width-full">
				Partially Paid
			</span>
		@elseif(($item->net_invoice_value!=$item->balance) && $item->balance==0)
			<span class="label label-succss br-5 width-full">
				Paid
			</span>
		@else
			<span class="label label-default br-5 width-full">
				-
			</span>
		@endif
	</td>
	<td>
	@if((float)$item->balance > 0)
	<a class="btn btn-primary no-hover br-5" style="padding:2px 2px!important;" data-id="{{ $item->voucher_id }}" href="{{route('finance.vendorpayment.make',Crypt::encrypt($item->voucher_id))}}">
		<i class="fa fa-money"></i> Make Payment
	</a>
	@else
	<span class="text-muted">No Balance</span>
	@endif
	</td>
</tr>

@endforeach

@if($data->count()==0)
<tr>
	<td colspan="11" style="text-align:center;">
		<br>
		<i class="fa fa-warning blue nodata"></i><br>
		{!! __('messages.sorry') !!}
	</td>
</tr>
@else
<tr>
	<td colspan="11" style="text-align:right;">
		{{ $data->links('vendor.pagination.default') }}
	</td>
</tr>
@endif

<script>

document.querySelectorAll('.format-indian').forEach(function(el)
{
	el.style.fontWeight = 'bold';
	el.innerText = formatIndianNumber(el.dataset.value);
});
</script>