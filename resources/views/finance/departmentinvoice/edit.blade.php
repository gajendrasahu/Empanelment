@extends('admin.admin_master')

@section('admin')

<div class="main-content">
	<div class="main-content-inner">
		<div class="page-content">

			<div class="ace-settings-container" id="ace-settings-container"></div>

			<form id="frmDepartmentInvoice" enctype="multipart/form-data">
				@csrf

				<input type="hidden" name="department_invoice_id" value="{{ $invoice->department_invoice_id }}">

				<div class="card mt-3">

					<div class="card-body mt-10">

						<table class="table table-bordered width-700">

							<tr>
								<td class="myheadbg">Department</td>
								<td>
									<b>{{ $department->departmentname }}</b>
									<input type="hidden" name="department_id" value="{{ $departmentId }}">
								</td>

								<td class="myheadbg">Current Invoice Amount</td>
								<td style="text-align:right;">
									<b><span class="format-indian" data-value="{{ $invoice->invoice_amount }}"></span></b>
								</td>
							</tr>

						</table>

					</div>

				</div>


				<div class="card mt-3">

					<div class="card-header mt-10">
						<h4>Receipt Allocation</h4>
					</div>

					<div class="card-body mt-10">

						<table class="table table-bordered">

							<thead>
								<tr class="myheadbg font-bold">
									<td>Receipt No.</td>
									<td>Receipt Date</td>
									<td>Demand Note</td>
									<td style="text-align:right;">Gross Receipt</td>
									<td style="text-align:right;">Previously Invoiced</td>
									<td style="text-align:right;">Available Balance</td>
									<td style="text-align:right;">Current Allocation</td>
								</tr>
							</thead>

							<tbody>

								@foreach($receipts as $item)

								<tr>

									<td>{{ $item->receipt_no ?: '-' }}</td>

									<td>{{ $item->receipt_date ? date('d-m-Y',strtotime($item->receipt_date)) : '-' }}</td>

									<td>{{ $item->demand_note_no ?: '-' }}</td>

									<td style="text-align:right;">
										<span class="format-indian" data-value="{{ $item->gross_received_amount }}"></span>
									</td>

									<td style="text-align:right;">
										<span class="format-indian" data-value="{{ $item->previous_invoiced }}"></span>
									</td>

									<td style="text-align:right;">
										<span class="format-indian" data-value="{{ $item->invoice_balance }}"></span>
									</td>

									<td style="text-align:right;">
										<input type="text" name="allocation_amount[{{ $item->department_payment_id }}]" class="form-control text-right allocationAmount" data-payment-id="{{ $item->department_payment_id }}" data-balance="{{ $item->invoice_balance }}" data-gst-compatible="{{ $item->gst_compatible }}" value="{{ number_format($item->current_allocation,2,'.','') }}" placeholder="0.00">
									</td>

								</tr>

								@endforeach

							</tbody>

							<tfoot>

								<tr>
									<th colspan="6" style="text-align:right;">
										Total Allocation
									</th>

									<th style="text-align:right;">
										<input type="text" id="totalAllocation" name="totalAllocation" class="form-control text-right" value="{{ number_format($invoice->invoice_amount,2,'.','') }}" readonly>
									</th>
								</tr>

							</tfoot>

						</table>

					</div>

				</div>


				<div class="card mt-10">

					<div class="card-header">
						<h4>Invoice Details</h4>
					</div>

					<div class="card-body mt-10">

						<div class="row">

							<div class="col-md-3">
								<label>Financial Year <span class="text-danger">*</span></label>
								<select name="financial_year" id="financial_year" class="form-control">
									@foreach($financialYears as $year)
										<option value="{{ $year }}" {{ $invoice->financial_year == $year ? 'selected' : '' }}>{{ $year }}</option>
									@endforeach
								</select>
							</div>

							<div class="col-md-3">
								<label>Invoice No. <span class="text-danger">*</span></label>
								<input type="text" name="invoice_no" id="invoice_no" class="form-control" value="{{ $invoice->invoice_no }}" placeholder="Invoice number">
							</div>

							<div class="col-md-3">
								<label>Invoice Date <span class="text-danger">*</span></label>
								<input type="text" name="invoice_date" id="invoice_date" class="form-control todays_dt_blank" value="{{ $invoice->invoice_date ? date('d-m-Y',strtotime($invoice->invoice_date)) : '' }}" placeholder="dd-mm-YYYY">
							</div>

							<div class="col-md-3">
								<label>GSTIN</label>
								<input type="text" name="gstin" id="gstin" class="form-control" value="{{ $invoice->gstin }}" placeholder="GSTIN">
							</div>

						</div>

						<div class="row mt-10">

							<div class="col-md-6">
								<label>Period From</label>
								<input type="text" name="period_from" id="period_from" class="form-control" value="{{ $invoice->period_from ? date('d-m-Y',strtotime($invoice->period_from)) : '' }}" placeholder="Period from">
							</div>

							<div class="col-md-6">
								<label>Period To</label>
								<input type="text" name="period_to" id="period_to" class="form-control" value="{{ $invoice->period_to ? date('d-m-Y',strtotime($invoice->period_to)) : '' }}" placeholder="Period to">
							</div>

						</div>

						<div class="row mt-10">

							<div class="col-md-12">
								<label>Particulars</label>
								<textarea name="particulars" id="particulars" class="form-control" placeholder="Particular.." rows="3">{{ $invoice->particulars }}</textarea>
							</div>

						</div>

						<div class="row mt-10">

							<div class="col-md-12">
								<label>Remarks</label>
								<textarea name="remarks" id="remarks" class="form-control" placeholder="Remarks.." rows="3">{{ $invoice->remarks }}</textarea>
							</div>

						</div>

					</div>

				</div>


				<div class="card mt-10">

					<div class="card-header">
						<h4>Invoice Amount</h4>
					</div>

					<div class="card-body mt-10">

						<table class="table table-bordered">

							<tr>
								<td class="">Taxable Amount</td>
								<td style="text-align:right;">
									<input type="text" id="taxableAmount" name="taxable_amount" class="form-control text-right" value="{{ number_format($invoice->taxable_amount,2,'.','') }}" readonly>
								</td>

								<td class="">CGST ({{ number_format($invoice->cgst_percent,2,'.','') }}%)</td>
								<td style="text-align:right;">
									<input type="text" id="cgstAmount" name="cgst_amount" class="form-control text-right" value="{{ number_format($invoice->cgst_amount,2,'.','') }}" readonly>
								</td>
							</tr>

							<tr>
								<td class="">SGST ({{ number_format($invoice->sgst_percent,2,'.','') }}%)</td>
								<td style="text-align:right;">
									<input type="text" id="sgstAmount" name="sgst_amount" class="form-control text-right" value="{{ number_format($invoice->sgst_amount,2,'.','') }}" readonly>
								</td>

								<td class="">IGST ({{ number_format($invoice->igst_percent,2,'.','') }}%)</td>
								<td style="text-align:right;">
									<input type="text" id="igstAmount" name="igst_amount" class="form-control text-right" value="{{ number_format($invoice->igst_amount,2,'.','') }}" readonly>
								</td>
							</tr>

							<tr>
								<td class=""><b>Invoice Amount</b></td>
								<td style="text-align:right;">
									<input type="text" id="invoiceAmount" name="invoice_amount" class="form-control text-right font-bold" value="{{ number_format($invoice->invoice_amount,2,'.','') }}" readonly>
								</td>

								<td></td>
								<td></td>
							</tr>

						</table>

					</div>

				</div>


				<div class="card mt-10">

					<div class="card-body text-right">

						<button type="button" id="btnUpdateInvoice" class="btn btn-info gridbtn width-150">
							<i class="fa fa-refresh"></i> Update Invoice
						</button>

					</div>

				</div>

			</form>

		</div>
	</div>
