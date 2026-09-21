@extends('admin.admin_master')
@section('admin')

@php
$t = 0;
@endphp

<div class="main-content">
	<div class="main-content-inner">
		<div class="page-content">

			<div class="ace-settings-container" id="ace-settings-container"></div>

			<form id="frmVendorInvoice" enctype="multipart/form-data">
				@csrf

				<!-- Vendor Invoice Details -->
				<div class="card">

					<div class="card-header">
						<strong class="font-16">Vendor Invoice Details {{$caseType}}</strong>
					</div>

					<div class="card-body mt-10">

						<div class="row">

							<div class="col-md-3">
								<label>Invoice No.</label>
								<div class="bg-light">
									{{ $invoice->invoice_number ?: '-' }}
								</div>
							</div>

							<div class="col-md-3">
								<label>Invoice Date</label>
								<div class="bg-light">
									{{ $invoice->invoice_date ? date('d-m-Y',strtotime($invoice->invoice_date)) : '-' }}
								</div>
							</div>

							<div class="col-md-3">
								<label>Invoice Status</label>
								<div class="bg-light">
									{{ $invoice->invoice_status ?: '-' }}
								</div>
							</div>

							<div class="col-md-3">
								<label>Finance Status</label>
								<div class="bg-light">

									@if($invoice->finance_status == 'Pending')

										<span class="label label-warning">
											Pending
										</span>

									@elseif($invoice->finance_status == 'Verified')

										<span class="label label-success">
											Verified
										</span>

									@elseif($invoice->finance_status == 'Rejected')

										<span class="label label-danger">
											Rejected
										</span>

									@else

										{{ $invoice->finance_status ?: '-' }}

									@endif

								</div>
							</div>

						</div>


						<div class="row mt-20">

							<div class="col-md-3">
								<label>Payment Status</label>
								<div class="bg-light">
									{{ $invoice->payment_status ?: '-' }}
								</div>
							</div>

							<div class="col-md-3">
								<label>Voucher Number</label>
								<div class="bg-light">
									{{ $invoice->voucher_number ?: '-' }}
								</div>
							</div>

							<div class="col-md-3">
								<label>Voucher Date</label>
								<div class="bg-light">
									{{ $invoice->voucher_date ? date('d-m-Y',strtotime($invoice->voucher_date)) : '-' }}
								</div>
							</div>

						</div>

					</div>

				</div>


				<!-- Reference Details -->
				<div class="card mt-10">

					<div class="card-header">
						<strong class="font-16">Reference Details</strong>
					</div>

					<div class="card-body mt-10">

						<div class="row">

							<div class="col-md-3">
								<label>Vendor</label>
								<div class="bg-light">
									{{ $invoice->companyname ?: '-' }}
								</div>
							</div>

							<div class="col-md-3">
								<label>Vendor GSTIN</label>
								<div class="bg-light">
									{{ $invoice->vendor_gstin ?: '-' }}
								</div>
							</div>

							<div class="col-md-3">
								<label>Vendor PAN</label>
								<div class="bg-light">
									{{ $invoice->vendor_pan ?: '-' }}
								</div>
							</div>

							<div class="col-md-3">
								<label>Contact Person</label>
								<div class="bg-light">
									{{ $invoice->contactperson ?: '-' }}
								</div>
							</div>

						</div>


						<div class="row mt-20">

							<div class="col-md-6">
								<label>Work Order</label>
								<div class="bg-light">
									{{ $invoice->ordernumber ?: '-' }}
								</div>
							</div>

							<div class="col-md-6">
								<label>EoI / Request</label>
								<div class="bg-light">
									{{ $invoice->eoinumber ?: '-' }}
								</div>
							</div>

						</div>

					</div>

				</div>


				<!-- MPR & Resource Details -->
				<div class="card mt-10">

					<div class="card-header" style="position:relative;">

						<strong class="font-16">
							MPR & Resource Details
						</strong>

						<span class="bg-primary font-bold br-5" style="position:absolute; right:5px; padding:2px 10px;">
							P - Present | A - Absent | L - Leave | H - Holiday | E.D. - Eligible Days | P.D.C. - Per Day Cost | A.S. - Approved Salary
						</span>

					</div>


					<div class="card-body mt-10">

						@forelse($mprs as $mpr)

							<div class="card mb-3">

								<div class="card-header" style="position:relative;">

									<strong class="bg-primary padding-5" style="width:100%!important;">
										MPR Number : {{ $mpr->mpr_number ?: 'MPR-'.$mpr->mpr_id }} |
										Month : {{ $mpr->mpr_month ?: '-' }}/{{ $mpr->mpr_year ?: '-' }} |
										Submission Date : {{ $mpr->submission_date ? date('d-m-Y',strtotime($mpr->submission_date)) : '-' }} |
										Approved Amount : {{ number_format($mpr->mpr_approved_value,2) }}
									</strong>

								</div>


								<div class="card-body">
									<div class="row mt-5">
										<div class="col-md-12">
											<table class="table table-bordered">
													<tr class="myheadbg font-bold">
														<td width="5%" class="text-center">#</td>
														<td>Name</td>
														<td class="text-center">E.D.</td>
														<td class="text-center">P</td>
														<td class="text-center">A</td>
														<td class="text-center">L</td>
														<td class="text-center">H</td>
														<td class="text-right">P.D.C.</td>
														<td class="text-right">A.S.</td>
														<td class="text-center">MPR</td>
														<td class="text-center">Attendance</td>
														<td class="text-center">Other</td>
													</tr>
												<tbody>
													@php
													$total_approved = 0;
													@endphp

													@forelse($mpr->resources as $resource)

														<tr>

															<td class="text-center">{{ $loop->iteration }}</td>
															<td>{{ $resource->name }}</td>
															<td class="text-center">{{ $resource->count_days }}</td>
															<td class="text-center">{{ $resource->total_present }}</td>
															<td class="text-center">{{ $resource->total_absent }}</td>
															<td class="text-center">{{ $resource->total_leave }}</td>
															<td class="text-center">{{ $resource->total_holiday }}</td>
															<td class="text-right format-indian" data-value="{{ $resource->per_day_cost }}"></td>
															<td class="text-right format-indian" data-value="{{ $resource->approved_salary }}"></td>
															<td class="text-center">
																@if($resource->mpr_file)
																	<a href="{{ route('view.uploadedfile',Crypt::encrypt($resource->mpr_file)) }}" target="_blank">
																		<i class="fa fa-file-pdf-o"></i>
																	</a>
																@endif
															</td>
															<td class="text-center">
																@if($resource->attendance_file)
																	<a href="{{ route('view.uploadedfile',Crypt::encrypt($resource->attendance_file)) }}" target="_blank">
																		<i class="fa fa-file-pdf-o"></i>
																	</a>
																@endif
															</td>
															<td class="text-center">
																@if($resource->supporting_file)
																	<a href="{{ route('view.uploadedfile',Crypt::encrypt($resource->supporting_file)) }}" target="_blank">
																		<i class="fa fa-file-pdf-o"></i>
																	</a>
																@endif
															</td>
														</tr>
														@php
														$total_approved = $total_approved + $resource->approved_salary;
														@endphp

													@empty
														<tr>
															<td colspan="12" class="text-center">
																No resource records found.
															</td>
														</tr>
													@endforelse
													<tr>

														<td colspan="4" class="text-left bg-primary font-bold">

															Group Files <i class="fa fa-angle-double-right"></i>

															@if($mpr->signed_mpr)

																<a href="{{ route('view.uploadedfile',Crypt::encrypt($mpr->signed_mpr)) }}" class="color-white no-outline" target="_blank">
																	MPR
																</a>

															@endif

															@if($mpr->signed_attendance)

																| <a href="{{ route('view.uploadedfile',Crypt::encrypt($mpr->signed_attendance)) }}" class="color-white no-outline" target="_blank">
																	Attendance
																</a>

															@endif

															@if($mpr->signed_supporting)

																| <a href="{{ route('view.uploadedfile',Crypt::encrypt($mpr->signed_supporting)) }}" class="color-white no-outline" target="_blank">
																	Other
																</a>

															@endif

														</td>

														<td colspan="4" class="text-right">
															<b>Total Approved Salary</b>
														</td>

														<td class="text-right">
															<b class="format-indian" data-value="{{ number_format($total_approved,'2','.','') }}"></b>
														</td>

														<td class="text-center padding-3 v-middle" colspan="4">

															@if($mpr->finance_verified)

																<button type="button" class="btn btn-success br-5 no-hover btnVerifyMpr width-full" disabled>
																	<i class="fa fa-check-circle"></i> MPR & Attendance Verified
																</button>

															@else

																<button type="button" class="btn btn-primary br-5 no-hover btnVerifyMpr width-full" data-mpr-id="{{ $mpr->mpr_id }}">
																	<i class="fa fa-check"></i> Verify MPR & Attendance
																</button>

															@endif

														</td>

													</tr>

												</tbody>

											</table>

										</div>

									</div>

								</div>

							</div>

						@empty

							<div class="text-center text-muted">
								No MPR records found for this invoice.
							</div>

						@endforelse

					</div>

				</div>


				<!-- Invoice & Tax Details -->
				<div class="card mt-10">

					<div class="card-header">
						<strong class="font-16">Invoice & Tax Details</strong>
					</div>

					<div class="card-body mt-10">

						<table class="table table-bordered">

							<tr>

								<th width="30%">MPR Approved Value</th>

								<td class="text-right">
									₹ <span class="format-indian" data-value="{{ number_format($invoice->mpr_approved_amount,'2','.','') }}"></span>
								</td>

							</tr>

							<tr>

								<th>Operating Margin</th>

								<td class="text-right">
									{{ number_format($invoice->operating_margin,2) }}%
									&nbsp;&nbsp;|&nbsp;&nbsp;
									₹ <span class="format-indian" data-value="{{ number_format($invoice->operating_cost,'2','.','') }}"></span>
								</td>

							</tr>

							<tr>

								<th width="30%">Taxable Amount</th>

								<td class="text-right">
									₹ <span class="format-indian" data-value="{{ number_format($invoice->taxable_amount,'2','.','') }}"></span>
								</td>

							</tr>

							<tr>

								<th>CGST</th>

								<td class="text-right">
									{{ number_format($invoice->cgst_percent,2) }}%
									&nbsp;&nbsp;|&nbsp;&nbsp;
									₹ <span class="format-indian" data-value="{{ number_format($invoice->cgst_amount,'2','.','') }}"></span>
								</td>

							</tr>

							<tr>

								<th>SGST</th>

								<td class="text-right">
									{{ number_format($invoice->sgst_percent,2) }}%
									&nbsp;&nbsp;|&nbsp;&nbsp;
									₹ <span class="format-indian" data-value="{{ number_format($invoice->sgst_amount,'2','.','') }}"></span>
								</td>

							</tr>

							<tr>

								<th>IGST</th>

								<td class="text-right">
									{{ number_format($invoice->igst_percent,2) }}%
									&nbsp;&nbsp;|&nbsp;&nbsp;
									₹ <span class="format-indian" data-value="{{ number_format($invoice->igst_amount,'2','.','') }}"></span>
								</td>

							</tr>

							<tr>

								<th>Invoice Amount</th>

								<td class="text-right">
									₹ <span class="format-indian" data-value="{{ number_format($invoice->net_invoice_value,'2','.','') }}"></span>
								</td>

							</tr>

							<tr>

								<th>
									<strong>Net Invoice Value</strong>
								</th>

								<td class="text-right">

									<strong>
										₹ <span class="format-indian" data-value="{{ number_format($invoice->net_invoice_value,'2','.','') }}"></span>
									</strong>

								</td>

							</tr>

						</table>

					</div>

				</div>


				<!-- Finance Verification Details -->
				<div class="card mt-10">

					<div class="card-header">
						<strong class="font-16">Finance Verification Details</strong>
					</div>

					<div class="card-body mt-10">

						<div class="row">

							<div class="col-md-3">

								<label>Finance Status</label>

								<div class="bg-light">
									{{ $invoice->finance_status ?: '-' }}
								</div>

							</div>

							<div class="col-md-3">

								<label>Verified By</label>

								<div class="bg-light">
									{{ $invoice->finance_verified_by_name ?: '-' }}
								</div>

							</div>

							<div class="col-md-3">

								<label>Verified On</label>

								<div class="bg-light">
									{{ $invoice->finance_verified_on ? date('d-m-Y H:i:s',strtotime($invoice->finance_verified_on)) : '-' }}
								</div>

							</div>

							<div class="col-md-3">

								<label>Submitted By</label>

								<div class="bg-light">
									{{ $invoice->submitted_by ?: '-' }}
								</div>

							</div>

						</div>


						<div class="row mt-20">

							<div class="col-md-12">

								<label>Finance Remarks</label>

								<div class="bg-light" style="height:auto; min-height:60px;">
									{{ $invoice->finance_remarks ?: '-' }}
								</div>

							</div>

						</div>

					</div>

				</div>


				<!-- Payment Summary -->
				<div class="card mt-10">

					<div class="card-header">
						<strong class="font-16">Payment Summary</strong>
					</div>

					<div class="card-body mt-10">

						<table class="table table-bordered">

							<tr>

								<th width="30%">Net Invoice Value</th>

								<td class="text-right">
									₹ <span class="format-indian" data-value="{{ number_format($invoice->net_invoice_value,'2','.','') }}"></span>
								</td>

							</tr>

							<tr>

								<th>Total Paid</th>

								<td class="text-right">
									₹ <span class="format-indian" data-value="{{ number_format($totalPaid,'2','.','') }}"></span>
								</td>

							</tr>

							<tr>

								<th>
									<strong>Current Balance</strong>
								</th>

								<td class="text-right">

									<strong>
										₹ <span class="format-indian" data-value="{{ number_format($currentBalance,'2','.','') }}"></span>
									</strong>

								</td>

							</tr>

						</table>

					</div>

				</div>


				<!-- Payment History -->
				<div class="card mt-10">

					<div class="card-header">
						<strong class="font-16">Payment History</strong>
					</div>

					<div class="card-body mt-10">

						<table class="table table-bordered">

							<tr>

								<th>#</th>
								<th>Payment Date</th>
								<th>Payment Mode</th>
								<th>Transaction No.</th>
								<th class="text-right">Gross Amount</th>
								<th class="text-right">Net Paid Amount</th>

							</tr>

							<tbody>

								@forelse($payments as $item)

									<tr>

										<td>
											{{ $loop->iteration }}
										</td>

										<td>
											{{ $item->payment_date ? date('d-m-Y',strtotime($item->payment_date)) : '-' }}
										</td>

										<td>
											{{ $item->payment_mode ?: '-' }}
										</td>

										<td>
											{{ $item->transaction_no ?: '-' }}
										</td>

										<td class="text-right">
											₹ {{ number_format($item->gross_amount,2) }}
										</td>

										<td class="text-right">
											₹ {{ number_format($item->net_paid_amount,2) }}
										</td>

									</tr>

								@empty

									<tr>

										<td colspan="6" class="text-center">
											No payment transaction found.
										</td>

									</tr>

								@endforelse

							</tbody>

						</table>

					</div>

				</div>


				<!-- Ledger Details -->
				<div class="card mt-10">

					<div class="card-header">
						<strong class="font-16">Ledger Details</strong>
					</div>

					<div class="card-body mt-10">

						<table class="table table-bordered">

							<tr>

								<th>Date</th>
								<th>Type</th>
								<th class="text-right">Debit</th>
								<th class="text-right">Credit</th>
								<th>Narration</th>

							</tr>

							<tbody>

								@forelse($ledger as $item)

									<tr>

										<td>
											{{ date('d-m-Y',strtotime($item->transaction_date)) }}
										</td>

										<td>
											{{ $item->ledger_type }}
										</td>

										<td class="text-right">
											₹ {{ number_format($item->dr_amount,2) }}
										</td>

										<td class="text-right">
											₹ {{ number_format($item->cr_amount,2) }}
										</td>

										<td>
											{{ $item->narration ?: '-' }}
										</td>

									</tr>

								@empty

									<tr>

										<td colspan="5" class="text-center">
											No ledger transaction found.
										</td>

									</tr>

								@endforelse

							</tbody>

						</table>

					</div>

				</div>


				<!-- Additional Details -->
				<div class="card mt-10">

					<div class="card-header">
						<strong class="font-16">Additional Details</strong>
					</div>

					<div class="card-body mt-10">

						<div class="row">

							<div class="col-md-6">

								<label>Vendor Remarks</label>

								<div class="bg-light" style="height:auto; min-height:60px;">
									{{ $invoice->invoice_remark ?: '-' }}
								</div>

							</div>

							<div class="col-md-6">

								<label>Invoice Attachment</label>

								<div>

									@if($invoice->invoice_file)

										<a href="{{ route('view.uploadedfile',Crypt::encrypt($invoice->invoice_file)) }}" target="_blank" class="btn btn-sm btn-info">
											<i class="fa fa-file"></i> View Invoice
										</a>

									@else

										<span class="text-muted">
											No invoice attachment
										</span>

									@endif

								</div>

							</div>

						</div>

					</div>

				</div>


				<!-- Finance Remark -->
				<div class="card mt-10">

					<div class="card-header">
						<strong class="font-16">Finance Remark</strong>
					</div>

					<div class="card-body mt-10">

						<div class="row">

							<div class="col-md-12">

								@if($invoice->finance_status == 'Pending')

									<textarea name="finance_remarks" id="finance_remarks" class="form-control" placeholder="Finance remark...">{{ $invoice->finance_remarks }}</textarea>

								@else

									{{ $invoice->finance_remarks ?? '-' }}

									<input type="hidden" name="finance_remarks" id="finance_remarks">

								@endif

							</div>

						</div>

					</div>

				</div>


				<!-- Available Department Fund Details -->
				@if($invoice->finance_status == 'Pending')

					@if($caseType == 'CSF')

