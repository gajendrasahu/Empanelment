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
		<td rowspan="3" class="align-middle">
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
	<tr class="font-14">
		<td><strong>Total</strong></td>
		<td class="text-right no_wrap">
			<strong>
				<span class="format-indian" data-value="{{ number_format($fund->vendor_funding_opening+$fund->service_charge_opening,'2','.','') }}"></span>
			</strong>
		</td>
		<td class="text-right no_wrap">
			<strong>
				<span class="format-indian" data-value="{{ number_format($fund->vendor_funding_received+$fund->service_charge_received,'2','.','') }}"></span>
			</strong>
		</td>
		<td class="text-right no_wrap">
			<strong>
				<span class="format-indian" data-value="{{ number_format($fund->vendor_funding_utilized+$fund->service_charge_utilized,'2','.','') }}"></span>
			</strong>
		</td>
		<td class="text-right no_wrap">
			<strong>
				<span class="format-indian" data-value="{{ number_format($fund->vendor_funding_balance+$fund->service_charge_balance,'2','.','') }}"></span>
			</strong>
		</td>
	</tr>

@empty

	<tr>

		<td colspan="6" class="text-center py-4">
			No CSF Department Projects found.
		</td>

	</tr>

@endforelse

<script>
document.querySelectorAll('.format-indian').forEach(function(el)
{
	el.style.fontWeight = 'bold';
	el.innerText = formatIndianNumber(el.dataset.value);
});
</script>