@extends('admin.admin_master')
@section('admin')
@php
$t=0;
@endphp

<div class="main-content">
<style>
.ace-file-input
{
	width:300px;
}
</style>
	<div class="main-content-inner">
			<div class="page-content" style="padding:10px 15px!important;">
				<div class="ace-settings-container" id="ace-settings-container"></div>
				<div class="row">
					<div class="col-xs-12">
					<!-- PAGE CONTENT BEGINS -->
<div class="tabbable animated fadeInUp delay-02" style="padding:0px 0px!important;">
		<ul class="nav nav-tabs padding-0">
		@permission('create.finalworkorder')
		<li class="active">
			<a data-toggle="tab" href="#home" class="form-label font-14">
				<i class="green ace-icon fa fa-building bigger-120" style="vertical-align:text-top;"></i>Work Order Creation
			</a>
		</li>
		@endpermission
		</ul>

	
		<div class="tab-content no-border" style="border:1px solid #ddd; min-height:100px; padding:0px 10px;">
@permission('create.finalworkorder')
<div id="home" class="tab-pane in active" style="padding:0px 10px!important;">
	<div class="row">
			<form name="workorderfrm" id="workorderfrm" action="{{ route('create.finalworkorder') }}" method="post" enctype="multipart/form-data">
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
					<div class="col-sm-12">
						<div class="alert alert-block alert-danger">
							<button type="button" class="close" data-dismiss="alert">
								<i class="ace-icon fa fa-times"></i>
							</button>
							@foreach ($errors->all() as $error)
								<i class="fa fa-warning"></i> {{ $error }}<br>
							@endforeach
						</div>
					</div>
				@endif		
				
				@csrf
				<div class="form-group">
					<div class="content-detail" style="position:relative;">
						<span>
							<i class="fa fa-info-circle"></i> Please do not refresh the page while the work order is being prepared.
						</span>
						@if($previousOrders->count()>0)
						<div class="section-block-detail">
							<div class="section-header-detail">
								<span class="material-icons-outlined">list</span>
								&nbsp;<h2 class="section-title-detail form-label">Previous Work Orders associated with EOI : {{$eoi->eoinumber}}</h2>
							</div>
							<table class="mytable pd-10" border="1" style="text-transform: none!important;">
								<tr class="myheadbg">
									<td class="center font-bold" style="width:30px;">S.No.</td>
									<td class="font-bold">Work Order Number</td>
									<td class="font-bold">Firm Name</td>
									<td class="font-bold center">Order Date</td>
									<td class="font-bold center">Order Expiry Date</td>
								</tr>
								@foreach($previousOrders as $ords)
								<tr>
									<td class="center">{{$loop->iteration}}</td>
									<td style="position:relative;">
										{{$ords->ordernumber}}
										@if($ords->signedcopy)
										<a href="{{route('view.uploadedfile',Crypt::encrypt($ords->signedcopy))}}" target="_blank" style="position:absolute; right:5px; outline:none;">
											<i class="fa fa-file-pdf-o"></i>
										</a>
										@endif
									</td>
									<td>
										{{$ords->companyname}}
									</td>
									<td class="center">{{date('d\-m\-Y',strtotime($ords->orderdate))}}</td>
									<td class="center">{{date('d\-m\-Y',strtotime($ords->workorderduedate))}}</td>
								</tr>
								@endforeach
							</table>
						</div>
						@endif

						<div class="ui-card-detail">
							<div>
						
							<table class="pd-3 font-12">
								<tr>
									<td class="font-bold">Firm Name</td>
									<td class="font-bold">Rate List</td>
									<td class="font-bold">Work Order Date</td>
									<td class="font-bold">Work Order Due Date</td>
								</tr>
								<tr>
									<td>
										<select name="vendorid" id="vendorid" class="width-250">
											<option value="">--Firm Name--</option>
											@foreach($vendors as $vendor)
											<option value="{{$vendor->vendorid}}">{{$vendor->companyname}}</option>
											@endforeach
										</select>
									</td>
									
									<td>
										<select name="rateid" id="rateid" class="width-250">
											<option value="">--Rate List--</option>
											@foreach($rateList as $rate)
											<option value="{{$rate->rateid}}" @if($rate->rateid==$rateid) selected @endif>{{$rate->ratelist_name}}</option>
											@endforeach
										</select>
									</td>
									
									<td>
										<input type="text" name="order_date" id="order_date" class="todays_dt_blank form-control" style="margin-top:0px;" placeholder="dd-mm-YYYY" autocomplete="off">
									</td>
									<td>
										<input type="text" name="order_duedate" id="order_duedate" class="todays_dt_blank form-control" style="margin-top:0px;" placeholder="dd-mm-YYYY" autocomplete="off">
									</td>
								</tr>
							</table>
							</div>
						</div>

						<div class="section-block-detail">
							<div class="section-header-detail">
								<span class="material-icons-outlined">group_add</span>
								&nbsp;<h2 class="section-title-detail">Resource Details</h2>
							</div>
							@if($eoi->categoryid==2)
							<table class="mytable pd-10 resourceTable" border="1" style="text-transform: none!important;">
								<tr class="myheadbg">
									<td class="center font-bold" style="width:30px;">S.No.</td>
									<td class="center font-bold" style="width:30px;">
										<input type="checkbox" class="chkAll">
									</td>
									<td class="font-bold">Sector Name</td>
									<td class="font-bold">Position</td>
									<td class="font-bold">Deployment Type</td>
									<td class="font-bold width-100">Start Date</td>
									<td class="font-bold width-100">End Date</td>
									@if($previousOrders->count()>0)
									<td class="font-bold center" style="width:120px;">Action</td>
									@endif									
								</tr>
								@php
								$totalmanmonth		=	0;
								$adminchargetotal	=	0;
								$grandtotal			=	0;
								@endphp
								<tbody class="resource-existing">
								@foreach($resumes as $index=>$req)
								<tr>
									<td class="center">{{$loop->iteration}}</td>
									<td><input type="checkbox" name="recordids[]" value="{{$req->recordid}}"></td>
									<td>{{ucwords(strtolower($req->sectorname))}}<br>{!!$req->remark!!}</td>
									<td>{{$req->consultantposition}}</td>
									<td>{{$req->employmenttype}}</td>
									<td>
									<input type="text" name="startDate[]" placeholder="dd-mm-YYYY" class="startDate todays_dt_blank width-100 form-control" autocomplete="off">
									</td>
									<td>
									<input type="text" name="endDate[]" placeholder="dd-mm-YYYY" class="endDate todays_dt_blank width-100 form-control" readonly autocomplete="off" onchange="CheckDueDate(this)">
									</td>
									@if($previousOrders->count()>0)
									<td class="center">-</td>
									@endif									
								</tr>
								@endforeach
								</tbody>
								@if($previousOrders->count()>0)
								<tbody id="dynamicResourceRows"></tbody>

								<tr>
									<td colspan="8" class="padding-10">
										<button type="button" class="btn btn-success btn-sm" id="addResourceBtn">
											<i class="fa fa-plus"></i> Add Resource
										</button>
									</td>
								</tr>
								@endif								
							</table>
							@else
							<table class="mytable pd-10 awdResourceTable" border="1" style="text-transform: none!important;">
								<tr class="myheadbg">
									<td class="center font-bold" style="width:30px;">S.No.</td>
									<td class="center v-top" style="width:30px;">
										<input type="checkbox" class="chkAll">
									</td>
									<td class="font-bold" nowrap>Role & Experience</td>
									<td class="font-bold" nowrap>Name & Level</td>
									<td class="font-bold" nowrap>Deployment Type</td>
									<td class="font-bold width-100" nowrap>Start Date</td>
									<td class="font-bold width-100" nowrap>End Date</td>
									@if($previousOrders->count()>0)
									<td class="font-bold center width-30">Action</td>
									@endif									
									
								</tr>
								@php
								$totalmanmonth		=	0;
								$adminchargetotal	=	0;
								$grandtotal			=	0;
								@endphp
								<tbody class="awdresource-existing">
								@foreach($resumes as $index=>$req)
								<tr>
									<td class="center v-top">{{$loop->iteration}}</td>
									<td class="center v-top">
										<input type="checkbox" name="recordids[]" value="{{$req->recordid}}">
									</td>
									<td class="v-top">{{$req->role}}@if($req->experience)<br>{{$req->experience}}@endif<br>{!!$req->remark!!}</td>
									<td class=" v-top">
									<input type="text" class="resource_name form-control" name="resource_name[]" placeholder="Name" readonly disabled autocomplete="off">

									<select name="exp_level[]" class="exp_level form-control" disabled>
									
										@foreach($levels as $level)
										@if($level->experiencelevel<=$req->experiencelevel)
										<option value="{{$level->experiencelevel}}" @if($level->experiencelevel==$req->experiencelevel) selected @endif>Level-{{$level->experiencelevel}}</option>
										@endif
										@endforeach
									</select>
									</td>
									<td class="v-top">
									{{$req->employmenttype}}
									</td>
									<td class="v-top">
									<input type="text" name="startDate[]" placeholder="dd-mm-YYYY" class="startDate todays_dt_blank width-100 form-control" autocomplete="off" readonly>
									</td>
									<td class="v-top">
									<input type="text" name="endDate[]" placeholder="dd-mm-YYYY" class="endDate todays_dt_blank width-100 form-control" readonly autocomplete="off" onchange="CheckDueDate(this)">
									</td>
									@if($previousOrders->count()>0)
									<td class="center width-30">-</td>
									@endif									
									
								</tr>
								@endforeach
								</tbody>
								@if($previousOrders->count()>0)
								<tbody id="dynamicResourceRowsAwd"></tbody>

								<tr>
									<td colspan="8" class="padding-10">
										<button type="button" class="btn btn-success btn-sm" id="addAwdResourceBtn">
											<i class="fa fa-plus"></i> Add Resource
										</button>
									</td>
								</tr>
								@endif								
							</table>
							@endif
							
							<div style="width:100%; position:relative; height:30px;">
								<button type="button" onclick="ViewOrderValue('{{route('show.workordervalue',Crypt::encrypt(0))}}')" class="btn btn-info gridbtn mt-5 width-150" style="position:absolute; right:0px;">
									<i class="fa fa-calculator"></i> View Order Value
								</button>
							</div>
							
						</div>
						<div class="section-block-detail">
							<div class="section-header-detail">
								<span class="material-icons-outlined">list</span>
								&nbsp;<h2 class="section-title-detail">Work Order Detail</h2>
							</div>
							<table class="mytable" border="1" style="text-transform: capitalize!important;">
								<tr>
									<td class="padding-10">MoM File*</td>
									<td class="padding-10">
										<input type="file" class="form-control" required name="momfile" id="momfile" accept="application/pdf" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}" />
									</td>
								</tr>
								<tr>
									<td class="padding-10">Marking Sheet*</td>
									<td class="padding-10">
										<input type="file" class="form-control" required name="markingsheet" id="markingsheet" accept="application/pdf" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}" />
									</td>
								</tr>
								<tr>
									<td class="padding-10">Note Sheet*</td>
									<td class="padding-10">
										<input type="file" class="form-control" required name="notesheet" id="notesheet" accept="application/pdf" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}" />
									</td>
								</tr>
								<tr>
									<td class="padding-10">Signed Order File*</td>
									<td class="padding-10">
										<input type="file" class="form-control" required name="signedcopy" id="signedcopy" accept="application/pdf" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}" />
									</td>
								</tr>
								
								<tr>
									<td class="padding-10">Subject*</td>
									<td class="padding-10">
										<input type="text" name="subject" id="subject" required placeholder="Subject" class="form-control" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}" style="margin-top:-1px!important; width:100%;" autocomplete="off" value="{{old('subject')}}">
									</td>
								</tr>
								<tr>
									<td class="padding-10">Reference*</td>
									<td class="padding-10">
										<input type="text" name="refrence" id="refrence" required placeholder="Reference" class="form-control" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}" style="margin-top:-1px!important; width:100%;" autocomplete="off" value="{{old('refrence',$eoi->eoinumber)}}">
										
										<input type="hidden" name="agreementdate" id="agreementdate" required class="" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}" style="margin-top:-1px!important;" autocomplete="off" value="2025-07-01" readonly>
									</td>
								</tr>
								<tr>
									<td class="padding-10" style="width:200px;">Signed By*</td>
									<td class="padding-10">
										<select name="signedby" id="signedby" required onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}" onchange="generateWorkOrderNumber('{{Crypt::encrypt($eoi->requestid)}}','{{route('generate.wonumber')}}','ordernumber')">
											<option value="">--Signed By--</option>
											<option value="Chief Executive Officer (CEO)">CEO</option>
											<option value="Chief Operating Officer">COO</option>
											<option value="Add. Chief Executive Officer">ACEO</option>
											<option value="Jt. CEO (Project)">JCEO(P)</option>
											<option value="Jt. CEO(Finance)">JCEO(F)</option>											
										</select>
										
									</td>
								</tr>			
								<tr>
									<td class="padding-10" style="width:200px;">Work Order Number.*</td>
									<td class="padding-10">
										<input type="hidden" name="requestid" id="requestid" value="{{old('requestid',Crypt::encrypt($eoi->requestid))}}">
										<input type="text" name="ordernumber" id="ordernumber" required placeholder="Order No." class="form-control" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}" style="margin-top:-1px!important; width:100%;" autocomplete="off" value="{{old('ordernumber')}}">
									</td>
								</tr>								
								<tr>
									<td class="padding-10" colspan="2" style="border-top:none!important;">
										<a href="{{ route('view.pptresumes',Crypt::encrypt($eoi->requestid))}}">
										<button type="button" class="btn btn-info no-hover" style="width:150px; float:left;">
											<i class="fa fa-arrow-left"></i> Back
										</button>
										</a>
