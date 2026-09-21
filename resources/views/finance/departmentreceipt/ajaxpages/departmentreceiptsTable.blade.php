@php
$i = 1;
$now = \Carbon\Carbon::now();
@endphp

@foreach($data as $item)
<tr>
	<td class="text-center">{{$loop->iteration}}</td>
	<td class="text-center">
		@if((float)$item->balance > 0)
			<input type="checkbox" name="department_payment_ids[]" value="{{ $item->department_payment_id }}" class="receiptCheck" data-balance="{{ $item->balance }}">
		@endif
	</td>
	<td>{{ $item->receipt_no ?: '-' }}</td>
	<td class="text-center">{{ $item->receipt_date ? date('d-m-Y',strtotime($item->receipt_date)) : '-' }}</td>
	<td>{{ $item->demand_note_no ?: '-' }}</td>
	<td  class="text-right"><span class="format-indian" data-value="{{ $item->gross_received_amount }}"></span></td>
	<td class="text-right"><span class="format-indian" data-value="{{ $item->total_invoiced }}"></span></td>
	<td class="text-right"><span class="format-indian" data-value="{{ number_format($item->balance,'2','.','') }}"></span></td>
</tr>
@endforeach

@if($data->count()==0)
<tr>
	<td colspan="8" style="text-align:center;">
		<br>
		<i class="fa fa-warning blue nodata"></i><br>
		{!! __('messages.sorry') !!}
	</td>
</tr>
@else
<tr>
	<td colspan="8" class="text-right">
		<button type="button" id="btnPrepareInvoice" class="btn btn-info gridbtn width-150" disabled>
			<i class="fa fa-file-text-o"></i> Prepare Invoice
		</button>		
	</td>
</tr>
<tr>
	<td colspan="8" style="text-align:right;">
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

$(document).on('change','.receiptCheck',function()
{
	let selectedCount = $('.receiptCheck:checked').length;

	$('#btnPrepareInvoice').prop('disabled',selectedCount === 0);
});

$(document).on('click','#btnPrepareInvoice',function()
{
	let paymentIds = [];

	$('.receiptCheck:checked').each(function()
	{
		paymentIds.push($(this).val());
	});

	if(paymentIds.length === 0)
	{
		bootbox.alert('Please select at least one Department Receipt.');
		return false;
	}

	$.ajax({
		url: "{{ route('finance.departmentinvoice.prepare') }}",
		type: 'POST',
		data: {
			_token: "{{ csrf_token() }}",
			department_payment_ids: paymentIds
		},
		beforeSend: function()
		{
			$('#btnPrepareInvoice').prop('disabled',true);
		},
		success: function(response)
		{
			if(response.status == 1)
			{
				window.location.href = response.redirect;
			}
			else
			{
				bootbox.alert(response.message);
				$('#btnPrepareInvoice').prop('disabled',false);
			}
		},
		error: function(xhr)
		{
			let message = 'Something went wrong.';

			if(xhr.responseJSON && xhr.responseJSON.message)
			{
				message = xhr.responseJSON.message;
			}

			bootbox.alert(message);

			$('#btnPrepareInvoice').prop('disabled',false);
		}
	});
});
</script>