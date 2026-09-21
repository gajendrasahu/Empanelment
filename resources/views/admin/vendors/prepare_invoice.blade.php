@extends('admin.admin_master')
@section('admin')
@php
$t=0;
@endphp

<div class="main-content">
	<div class="main-content-inner">
			<div class="page-content">
				<div class="ace-settings-container" id="ace-settings-container"></div>
				<div class="row">
					<div class="col-xs-12">
					<!-- PAGE CONTENT BEGINS -->
<div class="tabbable animated fadeInUp delay-02" style="padding:0px 0px!important;">
		<ul class="nav nav-tabs padding-0">
		@if(in_array(1,Session::get('actions')))
			<li class="active">
				<a data-toggle="tab" href="#home" class="form-label font-14 v-middle">
					<i class="fa fa-book" style="margin-top:3px;"></i> Submit Invoice
				</a>
			</li>
		@endif
		</ul>

	
		<div class="tab-content no-border" style="border-radius:0px!important;">
@if(in_array(1,Session::get('actions')))
<div id="home" class="tab-pane in active" style="padding:0px 10px!important;">
	<div class="row">
			<form name="invoicevoucher" id="invoicevoucher" action="{{route('store.invoicevoucher')}}" method="post" enctype="multipart/form-data">
				@if(Session::has('success'))
				<div class="col-sm-12">
					<div class="alert alert-block alert-success">
				    	<button type="button" class="close" data-dismiss="alert">
							<i class="ace-icon fa fa-times"></i>
						</button>
						{!! Session::get('success') !!}
					</div>
				</div>
				@endif
				@if(Session::has('fail'))
				<div class="col-sm-12">
					<div class="alert alert-block alert-danger">
				    	<button type="button" class="close" data-dismiss="alert">
							<i class="ace-icon fa fa-times"></i>
						</button>
						<i class="fa fa-warning"></i> {{ Session::get('fail') }}
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
				@if ($errors->any())
					<div class="col-sm-12 animated flipInX">
					<div class="alert alert-danger">
						<ul>
							@foreach ($errors->all() as $error)
								<i class="fa fa-warning"></i> {{ $error }}<br>
							@endforeach
						</ul>
					</div>
					</div>
				@endif				
				@csrf
				<div class="form-group content-area">
					<div class="col-sm-12" style="padding:8px; font-weight:bold!important;">
						@if($order->project_name) {{$order->project_name}} | @endif Work Order Number : {{$order->ordernumber}} | Date : {{date('d\-m\-Y',strtotime($order->orderdate))}}
					</div>
					<div class="col-sm-6">
						Invoice Number <label id="req">*</label>
						<input type="hidden" name="form_token" id="form_token" value="{{$token}}">
						<input type="hidden" name="mpr_ids" id="mpr_ids" value="{{$mpr_ids}}">
						<input type="text" class="form-control" name="invoice_number" id="invoice_number" value="{{old('invoice_number')}}" placeholder="Invoice Number" required autocomplete="off" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}"/>
						
						<span class="text-danger">@error('invoice_number') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
					</div>
					<div class="col-sm-3">
						Invoice Date <label id="req">*</label>
						<input type="text" class="form-control form_dates" name="invoice_date" id="invoice_date" value="{{old('invoice_date')}}" placeholder="dd-mm-YYYY" required autocomplete="off" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}"/>
						
						<span class="text-danger">@error('invoice_date') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
					</div>
					<div class="col-sm-3">
						Tax Type <label id="req">*</label>
						<select class="form-control" name="tax_type" id="tax_type" required tabindex="{{$t++}}">
							<option value="">Select Tax Type</option>
							<option value="INTRA_STATE" {{ old('tax_type') == 'INTRA_STATE' ? 'selected' : '' }}>
								Intra-State (CGST + SGST)
							</option>

							<option value="INTER_STATE" {{ old('tax_type') == 'INTER_STATE' ? 'selected' : '' }}>
								Inter-State (IGST)
							</option>
						</select>
						<span class="text-danger">
							@error('tax_type') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror
						</span>
					</div>					
					<div class="col-sm-12">&nbsp;</div>
					<div class="col-sm-12">
					<table class="mytable pd-8" border="1">
						<tr class="myheadbg font-14">
							<td colspan="9" style="font-weight:600;">
								Work Order Number : {{$order->ordernumber}} | Date : {{date('d\-m\-Y',strtotime($order->orderdate))}}
							</td>
						</tr>
						<tr class="myheadbg font-14" style="font-weight:600;">
							<td class="center width-30">S.No.</td>
							<td class="center width-130">Submission Date</td>
							<td class="">MPR Number</td>
							<td class="text-center">MPR</td>
							<td class="text-center">Attendance</td>
							<td class="text-center">Supporting</td>
							<td class="center width-100">MPR Month</td>
							<td class="center width-130">Verification Date</td>
							<td class="width-150" style="text-align:right;">Approved Value</td>
						</tr>
						@php
						$total	=	0;
						@endphp
						@foreach($mprs as $mpr)
						<tr>
							<td class="center">{{$loop->iteration}}</td>
							<td class="center">{{date('d\-m\-Y',strtotime($mpr->submission_date))}}</td>
							<td class="">{{$mpr->mpr_number}}</td>
							<td class="text-center">
							@if($mpr->signed_mpr)
								<a href="{{route('view.uploadedfile',Crypt::encrypt($mpr->signed_mpr))}}" target="_blank"><i class="fa fa-file-pdf-o"></i></a>
							@endif
							</td>
							<td class="text-center">
							@if($mpr->signed_attendance)
								<a href="{{route('view.uploadedfile',Crypt::encrypt($mpr->signed_attendance))}}" target="_blank"><i class="fa fa-file-pdf-o"></i></a>
							@endif
							</td>
							
							<td class="text-center">
							@if($mpr->signed_supporting)
								<a href="{{route('view.uploadedfile',Crypt::encrypt($mpr->signed_supporting))}}" target="_blank"><i class="fa fa-file-pdf-o"></i></a>
							@endif
							</td>
							<td class="center">{{ date('F', mktime(0, 0, 0, $mpr->mpr_month, 1)) }}-{{$mpr->mpr_year}}</td>
							<td class="center">{{date('d\-m\-Y',strtotime($mpr->verified_date))}}</td>
							<td class="format-indian" style="text-align:right;" data-value="{{$mpr->mpr_approved_value}}"></td>
						</tr>
						@php
						$total=$total+$mpr->mpr_approved_value;
						@endphp
						@endforeach
						@php
						$operatingRate	=	$operating;
						$operating		=	($total*$operating)/100;
						$gst			=	(($operating+$total)*$order->tax)/100;
						$taxableamount	=	$total+$operating;
						$netamount		=	$taxableamount+$gst;
						@endphp
						@if($mprs->count()!=0)
						@if(!$operating)
						<tr>
							<td class="form-label font-14" colspan="8" style="text-align:right;">Taxable Amount</td>
							<td class="form-label font-14 format-indian" colspan="6" style="text-align:right;" data-value="{{ number_format($total,'2','.','') }}"></td>
						</tr>
						@else
						<tr>
							<td class="form-label font-14" colspan="8" style="text-align:right;">Total Amount</td>
							<td class="form-label font-14 format-indian" colspan="6" style="text-align:right;" data-value="{{ number_format($total,'2','.','') }}"></td>
						</tr>
						<tr>
							<td class="form-label font-14" colspan="8" style="text-align:right;">Operating Cost @ {{round($operatingRate)}}%</td>
							<td class="form-label font-14 format-indian" colspan="6" style="text-align:right;" data-value="{{ number_format($operating,'2','.','') }}"></td>
						</tr>
						<tr>
							<td class="form-label font-14" colspan="8" style="text-align:right;">Taxable Amount</td>
							<td class="form-label font-14 format-indian" colspan="6" style="text-align:right;" data-value="{{ number_format($taxableamount,'2','.','') }}"></td>
						</tr>
							
						@endif
						<tr>
							<td class="form-label font-14" colspan="8" style="text-align:right;">Gst @ {{$order->tax}}%</td>
							<td class="form-label font-14 format-indian" colspan="6" style="text-align:right;" data-value="{{ number_format($gst,'2','.','') }}"></td>
						</tr>
						<tr>
							<td class="font-14" colspan="8" style="text-align:right;">Net Payable Amount</td>
							<td class="font-14 format-indian" colspan="6" style="text-align:right; font-weight:600;" data-value="{{ number_format($netamount,'2','.','') }}"></td>
						</tr>
						@endif
					</table>
					<br>
					</div>
					<div class="col-sm-12">
						Remark <label id="req">&nbsp;</label>
						<input type="text" class="form-control" name="invoice_remark" id="invoice_remark" value="{{old('invoice_remark')}}" placeholder="Remark here.." autocomplete="off" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}"/>
						
						<span class="text-danger">@error('invoice_remark') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
					</div>
					<div class="col-sm-12">&nbsp;</div>
					<div class="col-sm-4">
						Invoice File <label id="req">*</label>
						<input type="file" class="form-control invoicefile" required name="invoice_file" id="invoice_file" accept="application/pdf" onKeyPress="return OnKeyPress(this, event)" />
						<span class="text-danger">@error('invoice_file') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
					</div>
					<div class="col-sm-5">&nbsp;</div>
					<div class="col-sm-3" style="text-align:right;">
						<br>
						<button type="button" class="btn btn-info prepareInvoiceBtn"><i class="fa fa-save"></i> Submit Invoice</button>
					</div>
					<div class="col-sm-12">&nbsp;</div>
				</div>		
			</form>
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
	$('#invoice_file').ace_file_input({
		no_file:'No File ...',
		btn_choose:'Upload Invoice File*',
		btn_change:'Change',
		btn_name:'fourthimage',
		thumbnail:false //| true | large
	});