@permission('create.finalworkorder')
<button type="button" class="btn btn-info generateorderBtn" style="width:auto; max-width:250px; float:right;" tabindex="{{$t++}}">
	<i class="fa fa-refresh"></i> Create Work Order
</button>
@endpermission
									
									</td>
								</tr>
								
								<tr><td colspan="2" class="padding-10">&nbsp;</td></tr>
							</table>
							
						</div>
					</div>
				
				
<div class="col-sm-12">&nbsp;</div>
				</div>		
			</form>
	</div>
</div>
@endpermission


</div>
</div>


						
				</div><!-- /.col -->
			</div><!-- /.row -->
		</div><!-- /.page-content -->
	
		
	</div>
</div>

<div class="modal fade" id="ordervalueModal">
  <div class="modal-dialog modal-xl" style="width:95%; margin-top:0px;">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" style="float:left;">Resource Cost by Duration</h5>
        <button type="button" class="btn btn-close" style="float:right;" data-bs-dismiss="modal" onclick="ClsResource()">Close</button>
      </div>
      <div class="modal-body" id="ordervalueContent">
        Loading...
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-close" data-bs-dismiss="modal" onclick="ClsResource()">Close</button>
      </div>
    </div>
  </div>
</div>

<script src="{{ asset('panel/assets/js/jquery-2.1.4.min.js') }}"></script>
<script src="{{ asset('panel/assets/js/ace-elements.min.js') }}"></script>
<script src="{{ asset('panel/assets/js/ace.min.js') }}"></script>
<script src="{{ asset('panel/assets/js/order.js') }}"></script>


