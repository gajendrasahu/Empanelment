@extends('admin.admin_master')

@section('admin')

<div class="main-content">
	<div class="main-content-inner">
		<div class="page-content">

			<div class="card mt-3">

				<div class="card-header">
					<h4 class="font-16 font-bold">Demand Note Group Details</h4>
				</div>

				<div class="card-body mt-10">

					<table class="table table-bordered">

						<tr>
							<td class="myheadbg width-150">Demand Note Group</td>
							<td colspan="3">
								<b>{{ $vendorFunding->demand_note_group_uuid ?: '-' }}</b>
							</td>
						</tr>

						<tr>
							<td class="myheadbg width-150">Department</td>
							<td>
								<b>{{ $vendorFunding->departmentname ?: '-' }}</b>
							</td>

							<td class="myheadbg width-150">Financial Year</td>
							<td>
								{{ $vendorFunding->financial_year ?: '-' }}
							</td>
						</tr>

						<tr>
							<td class="myheadbg">Request / EOI</td>
							<td>
								{{ $vendorFunding->eoinumber ?: '-' }}
							</td>

							<td class="myheadbg">Order</td>
							<td>
								{{ $vendorFunding->order_id ?: '-' }}
							</td>
						</tr>

						<tr>
							<td class="myheadbg">Project</td>
							<td>
								{{ $vendorFunding->project_id ?: '-' }}
							</td>

							<td class="myheadbg">Demand Note Date</td>
							<td>
								{{ $vendorFunding->demand_note_date ? date('d-m-Y',strtotime($vendorFunding->demand_note_date)) : '-' }}
							</td>
						</tr>

					</table>

				</div>

			</div>


			<div class="card mt-10">

				<div class="card-header">
					<h4 class="font-16 font-bold">
						Vendor Payment Advance
					</h4>
				</div>

				<div class="card-body mt-10">

					<table class="table table-bordered">

						<tr>
							<td class="myheadbg width-150">
								Demand Note No.
							</td>

							<td>
								<b>{{ $vendorFunding->demand_note_no ?: '-' }}</b>
							</td>

							<td class="myheadbg width-150">
								Payment Status
							</td>

							<td>
								<b>{{ $vendorFunding->payment_status ?: '-' }}</b>
							</td>
						</tr>

						<tr>
							<td class="myheadbg">
								Tax
							</td>

							<td>
								{{ $vendorFunding->tax_name ?: '-' }}
							</td>

							<td class="myheadbg">
								GSTIN
							</td>

							<td>
								{{ $vendorFunding->gstin ?: '-' }}
							</td>
						</tr>

					</table>


					<table class="table table-bordered mt-10">

						<tr>
							<td class="myheadbg width-250">
								<b>Taxable Amount</b>
							</td>
							<td class="myheadbg width-250">
								<b>CGST</b>
							</td>
							<td class="myheadbg">
								<b>SGST</b>
							</td>
							<td class="myheadbg">
								<b>IGST</b>
							</td>
							<td class="myheadbg">
								<b>Total Demand Note Amount</b>
							</td>

						</tr>

						<tr>
							<td style="text-align:left;">
								<span class="format-indian" data-value="{{ $vendorFunding->taxable_amount }}"></span>
							</td>


							<td style="text-align:left;">
								<span class="format-indian" data-value="{{ $vendorFunding->cgst_amount }}"></span>
							</td>

							<td style="text-align:left;">
								<span class="format-indian" data-value="{{ $vendorFunding->sgst_amount }}"></span>
							</td>


							<td style="text-align:left;">
								<span class="format-indian" data-value="{{ $vendorFunding->igst_amount }}"></span>
							</td>
							<td style="text-align:left;">
								<b>
									<span class="format-indian" data-value="{{ $vendorFunding->total_amount }}"></span>
								</b>
							</td>
							
						</tr>

					</table>


					<table class="table table-bordered mt-10">

						<tr>
							<td class="myheadbg width-150">
								Particulars
							</td>

							<td>
								{!! $vendorFunding->particular ?: '-' !!}
							</td>
						</tr>

					</table>

				</div>

			</div>

			<div class="card mt-10">

				<div class="card-header">
					<h4 class="font-16 font-bold">
						Department Receipts - Vendor Payment Advance
					</h4>
				</div>

				<div class="card-body mt-10">

					<table class="table table-bordered">

						
							<tr class="myheadbg font-bold">

								<td>Receipt No.</td>
								<td>Receipt Date</td>
								<td>Payment Mode</td>
								<td>Voucher No.</td>
								<td>Transaction No.</td>

								<td style="text-align:right;">
									Gross Receipt
								</td>

								<td style="text-align:right;">
									Invoiced
								</td>

								<td style="text-align:right;">
									Balance
								</td>

							</tr>
						

						<tbody>

							@foreach($vendorFundingReceipts as $item)

								@php
									$receiptInvoiced = DB::table('finance_department_invoice_payment')
										->where('department_payment_id',$item->department_payment_id)
										->sum('allocated_amount');

									$receiptBalance =
										(float)$item->gross_received_amount -
										(float)$receiptInvoiced;
								@endphp

								<tr>

									<td>
										{{ $item->receipt_no ?: '-' }}
									</td>

									<td>
										{{ $item->receipt_date ? date('d-m-Y',strtotime($item->receipt_date)) : '-' }}
									</td>

									<td>
										{{ $item->payment_mode ?: '-' }}
									</td>

									<td>
										{{ $item->voucher_no ?: '-' }}
									</td>

									<td>
										{{ $item->transaction_no ?: '-' }}
									</td>

									<td style="text-align:right;">
										<span class="format-indian" data-value="{{ $item->gross_received_amount }}"></span>
									</td>

									<td style="text-align:right;">
										<span class="format-indian" data-value="{{ $receiptInvoiced }}"></span>
									</td>

									<td style="text-align:right;">
										<span class="format-indian" data-value="{{ $receiptBalance }}"></span>
									</td>

								</tr>

							@endforeach


							@if($vendorFundingReceipts->count() == 0)

								<tr>
									<td colspan="8" style="text-align:center;">
										<br>
										<i class="fa fa-warning blue nodata"></i>
										<br>
										{!! __('messages.sorry') !!}
									</td>
								</tr>

							@endif

						</tbody>


						@if($vendorFundingReceipts->count() > 0)

							<tfoot>

								<tr>

									<th colspan="5" style="text-align:right;">
										Total
									</th>

									<th style="text-align:right;">
										<span class="format-indian" data-value="{{ $vendorFundingTotalReceived }}"></span>
									</th>

									<th style="text-align:right;">
										<span class="format-indian" data-value="{{ $vendorFundingTotalInvoiced }}"></span>
									</th>

									<th style="text-align:right;">
										<span class="format-indian" data-value="{{ $vendorFundingRemainingBalance }}"></span>
									</th>

								</tr>

							</tfoot>

						@endif

					</table>

				</div>

			</div>

			<div class="card mt-10">

				<div class="card-header">
					<h4 class="font-16 font-bold">
						CHiPS Service Charge
					</h4>
				</div>

				<div class="card-body mt-10">

					<table class="table table-bordered">
						<tr>
							<td class="myheadbg width-150">Demand Note No.</td>
							<td><b>{{ $serviceCharge->demand_note_no ?: '-' }}</b></td>
							<td class="myheadbg width-150">Payment Status</td>
							<td><b>{{ $serviceCharge->payment_status ?: '-' }}</b></td>
						</tr>
						<tr>
							<td class="myheadbg">Tax</td>
							<td>{{ $serviceCharge->tax_name ?: '-' }}</td>
							<td class="myheadbg">GSTIN</td>
							<td>{{ $serviceCharge->gstin ?: '-' }}</td>
						</tr>
					</table>


					<table class="table table-bordered mt-10">
						<tr>
							<td class="myheadbg width-250">
								<b>Service Charge ({{$serviceCharge->adminChargePercentage}}%)</b>
							</td>
							<td class="myheadbg width-250">
								<b>CGST</b>
							</td>
							<td class="myheadbg">
								<b>SGST</b>
							</td>
							<td class="myheadbg">
								<b>IGST</b>
							</td>
							<td class="myheadbg">
								<b>Total Service Charge</b>
							</td>


						</tr>

						<tr>

							<td style="text-align:left;">
								<span class="format-indian" data-value="{{ $serviceCharge->taxable_amount }}"></span>
							</td>


							<td style="text-align:left;">
								<span class="format-indian" data-value="{{ $serviceCharge->cgst_amount }}"></span>
							</td>

							<td style="text-align:left;">
								<span class="format-indian" data-value="{{ $serviceCharge->sgst_amount }}"></span>
							</td>


							<td style="text-align:left;">
								<span class="format-indian" data-value="{{ $serviceCharge->igst_amount }}"></span>
							</td>
							<td style="text-align:left;">
								<b>
									<span class="format-indian" data-value="{{ $serviceCharge->total_amount }}"></span>
								</b>
							</td>

						</tr>
					</table>


					<table class="table table-bordered mt-10">

						<tr>

							<td class="myheadbg width-150">
								Particulars
							</td>

							<td>
								{!! $serviceCharge->particular ?: '-' !!}
							</td>

						</tr>

					</table>

				</div>

			</div>

			<div class="card mt-10">

				<div class="card-header">
					<h4 class="font-16 font-bold">
						Department Receipts - CHiPS Service Charge
					</h4>
				</div>

				<div class="card-body mt-10">

					<table class="table table-bordered">

						<thead>

							<tr class="myheadbg font-bold">

								<td>Receipt No.</td>
								<td>Receipt Date</td>
								<td>Payment Mode</td>
								<td>Voucher No.</td>
								<td>Transaction No.</td>

								<td style="text-align:right;">
									Gross Receipt
								</td>

								<td style="text-align:right;">
									Invoiced
								</td>

								<td style="text-align:right;">
									Balance
								</td>

							</tr>

						</thead>

						<tbody>

							@foreach($serviceChargeReceipts as $item)

								@php

									$receiptInvoiced = DB::table('finance_department_invoice_payment')
										->where(
											'department_payment_id',
											$item->department_payment_id
										)
										->sum('allocated_amount');

									$receiptBalance =
										(float)$item->gross_received_amount -
										(float)$receiptInvoiced;

								@endphp

								<tr>

									<td>
										{{ $item->receipt_no ?: '-' }}
									</td>

									<td>
										{{ $item->receipt_date ? date('d-m-Y',strtotime($item->receipt_date)) : '-' }}
									</td>

									<td>
										{{ $item->payment_mode ?: '-' }}
									</td>

									<td>
										{{ $item->voucher_no ?: '-' }}
									</td>

									<td>
										{{ $item->transaction_no ?: '-' }}
									</td>

									<td style="text-align:right;">
										<span class="format-indian" data-value="{{ $item->gross_received_amount }}"></span>
									</td>

									<td style="text-align:right;">
										<span class="format-indian" data-value="{{ $receiptInvoiced }}"></span>
									</td>

									<td style="text-align:right;">
										<span class="format-indian" data-value="{{ $receiptBalance }}"></span>
									</td>

								</tr>

							@endforeach


							@if($serviceChargeReceipts->count() == 0)

								<tr>
									<td colspan="8" style="text-align:center;">
										<br>
										<i class="fa fa-warning blue nodata"></i>
										<br>
										{!! __('messages.sorry') !!}
									</td>
								</tr>
							@endif

						</tbody>


						@if($serviceChargeReceipts->count() > 0)

							<tfoot>

								<tr>

									<th colspan="5" style="text-align:right;">
										Total
									</th>

									<th style="text-align:right;">
										<span class="format-indian" data-value="{{ $serviceChargeTotalReceived }}"></span>
									</th>

									<th style="text-align:right;">
										<span class="format-indian" data-value="{{ $serviceChargeTotalInvoiced }}"></span>
									</th>

									<th style="text-align:right;">
										<span class="format-indian" data-value="{{ $serviceChargeRemainingBalance }}"></span>
									</th>

								</tr>

							</tfoot>

						@endif

					</table>

				</div>

			</div>

			<div class="card mt-10">

				<div class="card-header">
					<h4 class="font-16 font-bold">
						Remarks
					</h4>
				</div>

				<div class="card-body mt-10">

					<table class="table table-bordered">

						<tr>

							<td class="myheadbg width-150">
								Vendor Payment Advance
							</td>

							<td>
								{!! $vendorFunding->remarks ?: '-' !!}
							</td>

						</tr>

						<tr>

							<td class="myheadbg">
								CHiPS Service Charge
							</td>

							<td>
								{!! $serviceCharge->remarks ?: '-' !!}
							</td>

						</tr>

					</table>

				</div>

			</div>

		</div>
	</div>
</div>


<script src="{{ asset('panel/assets/js/jquery-2.1.4.min.js') }}"></script>

<script>
$(document).ready(function () {

	document.querySelectorAll('.format-indian').forEach(function (el) {
		el.style.fontWeight = 'bold';
		el.innerText = formatIndianNumber(el.getAttribute('data-value'));
	});

});
</script>

<script src="{{ asset('panel/assets/js/datatablePaginationWithFilter.js') }}"></script>

@endsection