@php
$i = 1;
$now = \Carbon\Carbon::now();
@endphp

@foreach($data as $item)

<tr>
	<td class="no_wrap text-center">{{ $loop->iteration }}</td>

	<td class="no_wrap text-center">
		{{ $item->payment_date ? date('d-m-Y',strtotime($item->payment_date)) : '-' }}
	</td>

	<td class="no_wrap">
		{{ $item->invoice_number ?: '-' }}
	</td>

	<td class="no_wrap">
		{{ $item->companyname ?: '-' }}
	</td>

	<td class="no_wrap text-right">
		<span class="format-indian" data-value="{{ $item->gross_amount ?? 0 }}"></span>
	</td>

	<td class="no_wrap text-right">
		<span class="format-indian" data-value="{{ $item->admin_charge_amount ?? 0 }}"></span>
	</td>

	<td class="no_wrap text-right">
		<span class="format-indian" data-value="{{ $item->approval_amount ?? 0 }}"></span>
	</td>

	<td class="no_wrap text-right" style="position:relative;">
		@if((float)$item->tds_rate > 0)
			<small style="position:absolute; top:2px; right:10px; font-size:10px;">(TDS @ {{ number_format((float)$item->tds_rate,2) }}%)</small>
		@endif
		<span class="format-indian" data-value="{{ $item->tds_amount ?? 0 }}"></span>
	</td>

	<td class="no_wrap text-right" style="position:relative;">
		@if((float)$item->gst_tds_rate > 0)
			<small style="position:absolute; top:2px; right:10px; font-size:10px;">{{ number_format((float)$item->gst_tds_rate,2) }}%</small>
		@endif
		<span class="format-indian" data-value="{{ $item->gst_tds_amount ?? 0 }}"></span>
	</td>

	<td class="no_wrap text-right">
		<span class="format-indian" data-value="{{ $item->net_paid_amount ?? 0 }}"></span>
	</td>

	<td class="no_wrap">
		{{ $item->payment_mode ?: '-' }}
	</td>

	<td class="no_wrap">
		{{ $item->transaction_no ?: '-' }}
	</td>

	<td class="no_wrap text-center">

		<a class="btn btn-primary no-hover br-5"
		   style="padding:2px 6px!important;"
		   href="{{ route('finance.vendorpayment.view',Crypt::encrypt($item->vendor_payment_id)) }}">
			<i class="fa fa-eye"></i> View
		</a>

	</td>
</tr>

@endforeach


@if($data->count()==0)

<tr>
	<td colspan="13" style="text-align:center;">
		<br>
		<i class="fa fa-warning blue nodata"></i><br>
		{!! __('messages.sorry') !!}
	</td>
</tr>

@else

<tr>
	<td colspan="13" style="text-align:right;">
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