<div class="card mt-10">
	<div class="card-header">
		<h4 class="font-16 font-bold">Fund Availability Against Vendor Invoice</h4>
	</div>
	<div class="card-body mt-10">
		<table class="table table-bordered">
			<tr class="myheadbg font-bold">
				<th>Particulars</th>
				<th class="no_wrap" style="text-align:right;">Vendor Invoice</th>
				<th></th>
				<th style="text-align:right;">CHiPS Administrative Charges @ {{ number_format((float)$adminChargePercent,2) }}%</th>
				<th style="text-align:right;">Total Invoice Raised by CHiPS</th>
			</tr>
			<tbody>
				<tr>
					<td class="myheadbg">Basic</td>
					<td style="text-align:right;" class="format-indian" data-value="{{ number_format((float)$invoiceBasicAmount,'2','.','') }}"></td>
					<td class="myheadbg"></td>
					<td style="text-align:right;" class="format-indian" data-value="{{ number_format((float)$fundAdminBasic,'2','.','') }}"></td>
					<td style="text-align:right;" class="format-indian" data-value="{{ number_format((float)$invoiceBasicAmount + (float)$fundAdminBasic,'2','.','') }}"></td>
				</tr>

				<tr>
					<td class="myheadbg">Tax @ {{ number_format((float)$departmentGstRate,2) }}%</td>
					<td style="text-align:right;" class="format-indian" data-value="{{ number_format((float)$invoiceGstAmount,'2','.','') }}"></td>
					<td class="myheadbg no_wrap">Tax @ {{ number_format((float)$departmentGstRate,2) }}% on CHiPS Administrative Charges</td>
					<td style="text-align:right;" class="format-indian" data-value="{{ number_format((float)$fundAdminGst,'2','.','') }}"></td>
					<td style="text-align:right;" class="format-indian" data-value="{{ number_format((float)$invoiceGstAmount + (float)$fundAdminGst,'2','.','') }}"></td>
				</tr>
				<tr>
					<td class="myheadbg"><b>Total</b></td>
					<td style="text-align:right;"><b class="format-indian" data-value="{{ number_format((float)$invoiceGrossAmount,'2','.','') }}"></b></td>
					<td class="myheadbg no_wrap"><b>Total CHiPS Administrative Charges</b></td>
					<td style="text-align:right;"><b class="format-indian" data-value="{{ number_format((float)$fundAdminTotal,'2','.','') }}"></b></td>
					<td style="text-align:right;"><b class="format-indian" data-value="{{ number_format((float)$invoiceGrossAmount + (float)$fundAdminTotal,'2','.','') }}"></b></td>
				</tr>
				<tr>
					<td class="myheadbg">GST TDS {{ number_format((float)$vendorFundingGstTdsRate,2) }}% Deducted by Department</td>
					<td style="text-align:right;" class="format-indian" data-value="{{ number_format((float)$fundInvoiceGstTds,'2','.','') }}"></td>
					<td class="myheadbg no_wrap">GST TDS {{ number_format((float)$serviceChargeGstTdsRate,2) }}% on CHiPS Administrative Charges</td>
					<td style="text-align:right;" class="format-indian" data-value="{{ number_format((float)$fundAdminGstTds,'2','.','') }}"></td>
					<td style="text-align:right;" class="format-indian" data-value="{{ number_format((float)$fundInvoiceGstTds + (float)$fundAdminGstTds,'2','.','') }}"></td>
				</tr>
				<tr>
					<td class="myheadbg">TDS {{ number_format((float)$vendorFundingTdsRate,2) }}% Deducted by Department</td>
					<td style="text-align:right;" class="format-indian" data-value="{{ number_format((float)$fundInvoiceTds,'2','.','') }}"></td>
					<td class="myheadbg no_wrap">TDS {{ number_format((float)$serviceChargeTdsRate,2) }}% on CHiPS Administrative Charges</td>
					<td style="text-align:right;" class="format-indian" data-value="{{ number_format((float)$fundAdminTds,'2','.','') }}"></td>
					<td style="text-align:right;" class="format-indian" data-value="{{ number_format((float)$fundInvoiceTds + (float)$fundAdminTds,'2','.','') }}"></td>
				</tr>
				<tr>
					<td class="myheadbg no_wrap"><b>Net Received Amount / Available Fund</b></td>
					<td style="text-align:right;"><b class="format-indian" data-value="{{ number_format((float)$fundInvoiceNet,'2','.','') }}"></b></td>
					<td class="myheadbg"><b>Net Administrative Charges</b></td>
					<td style="text-align:right;" class="width-150">
						<b class="format-indian" data-value="{{ number_format((float)$fundAdminNet,'2','.','') }}"></b>
					</td>
					<td style="text-align:right;" class="width-150">
						<b class="format-indian" data-value="{{ number_format((float)$fundTotalInvoice,'2','.','') }}"></b>
					</td>
				</tr>
			</tbody>
		</table>
	</div>
