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

			<div class="card mt-5">
				<div class="card-header">
					<strong class="font-16">Demand Note Information</strong>
				</div>
				<div class="card-body mt-10">
					<div class="row">
						<div class="col-md-3 mb-3">
							<label>Demand Note No.</label>
							<input type="text" name="demand_note_no" class="form-control" placeholder="Demand Note Number" value="{{ $record->demand_note_no }}" readonly>

							<input type="hidden" name="department_payment_id" value="{{ $departmentReceipt->department_payment_id }}">
							<input type="hidden" name="demand_note_id" value="{{ $record->demand_note_id }}">
							<input type="hidden" name="request_id" value="{{ $record->request_id }}">
							<input type="hidden" name="order_id" value="{{ $record->order_id }}">
							<input type="hidden" name="department_id" value="{{ $record->department_id }}">
							<input type="hidden" name="project_id" value="{{ $record->project_id }}">
							<input type="hidden" name="vendor_id" value="{{ $record->vendor_id }}">
						</div>
						<div class="col-md-3 mb-3">
							<label>Demand Note Date</label>
							<input type="text" name="demand_note_date" class="form-control" value="{{ date('d\-m\-Y',strtotime($record->demand_note_date)) }}" readonly required placeholder="dd-mm-YYYY">
						</div>
						<div class="col-md-3 mb-3">
							<label>Financial Year</label>
							<input type="text" name="fina_year" class="form-control" value="{{ $record->financial_year }}" readonly required>
						</div>
						<div class="col-md-3 mb-3">
							<label>EoI Number</label>
							<input type="text" name="eoi_number" class="form-control" value="{{ $record->eoinumber }}" placeholder="EoI Number" readonly>
						</div>
					</div>
					<div class="row mt-10">
						<div class="col-md-6 mb-3">
							<label>Department</label>
							<input type="text" class="form-control" value="{{ $record->departmentname}}" placeholder="Department Name" readonly>
						</div>
						<div class="col-md-6 mb-3">
							<label>Demand Note Duration</label>
							@if($record->from_date && $record->to_date)
							<input type="text" name="demand_duration" class="form-control" value="{{date('d\-m\-Y',strtotime($record->from_date))}} to {{date('d\-m\-Y',strtotime($record->to_date))}}" readonly>
							@else
							-
							@endif
						</div>
					</div>
					<div class="row mt-10">
						<div class="col-md-12 mb-3">
							<label>Project</label>
							<input type="text" name="project_name" class="form-control" value="{{ $record->engagementname }}" placeholder="Project Name" readonly>
						</div>
					</div>
				</div>
			</div>

			<div class="card mt-5">
				<div class="card-header">
					<strong class="font-16">Receipt Summary</strong>
				</div>
				<div class="card-body mt-10">
					<div class="row">
						<div class="col-md-3 mb-3">
							<label>Demand Amount</label>
							<input type="text" name="total_amount" id="total_amount" class="form-control" value="{{ $record->total_amount }}" readonly>
						</div>
						<div class="col-md-3 mb-3">
							<label>Total Received</label>
							<input type="text" name="total_received" id="total_received" class="form-control" value="{{ $record->total_received }}" readonly>
						</div>
						<div class="col-md-3 mb-3">
							<label>Balance Amount</label>
							<input type="text" name="total_balance" id="total_balance" class="form-control" value="{{ $record->total_amount-$record->total_received }}" readonly>
						</div>
					</div>
				</div>
			</div>

			<div class="card mt-5">
				<div class="card-header">
					<strong class="font-16">Receipt Details</strong>
				</div>
				<div class="card-body mt-10">
					<div class="row">
						<div class="col-md-3 mb-3">
							<label>Receipt Number</label>
							<input type="text" name="receipt_no" id="receipt_no" class="form-control" value="{{ $mode=='create' ? $receiptNo : $departmentReceipt->receipt_no }}" readonly placeholder="Receipt Number">
						</div>
						<div class="col-md-3 mb-3">
							<label>Receipt Date</label>
							<input type="text" name="receipt_date" id="receipt_date" class="form-control todays_dt_blank" placeholder="dd-mm-YYYY" value="{{ date('d\-m\-Y',strtotime($departmentReceipt->receipt_date)) }}">
						</div>
						<div class="col-md-3 mb-3">
							<label>Financial Year</label>
							<select name="financial_year" id="financial_year" class="form-control" required>
								@foreach($financialYears as $year)
									<option value="{{$year}}" @if($departmentReceipt->financial_year==$year) selected @endif>{{$year}}</option>
								@endforeach
							</select>
						</div>
						<div class="col-md-3 mb-3">
							<label>Voucher Number</label>
							<input type="text" name="voucher_no" id="voucher_no" class="form-control" placeholder="Voucher Number" value="{{ $departmentReceipt->voucher_no }}" autocomplete="off">
						</div>
						<div class="col-md-3 mt-10 mb-3">
							<label>Voucher Date</label>
							<input type="text" name="voucher_date" id="voucher_date" class="form-control todays_dt_blank" placeholder="dd-mm-YYYY" value="{{ date('d\-m\-Y',strtotime($departmentReceipt->voucher_date)) }}" autocomplete="off">
						</div>
					</div>
				</div>
			</div>

			<div class="card mt-5">
				<div class="card-header">
					<strong class="font-16">Payment Details</strong>
				</div>
				<div class="card-body mt-10">
					<div class="row">
						<div class="col-md-3 mb-3">
							<label>Payment Mode</label>
							<select name="payment_mode_id" id="payment_mode_id" class="form-control" required>
								<option value="">--Payment Mode--</option>
								@foreach($paymentModes as $payMode)
									<option value="{{$payMode->payment_mode_id}}" @if($departmentReceipt->payment_mode_id==$payMode->payment_mode_id) selected @endif>{{$payMode->payment_mode}}</option>
								@endforeach
							</select>
						</div>
						<div class="col-md-3 mb-3">
							<label>Bank Account</label>
							<select name="bank_account_id" id="bank_account_id" class="form-control" required>
								<option value="">--Bank Name--</option>
								@foreach($bankAccounts as $bank)
									<option value="{{$bank->bank_account_id}}" @if($departmentReceipt->bank_account_id==$bank->bank_account_id) selected @endif>{{$bank->account_name}}</option>								
								@endforeach
							</select>
						</div>
						<div class="col-md-6 mb-3">
							<label>Transaction No (UTR/Cheque/DD)</label>
							<input type="text" name="transaction_no" id="transaction_no" class="form-control" placeholder="Transaction No (UTR/Cheque/DD)" value="{{ $departmentReceipt->transaction_no }}" autocomplete="off">
						</div>
						
					</div>
				</div>
			</div>

			<div class="card mt-5">
				<div class="card-header">
					<strong class="font-16">Amount Details</strong>
				</div>
				<div class="card-body mt-10">
					<div class="row">
						<div class="col-md-3 mb-3">
							<label>
								Gross Receipt Amount <span class="text-danger">*</span>
							</label>
							<input type="text" step="0.01" name="gross_received_amount" id="gross_received_amount" class="form-control numbers" 	value="{{ old('gross_received_amount',$departmentReceipt->gross_received_amount ?? '') }}" onkeyup="calculateNetReceipt()" required placeholder="Gross Receipt Amount" autocomplete="off">
						</div>
						<div class="col-md-3 mb-3">
							<label>Taxable Amount</label>
							<input type="text" class="form-control" name="taxable_amount" id="taxable_amount" placeholder="Taxable Amount" value="{{ old('taxable_amount',$departmentReceipt->taxable_amount ?? '') }}" readonly>
						</div>			
						<div class="col-md-3 mb-3">
							<label>GST Amount</label>
							<input type="text" class="form-control" name="gst_amount" id="gst_amount" placeholder="GST Amount" value="{{ old('gst_amount',$departmentReceipt->gst_amount ?? '') }}" readonly>
						</div>
						<div class="col-md-3 mb-3">
							<label>GST Rate</label>
							<input type="text" class="form-control" value="{{ $record->gst_rate }} %" readonly placeholder="GST Rate">
							<input type="hidden" id="gst_rate" value="{{ $record->gst_rate }}">
						</div>						
						<div class="col-md-3 mt-10 mb-3">
							<label>TDS</label>
							<select name="tds_tax_id" id="tds_tax_id" class="form-control" onchange="calculateNetReceipt()">
								<option value="">No TDS</option>
								@foreach($tdsTaxes as $tax)
									<option value="{{ $tax->tax_id }}" data-rate="{{ $tax->rate }}" {{ $departmentReceipt->tds_tax_id==$tax->tax_id ? 'selected' : '' }}>{{ $tax->tax_name }}</option>
								@endforeach
							</select>
						</div>
						<div class="col-md-3 mt-10 mb-3">
							<label>TDS Amount*</label>
							<input type="text" name="tds_amount" id="tds_amount" class="form-control" placeholder="TDS Amount" value="{{ old('tds_amount',$departmentReceipt->tds_amount ?? '') }}"	readonly>
						</div>
						<div class="col-md-3 mt-10 mb-3">
							<label>GST-TDS</label>
							<select name="gst_tds_tax_id" id="gst_tds_tax_id" class="form-control" onchange="calculateNetReceipt()">
								<option value="">No GST-TDS</option>
								@foreach($gstTdsTaxes as $tax)
									<option value="{{ $tax->tax_id }}" data-rate="{{ $tax->rate }}" {{ $departmentReceipt->gst_tds_tax_id==$tax->tax_id ? 'selected' : '' }}>{{ $tax->tax_name }}</option>
								@endforeach
							</select>
						</div>
						<div class="col-md-3 mt-10 mb-3">
							<label>GST-TDS Amount</label>
							<input type="text" name="gst_tds_amount" id="gst_tds_amount" class="form-control" placeholder="GST-TDS Amount" value="{{ old('gst_tds_amount',$departmentReceipt->gst_tds_amount ?? '') }}" readonly>
						</div>
					</div>
					<div class="row mt-10">
						<div class="col-md-3 mb-3">
							<label>Other Deduction</label>
							<input type="text" name="other_deduction" id="other_deduction" class="form-control" placeholder="Other Deduction" value="{{ old('other_deduction',$departmentReceipt->other_deduction ?? 0) }}" onkeyup="calculateNetReceipt()" autocomplete="off">
						</div>
						<div class="col-md-3 mb-3">
							<label>Net Received Amount</label>
							<input type="text" name="net_received_amount" id="net_received_amount" class="form-control" placeholder="Net Received Amount" value="{{ old('net_received_amount',$departmentReceipt->net_received_amount ?? '') }}" readonly>
						</div>
						
					</div>
				</div>
			</div>


			<div class="card mt-5">

				<div class="card-header">
					<strong class="font-16">Other Details</strong>
				</div>
				<div class="card-body mt-10">
					<div class="row mt-10">
						<div class="col-md-12 mb-3">
							<label>Remarks</label>
							<textarea name="remarks" class="form-control tinymce" rows="2" placeholder="Remark..">{{$departmentReceipt->remarks}}</textarea>
						</div>
					</div>
					<div class="row mt-10">
						<div class="col-md-6 mb-3">
							<label>Attachment</label>
							<input type="file" name="attachment" class="form-control" id="payment_attachment" accept="application/pdf">
						</div>
						<div class="col-md-2 mb-3">
							@if($mode!='create')
								@if($departmentReceipt->attachment)
									<a href="{{route('view.uploadedfile',Crypt::encrypt($departmentReceipt->attachment))}}" target="_blank" style="position:absolute; right:3px; outline:none;">
										<button type="button" class="btn btn-info gridbtn width-150 mt-10">
											<i class="fa fa-file-pdf-o"></i> View Uploaded File
										</button>
									</a>
								@endif
							@endif
						</div>
						<div class="col-md-4 mt-10 text-right">
							@if($mode=='create')
							<button type="button" class="btn btn-info gridbtn width-100" id="btnSaveDepartmentPayment">
								<i class="fa fa-save"></i> Submit
							</button>
							@else
							<button type="button" class="btn btn-info gridbtn width-100" id="btnUpdateDepartmentPayment">
								<i class="fa fa-refresh"></i> Update
							</button>
							@endif
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

