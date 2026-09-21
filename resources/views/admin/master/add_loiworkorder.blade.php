@extends('admin.admin_master')
@section('admin')
@php
	$t = 1;
@endphp
<div class="main-content">
<style>
.ace-file-input {
    width: 300px;
}
</style>
	<div class="main-content-inner">
		<div class="page-content" style="padding:10px 15px!important;">
			<div class="ace-settings-container" id="ace-settings-container"></div>
			<div class="row">
				<div class="col-xs-12">
					<!-- PAGE CONTENT BEGINS -->
<div class="tabbable" style="padding:0px 0px!important;">
<div class="eoi-section-header" style="padding:8px 8px 16px 8px;">

	<div style="font-size:20px; font-weight:600; color:#2c3e50;">
		Add Direct Work Order
	</div>

	<div style="font-size:14px; color:#6c757d; margin:2px 0px; 8px 0">
		Create a new work order by capturing the project scope, dates, and commercial terms.
	</div>

</div>
<div class="tab-content no-border" style="min-height:100px; padding:0px 10px;">
	<div id="home" class="tab-pane in active" style="padding: 0 16px;">
		<div class="row">
			<div class="row">
@permission('store.directworkorder')			
<form name="frm" id="frm" action="{{ route('store.directworkorder') }}" data-url="" method="post" enctype="multipart/form-data">
@csrf
<div class="form-group">
	<table class="pd-3 font-12">
		<tr>
			<td class="font-bold" colspan="5">
				Project Title<br>
				<input type="text" name="project_title" id="project_title" class="form-control" style="margin-top:0px;" placeholder="Project title" autocomplete="off">
			</td>
		</tr>
		<tr><td colspan="5"></td></tr>
		<tr>
			<td class="font-bold">Firm Name</td>
			<td class="font-bold">Rate List</td>
			<td class="font-bold">@if($categoryid==2) Department / Project Manager @else Project Manager @endif</td>
			<td class="font-bold">Work Order Date</td>
			<td class="font-bold">Work Order Due Date</td>
		</tr>
		<tr>
			<td>
				<select name="vendorid" id="vendorid" class="select2 width-200" data-placeholder="Firm Name">
					<option value=""></option>
					@foreach($vendors as $vendor)
					<option value="{{$vendor->vendorid}}">{{$vendor->companyname}}</option>
					@endforeach
				</select>
			</td>
			
			<td>
				<select name="rateid" id="rateid" class="select2 width-200" data-placeholder="Rate List">
					<option value=""></option>
					@foreach($rateList as $rate)
					<option value="{{$rate->rateid}}">{{$rate->ratelist_name}}</option>
					@endforeach
				</select>
			</td>
			<td>
				<select name="departmentid" id="departmentid" class="select2 width-200" data-placeholder="@if($categoryid==2)Department / Project Manager @else Project Manager @endif">
					<option value=""></option>
					@if($categoryid==2)
					<optgroup label="Department">
					@foreach($departments as $rec)
					@if($rec->isdepartment==1)
					<option value="{{$rec->departmentid}}">{{$rec->shortname}}-{{$rec->departmentname}}</option>
					@endif
					@endforeach
					</optgroup>
					@endif
					<optgroup label="Project Manager">
					@foreach($departments as $rec)
					@if($rec->ispm==1)
					<option value="{{$rec->departmentid}}">{{$rec->departmentname}}</option>
					@endif
					@endforeach
					</optgroup>
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
	@if($categoryid==2)
	<div class="section-block-detail">
		<div class="section-header-detail">
			<span class="material-icons-outlined">group_add</span>
			&nbsp;<h2 class="section-title-detail">Resource Details</h2>
		</div>

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
	</div>
	@else
	<div class="section-block-detail">
		<div class="section-header-detail">
			<span class="material-icons-outlined">group_add</span>
			&nbsp;<h2 class="section-title-detail">Resource Details</h2>
		</div>

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
	</div>
	
	@endif
	<div class="section-block-detail orderdetail" style="display:none;">
		<div class="section-header-detail">
			<span class="material-icons-outlined">list</span>
			&nbsp;<h2 class="section-title-detail">Work Order Detail</h2>
		</div>
		<table class="mytable" border="1" style="text-transform: capitalize!important;">
			<tr>
				<td class="padding-10">Note Sheet*</td>
				<td class="padding-10">
					<input type="file" required name="notesheet" id="notesheet" accept="application/pdf" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}" data-width="200" style="width:200px!important;" />
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
						<option value="Chief Executive Officer (CEO)">Chief Executive Officer (CEO)</option>
						<option value="Jt. CEO (Project)">Jt. CEO (Project)</option>
						<option value="Jt. CEO(Finance)">Jt. CEO (Finance)</option>											
						<option value="Add. Chief Executive Officer">Add. Chief Executive Officer</option>
						<option value="Chief Operating Officer">Chief Operating Officer</option>
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
				@permission('store.directworkorder')
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
</form>
@endpermission
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
<script src="{{ asset('panel/assets/js/ace-elements.min.js') }}"></script>
<script src="{{ asset('panel/assets/js/ace.min.js') }}"></script>
<script src="{{ asset('panel/assets/js/loiorder.js') }}"></script>
<script>

$('.generateorderBtn').on('click', function () {
	
    if (!validateSelectedRows({{$categoryid}})) {
        return false;
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
	bootbox.confirm('Are you sure you want to create this work order? Once created, it cannot be undone.',function(result){
		if(result)
		{
			$(".generateorderBtn").css("display","none");
			var rl		=	$('#frm').attr('action');
			var formData= 	new FormData($('#frm')[0]);
			
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

$('#addAwdResourceBtn').on('click', function () {

    if (!validateTopFields()) {
        return;
    }
	
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
                   name="resource_names[]"
                   value=""
                   placeholder="Name"
                   class="resource_name form-control"
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

            <select name="exp_levels[]" class="exp_level form-control">
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
                   autocomplete="off" readonly onchange="CheckDueDate(this)">
        </td>

        <td class="center v-top">
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


$('#addResourceBtn').on('click', function () {

    if (!validateTopFields()) {
        return;
    }
	
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
                   autocomplete="off" readonly onchange="CheckDueDate(this)">
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

</script>
<script src="{{ asset('panel/assets/js/master.js') }}"></script>
<script src="{{ asset('panel/assets/js/datatablePaginationWithFilter.js') }}"></script>
@endsection