</div>
<div class="card mt-10">
	<div class="card-header">
		<h4 class="font-16 font-bold">Vendor Payment Calculation</h4>
	</div>
	<div class="card-body mt-10">
		<div class="row">
			<div class="col-md-4 mb-3">
				<label>TDS</label>
				<select name="tds_tax_id" id="tds_tax_id" class="form-control" onchange="calculateVendorPayment()">
					<option value="" data-rate="0">Select TDS</option>
					@foreach($tdsTaxes as $tax)
						<option value="{{ $tax->tax_id }}" data-rate="{{ (float)$tax->rate }}">{{ $tax->tax_name }} ({{ number_format((float)$tax->rate,2) }}%)</option>
					@endforeach
				</select>
			</div>

			<div class="col-md-4 mb-3">
				<label>GST-TDS</label>
				<select name="gst_tds_tax_id" id="gst_tds_tax_id" class="form-control" onchange="calculateVendorPayment()">
					<option value="" data-rate="0">Select GST-TDS</option>
					@foreach($gstTdsTaxes as $tax)
						<option value="{{ $tax->tax_id }}" data-rate="{{ (float)$tax->rate }}">{{ $tax->tax_name }} ({{ number_format((float)$tax->rate,2) }}%)</option>
					@endforeach
				</select>
			</div>
		</div>

		<table class="table table-bordered mt-10">
			<tbody>
				<tr>
					<td class="myheadbg" style="width:50%;"><b>Total Received (A)</b></td>
					<td style="text-align:right;"><b>₹ <span id="vendor_total_received" class="format-indian" data-value="{{ number_format((float)$vendorInvoiceAvailableFund,'2','.','') }}"></span></b></td>
				</tr>

				<tr>
					<td class="myheadbg">CHiPS Share Admin Charges {{ number_format((float)$adminChargePercent,2) }}% (B)</td>
					<td style="text-align:right;">₹ <span id="vendor_admin_charge" class="format-indian" data-value="{{ number_format((float)$adminChargeAmount,'2','.','') }}"></span></td>
				</tr>

				<tr>
					<td class="myheadbg"><b>Total Approval Amount as per Note Sheet (C = A - B)</b></td>
					<td style="text-align:right;"><b>₹ <span id="vendor_approval_amount" class="format-indian" data-value="{{ number_format((float)$vendorInvoiceApprovalAmount,'2','.','') }}"></span></b></td>
				</tr>

				<tr>
					<td class="myheadbg">Basic Amount Total Bill (D)</td>
					<td style="text-align:right;">₹ <span id="vendor_basic_amount" class="format-indian" data-value="{{ number_format((float)$invoiceBasicAmount,'2','.','') }}"></span></td>
				</tr>

				<tr>
					<td class="myheadbg">TDS <span id="vendor_tds_rate_display">0.00</span>% (E = D * TDS %)</td>
					<td style="text-align:right;">₹ <span id="vendor_tds_amount">0.00</span></td>
				</tr>

				<tr>
					<td class="myheadbg">GST-TDS <span id="vendor_gst_tds_rate_display">0.00</span>% (F = D * GST-TDS %)</td>
					<td style="text-align:right;">₹ <span id="vendor_gst_tds_amount">0.00</span></td>
				</tr>

				<tr>
					<td class="myheadbg"><b>Net Payment To Vendor (G = C - E - F)</b></td>
					<td style="text-align:right;"><b>₹ <span id="net_payment_to_vendor">0.00</span></b></td>
				</tr>
			</tbody>
		</table>
	</div>