</script>
<script src="{{ asset('panel/assets/js/datatablePaginationWithFilter.js') }}"></script>
<script>
document.querySelectorAll('.format-indian').forEach(function(el) {
    el.innerText = formatIndianNumber(el.dataset.value);
});

$('.prepareInvoiceBtn').on('click', function(e) {
	e.preventDefault();
	var invoice_file = $('#invoice_file').get(0).files.length > 0;
	if (!invoice_file) {
		bootbox.alert("Invoice file is mandatory.");
		return false;
	}
	var invoice_number	=	document.getElementById("invoice_number").value;
	var invoice_date	=	document.getElementById("invoice_date").value;
	var tax_type 		= 	document.getElementById("tax_type").value;
	var msg				=	"";
	
	if(invoice_number=='')
	{
		msg+='Invoice number is mandatory field.<br>';
	}
	if(invoice_date=='')
	{
		msg+='Invoice date is mandatory field.<br>';
	}
	if(tax_type=='')
	{
		msg+='Tax Type is mandatory field.<br>';
	}	
	if(msg!='')
	{
		bootbox.alert(msg);
		return false;
	}
	bootbox.confirm('Do you want to submit these voucher details?',function(result){
		if(result)
		{
			$("#invoicevoucher").submit();
		}
	});
});

</script>

@endsection