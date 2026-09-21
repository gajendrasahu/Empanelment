@extends('admin.admin_master')

@section('admin')

<div class="main-content">
	<div class="main-content-inner">
		<div class="page-content">

			<form id="frmVendorPayment" method="POST" action="{{ route('finance.vendorpayment.store') }}" enctype="multipart/form-data">

				@csrf

				<input type="hidden" name="voucher_id" value="{{ $invoice->voucher_id }}">

				<div class="card mt-3">
					<div class="card-header">
						<h4 class="font-16 font-bold">Vendor Payment</h4>
					</div>

					<div class="card-body mt-10">
						<div class="row">
							<div class="col-md-3 mb-3">
								<label>Invoice No.</label>
								<input type="text" class="form-control" value="{{ $invoice->invoice_number ?: '-' }}" readonly>
							</div>

							<div class="col-md-3 mb-3">
								<label>Invoice Date</label>
								<input type="text" class="form-control" value="{{ $invoice->invoice_date ? date('d-m-Y',strtotime($invoice->invoice_date)) : '-' }}" readonly>
							</div>

							<div class="col-md-3 mb-3">
								<label>Vendor</label>
								<input type="text" class="form-control" value="{{ $invoice->companyname ?: '-' }}" readonly>
							</div>

							<div class="col-md-3 mb-3">
								<label>Vendor GSTIN</label>
								<input type="text" class="form-control" value="{{ $invoice->gstnumber ?: '-' }}" readonly>
							</div>
						</div>

						<div class="row mt-10">
							<div class="col-md-3 mb-3">
								<label>EoI No.</label>
								<input type="text" class="form-control" value="{{ $invoice->eoinumber ?: '-' }}" readonly>
							</div>

							<div class="col-md-3 mb-3">
								<label>Work Order No.</label>
								<input type="text" class="form-control" value="{{ $invoice->ordernumber ?: '-' }}" readonly>
							</div>

							<div class="col-md-3 mb-3">
								<label>Invoice Taxable Amount</label>
								<input type="text" id="invoice_taxable_amount_display" class="form-control" value="{{ number_format((float)$invoice->taxable_amount,2,'.','') }}" readonly>
							</div>

							<div class="col-md-3 mb-3">
								<label>Gross Invoice Value</label>
								<input type="text" id="invoice_gross_amount_display" class="form-control" value="{{ number_format((float)$invoice->net_invoice_value,2,'.','') }}" readonly>
							</div>
						</div>
					</div>
				</div>


				@if($caseType == 'CSF')
				{{-- =========================================================
					FUND AVAILABILITY
				========================================================= --}}

				<div class="card mt-10">

					<div class="card-header">
						<h4 class="font-16 font-bold">
							Fund Availability Against Vendor Invoice
						</h4>
					</div>

					<div class="card-body mt-10">

						<table class="table table-bordered">

							
								<tr class="myheadbg font-bold">
									<th>Particulars</th>
									<th style="text-align:right;">Vendor Invoice</th>
									<th style="text-align:right;">CHiPS Administrative Charges @ 5%</th>
									<th style="text-align:right;">Total Invoice Raised by CHiPS</th>
								</tr>
							

							<tbody>

								<tr>
									<td class="myheadbg">Basic</td>

									<td style="text-align:right;">
										<span id="fund_invoice_basic"></span>
									</td>

									<td style="text-align:right;">
										<span id="fund_admin_basic"></span>
									</td>

									<td style="text-align:right;">
										<span id="fund_total_basic"></span>
									</td>
								</tr>

								<tr>
									<td class="myheadbg">
										Tax @
										<span id="department_gst_rate_display">0.00</span>%
									</td>

									<td style="text-align:right;">
										<span id="fund_invoice_gst"></span>
									</td>

									<td style="text-align:right;">
										Tax @ {{$departmentGstRate}}% | <span id="fund_admin_gst"></span>
									</td>

									<td style="text-align:right;">
										<span id="fund_total_gst"></span>
									</td>
								</tr>

								<tr>
									<td class="myheadbg">
										<b>Total</b>
									</td>

									<td style="text-align:right;">
										<b><span id="fund_invoice_total"></span></b>
									</td>

									<td style="text-align:right;">
										<b><span id="fund_admin_total"></span></b>
									</td>

									<td style="text-align:right;">
										<b><span id="fund_total_invoice"></span></b>
									</td>
								</tr>

								<tr>
									<td class="myheadbg no_wrap">
										GST TDS
										{{$vendorFundingGstTdsRate}}%
										(Deducted by Department)
									</td>

									<td style="text-align:right;">
										<span id="fund_invoice_gst_tds"></span>
									</td>

									<td style="text-align:right;">
									GST-TDS @ {{$serviceChargeGstTdsRate}}% | <span id="fund_admin_gst_tds"></span>
									</td>

									<td style="text-align:right;">
										<span id="fund_total_gst_tds"></span>
									</td>
								</tr>

								<tr>
									<td class="myheadbg no_wrap">
										TDS
										{{$vendorFundingTdsRate}}%
										(Deducted by Department)
									</td>

									<td style="text-align:right;">
										<span id="fund_invoice_tds"></span>
									</td>

									<td style="text-align:right;">
										TDS @ {{$serviceChargeTdsRate}}% | <span id="fund_admin_tds"></span>
									</td>

									<td style="text-align:right;">
										<span id="fund_total_tds"></span>
									</td>
								</tr>

								<tr>
									<td class="myheadbg">
										<b>Net Received Amount / Available Fund</b>
									</td>

									<td style="text-align:right;">
										<b><span id="fund_invoice_net"></span></b>
									</td>

									<td style="text-align:right;">
										<b><span id="fund_admin_net"></span></b>
									</td>

									<td style="text-align:right;">
										<b><span id="department_available_fund"></span></b>
									</td>
								</tr>

							</tbody>

						</table>

					</div>

				</div>


				@endif


				{{-- =========================================================
					VENDOR PAYMENT CALCULATION
				========================================================= --}}

				<div class="card mt-10">

					<div class="card-header">
						<h4 class="font-16 font-bold">
							Vendor Payment Calculation
						</h4>
					</div>

					<div class="card-body mt-10">

						<div class="row">

							<div class="col-md-4 mb-3">
								<label>Payment Amount</label>

								<input type="text" name="gross_amount" id="gross_amount" class="form-control" value="{{ number_format((float)$invoiceBalance,2,'.','') }}" required onkeyup="calculateVendorPayment()" onchange="calculateVendorPayment()" autocomplete="off" readonly>

								<small class="text-muted">
									Maximum payable amount:
									₹<span id="max_payment_amount"></span>
								</small>
							</div>

							<div class="col-md-4 mb-3">
								<label>TDS*</label>

								<select name="tds_tax_id" id="tds_tax_id" class="form-control" onchange="calculateVendorPayment()">
				<option value="">TDS</option>
				@foreach($tdsTaxes as $tax)
				
				<option value="{{ $tax->tax_id }}" data-rate="{{ (float)$tax->rate }}" {{ $defaultTdsTaxId == $tax->tax_id ? 'selected' : '' }}>
					{{ $tax->tax_name }} ({{ number_format((float)$tax->rate,2) }}%)
				</option>
				
				@endforeach

								</select>
							</div>

							<div class="col-md-4 mb-3">
								<label>GST-TDS*</label>

								<select name="gst_tds_tax_id" id="gst_tds_tax_id" class="form-control" onchange="calculateVendorPayment()">
								<option value="">GST-TDS</option>
								@foreach($gstTdsTaxes as $tax)
								<option value="{{ $tax->tax_id }}" data-rate="{{ (float)$tax->rate }}" {{ $defaultGstTdsTaxId == $tax->tax_id ? 'selected' : '' }}>
								{{ $tax->tax_name }} ({{ number_format((float)$tax->rate,2) }}%)
								</option>
								@endforeach
								</select>
							</div>

						</div>


						@if($caseType == 'CSF')
						<table class="table table-bordered mt-10">
							<tbody>
								<tr>
									<td class="myheadbg width-350"><b>Total Received (A)</b></td>
									<td style="text-align:right;"><b><span id="vendor_total_received"></span></b></td>
								</tr>
								<tr>
									<td class="myheadbg">CHiPS Share Admin Charges 5% (B)</td>
									<td style="text-align:right;"><span id="vendor_admin_charge"></span></td>
								</tr>
								<tr>
									<td class="myheadbg"><b>Total Approval Amount as per Note Sheet (C = A - B)</b></td>
									<td style="text-align:right;"><b><span id="vendor_approval_amount"></span></b></td>
								</tr>
								<tr>
									<td class="myheadbg">Basic Amount Total Bill (D)</td>
									<td style="text-align:right;"><span id="vendor_basic_amount"></span></td>
								</tr>
								<tr>
									<td class="myheadbg">TDS <span id="vendor_tds_rate_display">0.00</span>% (E = D * TDS %)</td>
									<td style="text-align:right;"><span id="vendor_tds_amount"></span></td>
								</tr>
								<tr>
									<td class="myheadbg">GST-TDS <span id="vendor_gst_tds_rate_display">0.00</span>% (F = D * GST-TDS %)</td>
									<td style="text-align:right;"><span id="vendor_gst_tds_amount"></span></td>
								</tr>
								<tr>
									<td class="myheadbg"><b>Net Payment To Vendor (G = C - E - F)</b></td>
									<td style="text-align:right;"><b><span id="net_payment_to_vendor"></span></b></td>
								</tr>
							</tbody>
						</table>
						@elseif($caseType == 'AWD')
						<table class="table table-bordered mt-10">
							<tbody>
								<tr>
									<td class="myheadbg width-350"><b>Invoice Gross Value (A)</b></td>
									<td style="text-align:right;"><b><span id="vendor_total_received"></span></b></td>
								</tr>
								<tr>
									<td class="myheadbg">Taxable Amount (B)</td>
									<td style="text-align:right;"><span id="vendor_basic_amount"></span></td>
								</tr>
								<tr>
									<td class="myheadbg">TDS <span id="vendor_tds_rate_display">0.00</span>% (C = B * TDS %)</td>
									<td style="text-align:right;"><span id="vendor_tds_amount"></span></td>
								</tr>
								<tr>
									<td class="myheadbg">GST-TDS <span id="vendor_gst_tds_rate_display">0.00</span>% (D = B * GST-TDS %)</td>
									<td style="text-align:right;"><span id="vendor_gst_tds_amount"></span></td>
								</tr>
								<tr>
									<td class="myheadbg"><b>Net Payable To Vendor (E = A - C - D)</b></td>
									<td style="text-align:right;"><b><span id="net_payment_to_vendor"></span></b></td>
								</tr>
							</tbody>
						</table>
						@endif

					</div>

				</div>


				{{-- =========================================================
					PAYMENT DETAILS
				========================================================= --}}

				<div class="card mt-10">
					<div class="card-header">
						<h4 class="font-16 font-bold">Payment Details</h4>
					</div>

					<div class="card-body mt-10">
						<div class="row">
							<div class="col-md-2 mb-3">
								<label>Payment Date</label>
								<input type="date" name="payment_date" class="form-control" value="{{ date('Y-m-d') }}" required>
							</div>

							<div class="col-md-2 mb-3">
								<label>Payment Mode</label>
								<select name="payment_mode_id" class="form-control" required>
									<option value="">Payment Mode</option>
									@foreach($paymentModes as $mode)
										<option value="{{ $mode->payment_mode_id }}">{{ $mode->payment_mode }}</option>
									@endforeach
								</select>
							</div>

							<div class="col-md-3 mb-3">
								<label>Voucher Number</label>
								<input type="text" name="voucher_number" class="form-control" maxlength="50" placeholder="Voucher Number" autocomplete="off">
							</div>

							<div class="col-md-3 mb-3">
								<label>Transaction / UTR No.</label>
								<input type="text" name="transaction_no" class="form-control" maxlength="100" placeholder="Transaction number" autocomplete="off">
							</div>
							<div class="col-md-2 mb-3">
								<label>Bank Account</label>
								<select name="bank_account_id" class="form-control" required>
									<option value="">Select Bank Account</option>
									@foreach($bankAccounts as $bank)
										<option value="{{ $bank->bank_account_id }}">{{ $bank->bank_name }}@if(!empty($bank->account_name)) - {{ $bank->account_name }}@endif</option>
									@endforeach
								</select>
							</div>
						</div>

						<div class="row mt-10">

							<div class="col-md-12 mb-3">
								<label>Remarks</label>
								<textarea name="remarks" class="form-control" rows="3" maxlength="1000" placeholder="Payment remark"></textarea>
							</div>

						</div>
						<div class="row mt-10">
							<div class="col-md-8 mb-3"></div>
							<div class="col-md-4 mb-3">
								<label>Attachment</label>
								<input type="file" name="attachment" id="demandnote_attachment" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
								<small class="text-muted">PDF, JPG, JPEG or PNG. Maximum 5 MB.</small>
							</div>
						</div>
					</div>
				</div>



				<div class="card mt-10">

					<div class="card-body">

						<div class="row">

							<div class="col-md-12 text-right">

								<a href="{{ route('finance.vendorpayment.index') }}" class="btn btn-info gridbtn width-100">
									<i class="fa fa-remove"></i> Cancel
								</a>

						<button type="button" id="btnSubmitPayment" class="btn btn-info gridbtn width-150">
							<i class="fa fa-money"></i> Make Payment
						</button>

							</div>

						</div>

					</div>

				</div>

			</form>

		</div>
	</div>