<script>
function ClsResource()
{
	$('#ordervalueModal').modal('hide');
	$('#ordervalueContent').html("");
}

function ViewOrderValue(rl)
{
    if (!validateSelectedRows())
	{
        return false;
    }	
	if($('input[name="recordids[]"]:checked').length === 0)
	{
		bootbox.alert("Please select at least one record to view the work order value.");
		return;
	}	
	if(!$('#order_date').val())
	{
		bootbox.alert('Please enter order date.');
		return;				
	}
	
	var formData= 	new FormData($('#workorderfrm')[0]);
	
	bootbox.confirm('This will only accept complete data. Both the start date and end date must be selected; otherwise, it will not calculate incomplete data.',function(result)
	{
		if(result)
		{
			$.ajax({
				url: rl,
				type:'POST',
				data:formData,
				processData:false,
				contentType:false,
				headers: {
					'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
				},
				success: function (response) {
					$('#ordervalueModal').modal('show');
					$('#ordervalueContent').html(response.formhtml);
				},
				error: function (xhr) {
					if(xhr.responseJSON && xhr.responseJSON.errors)
					{
						var errors = xhr.responseJSON.errors;
						var allMessages = '';

						$.each(errors, function(field, messages) {
							$.each(messages, function(index, msg) {
								allMessages += msg + '<br>';
							});
						});
						bootbox.alert(allMessages);
						setTimeout(function() { $(".no-skin").css("padding-right",""); }, 5000);
					}
				}
			});
		}
	});
}

