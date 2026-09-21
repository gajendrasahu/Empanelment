@php
$i = 1;
$now = \Carbon\Carbon::now();
@endphp
@foreach($data as $item)

<tr>

	<td class="no_wrap text-center">
		{{$loop->iteration}}
	</td>

	<td class="no_wrap">
		{{ $item->invoice_number ?: '-' }}
	</td>

	<td class="no_wrap text-center">
		{{ $item->invoice_date ? date('d-m-Y',strtotime($item->invoice_date)) : '-' }}
	</td>

	<td class="no_wrap">
		{{ $item->companyname ?: '-' }}
	</td>

	<td class="no_wrap">
		{{ $item->ordernumber ?: '-' }}
	</td>

	<td class="no_wrap">
		{{ $item->eoinumber ?: '-' }}
	</td>

	<td class="no_wrap text-right">
		<span class="format-indian" data-value="{{ $item->net_invoice_value }}"></span>
	</td>

	<td class="no_wrap text-center">

		@if($item->finance_status == 'Pending')
			<span class="label label-warning">
				Pending
			</span>
		@elseif($item->finance_status == 'Verified')
			<span class="label label-success">
				Verified
			</span>
		@elseif($item->finance_status == 'Rejected')
			<span class="label label-danger">
				Rejected
			</span>
		@else
			<span class="label label-default">
				-
			</span>
		@endif

	</td>

	<td class="no_wrap text-center">

		@if($item->payment_status == 'Unpaid')
			<span class="label label-warning">
				Unpaid
			</span>
		@elseif($item->payment_status == 'Partially Paid')
			<span class="label label-info">
				Partially Paid
			</span>
		@elseif($item->payment_status == 'Paid')
			<span class="label label-success">
				Paid
			</span>
		@else
			<span class="label label-default">
				-
			</span>
		@endif

	</td>

	<td class="no_wrap text-right">
		<span class="format-indian" data-value="{{ $item->calculated_balance ?? 0 }}"></span>
	</td>

	<td class="no_wrap text-center">
		<a href="{{ route('finance.vendorinvoice.view',Crypt::encrypt($item->voucher_id)) }}" class="" title="View" style="outline:none; text-decoration:none;">
			<i class="fa fa-eye"></i>
		</a>
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