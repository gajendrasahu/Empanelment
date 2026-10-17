@extends('admin.admin_master')

@section('admin')

<div class="main-content finance-dashboard-body finance-payment-cycle">
<link rel="stylesheet" href="{{ asset('panel/assets/css/finance_dashboard.css') }}" />
    <div class="main-content-inner">

        <div class="finance-dashboard-header">

            <div>
                <h1 class="welcome-title">Fund & Payment Cycle Report</h1>
                <p class="finance-dashboard-subtitle">Department receipts, deductions, invoices, finance verification, vendor payments and refundable consultancy deductions</p>
            </div>

			<form method="GET" action="{{ route('finance.payment.cycle') }}" class="finance-dashboard-period-form">
				<label>Financial Year</label>
				<select name="financial_year" onchange="this.form.submit()">
					@foreach($financialYears as $financialYear)
						<option value="{{ $financialYear }}" {{ ($selectedFinancialYear ?? '') == $financialYear ? 'selected' : '' }}>{{ $financialYear }}</option>
					@endforeach
				</select>
			</form>
        </div>


        {{-- SUMMARY CARDS --}}

        <div class="row finance-dashboard-row">

			<div class="col-lg-4 col-md-6 col-sm-6 finance-dashboard-col">
				<div class="finance-kpi-card finance-kpi-fund">
					<div class="finance-kpi-content">
						<div class="finance-kpi-icon"><i class="fa fa-university"></i></div>
						<div>
							<div class="finance-kpi-title">Opening Fund Balance</div>
							<div class="finance-kpi-value">₹ <span class="format-indian" data-value="{{ number_format($summary['opening_balance'] ?? 0,2,'.','') }}"></span></div>
							<div class="finance-kpi-description">Vendor Funding + Service Charge</div>
						</div>
					</div>
				</div>
			</div>
<div class="col-lg-4 col-md-6 col-sm-6 finance-dashboard-col">
    <div class="finance-kpi-card finance-kpi-fund">
        <div class="finance-kpi-content">
            <div class="finance-kpi-icon"><i class="fa fa-money"></i></div>
            <div>
                <div class="finance-kpi-title">Total Fund Available</div>
                <div class="finance-kpi-value">₹ <span class="format-indian" data-value="{{ number_format($summary['total_fund_available'] ?? 0,2,'.','') }}"></span></div>
                <div class="finance-kpi-description">Opening Balance + Department Receipt</div>
            </div>
        </div>
    </div>