$('.generateorderBtn').on('click', function () {
	
    if (!validateSelectedRows()) {
        return false;
    }	

	if($('input[name="recordids[]"]:checked').length === 0)
	{
		bootbox.alert("At least one record must be selected to prepare the work order.");
		return;
	}	
	
	
	if(!$('#momfile').val())
	{
		bootbox.alert('Please upload the MoM file before proceeding.');
		return;				
	}
	if(!$('#markingsheet').val())
	{
		bootbox.alert('Please upload the marking sheet file before proceeding.');
		return;				
	}
	if(!$('#notesheet').val())
	{
		bootbox.alert('Please upload the note sheet file before proceeding.');
		return;				
	}
	if(!$('#signedcopy').val())
	{
		bootbox.alert('Please upload the signed order file before proceeding.');
		return;				
	}
	
	if(!$('#ordernumber').val())
	{
		bootbox.alert('Please enter order number before proceeding.');
		return;				
	}
	if(!$('#subject').val())
	{
		bootbox.alert('Please enter subject before proceeding.');
		return;				
	}
	if(!$('#signedby').val())
	{
		bootbox.alert('Please select signed by name.');
		return;				
	}
	if(!$('#order_date').val())
	{
		bootbox.alert('Please enter order date.');
		return;				
	}
	bootbox.confirm('Are you sure you want to create this work order? Once created, it cannot be undone.',function(result){
		if(result)
		{
			$(".generateorderBtn").css("display","none");
			var rl		=	$('#workorderfrm').attr('action');
			var formData= 	new FormData($('#workorderfrm')[0]);
			
			$.ajax({
				url: rl,
				type:'POST',
				data:formData,
				processData:false,
				contentType:false,
				headers: {
					'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
				},
				success: function (response) {
					if(response.status == 200) {
						window.location.href = response.redirect_url;
					}
					else
					{
						$(".generateorderBtn").css("display","");
					}
				},
				error: function (xhr) {
					$(".generateorderBtn").css("display","");
					if (xhr.responseJSON && xhr.responseJSON.errors) {
						var errors = xhr.responseJSON.errors;
						var allMessages = '';

						$.each(errors, function(field, messages) {
							$.each(messages, function(index, msg) {
								allMessages += msg + '<br>';
							});
						});

						bootbox.alert(allMessages);
						setTimeout(function() { $(".no-skin").css("padding-right",""); }, 5000);
					}
				}
			});			
		}
	});
});




