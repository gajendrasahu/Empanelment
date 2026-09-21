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
					<i class="green ace-icon fa fa-plus-circle bigger-120 datalist" style="vertical-align:text-top;"></i> Add Order Against EoI
					<p>{{$eoi->eoinumber}}</p>
				</a>
			</li>
		@endif
		</ul>

	
		<div class="tab-content no-border" style="border:1px solid #ddd; min-height:100px; padding:0px 10px;">
@if(in_array(1,Session::get('actions')))
<div id="home" class="tab-pane in active" style="padding:0px 10px!important;">
	<div class="row">
		<div class="row">
			<form name="frm" id="frm" action="{{ route('store.neworder',Crypt::encrypt($eoi->requestid))}}" method="post" enctype="multipart/form-data">
				@csrf
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
				
				@csrf
				<div class="form-group">
					<div class="content-detail">
						<div class="ui-card-detail">
							<div>
								<div class="title-detail">
									<i class="material-icons-outlined">topic</i> {{ $eoi->projecttitle }}
								</div>
								<div class="details-detail lh-30">
									<b>Department / Project Manager : </b>{{ ucwords(strtolower($eoi->departmentname)) }}<br>
									
								</div>
							</div>
						</div>

						<div class="ui-card-detail">
							<div>
								<div class="title-detail">
									<i class="material-icons-outlined">contact_mail</i> {{ ucwords(strtolower($vendor->companyname)) }} [{{$vendor->tiername}}]
								</div>
								<div class="details-detail lh-30">
									<b>Address : </b>{{ $vendor->officelocation }}<br>
									
								</div>
							</div>
						</div>
						
						<div class="section-block-detail">
							<div class="section-header-detail" style="position:relative;">
								<span class="material-icons">playlist_add_check</span>
								<h2 class="section-title-detail">&nbsp;Existing Orders</h2>								
							</div>
							<div class="grid-layout-detail cols-4" style="padding:0px 0px; gap:0px;">
								<table class="mytable" border="1" style="text-transform: none!important; margin-top:0px;">
									<tr class="myheadbg">
										<td class="padding-5 form-label center" style="width:30px;">S.No.</td>
										<td class="padding-5 form-label">Order Number</td>
										<td class="padding-5 form-label center">Order Date</td>
										<td class="padding-5 form-label center">Order Due Date</td>
										<td class="padding-5 form-label center">Work Order Amount</td>
										<td class="padding-5 form-label center"></td>
										<td class="padding-5 form-label center"></td>
									</tr>
									<tbody>
									@foreach($orders as $order)
									<tr>
										<td class="padding-5 center">{{$loop->iteration}}</td>
										<td class="padding-5">{{$order->ordernumber}}</td>
										<td class="padding-5 center">{{date('d\-m\-Y',strtotime($order->orderdate))}}</td>
										<td class="padding-5 center">{{date('d\-m\-Y',strtotime($order->workorderduedate))}}</td>
										<td class="padding-5 center form-label format-indian" data-value="{{ number_format($order->workorderamount,'2','.','') }}"></td>
										<td class="padding-5 center">
											<a href="{{ route('view.uploadedfile',Crypt::encrypt($order->signedcopy))}}" target="_blank">
												<span class="label label-white middle animated flipInX delay-02 f-s-10 form-label span-content">
													<i class="fa fa-file-o"></i> View File
												</span>
											</a>
										</td>
										<td class="padding-5 center">
											<span class="label label-white middle animated flipInX delay-02 f-s-10 form-label span-content" onclick="viewResource('{{route('view.orderresource')}}','{{Crypt::encrypt($order->orderid)}}')">
												<i class="fa fa-users"></i> View Resource
											</span>
										</td>
									</tr>
									@endforeach
									</tbody>
								</table>

							</div>
							
						</div>
						@if($isavailable>0 && $resources->count()!=0)
						<div class="section-block-detail">
							<div class="section-header-detail">
								<span class="material-icons">bookmark_add</span>
								<h2 class="section-title-detail">&nbsp;Resources Awaiting Deployment</h2>
							</div>
							<div class="grid-layout-detail cols-4" style="padding:0px 0px; gap:0px;">
							@if($eoi->categoryid==2)
							<table class="mytable" border="1" style="text-transform: none!important; margin-top:0px;">
								<tr class="myheadbg">
									<td class="padding-5 form-label center" style="width:30px;">
										<input type="checkbox" class="chkAll">
									</td>
									<td class="padding-5 form-label">Sector</td>
									<td class="padding-5 form-label">Position</td>
									<td class="padding-5 form-label">Name</td>
									<td class="padding-5 form-label">Mobile Number</td>
									<td class="padding-5 form-label">Email</td>
									<td class="padding-5 form-label center">Work Order Issuance Date</td>
									<td class="padding-5 form-label center">Status</td>
									<td class="padding-5 form-label" style="text-align:right;">Remuneration</td>
								</tr>
								<tbody>
								@foreach($resources as $resource)
								<tr>
									<td class="padding-5 center">
										<input type="checkbox" name="deploymentid[]" value="{{$resource->deploymentid}}">
									</td>
									<td class="padding-5">{{ucwords(strtolower($resource->sectorname))}}</td>
									<td class="padding-5">{{ucwords(strtolower($resource->consultantposition))}}</td>
									<td class="padding-5">{{$resource->name}}</td>
									<td class="padding-5">{{$resource->mobilenumber}}</td>
									<td class="padding-5">{{$resource->email}}</td>
									<td class="padding-5 center">@if($resource->deployment_date!=NULL && $resource->deployment_date!='1970-01-01'){{date('d\-m\-Y',strtotime($resource->deployment_date))}}@endif</td>
									<td class="padding-5 center">{{$resource->deployment_status}}</td>
									<td class="padding-5 form-label format-indian" style="text-align:right;" data-value="{{ number_format($resource->remuneration,'2','.','') }}"></td>
								</tr>
								@endforeach								
								</tbody>
							</table>
							<br>
							<table class="mytable" border="1" style="text-transform: none!important; margin-top:0px; width:580px!important;">
								<tr class="myheadbg">
									<td colspan="4" class="padding-5 form-label font-14">
										<i class="fa fa-file-o font-16"></i> Order Related Document
									</td>
								</tr>
								<tr>
									<td class="padding-5">Attach Note Sheet</td>
									<td class="padding-5">
										<input type="file" class="form-control" name="notesheet" id="notesheet" accept="application/pdf" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}" data-width="200px" />
									</td>
								</tr>
								<tr>
									<td class="padding-5" nowrap>Attach a Signed Order Copy*</td>
									<td class="padding-5">
										<input type="file" class="form-control" required name="signedcopy" id="signedcopy" accept="application/pdf" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}" />
									</td>
								</tr>
								<tr>
									<td class="padding-5">Order Number Prefix*</td>
									<td class="padding-5">
										<input type="text" class="form-control" required name="orderno" id="orderno" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}" value="{{old('orderno')}}" placeholder="Order number prefix" autocomplete="off" style="width:100px; float:left;" /><span style="float:left; margin-left:5px; margin-top:5px;">/</span>
										<input type="text" class="form-control" required name="ordernumber" id="ordernumber" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}" value="{{old('ordernumber')}}" placeholder="Order number" autocomplete="off" style="width:250px; float:left; margin-left:5px;" />
									</td>
								</tr>
								
								<tr>
									<td class="padding-5" style="width:200px;">Order Date*</td>
									<td class="padding-5">
										<input type="text" name="orderdate" id="orderdate" required class="order_date" placeholder="dd-mm-YYYY" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}" style="margin-top:-1px!important; width:150px;" autocomplete="off" value="{{old('orderdate')}}">
										
									</td>
								</tr>
								<tr>
									<td class="padding-5" style="width:200px;">Work Order Duration*</td>
									<td class="padding-5">
										<select class="form-control" name="project_duration" id="project_duration" required onKeyPress="return OnKeyPress(this, event)"  data-placeholder="Project Duration" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}" style="width:150px;">
											<option value="">--Order Duration--</option>
											@for($i=3;$i<=60; $i++)
												<option value="{{ $i }}">{{ $i }} Months</option>
											@endfor
										</select>
									</td>
								</tr>
								<tr>
									<td colspan="4" class="padding-5" style="text-align:right;">
										<button type="button" class="btn btn-info submitBtn" style="width:150px;" tabindex="{{$t++}}">
											<i class="fa fa-save"></i> SUBMIT ORDER
										</button>
									</td>
								</tr>
							</table>
							@elseif($eoi->categoryid==1)
							<table class="mytable" border="1" style="text-transform: none!important; margin-top:0px;">
								<tr class="myheadbg">
									<td class="padding-5 form-label center" style="width:30px;">
										<input type="checkbox" class="chkAll">
									</td>
									<td class="padding-5 form-label">Role</td>
									<td class="padding-5 form-label">Experience</td>
									<td class="padding-5 form-label">Name</td>
									<td class="padding-5 form-label">Mobile Number</td>
									<td class="padding-5 form-label">Email</td>
									<td class="padding-5 form-label center">Work Order Issuance Date</td>
									<td class="padding-5 form-label center">Status</td>
									<td class="padding-5 form-label" style="text-align:right;">Remuneration</td>
								</tr>
								<tbody>
								@foreach($resources as $resource)
								<tr>
									<td class="padding-5 center">
										<input type="checkbox" name="deploymentid[]" value="{{$resource->deploymentid}}">
									</td>
									<td class="padding-5">{{ucwords(strtolower($resource->role))}}</td>
									<td class="padding-5">{{$resource->experience}}<br>[{{$resource->experiencelevel}}]</td>
									<td class="padding-5">{{$resource->name}}</td>
									<td class="padding-5">{{$resource->mobilenumber}}</td>
									<td class="padding-5">{{$resource->email}}</td>
									<td class="padding-5 center">@if($resource->deployment_date!=NULL && $resource->deployment_date!='1970-01-01'){{date('d\-m\-Y',strtotime($resource->deployment_date))}}@endif</td>
									<td class="padding-5 center">{{$resource->deployment_status}}</td>
									<td class="padding-5 form-label format-indian" style="text-align:right;" data-value="{{ number_format($resource->remuneration,'2','.','') }}"></td>
								</tr>
								@endforeach								
								</tbody>
							</table>
							<br>
							<table class="mytable" border="1" style="text-transform: none!important; margin-top:0px; width:580px!important;">
								<tr class="myheadbg">
									<td colspan="4" class="padding-5 form-label font-14">
										<i class="fa fa-file-o font-16"></i> Order Related Document
									</td>
								</tr>
								<tr>
									<td class="padding-5">Attach Note Sheet</td>
									<td class="padding-5">
										<input type="file" class="form-control" name="notesheet" id="notesheet" accept="application/pdf" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}" data-width="200px" />
									</td>
								</tr>
								<tr>
									<td class="padding-5" nowrap>Attach Order Signed Copy*</td>
									<td class="padding-5">
										<input type="file" class="form-control" required name="signedcopy" id="signedcopy" accept="application/pdf" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}" />
									</td>
								</tr>
								<tr>
									<td class="padding-5">Order Number Prefix*</td>
									<td class="padding-5">
										<input type="text" class="form-control" required name="orderno" id="orderno" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}" value="{{old('orderno')}}" placeholder="Order number prefix" autocomplete="off" style="width:100px; float:left;" /><span style="float:left; margin-left:5px; margin-top:5px;">/</span>
										<input type="text" class="form-control" required name="ordernumber" id="ordernumber" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}" value="{{old('ordernumber')}}" placeholder="Order number" autocomplete="off" style="width:250px; float:left; margin-left:5px;" />
									</td>
								</tr>
								
								<tr>
									<td class="padding-5" style="width:200px;">Order Date*</td>
									<td class="padding-5">
										<input type="text" name="orderdate" id="orderdate" required class="order_date" placeholder="dd-mm-YYYY" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}" style="margin-top:-1px!important; width:150px;" autocomplete="off" value="{{old('orderdate')}}">
										
									</td>
								</tr>
								<tr>
									<td class="padding-5" style="width:200px;">Work Order Duration*</td>
									<td class="padding-5">
										<select class="form-control" name="project_duration" id="project_duration" required onKeyPress="return OnKeyPress(this, event)"  data-placeholder="Project Duration" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}" style="width:150px;">
											<option value="">--Order Duration--</option>
											@for($i=3;$i<=60; $i++)
												<option value="{{ $i }}">{{ $i }} Months</option>
											@endfor
										</select>
									</td>
								</tr>
								<tr>
									<td colspan="4" class="padding-5" style="text-align:right;">
										<button type="button" class="btn btn-info submitBtn" style="width:150px;" tabindex="{{$t++}}">
											<i class="fa fa-save"></i> SUBMIT ORDER
										</button>
									</td>
								</tr>
							</table>
							
							@endif
							</div>

						</div>
						
						@endif
						<!-- INCLUDE OBJECTIVE ABOUT SCOPE CONTENT -->

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

