@extends('admin.admin_master')
@section('admin')
	@php
		$t = 0;
	@endphp
	<div class="main-content">
		<div class="main-content-inner">
			<div class="page-content" style="padding:10px 15px!important;">
				<div class="ace-settings-container" id="ace-settings-container"></div>
				<div class="row">
					<div class="col-xs-12">
						<!-- PAGE CONTENT BEGINS -->
						<div class="tabbable" style="padding:0px 0px!important;">
							<ul class="nav nav-tabs padding-0">
								@if(in_array(1, Session::get('actions')))
								<li class="active">
									<a data-toggle="tab" href="#home">
										<i class="green ace-icon fa fa-plus-circle bigger-120" style="vertical-align:bottom;"></i>Submit Invoice
									</a>
								</li>
								@endif
							</ul>


							<div class="tab-content no-border"
								style="border:1px solid #ddd; min-height:100px; padding:0px 10px;">
								@if(in_array(1, Session::get('actions')))
									<div id="home" class="tab-pane in active" style="padding:0px 10px!important;">
										<div class="row">
											<div class="row">
												<form name="frm" id="frm" action="{{ route('store.invoice', 0)}}"
													method="post" enctype="multipart/form-data">
													@if(Session::has('success'))
														<div class="col-sm-12">
															<div class="alert alert-block alert-success">
																<button type="button" class="close" data-dismiss="alert">
																	<i class="ace-icon fa fa-times"></i>
																</button>
																{{ Session::get('success') }}
															</div>
														</div>
													@endif
													@if(Session::has('fail'))
														<div class="col-sm-12">
															<div class="alert alert-block alert-danger">
																<button type="button" class="close" data-dismiss="alert">
																	<i class="ace-icon fa fa-times"></i>
																</button>
																{{ Session::get('fail') }}
															</div>
														</div>
													@endif
													@if(Session::has('duplicate'))
														<div class="col-sm-12">
															<div class="alert alert-block alert-danger">
																<button type="button" class="close" data-dismiss="alert">
																	<i class="ace-icon fa fa-times"></i>
																</button>
																<i class="fa fa-warning"></i> {{ Session::get('duplicate') }}
															</div>
														</div>
													@endif
													@csrf
													<div class="form-group">

<div class="col-sm-12">
	Project Name <label id="req">*</label>
	<select class="form-control select2" name="order_id" id="order_id" data-placeholder="Project Name" required onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}" style="width:100%!important;">
			<option value=""></option>
			@foreach($orders as $order)
			<option value="{{$order->orderid}}" @if(old('order_id')==$order->orderid) selected @endif>{{$order->ordernumber}} [{{$order->project_name}}]</option>
			@endforeach
		</select>

	<span class="text-danger">@error('mpr_month') <i
		class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i>
	@enderror</span>
</div>
<div class="col-sm-12">&nbsp;</div>
<div class="col-sm-3">
	Month <label id="req">*</label>
	<input type="hidden" name="form_token" id="form_token" value="{{$token}}">

	<select class="form-control select2" name="mpr_month" id="mpr_month" data-placeholder="Month" required onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}" style="width:100%!important;">
			<option value=""></option>
			@foreach($months as $month)
			<option value="{{$month['value']}}" @if(old('mpr_month')==$month['value']) selected @endif>{{$month['label']}}</option>
			@endforeach
		</select>

	<span class="text-danger">@error('mpr_month') <i
		class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i>
	@enderror</span>
</div>

<div class="col-sm-3">
	Year <label id="req">*</label>
	<select class="form-control select2" name="mpr_year" id="mpr_year" required onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}">
		@foreach($years as $year)
		<option value="{{$year['value']}}" @if(old('mpr_year')==$month['value']) selected @endif>{{$year['label']}}</option>
		@endforeach
	</select>

	<span class="text-danger">
		@error('mpr_year') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror
	</span>
</div>

<div class="col-sm-3">
	Invoice Date <label id="req">*</label>
	<input type="text" class="form-control order_date width-full" name="invoice_date" id="invoice_date" placeholder="dd-mm-YYYY" required autocomplete="off" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}" style="margin-top:0px;" value="{{old('invoice_date')}}">

	<span class="text-danger">
		@error('invoice_date') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i>@enderror
	</span>
</div>
<div class="col-sm-3">
	Invoice Number <label id="req">*</label>
	<input type="text" class="form-control" name="invoice_number" id="invoice_number" placeholder="0" required autocomplete="off" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}" style="margin-top:0px;" value="{{old('invoice_number')}}">

	<span class="text-danger">
		@error('invoice_number') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i>@enderror
	</span>
</div>

<div class="col-sm-12">&nbsp;</div>

<div class="col-sm-3">
	Amount <label id="req">*</label>
	<input type="text" class="form-control numbers" name="amount_value" id="amount_value" placeholder="0" required autocomplete="off" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}" style="margin-top:0px;" value="{{old('amount_value')}}" onchange="CalAmount()">

	<span class="text-danger">
		@error('amount_value') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i>@enderror
	</span>
</div>


<div class="col-sm-3">
	Tax Value <label id="req">*</label>
	<input type="text" class="form-control numbers" name="tax_value" id="tax_value" placeholder="0" required autocomplete="off" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}" style="margin-top:0px;" value="{{old('tax_value')}}" onchange="CalAmount()">

	<span class="text-danger">
		@error('tax_value') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i>@enderror
	</span>
</div>

