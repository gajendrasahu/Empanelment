<div class="col-sm-12 dashboard-cards-row">
	@permission('eoi.requestlist')
	<div class="col-sm-2">
	<a href="{{route('eoi.requestlist',['firm_type'=>$firm_type])}}">
		<div class="dashboard-horizontal-card bg-yellow">
			<span class="material-icons-outlined dashboard-icon">
				assignment
			</span>
			<div>
				<div class="dashboard-card-count">{{ $eoi }}</div>
				<p class="dashboard-card-title">Requests Received</p>
			</div>
		</div>
	</a>
	</div>
	@endpermission
	
	@permission('eoi.requestlist')
	<div class="col-sm-2">
	<a href="{{ route('eoi.requestlist', ['firm_type' => $firm_type, 'record_type' => Crypt::encrypt('102')]) }}">
		<div class="dashboard-horizontal-card bg-orange">
			<span class="material-icons-outlined dashboard-icon">
				task_alt
			</span>
			<div>
				<div class="dashboard-card-count">{{ $approved }}</div>
				<p class="dashboard-card-title">Draft EoI Approved</p>
			</div>
		</div>
	</a>
	</div>
	@endpermission
	
	@permission('eoi.requestlist')
	<div class="col-sm-2">
	<a href="{{ route('eoi.requestlist', ['firm_type' => $firm_type, 'record_type' => Crypt::encrypt('103')]) }}">
		<div class="dashboard-horizontal-card bg-blue">
			<span class="material-icons-outlined dashboard-icon">
				publish
			</span>
			<div>
				<div class="dashboard-card-count">{{ $floated }}</div>
				<p class="dashboard-card-title">EoI Published</p>
			</div>
		</div>
	</a>
	</div>
	@endpermission
	
	@permission('eoi.requestlist')
	<div class="col-sm-2">
	<a href="{{ route('eoi.requestlist',['firm_type' => $firm_type,'record_type' => Crypt::encrypt('104')])}}">
		<div class="dashboard-horizontal-card bg-purple">
			<span class="material-icons-outlined dashboard-icon">
				trending_up
			</span>
			<div>
				<div class="dashboard-card-count">{{ $progress }}</div>
				<p class="dashboard-card-title">Active EoI</p>
			</div>
		</div>
	</a>
	</div>
	@endpermission
	
	@permission('eoi.requestlist')
	<div class="col-sm-2">
	<a href="{{ route('eoi.requestlist',['firm_type' => $firm_type,'record_type' => Crypt::encrypt('105')])}}">
		<div class="dashboard-horizontal-card bg-red">
			<span class="material-icons-outlined dashboard-icon">
				groups
			</span>
			<div>
				<div class="dashboard-card-count">{{ $presentation }}</div>
				<p class="dashboard-card-title">Upcoming Interview</p>
			</div>
		</div>
	</a>
	</div>
	@endpermission
	
	@permission('workorder.list')
	<div class="col-sm-2">
	<a href="{{ route('workorder.list',['firm_type' => $firm_type])}}">
		<div class="dashboard-horizontal-card bg-green">
			<span class="material-icons-outlined dashboard-icon">
				work
			</span>
			<div>
				<div class="dashboard-card-count">{{ $wos }}</div>
				<p class="dashboard-card-title">Active Work Orders</p>
			</div>
		</div>
	</a>
	</div>
	@endpermission
</div>





<!-- <div class="col-sm-12">&nbsp;</div>

<div class="col-sm-12">



	<div class="col-sm-3">
		<div class="dashboard-horizontal-card bg-purple-light">
			<img src="{{ asset('panel/assets/images/icons/icon_7.png') }}" class="dashboard_icons">
			<div class="dashboard-card-count">{{$departments}}</div>
			<div class="dashboard-card-header">
				<h4 class="dashboard-card-title">Total Departments</h4>
			</div>
		</div>
	</div>

	<div class="col-sm-3">
		<div class="dashboard-horizontal-card bg-pink-light">
			<img src="{{ asset('panel/assets/images/icons/icon_8.png') }}" class="dashboard_icons">
			<div class="dashboard-card-count">{{$resources}}</div>
			<div class="dashboard-card-header">
				<h4 class="dashboard-card-title">Total Resources</h4>
			</div>
		</div>
	</div>


</div> -->

<div class="col-sm-12">&nbsp;</div>
<div class="col-sm-12" style="display: flex; gap: 24px; padding: 16px 24px">
	<div class="card col-sm-4">
		<div class="dashboard-charts">
			<div class="dashboard-chart-header">
				<h4 class="dashboard-chart-title">Workforce Composition</h4>
			</div>
			<div class="chart-wrapper">
				<canvas id="resourceChart" width="280" height="280"></canvas>
			</div>
		</div>
	</div>
	<div class="card col-sm-8 delay-04">
		<div class="dashboard-charts" style="height:100%; overflow-y:auto;">
			<div class="dashboard-chart-header">
				<h4 class="dashboard-chart-title">Firm Utilisation</h4>
			</div>
			<div class="chart-wrapper">
				<canvas id="firmResourceChart"></canvas>
			</div>
		</div>
	</div>
