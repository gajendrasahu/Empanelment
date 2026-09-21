<table class="pd-5" border="1" width="100%" cellpadding="6">

<style>
table
{
    border:1px solid #000;
    border-collapse:collapse;
    margin-bottom:20px;
}
th
{
    padding:5px;
}
td
{
    padding:5px;
}
.text-left
{
    text-align:left;
}
.text-center
{
    text-align:center;
}
.text-right
{
    text-align:right;
}
.v-top
{
    vertical-align:top;
}
</style>

<thead>

<tr>
    <th colspan="6" class="text-left">Pricing Details</th>
</tr>

<tr>
    <th class="text-left">Position</th>
    <th class="text-left">Sector</th>
    <th class="text-center">Duration</th>
    <th class="text-right">Man Month Rate (inc. tax)</th>
    <th class="text-right">Total Cost for Duration (inc. tax)</th>
    <th class="text-right">CHiPS Admin Charge (inc. tax)</th>
</tr>

</thead>

<tbody>

@php
	$total_salary = 0;
	$total_charge = 0;
@endphp


@if(!empty($data['resources']))

    @foreach($data['resources'] as $resource)

        @php
            $rowCount = count($resource['rows']);
        @endphp


        @foreach($resource['rows'] as $index => $row)
			@php
			$resource_cost	=	round($row['total']+(($row['total']*18)/100));
			$admin_charge	=	round(($resource_cost*5)/100);
			@endphp
            <tr>
                @if($index == 0)
				<td nowrap class="v-middle text-left" rowspan="{{ $rowCount }}">
					{{ $resource['position'] }}
				</td>
				<td nowrap class="v-middle text-left" rowspan="{{ $rowCount }}">
					{{ ucwords(strtolower($resource['sector'])) }}
				</td>
                @endif
                <td class="text-center" nowrap>{{ $row['slab'] }}</td>
                <td class="text-right">{{ formatIndianNumber(round($row['basePrice']+(($row['basePrice']*18)/100))) }}</td>
                <td class="text-right">{{ formatIndianNumber($resource_cost) }}</td>
				<td class="text-right">{{ formatIndianNumber($admin_charge) }}</td>
            </tr>
            @php
                $total_salary	= $total_salary + $resource_cost;
				$total_charge 	= $total_charge + $admin_charge;
            @endphp
        @endforeach
    @endforeach
    <tr>
        <td colspan="4"><b>Grand Total</b></td>
        <td class="text-right"><b>{{ formatIndianNumber($total_salary) }}</b></td>
		<td class="text-right"><b>{{ formatIndianNumber($total_charge) }}</b></td>
    </tr>
@else
    <tr>
        <td colspan="6" class="text-center">
            No pricing calculation found.
        </td>
    </tr>
@endif

</tbody>

</table>
<script>
@php
function formatIndianNumber($number)
{
    $number = number_format((float)$number, 2, '.', '');

    $parts = explode('.', $number);

    $integer = $parts[0];
    $decimal = $parts[1];

    if (strlen($integer) > 3) {
        $lastThree = substr($integer, -3);
        $remaining = substr($integer, 0, -3);

        $remaining = preg_replace(
            '/\B(?=(\d{2})+(?!\d))/',
            ',',
            $remaining
        );

        $integer = $remaining . ',' . $lastThree;
    }

    return $integer . '.' . $decimal;
}
@endphp
</script>