<div class="col-sm-3">
	Invoice Value <label id="req">*</label>
	<input type="text" class="form-control numbers" name="invoice_value" id="invoice_value" placeholder="0" required autocomplete="off" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}" style="margin-top:0px;" value="{{old('invoice_value')}}">

	<span class="text-danger">
		@error('invoice_value') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i>@enderror
	</span>
</div>

<div class="col-sm-12">&nbsp;</div>

<div class="col-sm-3">
	MPR File <label id="req">*</label>
	<input type="file" class="form-control" required name="mpr_file" id="mpr_file" accept=".pdf,.zip,.rar" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}"/>						
	<span class="text-danger">@error('mpr_file') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
</div>

<div class="col-sm-3">
	Attendance File <label id="req">*</label>
	<input type="file" class="form-control" required name="attendance_file" id="attendance_file" accept=".pdf,.zip,.rar" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}"/>						
	<span class="text-danger">@error('attendance_file') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
</div>

<div class="col-sm-3">
	Supporting File <label id="req">*</label>
	<input type="file" class="form-control" required name="supporting_file" id="supporting_file" accept=".pdf,.zip,.rar" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}"/>						
	<span class="text-danger">@error('supporting_file') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
</div>


<div class="col-sm-3">
	Invoice File <label id="req">*</label>
	<input type="file" class="form-control" required name="invoice_file" id="invoice_file" accept=".pdf,.zip,.rar" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}"/>						
	<span class="text-danger">@error('invoice_file') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
</div>
<div class="col-sm-12">&nbsp;</div>
<div class="col-sm-9">&nbsp;</div>


														<div class="col-sm-3">
														<button type="submit" class="btn btn-info myfrmbtn submitBtn" tabindex="{{$t++}}">SUBMIT</button>
														</div>
														<div class="col-sm-12">&nbsp;</div>
														<div class="col-sm-12">&nbsp;</div>
													</div>
												</form>
											</div>
										</div>
									</div>
								@endif
							</div>
						</div>



					</div><!-- /.col -->
				</div><!-- /.row -->
			</div><!-- /.page-content -->


		</div>
	</div>
<script src="{{ asset('panel/assets/js/jquery-2.1.4.min.js') }}"></script>
<script src="{{ asset('panel/assets/js/ace-elements.min.js') }}"></script>
<script src="{{ asset('panel/assets/js/ace.min.js') }}"></script>
<script>

setTimeout(function(){ $(".select2").css('width','100%');},500);

jQuery(function($) {	

	$('#mpr_file').ace_file_input({
		no_file:'No File ...',
		btn_choose:'MPR File (.pdf,.zip,.rar)',
		btn_change:'Change',
		btn_name:'fourthimage',
		thumbnail:false //| true | large
	});

	$('#attendance_file').ace_file_input({
		no_file:'No File ...',
		btn_choose:'Attendance File (.pdf,.zip,.rar)',
		btn_change:'Change',
		btn_name:'fourthimage',
		thumbnail:false //| true | large
	});

	$('#supporting_file').ace_file_input({
		no_file:'No File ...',
		btn_choose:'Supporting File (.pdf,.zip,.rar)',
		btn_change:'Change',
		btn_name:'fourthimage',
		thumbnail:false //| true | large
	});

	$('#invoice_file').ace_file_input({
		no_file:'No File ...',
		btn_choose:'Invoice File (.pdf,.zip,.rar)',
		btn_change:'Change',
		btn_name:'fourthimage',
		thumbnail:false //| true | large
	});

});	

$('.submitBtn').on('click', function(e) {
	e.preventDefault();
	
	const mpr_month = $('#mpr_month').val();
	const mpr_year = $('#mpr_year').val();
	const invoice_date = $('#invoice_date').val();
	const invoice_number = $('#invoice_number').val();
	const amount_value = $('#amount_value').val();
	const tax_value = $('#tax_value').val();
	const invoice_value = $('#invoice_value').val();
    const mprFile = $('#mpr_file').val();
    const attendanceFile = $('#attendance_file').val();
    const supportingFile = $('#supporting_file').val();

    // Validate file selections
    if (!mpr_month) {
        bootbox.alert("Please select the Month for the MPR.");
        return;
    }
    if (!mpr_year) {
        bootbox.alert("Please select the Year for the MPR.");
        return;
    }
    if (!invoice_date) {
        bootbox.alert("Please provide the Invoice Date.");
        return;
    }
    if (!invoice_number) {
        bootbox.alert("Please enter a valid Invoice Number.");
        return;
    }
    if (!amount_value) {
        bootbox.alert("Please enter the Amount value.");
        return;
    }
    if (!tax_value) {
        bootbox.alert("Please enter the Tax value.");
        return;
    }
    if (!invoice_value) {
        bootbox.alert("Please enter the total Invoice value.");
        return;
    }
    if (!mprFile) {
        bootbox.alert("Please select the MPR File before submitting.");
        return;
    }
    if (!attendanceFile) {
        bootbox.alert("Please select the Attendance File before submitting.");
        return;
    }
    if (!supportingFile) {
        bootbox.alert("Please select the Supporting File before submitting.");
        return;
    }
	bootbox.confirm('Are you sure you want to submit the invoice details?', function(result) {
		if (result)
		{
			$('#frm')[0].submit();
		}
	});
});


function CalAmount()
{
	var amount	=	Number(document.getElementById("amount_value").value);
	var tax		=	Number(document.getElementById("tax_value").value);
	
	document.getElementById("invoice_value").value=(amount+tax).toFixed(2);
}
</script>
<script src="{{ asset('panel/assets/js/datatablePaginationWithFilter.js') }}"></script>
@endsection