jQuery(function($) {	

	$('.attachment').ace_file_input({
		no_file:'No File ...',
		btn_choose:'Resume',
		btn_change:'Change',
		btn_name:'fourthimage',
		thumbnail:false //| true | large
	});


	$('#demandnotefile').ace_file_input({
		no_file:'No File ...',
		btn_choose:'Upload Demand Note',
		btn_change:'Change',
		btn_name:'fourthimage',
		thumbnail:false //| true | large
	});

	$('#paymentfile').ace_file_input({
		no_file:'No File ...',
		btn_choose:'Upload Payment File',
		btn_change:'Change',
		btn_name:'fourthimage',
		thumbnail:false //| true | large
	});

	$('#momfile').ace_file_input({
		no_file:'No File ...',
		btn_choose:'Upload MoM*',
		btn_change:'Change',
		btn_name:'fourthimage',
		thumbnail:false //| true | large
	});

	$('#markingsheet').ace_file_input({
		no_file:'No File ...',
		btn_choose:'Upload Marking Sheet*',
		btn_change:'Change',
		btn_name:'fourthimage',
		thumbnail:false //| true | large
	});
	$('#notesheet').ace_file_input({
		no_file:'No File ...',
		btn_choose:'Upload Note Sheet*',
		btn_change:'Change',
		btn_name:'fourthimage',
		thumbnail:false //| true | large
	});
	$('#signedcopy').ace_file_input({
		no_file:'No File ...',
		btn_choose:'Upload Order Copy*',
		btn_change:'Change',
		btn_name:'fourthimage',
		thumbnail:false //| true | large
	});
		
});

