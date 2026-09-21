@extends('admin.admin_master')
@section('admin')
@php
$t=0;
@endphp

<div class="main-content">
	<div class="main-content-inner">
		<div class="page-content">
			<div class="ace-settings-container" id="ace-settings-container"></div>
			<form id="frmDepartmentPayment" enctype="multipart/form-data">
			 @csrf

<div class="card mt-3">
	<div class="card-body mt-10">
	<table class="table table-bordered width-700">
			<tr>
				<td class="myheadbg">Department</td>
				<td>
					<b>{{ $department->departmentname }}</b>
					<input type="hidden" name="department_id" value="{{ $departmentId }}">
				</td>
				<td class="myheadbg">Total Selected Receipt Balance</td>
				<td class="text-right">
					<b>
						<span class="format-indian" data-value="{{ number_format($selectedReceiptBalance,'2','.','') }}"></span>
					</b>
					<input type="hidden" name="selectedReceiptBalance" id="selectedReceiptBalance" value="{{$selectedReceiptBalance}}">
				</td>
			</tr>
	</table>
	</div>
</div>			 
<div class="card mt-3">

	<div class="card-header mt-10">
		<strong class="font-14">Selected Department Receipts</strong>
	</div>

	<div class="card-body mt-10">
		<table class="table table-bordered">
			<tr class="myheadbg font-bold">
				<td>Receipt No.</td>
				<td>Receipt Date</td>
				<td>Demand Note</td>
				<td style="text-align:right;">Gross Receipt</td>
				<td style="text-align:right;">Already Invoiced</td>
				<td style="text-align:right;">Available Balance</td>
				<td style="text-align:right;">Allocation Amount</td>
			</tr>
			<tbody>
				@foreach($receipts as $item)
				<tr>
					<td>{{ $item->receipt_no ?: '-' }}</td>
					<td>{{ $item->receipt_date ? date('d-m-Y',strtotime($item->receipt_date)) : '-' }}</td>
					<td>{{ $item->demand_note_no ?: '-' }}</td>
					<td style="text-align:right;" class="format-indian" data-value="{{ $item->gross_received_amount }}"></td>
					<td style="text-align:right;" class="format-indian" data-value="{{ $item->total_invoiced }}"></td>
					<td style="text-align:right;" class="format-indian" data-value="{{ $item->invoice_balance }}"></td>
					<td style="text-align:right;">
						<input type="text" name="allocation_amount[{{ $item->department_payment_id }}]" class="form-control text-right allocationAmount" data-payment-id="{{ $item->department_payment_id }}" data-balance="{{ $item->invoice_balance }}" value="{{ $mode == 'edit' ? number_format($item->current_allocation,2,'.','') : $item->invoice_balance }}" placeholder="0.00" autocomplete="off" readonly>
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
						<input type="text" id="totalAllocation" name="totalAllocation" class="form-control text-right" value="0.00" readonly>
					</th>
				</tr>
			</tfoot>
		</table>
	</div>


	
</div>			 


<div class="card mt-10">

	<div class="card-header">
		<strong class="font-14">Invoice Details</strong>
	</div>

	<div class="card-body mt-10">

		<div class="row">

			<div class="col-md-3">
				<label>Financial Year <span class="text-danger">*</span></label>
				<select name="financial_year" id="financial_year" class="form-control">
					@foreach($financialYears as $year)
						<option value="{{ $year }}">{{ $year }}</option>
					@endforeach
				</select>
			</div>

			<div class="col-md-3">
				<label>Invoice No. <span class="text-danger">*</span></label>
				<input type="text" name="invoice_no" id="invoice_no" class="form-control" placeholder="Invoice number" autocomplete="off">
			</div>

			<div class="col-md-3">
				<label>Invoice Date <span class="text-danger">*</span></label>
				<input type="text" name="invoice_date" id="invoice_date" class="form-control todays_dt_blank" placeholder="dd-mm-YYYY">
			</div>

			<div class="col-md-3">
				<label>GSTIN</label>
				<input type="text" name="gstin" id="gstin" class="form-control" placeholder="GSTIN" autocomplete="off">
			</div>

		</div>

		<div class="row mt-10">

			<div class="col-md-6">
				<label>Period From</label>
				<input type="text" name="period_from" id="period_from" class="form-control todays_dt_blank" placeholder="dd-mm-YYYY" autocomplete="off" value="@if($from_date) {{ $from_date }} @endif">
			</div>

			<div class="col-md-6">
				<label>Period To</label>
				<input type="text" name="period_to" id="period_to" class="form-control todays_dt_blank" placeholder="dd-mm-YYYY" autocomplete="off" value="@if($to_date) {{ $to_date }} @endif">
			</div>

		</div>

		<div class="row mt-10">

			<div class="col-md-12">
				<label>Particulars</label>
				<textarea name="particulars" id="particulars" class="form-control" placeholder="Particular.." rows="3"></textarea>
			</div>

		</div>

	</div>

</div>