</div>


<script src="{{ asset('panel/assets/js/jquery-2.1.4.min.js') }}"></script>
<script src="{{ asset('panel/assets/js/ace-elements.min.js') }}"></script>
<script src="{{ asset('panel/assets/js/ace.min.js') }}"></script>
<script src="{{ asset('panel/assets/tinymce/tinymce.min.js') }}"></script>


<script>
$('#demandnote_attachment').ace_file_input({
	no_file: 'No File ...',
	btn_choose: 'Attachment (Optional)',
	btn_change: 'Change',
	btn_name: 'btnname',
	thumbnail: false //| true | large
})

let departmentGstRate =
	{{ (float)($departmentGstRate ?? 0) }};

let vendorFundingTdsRate =
	{{ (float)($vendorFundingTdsRate ?? 0) }};

let vendorFundingGstTdsRate =
	{{ (float)($vendorFundingGstTdsRate ?? 0) }};

let serviceChargeTdsRate =
	{{ (float)($serviceChargeTdsRate ?? 0) }};

let serviceChargeGstTdsRate =
	{{ (float)($serviceChargeGstTdsRate ?? 0) }};

let invoiceGrossAmount =
	{{ (float)$invoice->net_invoice_value }};

let invoiceTaxableAmount =
	{{ (float)$invoice->taxable_amount }};