document.addEventListener('change', function (e) {
    // Match only single file input fields with class .file-input
    if (e.target && e.target.classList.contains('file-input')) {
        const input = e.target;
        const label = input.parentElement;

        if (input.files.length > 0) {
            const fileName = input.files[0].name;
            label.innerHTML = `<i class="fa fa-check"></i> 1 File Selected`;
            label.appendChild(input); // keep the input inside the label
        } else {
            label.innerHTML = '<i class="fa fa-upload"></i> Resume';
            label.appendChild(input);
        }
    }
});

</script>

<script src="{{ asset('panel/assets/js/datatablePaginationWithFilter.js') }}"></script>
<script>
document.querySelectorAll('.format-indian').forEach(function(el) {
    el.innerText = formatIndianNumber(el.dataset.value);
});


let dynamicIndex = 1;

$('#addResourceBtn').on('click', function () {

    if (!validateTopFields()) {
        return;
    }

    let orderDate = $('#order_date').val();

    let rowCount =
        $('#dynamicResourceRows tr').length +
        $('input[name="recordids[]"]').length + 1;

    let newRow = `
    <tr class="dynamic-resource-row">

        <td class="center">${rowCount}</td>

        <td class="center">
            <input type="checkbox" name="recordids[]" value="0">
        </td>

        <td>
            <select name="sectorids[]" class="form-control width-150 sector-select">
                <option value="">--Select Sector--</option>

                @foreach($sectors as $sector)
                    <option value="{{$sector->sectorid}}">
                        {{$sector->sectorname}}
                    </option>
                @endforeach

            </select>
        </td>

        <td>
            <select name="positionids[]" class="form-control width-150">
                <option value="">--Select Position--</option>

                @foreach($positions as $position)
                    <option value="{{$position->positionid}}">
                        {{$position->consultantposition}}
                    </option>
                @endforeach

            </select>
        </td>

        <td>
            <select name="deploymenttypes[]" class="deploymenttype form-control width-150">
                <option value="">--Deployment Type--</option>
				<option value="FULL TIME">FULL TIME</option>
				<option value="PART TIME">PART TIME</option>
            </select>
        </td>

        <td>
            <input type="text"
                   name="startDate[]"
                   value="${orderDate}"
                   placeholder="dd-mm-YYYY"
                   class="startDate todays_dt_blank width-100 form-control"
                   autocomplete="off" readonly>
        </td>

        <td>
            <input type="text"
                   name="endDate[]"
                   placeholder="dd-mm-YYYY"
                   class="endDate todays_dt_blank width-100 form-control"
                   autocomplete="off" readonly>
        </td>

        <td class="center">
            <button type="button"
                    class="btn btn-danger btn-xs removeResourceBtn">
                <i class="fa fa-trash"></i>
            </button>
        </td>

    </tr>`;

    $('#dynamicResourceRows').append(newRow);
	  flatpickr(".todays_dt_blank", {
		dateFormat: "d-m-Y", // Set the desired date format: DD-MM-YYYY
		defaultDate: "",
		allowInput: true
	  });
	updateResourceSerialNumbers();
});

