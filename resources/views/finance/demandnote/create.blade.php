@extends('admin.admin_master')
@section('admin')
@php
$t=0;

if ($record->project_name!='') {
    $project_name = $record->project_name;
} elseif ($record->engagementname) {
    $project_name = $record->engagementname;
} else {
    $project_name = $record->projecttitle;
}

@endphp

<div class="main-content">
	<div class="main-content-inner">
		<div class="page-content">
			<div class="ace-settings-container" id="ace-settings-container"></div>
			<form id="frmDemandNote" enctype="multipart/form-data">
			 @csrf
			<div class="card">
				<div class="card-header">
					<strong class="font-16">Project Information</strong>
				</div>
				<div class="card-body mt-10">
					<div class="row">
						<div class="col-md-12 mb-3">
							<label>Project Name</label>
							<input type="text" class="form-control" value="{{ $project_name }}" readonly placeholder="Project name">
							<input type="hidden" name="request_id" id="request_id" value="{{ $record->requestid }}">
							<input type="hidden" name="department_id" value="{{ $record->departmentid }}">
							<input type="hidden" name="project_id" value="{{ $record->projectid }}">
						</div>
					</div>
					<div class="row"><div class="col-sm-12">&nbsp;</div></div>
					<div class="row">
						<div class="col-md-4 mb-3">
							<label>EoI Number</label>
							<input type="text" class="form-control" value="{{ $record->eoinumber }}" readonly placeholder="EoI number">
						</div>
						<div class="col-md-2 mb-3">
							<label>EoI Date</label>
							<input type="text" class="form-control" value="@if($record->releasedate){{ date('d-m-Y',strtotime($record->releasedate)) }}@endif" readonly placeholder="dd-mm-YYYY">
						</div>
						<div class="col-md-6 mb-3">
							<label>Department</label>
							<input type="text" class="form-control" value="{{ $record->departmentname }}" readonly placeholder="Department / Project Manager Name">
						</div>
					</div>
					<div class="row"><div class="col-sm-12">&nbsp;</div></div>
				</div>
			</div>

			<div class="card mt-5">
				<div class="card-header">
					<strong class="font-16">Demand Note Date & Period</strong>
				</div>
				<div class="card-body mt-10">
					<div class="row">
						<div class="col-md-2 mb-2">
							<label>Period From <span class="text-danger">&nbsp;</span></label>
							<input type="text" name="from_date" id="from_date" class="form-control todays_dt_blank" placeholder="dd-mm-YYYY" value="{{$mode=='create' ? '' : ($record->from_date ? date('d-m-Y',strtotime($record->from_date)) : '')}}" autocomplete="off">
						</div>
						<div class="col-md-2 mb-3">
							<label>Period To <span class="text-danger">&nbsp;</span></label>
							<input type="text" name="to_date" id="to_date" class="form-control todays_dt_blank" placeholder="dd-mm-YYYY" value="{{$mode=='create' ? '' : ($record->to_date ? date('d-m-Y',strtotime($record->to_date)) : '')}}" autocomplete="off">
						</div>
					
						<div class="col-md-2 mb-3">
							<label>Demand Note Date</label>
							<input type="text" name="demand_note_date" class="form-control todays_dt_blank" value="{{ $mode=='create' ? date('d\-m\-Y') : date('d\-m\-Y',strtotime($record->demand_note_date)) }}" required>
						</div>		
						<div class="col-md-2 mb-3">
							<label>Financial Year</label>
							<select name="financial_year" id="financial_year" class="form-control" required>
								@if($mode=='create')
									@foreach($financialYears as $year)
										<option value="{{$year}}">{{$year}}</option>
									@endforeach
								@else
									@foreach($financialYears as $year)
										<option value="{{$year}}" @if($record->financial_year==$year) selected @endif>{{$year}}</option>
									@endforeach
								@endif
							</select>
						</div>						
						<div class="col-md-2 mb-3">
							<label>Reference Type</label>
							<input type="text" class="form-control" value="{{ $record->requestid ? 'EOI' : 'Work Order' }}" readonly>
							<input type="hidden" name="reference_type" value="{{ $record->requestid ? 'EOI' : 'WORK_ORDER' }}">
						</div>

						<div class="col-md-2">
							<button type="button" class="form-control btn btn-info gridbtn mt-20 calculateDemandNoteBtn">
								<i class="fa fa-eye"></i> View
							</button>
						</div>
						
					</div>
					<div class="row"><div class="col-sm-12">&nbsp;</div></div>
				</div>
			</div>


			<div class="card mt-5">
				<div class="card-header">
					<strong class="font-16">Demand Note Amount Details</strong>
				</div>
				<div class="card-body mt-10">
					<div class="row">
						<div class="col-md-12 mb-3">
							<textarea name="vendor_funding_particular" class="form-control tinymce" rows="3" required placeholder="Particular for Vendor Payment Advance">{{$mode=='create' ? '' : $record->particular}}</textarea>
						</div>
					</div>


					{{-- Vendor Payment Advance --}}
					<div class="row mt-15">
						<div class="col-md-3 mb-3">
							<label>Advance Amount <span class="text-danger">*</span></label>
							<input type="text" name="advance_amount" id="advance_amount" class="form-control numbers" required placeholder="Enter advance amount" autocomplete="off" value="{{$mode=='create' ? '' : $record->taxable_amount}}">
						</div>
						<div class="col-md-3 mb-3">
							<label>GST</label>
							<select name="advance_gst_tax_id" id="advance_gst_tax_id" class="form-control" required onchange="calculateDemandAmounts()">
								<option value="">Select GST</option>
								@foreach($gstTaxes as $gst)
									@if($gst->rate==18)
										<option value="{{$gst->tax_id}}" data-rate="{{$gst->rate}}" data-cgst="{{$gst->cgst_percent}}" data-sgst="{{$gst->sgst_percent}}" data-igst="{{$gst->igst_percent}}" @if($mode!='create' && $record->tax_id==$gst->tax_id) selected @endif>{{$gst->tax_name}}</option>
									@endif
								@endforeach
							</select>
						</div>
						<div class="col-md-3 mb-3">
							<label>GST Amount</label>
							<input type="text" id="advance_gst_amount" class="form-control" value="{{$mode=='create' ? '0.00' : number_format($record->cgst_amount + $record->sgst_amount + $record->igst_amount,2,'.','')}}" readonly>
						</div>
						<div class="col-md-3 mb-3">
							<label>Total Advance (Inc. tax)</label>
							<input type="text" id="advance_total" class="form-control" value="{{$mode=='create' ? '0.00' : number_format($record->total_amount,2,'.','')}}" readonly>
						</div>
					</div>


					{{-- CHiPS Service Charge --}}
					<div class="row mt-15">
						<div class="col-md-12 mb-3">
							<textarea name="service_charge_particular" class="form-control tinymce" rows="3" required placeholder="Particular for CHiPS Service Charge">{{$mode=='create' ? '' : $serviceChargeRecord->particular}}</textarea>
						</div>
					</div>		

					<div class="row mt-15">
						<div class="col-md-3 mb-3">
							<label>Service Charge ({{$mode=='create' ? $record->adminChargePercentage : $serviceChargeRecord->adminChargePercentage}}%)</label>
							<input type="text" name="service_charge_amount" id="service_charge_amount" class="form-control" value="{{$mode=='create' ? '0.00' : number_format($serviceChargeRecord->taxable_amount,2,'.','')}}" readonly>
						</div>

						<div class="col-md-3 mb-3">
							<label>GST</label>
							<select name="service_charge_gst_tax_id" id="service_charge_gst_tax_id" class="form-control" required onchange="calculateDemandAmounts()">
								@foreach($gstTaxes as $gst)
									@if($gst->rate==18)
										<option value="{{$gst->tax_id}}" data-rate="{{$gst->rate}}" data-cgst="{{$gst->cgst_percent}}" data-sgst="{{$gst->sgst_percent}}" data-igst="{{$gst->igst_percent}}" @if($mode!='create' && $serviceChargeRecord->tax_id==$gst->tax_id) selected @endif>{{$gst->tax_name}}</option>
									@endif
								@endforeach
							</select>
						</div>

						<div class="col-md-3 mb-3">
							<label>GST Amount</label>
							<input type="text" id="service_charge_gst_amount" class="form-control" value="{{$mode=='create' ? '0.00' : number_format($serviceChargeRecord->cgst_amount + $serviceChargeRecord->sgst_amount + $serviceChargeRecord->igst_amount,2,'.','')}}" readonly>
						</div>

						<div class="col-md-3 mb-3">
							<label>Total Service Charge (Inc. tax)</label>
							<input type="text" id="service_charge_total" class="form-control" value="{{$mode=='create' ? '0.00' : number_format($serviceChargeRecord->total_amount,2,'.','')}}" readonly>
						</div>
					</div>


					{{-- Grand Total --}}
					<div class="row mt-15">
						<div class="col-md-9 mb-3"></div>
						<div class="col-md-3 mb-3">
							<label><strong>Total of Both Demand Notes</strong></label>
							<input type="text" id="total_demand_note_value" class="form-control" value="0.00" readonly>
						</div>
					</div>


					<div class="row mt-10">
						<div class="col-md-12 mb-3">
							<label>Remarks</label>
							<textarea name="remarks" class="form-control tinymce" rows="2" placeholder="Remark..">{{$mode=='create' ? '' : $record->remarks}}</textarea>
						</div>
					</div>

					<div class="row mt-10">
						<div class="col-md-6 mb-3">
							<label>Attachment</label>
							<input type="file" name="attachment" class="form-control" id="demandnote_attachment">
						</div>

						<div class="col-md-2 mb-3">
							@if($mode!='create')
								@if($record->attachment)
									<a href="{{route('view.uploadedfile',Crypt::encrypt($record->attachment))}}" target="_blank" style="position:absolute; right:3px; outline:none;">
										<button type="button" class="btn btn-info gridbtn width-150 mt-10"><i class="fa fa-file-pdf-o"></i> View Uploaded File</button>
									</a>
								@endif
							@endif
						</div>

						<div class="col-md-4 mt-10 text-right">
							@if($mode=='create')
								<button type="button" class="btn btn-info gridbtn width-100" id="btnSaveDemandNote"><i class="fa fa-save"></i> Submit</button>
							@else
								<button type="button" class="btn btn-info gridbtn width-100" id="btnUpdateDemandNote"><i class="fa fa-refresh"></i> Update</button>
							@endif
						</div>
					</div>

				</div>
			</div>
			</form>
		</div>
	</div>
