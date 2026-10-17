@php
$i = 1;
$now = \Carbon\Carbon::now();
$opening_total	=	0;
$received_total	=	0;
$utilized_total	=	0;
$available_total=	0;
@endphp
@forelse($data as $fund)
	<tr>
		<td rowspan="2" class="align-middle">
			<strong>{{ $fund->name }}</strong><br>
			<strong>{{ $fund->eoinumber }}</strong><br>
			{{ $fund->projecttitle }}
			
		</td>
		<td class="no_wrap">
			<strong>Consultancy</strong>
		</td>
		<td class="text-right no_wrap">
			<span class="format-indian" data-value="{{ number_format($fund->vendor_funding_opening,'2','.','') }}"></span>
		</td>
		<td class="text-right no_wrap">
			<span class="format-indian" data-value="{{ number_format($fund->vendor_funding_received,'2','.','') }}"></span>
		</td>
		<td class="text-right no_wrap">
			<span class="format-indian" data-value="{{ number_format($fund->vendor_funding_utilized,'2','.','') }}"></span>
		</td>
		<td class="text-right no_wrap">
			<strong>
				<span class="format-indian" data-value="{{ number_format($fund->vendor_funding_balance,'2','.','') }}"></span>
			</strong>
		</td>
	</tr>
	<tr>
		<td class="no_wrap">
			<strong>Administrative Charge</strong>
		</td>
		<td class="text-right no_wrap">
			<span class="format-indian" data-value="{{ number_format($fund->service_charge_opening,'2','.','') }}"></span>
		</td>
		<td class="text-right no_wrap">
			<span class="format-indian" data-value="{{ number_format($fund->service_charge_received,'2','.','') }}"></span>
		</td>
		<td class="text-right no_wrap">
			<span class="format-indian" data-value="{{ number_format($fund->service_charge_utilized,'2','.','') }}"></span>
		</td>
		<td class="text-right no_wrap">
			<strong>
				<span class="format-indian" data-value="{{ number_format($fund->service_charge_balance,'2','.','') }}"></span>
			</strong>
		</td>
	</tr>
	@php
	$opening_total	=	$opening_total+$fund->vendor_funding_opening+$fund->service_charge_opening;
	$received_total	=	$received_total+$fund->vendor_funding_received+$fund->service_charge_received;
	$utilized_total	=	$utilized_total+$fund->vendor_funding_utilized+$fund->service_charge_utilized;
	$available_total=	$available_total+$fund->vendor_funding_balance+$fund->service_charge_balance;
	@endphp
@empty

	<tr>

		<td colspan="6" class="text-center py-4">
			No CSF Department Projects found.
		</td>

	</tr>

@endforelse

@if($data->count()>0)
<tr class="font-14">
	<td colspan="2" class="text-right"><strong>Total</strong></td>
	<td class="text-right no_wrap">
		<strong>
			<span class="format-indian" data-value="{{ number_format($opening_total,'2','.','') }}"></span>
		</strong>
	</td>
	<td class="text-right no_wrap">
		<strong>
			<span class="format-indian" data-value="{{ number_format($received_total,'2','.','') }}"></span>
		</strong>
	</td>
	<td class="text-right no_wrap">
		<strong>
			<span class="format-indian" data-value="{{ number_format($utilized_total,'2','.','') }}"></span>
		</strong>
	</td>
	<td class="text-right no_wrap">
		<strong>
			<span class="format-indian" data-value="{{ number_format($available_total,'2','.','') }}"></span>
		</strong>
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