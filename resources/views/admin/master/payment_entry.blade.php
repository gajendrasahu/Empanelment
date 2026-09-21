@extends('admin.admin_master')
@section('admin')
@php
$t=1;
@endphp
<div class="main-content">
	<div class="main-content-inner">
			<div class="page-content" style="padding:10px 15px!important;">
				<div class="ace-settings-container" id="ace-settings-container"></div>
				<div class="row">
					<div class="col-xs-12">
					<!-- PAGE CONTENT BEGINS -->
<div class="tabbable animated fadeInUp delay-02" style="padding:0px 0px!important;">

		<div class="tab-content no-border" style="min-height:100px; padding:0px 10px;">

<div id="recordlist" class="tab-pane in active" style="padding:0px 0px!important;">
	<div class="row" style="padding:0px;">
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
		@if(Session::has('duplicate'))
		<div class="col-sm-12">
			<div class="alert alert-block alert-warning">
				<button type="button" class="close" data-dismiss="alert">
					<i class="ace-icon fa fa-times"></i>
				</button>
				<i class="fa fa-warning"></i> {{ Session::get('duplicate') }}
			</div>
		</div>
		@endif
		@if ($errors->any())
		<br>
		<div class="col-sm-12">
			<div class="alert alert-block alert-danger">
				<button type="button" class="close" data-dismiss="alert">
					<i class="ace-icon fa fa-times"></i>
				</button>
				<ul>
					@foreach ($errors->all() as $error)
						<i class="fa fa-warning"></i> {{ $error }}<br>
					@endforeach
				</ul>
			</div>
		</div>
		@endif				
		
		<div class="col-xs-12">
		<form name="add_payment" id="add_payment" action="#" method="post">
			@csrf
			<div class="table-responsive">
				<table class="pd-15 mytable" style="width:100%; border:1px solid #777; border-collapse:collapse;" border="1">
				<tr class="myheadbg"><td colspan="7"><i class="fa fa-warning"></i> Please do not refresh page.</td></tr>
				<tr><td colspan="7"><b>Firm Name : {{$first->companyname}}</b></td></tr>
				<tr>
					<td style="width:25px;" nowrap><b>S.No.</b></td>
					<td nowrap class="center"><b>Invoice Date</b></td>
					<td nowrap><b>Invoice Number</b></td>
					<td nowrap style="text-align:right;"><b>Taxable Amount</b></td>
					<td nowrap style="text-align:right;"><b>Tax Amount</b></td>
					<td nowrap style="text-align:right;"><b>Invoice Value</b></td>
					<td nowrap style="text-align:right;"><b>Balance Value</b></td>
				</tr>			
				</thead>
				<tbody class="tabledata">
				@php
				$total_amount	=	0;
				$total_tax	=	0;
				$total_invoice	=	0;
				$total_balance	=	0;
				$invoice_numbers	=	"";
				@endphp
				@foreach($invoices as $invoice)
				<tr>
					<td class="center">{{$loop->iteration}}</td>
					<td class="center">{{date('d\-m\-Y',strtotime($invoice->invoice_date))}}</td>
					<td>{{$invoice->invoice_number}}</td>
					<td style="text-align:right;" class="format-indian" data-value="{{number_format($invoice->amount_value,'2','.','')}}"></td>
					<td style="text-align:right;" class="format-indian" data-value="{{number_format($invoice->tax_value,'2','.','')}}"></td>
					<td style="text-align:right;" class="format-indian" data-value="{{number_format($invoice->invoice_value,'2','.','')}}"></td>
					<td style="text-align:right;" class="format-indian" data-value="{{number_format($invoice->balance_value,'2','.','')}}"></td>
				</tr>
				@php
					$total_amount	=	$total_amount+$invoice->amount_value;
					$total_tax		=	$total_tax+$invoice->tax_value;
					$total_invoice	=	$total_invoice+$invoice->invoice_value;				
					$total_balance	=	$total_balance+$invoice->balance_value;
					if($loop->iteration==1)
					$invoice_numbers	=	$invoice->invoice_number;
					else
					$invoice_numbers	=	$invoice_numbers.", ".$invoice->invoice_number;
				@endphp
				@endforeach
				<tr>
					<td colspan="6" style="text-align:right;"><b>Total Balance Value for Selected Invoices</b></td>
					<td style="text-align:right;">
						<b>
							<span class="format-indian" data-value="{{number_format($total_balance,'2','.','')}}"></span>
						</b>
					</td>
				</tr>
				<tr>
					<td colspan="7" style="padding:0px!important;">