</div>
            <div class="col-lg-4 col-md-6 col-sm-6 finance-dashboard-col">
                <div class="finance-kpi-card finance-kpi-receipt">
                    <div class="finance-kpi-content">
                        <div class="finance-kpi-icon"><i class="fa fa-university"></i></div>
                        <div>
                            <div class="finance-kpi-title">Department Receipt</div>
                            <div class="finance-kpi-value">₹ <span class="format-indian" data-value="{{ number_format($summary['department_receipt'] ?? 0,2,'.','') }}"></span></div>
                            <div class="finance-kpi-description">Gross amount received from departments</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 col-md-6 col-sm-6 finance-dashboard-col">
                <div class="finance-kpi-card finance-kpi-payment">
                    <div class="finance-kpi-content">
                        <div class="finance-kpi-icon"><i class="fa fa-minus-circle"></i></div>
                        <div>
                            <div class="finance-kpi-title">Department Deductions</div>
                            <div class="finance-kpi-value">₹ <span class="format-indian" data-value="{{ number_format($summary['department_total_deductions'] ?? 0,2,'.','') }}"></span></div>
                            <div class="finance-kpi-description">TDS + GST-TDS</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 col-md-6 col-sm-6 finance-dashboard-col">
                <div class="finance-kpi-card finance-kpi-fund">
                    <div class="finance-kpi-content">
                        <div class="finance-kpi-icon"><i class="fa fa-credit-card"></i></div>
                        <div>
                            <div class="finance-kpi-title">Net Received by CHiPS</div>
                            <div class="finance-kpi-value">₹ <span class="format-indian" data-value="{{ number_format($summary['net_received_by_chips'] ?? 0,2,'.','') }}"></span></div>
                            <div class="finance-kpi-description">After department deductions</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 col-md-6 col-sm-6 finance-dashboard-col">
                <div class="finance-kpi-card finance-kpi-invoice">
                    <div class="finance-kpi-content">
                        <div class="finance-kpi-icon"><i class="fa fa-file-text-o"></i></div>
                        <div>
                            <div class="finance-kpi-title">Invoice Raised</div>
                            <div class="finance-kpi-value">₹ <span class="format-indian" data-value="{{ number_format($summary['invoice_raised'] ?? 0,2,'.','') }}"></span></div>
                            <div class="finance-kpi-description">Total vendor invoices</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 col-md-6 col-sm-6 finance-dashboard-col">
                <div class="finance-kpi-card finance-kpi-invoice">
                    <div class="finance-kpi-content">
                        <div class="finance-kpi-icon"><i class="fa fa-check-circle"></i></div>
                        <div>
                            <div class="finance-kpi-title">Finance Verified</div>
                            <div class="finance-kpi-value">₹ <span class="format-indian" data-value="{{ number_format($summary['finance_verified'] ?? 0,2,'.','') }}"></span></div>
                            <div class="finance-kpi-description">{{ number_format($summary['fund_utilization_percent'] ?? 0,2) }}% of department receipt</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 col-md-6 col-sm-6 finance-dashboard-col">
                <div class="finance-kpi-card finance-kpi-payment">
                    <div class="finance-kpi-content">
                        <div class="finance-kpi-icon"><i class="fa fa-money"></i></div>
                        <div>
                            <div class="finance-kpi-title">Paid to Vendors</div>
                            <div class="finance-kpi-value">₹ <span class="format-indian" data-value="{{ number_format($summary['paid_to_vendor'] ?? 0,2,'.','') }}"></span></div>
                            <div class="finance-kpi-description">{{ number_format($summary['payment_completion_percent'] ?? 0,2) }}% of verified amount</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 col-md-6 col-sm-6 finance-dashboard-col">
                <div class="finance-kpi-card finance-kpi-payment">
                    <div class="finance-kpi-content">
                        <div class="finance-kpi-icon"><i class="fa fa-refresh"></i></div>
                        <div>
                            <div class="finance-kpi-title">Refundable to Vendors</div>
                            <div class="finance-kpi-value">₹ <span class="format-indian" data-value="{{ number_format($summary['refundable_to_vendor'] ?? 0,2,'.','') }}"></span></div>
                            <div class="finance-kpi-description">Department Consultancy TDS + GST-TDS</div>
                        </div>
                    </div>
                </div>
            </div>

        </div>


        {{-- PAYMENT FLOW --}}

        <div class="row finance-dashboard-row">

            <div class="col-lg-12 col-md-12 col-sm-12 finance-dashboard-col">

                <div class="finance-dashboard-card">

                    <div class="finance-dashboard-card-header">

                        <h4>
                            <span class="finance-section-icon finance-section-csf">
                                <i class="fa fa-exchange"></i>
                            </span>
                            Payment Cycle Flow
                        </h4>

                    </div>

                    <div class="finance-cycle-flow">

						<div class="finance-cycle-box finance-cycle-invoice">
							<strong>1</strong>
							<span>Opening Balance</span>
							<b>₹ <span class="format-indian" data-value="{{ number_format($summary['opening_balance'] ?? 0,2,'.','') }}"></span></b>
						</div>

						<i class="fa fa-plus finance-cycle-arrow"></i>

						<div class="finance-cycle-box finance-cycle-receipt">
							<strong>2</strong>
							<span>Department Receipt</span>
							<b>₹ <span class="format-indian" data-value="{{ number_format($summary['department_receipt'] ?? 0,2,'.','') }}"></span></b>
						</div>

						<i class="fa fa-arrow-right finance-cycle-arrow"></i>

						<div class="finance-cycle-box finance-cycle-net">
							<strong>3</strong>
							<span>Total Fund Available</span>
							<b>₹ <span class="format-indian" data-value="{{ number_format($summary['total_fund_available'] ?? 0,2,'.','') }}"></span></b>
						</div>

						<i class="fa fa-arrow-right finance-cycle-arrow"></i>

						<div class="finance-cycle-box finance-cycle-deduction">
							<strong>4</strong>
							<span>Department Deductions</span>
							<b>₹ <span class="format-indian" data-value="{{ number_format($summary['department_total_deductions'] ?? 0,2,'.','') }}"></span></b>
						</div>

						<i class="fa fa-arrow-right finance-cycle-arrow"></i>

						<div class="finance-cycle-box finance-cycle-invoice">
							<strong>5</strong>
							<span>Invoice Raised</span>
							<b>₹ <span class="format-indian" data-value="{{ number_format($summary['invoice_raised'] ?? 0,2,'.','') }}"></span></b>
						</div>

						<i class="fa fa-arrow-right finance-cycle-arrow"></i>

						<div class="finance-cycle-box finance-cycle-verified">
							<strong>6</strong>
							<span>Finance Verified</span>
							<b>₹ <span class="format-indian" data-value="{{ number_format($summary['finance_verified'] ?? 0,2,'.','') }}"></span></b>
						</div>

						<i class="fa fa-arrow-right finance-cycle-arrow"></i>

						<div class="finance-cycle-box finance-cycle-paid">
							<strong>7</strong>
							<span>Paid to Vendors</span>
							<b>₹ <span class="format-indian" data-value="{{ number_format($summary['paid_to_vendor'] ?? 0,2,'.','') }}"></span></b>
						</div>

						<i class="fa fa-arrow-right finance-cycle-arrow"></i>

						<div class="finance-cycle-box finance-cycle-refund">
							<strong>8</strong>
							<span>Refundable to Vendors</span>
							<b>₹ <span class="format-indian" data-value="{{ number_format($summary['refundable_to_vendor'] ?? 0,2,'.','') }}"></span></b>
						</div>
                    </div>

                </div>

            </div>

        </div>


        {{-- DEDUCTION SUMMARY --}}

        <div class="row finance-dashboard-row">

            <div class="col-lg-4 col-md-6 col-sm-12 finance-dashboard-col">

                <div class="finance-dashboard-card">

                    <div class="finance-dashboard-card-header">

                        <h4 class="font-14">
                            <span class="finance-section-icon finance-section-csf">
                                <i class="fa fa-university"></i>
                            </span>
                            Department Consultancy Receipt
                        </h4>

                    </div>

                    <div class="finance-fund-body">

                        <div class="finance-fund-row">
                            <span>Gross Receipt</span>
                            <strong>₹ <span class="format-indian" data-value="{{ number_format($summary['department_consultancy_receipt'] ?? 0,2,'.','') }}"></span></strong>
                        </div>

                        <div class="finance-fund-row">
                            <span>TDS</span>
                            <strong>₹ <span class="format-indian" data-value="{{ number_format($summary['department_consultancy_tds'] ?? 0,2,'.','') }}"></span></strong>
                        </div>

                        <div class="finance-fund-row">
                            <span>GST-TDS</span>
                            <strong>₹ <span class="format-indian" data-value="{{ number_format($summary['department_consultancy_gst_tds'] ?? 0,2,'.','') }}"></span></strong>
                        </div>

                        <div class="finance-fund-available">
                            <span>Refundable to Vendors</span>
                            <strong>₹ <span class="format-indian" data-value="{{ number_format($summary['refundable_to_vendor'] ?? 0,2,'.','') }}"></span></strong>
                        </div>

                    </div>

                </div>

            </div>


            <div class="col-lg-4 col-md-6 col-sm-12 finance-dashboard-col">

                <div class="finance-dashboard-card">

                    <div class="finance-dashboard-card-header">

                        <h4 class="font-14">
                            <span class="finance-section-icon finance-section-vendor">
                                <i class="fa fa-building"></i>
                            </span>
                            Department Administrative Receipt
                        </h4>

                    </div>

                    <div class="finance-fund-body">

                        <div class="finance-fund-row">
                            <span>Gross Receipt</span>
                            <strong>₹ <span class="format-indian" data-value="{{ number_format($summary['department_admin_receipt'] ?? 0,2,'.','') }}"></span></strong>
                        </div>

                        <div class="finance-fund-row">
                            <span>TDS</span>
                            <strong>₹ <span class="format-indian" data-value="{{ number_format($summary['department_admin_tds'] ?? 0,2,'.','') }}"></span></strong>
                        </div>

                        <div class="finance-fund-row">
                            <span>GST-TDS</span>
                            <strong>₹ <span class="format-indian" data-value="{{ number_format($summary['department_admin_gst_tds'] ?? 0,2,'.','') }}"></span></strong>
                        </div>

                        <div class="finance-fund-available finance-admin-note">
                            <span>Vendor Refundable</span>
                            <strong>₹ 0.00</strong>
                        </div>

                    </div>

                </div>

            </div>


            <div class="col-lg-4 col-md-6 col-sm-12 finance-dashboard-col">

                <div class="finance-dashboard-card">

                    <div class="finance-dashboard-card-header">

                        <h4 class="font-14">
                            <span class="finance-section-icon finance-section-awd">
                                <i class="fa fa-info-circle"></i>
                            </span>
                            Refundable Amount
                        </h4>

                    </div>

                    <div class="finance-fund-body">

                        <div class="finance-fund-row">
                            <span>Consultancy TDS</span>
                            <strong>₹ <span class="format-indian" data-value="{{ number_format($summary['department_consultancy_tds'] ?? 0,2,'.','') }}"></span></strong>
                        </div>

                        <div class="finance-fund-row">
                            <span>Consultancy GST-TDS</span>
                            <strong>₹ <span class="format-indian" data-value="{{ number_format($summary['department_consultancy_gst_tds'] ?? 0,2,'.','') }}"></span></strong>
                        </div>

                        <div class="finance-fund-available">
                            <span>Total Refundable</span>
                            <strong>₹ <span class="format-indian" data-value="{{ number_format($summary['refundable_to_vendor'] ?? 0,2,'.','') }}"></span></strong>
                        </div>

                    </div>

                </div>

            </div>

        </div>


        {{-- EOI DETAIL TABLE --}}

		<div class="row finance-dashboard-row">
			<div class="col-lg-12 col-md-12 col-sm-12 finance-dashboard-col">
				<div class="finance-dashboard-card">
					<div class="finance-dashboard-card-header">
						<div>
							<h4 class="font-14">
								<span class="finance-section-icon finance-section-csf">
									<i class="fa fa-list-alt"></i>
								</span>
								EOI / Project Wise Financial Details
							</h4>

							<span>Opening balance → department receipt → fund available → invoice → verification → vendor payment → refundable amount</span>
						</div>
					</div>
					<div class="finance-report-table-wrapper padding-10">
						<table class="table finance-dashboard-table finance-cycle-table">
							<thead>
								<tr>
									<th rowspan="2">#</th>
									<th rowspan="2">EOI / Project</th>
									<th rowspan="2">Department</th>
									<th colspan="4" class="finance-group-department">Vendor Funding / Consultancy</th>
									<th colspan="4" class="finance-group-department">Service Charge / Admin</th>
									<th colspan="2" class="finance-group-department">Department Deductions</th>
									<th colspan="2" class="finance-group-invoice">Invoice & Verification</th>
									<th colspan="3" class="finance-group-payment">CHiPS Payment</th>
									<th colspan="3" class="finance-group-refund">Refundable to Vendor</th>
								</tr>
								<tr>

									<th>Opening</th>
									<th>Receipt</th>
									<th>Available</th>
									<th>Net Received</th>

									<th>Opening</th>
									<th>Receipt</th>
									<th>Available</th>
									<th>Net Received</th>

									<th>TDS</th>
									<th>GST-TDS</th>

									<th>Invoice Raised</th>
									<th>Finance Verified</th>

									<th>Consultancy Fee</th>
									<th>Admin Charges</th>
									<th>Paid to Vendor</th>

									<th>TDS</th>
									<th>GST-TDS</th>
									<th>Total Refundable</th>

								</tr>

							</thead>

							<tbody>

								@forelse($report as $row)
								<tr>
									<td>{{ $loop->iteration }}</td>

									<td>
										<strong>{{ $row->eoinumber }}</strong>
										<small>{{ $row->projecttitle }}</small>
									</td>

									<td>{{ $row->department_name ?? '-' }}</td>

									{{-- VENDOR FUNDING / CONSULTANCY --}}

									<td class="text-right">
										₹ {{ number_format($row->vendor_funding_opening_balance,2) }}
									</td>

									<td class="text-right">
										₹ {{ number_format($row->vendor_funding_department_receipt,2) }}
									</td>

									<td class="text-right">
										₹ {{ number_format($row->vendor_funding_total_available,2) }}
									</td>

									<td class="text-right">
										₹ {{ number_format($row->consultancy_net_received,2) }}
									</td>

									{{-- SERVICE CHARGE / ADMIN --}}

									<td class="text-right">
										₹ {{ number_format($row->service_charge_opening_balance,2) }}
									</td>

									<td class="text-right">
										₹ {{ number_format($row->service_charge_department_receipt,2) }}
									</td>

									<td class="text-right">
										₹ {{ number_format($row->service_charge_total_available,2) }}
									</td>

									<td class="text-right">
										₹ {{ number_format($row->admin_net_received,2) }}
									</td>

									{{-- DEPARTMENT DEDUCTIONS --}}

									<td class="text-right">
										₹ {{ number_format($row->total_department_tds,2) }}
									</td>

									<td class="text-right">
										₹ {{ number_format($row->total_department_gst_tds,2) }}
									</td>

									{{-- INVOICE & VERIFICATION --}}

									<td class="text-right">
										₹ {{ number_format($row->invoice_raised,2) }}
									</td>

									<td class="text-right">
										₹ {{ number_format($row->finance_verified,2) }}
									</td>

									{{-- CHIPS PAYMENT --}}

									<td class="text-right">
										₹ {{ number_format($row->consultancy_fee,2) }}
									</td>

									<td class="text-right">
										₹ {{ number_format($row->admin_charge,2) }}
									</td>

									<td class="text-right">
										₹ {{ number_format($row->paid_to_vendor,2) }}
									</td>

									{{-- REFUNDABLE --}}

									<td class="text-right finance-refund-value">
										₹ {{ number_format($row->consultancy_tds,2) }}
									</td>

									<td class="text-right finance-refund-value">
										₹ {{ number_format($row->consultancy_gst_tds,2) }}
									</td>

									<td class="text-right finance-refund-total">
										₹ {{ number_format($row->refundable_to_vendor,2) }}
									</td>

								</tr>

								@empty

								<tr>
									<td colspan="22" class="text-center finance-empty-row">
										No financial records found.
									</td>
								</tr>

								@endforelse

							</tbody>

							@if($report->count() > 0)

							<tfoot>

								<tr>

									<th colspan="3" class="text-right">Total</th>

									{{-- VENDOR FUNDING --}}

									<th class="text-right">
										₹ {{ number_format($summary['vendor_funding_opening_balance'],2) }}
									</th>

									<th class="text-right">
										₹ {{ number_format($summary['vendor_funding_department_receipt'],2) }}
									</th>

									<th class="text-right">
										₹ {{ number_format($summary['vendor_funding_total_available'],2) }}
									</th>

									<th class="text-right">
										₹ {{ number_format($summary['department_consultancy_net_received'],2) }}
									</th>

									{{-- SERVICE CHARGE --}}

									<th class="text-right">
										₹ {{ number_format($summary['service_charge_opening_balance'],2) }}
									</th>

									<th class="text-right">
										₹ {{ number_format($summary['service_charge_department_receipt'],2) }}
									</th>

									<th class="text-right">
										₹ {{ number_format($summary['service_charge_total_available'],2) }}
									</th>

									<th class="text-right">
										₹ {{ number_format($summary['department_admin_net_received'],2) }}
									</th>

									{{-- DEPARTMENT DEDUCTIONS --}}

									<th class="text-right">
										₹ {{ number_format($summary['department_total_tds'],2) }}
									</th>

									<th class="text-right">
										₹ {{ number_format($summary['department_total_gst_tds'],2) }}
									</th>

									{{-- INVOICE & VERIFICATION --}}

									<th class="text-right">
										₹ {{ number_format($summary['invoice_raised'],2) }}
									</th>

									<th class="text-right">
										₹ {{ number_format($summary['finance_verified'],2) }}
									</th>

									{{-- CHIPS PAYMENT --}}

									<th class="text-right">
										₹ {{ number_format($summary['consultancy_fee'],2) }}
									</th>

									<th class="text-right">
										₹ {{ number_format($summary['admin_charge'],2) }}
									</th>

									<th class="text-right">
										₹ {{ number_format($summary['paid_to_vendor'],2) }}
									</th>

									{{-- REFUNDABLE --}}

									<th class="text-right">
										₹ {{ number_format($summary['department_consultancy_tds'],2) }}
									</th>

									<th class="text-right">
										₹ {{ number_format($summary['department_consultancy_gst_tds'],2) }}
									</th>

									<th class="text-right finance-refund-total">
										₹ {{ number_format($summary['refundable_to_vendor'],2) }}
									</th>

								</tr>

							</tfoot>

							@endif

						</table>

					</div>

				</div>

			</div>

		</div>

        {{-- IMPORTANT NOTE --}}

        <div class="row finance-dashboard-row">

            <div class="col-lg-12 finance-dashboard-col">

                <div class="finance-report-note">

                    <i class="fa fa-info-circle"></i>

                    <div>

                        <strong>Important:</strong>

                        Department TDS and GST-TDS deducted against <strong>Consultancy / Vendor Funding</strong> are included in the refundable amount to vendors.

                        Department TDS and GST-TDS deducted against <strong>Administrative Charges</strong> are not refundable to vendors.

                        TDS and GST-TDS deducted by CHiPS while making vendor payment are not included in this refundable calculation.

                    </div>

                </div>

            </div>

        </div>

    </div>

</div>


<script src="{{ asset('panel/assets/js/chart.js') }}"></script>
<script src="{{ asset('panel/assets/js/chart-label.js') }}"></script>

<script src="{{ asset('panel/assets/js/datatablePaginationWithFilter.js') }}"></script>

<script>
document.addEventListener('DOMContentLoaded',function()
{
    document.querySelectorAll('.format-indian').forEach(function(el)
    {
        if(typeof formatIndianNumber === 'function')
        {
            el.innerText = formatIndianNumber(el.dataset.value);
        }
        else
        {
            el.innerText = Number(el.dataset.value || 0).toLocaleString('en-IN',{minimumFractionDigits:2,maximumFractionDigits:2});
        }
    });
});

document.querySelectorAll('.format-indian').forEach(function(el){
	el.style.fontWeight = 'bold';
	el.innerText = formatIndianNumber(el.dataset.value);
});

</script>

@endsection