</div>
<div class="card mt-10">
	<div class="card-header">
		<h4 class="font-16 font-bold">Available Fund Balance</h4>
	</div>

	<div class="card-body mt-10">
		<table class="table table-bordered">
			<tbody>
				<tr>
					<td class="myheadbg" style="width:50%;"><b>Department Fund Received</b></td>
					<td style="text-align:right;">
						₹ <b class="format-indian" data-value="{{ number_format((float)$departmentReceived,'2','.','') }}"></b>
					</td>
				</tr>

				<tr>
					<td class="myheadbg">Amount Utilized for Previous Verified Vendor Invoices</td>
					<td style="text-align:right;">₹ <span class="format-indian" data-value="{{ number_format((float)$utilizedAmount,'2','.','') }}"></span></td>
				</tr>

				<tr>
					<td class="myheadbg"><b>Available Fund Before Current Invoice</b></td>
					<td style="text-align:right;">
						₹ <b class="format-indian" data-value="{{ number_format((float)$balanceBeforeCurrentInvoice,'2','.','') }}"></b>
					</td>
				</tr>

				<tr>
					<td class="myheadbg">Current Invoice Approval Amount</td>
					<td style="text-align:right;">
						₹ <span class="format-indian" data-value="{{ number_format((float)$vendorInvoiceAvailableFund,'2','.','') }}"></span>
					</td>
				</tr>

				<tr>
					<td class="myheadbg"><b>Balance After Current Invoice</b></td>
					<td style="text-align:right;">
						₹ <b class="format-indian" data-value="{{ number_format(max((float)$availableBalance,0),'2','.','') }}"></b>
					</td>
				</tr>

				<tr>
					<td class="myheadbg"><b>Net Payment To Vendor</b></td>
					<td style="text-align:right;">
						₹ <b id="available_balance_net_payment">00000</b>
					</td>
				</tr>

				<tr>
					<td class="myheadbg"><b>Fund Availability Status</b></td>
					<td style="text-align:right;">
						@if($hasCsfShortfall)
							<span class="label label-danger" style="font-size:12px; padding:4px 10px;">
								INSUFFICIENT FUND
							</span>
							<div class="text-danger mt-5">
								Required Shortfall: ₹ <span class="format-indian" data-value="{{ number_format((float)$shortfall,'2','.','') }}"></span>
							</div>
						@else
							<span class="label label-success font-bold br-5" style="font-size:12px; padding:4px 10px;">
								FUND AVAILABLE
							</span>
						@endif
					</td>
				</tr>
			</tbody>
		</table>

		@if($hasCsfShortfall)
			<div class="alert alert-danger mt-10">
				<strong>Payment cannot be approved.</strong><br>
				Available department fund is insufficient for the current invoice approval amount.
				Required additional fund:
				₹ <strong class="format-indian" data-value="{{ number_format((float)$shortfall,'2','.','') }}"></strong>
			</div>
		@else
			<div class="alert alert-success mt-10">
				<strong>Fund is available.</strong>
				The current invoice approval amount of
				₹ <strong class="format-indian" data-value="{{ number_format((float)$vendorInvoiceApprovalAmount,'2','.','') }}"></strong>
				can be accommodated from the available department fund.
			</div>
		@endif
	</div>