$(document).on('click', '.removeResourceBtn', function () {

    $(this).closest('tr').remove();

    // Recalculate serial numbers
	updateResourceSerialNumbers();
});

function updateResourceSerialNumbers() {

    let counter = 1;

    // Existing rows only
    $('.resourceTable tbody.resource-existing tr').each(function () {

        $(this).find('td:first').text(counter++);
    });

    // Dynamic rows only
    $('#dynamicResourceRows tr').each(function () {

        $(this).find('td:first').text(counter++);
    });
}


$('#addAwdResourceBtn').on('click', function () {

    if (!validateTopFields()) {
        return;
    }

    let orderDate = $('#order_date').val();
	let orderDueDate = $('#order_duedate').val();

    let rowCount =
        $('#dynamicResourceRowsAwd tr').length +
        $('input[name="recordids[]"]').length + 1;

    let newRow = `
    <tr class="dynamic-resource-row">

        <td class="center v-top">${rowCount}</td>

        <td class="center v-top">
            <input type="checkbox" name="recordids[]" value="0">
        </td>

        <td class="v-top">
            <input type="text" name="new_roles[]" placeholder="Role" class="role form-control width-150" autocomplete="off" readonly>
            <select name="new_experience[]" class="experience form-control width-150">
                <option value="">--Experience--</option>

                @foreach($experiences as $experience)
                    <option value="{{$experience->workexperience}}">
                        {{$experience->workexperience}}
                    </option>
                @endforeach

            </select>
			
        </td>
        <td class="v-top">
            <input type="text" name="new_resource_name[]" placeholder="Name" class="resource_name form-control" autocomplete="off" readonly>
            <select name="new_exp_level[]" class="exp_level form-control">
                <option value="">--Level--</option>

                @foreach($levels as $level)
                    <option value="{{$level->experiencelevel}}">
                        {{$level->experiencelevel}} Level
                    </option>
                @endforeach

            </select>

        </td>

        <td class="v-top">
            <select name="new_deploymenttype[]" class="deploymenttype form-control">
			<option value="FULL TIME">FULL TIME</option>
			<option value="PART TIME">PART TIME</option>
            </select>
        </td>

        <td class="v-top">
            <input type="text" name="new_startDate[]" value="${orderDate}" placeholder="dd-mm-YYYY" class="startDate todays_dt_blank width-100 form-control" autocomplete="off" readonly>
        </td>

        <td class="center v-top">
            <input type="text" name="new_endDate[]" value="${orderDueDate}" placeholder="dd-mm-YYYY" class="endDate todays_dt_blank width-100 form-control" autocomplete="off" readonly>
        </td>

        <td class="center v-top">
            <button type="button" class="btn btn-danger btn-xs removeAwdResourceBtn">
                <i class="fa fa-trash"></i>
            </button>
        </td>

    </tr>`;

    $('#dynamicResourceRowsAwd').append(newRow);
	  flatpickr(".todays_dt_blank", {
		dateFormat: "d-m-Y", // Set the desired date format: DD-MM-YYYY
		defaultDate: "",
		allowInput: true
	  });
	updateAwdResourceSerialNumbers();
});

$(document).on('click', '.removeAwdResourceBtn', function () {

    $(this).closest('tr').remove();

    // Recalculate serial numbers
	updateAwdResourceSerialNumbers();
});

function updateAwdResourceSerialNumbers() {

    let counter = 1;

    // Existing rows only
    $('.awdresourceTable tbody.awdresource-existing tr').each(function () {

        $(this).find('td:first').text(counter++);
    });

    // Dynamic rows only
    $('#dynamicResourceRowsAwd tr').each(function () {

        $(this).find('td:first').text(counter++);
    });
}

</script>

@endsection