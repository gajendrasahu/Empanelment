@php
$i=1;
$total_resource	=	0;
$total_order	=	0;
@endphp
@foreach ($data as $item)
<tr class="dashboard-table-head">
	<td nowrap>
		<img src="{{ asset('panel/assets/images/icons/icon_10.png') }}" class="dashboard_icons" style="float:left; margin-right:10px;">
		{{ucwords(strtolower($item->companyname))}}<br>[{{$item->tiername}}]
	</td>
	<td nowrap class="center">{{$item->total_project}}</td>
	<td nowrap class="center">{{$item->total_count}}</td>
	<td style="text-align:right; font-weight:bold;" class="format-indian" data-value="{{ number_format($item->order_value,'2','.','') }}"></td>
</tr>
@php
$total_resource	=	$total_resource+$item->total_count;
$total_order	=	$total_order+$item->order_value;
@endphp
@endforeach
@if($data->count()>0)
<tr class="dashboard-table-head">
	<td colspan="2" class="font-14" style="text-align:right; font-weight:bold;">Total</td>
	<td class="center font-14" style="font-weight:bold;">{{$total_resource}}</td>
	<td style="text-align:right; font-weight:bold;" class="format-indian font-14" data-value="{{ number_format($total_order,'2','.','') }}"></td>
</tr>
@else
<tr class="dashboard-table-head">
	<td colspan="4" class="center">--No Record Found--</td>
</tr>

@endif
<script src="{{ asset('panel/assets/js/datatablePaginationWithFilter.js') }}"></script>
<script>
document.querySelectorAll('.format-indian').forEach(function(el) {
    el.innerText = formatIndianNumber(el.dataset.value);
});

</script>