<div class="modal fade" id="resourceModal" style="margin-top:-20px;" data-backdrop="static">
  <div class="modal-dialog modal-xl" style="width:95%;">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title form-label" style="float:left;">Resource Detail</h5>    
      </div>
      <div class="modal-body" id="resourceContent" style="min-height:200px; max-height:450px; overflow-y:scroll;">Loading...</div>
      <div class="modal-footer">
        <button type="button" class="btn btn-close" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<script src="{{ asset('panel/assets/js/jquery-2.1.4.min.js') }}"></script>
<script src="{{ asset('panel/assets/js/ace-elements.min.js') }}"></script>
<script src="{{ asset('panel/assets/js/ace.min.js') }}"></script>

<script>

jQuery(function($) {	

	$('#notesheet').ace_file_input({
		no_file:'No File ...',
		btn_choose:'Upload Note Sheet',
		btn_change:'Change',
		btn_name:'fourthimage',
		thumbnail:false
	});
	$('#signedcopy').ace_file_input({
		no_file:'No File ...',
		btn_choose:'Attach a Signed Order Copy*',
		btn_change:'Change',
		btn_name:'fourthimage',
		thumbnail:false
	});
	
	$(".ace-file-container").css("width","370px");
});


$(document).ready(function() {
    $('.chkAll').change(function()
	{
        if ($(this).prop('checked'))
		{
            $('input[name="deploymentid[]"]').prop('checked', true);
        }
		else
		{
            $('input[name="deploymentid[]"]').prop('checked', false);
        }
    });
});