@if($mode!='create')
setTimeout(function() { calculateGST(); },2000);
@endif

$('#payment_attachment').ace_file_input({
	no_file: 'No File ...',
	btn_choose: 'Attachment (Optional)',
	btn_change: 'Change',
	btn_name: 'btnname',
	thumbnail: false //| true | large
})

tinymce.init({
    selector: 'textarea.tinymce',
    promotion: false,
    branding: false,
    plugins: 'autoresize code advlist autolink lists charmap preview table searchreplace save',
    toolbar_mode: 'floating',
    toolbar: 'undo redo | formatselect | bold italic underline strikethrough | alignleft aligncenter alignright alignjustify | bullist numlist | table',
    menubar: false,
    statusbar: false,
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

$("#gross_received_amount,#other_deduction").on("keyup change", function(){
	calculateNetReceipt();
});

$("#tds_tax_id,#gst_tds_tax_id").on("change", function(){
	calculateNetReceipt();
});

function calculateNetReceipt()
{
	let grossAmount 	= 	parseFloat($("#gross_received_amount").val()) || 0;
	let gstRate 		= 	parseFloat($("#gst_rate").val()) || 0;
	let otherDeduction 	= 	parseFloat($("#other_deduction").val()) || 0;

	let taxableAmount = 0;
	let gstAmount = 0;

	if(gstRate > 0)
	{
		taxableAmount	=	grossAmount / (1 + (gstRate / 100));
		gstAmount 		= 	grossAmount - taxableAmount;
	}
	else
	{
		taxableAmount 	=	grossAmount;
		gstAmount 		= 	0;
	}

	let tdsRate 	= 	parseFloat($("#tds_tax_id option:selected").data("rate")) || 0;
	let tdsAmount 	= 	(taxableAmount * tdsRate) / 100;

	let gstTdsRate 			= 	parseFloat( $("#gst_tds_tax_id option:selected").data("rate") ) || 0;
	let gstTdsAmount		= 	(taxableAmount * gstTdsRate) / 100;
	let netReceivedAmount 	=	grossAmount - tdsAmount - gstTdsAmount - otherDeduction;

	if(netReceivedAmount < 0)
	{
		netReceivedAmount = 0;
	}

	$("#taxable_amount").val(taxableAmount.toFixed(2));
	$("#gst_amount").val(gstAmount.toFixed(2));
	$("#tds_amount").val(tdsAmount.toFixed(2));
	$("#gst_tds_amount").val(gstTdsAmount.toFixed(2));
	$("#net_received_amount").val(netReceivedAmount.toFixed(2));
}

$("#btnSaveDepartmentPayment").click(function () {

    bootbox.confirm("Are you sure you want to save this Department Payment Receipt?", function(result){

        if(result)
        {
            saveDepartmentReceipt();
        }

    });

});

$("#btnUpdateDepartmentPayment").click(function () {

    bootbox.confirm("Are you sure you want to update this Department Payment Receipt?", function(result){

        if(result)
        {
            saveDepartmentReceipt();
        }

    });

});

function saveDepartmentReceipt()
{
	calculateNetReceipt();
	let balance 	= 	parseFloat($("input[name='total_balance']").val()) || 0;
	let netReceived =	parseFloat($("#net_received_amount").val()) || 0;
	
    if(netReceived > balance)
    {
        bootbox.alert("Net Received Amount cannot be greater than Balance Amount.");
        return false;
    }	
	
	let url = "";
	@if($mode=='create')
		url="{{ route('finance.departmentreceipt.store') }}";
	@else
		url = "{{ route('finance.departmentreceipt.update', ['id' => Crypt::encrypt($departmentReceipt->department_payment_id)]) }}";
	@endif
	

	tinymce.triggerSave();
    var formData = new FormData($("#frmDepartmentPayment")[0]);

    $.ajax({

        url: url,
        type: "POST",
        data: formData,
        processData: false,
        contentType: false,
        headers:{
            'X-CSRF-TOKEN':'{{ csrf_token() }}'
        },
        beforeSend:function(){
            $("#btnSaveDepartmentPayment").prop("disabled",true);
			$("#btnUpdateDepartmentPayment").prop("disabled",true);
        },
        success:function(response)
        {
            $("#btnSaveDepartmentPayment").prop("disabled",false);
			$("#btnUpdateDepartmentPayment").prop("disabled",false);
            if(response.status==1)
            {
                bootbox.alert(response.message,function(){
                    window.location.href=response.redirect;
                });
            }
            else
            {
                bootbox.alert(response.message);
            }
        },
		error: function(xhr)
		{
			$("#btnSaveDepartmentPayment").prop("disabled", false);
			$("#btnUpdateDepartmentPayment").prop("disabled", false);

			if(xhr.status == 422)
			{
				if(xhr.responseJSON.errors)
				{
					var errors = "";

					$.each(xhr.responseJSON.errors, function(key, value)
					{
						errors += "<div>" + value[0] + "</div>";
					});

					bootbox.alert(errors);
				}
				else if(xhr.responseJSON.message)
				{
					bootbox.alert(xhr.responseJSON.message);
				}
				else
				{
					bootbox.alert("Something went wrong. Please check your input.");
				}
			}
			else
			{
				bootbox.alert("Something went wrong. Please try again.");
			}
		}
    });

}



</script>
@endsection