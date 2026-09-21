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
		<ul class="nav nav-tabs padding-0">
		@if(in_array(1,Session::get('actions')))
			<li class="active">
				<a data-toggle="tab" href="#home" class="form-label font-14">
					<i class="green ace-icon fa fa-plus-circle bigger-120 datalist" style="vertical-align:text-top;"></i> Add Demand Note
					<p class="font-12">Receive payment against demand note</p>
				</a>
			</li>
		@endif
		</ul>

	
		<div class="tab-content no-border" style="border:1px solid #ddd; min-height:100px; padding:0px 10px;">
@if(in_array(1,Session::get('actions')))
<div id="home" class="tab-pane in active" style="padding:0px 10px!important;">
	<div class="row">
		<div class="row">
			<form name="frm" id="frm" action="{{ route('store.demandnote',Crypt::encrypt($order->orderid))}}" method="post" enctype="multipart/form-data">
				@csrf
				@if(Session::has('success'))
				<div class="col-sm-12" style="margin-top:20px;">
					<div class="alert alert-block alert-success">
				    	<button type="button" class="close" data-dismiss="alert">
							<i class="ace-icon fa fa-times"></i>
						</button>
						<i class="fa fa-check"></i> {{ Session::get('success') }}
					</div>
				</div>
				@endif
				@if(Session::has('fail'))
					
				<div class="col-sm-12" style="margin-top:20px;">
					<div class="alert alert-block alert-danger">
				    	<button type="button" class="close" data-dismiss="alert">
							<i class="ace-icon fa fa-times"></i>
						</button>
						<i class="fa fa-warning"></i> {{ Session::get('fail') }}
					</div>
				</div>
				@endif
				@if(Session::has('duplicate'))
				<div class="col-sm-12" style="margin-top:20px;">
				    <div class="alert alert-block alert-danger">
				    	<button type="button" class="close" data-dismiss="alert">
							<i class="ace-icon fa fa-times"></i>
						</button>
				    	<i class="fa fa-warning"></i> {{ Session::get('duplicate') }}
				    </div>
				</div>
				@endif			
				@if ($errors->any())
				<div class="col-sm-12" style="margin-top:20px;">
				    <div class="alert alert-block alert-danger">
				    	<button type="button" class="close" data-dismiss="alert">
							<i class="ace-icon fa fa-times"></i>
						</button>
						<ul>
							@foreach ($errors->all() as $error)
								<li><i class="fa fa-warning"></i> {{ $error }}</li>
							@endforeach
						</ul>
				    </div>
				</div>
				@endif				
				
				@csrf
				<div class="form-group">
					<div class="content-detail">
						<div class="section-block-detail">
							<div class="section-header-detail">
								&nbsp;<h2 class="section-title-detail"><i class="fa fa-bars"></i> Add Demand Note Detail</h2>
							</div><br>
							<div class="table-responsive">
							<table class="table table-bordered font-14" border="1" style="text-transform:none!important; width:500px;">
								<tr>
									<td class="padding-10 v-middle">Demand Note Raised*</td>
									<td class="padding-10">
										<input type="file" class="" name="demandnotefile" id="demandnotefile" accept="application/pdf" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}" data-width="200" style="width:150px!important;" />
									</td>
								</tr>
								<tr>
									<td class="padding-10 v-middle" style="width:200px;">Demand Note Number*</td>
									<td class="padding-10">
									<input type="text" name="demandnotenumber" id="demandnotenumber" value="{{old('demandnotenumber')}}" style="width:100%;" placeholder="Demand note number">
									</td>
								</tr>
								<tr>
									<td class="padding-10 v-middle">Demand Note Amount*</td>
									<td class="padding-10">
									<input type="text" class="numbers" name="demandnoteamount" id="demandnoteamount" value="{{old('demandnoteamount')}}" style="width:100%;" placeholder="0">
									</td>
								</tr>
								<tr>
									<td class="padding-10 v-middle">Amount Received</td>
									<td class="padding-10">
										<input type="text" class="numbers" name="amountreceived" id="amountreceived" value="{{old('amountreceived')}}" style="width:100%;" placeholder="0">
									</td>
								</tr>
								<tr>
									<td class="padding-10 v-middle">Transaction Detail</td>
									<td class="padding-10">
										<input type="text" name="transactiondetail" id="transactiondetail" placeholder="Transaction detail" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}" style="width:100%;" autocomplete="off" value="{{old('transactiondetail')}}">
									</td>
								</tr>
								<tr>
									<td class="padding-10 v-middle">Transaction File</td>
									<td class="padding-10">
										<input type="file" name="paymentfile" id="paymentfile" accept="application/pdf" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}" data-width="200" style="width:150px!important;" />
									</td>
								</tr>
								<tr>
									<td class="padding-10 v-middle">Payment Period (in months)</td>
									<td class="padding-10">
										<select name="paymentperiod" id="paymentperiod" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}" style="width:100%;">
											<option value="">--Payment Period--</option>
											@for($i=1;$i<=$order->projectduration;$i++)
											<option value="{{$i}}">{{$i}} Months</option>
											@endfor
										</select>

									</td>
								</tr>
								<tr>
									<td class="padding-10 v-middle">Payment Date</td>
									<td class="padding-10">
										<input type="text" name="payment_date" id="payment_date" class="todays_dt_blank" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}" style="width:100%;" autocomplete="off" value="{{old('payment_date')}}" placeholder="dd-mm-YYYY">
									</td>
								</tr>
								<tr>
									<td class="padding-10 v-middle">Payment Remark</td>
									<td class="padding-10">
										<input type="text" class="" name="payment_remark" id="payment_remark" value="{{old('payment_remark')}}" style="width:100%;" placeholder="Payment remark.." autocomplete="off" tabindex="{{$t++}}">
									</td>
								</tr>
								
								<tr>
									<td colspan="2" class="padding-10 v-middle" style="text-align:right;">
										
										<button type="button" class="btn btn-info" tabindex="{{$t++}}" style="float:left;" onclick="history.back()">
											<i class="fa fa-arrow-left"></i> Back
										</button>
										<button type="submit" class="btn btn-info" tabindex="{{$t++}}">
											<i class="fa fa-save"></i> Submit
										</button>
									</td>
								</tr>
							</table>
							</div>
						</div>
						<div class="section-block-detail">
							<div class="section-header-detail">
								<i class="material-icons-outlined">topic</i> {{ $order->projecttitle }}
							</div>
							<table class="table table-bordered table-striped table-hover mytable" border="1" style="text-transform:none!important;">
								<tr>
									<td style="line-height:30px;">
										<input type="hidden" name="form_token" id="form_token" value="{{$token}}">
										<b>Department</b><br>
										{{ ucwords(strtolower($order->departmentname)) }}<br>
										EoI Number: <span class="form-label">{{$order->eoinumber}}</span><br>
										Requested On : <span class="form-label">{{ date('d\-m\-Y, h:i A',strtotime($order->creationdate)) }}<br>
										Project Duration : {{ ucwords(strtolower($order->projectduration)) }} Months
									</td>
									<td style="line-height:30px;">
										<b>Vendor</b><br>
										{{ ucwords(strtolower($order->companyname)) }}<br>
										Order Number : <span class="form-label">{{$order->ordernumber}}</span><br>
										Order Date : {{date('d\-m\-Y',strtotime($order->orderdate))}}
									</td>
								</tr>
							</table>
						</div>


<div class="col-sm-12">
	

	
</div>
						
						
						
						
					</div>
				

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
jQuery(function($) {	

	$('#paymentfile').ace_file_input({
		no_file:'No File ...',
		btn_choose:'Upload Payment File',
		btn_change:'Change',
		btn_name:'fourthimage',
		thumbnail:false //| true | large
	});
	$('#demandnotefile').ace_file_input({
		no_file:'No File ...',
		btn_choose:'Upload Demand Note File*',
		btn_change:'Change',
		btn_name:'fourthimage',
		thumbnail:false //| true | large
	});
});
</script>


<script src="{{ asset('panel/assets/js/datatablePaginationWithFilter.js') }}"></script>
@endsection