$('.btn-close').on('click', function (e)
{
	$('#resourceContent').html('');
	$('#resourceModal').modal('hide');
});

function viewResource(r1,orderid)
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
			$('#resourceModal').modal('show');
			$('#resourceContent').html(data.formhtml);
		}
	});
}

$('.submitBtn').on('click', function(e) {
	e.preventDefault();

	var orderno			=	document.getElementById("orderno").value;
	var ordernumber		=	document.getElementById("ordernumber").value;
	var project_duration=	document.getElementById("project_duration").value;

    var deploymentSelected = $('input[name="deploymentid[]"]:checked').length > 0;
    if (!deploymentSelected) {
        bootbox.alert("At least one resource must be selected to create the order.");
        return false;
    }	
	
	var signedcopy = $('#signedcopy').get(0).files.length > 0;
	if (!signedcopy) {
		bootbox.alert("A signed copy of the order is mandatory.");
		return false;
	}
	if(!orderno)
	{
		bootbox.alert("Enter order number prefix value.");
		return false;			
	}
	if(!ordernumber)
	{
		bootbox.alert("Enter order number.");
		return false;			
	}
	if(!project_duration)
	{
		bootbox.alert("Select work order duration.");
		return false;			
	}
	bootbox.confirm('Do you want to submit this order?',function(result){
		if(result)
		{
			document.forms['frm'].submit();				
		}
	});
});

</script>

<script src="{{ asset('panel/assets/js/datatablePaginationWithFilter.js') }}"></script>
<script>
document.querySelectorAll('.format-indian').forEach(function(el) {
    el.innerText = formatIndianNumber(el.dataset.value);
});

</script>
@endsection