</div>

<script src="{{ asset('panel/assets/js/jquery-2.1.4.min.js') }}"></script>

<script>
$(document).ready(function()
{
	$('.format-indian').each(function()
	{
		$(this).css('font-weight','bold');
		$(this).text(formatIndianNumber($(this).data('value')));
	});

	calculateInvoice();

	$(document).on('input change','.allocationAmount',function()
	{
		let balance = parseFloat($(this).attr('data-balance')) || 0;
		let amount = parseFloat($(this).val()) || 0;
		let gstCompatible = parseInt($(this).attr('data-gst-compatible')) || 0;

		if(amount < 0)
		{
			$(this).val('0.00');
			amount = 0;
		}

		if(amount > balance)
		{
			bootbox.alert('Allocation Amount cannot be greater than Receipt Balance.');

			$(this).val(balance.toFixed(2));
		}

		if(amount > 0 && gstCompatible != 1)
		{
			bootbox.alert('This receipt has a different GST treatment and cannot be allocated to this invoice.');

			$(this).val('0.00');
		}

		calculateInvoice();
	});


	$(document).on('click', '#btnUpdateInvoice', function () {

		let totalAllocation = parseFloat($('#totalAllocation').val()) || 0;

		if (totalAllocation <= 0) {
			bootbox.alert('Please enter allocation amount for at least one receipt.');
			return false;
		}

		bootbox.confirm({
			message: 'Are you sure you want to update this Department Invoice?',
			buttons: {
				confirm: {
					label: '<i class="fa fa-check"></i> Yes, Update Invoice',
					className: 'btn-success'
				},
				cancel: {
					label: '<i class="fa fa-times"></i> Cancel',
					className: 'btn-secondary'
				}
			},
			callback: function (result) {

				if (!result) {
					return;
				}

				let form = document.getElementById('frmDepartmentInvoice');
				let formData = new FormData(form);

				$('#btnUpdateInvoice')
					.prop('disabled', true)
					.html('<i class="fa fa-spinner fa-spin"></i> Updating...');

				$.ajax({
					url: "{{ route('finance.departmentinvoice.update') }}",
					type: "POST",
					data: formData,
					processData: false,
					contentType: false,

					success: function (response) {

						if (response.status == 1) {

							bootbox.alert(response.message, function () {
								window.location.href = response.redirect;
							});

						} else {

							bootbox.alert(response.message);

							$('#btnUpdateInvoice')
								.prop('disabled', false)
								.html('<i class="fa fa-save"></i> Update Invoice');
						}
					},

					error: function (xhr) {

						let message = 'Unable to update Department Invoice.';

						if (xhr.responseJSON && xhr.responseJSON.message) {
							message = xhr.responseJSON.message;
						}

						if (xhr.responseJSON && xhr.responseJSON.errors) {

							message = '';

							$.each(xhr.responseJSON.errors, function (field, error) {
								message += error[0] + '<br>';
							});
						}

						bootbox.alert(message);

						$('#btnUpdateInvoice')
							.prop('disabled', false)
							.html('<i class="fa fa-save"></i> Update Invoice');
					}
				});
			}
		});

		return false;
	});

});

