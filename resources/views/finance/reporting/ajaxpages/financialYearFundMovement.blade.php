@if($report_format == '1')

<div class="table-responsive">

	<table class="table table-bordered pd-10" id="tablerecords">

		<thead>

			<tr style="background:#E8EEF3;">
				<th rowspan="2" style="text-align:center;vertical-align:middle;">#</th>
				<th rowspan="2" style="text-align:left;vertical-align:middle;">Financial Year</th>
				<th colspan="5" style="text-align:center;vertical-align:middle;">Fund Movement</th>
			</tr>

			<tr style="background:#E8EEF3;">
				<th style="text-align:right;">Opening Balance</th>
				<th style="text-align:right;">Department Receipt</th>
				<th style="text-align:right;">Total Available</th>
				<th style="text-align:right;">Utilized</th>
				<th style="text-align:right;">Closing Balance</th>
			</tr>

		</thead>

		<tbody>

			@forelse($report as $key => $row)

				<tr>

					<td style="text-align:center;">{{ $key + 1 }}</td>

					<td style="font-weight:600;">
						{{ $row->financial_year }}
					</td>

					<td style="text-align:right;">
						<span class="format-indian" data-value="{{ number_format($row->opening_balance,2,'.','') }}"></span>
					</td>

					<td style="text-align:right;">
						<span class="format-indian" data-value="{{ number_format($row->department_receipt,2,'.','') }}"></span>
					</td>

					<td style="text-align:right;font-weight:600;">
						<span class="format-indian" data-value="{{ number_format($row->total_available,2,'.','') }}"></span>
					</td>

					<td style="text-align:right;">
						<span class="format-indian" data-value="{{ number_format($row->utilized,2,'.','') }}"></span>
					</td>

					<td style="text-align:right;font-weight:600;">
						<span class="format-indian" data-value="{{ number_format($row->closing_balance,2,'.','') }}"></span>
					</td>

				</tr>

			@empty

				<tr>
					<td colspan="7" style="text-align:center;">No record found.</td>
				</tr>

			@endforelse

		</tbody>

		@if($report->count() > 0)

		<tfoot>

			<tr style="font-weight:700;background:#F5F5F5;">

				<td colspan="2" style="text-align:right;">Total</td>

				<td style="text-align:right;">
					<span class="format-indian" data-value="{{ number_format($summary['opening_balance'],2,'.','') }}"></span>
				</td>

				<td style="text-align:right;">
					<span class="format-indian" data-value="{{ number_format($summary['department_receipt'],2,'.','') }}"></span>
				</td>

				<td style="text-align:right;">
					<span class="format-indian" data-value="{{ number_format($summary['total_available'],2,'.','') }}"></span>
				</td>

				<td style="text-align:right;">
					<span class="format-indian" data-value="{{ number_format($summary['utilized'],2,'.','') }}"></span>
				</td>

				<td style="text-align:right;">
					<span class="format-indian" data-value="{{ number_format($summary['closing_balance'],2,'.','') }}"></span>
				</td>

			</tr>

		</tfoot>

		@endif

	</table>

</div>

@else

<div class="finance-project-list">

	@forelse($report as $row)

		<div class="finance-project-summary-card">

			<div class="finance-project-summary-header finance-summary-consultancy">

				<div class="finance-project-summary-title">
					Financial Year: {{ $row->financial_year }}
				</div>

			</div>

			<div class="finance-project-summary-grid">

				<div class="finance-summary-item">
					<div class="finance-summary-label">Opening Balance</div>
					<div class="finance-project-value">
						<span class="format-indian" data-value="{{ number_format($row->opening_balance,2,'.','') }}"></span>
					</div>
				</div>

				<div class="finance-summary-item">
					<div class="finance-summary-label">Department Receipt</div>
					<div class="finance-project-value">
						<span class="format-indian" data-value="{{ number_format($row->department_receipt,2,'.','') }}"></span>
					</div>
				</div>

				<div class="finance-summary-item">
					<div class="finance-summary-label">Total Available</div>
					<div class="finance-project-value">
						<span class="format-indian" data-value="{{ number_format($row->total_available,2,'.','') }}"></span>
					</div>
				</div>

				<div class="finance-summary-item">
					<div class="finance-summary-label">Utilized</div>
					<div class="finance-project-value">
						<span class="format-indian" data-value="{{ number_format($row->utilized,2,'.','') }}"></span>
					</div>
				</div>

				<div class="finance-summary-item">
					<div class="finance-summary-label">Closing Balance</div>
					<div class="finance-project-value">
						<span class="format-indian" data-value="{{ number_format($row->closing_balance,2,'.','') }}"></span>
					</div>
				</div>

			</div>

		</div>

	@empty

		<div class="finance-project-summary-card">
			<div style="text-align:center;padding:20px;">No record found.</div>
		</div>

	@endforelse

	@if($report->count() > 0)

		<div class="finance-project-summary-card">

			<div class="finance-project-summary-header finance-summary-payment">

				<div class="finance-project-summary-title">
					Total Fund Movement
				</div>

			</div>

			<div class="finance-project-summary-grid">

				<div class="finance-summary-item">
					<div class="finance-summary-label">Opening Balance</div>
					<div class="finance-project-value">
						<span class="format-indian" data-value="{{ number_format($summary['opening_balance'],2,'.','') }}"></span>
					</div>
				</div>

				<div class="finance-summary-item">
					<div class="finance-summary-label">Department Receipt</div>
					<div class="finance-project-value">
						<span class="format-indian" data-value="{{ number_format($summary['department_receipt'],2,'.','') }}"></span>
					</div>
				</div>

				<div class="finance-summary-item">
					<div class="finance-summary-label">Total Available</div>
					<div class="finance-project-value">
						<span class="format-indian" data-value="{{ number_format($summary['total_available'],2,'.','') }}"></span>
					</div>
				</div>

				<div class="finance-summary-item">
					<div class="finance-summary-label">Utilized</div>
					<div class="finance-project-value">
						<span class="format-indian" data-value="{{ number_format($summary['utilized'],2,'.','') }}"></span>
					</div>
				</div>

				<div class="finance-summary-item">
					<div class="finance-summary-label">Closing Balance</div>
					<div class="finance-project-value">
						<span class="format-indian" data-value="{{ number_format($summary['closing_balance'],2,'.','') }}"></span>
					</div>
				</div>

			</div>

		</div>

	@endif

</div>

@endif

<script>
	$(document).ready(function() {
		$(".format-indian").each(function() {
			var value = $(this).attr("data-value");
			$(this).text(formatIndianNumber(value));
		});
	});

	function formatIndianNumber(number) {
		number = parseFloat(number || 0).toFixed(2);
		var parts = number.split(".");
		var integerPart = parts[0];
		var decimalPart = parts[1];
		var lastThree = integerPart.substring(integerPart.length - 3);
		var otherNumbers = integerPart.substring(0, integerPart.length - 3);

		if(otherNumbers != '') {
			lastThree = ',' + lastThree;
		}

		otherNumbers = otherNumbers.replace(/\B(?=(\d{2})+(?!\d))/g, ",");

		return otherNumbers + lastThree + "." + decimalPart;
	}
</script>