</div>
<div class="col-sm-12">&nbsp;</div>
<div class="col-sm-12">
	<div class="col-sm-12">
		<div class="table-responsive">
			<table class="table table-bordered table-striped table-hover" id="tablerecords">
				<thead>
					<tr class="dashboard-table-head">
						<td colspan="5" class="v-middle" style="font-size: 16px !important;">
							Firm-wise Order Details
						</td>
					</tr>
					<tr>
						<td nowrap class="center width-30">S.No.</td>
						<td nowrap>Firm Name</td>
						<td nowrap class="center">Tier</td>
						<td nowrap class="center">Total Work Orders</td>
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
							<td nowrap>
								<!-- <img src="{{ asset('panel/assets/images/icons/icon_10.png') }}" class="dashboard_icons"
																																										style="float:left; margin-right:10px;"> -->
								<span class="firm-name first-column-name">
									{{ ucwords(strtolower($item->companyname)) }}
								</span>
							</td>
							<td nowrap class="center">
								<span class="firm-tier {{ $tierClass }}">
									{{ $item->tiername }}
								</span>
							</td>
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
</div>
<div class="col-sm-12">&nbsp;</div>
@php



@endphp
<script src="{{ asset('panel/assets/js/chart.js') }}"></script>
<script src="{{ asset('panel/assets/js/chart-label.js') }}"></script>

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
	   DOUGHNUT – Workforce Composition
	================================ */
	const ctx = document.getElementById('resourceChart').getContext('2d');

	new Chart(ctx, {
		type: 'doughnut',
		data: {
			labels: [
				'AWD ({{ intval($awd_staff) }} Resources)',
				'CSF ({{ intval($csf_staff) }} Resources)'
			],
			datasets: [{
				data: [{{ $awd_percentage }}, {{ $csf_percentage }}],
				backgroundColor: ['#f9652a', '#6629f5'],
				hoverBackgroundColor: ['#f9652a', '#6629f5'],
				borderWidth: 0,
				borderRadius: 10,
				spacing: 6
			}]
		},
		options: {
			cutout: '65%',
			responsive: true,
			maintainAspectRatio: false,
			animation: {
				duration: 900,
				easing: 'easeOutQuart'
			},
			plugins: {
				legend: {
					position: 'bottom',
					labels: {
						usePointStyle: true,
						pointStyle: 'circle',
						padding: 18,
						boxWidth: 10,
						color: '#374151'
					}
				}
			}
		}
	});


	/* ==============================
	   BAR – Firm Utilisation
	================================ */
	const categories = @json($vendor_category);
	const resourceCounts = @json($vendor_resource_count);
	const maxValue = Math.max(...resourceCounts);

	const barColors = categories.map(cat =>
		cat == 1 ? '#f9652a' : '#6629f5'
	);

	const ctx1 = document.getElementById('firmResourceChart').getContext('2d');

	new Chart(ctx1, {
		type: 'bar',
		data: {
			labels: @json($vendor_resource_name),
			datasets: [{
				data: resourceCounts,
				backgroundColor: barColors,
				borderRadius: 4,
				borderSkipped: false,
				barPercentage: 1,
				categoryPercentage: 0.6
			}]
		},
		options: {
			responsive: true,
			maintainAspectRatio: false,
			animation: {
				duration: 900,
				easing: 'easeOutQuart'
			},
			plugins: {
				legend: { display: false },
				datalabels: {
					anchor: 'end',
					align: 'end',
					color: '#111827',
					font: {
						weight: '600',
						size: 11
					}
				},
				tooltip: {
					callbacks: {
						label: ctx => ` ${ctx.parsed.y} Resources`
					}
				}
			},
			scales: {
				x: {
					grid: { display: false },
					ticks: {
						color: '#6b7280',
						font: { size: 12 }
					},
					title: {
						display: true,
						text: 'Firm Name',
						color: '#374151',
						font: { weight: '600' }
					}
				},
				y: {
					beginAtZero: true,
					max: maxValue + 10,
					grid: {
						color: '#e5e7eb',
						drawBorder: false
					},
					ticks: {
						color: '#6b7280',
						font: { size: 11 }
					},
					title: {
						display: true,
						text: 'Resource Count',
						color: '#374151',
						font: { weight: '600' }
					}
				}
			}
		},
		plugins: [ChartDataLabels]
	});
</script>


<script src="{{ asset('panel/assets/js/datatablePaginationWithFilter.js') }}"></script>
<script>
	document.querySelectorAll('.format-indian').forEach(function (el) {
		el.innerText = formatIndianNumber(el.dataset.value);
	});
</script>