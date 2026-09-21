@extends('admin.admin_master')
@section('admin')
@php
$t=0;
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
				<a data-toggle="tab" href="#home" class="form-label">
					<i class="green ace-icon fa fa-plus-circle bigger-120 datalist" style="vertical-align:text-top;"></i> Work Order History
					<p>Track every update and milestone in this work order’s lifecycle.</p>
				</a>
			</li>
		@endif
		</ul>

	
		<div class="tab-content no-border" style="border:1px solid #ddd; min-height:100px; padding:0px 10px;">
@if(in_array(1,Session::get('actions')))
<div id="home" class="tab-pane in active" style="padding:0px 10px!important;">
	<div class="row">
		<div class="row">
			<form name="frm" id="frm" action="{{ route('update.draft',Crypt::encrypt($order->orderid))}}" method="post" enctype="multipart/form-data">
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
						{{ Session::get('fail') }}
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
								<li>{{ $error }}</li>
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
								&nbsp;<h2 class="section-title-detail"><i class="fa fa-bars"></i> Demand Notes</h2>
							</div>
							<div class="table-responsive">
							<table class="table table-bordered table-striped table-hover mytable" border="1" style="text-transform: none!important;">
								<thead>
								<tr class="font-14">
									<td class="padding-5 center" style="width:50px;">S.No.</td>
									<td class="padding-5" nowrap>Demand Note Number</td>
									<td class="padding-5 center" nowrap>Demant Note Amount</td>
									<td class="padding-5 center" nowrap>Received Amount</td>
									<td class="padding-5 center" nowrap>Balance Amount</td>
									<td class="padding-5 center" nowrap>Next Due Date</td>
									<td class="padding-0 center v-middle" style="width:100px;">
										<a href="{{ route('add.demandnote',Crypt::encrypt($order->orderid)) }}">
											<button type="button" class="btn btn-info">
												<i class="fa fa-plus-circle"></i> Add Demand Note
											</button>
										</a>										
									</td>
								</tr>
								</thead>
								<tbody>
								@foreach($demand_notes as $notes)
								<tr class="font-14">
									<td class="center">{{$loop->iteration}}</td>
									<td class="">{{$notes->demandnotenumber}}<br>
									@if($notes->demandnotefile!='')
									<a href="{{route('view.uploadedfile',Crypt::encrypt($notes->demandnotefile))}}" target="_blank" class="action-a">
										<span class="label label-white middle animated flipInX delay-02 f-s-10 form-label span-content" style="">
											<i class="fa fa-file-pdf-o"></i> Demand Note
										</span>
									</a>
									@endif
									@if($notes->paymentfile!='')
									<a href="{{ asset('storage/'.$notes->paymentfile) }}" target="_blank" class="action-a">
										<span class="label label-white middle animated flipInX delay-02 f-s-10 form-label span-content" style="">
											<i class="fa fa-file-pdf-o"></i> Payment File
										</span>
									</a>
									@endif
									</td>
									<td class="center"><i class="fa fa-inr"></i> {{$notes->demandnoteamount}}</td>
									<td class="center"><i class="fa fa-inr"></i> {{$notes->amountreceived}}</td>
									<td class="center"><i class="fa fa-inr"></i> {{$notes->balance}}</td>
									<td nowrap class="center">
										@if($notes->next_payment_due_date!='0000-00-00' && $notes->next_payment_due_date!=NULL)
											{{ date('d\-m\-Y',strtotime($notes->next_payment_due_date)) }}
										@endif
										@if($notes->days_to_expire!=0)
										<label @if($notes->days_to_expire<60) class="alert-warning" @endif style="width:80px; min-height:80px; line-height:20px; border:1px solid #ddd; border-radius:50px; vertical-align:middle!important; font-weight:normal; display: flex; align-items: center; justify-content: center; text-align: center; margin:10px auto;">
										@if($notes->days_to_expire>=0)
											{{ $notes->days_to_expire }}<br>days left
										@else
											Expired<br>{{ abs($notes->days_to_expire) }}<br>days ago
										@endif
										</label>
										@endif
									</td>
									
									<td class="center">
										<a href="{{ route('pay.demandnote',Crypt::encrypt($notes->paymentid)) }}">
											<span class="label label-white middle animated flipInX delay-02 f-s-10 form-label span-content">
												<i class="fa fa-inr"></i> Receive Balance
											</span>
										</a>
									</td>
								</tr>
								@endforeach
								@if($demand_notes->count()==0)
								<tr>
									<td class="padding-5 center" colspan="5">--No Record Found--</td>
								</tr>
								@endif
								</tbody>
							</table>
							</div>
						</div>
						<div class="section-block-detail">
							<div class="section-header-detail">
								<i class="material-icons-outlined">topic</i>&nbsp;<span class="form-label">{{ $order->projecttitle }}</span>
							</div>
							<table class="table table-bordered table-striped table-hover mytable" border="1" style="text-transform:none!important;">
								<tr>
									<td style="line-height:30px;">
										<b>Department</b><br>
										{{ ucwords(strtolower($order->departmentname)) }}<br>
										EoI Number: <span class="form-label">{{$order->eoinumber}}</span><br>
										Requested On : <span class="form-label">{{ date('d\-m\-Y, h:i A',strtotime($order->creationdate)) }}<br>
										Project Duration : {{ ucwords(strtolower($order->projectduration)) }} Months<br>

										<button type="button" class="btn btn-info" onclick="history.back()">
											<i class="fa fa-arrow-left"></i> Back
										</button>
										
										<button type="button" class="btn btn-info" onclick="viewDepartmentPaymentHistory('{{route('demandnote.paymenthistory')}}','{{Crypt::encrypt($order->orderid)}}')">
											<i class="fa fa-bars"></i> Department Payment History
										</button>
									</td>
									<td style="line-height:30px; position:relative;">
										<b>Vendor</b><br>
										{{ ucwords(strtolower($order->companyname)) }}<br>
										Order Number : <span class="form-label">{{$order->ordernumber}}</span><br>
										Order Date : {{date('d\-m\-Y',strtotime($order->orderdate))}}<br>

										
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

