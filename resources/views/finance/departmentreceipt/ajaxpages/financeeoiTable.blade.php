@php
$i = 1;
$now = \Carbon\Carbon::now();
@endphp
@foreach($data as $item)
<tr>
	<td class="center">{{$loop->iteration}}</td>
	<td>@if($item->project_name) {{$item->project_name}}@endif</td>
	<td class="lh-25">
		<b>{{$item->eoinumber}}</b>
		@if($item->name) <br> {{$item->name}}@endif
		@if($item->ordernumber) <br><b>{{$item->ordernumber}}</b> @endif
		@if($item->shortname) <br><b>{{$item->shortname}}</b> @endif
	</td>
	<td nowrap style="font-weight:bold; text-align:center;">
		{{$item->demand_note_count}} | <span class="format-indian" style="font-size:14px; font-weight:bold!important;" data-value="{{$item->total_demand_note_value}}"></span> | <span class="format-indian" style="font-size:14px; font-weight:bold!important;" data-value="{{$item->total_received_amount}}"></span>
		<br>
		<a href="{{ route('finance.demandnote.create', Crypt::encrypt($item->requestid)) }}" class="btn btn-info gridbtn width-150 mt-10">
			Create Demand Note
		</a>	
	</td>
</tr>
@endforeach
@if($data->count()==0)
	<tr>
		<td colspan="4" style="text-align:center;">
			<br>
			<i class="fa fa-warning blue nodata"></i><br>
			{!! __('messages.sorry') !!}
		</td>
	</tr>
@else
	<tr>
		<td colspan="4" style="text-align:right;">
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