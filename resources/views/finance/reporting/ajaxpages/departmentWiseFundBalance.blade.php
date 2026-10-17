@if($reportFormat=='1')

<table class="table table-bordered" id="tablerecords">

	<thead>
		<tr class="myheadbg">
			<th>#</th>
			<th class="no_wrap">Department</th>
			<th class="text-center no_wrap">Projects</th>
			<th class="text-right no_wrap">Opening Fund</th>
			<th class="text-right no_wrap">Department Receipt</th>
			<th class="text-right no_wrap">Total Available</th>
			<th class="text-right no_wrap">Consultancy Funding Utilized</th>
			<th class="text-right no_wrap">Admin Charge Utilized</th>
			<th class="text-right no_wrap">Total Utilized</th>
			<th class="text-right no_wrap">Closing Balance</th>
		</tr>
	</thead>

	<tbody>

	@forelse($reportData as $row)

		<tr>

			<td class="text-center">{{ $loop->iteration }}</td>

			<td>
				<strong>{{ $row->shortname ?: $row->departmentname }}</strong>
				@if($row->shortname && $row->departmentname)
					<br><small>{{ $row->departmentname }}</small>
				@endif
			</td>

			<td class="text-center">
				{{ $row->project_count }}
			</td>

			<td class="text-right no_wrap">
				₹ <span class="format-indian" data-value="{{ number_format($row->opening_fund,'2','.','') }}"></span>
			</td>

			<td class="text-right no_wrap">
				₹ <span class="format-indian" data-value="{{ number_format($row->department_receipt,'2','.','') }}"></span>
			</td>

			<td class="text-right no_wrap">
				₹ <span class="format-indian" data-value="{{ number_format($row->total_available,'2','.','') }}"></span>
			</td>

			<td class="text-right no_wrap">
				₹ <span class="format-indian" data-value="{{ number_format($row->vendor_funding_utilized,'2','.','') }}"></span>
			</td>

			<td class="text-right no_wrap">
				₹ <span class="format-indian" data-value="{{ number_format($row->service_charge_utilized,'2','.','') }}"></span>
			</td>

			<td class="text-right no_wrap">
				₹ <span class="format-indian" data-value="{{ number_format($row->total_utilized,'2','.','') }}"></span>
			</td>

			<td class="text-right no_wrap">
				₹ <span class="format-indian" data-value="{{ number_format($row->closing_balance,'2','.','') }}"></span>
			</td>

		</tr>

	@empty

		<tr>
			<td colspan="10" class="text-center finance-empty-row">
				No financial records found.
			</td>
		</tr>

	@endforelse

	@if($reportData->count()>0)

		<tr>

			<th colspan="2" class="text-right">Total</th>

			<th class="text-center">
				{{ $reportData->sum('project_count') }}
			</th>

			<th class="text-right no_wrap">
				₹ <span class="format-indian" data-value="{{ number_format($reportData->sum('opening_fund'),'2','.','') }}"></span>
			</th>

			<th class="text-right no_wrap">
				₹ <span class="format-indian" data-value="{{ number_format($reportData->sum('department_receipt'),'2','.','') }}"></span>
			</th>

			<th class="text-right no_wrap">
				₹ <span class="format-indian" data-value="{{ number_format($reportData->sum('total_available'),'2','.','') }}"></span>
			</th>

			<th class="text-right no_wrap">
				₹ <span class="format-indian" data-value="{{ number_format($reportData->sum('vendor_funding_utilized'),'2','.','') }}"></span>
			</th>

			<th class="text-right no_wrap">
				₹ <span class="format-indian" data-value="{{ number_format($reportData->sum('service_charge_utilized'),'2','.','') }}"></span>
			</th>

			<th class="text-right no_wrap">
				₹ <span class="format-indian" data-value="{{ number_format($reportData->sum('total_utilized'),'2','.','') }}"></span>
			</th>

			<th class="text-right no_wrap">
				₹ <span class="format-indian" data-value="{{ number_format($reportData->sum('closing_balance'),'2','.','') }}"></span>
			</th>

		</tr>

	@endif

	</tbody>

</table>

@else

<div class="finance-project-list mt-10">

	@forelse($reportData as $row)

		<div class="finance-project-summary-card">

			<div class="finance-project-summary-header">

				<div>
					<div class="finance-project-eoi no_wrap">
						{{ $row->shortname ?: $row->departmentname }}
					</div>

					@if($row->shortname && $row->departmentname)
						<div class="finance-project-name">
							{{ $row->departmentname }}
						</div>
					@endif
				</div>

				<div>
					<strong>{{ $row->project_count }}</strong> Projects
				</div>

			</div>

			<div class="finance-project-summary-grid">

				<div class="finance-summary-item">
					<span class="finance-summary-label">Opening Fund</span>
					<strong class="finance-project-value">
						₹ <span class="format-indian" data-value="{{ number_format($row->opening_fund,'2','.','') }}"></span>
					</strong>
				</div>

				<div class="finance-summary-item">
					<span class="finance-summary-label">Department Receipt</span>
					<strong class="finance-project-value">
						₹ <span class="format-indian" data-value="{{ number_format($row->department_receipt,'2','.','') }}"></span>
					</strong>
				</div>

				<div class="finance-summary-item">
					<span class="finance-summary-label">Total Available</span>
					<strong class="finance-project-value">
						₹ <span class="format-indian" data-value="{{ number_format($row->total_available,'2','.','') }}"></span>
					</strong>
				</div>

				<div class="finance-summary-item finance-summary-consultancy">
					<span class="finance-summary-label">Consultancy Funding Utilized</span>
					<strong class="finance-project-value">
						₹ <span class="format-indian" data-value="{{ number_format($row->vendor_funding_utilized,'2','.','') }}"></span>
					</strong>
				</div>

				<div class="finance-summary-item finance-summary-admin">
					<span class="finance-summary-label">Admin Charge Utilized</span>
					<strong class="finance-project-value">
						₹ <span class="format-indian" data-value="{{ number_format($row->service_charge_utilized,'2','.','') }}"></span>
					</strong>
				</div>

				<div class="finance-summary-item finance-summary-payment">
					<span class="finance-summary-label">Total Utilized</span>
					<strong class="finance-project-value">
						₹ <span class="format-indian" data-value="{{ number_format($row->total_utilized,'2','.','') }}"></span>
					</strong>
				</div>

				<div class="finance-summary-item">
					<span class="finance-summary-label">Closing Balance</span>
					<strong class="finance-project-value">
						₹ <span class="format-indian" data-value="{{ number_format($row->closing_balance,'2','.','') }}"></span>
					</strong>
				</div>

			</div>

		</div>

	@empty

		<div class="finance-project-empty">

			<i class="fa fa-folder-open-o"></i>

			<strong>No financial records found.</strong>

			<span>There are no departments available for the selected criteria.</span>

		</div>

	@endforelse

</div>

@endif

<script>
document.querySelectorAll('.format-indian').forEach(function(el){el.style.fontWeight='bold';el.innerText=formatIndianNumber(el.dataset.value);});
</script>