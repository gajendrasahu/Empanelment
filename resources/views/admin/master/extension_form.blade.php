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
		<ul class="nav nav-tabs padding-0" style="position:relative;">
		@permission('extend.workorder')
			<li class="active">
				<a data-toggle="tab" href="#home" class="form-label font-14">
					<i class="green ace-icon fa fa-refresh bigger-120" style="vertical-align:text-top;"></i>Work Order Extension Form
				</a>
				
			</li>
			<span style="position:absolute; right:10px; top:10px;">
				<i class="fa fa-info-circle" style="margin-top:3px;"></i> Please do not refresh the page while the work order is being extended.
			</span>
		@endpermission
		</ul>

	
		<div class="tab-content no-border" style="border:1px solid #ddd; min-height:100px; padding:0px 10px;">
@permission('extend.workorder')		
<div id="home" class="tab-pane in active" style="padding:0px 10px!important;">
	<div class="row">
	<form name="workorderfrm" id="workorderfrm" action="#" data-url="{{ route('extend.workorder',Crypt::encrypt($order->orderid)) }}" method="post" enctype="multipart/form-data">
	
		@csrf
		<div class="form-group">
			<div class="content-detail" style="position:relative;">
				@if(count($previousExtensions) > 0)
				<div class="section-block-detail">
					<div class="section-header-detail">
						<span class="material-icons-outlined">list</span>
						&nbsp;<h2 class="section-title-detail form-label">Previous Work Orders associated with this Work Order</h2>
					</div>
					<table class="mytable pd-10" border="1" style="text-transform: none!important;">
						<tr class="myheadbg">
							<td class="center font-bold" style="width:30px;">S.No.</td>
							<td class="font-bold">Work Order Number & Firm Name</td>
							<td class="font-bold center">Order Date</td>
							<td class="font-bold center">Order Expiry Date</td>
						</tr>
						@foreach($previousExtensions as $ords)
						<tr>
							<td class="center">{{$loop->iteration}}</td>
							<td style="position:relative;">
								<b>{{$ords->ordernumber}}</b>
								@if($ords->signedcopy)
								<a href="{{route('view.uploadedfile',Crypt::encrypt($ords->signedcopy))}}" target="_blank" style="position:absolute; right:5px; outline:none;">
									<i class="fa fa-file-pdf-o"></i>
								</a>
								@endif
								<br>
								{{$ords->companyname}}
							</td>
							<td class="center" nowrap>{{date('d\-m\-Y',strtotime($ords->orderdate))}}</td>
							<td class="center" nowrap>{{date('d\-m\-Y',strtotime($ords->workorderduedate))}}</td>
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
							<td class="font-bold">Work Order Date</td>
							<td class="font-bold">Work Order Due Date</td>
						</tr>
						<tr>
							<td>
								<input type="hidden" name="rateid" id="rateid" value="{{$rate->rateid}}">
								<input type="hidden" name="departmentid" id="departmentid" value="{{$order->department_id}}">
								<select name="vendorid" id="vendorid" class="width-250">
									<option value="">--Firm Name--</option>
									@foreach($vendors as $vendor)
									<option value="{{$vendor->vendorid}}" @if($order->vendorid==$vendor->vendorid) selected @endif>{{$vendor->companyname}}</option>
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
					@if($order->categoryid==2)
					<table class="mytable pd-10" border="1" style="text-transform: none!important;">
						<tr class="myheadbg font-bold">
							<td colspan="8">Firm Name : {{$order->companyname}}</td>
						</tr>
						<tr class="myheadbg font-bold">
							<td colspan="8">Work Order Number : {{$order->ordernumber}} | Order Date : {{date('d\-m\-Y',strtotime($order->orderdate))}} | Order Due Date : {{date('d\-m\-Y',strtotime($order->workorderduedate))}}</td>
						</tr>
						<tr class="myheadbg">
							<td class="center font-bold" style="width:30px;">S.No.</td>
							<td class="font-bold">Name</td>
							<td class="font-bold">Sector & Position</td>
							<td class="font-bold center" nowrap>Extend</td>
							<td class="font-bold center" nowrap>Release</td>
							<td class="font-bold">Deployment Type</td>
							<td class="font-bold width-100">Start Date</td>
							<td class="font-bold width-100">End Date</td>
						</tr>
						@php
						$totalmanmonth		=	0;
						$adminchargetotal	=	0;
						$grandtotal			=	0;
						@endphp
						<tbody>
						@foreach($resources as $index=>$req)
						<tr>
							<td class="center">{{$loop->iteration}}</td>
							<td>{{$req->name}}</td>
							<td>{{ucwords(strtolower($req->sectorname))}}<br>{{$req->consultantposition}}</td>
							<td class="center">
								<input type="hidden" name="existing[{{$loop->index}}][deployment_id]" value="{{$req->deploymentid}}">
								<input type="checkbox" name="existing[{{$loop->index}}][extend]" class="deployment deploymentChk" value="{{$req->deploymentid}}" checked>
							</td>							
							<td class="center">
								<input type="checkbox" name="existing[{{$loop->index}}][release]" class="release releaseChk" value="{{$req->deploymentid}}">
							</td>							
							<td>
							<select name="existing[{{$loop->index}}][deploymenttype]" class="deploymenttype form-control">
								<option value="FULL TIME" @if($req->deploymenttype=='FULL TIME') selected @endif>FULL TIME</option>
								<option value="PART TIME" @if($req->deploymenttype=='PART TIME') selected @endif>PART TIME</option>
							</select>
							</td>
							<td>
							<input type="text" name="existing[{{$loop->index}}][start_date]" placeholder="dd-mm-YYYY" class="startDate todays_dt_blank width-100 form-control" autocomplete="off">
							</td>
							<td>
							<input type="text" name="existing[{{$loop->index}}][end_date]" placeholder="dd-mm-YYYY" class="endDate todays_dt_blank width-100 form-control" readonly autocomplete="off">
							</td>
						</tr>
						@endforeach
						</tbody>
					</table>
					@else
					<table class="mytable pd-10" border="1" style="text-transform: none!important;">
						<tr class="myheadbg font-bold">
							<td colspan="8">Firm Name : {{$order->companyname}}</td>
						</tr>
						<tr class="myheadbg font-bold">
							<td colspan="8">Work Order Number : {{$order->ordernumber}} | Order Date : {{date('d\-m\-Y',strtotime($order->orderdate))}} | Order Due Date : {{date('d\-m\-Y',strtotime($order->workorderduedate))}}</td>
						</tr>
						<tr class="myheadbg">
							<td class="center font-bold" style="width:30px;" nowrap>S. No.</td>
							<td class="font-bold center" nowrap>Extend</td>
							<td class="font-bold center" nowrap>Release</td>
							<td class="font-bold" nowrap>Name, Role & Experience</td>
							<td class="font-bold" nowrap>Experience Level</td>
							<td class="font-bold" nowrap>Deployment Type</td>
							<td class="center font-bold width-100" nowrap>Start Date</td>
							<td class="center font-bold width-100" nowrap>End Date</td>				
						</tr>
						@php
						$totalmanmonth		=	0;
						$adminchargetotal	=	0;
						$grandtotal			=	0;
						@endphp
						<tbody>
						@foreach($resources as $index=>$req)
						<tr>
							<td class="center">{{$loop->iteration}}</td>
							<td class="center">
								<input type="hidden" name="existing[{{$loop->index}}][deployment_id]" value="{{$req->deploymentid}}">
								<input type="checkbox" name="existing[{{$loop->index}}][extend]" class="deployment deploymentChk" value="{{$req->deploymentid}}" checked>
							</td>							
							<td class="center">
								<input type="checkbox" name="existing[{{$loop->index}}][release]" class="release releaseChk" value="{{$req->deploymentid}}">
							</td>							
							<td class="">{{$req->name}}<br>{{$req->role}}@if($req->experience)<br>{{$req->experience}}@endif</td>
							<td class="">
							<select name="existing[{{$loop->index}}][experience_level]" class="experiencelevel form-control">
							@foreach($levels as $lvl)
								<option value="{{$lvl->experiencelevel}}" @if($lvl->experiencelevel==$req->experiencelevel) selected @endif>Level-{{$lvl->experiencelevel}}</option>
							@endforeach
							</select>
							</td>
							<td class="">
							<select name="existing[{{$loop->index}}][deploymenttype]" class="deploymenttype form-control">
								<option value="FULL TIME" @if($req->deploymenttype=='FULL TIME') selected @endif>FULL TIME</option>
								<option value="PART TIME" @if($req->deploymenttype=='PART TIME') selected @endif>PART TIME</option>
							</select>
							</td>
							<td class="center">
							<input type="text" name="existing[{{$loop->index}}][start_date]" placeholder="dd-mm-YYYY" class="startDate todays_dt_blank width-100 form-control" autocomplete="off">
							</td>
							<td class="center">
							<input type="text" name="existing[{{$loop->index}}][end_date]" placeholder="dd-mm-YYYY" class="endDate todays_dt_blank width-100 form-control" autocomplete="off">
							</td>
						</tr>
						@endforeach
						</tbody>
					</table>
						
					@endif
				</div>
				
				@if(!empty($orders))
				<div class="section-block-detail">
					<div class="section-header-detail">
						<h2 class="section-title-detail font-14">Other Order Details for the Same EoI</h2>
					</div>
					@if($order->categoryid==1)
					<table class="mytable pd-10" border="1" style="text-transform: none!important;">
						@php
							$mergeIndex = 0;
						@endphp
						@foreach($orders as $ord)
						<tr class="myheadbg font-bold"><td colspan="8">Order Number : {{$ord->ordernumber}} | Order Date : {{date('d\-m\-Y',strtotime($ord->orderdate))}} | Order Due Date : {{date('d\-m\-Y',strtotime($ord->workorderduedate))}}</td></tr>
						<tr class="myheadbg">
							<td class="center font-bold" style="width:30px;">S.No.</td>
							<td class="font-bold center" nowrap>Merge</td>
							<td class="font-bold" nowrap>Name, Role & Experience</td>
							<td class="font-bold" nowrap>Experience Level</td>
							<td class="font-bold" nowrap>Deployment Type</td>
							<td class="center font-bold width-100" nowrap>Start Date</td>
							<td class="center font-bold width-100" nowrap>End Date</td>				
						</tr>
						@foreach($ord->orderResources as $req)
						<tr>
							<td class="center">{{$loop->iteration}}</td>
							<td class="center">
								<input type="hidden" name="merge[{{$mergeIndex}}][deployment_id]" value="{{$req->deploymentid}}">
								<input type="checkbox" name="merge[{{$mergeIndex}}][selected]" class="mergedeployment" value="1">
							</td>							
							<td class="">{{$req->name}}<br>{{$req->role}}@if($req->experience)<br>{{$req->experience}}@endif</td>
							<td class="">
							<select name="merge[{{$mergeIndex}}][experiencelevel]" class="merge_experiencelevel form-control" disabled>
							@foreach($levels as $lvl)
								<option value="{{$lvl->experiencelevel}}" @if($lvl->experiencelevel==$req->experiencelevel) selected @endif>Level-{{$lvl->experiencelevel}}</option>
							@endforeach
							</select>
							</td>
							<td class="">
							<select name="merge[{{$mergeIndex}}][deploymenttype]" class="merge_deploymenttype form-control" disabled>
								<option value="FULL TIME" @if($req->deploymenttype=='FULL TIME') selected @endif>FULL TIME</option>
								<option value="PART TIME" @if($req->deploymenttype=='PART TIME') selected @endif>PART TIME</option>
							</select>
							</td>
							<td class="center">
							<input type="text" name="merge[{{$mergeIndex}}][start_date]" placeholder="dd-mm-YYYY" class="merge_startDate todays_dt_blank width-100 form-control" autocomplete="off" readonly disabled>
							</td>
							<td class="center">
							<input type="text" name="merge[{{$mergeIndex}}][end_date]" placeholder="dd-mm-YYYY" class="merge_endDate todays_dt_blank width-100 form-control" readonly autocomplete="off" disabled>
							</td>
						</tr>
						@php
							$mergeIndex++;
						@endphp						
						@endforeach
					@endforeach
					</table>
					@else
					<table class="mytable pd-10" border="1" style="text-transform: none!important;">
						@php
							$mergeIndex = 0;
						@endphp
						@foreach($orders as $ord)
						<tr class="myheadbg font-bold">
							<td colspan="8">
								Order Number : {{$ord->ordernumber}} | Order Date : {{date('d\-m\-Y',strtotime($ord->orderdate))}} | Order Due Date : {{date('d\-m\-Y',strtotime($ord->workorderduedate))}}
							</td>
						</tr>
						<tr class="myheadbg">
							<td class="center font-bold" style="width:30px;">S.No.</td>
							<td class="font-bold">Name</td>
							<td class="font-bold">Sector & Position</td>
							<td class="font-bold center" nowrap>Merge</td>
							<td class="font-bold">Deployment Type</td>
							<td class="font-bold width-100">Start Date</td>
							<td class="font-bold width-100">End Date</td>
						</tr>
						@foreach($ord->orderResources as $req)
						<tr>
							<td class="center">{{$loop->iteration}}</td>
							<td>{{$req->name}}</td>
							<td>{{ucwords(strtolower($req->sectorname))}}<br>{{$req->consultantposition}}</td>
							<td class="center">
								<input type="hidden" name="merge[{{$mergeIndex}}][deployment_id]" value="{{$req->deploymentid}}">
								<input type="checkbox" name="merge[{{$mergeIndex}}][selected]" class="mergedeployment" value="1">
							</td>							
							<td>
							<select name="merge[{{$mergeIndex}}][deploymenttype]" class="merge_deploymenttype form-control" disabled>
								<option value="FULL TIME" @if($req->deploymenttype=='FULL TIME') selected @endif>FULL TIME</option>
								<option value="PART TIME" @if($req->deploymenttype=='PART TIME') selected @endif>PART TIME</option>
							</select>
							</td>
							<td>
							<input type="text" name="merge[{{$mergeIndex}}][start_date]" placeholder="dd-mm-YYYY" class="merge_startDate todays_dt_blank width-100 form-control" autocomplete="off" readonly disabled>
							</td>
							<td>
							<input type="text" name="merge[{{$mergeIndex}}][end_date]" placeholder="dd-mm-YYYY" class="merge_endDate todays_dt_blank width-100 form-control" readonly autocomplete="off" disabled>
							</td>
						</tr>
						@php
							$mergeIndex++;
						@endphp
						@endforeach
					@endforeach
					</table>
						
					@endif
					
				</div>
				@endif
				
				<div class="section-block-detail">
					<div class="section-header-detail">
						<span class="material-icons-outlined">group_add</span>
						&nbsp;<h2 class="section-title-detail">Additional Resource Details</h2>
					</div>
					@if($order->categoryid==1)
					<table class="mytable pd-10 resourceTable" border="1" style="text-transform: none!important;">
						<tr class="myheadbg">
							<td class="center font-bold" style="width:30px;">S.No.</td>
							<td class="font-bold">Role & Name</td>
							<td class="font-bold width-150">Experience & Level</td>
							<td class="font-bold width-150">Deployment Type</td>
							<td class="font-bold width-100">Start Date</td>
							<td class="font-bold width-100">End Date</td>
							<td class="font-bold center" style="width:30px;">Action</td>
						</tr>
						<tbody id="dynamicResourceRows"></tbody>
						<tr>
							<td colspan="7" class="padding-10 text-right">
								<button type="button" class="btn btn-success btn-sm" id="addAwdResourceBtn">
									<i class="fa fa-plus"></i> Add Resource
								</button>
							</td>
						</tr>
						
					</table>
					@else
					<table class="mytable pd-10 resourceTable" border="1" style="text-transform: none!important;">
						<tr class="myheadbg">
							<td class="center font-bold" style="width:30px;">S.No.</td>
							<td class="font-bold">Sector Name</td>
							<td class="font-bold">Position</td>
							<td class="font-bold">Deployment Type</td>
							<td class="font-bold">Start Date</td>
							<td class="font-bold">End Date</td>
							<td class="font-bold center" style="width:120px;">Action</td>
						</tr>
						<tbody id="dynamicResourceRows"></tbody>
						<tr>
							<td colspan="7" class="padding-10 text-right">
								<button type="button" class="btn btn-success btn-sm" id="addResourceBtn">
									<i class="fa fa-plus"></i> Add Resource
								</button>
							</td>
						</tr>
						
					</table>						
					@endif
				</div>
				
				
				<div class="section-block-detail">
					<div class="section-header-detail">
						<span class="material-icons-outlined">list</span>
						&nbsp;<h2 class="section-title-detail">Work Order Detail</h2>
					</div>
					<table class="mytable" border="1" style="text-transform: capitalize!important;">
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
							<td class="padding-10" style="width:200px;">Signed By*</td>
							<td class="padding-10">
								<select name="signedby" id="signedby" required onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}">
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
								<input type="text" name="ordernumber" id="ordernumber" required placeholder="Order No." class="form-control" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}" style="margin-top:-1px!important; width:100%;" autocomplete="off" value="{{old('ordernumber')}}">
							</td>
						</tr>								
						<tr>
							<td class="padding-10" colspan="2" style="border-top:none!important;">
								<button type="button" class="btn btn-info extendorderBtn" style="width:auto; max-width:250px; float:right;" tabindex="{{$t++}}">
									<i class="fa fa-refresh"></i> Extend Work Order
								</button>
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


