@php
$i = 1;
$now = \Carbon\Carbon::now();
@endphp

@foreach($data as $item)
<tr>
	<td class="text-center">{{$loop->iteration}}</td>
	<td class="no_wrap">
		<a href="{{ route('finance.departmentinvoice.view',Crypt::encrypt($item->department_invoice_id)) }}">
		<u>{{ $item->invoice_no ?: '-' }}</u>
		</a>
	</td>

	<td class="no_wrap">{{ $item->invoice_date ? date('d-m-Y',strtotime($item->invoice_date)) : '-' }}</td>

	<td class="no_wrap">@if($item->project_name) {{ $item->project_name }}<br> @endif{{ $item->departmentname ?: '-' }}</td>

	<td class="text-right">
		<span class="format-indian" data-value="{{ $item->taxable_amount }}"></span>
	</td>

	<td class="text-right">
		<span class="format-indian" data-value="{{ $item->cgst_amount }}"></span>
	</td>

	<td class="text-right">
		<span class="format-indian" data-value="{{ $item->sgst_amount }}"></span>
	</td>

	<td class="text-right">
		<span class="format-indian" data-value="{{ $item->igst_amount }}"></span>
	</td>

	<td class="text-right">
		<span class="format-indian" data-value="{{ $item->invoice_amount }}"></span>
	</td>

	<td class="text-center">
	@if($item->status == 'Draft')
	<a href="{{ route('finance.departmentinvoice.edit',Crypt::encrypt($item->department_invoice_id)) }}" title="Edit">
		<i class="fa fa-pencil"></i>
	</a>
	@endif
	</td>

</tr>
@endforeach

@if($data->count()==0)
<tr>
	<td colspan="10" style="text-align:center;">
		<br>
		<i class="fa fa-warning blue nodata"></i><br>
		{!! __('messages.sorry') !!}
	</td>
</tr>
@else
<tr>
	<td colspan="10" style="text-align:right;">
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