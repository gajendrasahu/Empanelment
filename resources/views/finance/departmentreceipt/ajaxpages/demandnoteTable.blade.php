@php
$i = 1;
$now = \Carbon\Carbon::now();
@endphp
@foreach($data as $item)
@php
$balance	=	$item->total_amount-$item->total_received;
@endphp
<tr>
	<td class="center">{{$loop->iteration}}</td>
	<td style="position:relative; white-space:nowrap; width:230px;">
		{{$item->demand_note_no}} | {{date('d\-m\-Y',strtotime($item->demand_note_date))}}
		@if($item->attachment)
			<a href="{{route('view.uploadedfile',Crypt::encrypt($item->attachment))}}" target="_blank" style="position:absolute; right:3px; outline:none;">
				<i class="fa fa-file-pdf-o"></i>
			</a>
		@endif
	</td>
	<td style="width:250px;">@if($item->project_name) {{$item->project_name}}<br> @endif {{$item->departmentname}}</td>
	
	<td class="text-right format-indian" data-value="{{$item->total_amount}}"></td>
	<td class="text-right format-indian" data-value="{{$item->total_received}}"></td>
	<td class="text-right format-indian" data-value="{{number_format($balance,'2','.','')}}"></td>
	<td class="text-right">
		@if($item->total_received==0)
			Pending
		@elseif($item->total_received<$item->total_amount)
			Partially Paid
		@else
			Paid
		@endif
	</td>
	<td nowrap class="text-center">
		@if(($item->total_amount-$item->total_received)>0)
		<a href="{{ route('finance.departmentreceipt.create',Crypt::encrypt($item->demand_note_id)) }}" class="no-outline" title="Receive Payment">
			<i class="fa fa-money"></i>
		</a>
		@endif
		@if($item->total_received>0)
			@if(($item->total_amount-$item->total_received)>0)
			|
			@endif
		<a href="{{ route('finance.departmentreceipt.receipts',Crypt::encrypt($item->demand_note_id)) }}" title="View Receipts">
			<i class="fa fa-list"></i>
		</a>
		@endif
	</td>
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

</script>