</div>

					@elseif($caseType == 'AWD')

						<div class="card mt-10">
							<div class="card-header"><h4 class="font-16 font-bold">Vendor Payment Calculation - AWD</h4></div>
							<div class="card-body mt-10">
								<div class="row">
									<div class="col-md-4 mb-3"><label>TDS</label><select name="tds_tax_id" id="tds_tax_id" class="form-control" onchange="calculateVendorPayment()"><option value="" data-rate="0">Select TDS</option>@foreach($tdsTaxes as $tax)<option value="{{ $tax->tax_id }}" data-rate="{{ (float)$tax->rate }}">{{ $tax->tax_name }} ({{ number_format((float)$tax->rate,2) }}%)</option>@endforeach</select></div>
									<div class="col-md-4 mb-3"><label>GST-TDS</label><select name="gst_tds_tax_id" id="gst_tds_tax_id" class="form-control" onchange="calculateVendorPayment()"><option value="" data-rate="0">Select GST-TDS</option>@foreach($gstTdsTaxes as $tax)<option value="{{ $tax->tax_id }}" data-rate="{{ (float)$tax->rate }}">{{ $tax->tax_name }} ({{ number_format((float)$tax->rate,2) }}%)</option>@endforeach</select></div>
								</div>
								<table class="table table-bordered mt-10"><tbody>
									<tr><td class="myheadbg" style="width:50%;"><b>Invoice Gross Value (A)</b></td><td style="text-align:right;"><b>₹ <span class="format-indian" data-value="{{ number_format((float)$invoice->net_invoice_value,'2','.','') }}"></span></b></td></tr>
									<tr><td class="myheadbg"><b>Taxable Amount (B)</b></td><td style="text-align:right;">₹ <span class="format-indian" data-value="{{ number_format((float)$invoice->taxable_amount,'2','.','') }}"></span></td></tr>
									<tr><td class="myheadbg">TDS <span id="vendor_tds_rate_display">0.00</span>% (C = B * TDS %)</td><td style="text-align:right;">₹ <span id="vendor_tds_amount">0.00</span></td></tr>
									<tr><td class="myheadbg">GST-TDS <span id="vendor_gst_tds_rate_display">0.00</span>% (D = B * GST-TDS %)</td><td style="text-align:right;">₹ <span id="vendor_gst_tds_amount">0.00</span></td></tr>
									<tr><td class="myheadbg"><b>Net Payable to Vendor (E = A - C - D)</b></td><td style="text-align:right;"><b>₹ <span id="net_payment_to_vendor">0.00</span></b></td></tr>
								</tbody></table>
							</div>
						</div>

					@endif

				@endif

				<!-- Actions -->
				@if($invoice->finance_status == 'Pending')

					<div class="card mt-10">

						<div class="card-body text-right">
							@if($caseType != 'CSF' || !$hasCsfShortfall)
							<button type="button" class="btn btn-success btnVerifyInvoice" data-id="{{ $invoice->voucher_id }}">
								<i class="fa fa-check"></i> Verify This Invoice
							</button>
							@endif
							<!--
							<button type="button" class="btn btn-danger btnRejectInvoice" data-id="{{ $invoice->voucher_id }}">
								<i class="fa fa-times"></i> Reject
							</button>
							-->

						</div>

					</div>

				@endif


			</form>

		</div>
	</div>