<div class="modal fade" id="paymentHistory">
  <div class="modal-dialog modal-xl" style="width:95%;">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" style="float:left;">Department Payment History</h5>
        <!-- Proper close icon button -->

		<select name="paymentid" id="paymentid" style="float:right;" onchange="filterDemandNotePayment('{{route('filter.demandnotepayment')}}',this.value)">
			<option value="">All Demand Note</option>
		</select>

        
      </div>
      <div class="modal-body" id="paymentContent" style="min-height:200px; max-height:400px; overflow-y:scroll;">
        Loading...
      </div>
      <div class="modal-footer">
        <!-- Text button to close -->
        <button type="button" class="btn btn-close" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<script src="{{ asset('panel/assets/js/jquery-2.1.4.min.js') }}"></script>
<script>

function viewDepartmentPaymentHistory(r1,orderid)
{
	$.get(""+r1,
	{
		orderid:orderid,
	},
	function(data, status){
		let options = '<option value="">All Demand Note</option>';
		$.each(data.notes, function(i, note) {
			options += `<option value="${note.paymentid}">${note.demandnotenumber ?? 'Note ' + note.paymentid}</option>`;
		});
		$('#paymentid').html(options);		
		$('#paymentHistory').modal('show');
		$('#paymentContent').html(data.data);
	});
}

function filterDemandNotePayment(r1,paymentid)
{
	var orderid	=	document.getElementById("orderid").value;
	$.get(""+r1,
	{
		orderid:orderid,
		paymentid:paymentid,
	},
	function(data, status){
		$('#paymentContent').html(data.data);
	});
}

$('.btn-close').on('click', function (e) {

	$('#paymentContent').html('');
	$('#paymentHistory').modal('hide');
});

</script>


<script src="{{ asset('panel/assets/js/datatablePaginationWithFilter.js') }}"></script>
@endsection