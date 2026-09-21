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
			@if(Session::has('success'))
			
			
				<div class="alert alert-block alert-success" style="background-color:white!important;">
					<button type="button" class="close" data-dismiss="alert">
						<i class="ace-icon fa fa-times"></i>
					</button>
					<i class="fa fa-check-circle"></i> {{ Session::get('success') }}
				</div>
			
			
			@endif

			<div class="card mt-3">
				<div class="card-header">
					<h4 class="font-16 font-bold">Department Invoice Details</h4>
				</div>
				<div class="card-body mt-10">
					<table class="table table-bordered">
						<tr>
							<td class="myheadbg">Invoice No.</td>
							<td><b>{{ $invoice->invoice_no ?: '-' }}</b></td>
							<td class="myheadbg">Invoice Date</td>
							<td>{{ $invoice->invoice_date ? date('d-m-Y',strtotime($invoice->invoice_date)) : '-' }}</td>
						</tr>
						<tr>
							<td class="myheadbg">Department</td>
							<td><b>{{ $invoice->departmentname ?: '-' }}</b></td>
							<td class="myheadbg">Financial Year</td>
							<td>{{ $invoice->financial_year ?: '-' }}</td>
						</tr>
						<tr>
							<td class="myheadbg">GSTIN</td>
							<td>{{ $invoice->gstin ?: '-' }}</td>
							<td class="myheadbg">Tax</td>
							<td>{{ $invoice->tax_name ?: '-' }}</td>
						</tr>
						<tr>
							<td class="myheadbg">Period From</td>
							<td>{{ $invoice->period_from ? date('d-m-Y',strtotime($invoice->period_from)) : '-' }}</td>
							<td class="myheadbg">Period To</td>
							<td>{{ $invoice->period_to ? date('d-m-Y',strtotime($invoice->period_to)) : '-' }}</td>
						</tr>
					</table>
				</div>
			</div>
			<div class="card mt-10">
				<div class="card-header">
					<h4 class="font-16 font-bold">Invoice Amount</h4>
				</div>
				<div class="card-body mt-10">
					<table class="table table-bordered">
						<tr>
							<td class="myheadbg">Taxable Amount</td>
							<td style="text-align:right;">
								<i class="fa fa-inr"></i> <span class="format-indian" data-value="{{ $invoice->taxable_amount }}"></span>
							</td>
						</tr>
						<tr>
							<td class="myheadbg">CGST ({{ number_format($invoice->cgst_percent,2,'.','') }}%)</td>
							<td style="text-align:right;">
								<i class="fa fa-inr"></i> <span class="format-indian" data-value="{{ $invoice->cgst_amount }}"></span>
							</td>
						</tr>
						<tr>
							<td class="myheadbg">SGST ({{ number_format($invoice->sgst_percent,2,'.','') }}%)</td>
							<td style="text-align:right;">
								<i class="fa fa-inr"></i> <span class="format-indian" data-value="{{ $invoice->sgst_amount }}"></span>
							</td>
						</tr>
						<tr>
							<td class="myheadbg">IGST ({{ number_format($invoice->igst_percent,2,'.','') }}%)</td>
							<td style="text-align:right;">
								<i class="fa fa-inr"></i> <span class="format-indian" data-value="{{ $invoice->igst_amount }}"></span>
							</td>
						</tr>
						<tr>
							<td class="myheadbg"><b>Invoice Amount</b></td>
							<td style="text-align:right;">
								<i class="fa fa-inr"></i> <b><span class="format-indian" data-value="{{ $invoice->invoice_amount }}"></span></b>
							</td>
						</tr>
					</table>
				</div>
			</div>
			<div class="card mt-10">
				<div class="card-header">
					<h4 class="font-16 font-bold">Receipt Allocation Details</h4>
				</div>
				<div class="card-body mt-10">
					<table class="table table-bordered">
						<thead>
							<tr class="myheadbg font-bold">
								<td>Receipt No.</td>
								<td>Receipt Date</td>
								<td>Demand Note</td>
								<td style="text-align:right;">Gross Receipt</td>
								<td style="text-align:right;">Invoice Allocation</td>
							</tr>
						</thead>
						<tbody>
							@foreach($allocations as $item)
							<tr>
								<td>{{ $item->receipt_no ?: '-' }}</td>
								<td>{{ $item->receipt_date ? date('d-m-Y',strtotime($item->receipt_date)) : '-' }}</td>
								<td>{{ $item->demand_note_no ?: '-' }}</td>
								<td style="text-align:right;">
									<span class="format-indian" data-value="{{ $item->gross_received_amount }}"></span>
								</td>
								<td style="text-align:right;">
									<span class="format-indian" data-value="{{ $item->allocated_amount }}"></span>
								</td>
							</tr>
							@endforeach
						</tbody>
						<tfoot>
							<tr>
								<th colspan="4" style="text-align:right;">
									Total Allocation
								</th>
								<th style="text-align:right;">
									<span class="format-indian" data-value="{{ $totalAllocated }}"></span>
								</th>
							</tr>
						</tfoot>
					</table>
				</div>
			</div>
			<div class="card mt-10">
				<div class="card-header">
					<h4 class="font-16 font-bold">Particulars / Remarks</h4>
				</div>
				<div class="card-body mt-10">
					<table class="table table-bordered">
						<tr>
							<td class="myheadbg">Particulars</td>
							<td>{!! nl2br(e($invoice->particulars ?: '-')) !!}</td>
						</tr>
						<tr>
							<td class="myheadbg">Remarks</td>
							<td>{!! nl2br(e($invoice->remarks ?: '-')) !!}</td>
						</tr>
					</table>
				</div>
			</div>
			@if($invoice->status == 'Draft' || $invoice->status == 'Posted')
			<div class="card mt-10">
				<div class="card-body mt-10 text-right">
				@if($invoice->status == 'Draft')
				<a href="{{ route('finance.departmentinvoice.edit',Crypt::encrypt($item->department_invoice_id)) }}" title="Edit">
					<button type="button" id="btnPostInvoice" class="btn btn-info gridbtn width-100">
						<i class="fa fa-pencil"></i> Edit Invoice
					</button>
				</a>
				@endif
				@if($invoice->status == 'Draft')
					<button type="button" id="btnPostInvoice" class="btn btn-info gridbtn width-100">
						<i class="fa fa-check"></i> Post Invoice
					</button>
				@endif
				@if($invoice->status == 'Posted')
					<button type="button" id="btnCancelInvoice" class="btn btn-info gridbtn width-100">
						<i class="fa fa-times"></i> Cancel Invoice
					</button>
				@endif				
				</div>
			</div>
			@endif
			</form>
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

