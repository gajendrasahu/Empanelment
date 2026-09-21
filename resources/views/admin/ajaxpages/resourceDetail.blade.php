@php
use Carbon\Carbon;
$t=1;
@endphp
@if($order->categoryid==2)
<div class="modal-header">
<h5 class="modal-title form-label" style="float:left;">Resource Detail</h5>
</div>
<form name="updateResourceDetail" id="updateResourceDetail" action="{{route('adminresource.update',Crypt::encrypt($resource->deploymentid))}}" method="post" enctype="multipart/form-data">
@csrf
<div class="modal-body" style="background-color:white!important;">
	<div class="row" style="background-color:white!important;">
		<div class="col-sm-2">
			<span class="form-label"><b>Employee Code</b><label id="req">&nbsp;</label></span>
			<input type="text" name="employee_code" id="employee_code" class="form-control" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}" placeholder="exm. EM-101" autocomplete="off" value="{{$resource->employee_code}}">
		</div>
		<div class="col-sm-2">
			<span class="form-label"><b>Name</b><label id="req">&nbsp;</label></span>
			<input type="text" name="name" id="name" class="form-control" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}" placeholder="Name" autocomplete="off" value="{{$resource->name}}">
		</div>
		<div class="col-sm-2">
			<span class="form-label"><b>Mobile Number</b><label id="req">&nbsp;</label></span>
			<input type="text" name="mobilenumber" id="mobilenumber" class="form-control" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}" placeholder="Mobile Number" autocomplete="off" value="{{$resource->mobilenumber}}">
		</div>
		<div class="col-sm-2">
			<span class="form-label"><b>Email</b><label id="req">&nbsp;</label></span>
			<input type="text" name="email" id="email" class="form-control" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}" placeholder="Email" autocomplete="off" value="{{$resource->email}}">
		</div>

		<div class="col-sm-2">
			<span class="form-label"><b>Sector</b><label id="req">&nbsp;</label></span>
			<select name="sectorid" id="sectorid" class="form-control" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}">
				<option value="">--Sector--</option>
				@foreach($sectors as $sector)
				<option value="{{$sector->sectorid}}" @if($resource->sectorid==$sector->sectorid) selected @endif>{{ucwords(strtolower($sector->sectorname))}}</option>
				@endforeach
			</select>
		</div>

		<div class="col-sm-2">
			<span class="form-label"><b>Position</b><label id="req">&nbsp;</label></span>
			<select name="positionid" id="positionid" class="form-control" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}">
				<option value="">--Position--</option>
				@foreach($positions as $position)
				<option value="{{$position->positionid}}" @if($resource->positionid==$position->positionid) selected @endif>{{$position->consultantposition}}</option>
				@endforeach
			</select>
		</div>
		<div class="col-sm-12">&nbsp;</div>
		<div class="col-sm-2">
			<span class="form-label"><b>Joining Date</b><label id="req">&nbsp;</label></span>
			<input type="text" name="deployed_date" id="deployed_date" class="form-control todays_dt_blank" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}" placeholder="dd-mm-YYYY" autocomplete="off" value="@if($resource->deployed_date){{date('d\-m\-Y',strtotime(trim($resource->deployed_date)))}}@endif">
		</div>

		<div class="col-sm-2">
			<span class="form-label"><b>Deployment Status</b><label id="req">&nbsp;</label></span>
			<select name="deployment_status" id="deployment_status" class="form-control" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}">
				<option value="Pending" @if($resource->deployment_status=='Pending') selected @endif>Pending</option>
				<option value="Active" @if($resource->deployment_status=='Active') selected @endif>Active</option>
				<option value="Released" @if($resource->deployment_status=='Released') selected @endif>Released</option>
			</select>
		</div>

		<div class="col-sm-2">
			<span class="form-label"><b>Release Date <i class="fa fa-info-circle" title="Only applicable when Deployment Status is Released"></i></b><label id="req">&nbsp;</label></span>
			<input type="text" name="released_date" id="released_date" class="form-control todays_dt_blank" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}" placeholder="dd-mm-YYYY" autocomplete="off" value="@if($resource->lastdate){{date('d\-m\-Y',strtotime(trim($resource->lastdate)))}}@endif">
		</div>


	</div>
	