let invoiceBalance =
	{{ (float)$invoiceBalance }};

let adminChargePercent = 5;
let vendorPaymentTotalReceived = 0;
let vendorPaymentAdminCharge = 0;
let vendorPaymentBasicAmount = 0;

function numberFormat(value)
{
	value = parseFloat(value) || 0;

	return value.toLocaleString(
		'en-IN',
		{
			minimumFractionDigits: 2,
			maximumFractionDigits: 2
		}
	);
}

function getTaxableFromGross(grossAmount,gstRate)
{
	grossAmount =
		parseFloat(grossAmount) || 0;

	gstRate =
		parseFloat(gstRate) || 0;

	if(grossAmount <= 0)
	{
		return 0;
	}

	if(gstRate <= 0)
	{
		return grossAmount;
	}

	return grossAmount / (1 + (gstRate / 100));
}


function setAmount(id,value)
{
	let element =
		document.getElementById(id);

	if(!element)
	{
		return;
	}

	value =
		parseFloat(value) || 0;

	element.innerText =
		numberFormat(
			Math.round(value * 100) / 100
		);
}


function calculateFundAvailability()
{
	let grossInvoice = parseFloat(invoiceGrossAmount) || 0;
	let gstRate = parseFloat(departmentGstRate) || 0;
	let vendorFundingTdsRateValue = parseFloat(vendorFundingTdsRate) || 0;
	let vendorFundingGstTdsRateValue = parseFloat(vendorFundingGstTdsRate) || 0;
	let serviceChargeTdsRateValue = parseFloat(serviceChargeTdsRate) || 0;
	let serviceChargeGstTdsRateValue = parseFloat(serviceChargeGstTdsRate) || 0;
	let invoiceBasic = parseFloat(invoiceTaxableAmount) || 0;
	let invoiceGst = grossInvoice - invoiceBasic;
	invoiceGst = Math.round(invoiceGst * 100) / 100;

	let adminBasic = invoiceBasic * adminChargePercent / 100;
	adminBasic = Math.round(adminBasic * 100) / 100;

	let adminGst = adminBasic * gstRate / 100;
	adminGst = Math.round(adminGst * 100) / 100;

	let totalBasic = invoiceBasic + adminBasic;
	totalBasic = Math.round(totalBasic * 100) / 100;

	let totalGst = invoiceGst + adminGst;
	totalGst = Math.round(totalGst * 100) / 100;

	let totalInvoice = totalBasic + totalGst;
	totalInvoice = Math.round(totalInvoice * 100) / 100;

	// VENDOR_FUNDING deductions apply only to Vendor Invoice.
	let invoiceTds = invoiceBasic * vendorFundingTdsRateValue / 100;
	invoiceTds = Math.round(invoiceTds * 100) / 100;

	let invoiceGstTds = invoiceBasic * vendorFundingGstTdsRateValue / 100;
	invoiceGstTds = Math.round(invoiceGstTds * 100) / 100;

	let invoiceNet = grossInvoice - invoiceTds - invoiceGstTds;
	invoiceNet = Math.round(invoiceNet * 100) / 100;

	// SERVICE_CHARGE deductions apply only to CHiPS Administrative Charges.
	let adminTds = adminBasic * serviceChargeTdsRateValue / 100;
	adminTds = Math.round(adminTds * 100) / 100;

	let adminGstTds = adminBasic * serviceChargeGstTdsRateValue / 100;
	adminGstTds = Math.round(adminGstTds * 100) / 100;

	let adminGross = adminBasic + adminGst;
	adminGross = Math.round(adminGross * 100) / 100;

	let adminNet = adminGross - adminTds - adminGstTds;
	adminNet = Math.round(adminNet * 100) / 100;

	let availableFund = invoiceNet + adminNet;
	availableFund = Math.round(availableFund * 100) / 100;

	vendorPaymentTotalReceived = availableFund;
	vendorPaymentAdminCharge = adminNet;
	vendorPaymentBasicAmount = invoiceBasic;

	let gstRateElement = document.getElementById('department_gst_rate_display');

	if(gstRateElement)
	{
		gstRateElement.innerText = gstRate.toFixed(2);
	}

	let tdsRateElement = document.getElementById('department_tds_rate_display');

	if(tdsRateElement)
	{
		tdsRateElement.innerText = vendorFundingTdsRateValue.toFixed(2) + '% / ' + serviceChargeTdsRateValue.toFixed(2) + '%';
	}

	let gstTdsRateElement = document.getElementById('department_gst_tds_rate_display');

	if(gstTdsRateElement)
	{
		gstTdsRateElement.innerText = vendorFundingGstTdsRateValue.toFixed(2) + '% / ' + serviceChargeGstTdsRateValue.toFixed(2) + '%';
	}

	setAmount('fund_invoice_basic', invoiceBasic);
	setAmount('fund_admin_basic', adminBasic);
	setAmount('fund_total_basic', totalBasic);
	setAmount('fund_invoice_gst', invoiceGst);
	setAmount('fund_admin_gst', adminGst);
	setAmount('fund_total_gst', totalGst);
	setAmount('fund_invoice_total', grossInvoice);
	setAmount('fund_admin_total', adminGross);
	setAmount('fund_total_invoice', totalInvoice);
	setAmount('fund_invoice_tds', invoiceTds);
	setAmount('fund_admin_tds', adminTds);
	setAmount('fund_total_tds', invoiceTds + adminTds);
	setAmount('fund_invoice_gst_tds', invoiceGstTds);
	setAmount('fund_admin_gst_tds', adminGstTds);
	setAmount('fund_total_gst_tds', invoiceGstTds + adminGstTds);
	setAmount('fund_invoice_net', Math.round(invoiceNet));
	setAmount('fund_admin_net', Math.round(adminNet));
	setAmount('department_available_fund', Math.round(availableFund));

	let maxPaymentElement = document.getElementById('max_payment_amount');

	if(maxPaymentElement)
	{
		maxPaymentElement.innerText = numberFormat(invoiceBalance);
	}

	return availableFund;
}


