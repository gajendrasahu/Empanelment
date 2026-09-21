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
			<div class="card">
				<div class="card-header">
					<strong class="font-16">Department Payment Details</strong>
				</div>
				<div class="card-body mt-10">
					<div class="row">
						<div class="col-md-3">
							<label>Receipt No.</label>
							<div class="bg-light">
								{{ $payment->receipt_no }}
							</div>
						</div>
						<div class="col-md-3">
							<label>Receipt Date</label>
							<div class="bg-light">
								{{ $payment->receipt_date ? date('d-m-Y',strtotime($payment->receipt_date)) : '-' }}
							</div>
						</div>
						<div class="col-md-3">
							<label>Financial Year</label>
							<div class="bg-light">
								{{ $payment->financial_year }}
							</div>
						</div>
						<div class="col-md-3">
							<label>Status</label>
							<div class="bg-light">
								{{ $payment->status }}
							</div>
						</div>
					</div>
				</div>
			</div>		
			<div class="card mt-3">
				<div class="card-header">
					<strong class="font-16">Reference Details</strong>
				</div>
				<div class="card-body mt-10">
					<div class="row">
						<div class="col-md-6">
							<label>Department</label>
							<div class="bg-light">
								{{ $payment->departmentname ?: '-' }}
							</div>
						</div>
						<div class="col-md-3">
							<label>Demand Note No.</label>
							<div class="bg-light">
								{{ $payment->demand_note_no ?: '-' }}
							</div>
						</div>
						<div class="col-md-3">
							<label>EOI Number</label>
							<div class="bg-light">
								{{ $payment->eoinumber ?: '-' }}
							</div>
						</div>
					</div>
					<div class="row mt-20">
						<div class="col-md-12">
							<label>Project</label>
							<div class="bg-light">
								{{ $payment->engagementname ?: '-' }}
							</div>
						</div>
					</div>
				</div>
			</div>		
			
			<div class="card mt-3">
				<div class="card-header">
					<strong class="font-16">Receipt & Tax Details</strong>
				</div>
				<div class="card-body mt-10">
					<table class="table table-bordered">
						<tr>
							<th width="30%">Gross Receipt Amount</th>
							<td class="text-right">
								₹ {{ number_format($payment->gross_received_amount,2) }}
							</td>
						</tr>
						<tr>
							<th>Taxable Amount</th>
							<td class="text-right">
								₹ {{ number_format($payment->taxable_amount,2) }}
							</td>
						</tr>
						<tr>
							<th>GST Rate</th>
							<td class="text-right">
								{{ number_format($payment->gst_rate,2) }}%
							</td>
						</tr>
						<tr>
							<th>GST Amount</th>
							<td class="text-right">
								₹ {{ number_format($payment->gst_amount,2) }}
							</td>
						</tr>
						<tr>
							<th>
								TDS
								@if($tdsTax)
									({{ $tdsTax->rate }}%)
								@endif
							</th>
							<td class="text-right">
								₹ {{ number_format($payment->tds_amount,2) }}
							</td>
						</tr>
						<tr>
							<th>
								GST-TDS
								@if($gstTdsTax)
									({{ $gstTdsTax->rate }}%)
								@endif
							</th>
							<td class="text-right">
								₹ {{ number_format($payment->gst_tds_amount,2) }}
							</td>
						</tr>
						<tr>
							<th>Other Deduction</th>
							<td class="text-right">
								₹ {{ number_format($payment->other_deduction,2) }}
							</td>
						</tr>
						<tr>
							<th><strong>Net Received Amount</strong></th>
							<td class="text-right">
								<strong>
									₹ {{ number_format($payment->net_received_amount,2) }}
								</strong>
							</td>
						</tr>
					</table>
				</div>
			</div>			
			<div class="card mt-10">
				<div class="card-header">
					<strong class="font-16">Bank & Voucher Details</strong>
				</div>
				<div class="card-body">
					<div class="row mt-10">
						<div class="col-md-3">
							<label>Payment Mode</label>
							<div class="bg-light">
								{{ $payment->payment_mode ?: '-' }}
							</div>
						</div>
						<div class="col-md-3">
							<label>Bank Account</label>
							<div class="bg-light">
								{{ $payment->bank_name ?: '-' }}
								@if($payment->account_name)
									- {{ $payment->account_name }}
								@endif
							</div>
						</div>
						<div class="col-md-3">
							<label>Transaction No.</label>
							<div class="bg-light">
								{{ $payment->transaction_no ?: '-' }}
							</div>
						</div>
						<div class="col-md-3">
							<label>Voucher No.</label>
							<div class="bg-light">
								{{ $payment->voucher_no ?: '-' }}
							</div>
						</div>
					</div>
					<div class="row mt-20">
						<div class="col-md-3">
							<label>Voucher Date</label>
							<div class="bg-light">
								{{ $payment->voucher_date ? date('d-m-Y',strtotime($payment->voucher_date)) : '-' }}
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="card mt-10">
				<div class="card-header">
					<strong class="font-16">Ledger Details</strong>
				</div>
				<div class="card-body mt-10">
					<table class="table table-bordered">
						<thead>
							<tr>
								<th>Date</th>
								<th>Type</th>
								<th class="text-right">Debit</th>
								<th class="text-right">Credit</th>
								<th>Narration</th>
							</tr>
						</thead>
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
			<div class="card mt-10">
				<div class="card-header">
					<strong class="font-16">Additional Details</strong>
				</div>
				<div class="card-body mt-10">
					<div class="row">
						<div class="col-md-6">
							<label>Remarks</label>
							<div class="bg-light" style="height:auto; min-height:60px;">
								{{ $payment->remarks ?: '-' }}
							</div>
						</div>
						<div class="col-md-6">
							<label>Attachment</label>
							<div>
								@if($payment->attachment)
									<a href="{{ asset('storage/'.$payment->attachment) }}"
									   target="_blank"
									   class="btn btn-sm btn-info">

										<i class="fa fa-file"></i>
										View Attachment

									</a>
								@else
									<span class="text-muted">
										No attachment
									</span>
								@endif
							</div>
						</div>
					</div>
				</div>
			</div>
			<!--
			<div class="card mt-10">
				<div class="card-header">
					<strong class="font-16">Activity History</strong>
				</div>
				<div class="card-body mt-10">
					<table class="table table-bordered">
						<thead>
							<tr>
								<th>Date</th>
								<th>Action</th>
								<th>Field</th>
								<th>Old Value</th>
								<th>New Value</th>
								<th>Description</th>
							</tr>
						</thead>
						<tbody>
							@forelse($activityLogs as $log)
								<tr>
									<td>
										{{ date('d-m-Y H:i:s',strtotime($log->action_date)) }}
									</td>
									<td>
										{{ $log->action }}
									</td>
									<td>
										{{ $log->field_name }}
									</td>
									<td>
										{{ $log->old_value ?: '-' }}
									</td>
									<td>
										{{ $log->new_value ?: '-' }}
									</td>
									<td>
										{{ $log->action_description ?: '-' }}
									</td>
								</tr>
							@empty
								<tr>
									<td colspan="6" class="text-center">
										No activity history found.
									</td>
								</tr>
							@endforelse
						</tbody>
					</table>
				</div>
			</div>
			-->
			</form>
		</div>
	</div>
</div>
<script src="{{ asset('panel/assets/js/jquery-2.1.4.min.js') }}"></script>
<script src="{{ asset('panel/assets/js/ace-elements.min.js') }}"></script>
<script src="{{ asset('panel/assets/js/ace.min.js') }}"></script>
<script src="{{ asset('panel/assets/tinymce/tinymce.min.js') }}"></script>

<script>

tinymce.init({
    selector:'textarea.tinymce',
    promotion:false,
    branding:false,
    plugins:'autoresize code advlist autolink lists charmap preview table searchreplace save',
    toolbar_mode:'floating',
    toolbar:'undo redo | formatselect | bold italic underline strikethrough | alignleft aligncenter alignright alignjustify | bullist numlist | table',
    menubar:false,
    statusbar:false,
    min_height: 60,
    autoresize_min_height: 60,
    autoresize_max_height: 490,
    autoresize_bottom_margin: 10,
    resize: true,
    setup: function (editor) {
        editor.on('init', function () {
            editor.getBody().style.fontSize = '14px';
            editor.getBody().style.lineHeight = '1.5';
        });
    }
});

</script>
@endsection