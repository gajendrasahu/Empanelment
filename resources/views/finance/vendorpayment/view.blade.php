@extends('admin.admin_master')
@section('admin')

<div class="main-content">
	<div class="main-content-inner">
		<div class="page-content">

			<div class="ace-settings-container" id="ace-settings-container"></div>


			{{-- =========================================================
				VENDOR PAYMENT DETAILS
			========================================================= --}}

			<div class="card">

				<div class="card-header">
					<strong class="font-16">Vendor Payment Details</strong>
				</div>

				<div class="card-body mt-10">

					<div class="row">

						<div class="col-md-3">
							<label>Payment Date</label>

							<div class="bg-light">
								{{ $payment->payment_date ? date('d-m-Y',strtotime($payment->payment_date)) : '-' }}
							</div>
						</div>

						<div class="col-md-3">
							<label>Payment Voucher Number</label>

							<div class="bg-light">
								{{ $payment->voucher_number ?: '-' }}
							</div>
						</div>

						<div class="col-md-3">
							<label>Invoice Number</label>

							<div class="bg-light">
								{{ $payment->invoice_number ?: '-' }}
							</div>
						</div>

						<div class="col-md-3">
							<label>Invoice Date</label>

							<div class="bg-light">
								{{ $payment->invoice_date ? date('d-m-Y',strtotime($payment->invoice_date)) : '-' }}
							</div>
						</div>

					</div>

					<div class="row mt-20">

						<div class="col-md-6">
							<label>EOI Number</label>

							<div class="bg-light">
								{{ $payment->eoinumber ?: '-' }}
							</div>
						</div>

						<div class="col-md-6">
							<label>Work Order Number</label>

							<div class="bg-light">
								{{ $payment->ordernumber ?: '-' }}
							</div>
						</div>

					</div>

					<div class="row mt-20">

						<div class="col-md-6">
							<label>Vendor</label>

							<div class="bg-light">
								{{ $payment->companyname ?: '-' }}
							</div>
						</div>

						<div class="col-md-6">
							<label>Vendor GSTIN</label>

							<div class="bg-light">
								{{ $payment->gstnumber ?: '-' }}
							</div>
						</div>

					</div>

				</div>

			</div>


			{{-- =========================================================
				INVOICE DETAILS
			========================================================= --}}

			<div class="card mt-10">

				<div class="card-header">
					<strong class="font-16">Invoice Details</strong>
				</div>

				<div class="card-body mt-10">

					<table class="table table-bordered">

						<tbody>

							<tr>

								<td class="myheadbg" width="50%">
									<b>Invoice Number</b>
								</td>

								<td class="text-right">
									{{ $payment->invoice_number ?: '-' }}
								</td>

							</tr>


							<tr>

								<td class="myheadbg">
									<b>Invoice Date</b>
								</td>

								<td class="text-right">
									{{ $payment->invoice_date ? date('d-m-Y',strtotime($payment->invoice_date)) : '-' }}
								</td>

							</tr>


							<tr>

								<td class="myheadbg">
									<b>Original Invoice Taxable Amount</b>
								</td>

								<td class="text-right">
									₹ <span class="format-indian" data-value="{{ number_format((float)($payment->invoice_taxable_amount ?? 0),2,'.','') }}"></span>
								</td>

							</tr>


							<tr>

								<td class="myheadbg">
									<b>Original Gross Invoice Value</b>
								</td>

								<td class="text-right">
									₹ <span class="format-indian" data-value="{{ number_format((float)($payment->net_invoice_value ?? 0),2,'.','') }}"></span>
								</td>

							</tr>


						</tbody>

					</table>

				</div>

			</div>


			{{-- =========================================================
				VENDOR PAYMENT CALCULATION
			========================================================= --}}

			<div class="card mt-10">

				<div class="card-header">
					<strong class="font-16">Vendor Payment Calculation</strong>
				</div>

				<div class="card-body mt-10">

					<table class="table table-bordered">

						<tbody>

							<tr>

								<td class="myheadbg" width="50%">
									<b>Gross Payment</b>
								</td>

								<td class="text-right">
									<b>
										₹ <span class="format-indian" data-value="{{ number_format((float)($payment->gross_amount ?? 0),2,'.','') }}"></span>
									</b>
								</td>

							</tr>


							@if($caseType == 'CSF')

							<tr>

								<td class="myheadbg">
									Available Fund
								</td>

								<td class="text-right">
									₹ <span class="format-indian" data-value="{{ number_format((float)($payment->available_fund ?? 0),2,'.','') }}"></span>
								</td>

							</tr>

							@endif


							<tr>

								<td class="myheadbg">
									<b>Taxable / Basic Amount</b>
								</td>

								<td class="text-right">
									<b>
										₹ <span class="format-indian" data-value="{{ number_format((float)($payment->basic_amount ?? 0),2,'.','') }}"></span>
									</b>
								</td>

							</tr>


							@if($caseType == 'CSF')

							<tr>

								<td class="myheadbg">
									CHiPS Share Admin Charges
									{{ number_format((float)$payment->admin_charge_percent,2) }}%
									(As applicable on Taxable / Basic Amount)
								</td>

								<td class="text-right">
									₹ <span class="format-indian" data-value="{{ number_format((float)($payment->admin_charge_amount ?? 0),2,'.','') }}"></span>
								</td>

							</tr>

							@endif


							@if($caseType == 'CSF')

							<tr>

								<td class="myheadbg">
									<b>Total Approval Amount as per Note Sheet</b>
								</td>

								<td class="text-right">
									<b>
										₹ <span class="format-indian" data-value="{{ number_format((float)($payment->approval_amount ?? 0),2,'.','') }}"></span>
									</b>
								</td>

							</tr>

							@endif

							<tr>

								<td class="myheadbg">
									TDS
									{{ number_format((float)$payment->tds_rate,2) }}%
									(As applicable on Taxable / Basic Amount)
								</td>

								<td class="text-right">
									₹ <span class="format-indian" data-value="{{ number_format((float)($payment->tds_amount ?? 0),2,'.','') }}"></span>
								</td>

							</tr>


							<tr>

								<td class="myheadbg">
									GST-TDS
									{{ number_format((float)$payment->gst_tds_rate,2) }}%
									(As applicable on Taxable / Basic Amount)
								</td>

								<td class="text-right">
									₹ <span class="format-indian" data-value="{{ number_format((float)($payment->gst_tds_amount ?? 0),2,'.','') }}"></span>
								</td>

							</tr>


							<tr>

								<td class="myheadbg">
									<b>Net Payment To Vendor</b>
								</td>

								<td class="text-right">

									<b>
										₹ <span class="format-indian" data-value="{{ number_format((float)($payment->net_paid_amount ?? 0),2,'.','') }}"></span>
									</b>

								</td>

							</tr>


						</tbody>

					</table>

				</div>

			</div>


			{{-- =========================================================
				PAYMENT / BANK DETAILS
			========================================================= --}}

			<div class="card mt-10">

				<div class="card-header">
					<strong class="font-16">Payment / Bank Details</strong>
				</div>

				<div class="card-body mt-10">

					<div class="row">

						<div class="col-md-3">
							<label>Payment Mode</label>

							<div class="bg-light">
								{{ $payment->payment_mode ?: '-' }}
							</div>
						</div>

						<div class="col-md-3">
							<label>Bank</label>

							<div class="bg-light">
								{{ $payment->bank_name ?: '-' }}
							</div>
						</div>

						<div class="col-md-3">
							<label>Transaction / UTR No.</label>

							<div class="bg-light">
								{{ $payment->transaction_no ?: '-' }}
							</div>
						</div>

						<div class="col-md-3">
							<label>Payment Voucher Number</label>

							<div class="bg-light">
								{{ $payment->voucher_number ?: '-' }}
							</div>
						</div>

					</div>

				</div>

			</div>


			{{-- =========================================================
				PAYMENT REMARKS
			========================================================= --}}

			<div class="card mt-10">

				<div class="card-header">
					<strong class="font-16">Payment Remarks</strong>
				</div>

				<div class="card-body mt-10">

					<div class="row">

						<div class="col-md-12">

							<label>Remarks</label>

							<div class="bg-light" style="height:auto;min-height:60px;">
								{{ $payment->remarks ?: '-' }}
							</div>

						</div>

					</div>

				</div>

			</div>


			{{-- =========================================================
				LEDGER DETAILS
			========================================================= --}}

			<div class="card mt-10">

				<div class="card-header">
					<strong class="font-16">Ledger Details</strong>
				</div>

				<div class="card-body mt-10">

					<table class="table table-bordered">

						<thead>

							<tr class="myheadbg">

								<td>S.No.</td>
								<td class="no_wrap">Date</td>
								<td class="no_wrap">Ledger Type</td>
								<td class="no_wrap" style="text-align:right;">Debit</td>
								<td class="no_wrap" style="text-align:right;">Credit</td>
								<td>Narration</td>

							</tr>

						</thead>


						<tbody>

							@forelse($ledger as $item)

							<tr>

								<td class="no_wrap text-center">
									{{ $loop->iteration }}
								</td>

								<td class="no_wrap">
									{{ $item->transaction_date ? date('d-m-Y',strtotime($item->transaction_date)) : '-' }}
								</td>

								<td class="no_wrap">
									{{ $item->ledger_type ?: '-' }}
								</td>

								<td class="no_wrap text-right">
									₹ <span class="format-indian" data-value="{{ number_format((float)($item->dr_amount ?? 0),2,'.','') }}"></span>
								</td>

								<td class="no_wrap text-right">
									₹ <span class="format-indian" data-value="{{ number_format((float)($item->cr_amount ?? 0),2,'.','') }}"></span>
								</td>

								<td>
									{{ $item->narration ?: '-' }}
								</td>

							</tr>

							@empty

							<tr>

								<td colspan="6" style="text-align:center;">
									<br>
									<i class="fa fa-warning blue nodata"></i><br>
									No ledger transaction found.
								</td>

							</tr>

							@endforelse

						</tbody>

					</table>

				</div>

			</div>


			{{-- =========================================================
				ATTACHMENT
			========================================================= --}}

			<div class="card mt-10">

				<div class="card-header">
					<strong class="font-16">Payment Attachment</strong>
				</div>

				<div class="card-body mt-10">

					@if($payment->attachment)

						<a href="{{ route('view.uploadedfile',Crypt::encrypt($payment->attachment)) }}" target="_blank" class="btn btn-info br-5">
							<i class="fa fa-file"></i> View Payment Attachment
						</a>

					@else

						<span class="text-muted">
							No payment attachment.
						</span>

					@endif

				</div>

			</div>


			{{-- =========================================================
				BACK
			========================================================= --}}

			<div class="card mt-10">

				<div class="card-body text-right">

					<a href="{{ route('finance.vendorpayment.index') }}" class="btn btn-info width-100">
						<i class="fa fa-arrow-left"></i> Back
					</a>

				</div>

			</div>

		</div>
	</div>
</div>


<script src="{{ asset('panel/assets/js/jquery-2.1.4.min.js') }}"></script>

<script>

$(document).ready(function()
{
	document.querySelectorAll('.format-indian').forEach(function(el)
	{
		el.style.fontWeight = 'bold';
		el.innerText = formatIndianNumber(el.getAttribute('data-value'));
	});
});

</script>

<script src="{{ asset('panel/assets/js/datatablePaginationWithFilter.js') }}"></script>

@endsection