function calculateVendorPayment()
{
	let grossPayment = parseFloat($('#gross_amount').val()) || 0;
	let currentInvoiceGrossAmount = parseFloat(invoiceGrossAmount) || 0;
	let currentInvoiceBasicAmount = parseFloat(invoiceTaxableAmount) || 0;

	if(grossPayment < 0)
	{
		grossPayment = 0;
		$('#gross_amount').val('0.00');
	}

	if(grossPayment > invoiceBalance)
	{
		grossPayment = invoiceBalance;
		$('#gross_amount').val(grossPayment.toFixed(2));
	}

	let tdsRate = parseFloat({{ (float)$defaultTdsRate }}) || 0;
	let tdsAmount = 0;
	let gstTdsRate = parseFloat({{ (float)$defaultGstTdsRate }}) || 0;
	let gstTdsAmount = 0;
	let approvalAmount = 0;
	let basicAmount = 0;
	let totalReceived = 0;
	let adminCharge = 0;
	let netPayment = 0;

	if('{{ $caseType }}' == 'AWD')
	{
		let paymentRatio = 0;

		if(currentInvoiceGrossAmount > 0)
		{
			paymentRatio = grossPayment / currentInvoiceGrossAmount;
		}

		approvalAmount = grossPayment;
		basicAmount = currentInvoiceBasicAmount * paymentRatio;
		basicAmount = parseFloat(basicAmount.toFixed(2));

		tdsAmount = basicAmount * tdsRate / 100;
		tdsAmount = parseFloat(tdsAmount.toFixed(2));

		gstTdsAmount = basicAmount * gstTdsRate / 100;
		gstTdsAmount = parseFloat(gstTdsAmount.toFixed(2));

		netPayment = approvalAmount - tdsAmount - gstTdsAmount;
		netPayment = parseFloat(netPayment.toFixed(2));

		if(netPayment < 0)
		{
			netPayment = 0;
		}

		totalReceived = approvalAmount;
	}
	else
	{
		let fundTotalReceived = vendorPaymentTotalReceived;
		let fundAdminCharge = vendorPaymentAdminCharge;
		let fundApprovalAmount = fundTotalReceived - fundAdminCharge;
		let paymentRatio = 0;

		if(currentInvoiceGrossAmount > 0)
		{
			paymentRatio = grossPayment / currentInvoiceGrossAmount;
		}

		totalReceived = fundTotalReceived * paymentRatio;
		totalReceived = parseFloat(totalReceived.toFixed(2));

		adminCharge = fundAdminCharge * paymentRatio;
		adminCharge = parseFloat(adminCharge.toFixed(2));

		approvalAmount = totalReceived - adminCharge;
		approvalAmount = parseFloat(approvalAmount.toFixed(2));

		basicAmount = currentInvoiceBasicAmount * paymentRatio;
		basicAmount = parseFloat(basicAmount.toFixed(2));

		tdsAmount = basicAmount * tdsRate / 100;
		tdsAmount = parseFloat(tdsAmount.toFixed(2));

		gstTdsAmount = basicAmount * gstTdsRate / 100;
		gstTdsAmount = parseFloat(gstTdsAmount.toFixed(2));

		netPayment = approvalAmount - tdsAmount - gstTdsAmount;
		netPayment = parseFloat(netPayment.toFixed(2));

		if(netPayment < 0)
		{
			netPayment = 0;
		}
	}

	$('#vendor_total_received').text(formatIndianNumber(Math.round(totalReceived).toFixed(2)));
	$('#vendor_admin_charge').text(formatIndianNumber(Math.round(adminCharge).toFixed(2)));
	$('#vendor_approval_amount').text(formatIndianNumber(Math.round(approvalAmount).toFixed(2)));
	$('#vendor_basic_amount').text(formatIndianNumber(Math.round(basicAmount).toFixed(2)));
	$('#vendor_tds_rate_display').text(tdsRate.toFixed(2));
	$('#vendor_tds_amount').text(formatIndianNumber(Math.round(tdsAmount).toFixed(2)));
	$('#vendor_gst_tds_rate_display').text(gstTdsRate.toFixed(2));
	$('#vendor_gst_tds_amount').text(formatIndianNumber(Math.round(gstTdsAmount).toFixed(2)));
	$('#net_payment_to_vendor').text(formatIndianNumber(Math.round(netPayment).toFixed(2)));
}