function calculateInvoice()
{
	let totalAllocation = 0;

	$('.allocationAmount').each(function()
	{
		let amount = parseFloat($(this).val()) || 0;

		totalAllocation += amount;
	});

	totalAllocation = parseFloat(totalAllocation.toFixed(2));

	let cgstPercent = parseFloat('{{ $invoice->cgst_percent }}') || 0;
	let sgstPercent = parseFloat('{{ $invoice->sgst_percent }}') || 0;
	let igstPercent = parseFloat('{{ $invoice->igst_percent }}') || 0;

	let gstPercent = igstPercent > 0 ? igstPercent : (cgstPercent + sgstPercent);

	let taxableAmount = 0;
	let cgstAmount = 0;
	let sgstAmount = 0;
	let igstAmount = 0;

	if(gstPercent > 0)
	{
		taxableAmount = totalAllocation / (1 + (gstPercent / 100));

		if(igstPercent > 0)
		{
			igstAmount = totalAllocation - taxableAmount;
		}
		else
		{
			cgstAmount = taxableAmount * (cgstPercent / 100);
			sgstAmount = taxableAmount * (sgstPercent / 100);
		}
	}
	else
	{
		taxableAmount = totalAllocation;
	}

	taxableAmount = parseFloat(taxableAmount.toFixed(2));
	cgstAmount = parseFloat(cgstAmount.toFixed(2));
	sgstAmount = parseFloat(sgstAmount.toFixed(2));
	igstAmount = parseFloat(igstAmount.toFixed(2));

	$('#totalAllocation').val(totalAllocation.toFixed(2));
	$('#taxableAmount').val(taxableAmount.toFixed(2));
	$('#cgstAmount').val(cgstAmount.toFixed(2));
	$('#sgstAmount').val(sgstAmount.toFixed(2));
	$('#igstAmount').val(igstAmount.toFixed(2));
	$('#invoiceAmount').val(totalAllocation.toFixed(2));
}
</script>
<script src="{{ asset('panel/assets/js/datatablePaginationWithFilter.js') }}"></script>
@endsection