<div class="modal-body" style="padding:10px 20px;">
	<div class="table-responsive printableResourceData">
	
@foreach($orders as $order)

<table class="table table-bordered">
	@if($loop->iteration==1)
    <tr>
        <td colspan="7" class="font-bold font-14">
            Department / Project Manager : {{$departmentName}}
        </td>
    </tr>
	@endif
    <tr class="table-label font-bold">
        <td colspan="7">
            Project : @if($order->engagementname){{$order->engagementname}}@endif
        </td>
    </tr>
    <tr class="table-label font-bold">
        <td colspan="7">
            Firm : {{$order->vendorname}}
        </td>
    </tr>
    <tr class="table-label font-bold">
        <td colspan="7">
            Work Order Date : {{ date('d-m-Y',strtotime($order->orderdate)) }} | Order No : {{ $order->ordernumber }} | Work Order Due Date : {{ date('d-m-Y',strtotime($order->workorderduedate)) }}
        </td>
    </tr>
    @foreach($order->groups as $group)
        <tr class="table-label-fade font-bold">
            <td colspan="7">{{ ucwords(strtolower($group->sectorname)) }} | {{ $group->consultantposition }} | Remuneration : INR {{$group->remuneration}}</td>
        </tr>
		<tr class="table-label-fade font-bold">
			<td nowrap class="text-center width-30">S.No.</td>
			<td nowrap class="text-center width-100">Code</td>
			<td nowrap class="">Name</td>
			<td nowrap class="text-center width-100">Mobile Number</td>
			<td nowrap class="">Email</td>
			<td nowrap class="text-center width-100">Joining Date</td>
			<td nowrap class="text-center width-100">Status</td>
		</tr>
        @foreach($group->resources as $resource)

            <tr>
				<td nowrap class="text-center width-30">{{$loop->iteration}}</td>
                <td>{{ $resource->employee_code }}</td>
				<td nowrap>{{ $resource->name }}</td>
				<td>{{ $resource->mobilenumber }}</td>
				<td>{{ $resource->email }}</td>
                <td class="text-center">@if($resource->deployed_date){{ date('d-m-Y',strtotime($resource->deployed_date)) }}@endif</td>
				<td class="text-center">{{ $resource->deployment_status }}</td>
            </tr>

        @endforeach

    @endforeach

</table>

@endforeach
	</div>
	
</div>
<div class="modal-footer">
	<div class="eoi-actions" style="float:right;">
		<a data-bs-dismiss="modal" onclick="CloseThis()" class="eoi-btn" style="cursor:pointer;">
			<i class="fa fa-remove"></i> Close
		</a>
	</div>
	<div class="eoi-actions" style="float:right;">
		<a data-bs-dismiss="modal" onclick="printResourceData()" class="eoi-btn" style="cursor:pointer; margin-right:10px;">
			<i class="fa fa-print"></i> Print
		</a>
	</div>

</div>