<div class="card mt-10">

	<div class="card-header">
		<strong class="font-14">Invoice Amount & Tax Details</strong>
	</div>

	<div class="card-body mt-10">

		<div class="row">

			<div class="col-md-2">
				<label>Total</label>
				<input type="text" id="invoiceAmount" name="invoiceAmount" class="form-control text-right" value="0.00" readonly placeholder="0.00">
			</div>
			<div class="col-md-2">
				<label>GST Rate</label>
				<input type="text" id="gstRate" class="form-control text-right" value="{{ number_format($gstRate,2,'.','') }}" readonly>
			</div>

			<div class="col-md-2">
				<label>Taxable Amount</label>
				<input type="text" id="taxableAmount" name="taxable_amount" class="form-control text-right" value="0.00" readonly>
			</div>

			<div class="col-md-2">
				<label>CGST ({{ number_format($cgstRate,2,'.','') }}%)</label>
				<input type="text" id="cgstAmount" name="cgst_amount" class="form-control text-right" value="0.00" readonly>
			</div>
			<div class="col-md-2">
				<label>SGST ({{ number_format($sgstRate,2,'.','') }}%)</label>
				<input type="text" id="sgstAmount" name="sgst_amount" class="form-control text-right" value="0.00" readonly>
			</div>
			<div class="col-md-2">
				<label>IGST ({{ number_format($igstRate,2,'.','') }}%)</label>
				<input type="text" id="igstAmount" name="igst_amount" class="form-control text-right" value="0.00" readonly>
			</div>

		</div>
		<div class="row mt-10">
			<div class="col-md-12 text-right">
				<button type="button" id="btnSaveInvoice" class="btn btn-info gridbtn mt-25 width-130">
					<i class="fa fa-save"></i> Create Invoice
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
<script>
$(document).ready(function () {
    document.querySelectorAll('.format-indian').forEach(function (el) {
        el.style.fontWeight = 'bold';
        el.innerText = formatIndianNumber(el.dataset.value);
    });
});
calculateAllocation();
$(document).on('input change','.allocationAmount',function()
{
	let balance = parseFloat($(this).attr('data-balance')) || 0;
	let amount = parseFloat($(this).val()) || 0;

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

	calculateAllocation();
});

function calculateAllocation()
{
	let total = 0;

	$('.allocationAmount').each(function()
	{
		let amount = parseFloat($(this).val()) || 0;

		total += amount;
	});

	total = parseFloat(total.toFixed(2));

	$('#totalAllocation').val(total.toFixed(2));
	$('#invoiceAmount').val(total.toFixed(2));

	calculateGST(total);
}
function calculateGST(invoiceAmount)
{
	let gstRate = parseFloat($('#gstRate').val()) || 0;
	let cgstRate = {{ (float)$cgstRate }};
	let sgstRate = {{ (float)$sgstRate }};
	let igstRate = {{ (float)$igstRate }};

	let taxableAmount = 0;
	let cgstAmount = 0;
	let sgstAmount = 0;
	let igstAmount = 0;

	if(gstRate > 0)
	{
		taxableAmount = invoiceAmount / (1 + (gstRate / 100));
		taxableAmount = parseFloat(taxableAmount.toFixed(2));

		if(igstRate > 0)
		{
			igstAmount = invoiceAmount - taxableAmount;
			igstAmount = parseFloat(igstAmount.toFixed(2));
		}
		else
		{
			cgstAmount = taxableAmount * cgstRate / 100;
			sgstAmount = taxableAmount * sgstRate / 100;

			cgstAmount = parseFloat(cgstAmount.toFixed(2));
			sgstAmount = parseFloat(sgstAmount.toFixed(2));

			let difference = invoiceAmount - taxableAmount - cgstAmount - sgstAmount;

			if(Math.abs(difference) > 0)
			{
				sgstAmount = parseFloat((sgstAmount + difference).toFixed(2));
			}
		}
	}
	else
	{
		taxableAmount = invoiceAmount;
	}

	$('#taxableAmount').val(taxableAmount.toFixed(2));
	$('#cgstAmount').val(cgstAmount.toFixed(2));
	$('#sgstAmount').val(sgstAmount.toFixed(2));
	$('#igstAmount').val(igstAmount.toFixed(2));
}

$(document).on('click', '#btnSaveInvoice', function () {

    if (!validateAllocation()) {
        return false;
    }

    let totalAllocation = parseFloat($('#totalAllocation').val()) || 0;

    if (totalAllocation <= 0) {
        bootbox.alert('Please enter allocation amount for at least one receipt.');
        return false;
    }

    bootbox.confirm({
        message: 'Are you sure you want to create this Department Invoice?',
        buttons: {
            confirm: {
                label: '<i class="fa fa-check"></i> Yes, Create Invoice',
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

            let form = document.getElementById('frmDepartmentPayment');
            let formData = new FormData(form);

            $('#btnSaveInvoice')
                .prop('disabled', true)
                .html('<i class="fa fa-spinner fa-spin"></i> Saving...');

            $.ajax({
                url: "{{ route('finance.departmentinvoice.store') }}",
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

                        $('#btnSaveInvoice')
                            .prop('disabled', false)
                            .html('<i class="fa fa-save"></i> Create Invoice');
                    }
                },

                error: function (xhr) {

                    let message = 'Unable to create Department Invoice.';

                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        message = xhr.responseJSON.message;
                    }

                    if (xhr.responseJSON && xhr.responseJSON.errors) {

                        let errors = xhr.responseJSON.errors;
                        message = '';

                        $.each(errors, function (field, error) {
                            message += error[0] + '<br>';
                        });
                    }

                    bootbox.alert(message);

                    $('#btnSaveInvoice')
                        .prop('disabled', false)
                        .html('<i class="fa fa-save"></i> Create Invoice');
                }
            });
        }
    });

    return false;
});
function validateAllocation()
{
	let selectedBalance = parseFloat($('#selectedReceiptBalance').val()) || 0;
	let totalAllocation = parseFloat($('#totalAllocation').val()) || 0;

	if(totalAllocation <= 0)
	{
		bootbox.alert('Please enter allocation amount for at least one receipt.');

		return false;
	}

	if(totalAllocation > selectedBalance)
	{
		bootbox.alert('Total Allocation cannot be greater than Total Selected Receipt Balance.');

		return false;
	}

	return true;
}
</script>
<script src="{{ asset('panel/assets/js/datatablePaginationWithFilter.js') }}"></script>
@endsection