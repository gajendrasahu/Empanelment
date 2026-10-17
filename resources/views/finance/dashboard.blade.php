@extends('admin.admin_master')

@section('admin')

<div class="main-content finance-dashboard-body">
<link rel="stylesheet" href="{{ asset('panel/assets/css/finance_dashboard.css') }}" />
    <div class="main-content-inner">

        {{-- DASHBOARD HEADER --}}
        <div class="finance-dashboard-header">

            <div>
                <h1 class="welcome-title">Finance Dashboard</h1>
                <p class="finance-dashboard-subtitle">Overview of Department Payments, Vendor Invoices, Vendor Payments and Fund Position</p>
            </div>
        </div>


        {{-- KPI CARDS --}}
        <div class="row finance-dashboard-row">

            <div class="col-lg-4 col-md-6 col-sm-6 finance-dashboard-col">

                <div class="finance-kpi-card finance-kpi-invoice">

                    <div class="finance-kpi-content">

                        <div class="finance-kpi-icon">
                            <i class="fa fa-file-text-o"></i>
                        </div>

                        <div class="finance-kpi-details">

                            <div class="finance-kpi-title">Pending Vendor Invoices</div>

                            <div class="finance-kpi-value">
                                {{ $data['pendingVendorInvoices'] ?? 0 }}
                            </div>

                            <div class="finance-kpi-description">
                                Awaiting Finance Verification
                            </div>

                        </div>

                    </div>

                    <div class="finance-kpi-footer">
                        <span>Vendor Invoices</span>
                        <span class="finance-kpi-arrow">
                            <i class="fa fa-arrow-right"></i>
                        </span>
                    </div>

                </div>

            </div>


            <div class="col-lg-4 col-md-6 col-sm-6 finance-dashboard-col">

                <div class="finance-kpi-card finance-kpi-payment">

                    <div class="finance-kpi-content">

                        <div class="finance-kpi-icon">
                            <i class="fa fa-credit-card"></i>
                        </div>

                        <div class="finance-kpi-details">

                            <div class="finance-kpi-title">Pending Vendor Payments</div>

                            <div class="finance-kpi-value">
                                {{ $data['pendingVendorPayments'] ?? 0 }}
                            </div>

                            <div class="finance-kpi-description">
                                Verified Invoices Awaiting Payment
                            </div>

                        </div>

                    </div>

                    <div class="finance-kpi-footer">
                        <span>Vendor Payments</span>
                        <span class="finance-kpi-arrow">
                            <i class="fa fa-arrow-right"></i>
                        </span>
                    </div>

                </div>

            </div>


            <div class="col-lg-4 col-md-6 col-sm-6 finance-dashboard-col">

                <div class="finance-kpi-card finance-kpi-receipt">

                    <div class="finance-kpi-content">

                        <div class="finance-kpi-icon">
                            <i class="fa fa-university"></i>
                        </div>

                        <div class="finance-kpi-details">

                            <div class="finance-kpi-title">Total Department Receipt</div>

                            <div class="finance-kpi-value">
                                ₹ <span class="format-indian" data-value="{{ number_format($data['totalDepartmentReceipt'] ?? 0,'2','.','') }}"></span>
                            </div>

                            <div class="finance-kpi-description">
                                Gross Received Amount
                            </div>

                        </div>

                    </div>

                    <div class="finance-kpi-footer">
                        <span>Department Payments</span>
                        <span class="finance-kpi-arrow">
                            <i class="fa fa-arrow-right"></i>
                        </span>
                    </div>

                </div>

            </div>


			<div class="col-lg-4 col-md-6 col-sm-6 finance-dashboard-col">

				<div class="finance-kpi-card finance-kpi-fund">

					<div class="finance-kpi-content">

						<div class="finance-kpi-icon">
							<i class="fa fa-money"></i>
						</div>

						<div class="finance-kpi-details">

							<div class="finance-kpi-title">CSF Department Fund</div>

							<div class="finance-kpi-value">
								₹ <span class="format-indian" data-value="{{ number_format($data['csfFundPosition']['available'] ?? 0,'2','.','') }}"></span>
							</div>

							<div class="finance-kpi-description">
								Available Balance
							</div>

						</div>

					</div>

					<div class="finance-kpi-footer">
						<span>
							Receipt:
							₹ <span class="format-indian" data-value="{{ number_format($data['csfFundPosition']['received'] ?? 0,'2','.','') }}"></span>
						</span>

						<span>
							Utilized:
							₹ <span class="format-indian" data-value="{{ number_format($data['csfFundPosition']['utilized'] ?? 0,'2','.','') }}"></span>
						</span>
					</div>

				</div>

			</div>
        </div>



        {{-- RECENT DATA --}}
        <div class="row finance-dashboard-row">

            <div class="col-lg-12 finance-dashboard-col">

                <div class="finance-dashboard-card padding-10">

                    <div class="finance-dashboard-card-header">

                        <h4>Recent Vendor Invoices</h4>

                        <a href="#" class="finance-view-all">
                            View All
                        </a>

                    </div>

                    <div class="table-responsive">

                        <table class="table finance-dashboard-table">

                            <thead>

                                <tr>
                                    <th>#</th>
                                    <th>Invoice Number</th>
                                    <th>Vendor</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                </tr>

                            </thead>

                            <tbody>

                                @forelse($data['recentInvoices'] ?? [] as $invoice)

                                    <tr>

                                        <td>{{ $loop->iteration }}</td>

                                        <td>
                                            <strong>{{ $invoice->invoice_number }}</strong>
                                        </td>

                                        <td>
                                            {{ $invoice->vendor_name ?? '-' }}
                                        </td>

                                        <td>
                                            ₹ <span class="format-indian" data-value="{{ number_format($invoice->net_invoice_value ?? 0,'2','.','') }}"></span>
                                        </td>

                                        <td>

                                            @if(($invoice->payment_status ?? '') == 'Paid')

                                                <span class="finance-status finance-status-paid">Fully Paid</span>

                                            @elseif(($invoice->payment_status ?? '') == 'Partially Paid')

                                                <span class="finance-status finance-status-partial">Partially Paid</span>

                                            @elseif(($invoice->finance_status ?? '') == 'Pending')

                                                <span class="finance-status finance-status-pending">Pending</span>

                                            @else

                                                <span class="finance-status finance-status-unpaid">Unpaid</span>

                                            @endif

                                        </td>

                                    </tr>

                                @empty

                                    <tr>
                                        <td colspan="5" class="text-center">No vendor invoices found.</td>
                                    </tr>

                                @endforelse

                            </tbody>

                        </table>

                    </div>

                </div>

            </div>


            <div class="col-lg-12 finance-dashboard-col">

                <div class="finance-dashboard-card padding-10">

                    <div class="finance-dashboard-card-header">

                        <h4>Recent Vendor Payments</h4>

                        <a href="#" class="finance-view-all">
                            View All
                        </a>

                    </div>

                    <div class="table-responsive">

                        <table class="table finance-dashboard-table">

                            <thead>

                                <tr>
                                    <th>#</th>
                                    <th>Payment Date</th>
                                    <th>Vendor</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                </tr>

                            </thead>

                            <tbody>

                                @forelse($data['recentPayments'] ?? [] as $payment)

                                    <tr>

                                        <td>{{ $loop->iteration }}</td>

                                        <td>
                                            {{ !empty($payment->payment_date) ? date('d-m-Y',strtotime($payment->payment_date)) : '-' }}
                                        </td>

                                        <td>
                                            {{ $payment->vendor_name ?? '-' }}
                                        </td>

                                        <td>
                                            ₹ <span class="format-indian" data-value="{{ number_format($payment->gross_amount ?? 0,'2','.','') }}"></span>
                                        </td>

                                        <td>
                                            <span class="finance-status finance-status-paid">Paid</span>
                                        </td>

                                    </tr>

                                @empty

                                    <tr>
                                        <td colspan="5" class="text-center">No vendor payments found.</td>
                                    </tr>

                                @endforelse

                            </tbody>

                        </table>

                    </div>

                </div>

            </div>


			<div class="col-lg-12 col-md-12 col-sm-12 finance-dashboard-col">

				<div class="finance-dashboard-card">

					<div class="finance-dashboard-card-header">

						<h4>
							<span class="finance-section-icon finance-section-csf">
								<i class="fa fa-bar-chart"></i>
							</span>
							Vendor Wise Payment Details
						</h4>

					</div>

					<div class="finance-chart-body padding-10" style="height:420px;">

						<canvas id="vendorPaymentChart"></canvas>

					</div>

				</div>

			</div>

        </div>
    </div>