<script src="{{ asset('panel/assets/js/jquery-2.1.4.min.js') }}"></script>
<script src="{{ asset('panel/assets/js/ace-elements.min.js') }}"></script>
<script src="{{ asset('panel/assets/js/ace.min.js') }}"></script>
<script src="{{ asset('panel/assets/js/orderextension.js') }}"></script>

<script>



$(document).ready(function () {
    initFlatpickr();
});

function initFlatpickr() {
    flatpickr(".todays_dt_blank", {
        dateFormat: "d-m-Y",
        allowInput: true,
        onClose: function(selectedDates, dateStr, instance) {
			/*
            if ($(instance.input).hasClass('endDate')) {
                CheckDueDate(instance.input);
            }
            if ($(instance.input).hasClass('startDate')) {
                CheckStartDate(instance.input);
            }
            if ($(instance.input).hasClass('merge_startDate')) {
                CheckStartDate(instance.input);
            }
            if ($(instance.input).hasClass('merge_endDate')) {
                CheckDueDate(instance.input);
            }
			*/
        }
    });
}



$(document).ready(function(){

    $('.deploymentChk').on('change', function () {

        let row = $(this).closest('tr');
        let releaseChk = row.find('.releaseChk');

        if($(this).is(':checked')) {
            releaseChk.prop('checked', false);
        } else {

            // prevent both unchecked
            if(!releaseChk.is(':checked')) {
                $(this).prop('checked', true);
            }
        }
    });

    $('.releaseChk').on('change', function () {

        let row = $(this).closest('tr');
        let deploymentChk = row.find('.deploymentChk');

        if($(this).is(':checked')) {
            deploymentChk.prop('checked', false);
        } else {

            // prevent both unchecked
            if(!deploymentChk.is(':checked')) {
                $(this).prop('checked', true);
            }
        }
    });

});
$('#addResourceBtn').on('click', function () {
	/*
    if (!validateTopFields()) {
        return;
    }
	*/
    let orderDate = $('#order_date').val();
	let orderDueDate = $('#order_duedate').val();

    let rowCount =
        $('#dynamicResourceRows tr').length+ 1;
	
	
    let newRow = `
    <tr class="dynamic-resource-row">

        <td class="center">${rowCount}</td>

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
				<option value="">--Deployment--</option>
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
				   value="${orderDueDate}"
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
	initFlatpickr();
	updateResourceSerialNumbers();
});


$('#addAwdResourceBtn').on('click', function () {
	
	/*
    if (!validateTopFields()) {
        return;
    }
	*/
    let orderDate = $('#order_date').val();
	let orderDueDate = $('#order_duedate').val();

    let rowCount =
        $('#dynamicResourceRows tr').length+ 1;
	
	
    let newRow = `
    <tr class="dynamic-resource-row">

        <td class="center v-top">${rowCount}</td>

        <td class="v-top">
            <input type="text"
                   name="roles[]"
                   value=""
                   placeholder="Role"
                   class="role form-control"
                   autocomplete="off">

            <input type="text"
                   name="resources[]"
                   value=""
                   placeholder="Name"
                   class="resource form-control"
                   autocomplete="off">

        </td>

        <td class="v-top">
            <select name="experiences[]" class="experience form-control">
                <option value="">--Experience--</option>

                @foreach($experiences as $experience)
                    <option value="{{$experience->workexperience}}">
                        {{$experience->workexperience}}
                    </option>
                @endforeach

            </select>

            <select name="levels[]" class="level form-control">
                <option value="">--Level--</option>

                @foreach($levels as $lvl)
                    <option value="{{$lvl->experiencelevel}}">
                        {{$lvl->experiencelevel}}
                    </option>
                @endforeach

            </select>
        </td>
        <td class="v-top">
            <select name="deploymenttypes[]" class="deploymenttype form-control">
                <option value="">--Deployment--</option>
				<option value="FULL TIME">FULL TIME</option>
				<option value="PART TIME">PART TIME</option>
            </select>
		</td>
        <td class="v-top">
            <input type="text"
                   name="startDate[]"
                   value="${orderDate}"
                   placeholder="dd-mm-YYYY"
                   class="startDate todays_dt_blank width-100 form-control"
                   autocomplete="off" readonly>
        </td>

        <td class="v-top">
            <input type="text"
                   name="endDate[]"
				   value="${orderDueDate}"
                   placeholder="dd-mm-YYYY"
                   class="endDate todays_dt_blank width-100 form-control"
                   autocomplete="off" readonly>
        </td>

        <td class="center v-top">
            <button type="button"
                    class="btn btn-danger btn-xs removeResourceBtn">
                <i class="fa fa-trash"></i>
            </button>
        </td>

    </tr>`;
	$('#dynamicResourceRows').append(newRow);
	initFlatpickr();
	updateResourceSerialNumbers();
});
$(document).on('click', '.removeResourceBtn', function () {

    $(this).closest('tr').remove();

    // Recalculate serial numbers
	updateResourceSerialNumbers();
});

function updateResourceSerialNumbers() {

    let counter = 1;

    $('.resourceTable tbody.resource-existing tr').each(function () {
        $(this).find('td:first').text(counter++);
    });

    $('#dynamicResourceRows tr').each(function () {

        $(this).find('td:first').text(counter++);
    });
	if(counter>1)
	{
		$(".orderdetail").css('display','');
	}
	else
	{
		$(".orderdetail").css('display','none');
	}
}

jQuery(function ($) {

	$('#notesheet').ace_file_input({
		no_file: 'No File ...',
		btn_choose: 'Upload Note Sheet*',
		btn_change: 'Change',
		btn_name: 'fourthimage',
		thumbnail: false //| true | large
	});
	$('#signedcopy').ace_file_input({
		no_file: 'No File ...',
		btn_choose: 'Upload Order Copy*',
		btn_change: 'Change',
		btn_name: 'fourthimage',
		thumbnail: false //| true | large
	});

});



$('.extendorderBtn').on('click', function () {
    // File validations
	
    if (!$('#notesheet').val()) {
        bootbox.alert('Please upload the note sheet file before proceeding.');
        return false;
    }

    if (!$('#signedcopy').val()) {
        bootbox.alert('Please upload the signed order file before proceeding.');
        return false;
    }

    // Basic validations
    if (!$('#ordernumber').val()) {
        bootbox.alert('Please enter order number before proceeding.');
        return false;
    }

    if (!$('#subject').val()) {
        bootbox.alert('Please enter subject before proceeding.');
        return false;
    }

    if (!$('#signedby').val()) {
        bootbox.alert('Please select signed by name.');
        return false;
    }
	
    if (!$('#vendorid').val()) {
        bootbox.alert('Please select firm name.');
        return false;
    }

    if (!$('#order_date').val()) {
        bootbox.alert('Please select work order date.');
        return false;
    }

    if (!$('#order_duedate').val()) {
        bootbox.alert('Please select work order due date.');
        return false;
    }
	

    let hasError = false;

    $('.deploymentChk').each(function () {

        let row = $(this).closest('tr');

        let deploymentChecked = row.find('.deploymentChk').is(':checked');
        let releaseChecked = row.find('.releaseChk').is(':checked');

        if (!deploymentChecked && !releaseChecked) {
            bootbox.alert('Please select either Extend or Release for all rows.');
            hasError = true;
            return false;
        }

        if (deploymentChecked) {

            let deploymentType = row.find('.deploymenttype').val();
            let startDate = row.find('.startDate').val();
            let endDate = row.find('.endDate').val();

            let experienceLevel = row.find('.experiencelevel').length
                ? row.find('.experiencelevel').val()
                : true;

            if (!deploymentType || !startDate || !endDate || !experienceLevel) {

                bootbox.alert('Please fill all details for the extending resources before proceeding.');
                hasError = true;
                return false;
            }
        }
    });

    if (hasError) {
        return false;
    }

    $('.mergedeployment:checked').each(function () {

        let row = $(this).closest('tr');

        let deploymentType = row.find('.merge_deploymenttype').val();
        let startDate = row.find('.merge_startDate').val();
        let endDate = row.find('.merge_endDate').val();

        let experienceLevel = row.find('.merge_experiencelevel').length
            ? row.find('.merge_experiencelevel').val()
            : true;

        if (!deploymentType || !startDate || !endDate || !experienceLevel) {

            bootbox.alert('Please fill all Merge Deployment details before proceeding.');
            hasError = true;
            return false;
        }
    });

    if (hasError) {
        return false;
    }

    let resourceRows = $('#dynamicResourceRows tr').length;

    if (resourceRows > 0) {

        $('#dynamicResourceRows tr').each(function (){

            let row = $(this);

            if({{$order->categoryid}} == 1)
			{

                let role = row.find('input[name="roles[]"]').val();
                let resource = row.find('input[name="resources[]"]').val();
                let experience = row.find('select[name="experiences[]"]').val();
                let level = row.find('select[name="levels[]"]').val();
                let deploymentType = row.find('select[name="deploymenttypes[]"]').val();
                let startDate = row.find('input[name="startDate[]"]').val();
                let endDate = row.find('input[name="endDate[]"]').val();

                // If any one field filled then all required
                let hasAnyValue = role || resource || experience || level || deploymentType || startDate || endDate;

                if (hasAnyValue)
				{
                    if (!role || !resource || !experience || !level || !deploymentType || !startDate || !endDate)
					{
                        bootbox.alert('Please complete or remove the Additional Resource details before proceeding.');
                        hasError = true;
                        return false;
                    }
                }
				else
				{
                    row.remove();
                }

            }
			else
			{
                let sector = row.find('select[name="sectorids[]"]').val();
                let position = row.find('select[name="positionids[]"]').val();
                let deploymentType = row.find('select[name="deploymenttypes[]"]').val();
                let startDate = row.find('input[name="startDate[]"]').val();
                let endDate = row.find('input[name="endDate[]"]').val();

                let hasAnyValue = sector || position || deploymentType || startDate || endDate;

                if(hasAnyValue)
				{

                    if (!sector || !position || !deploymentType || !startDate || !endDate)
					{
                        bootbox.alert('Please complete or remove the Additional Resource details before proceeding.');
                        hasError = true;
                        return false;
                    }

                }
				else
				{
                    row.remove();
                }
            }
        });
    }

    if (hasError) {
        return false;
    }


    bootbox.confirm(
        'Are you sure you want to extend this work order? Once created, it cannot be undone.',
        function (result) {

            if (result) {

                $('.extendorderBtn').prop('disabled', true);

                let rl = $('#workorderfrm').data('url');

                let formData = new FormData($('#workorderfrm')[0]);

                $.ajax({
                    url: rl,
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,

                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },

                    beforeSend: function () {
                        $('.extendorderBtn').html(
                            '<i class="fa fa-spinner fa-spin"></i> Processing...'
                        );
                    },

                    success: function (response) {

                        if (response.status == 200) {

                            bootbox.alert(response.message || 'Work order extended successfully.', function () {

                                if (response.redirect_url) {
                                    window.location.href = response.redirect_url;
                                } else {
                                    location.reload();
                                }
                            });

                        } else {

                            $('.extendorderBtn').prop('disabled', false).html(
                                '<i class="fa fa-refresh"></i> Extend Work Order'
                            );

                            bootbox.alert(response.message || 'Something went wrong.');
                        }
                    },

                    error: function (xhr) {

                        $('.extendorderBtn').prop('disabled', false).html(
                            '<i class="fa fa-refresh"></i> Extend Work Order'
                        );

                        if (xhr.responseJSON && xhr.responseJSON.errors) {

                            let errors = xhr.responseJSON.errors;

                            let allMessages = '';

                            $.each(errors, function (field, messages) {

                                $.each(messages, function (index, msg) {

                                    allMessages += msg + '<br>';
                                });
                            });

                            bootbox.alert(allMessages);

                        } else {

                            bootbox.alert('Something went wrong. Please try again.');
                        }
                    }
                });
            }
        }
    );
});


$(document).on('change', '#order_date, #order_duedate', function () {

    let orderDate = $('#order_date').val();
    let orderDueDate = $('#order_duedate').val();

    if (orderDate && orderDueDate) {

        // Existing extending rows only
        $('.deploymentChk').each(function () {

            let row = $(this).closest('tr');

            row.find('.startDate')
                .val(orderDate)
                .prop('readonly', false)
                .prop('disabled', false);

            row.find('.endDate')
                .val(orderDueDate)
                .prop('readonly', false)
                .prop('disabled', false);
        });
    }
});
</script>
<script src="{{ asset('panel/assets/js/datatablePaginationWithFilter.js') }}"></script>

@endsection