<table class="mytable pd-10" style="width:100%; color:black!important">
	<tr class="myheadbg"><td colspan="5"><b>Payment Detail</b></td></tr>
	<tr>
		<td>
			Entry Date*<br>
			<input type="hidden" name="recordIds" id="recordIds" value="{{session('record_ids')}}">
			<input type="text" class="form-control order_date" required placeholder="dd-mm-YYYY" name="payment_date" id="payment_date" autocomplete="off" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}">
		</td>
		<td>
			Total Balance Value<br>
			<input type="text" class="form-control numbers" readonly name="total_balance" id="total_balance" value="{{$total_balance}}" autocomplete="off" style="color:black; font-weight:bold;" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}">
		</td>
		<td>
			Paying Amount*<br>
			<input type="text" class="form-control numbers" name="paying_amount" id="paying_amount" value="" placeholder="0.00" autocomplete="off" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}" onchange="CalculateBalance()">
		</td>
		<td>
			Balance Amount<br>
			<input type="text" class="form-control numbers" readonly name="balance_amount" id="balance_amount" value="{{$total_balance}}" placeholder="0.00" autocomplete="off" style="color:black; font-weight:bold;" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}">
		</td>
		<td>
			Payment Method*<br>
			<select class="form-control" required name="payment_method" id="payment_method" autocomplete="off" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}" onchange="CheckVisibility(this.value)">
				<option Value="Bank Transfer">Bank Transfer</option>
				<option Value="Cheque">Cheque</option>
				<option Value="Demand Draft">Demand Draft</option>
			</selected>
		</td>
	</tr>
	<tr>
		<td class="cheque">
			Cheque Date* <i class="fa fa-info-circle" title="In case of cheque payment only"></i><br>
			<input type="text" class="form-control blankdate" placeholder="dd-mm-YYYY" name="cheque_date" id="cheque_date" autocomplete="off" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}">
		</td>
		<td class="demand">
			Demand Draft Date* <i class="fa fa-info-circle" title="In case of demand draft payment only"></i><br>
			<input type="text" class="form-control blankdate" placeholder="dd-mm-YYYY" name="demand_date" id="demand_date" autocomplete="off" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}">
		</td>
	
		<td class="bank">
			Bank Account*<br>
			<select class="form-control" required name="bank_id" id="bank_id" autocomplete="off" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}">
				<option value="">--Bank Account--</option>
				<option Value="1">Bank 1</option>
				<option Value="2">Bank 2</option>
				<option Value="3">Bank 3</option>
			</selected>
		</td>
		<td colspan="4">
			Transaction / UTR / DD Number*<br>
			<input type="text" class="form-control" name="transaction_number" id="transaction_number" value="" placeholder="Transaction number.." autocomplete="off" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}">
		</td>
	
	</tr>
	<tr>
	
		<td colspan="5">
			Invoice References<br>
			<input type="text" class="form-control" readonly required placeholder="Reference number.." name="invoice_reference" id="invoice_reference" autocomplete="off" value="{{$invoice_numbers}}" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}">
		</td>
	</tr>
	<tr>
		<td colspan="5">
			Remark<br>
			<input type="text" class="form-control" required placeholder="Remark.." name="payment_remark" id="payment_remark" autocomplete="off" value="" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}">
		</td>
	</tr>
	<tr>
		<td colspan="5" style="text-align:right;">
			<br>
			<button type="button" class="btn btn-info savePaymentBtn">Submit</button>
		</td>
	</tr>
	<tr><td colspan="5" style="height:200px;">&nbsp;</td></tr>
</table>					
					</td>
				</tr>
				</tbody>
				
				</table>
			</div>
		</form>
		</div>
	</div>
</div>

</div>
</div>


						
				</div><!-- /.col -->
			</div><!-- /.row -->
		</div><!-- /.page-content -->
	
		
	</div>
</div>

<script src="{{ asset('panel/assets/js/jquery-2.1.4.min.js') }}"></script>
<script>

$('.savePaymentBtn').on('click', function(e) {
	$(".savePaymentBtn").prop("disabled","disabled");
	$(".savePaymentBtn").css("display","none");
	bootbox.confirm('Do you confirm this action?',function(result){
		if(result)
		{
			e.preventDefault();
			var form = $('#add_payment')[0];
			var formData = new FormData(form);
			$.ajax({
				url: '{{route("save.payment")}}',
				method: 'POST',
				data: formData,
				processData: false,
				contentType: false,
				success: function(response) {
					if(response.status===200)
					{
						bootbox.alert(response.message);
						setTimeout(function(){ window.location.href ="{{ route('adminvendor.invoices') }}"; },3000);
						return false;
					}
					else
					{
						$(".savePaymentBtn").css("display","");			
						$(".savePaymentBtn").prop("disabled","");
						bootbox.alert('<span style="color:red;">'+response.message+'</span>');
						return false;
					}
				},
				error: function(xhr) {
					let errors = xhr.responseJSON?.errors;
					let message = '';
					$.each(errors, function(key, val) {
						message += '<i class="fa fa-hand-o-right"></i> '+ val + '<br>';
					});
					bootbox.alert(message);
					$(".savePaymentBtn").css("display","");			
					$(".savePaymentBtn").prop("disabled","");
				}
			});
		}
		else
		{
			$(".savePaymentBtn").css("display","");			
			$(".savePaymentBtn").prop("disabled","");
		}
	});
});


$(document).ready(function() {
	
	$(".demand").css("display","none");
	$(".cheque").css("display","none");
	
	$.ajaxSetup({
		headers: {
			'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
		}
	});

	document.querySelectorAll('.format-indian').forEach(function(el) {
		el.innerText = formatIndianNumber(el.dataset.value);
	});

});


function CalculateBalance()
{
	var totalBalance	=	Number(document.getElementById("total_balance").value);
	var payingAmount	=	Number(document.getElementById("paying_amount").value);
	if(payingAmount>totalBalance)
	{
		bootbox.alert("The payment amount must be less than the total balance.");
		document.getElementById("paying_amount").value	=	"";
		document.getElementById("balance_amount").value	=	Number(document.getElementById("total_balance").value);
	}
	else
	{
		document.getElementById("balance_amount").value	=	totalBalance-payingAmount;
	}
}
function CheckVisibility(val)
{
	if(val=='Bank Transfer')
	{
		$(".bank").css("display","");
		$(".demand").css("display","none");
		$(".cheque").css("display","none");
	}
	else if(val=='Cheque')
	{
		$(".demand").css("display","none");
		$(".bank").css("display","none");	
		$(".cheque").css("display","");		
	}
	else if(val=='Demand Draft')
	{
		$(".demand").css("display","");
		$(".bank").css("display","none");
		$(".cheque").css("display","none");			
	}
}


</script>
<script src="{{ asset('panel/assets/js/master.js') }}"></script>
<script src="{{ asset('panel/assets/js/datatablePaginationWithFilter.js') }}"></script>
@endsection
