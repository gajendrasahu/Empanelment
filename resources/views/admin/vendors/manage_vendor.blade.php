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
				<a data-toggle="tab" href="#home" class="form-label font-14 v-middle">
					<i class="fa fa-book" style="margin-top:3px;"></i> Manage Order
				</a>
			</li>
			<li class="pull-right" >
			<span  class="form-label font-12 v-middle lh-40">
				No : {{$order->ordernumber}}, Dated : {{ date('d\-m\-Y',strtotime($order->orderdate)) }}&nbsp;&nbsp;
			</span>
			</li>
		@endif
		</ul>

	
		<div class="tab-content no-border" style="border:1px solid #ddd; min-height:100px; padding:0px 10px;">
@if(in_array(1,Session::get('actions')))
<div id="home" class="tab-pane in active" style="padding:0px 10px!important;">
	<div class="row">
		
			<form name="participate" id="participate" action="" method="post" enctype="multipart/form-data">
				@if(Session::has('success'))
				<br>
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
				<br>
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
				<br>
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
						@if(!session('subUserId'))
						<div class="col-sm-12 mt-15">
						<div class="ui-card-detail">
							<div>
								<div class="title-detail">
									<i class="fa fa-user"></i> Assign Order to User (Optional)
								</div>
								<div class="details-detail lh-25" style="display: flex; align-items: center; gap: 10px;">
								<select name="subuserid" id="subuserid" style="width:300px; min-width:300px;">
									<option value="">--Select Name--</option>
									@foreach($subusers as $subuser)
									<option value="{{$subuser->userid}}" @if($subuser->userid==$order->subuserid) selected @endif>{{$subuser->name}}</option>
									@endforeach
								</select>
			@if($order->subuserid)
			<button type="button" class="btn btn-info myfrmbtn assignBtn" onclick="AssignUser('{{Crypt::encrypt($order->orderid)}}')">Move to Other User</button>
			@else
			<button type="button" class="btn btn-info myfrmbtn assignBtn" onclick="AssignUser('{{Crypt::encrypt($order->orderid)}}')">Submit</button>
			@endif
								</div>
							</div>							
						</div>
						</div>
						@endif


					<div class="col-sm-12 mt-15">
						<div class="manage-card-container">
							<div class="manage-card">Order Value (Including tax)<br><span><i class="fa fa-inr"></i> {{$order->workorderamount}}</span></div>
							<div class="manage-card">Invoiced Value (Including tax)<br><span><i class="fa fa-inr"></i> {{$order->invoiced}}</span></div>
							<div class="manage-card">Paid Amount<br><span><i class="fa fa-inr"></i> 0</span></div>
							<div class="manage-card">Resource Deployed<br>
								<label class="font-12">Pending-{{$order->pending_count}}</label> | <label class="font-12">Active-{{$order->active_count}}</label> | <label class="font-12">Released-{{$order->released_count}}</label>
							</div>
							
						</div>					
					</div>
					<div class="content-detail">
						<div class="section-block-detail" style="display:flex; align-items:center; justify-content:space-between;">
							
							<div style="display:flex; align-items:center; gap:10px;">
								<span class="material-icons-outlined">checklist</span>
								<span class="font-14">Please upload attendance data and Monthly Progress Reports (MPRs) of your resources here.</span>
							</div>

							<button type="button" class="btn btn-info"
								onclick="loadMonths('{{route('mpr.months')}}','{{Crypt::encrypt($order->orderid)}}')">
								<i class="ace-icon fa fa-calendar bigger-120"></i> Upload MPR & Attendance
							</button>							
						</div>
					</div>
					<div class="content-detail">
						<div class="section-block-detail" style="display:flex; align-items:center; justify-content:space-between;">
							<div style="display:flex; align-items:center; gap:10px;">
								<span class="material-icons-outlined">receipt_long</span>
								<span class="font-14">Vendors must submit invoices for MPRs that have been verified.</span>
							</div>
								<button type="button" class="btn btn-info" onclick="loadPendingInvoices('{{route('pending.invoices')}}','{{Crypt::encrypt($order->orderid)}}')">
									<i class="ace-icon fa fa-inr bigger-120"></i> Generate Invoice
								</button>
							
						</div>
					</div>
					<div class="col-sm-12" style="text-align:right;">
						<a href="{{route('vendor.orderslist')}}">
						<button type="button" class="btn btn-info"><i class="fa fa-arrow-left"></i> Back</button>
						</a>
					</div>
					<br>
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