</div>
</form>
<div class="row">
<div class="col-sm-12" style="padding:30px;">
<table class="pd-5" style="width:100%; border:1px solid #eee; border-collapse:collapse;" border="1">
	<tr class="myheadbg font-bold"><td colspan="13">Update Activity</td></tr>
	<tr class="myheadbg font-bold">
		<td class="center">S. No.</td>
		<td class="center">Code</td>
		<td>Name</td>
		<td class="center">Mobile Number</td>
		<td>Email</td>
		<td>Sector</td>
		<td>Position</td>
		<td class="center">Joining Date</td>
		<td class="center">Status</td>
		<td class="center">Release Date</td>
		<td class="center">Updated On</td>
		<td class="center">Updated By</td>
		<td class="center">Sub User</td>
	</tr>
	@if($updates->count()!=0)
		@foreach($updates as $update)
		<tr>
			<td class="center">{{$loop->iteration}}</td>
			<td class="center">{{$update->employee_code}}</td>
			<td>{{$update->name}}</td>
			<td class="center">{{$update->mobilenumber}}</td>
			<td>{{$update->email}}</td>
			<td>{{ucwords(strtolower($update->sectorname))}}</td>
			<td>{{$update->consultantposition}}</td>
			<td class="center">@if($update->deployed_date) {{date('d\-m\-Y',strtotime($update->deployed_date))}} @endif</td>
			<td class="center">{{$update->deployment_status}}</td>
			<td class="center">@if($update->lastdate) {{date('d\-m\-Y',strtotime($update->lastdate))}} @endif</td>
			<td class="center">@if($update->updatedOn) {{date('d\-m\-Y, h:i A',strtotime($update->updatedOn))}} @endif</td>
			<td class="center">{{$update->updatedBy}}</td>
			<td class="center">{{$update->subUserName}}</td>
		</tr>
		@endforeach
	@else
		<tr><td class="center" colspan="13">--No Update Records Found--</td></tr>
	@endif
</table>
</div>
</div>
@elseif($order->categoryid==1)
<div class="modal-header">
<h5 class="modal-title form-label" style="float:left;">Resource Detail</h5>
</div>
<form name="updateResourceDetail" id="updateResourceDetail" action="{{route('adminresource.update',Crypt::encrypt($resource->deploymentid))}}" method="post" enctype="multipart/form-data">
@csrf
<div class="modal-body" style="background-color:white!important;">
	@if($replacedBy)
	<div class="row padding-10">
		<div class="col-sm-12 bg-warning" style="padding:10px 10px!important;">
			<span><b>This resource has been replaced by : {{$replacedBy}}.</b></span>
		</div>
	</div>
	@endif	
	@if($replaces)
	<div class="row padding-10">
		<div class="col-sm-12 bg-warning" style="padding:10px 10px!important;">
			<span><b>Replacement For : {{$replaces}}.</b></span>
		</div>
	</div>
	@endif	
	<div class="row" style="background-color:white!important;">
		<div class="col-sm-3">
			<span class="form-label"><b>Employee Code</b><label id="req">&nbsp;</label></span>
			<input type="text" name="employee_code" id="employee_code" class="form-control" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}" placeholder="exm. EM-101" autocomplete="off" value="{{$resource->employee_code}}">
		</div>
		<div class="col-sm-3">
			<span class="form-label"><b>Name</b><label id="req">&nbsp;</label></span>
			<input type="text" name="name" id="name" class="form-control" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}" placeholder="Name" autocomplete="off" value="{{$resource->name}}">
		</div>
		<div class="col-sm-3">
			<span class="form-label"><b>Mobile Number</b><label id="req">&nbsp;</label></span>
			<input type="text" name="mobilenumber" id="mobilenumber" class="form-control" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}" placeholder="Mobile Number" autocomplete="off" value="{{$resource->mobilenumber}}">
		</div>
		<div class="col-sm-3">
			<span class="form-label"><b>Email</b><label id="req">&nbsp;</label></span>
			<input type="text" name="email" id="email" class="form-control" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}" placeholder="Email" autocomplete="off" value="{{$resource->email}}">
		</div>
		<div class="col-sm-12">&nbsp;</div>
		<div class="col-sm-3">
			<span class="form-label"><b>Role</b><label id="req">&nbsp;</label></span>
			<input type="text" name="role" id="role" class="form-control" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}" placeholder="Role" autocomplete="off" value="{{$resource->role}}">
		</div>
		<div class="col-sm-3">
			<span class="form-label"><b>Experience</b><label id="req">&nbsp;</label></span>
			<select name="experience" id="experience" class="form-control" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}">
				<option value="">--Experience--</option>
				@foreach($experiences as $exp)
				<option value="{{$exp->workexperience}}" @if($resource->experience==$exp->workexperience) selected @endif>{{$exp->workexperience}}</option>
				@endforeach
			</select>
		</div>

		<div class="col-sm-3">
			<span class="form-label"><b>Experience Level</b><label id="req">&nbsp;</label></span>
			<select name="experiencelevel" id="experiencelevel" class="form-control" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}">
				<option value="">--Experience Level--</option>
				@foreach($levels as $lvl)
				<option value="{{$lvl->experiencelevel}}" @if($resource->experiencelevel==$lvl->experiencelevel) selected @endif>Level - {{ucwords(strtolower($lvl->experiencelevel))}}</option>
				@endforeach
			</select>
		</div>

		
		<div class="col-sm-3">
			<span class="form-label"><b>Joining Date</b><label id="req">&nbsp;</label></span>
			<input type="text" name="deployed_date" id="deployed_date" class="form-control todays_dt_blank" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}" placeholder="dd-mm-YYYY" autocomplete="off" value="@if($resource->deployed_date){{date('d\-m\-Y',strtotime(trim($resource->deployed_date)))}}@endif">
		</div>
		<div class="col-sm-12">&nbsp;</div>
		<div class="col-sm-3">
			<span class="form-label"><b>Deployment Status</b><label id="req">&nbsp;</label></span>
			<select name="deployment_status" id="deployment_status" class="form-control" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}">
				<option value="Pending" @if($resource->deployment_status=='Pending') selected @endif>Pending</option>
				<option value="Active" @if($resource->deployment_status=='Active') selected @endif>Active</option>
				<option value="Released" @if($resource->deployment_status=='Released') selected @endif>Released</option>
			</select>
		</div>

		<div class="col-sm-3">
			<span class="form-label"><b>Release Date <i class="fa fa-info-circle" title="Only applicable when Deployment Status is Released"></i></b><label id="req">&nbsp;</label></span>
			<input type="text" name="released_date" id="released_date" class="form-control todays_dt_blank" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}" placeholder="dd-mm-YYYY" autocomplete="off" value="@if($resource->lastdate){{date('d\-m\-Y',strtotime(trim($resource->lastdate)))}}@endif">
		</div>
		<div class="col-sm-12">&nbsp;</div>
		<div class="col-sm-12">
			<b>Role / Remark (if any)</b>
			{!! $resource->remark !!}
		</div>

	</div>
	