</div>




{{-- CHART.JS --}}
<script src="{{ asset('panel/assets/js/chart.js') }}"></script>
<script src="{{ asset('panel/assets/js/chart-label.js') }}"></script>

<script src="{{ asset('panel/assets/js/datatablePaginationWithFilter.js') }}"></script>
<script>

document.addEventListener('DOMContentLoaded', function()
{
    var vendorPaymentCanvas = document.getElementById('vendorPaymentChart');

    if(!vendorPaymentCanvas)
    {
        return;
    }

    var vendorPaymentData = @json($data['vendorPaymentChart'] ?? []);

    if(!Array.isArray(vendorPaymentData) || vendorPaymentData.length === 0)
    {
        return;
    }

    var vendorLabels = vendorPaymentData.map(function(item)
    {
        return item.vendor_name || 'Unknown Vendor';
    });

    var invoiceRaisedData = vendorPaymentData.map(function(item)
    {
        return parseFloat(item.invoice_raised || 0);
    });

    var financeVerifiedData = vendorPaymentData.map(function(item)
    {
        return parseFloat(item.finance_verified || 0);
    });

    var paidData = vendorPaymentData.map(function(item)
    {
        return parseFloat(item.paid || 0);
    });

    var balanceData = vendorPaymentData.map(function(item)
    {
        return parseFloat(item.balance || 0);
    });

    new Chart(vendorPaymentCanvas,
    {
        type: 'bar',

        data:
        {
            labels: vendorLabels,

            datasets:
            [
                {
                    label: 'Invoice Raised',
                    data: invoiceRaisedData,
                    borderWidth: 1,
					barThickness: 30,
                },
                {
                    label: 'Finance Verified',
                    data: financeVerifiedData,
                    borderWidth: 1,
					barThickness: 30,
                },
                {
                    label: 'Paid',
                    data: paidData,
                    borderWidth: 1,
					barThickness: 30,
                },
                {
                    label: 'Balance',
                    data: balanceData,
                    borderWidth: 1,
					barThickness: 30,
                }
            ]
        },

        options:
        {
            responsive: true,
            maintainAspectRatio: false,

			datasets:
			{
				bar:
				{
					categoryPercentage: 0.85,
					barPercentage: 0.85,
					maxBarThickness: 25
				}
			},

            interaction:
            {
                mode: 'index',
                intersect: false
            },

            plugins:
            {
                legend:
                {
                    position: 'top'
                },

                tooltip:
                {
                    callbacks:
                    {
                        label: function(context)
                        {
                            return context.dataset.label + ': ₹ ' + formatIndianNumber(context.raw);
                        }
                    }
                }
            },

            scales:
            {
                x:
                {
                    stacked: false,

                    ticks:
                    {
						color: '#000000',
                        autoSkip: false,
                        maxRotation: 45,
                        minRotation: 0
                    },
					title:
					{
						color: '#000000'
					}
                },
                y:
                {
                    beginAtZero:true,
                    ticks:
                    {
						color: '#000000',
                        callback: function(value)
                        {
                            return '₹ ' + formatIndianNumber(value);
                        }
                    },
					title:
					{
						color: '#000000'
					}
                }
            }
        }
    });
});


document.querySelectorAll('.format-indian').forEach(function(el){
	el.style.fontWeight = 'bold';
	el.innerText = formatIndianNumber(el.dataset.value);
});

</script>
@endsection