<div class="modal fade" id="paymentHistory" style="margin-top:-20px;">
<form name="storeattendance" id="storeattendance" action="" method="post" enctype="multipart/form-data">
  <div class="modal-dialog modal-xl" style="width:95%;">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title form-label" style="float:left;"><i class="fa fa-calendar"></i> Monthly Progress Report — Generate or Update MPRs</h5>    
      </div>
      <div class="modal-body" id="paymentContent" style="min-height:200px; max-height:450px; overflow-y:scroll;">Loading...</div>
      <div class="modal-footer">
        <button type="button" class="btn btn-close btn-info gridbtn" style="width:80px;" data-bs-dismiss="modal"><i class="fa fa-remove"></i> Close</button>
      </div>
    </div>
  </div>
</form>
</div>

<div class="modal fade" id="invoiceHistory" style="margin-top:-20px;">
<form name="invoicing" id="invoicing" action="{{route('prepare.vendorinvoice')}}" method="post" enctype="multipart/form-data">
@csrf
  <div class="modal-dialog modal-xl" style="width:95%;">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title form-label invoice-title" style="float:left;"><i class="fa fa-calendar"></i> Monthly Progress Report — Generate or Update MPRs</h5>    
      </div>
      <div class="modal-body" id="invoiceContent" style="min-height:200px; max-height:450px; overflow-y:scroll;">Loading...</div>
      <div class="modal-footer invoice-footer">
        <button type="button" class="btn btn-close btn-info gridbtn" style="width:80px;" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</form>
</div>

<div class="modal fade" id="mprDetail" style="margin-top:-20px;">
<form name="updatempr" id="updatempr" action="" method="post" enctype="multipart/form-data">
  <div class="modal-dialog modal-xl" style="width:95%;">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title form-label" style="float:left;"><i class="fa fa-calendar"></i> Update MPR Detail</h5>    
      </div>
      <div class="modal-body" id="mprContent" style="min-height:200px; max-height:450px; overflow-y:scroll;">Loading...</div>
      <div class="modal-footer">
        <button type="button" class="btn btn-close btn-info gridbtn" style="width:80px;" data-bs-dismiss="modal"><i class="fa fa-remove"></i> Close</button>
      </div>
    </div>
  </div>
</form>
</div>

<script src="{{ asset('panel/assets/js/jquery-2.1.4.min.js') }}"></script>
<script>
function loadMonths(r1,orderid)
{
	$.get(""+r1,
	{
		orderid:orderid,
	},
	function(data, status){
		if(data.status==400)
		{
			bootbox.alert(data.message);
		}
		else if(data.status==500)
		{
			bootbox.alert(data.message);
		}
		else
		{
			$('#paymentHistory').modal('show');
			$('#paymentContent').html(data.data);
		}
	});
}


function loadPendingInvoices(r1,orderid)
{
	$.get(""+r1,
	{
		orderid:orderid
	},
	function(data, status){
		if(data.status==400)
		{
			bootbox.alert(data.message);
		}
		else if(data.status==500)
		{
			bootbox.alert(data.message);
		}
		else
		{
			$('#invoiceHistory').modal('show');
			$('#invoiceContent').html(data.data);
		}
	});
}

function viewMprDetail(r1,mprid)
{
	$.get(""+r1,
	{
		mprid:mprid
	},
	function(data, status){
		if(data.status==400)
		{
			bootbox.alert(data.message);
		}
		else if(data.status==500)
		{
			bootbox.alert(data.message);
		}
		else
		{
			$('#invoiceHistory').modal('show');
			$('#invoiceContent').html(data.data);
		}
	});
}

$('.btn-close').on('click', function (e)
{
	$('#paymentContent').html('');
	$('#paymentHistory').modal('hide');

	$('#invoiceContent').html('');
	$('#invoiceHistory').modal('hide');
});


@if(!session('subUserId'))
function AssignUser(orderid)
{
	$(".assignBtn").css("display","none");
	bootbox.confirm('Do you confirm this action?',function(result){
		if(result)
		{
			var subuserid	=	document.getElementById("subuserid").value;
			var form = $('#participate')[0];
			var formData = new FormData(form);
			formData.append('orderid',orderid);
			formData.append('subuserid',subuserid);
			$.ajax({
				url: '{{route("assignorderto.user")}}',
				method: 'POST',
				data: formData,
				processData: false,
				contentType: false,
				success:function(response)
				{
					if(response.status===200)
					{
						bootbox.alert(response.message);
						setTimeout(function(){ window.location.reload(); },3000);
					}
					else
					{
						$(".assignBtn").css("display","");
						bootbox.alert(response.message);
					}
				},
				error: function(xhr)
				{
					$(".assignBtn").css("display","");
					let errors	=	xhr.responseJSON?.errors;
					let message	=	'';
					$.each(errors, function(key, val)
					{
						message += '<i class="fa fa-hand-o-right"></i> '+ val + '<br>';
					});
					bootbox.alert(message);
				}
			});
		}
		else
		{
			$(".assignBtn").css("display","");
		}
	});
}
@endif

</script>
<script src="{{ asset('panel/assets/js/datatablePaginationWithFilter.js') }}"></script>
@endsection