</div>
</form>
<div class="row">
<div class="col-sm-12" style="padding:30px;">
<table class="pd-5" style="width:100%; border:1px solid #eee; border-collapse:collapse;" border="1">
	<tr class="myheadbg font-bold"><td colspan="13">Update Activity</td></tr>
	<tr class="myheadbg font-bold">
		<td class="center">S. No.</td>
		<td class="center">Code</td>
		<td>Name</td>
		<td class="center">Mobile Number</td>
		<td>Email</td>
		<td>Experience</td>
		<td>Experience Level</td>
		<td class="center">Joining Date</td>
		<td class="center">Status</td>
		<td class="center">Release Date</td>
		<td class="center">Updated On</td>
		<td class="center">Updated By</td>
		<td class="center">Sub User</td>
	</tr>
	@if($updates->count()!=0)
		@foreach($updates as $update)
		<tr>
			<td class="center">{{$loop->iteration}}</td>
			<td class="center">{{$update->employee_code}}</td>
			<td>{{$update->name}}</td>
			<td class="center">{{$update->mobilenumber}}</td>
			<td>{{$update->email}}</td>
			<td>{{$update->experience}}</td>
			<td>@if($update->experiencelevel) Level - {{$update->experiencelevel}} @endif</td>
			<td class="center">@if($update->deployed_date) {{date('d\-m\-Y',strtotime($update->deployed_date))}} @endif</td>
			<td class="center">{{$update->deployment_status}}</td>
			<td class="center">@if($update->lastdate) {{date('d\-m\-Y',strtotime($update->lastdate))}} @endif</td>
			<td class="center">@if($update->updatedOn) {{date('d\-m\-Y, h:i A',strtotime($update->updatedOn))}} @endif</td>
			<td class="center">{{$update->updatedBy}}</td>
			<td class="center">{{$update->subUserName}}</td>
		</tr>
		@endforeach
	@else
		<tr><td class="center" colspan="13">--No Update Records Found--</td></tr>
	@endif
</table>
</div>
</div>
@endif

<div class="modal-footer">
@if($resource->deployment_status=='Active')
<a href="{{ route('workorder.replaceresource', Crypt::encrypt($resource->deploymentid)) }}" style="float:left;">
<button type="button" class="btn btn-info gridbtn" style="width:150px;" onclick="CloseThis()" data-bs-dismiss="modal">
	<i class="fa fa-refresh"></i> Replace Resource
</button>
</a>
@endif

<button type="button" class="btn btn-info gridbtn" style="width:80px;" onclick="CloseThis()" data-bs-dismiss="modal">
	<i class="fa fa-remove"></i> Close
</button>

@if($resource->deployment_status!='Released')
	@permission('adminresource.update')
	<button type="button" class="btn btn-info gridbtn updateResource" style="width:100px;" data-bs-dismiss="modal">
		<i class="fa fa-save"></i> Update
	</button>
	@endpermission
@endif
</div>

<script>
  flatpickr(".todays_dt_blank", {
    dateFormat: "d-m-Y", // Set the desired date format: DD-MM-YYYY
    defaultDate: "",
	allowInput: true
  });
</script>