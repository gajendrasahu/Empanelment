<table class="pd-5" border="1" width="100%" cellpadding="6">
<thead>
<tr>
	<th>Resource</th>
	<th>Duration</th>
	<th>Base Price</th>
	<th>Resource Cost</th>
	<th>GST</th>
	<th>Admin Charge</th>
	<th>Total</th>
</tr>
</thead>
<tbody>
@foreach($data['resources'] as $resource)
@php
$rowCount = count($resource['rows']);
@endphp
@foreach($resource['rows'] as $index => $row)
<tr>
	@if($index == 0)
	<td style="vertical-align:top;" rowspan="{{ $rowCount }}">{{ $resource['resource'] }}</td>
	@endif
	<td>{{ $row['slab'] }}</td>
	<td>{{ number_format($row['basePrice'],2) }}</td>
	<td>{{ number_format($row['salary'],2) }}</td>
	<td>{{ number_format($row['gst'],2) }}</td>
	<td>{{ number_format($row['admin'],2) }}</td>
	<td>{{ number_format($row['total'],2) }}</td>
</tr>

@endforeach

@endforeach

<tr>
<td colspan="6"><b>Grand Total</b></td>
<td><b>{{ number_format($data['grand_total'],2) }}</b></td>
</tr>

</tbody>

</table>