$(document).on('click','#btnPostInvoice',function()
{
	let invoiceId = "{{ $invoice->department_invoice_id }}";

	bootbox.confirm({
		message: '<b>Are you sure you want to post this Department Invoice?</b><br><br> Once posted, the invoice cannot be edited.',
		buttons: {
			confirm: {
				label: 'Yes, Post Invoice',
				className: 'btn-success'
			},
			cancel: {
				label: 'Cancel',
				className: 'btn-secondary'
			}
		},
		callback: function(result)
		{
			if(!result)
			{
				return;
			}

			$('#btnPostInvoice')
				.prop('disabled',true)
				.html('<i class="fa fa-spinner fa-spin"></i> Posting...');

			$.ajax({
				url: "{{ route('finance.departmentinvoice.post') }}",
				type: "POST",
				data: {
					_token: "{{ csrf_token() }}",
					department_invoice_id: invoiceId
				},
				success: function(response)
				{
					if(response.status == 1)
					{
						bootbox.alert(response.message,function()
						{
							window.location.href = response.redirect;
						});
					}
					else
					{
						bootbox.alert(response.message);

						$('#btnPostInvoice')
							.prop('disabled',false)
							.html('<i class="fa fa-check"></i> Post Invoice');
					}
				},
				error: function(xhr)
				{
					let message = 'Unable to post Department Invoice.';

					if(xhr.responseJSON && xhr.responseJSON.message)
					{
						message = xhr.responseJSON.message;
					}

					bootbox.alert(message);

					$('#btnPostInvoice')
						.prop('disabled',false)
						.html('<i class="fa fa-check"></i> Post Invoice');
				}
			});
		}
	});
});

$(document).on('click','#btnCancelInvoice',function()
{
	let invoiceId = "{{ $invoice->department_invoice_id }}";

	bootbox.prompt({
		title: 'Cancel Department Invoice',
		inputType: 'textarea',
		message: 'Please enter the reason for cancelling this invoice.',
		placeholder: 'Cancellation reason...',
		required: true,
		buttons: {
			cancel: {
				label: 'Close',
				className: 'btn-secondary'
			},
			confirm: {
				label: 'Cancel Invoice',
				className: 'btn-danger'
			}
		},
		callback: function(result)
		{
			if(result === null)
			{
				return;
			}

			result = $.trim(result);

			if(result == '')
			{
				bootbox.alert('Cancellation remarks are mandatory.');
				return false;
			}

			bootbox.confirm({
				message: 'Are you sure you want to cancel this Department Invoice? This action cannot be undone.',
				buttons: {
					confirm: {
						label: 'Yes, Cancel Invoice',
						className: 'btn-danger'
					},
					cancel: {
						label: 'No',
						className: 'btn-secondary'
					}
				},
				callback: function(confirmResult)
				{
					if(!confirmResult)
					{
						return;
					}

					$('#btnCancelInvoice')
						.prop('disabled',true)
						.html('<i class="fa fa-spinner fa-spin"></i> Cancelling...');

					$.ajax({
						url: "{{ route('finance.departmentinvoice.cancel') }}",
						type: "POST",
						data: {
							_token: "{{ csrf_token() }}",
							department_invoice_id: invoiceId,
							cancellation_remarks: result
						},
						success: function(response)
						{
							if(response.status == 1)
							{
								bootbox.alert(response.message,function()
								{
									window.location.href = response.redirect;
								});
							}
							else
							{
								bootbox.alert(response.message);

								$('#btnCancelInvoice')
									.prop('disabled',false)
									.html('<i class="fa fa-times"></i> Cancel Invoice');
							}
						},
						error: function(xhr)
						{
							let message = 'Unable to cancel Department Invoice.';

							if(xhr.responseJSON && xhr.responseJSON.message)
							{
								message = xhr.responseJSON.message;
							}

							bootbox.alert(message);

							$('#btnCancelInvoice')
								.prop('disabled',false)
								.html('<i class="fa fa-times"></i> Cancel Invoice');
						}
					});
				}
			});
		}
	});
});
</script>
<script src="{{ asset('panel/assets/js/datatablePaginationWithFilter.js') }}"></script>
@endsection