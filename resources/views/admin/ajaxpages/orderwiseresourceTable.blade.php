@php 
$i = 1; 
$serial=0;
@endphp
@foreach ($data as $rec)
<tr>
	<td>
<table class="order-table pd-5" style="border:1px solid #eee!important; border-collapse:collapse!important;" border="1">

    <!-- ORDER SUMMARY -->
    <tr>
        <td colspan="@if($rec->categoryid==2) 8 @else 7 @endif" class="order-summary">
			<input type="hidden" name="order_id" value="{{$rec->orderid}}">
			<input type="hidden" name="vendor_id" value="{{$rec->vendorid}}">
            <strong>{{$loop->iteration}})</strong>
            {{$rec->ordernumber}} | {{$rec->companyname}} | Date : {{date('d-m-Y',strtotime($rec->orderdate))}} | Due Date : {{date('d-m-Y',strtotime($rec->workorderduedate))}}
        </td>
    </tr>


    <!-- TABLE HEADER -->
    @if($rec->categoryid==2)
    <tr class="resource-head">
        <th nowrap style="border:1px solid #eee!important; border-collapse:collapse!important;">S. No.</th>
        <th style="width:100px!important; border:1px solid #eee!important; border-collapse:collapse!important;">Code</th>
        <th style="border:1px solid #eee!important; border-collapse:collapse!important;">Name</th>
		<th style="width:100px!important;border:1px solid #eee!important; border-collapse:collapse!important;">Email</th>
		<th style="width:100px!important;border:1px solid #eee!important; border-collapse:collapse!important;">Sector</th>
		<th style="width:100px!important;border:1px solid #eee!important; border-collapse:collapse!important;">Position</th>
        <th style="width:100px!important;border:1px solid #eee!important; border-collapse:collapse!important;">Joining Date</th>
		<th style="border:1px solid #eee!important; border-collapse:collapse!important;" class="text-center"></th>
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
        <td class="center width-30" style="position:relative; border:1px solid #eee!important; border-collapse:collapse!important;">
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
        <td style="width:100px!important; border:1px solid #eee!important; border-collapse:collapse!important;">
			{{$resource->employee_code}}
		</td>
        <td style="border:1px solid #eee!important; border-collapse:collapse!important;">
			{{$resource->name}}
		</td>
        <td style="width:100px!important; border:1px solid #eee!important; border-collapse:collapse!important;">
			{{$resource->email}}
		</td>
        <td style="width:100px!important; border:1px solid #eee!important; border-collapse:collapse!important;" nowrap>
		{{ucwords(strtolower($resource->sectorname))}}
		</td>
        <td style="width:100px!important; border:1px solid #eee!important; border-collapse:collapse!important;" nowrap>
		{{ucwords(strtolower($resource->consultantposition))}}
		</td>
		
		<td class="center" style="width:120px!important; border:1px solid #eee!important; border-collapse:collapse!important;">
			@if($resource->deployed_date){{date('d\-m\-Y',strtotime($resource->deployed_date))}}@endif
		</td>
        <td style="width:100px!important; border:1px solid #eee!important; border-collapse:collapse!important;" class="text-center">
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
			{{$resource->employee_code}}
		</td>
		
        <td style="padding:0px!important;">
			{{$resource->name}}
		</td>
        <td style="padding:0px!important;">
			{{$resource->role}}
		</td>
	
        <td style="padding:0px!important; width:150px!important;" nowrap>
			<select name="levels[]" style="width:150px!important; height:37px;">
				@foreach($levels as $lvl)
				<option value="{{$lvl->experiencelevel}}" @if($resource->experiencelevel==$lvl->experiencelevel) selected @endif>Level-{{ucwords(strtolower($lvl->experiencelevel))}}</option>
				@endforeach
			</select>
		</td>
		<td style="padding:0px!important; width:100px!important;">
			@if($resource->deployed_date){{date('d\-m\-Y',strtotime($resource->deployed_date))}}@endif
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