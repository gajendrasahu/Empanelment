@php 
$i = 1; 
$serial=0;
@endphp
@foreach ($data as $rec)
<tr>
	<td>
<table class="order-table pd-8">

    <!-- ORDER SUMMARY -->
    <tr>
        <td colspan="@if($rec->categoryid==2) 9 @else 7 @endif" class="order-summary">
			<input type="hidden" name="order_id" value="{{$rec->orderid}}">
			<input type="hidden" name="vendor_id" value="{{$rec->vendorid}}">
            <strong>{{$loop->iteration}})</strong>
            {{$rec->ordernumber}} | {{$rec->companyname}} | Date : {{date('d-m-Y',strtotime($rec->orderdate))}} | Due Date : {{date('d-m-Y',strtotime($rec->workorderduedate))}}
        </td>
    </tr>


    <!-- TABLE HEADER -->
    @if($rec->categoryid==2)
    <tr class="resource-head">
        <th nowrap>S. No.</th>
        <th style="width:100px!important;">Code</th>
        <th>Name</th>
        <th style="width:100px!important;">Mobile Number</th>
		<th style="width:100px!important;">Email</th>
		<th style="width:100px!important;">Sector</th>
		<th style="width:100px!important;">Position</th>
        <th style="width:100px!important;">Joining Date</th>
		<th class="text-center"></th>
    </tr>
    @else
    <tr class="resource-head">
        <th nowrap>S. No.</th>
        <th style="width:100px!important;">Code</th>
        <th>Name</th>
		<th>Role</th>
        <th style="width:100px!important;">Experience Level</th>
        <th style="width:100px!important;">Joining Date</th>
		<th class="text-center"></th>
    </tr>
    @endif
	
	@foreach($rec->resources as $resource)
	@if($rec->categoryid==2)
    <tr class="resource-row">
        <td class="center width-30" style="position:relative;">
			{{$loop->iteration}}
			@if($resource->deployment_status=='Pending')
				<span class="bds-pending text-center resource-status">P</span>
			@elseif($resource->deployment_status=='Active')
			<span class="bds-active text-center resource-status">A</span>
			@elseif($resource->deployment_status=='Released')
			<span class="bds-released text-center resource-status">R</span>
			@elseif($resource->deployment_status=='Extended')
			<span class="bds-extended text-center resource-status">E</span>
			@endif		
		</td>
        <td style="padding:0px!important; width:100px!important;">
			<input type="text" placeholder="Code" name="employee_code[]" value="{{$resource->employee_code}}" style="padding:8px; width:100%!important;" autocomplete="off">
		</td>
        <td style="padding:0px!important;">
			<input type="text" placeholder="Name" name="name[]" value="{{$resource->name}}" style="width:100%; padding:8px;" autocomplete="off">
		</td>
        <td style="padding:0px!important; ">
			<input type="text" placeholder="Mobile number" name="mobilenumber[]" value="{{$resource->mobilenumber}}" style="width:120px!important; padding:8px;" autocomplete="off">
		</td>
        <td style="padding:0px!important; width:100px!important;">
			<input type="text" placeholder="Email" name="email[]" value="{{$resource->email}}" style="padding:8px; width:100%!important;" autocomplete="off">
		</td>
        <td style="padding:0px!important; width:100px!important;" nowrap>
			<select name="sectorids[]" style="width:100%!important; height:37px;">
				@foreach($sectors as $sector)
				<option value="{{$sector->sectorid}}" @if($resource->sectorid==$sector->sectorid) selected @endif>{{ucwords(strtolower($sector->sectorname))}}</option>
				@endforeach
			</select>
		</td>
        <td style="padding:0px!important; width:100px!important;" nowrap>
			<select name="positionids[]" style="width:100%!important; height:37px;">
				@foreach($positions as $position)
				<option value="{{$position->positionid}}" @if($resource->positionid==$position->positionid) selected @endif>{{ucwords(strtolower($position->consultantposition))}}</option>
				@endforeach
			</select>
		</td>
		
		<td style="padding:0px!important; width:120px!important;">
			<input type="text" placeholder="dd-mm-YYYY" name="deployed_date[]" value="@if($resource->deployed_date){{date('d\-m\-Y',strtotime($resource->deployed_date))}}@endif" style="padding:8px; width:100%!important;" autocomplete="off">
		</td>
        <td style="padding:0px!important; width:100px!important;" class="text-center">
			@if($resource->isExtended==1)
				Extended
			@elseif($resource->deployment_status=='Active' || $resource->deployment_status=='Pending' || $resource->deployment_status=='Released')
				<button type="button" class="btn btn-success gridbtn" style="padding:4px; height:35px; border:none!important; background-color:#147E8B!important;" onclick="viewResourceDetail('{{route('viewresource.detail',Crypt::encrypt($resource->deploymentid))}}')">View & Update</button>
				
			@endif
        </td>
		
    </tr>
	@elseif($rec->categoryid==1)
    <tr class="resource-row">
        <td class="center width-30" style="position:relative;">
			{{$loop->iteration}}
			
			@if($resource->deployment_status=='Pending')
				<span class="bds-pending text-center resource-status">P</span>
			@elseif($resource->deployment_status=='Active')
			<span class="bds-active text-center resource-status">A</span>
			@elseif($resource->deployment_status=='Released')
			<span class="bds-released text-center resource-status">R</span>
			@elseif($resource->deployment_status=='Extended')
			<span class="bds-extended text-center resource-status">E</span>
			@endif			
		</td>
        <td style="padding:0px!important; width:100px!important;">
			<input type="text" placeholder="Code" name="employee_code[]" value="{{$resource->employee_code}}" style="padding:8px; width:100%!important;" autocomplete="off">
		</td>
		
        <td style="padding:0px!important;">
			<input type="text" placeholder="Name" name="name[]" value="{{$resource->name}}" style="padding:8px; width:100%!important;" autocomplete="off">
		</td>
        <td style="padding:0px!important;">
			<input type="text" placeholder="Role" name="role[]" value="{{$resource->role}}" style="padding:8px; width:100%!important;" autocomplete="off">
		</td>
	
        <td style="padding:0px!important; width:150px!important;" nowrap>
			<select name="levels[]" style="width:150px!important; height:37px;">
				@foreach($levels as $lvl)
				<option value="{{$lvl->experiencelevel}}" @if($resource->experiencelevel==$lvl->experiencelevel) selected @endif>Level-{{ucwords(strtolower($lvl->experiencelevel))}}</option>
				@endforeach
			</select>
		</td>
		<td style="padding:0px!important; width:100px!important;">
			<input type="text" placeholder="dd-mm-YYYY" name="deployed_date[]" value="@if($resource->deployed_date){{date('d\-m\-Y',strtotime($resource->deployed_date))}}@endif" style="padding:8px; width:100px!important;" autocomplete="off">
		</td>
        <td style="padding:0px!important; width:100px!important;" class="text-center">
			@if($resource->isExtended==1)
				Extended
			@elseif($resource->deployment_status=='Active' || $resource->deployment_status=='Pending' || $resource->replaced_by_deployment_id==0)
				<button type="button" class="btn btn-success gridbtn" style="padding:4px; height:35px; border:none!important; background-color:#147E8B!important;" onclick="viewResourceDetail('{{route('viewresource.detail',Crypt::encrypt($resource->deploymentid))}}')">View & Update</button>
			@else
				<button type="button" class="btn btn-success gridbtn" style="padding:4px; height:35px; border:none!important; background-color:#147E8B!important;" onclick="viewResourceDetail('{{route('viewresource.detail',Crypt::encrypt($resource->deploymentid))}}')">Replaced</button>
			@endif
        </td>
		
    </tr>
	@endif
	@endforeach
</table>
	</td>
</tr>

@endforeach
@if($data->count()==0)
<tr>
    <td class="padding-10" style="text-align:center;">
        <br>
        <i class="fa fa-warning blue nodata"></i><br>
        {!! __('messages.sorry') !!}
    </td>
</tr>
@endif