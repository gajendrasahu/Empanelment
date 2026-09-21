@php
$i = 1;
$now = \Carbon\Carbon::now();
@endphp
@foreach($data as $item)
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
	<td style="width:250px;">
		@if($item->project_name) {{$item->project_name}}<br> @endif {{$item->departmentname}}<br>
		@if($item->demand_note_type=='VENDOR_FUNDING')
		(Consultancy Service Fee)
		@else
		(CHiPS Admin Charge)
		@endif

	</td>
	<td class="text-right">
		<span class="format-indian" data-value="{{$item->total_amount}}"></span>
	</td>
	<td class="text-right">
		<span class="format-indian" data-value="{{$item->total_received}}"></span>
	</td>
	<td class="text-right">
		@if($item->total_received==0)
			Pending
		@elseif($item->total_received!=0 && ($item->total_amount!=$item->total_received))
			Partially Paid
		@else
			Paid
		@endif
	</td>
	<td class="text-center" style="white-space: nowrap;">
		@if($item->status=='Active' && $item->payment_status=='Pending')
			<a href="{{ route('finance.demandnote.edit', Crypt::encrypt($item->demand_note_id)) }}"
			   style="outline:none; text-decoration:none;"
			   title="Edit Demand Note">
				<i class="fa fa-pencil"></i>
			</a>

			|

			<a href="javascript:void(0);"
			   class="btnCancelDemandNote"
			   data-id="{{ Crypt::encrypt($item->demand_note_id) }}"
			   style="outline:none; text-decoration:none;"
			   title="Cancel Demand Note">
				<i class="fa fa-ban"></i>
			</a>
			| 
			@endif
			<a href="{{ route('finance.demandnote.view', Crypt::encrypt($item->demand_note_id)) }}" style="outline:none; text-decoration:none;"
			   title="View Demand Note"> <i class="fa fa-eye"></i>
			</a>
	</td>
</tr>
@endforeach
@if($data->count()==0)
	<tr>
		<td colspan="7" style="text-align:center;">
			<br>
			<i class="fa fa-warning blue nodata"></i><br>
			{!! __('messages.sorry') !!}
		</td>
	</tr>
@else
	<tr>
		<td colspan="7" style="text-align:right;">
			{{ $data->links('vendor.pagination.default') }}
		</td>
	</tr>
@endif

<script>
document.querySelectorAll('.format-indian').forEach(function(el) {
	el.style.fontWeight = 'bold';
    el.innerText = formatIndianNumber(el.dataset.value);
});

</script>