$(document).on(
	'change',
	'#tds_tax_id,#gst_tds_tax_id',
	function()
	{
		calculateVendorPayment();
	}
);



$(document).on(
	'input change',
	'#gross_amount',
	function()
	{
		let amount =
			parseFloat($(this).val()) || 0;

		if(amount < 0)
		{
			amount = 0;

			$(this).val(
				'0.00'
			);
		}

		if(amount > invoiceBalance)
		{
			amount = invoiceBalance;

			$(this).val(
				amount.toFixed(2)
			);
		}
	}
);


$(document).ready(function()
{
	if('{{ $caseType }}' == 'CSF')
	{
		calculateFundAvailability();
	}

	calculateVendorPayment();
});

function getNumber(value)
{
	value = String(value).replace(/,/g,'');

	let number = parseFloat(value);

	return isNaN(number) ? 0 : number;
}

$(document).on('click','#btnSubmitPayment',
	function()
	{
		let form = $('#frmVendorPayment')[0];
		
		if(!form.checkValidity())
		{
			form.reportValidity();
			return false;
		}


		let paymentAmount = getNumber($('#gross_amount').val());


		let invoiceBalance = {{ (float)$invoiceBalance }};


		if(paymentAmount <= 0)
		{
			bootbox.alert('Payment Amount should be greater than zero.');
			return false;
		}


		if(paymentAmount > invoiceBalance)
		{
			bootbox.alert(
				'Payment amount cannot be greater than the Invoice Balance of ₹' +
				formatIndianNumber(
					invoiceBalance.toFixed(2)
				)
			);

			$('#gross_amount').val('');
			calculateVendorPayment();
			return false;
		}


		bootbox.confirm(
			'Are you sure you want to make this Vendor Payment?',
			function(result)
			{
				if(!result)
				{
					return;
				}


				let formData = new FormData(form);


				$('#btnSubmitPayment').prop('disabled',true);


				$.ajax({
					url: "{{ route('finance.vendorpayment.store') }}",
					type: 'POST',
					data: formData,
					processData: false,
					contentType: false,
					success: function(response)
					{
						if(response.status == 1)
						{
							bootbox.alert(
								response.message,
								function()
								{
									window.location.href =response.redirect;
								}
							);
						}
						else
						{
							bootbox.alert(response.message);

							$('#btnSubmitPayment').prop('disabled',false);
						}
					},
					error: function(xhr)
					{
						let message = 'Something went wrong.';
						
						if(xhr.responseJSON && xhr.responseJSON.message)
						{
							message = xhr.responseJSON.message;
						}

						if(xhr.responseJSON && xhr.responseJSON.errors)
						{
							let errors = xhr.responseJSON.errors;

							message = '';
							
							$.each(
								errors,
								function(field,messages)
								{
									message +=
										messages.join('<br>') +
										'<br>';
								}
							);
						}
						
						bootbox.alert(message);
						
						$('#btnSubmitPayment').prop('disabled',false);
					}
				});
			}
		);
	}
);

</script>
<script src="{{ asset('panel/assets/js/datatablePaginationWithFilter.js') }}"></script>
@endsection