</div>


<script src="{{ asset('panel/assets/js/jquery-2.1.4.min.js') }}"></script>
<script src="{{ asset('panel/assets/js/ace-elements.min.js') }}"></script>
<script src="{{ asset('panel/assets/js/ace.min.js') }}"></script>


<script>

$(document).ready(function () {

	document.querySelectorAll('.format-indian').forEach(function (el) {
		el.style.fontWeight = 'bold';
		el.innerText = formatIndianNumber(el.getAttribute('data-value'));
	});

});


$(document).on('click','.btnVerifyMpr',function()
{
	let button = $(this);
	let mprId = button.data('mpr-id');
	let voucherId = "{{ $invoice->voucher_id }}";

	bootbox.confirm(
		'Are you sure you want to Finance Verify this MPR?',
		function(result)
		{
			if(!result)
			{
				return;
			}

			button.prop('disabled',true);

			$.ajax({
				url: "{{ route('finance.vendorinvoice.mpr.verify') }}",
				type: 'POST',
				data: {
					_token: "{{ csrf_token() }}",
					voucher_id: voucherId,
					mpr_id: mprId
				},
				success: function(response)
				{
					if(response.status == 1)
					{
						button
							.removeClass('btn-warning')
							.addClass('btn-info')
							.prop('disabled',true)
							.html('<i class="fa fa-check-circle"></i> Verified');

						bootbox.alert(response.message);
					}
					else
					{
						bootbox.alert(response.message);
						button.prop('disabled',false);
					}
				},
				error: function(xhr)
				{
					let message = 'Something went wrong.';

					if(xhr.responseJSON && xhr.responseJSON.message)
					{
						message = xhr.responseJSON.message;
					}

					bootbox.alert(message);
					button.prop('disabled',false);
				}
			});
		}
	);
});