</div>


<div class="modal fade" id="demandResourceModal">
    <div class="modal-dialog modal-lg" style="width:95%; margin-top:0px;">
        <div class="modal-content">
            <div class="modal-header" style="padding:5px 20px!important;">
                <h5 class="modal-title">Demand Note Value <button type="button" class="btn btn-primary br-5 right clsBtn" class="close" data-dismiss="modal"><i class="fa fa-remove"></i> Close</button></h5>
            </div>

            <div class="modal-body">
                <div class="demand_resource"></div>
            </div>
        </div>
    </div>
</div>

<script src="{{ asset('panel/assets/js/jquery-2.1.4.min.js') }}"></script>
<script src="{{ asset('panel/assets/js/ace-elements.min.js') }}"></script>
<script src="{{ asset('panel/assets/js/ace.min.js') }}"></script>
<script src="{{ asset('panel/assets/tinymce/tinymce.min.js') }}"></script>

<script>

@if($mode == 'create')
    let adminChargePercentage = {{ $record->adminChargePercentage }};
@else
    let adminChargePercentage = {{ $serviceChargeRecord->adminChargePercentage }};
@endif

$(document).ready(function()
{
	calculateDemandAmounts();
});


$('#demandnote_attachment').ace_file_input({
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

function calculateDemandAmounts()
{
	let advanceAmount = parseFloat($("#advance_amount").val()) || 0;

	let advanceGstRate = parseFloat($("#advance_gst_tax_id option:selected").data("rate")) || 0;
	let advanceGstAmount = (advanceAmount * advanceGstRate) / 100;
	let advanceTotal = advanceAmount + advanceGstAmount;

	let serviceCharge = (advanceAmount * adminChargePercentage) / 100;

	let serviceGstRate = parseFloat($("#service_charge_gst_tax_id option:selected").data("rate")) || 0;
	let serviceGstAmount = (serviceCharge * serviceGstRate) / 100;
	let serviceChargeTotal = serviceCharge + serviceGstAmount;

	let grandTotal = Math.round(advanceTotal) + Math.round(serviceChargeTotal);

	$("#advance_gst_amount").val(advanceGstAmount.toFixed(2));
	$("#advance_total").val(Math.round(advanceTotal).toFixed(2));

	$("#service_charge_amount").val(serviceCharge.toFixed(2));
	$("#service_charge_gst_amount").val(serviceGstAmount.toFixed(2));
	$("#service_charge_total").val(Math.round(serviceChargeTotal).toFixed(2));

	$("#total_demand_note_value").val(grandTotal.toFixed(2));
}

$("#advance_amount").keyup(function(){
	calculateDemandAmounts();
});

$("#btnSaveDemandNote").click(function () {

    bootbox.confirm("Are you sure you want to save this Demand Note?", function(result){

        if(result)
        {
            saveDemandNote();
        }

    });

});

$("#btnUpdateDemandNote").click(function () {

    bootbox.confirm("Are you sure you want to update this Demand Note?", function(result){

        if(result)
        {
            saveDemandNote();
        }

    });

});

function saveDemandNote()
{
	let url = "";
	@if($mode=='create')
		url="{{ route('finance.demandnote.store') }}";
	@else
		url = "{{ route('finance.demandnote.update', ['id' => Crypt::encrypt($record->demand_note_id)]) }}";
	@endif
	

	tinymce.triggerSave();
    var formData = new FormData($("#frmDemandNote")[0]);

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
            $("#btnSaveDemandNote").prop("disabled",true);
			$("#btnUpdateDemandNote").prop("disabled",true);
        },
        success:function(response)
        {
            $("#btnSaveDemandNote").prop("disabled",false);
			$("#btnUpdateDemandNote").prop("disabled",false);
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
        error:function(xhr)
        {
            $("#btnSaveDemandNote").prop("disabled",false);
			$("#btnUpdateDemandNote").prop("disabled",false);
            if(xhr.status==422)
            {
                var errors="";
                $.each(xhr.responseJSON.errors,function(key,value){
                    errors+="<div>"+value[0]+"</div>";
                });
                bootbox.alert(errors);
            }
            else
            {
                bootbox.alert("Something went wrong. Please try again.");
            }
        }
    });

}



