
<div class="col-sm-12">&nbsp;</div>
@if($labels->count()!=0)
<div class="col-sm-12" style="display: flex; gap:4px; padding: 0px 15px;">
	<div class="card col-sm-12 delay-04">
		<div class="dashboard-charts">
			<div class="dashboard-chart-header">
				<h4 class="dashboard-chart-title">Firm Utilisation</h4>
			</div>
			<div class="chart-wrapper">
				<canvas id="firmResourceChart"></canvas>
			</div>
		</div>
	</div>
</div>
@endif
<div class="col-sm-12">&nbsp;</div>
<div class="col-sm-12">
	
		<div class="table-responsive">
			<table class="table table-bordered table-striped table-hover pd-8" id="tablerecords">
				<thead>
					<tr class="dashboard-table-head">
						<td colspan="5" class="pd-8 v-middle" style="line-height:30px; font-size: 16px !important;">
							Firm-wise Project Details
						</td>
					</tr>
					<tr>
						<td nowrap class="center width-30">S.No.</td>
						<td nowrap>Firm Name</td>
						<td nowrap class="center">Tier</td>
						<td nowrap class="center">Total Projects</td>
						<td nowrap class="center">Total Resources</td>
					</tr>
				</thead>
				<tbody class="tabledata">
					@php
						$i = 1;
						$total_resource = 0;
						$total_order = 0;
						$total_project = 0;
					@endphp

					@foreach ($data as $item)
						@php
							$tierLabel = strtolower(trim($item->tiername ?? ''));
							$tierClass = preg_match('/\\btier\\s*[-]?\\s*(1|i)\\b/', $tierLabel) ? 'tier-1' : 'tier-2';
						@endphp
						<tr class="dashboard-table-head">
							<td nowrap class="center">{{$i++}}</td>
							<td nowrap><span class="firm-name first-column-name">{{ ucwords(strtolower($item->companyname)) }}</span></td>
							<td nowrap class="center"><span class="firm-tier {{ $tierClass }}">{{ $item->tiername }}</span></td>
							<td nowrap class="center">{{$item->total_project}}</td>
							<td nowrap class="center">{{$item->total_count}}</td>
						</tr>
						@php
							$total_resource = $total_resource + $item->total_count;
							$total_order = $total_order + $item->order_value;
							$total_project = $total_project + $item->total_project;
						@endphp
					@endforeach
					@if($data->count() > 0)
						<tr class="dashboard-table-head">
							<td class="font-14" colspan="3" style="text-align:right; font-weight:bold;">Total</td>
							<td class="center font-14" style="font-weight:bold;">{{$total_project}}</td>
							<td class="center font-14" style="font-weight:bold;">{{$total_resource}}</td>
						</tr>
					@else
						<tr class="dashboard-table-head">
							<td colspan="5" class="center">--No Record Found--</td>
						</tr>

					@endif
				</tbody>

			</table>
		</div>
	
</div>
<div class="col-sm-12">&nbsp;</div>
@php



@endphp
<script src="{{ asset('panel/assets/js/chart.js') }}"></script>
<script src="{{ asset('panel/assets/js/chart-label.js') }}"></script>
@if($labels->count()!=0)
<script>
	/* ==============================
	   GLOBAL SHADCN-STYLE CHART THEME
	================================ */
	Chart.defaults.font.family =
		'Inter, system-ui, -apple-system, BlinkMacSystemFont, Segoe UI, Roboto, sans-serif';

	Chart.defaults.font.size = 12;
	Chart.defaults.color = '#6b7280';

	Chart.defaults.plugins.tooltip.backgroundColor = '#111827';
	Chart.defaults.plugins.tooltip.titleColor = '#ffffff';
	Chart.defaults.plugins.tooltip.bodyColor = '#e5e7eb';
	Chart.defaults.plugins.tooltip.cornerRadius = 8;
	Chart.defaults.plugins.tooltip.padding = 10;
	Chart.defaults.plugins.tooltip.displayColors = false;


	/* ==============================
	   BAR – Firm Utilisation
	================================ */
	const labels = @json($labels);
	const resourceCounts = @json($totals);
	const orderNumbers = @json($orderNumbers);
	const maxDataValue = Math.max(...resourceCounts);
	const yAxisMax = maxDataValue + 5;
	const ctx1 = document.getElementById('firmResourceChart').getContext('2d');

	const backgroundColors = labels.map(() => {
		const r = Math.floor(Math.random() * 200);
		const g = Math.floor(Math.random() * 200);
		const b = Math.floor(Math.random() * 200);
		return `rgb(${r}, ${g}, ${b})`;
	});

	new Chart(ctx1, {
		type: 'bar',
		data: {
			labels: labels,
			datasets: [{
				label: 'Resource Count',
				data: resourceCounts,
				backgroundColor: backgroundColors,
				borderRadius: 8,
				barThickness: 40
			}]
		},
		options: {
			responsive: true,
			plugins: {
				legend: {
					display: false
				},

				datalabels: {
					labels: {
						value: {
							anchor: 'end',
							align: 'end',
							color: '#000',
							font: { weight: 'bold' },
							formatter: function(value) {
								return value;
							}
						},
					}
				},
				tooltip: {
					callbacks: {
						label: function(context) {
							return 'Resources: ' + context.raw;
						}
					}
				}
			},

			scales: {
				x: {
					title: {
						display: true,
						text: 'Firm Name'
					}
				},
				y: {
					beginAtZero: true,
					max: yAxisMax,
					title: {
						display: true,
						text: 'Resource Count'
					}
				}
			}
		},
		plugins: [ChartDataLabels]
	});
</script>
@endif

<script src="{{ asset('panel/assets/js/datatablePaginationWithFilter.js') }}"></script>
<script>
	document.querySelectorAll('.format-indian').forEach(function (el) {
		el.innerText = formatIndianNumber(el.dataset.value);
	});
</script>