$(document).on('click','.btnVerifyInvoice',function()
{
	let button = $(this);
	let voucherId = button.data('id');
	let finance_remarks = $("#finance_remarks").val();
	let vendor_tds_tax_id  = $("#tds_tax_id").val();
	let vendor_gst_tds_tax_id  = $("#gst_tds_tax_id").val();

	if(vendor_tds_tax_id=='' || vendor_gst_tds_tax_id=='')
	{
		bootbox.alert("Please select TDS and GST-TDS values");
		return false;
	}

	bootbox.confirm(
		'Are you sure you want to Finance Verify this Vendor Invoice?',
		function(result)
		{
			if(!result)
			{
				return;
			}

			button.prop('disabled',true);

			verifyVendorInvoice(voucherId,button,finance_remarks,vendor_tds_tax_id,vendor_gst_tds_tax_id);
		}
	);
});


function verifyVendorInvoice(voucherId,button,finance_remarks,vendor_tds_tax_id,vendor_gst_tds_tax_id)
{
	$.ajax({
		url: "{{ route('finance.vendorinvoice.invoice.verify') }}",
		type: 'POST',
		data: {
			_token: "{{ csrf_token() }}",
			voucher_id: voucherId,
			finance_remarks: finance_remarks,
			vendor_tds_tax_id : vendor_tds_tax_id,
			vendor_gst_tds_tax_id : vendor_gst_tds_tax_id
		},
		success: function(response)
		{
			if(response.status == 1)
			{
				bootbox.alert(response.message,function()
				{
					location.reload();
				});

				return;
			}

			bootbox.alert(response.message);
			button.prop('disabled',false);
		},
		error: function(xhr)
		{
			let message = 'Something went wrong.';

			if(xhr.responseJSON && xhr.responseJSON.message)
			{
				message = xhr.responseJSON.message;
			}

			bootbox.alert(message);
			button.prop('disabled',false);
		}
	});
}