$(document).on('click', '.calculateDemandNoteBtn', function () {

    var from_date 	= 	$('#from_date').val();
    var to_date 	= 	$('#to_date').val();
    var requestid 	= 	$('#request_id').val();

    if (requestid == '') {
        bootbox.alert('Invalid data found.');
        return;
    }

    if (from_date == '') {
        bootbox.alert('Please select Period From.');
        return;
    }

    if (to_date == '') {
        bootbox.alert('Please select Period To.');
        return;
    }

    $.ajax({
        url: "{{ route('finance.demandnote.showresources') }}",
        type: "POST",

        data: {
            from_date: from_date,
            to_date: to_date,
            requestid: requestid,
            _token: "{{ csrf_token() }}"
        },

        beforeSend: function () {
            $('.calculateDemandNoteBtn').prop('disabled', true);
            $('.demand_resource').html(
                '<div class="text-center py-3">' +
                    '<i class="fa fa-spinner fa-spin"></i> Loading...' +
                '</div>'
            );
        },
        success: function (response) {

            if (response.status)
			{
                $('#advance_amount').val(response.advance_amount || 0);
                $('#service_charge_amount').val(response.service_charge_amount || 0);
                $('.demand_resource').html(response.html);
                $('#demandResourceModal').modal('show');
            }
			else 
			{
                $('.demand_resource').html('');
                bootbox.alert(response.message || 'Unable to calculate the amount.');
            }
        },
        error: function (xhr)
		{
            $('.demand_resource').html('');
            if(xhr.responseJSON && xhr.responseJSON.message)
			{
                bootbox.alert(xhr.responseJSON.message);
            }
			else
			{
                bootbox.alert('Something went wrong while calculating the amount.');
            }
        },
        complete: function () {
            $('.calculateDemandNoteBtn').prop('disabled', false);
        }
    });
});

</script>
@endsection