function calculateVendorPayment()
{
	var caseType = "{{ $caseType }}";
	var approvalAmount = parseFloat('{{ (float)$vendorInvoiceApprovalAmount }}') || 0;
	var grossAmount = parseFloat('{{ (float)$invoice->net_invoice_value }}') || 0;
	var basicAmount = parseFloat('{{ (float)$invoiceBasicAmount }}') || 0;
	var tdsRate = parseFloat($('#tds_tax_id option:selected').data('rate')) || 0;
	var gstTdsRate = parseFloat($('#gst_tds_tax_id option:selected').data('rate')) || 0;
	var tdsAmount = Math.round((basicAmount * tdsRate / 100) * 100) / 100;
	var gstTdsAmount = Math.round((basicAmount * gstTdsRate / 100) * 100) / 100;
	var netPayment = caseType == 'AWD' ? Math.round((grossAmount - tdsAmount - gstTdsAmount) * 100) / 100 : Math.round((approvalAmount - tdsAmount - gstTdsAmount) * 100) / 100;
	if(netPayment < 0) netPayment = 0;
	$('#vendor_tds_rate_display').text(tdsRate.toFixed(2));
	$('#vendor_gst_tds_rate_display').text(gstTdsRate.toFixed(2));
	$('#vendor_tds_amount').text(formatIndianNumber(Math.round(tdsAmount.toFixed(2))));
	$('#vendor_gst_tds_amount').text(formatIndianNumber(Math.round(gstTdsAmount.toFixed(2))));
	$('#net_payment_to_vendor').text(formatIndianNumber(Math.round(netPayment.toFixed(2))));
	$('#available_balance_net_payment').text(formatIndianNumber(Math.round(netPayment.toFixed(2))));
}
</script>

<script src="{{ asset('panel/assets/js/datatablePaginationWithFilter.js') }}"></script>

@endsection