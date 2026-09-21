<div class="modal-body" style="padding:10px 20px;">
	<div class="table-responsive printableResourceData">
		<table class="table table-bordered font-12">
			<thead>
				<tr>
					<td colspan="7" nowrap>Firm Name : {{$firmname}}</td>
				</tr>
			</thead>
			<tbody>
			@foreach($data as $order)
				<tr>
					<td colspan="7" class="table-label">
						Project Manager : {{$order->departmentname}}
					</td>
				</tr>
				<tr>
					<td colspan="7" class="table-label">
						Order Number : {{$order->ordernumber}} | Order Date : {{date('d\-m\-Y',strtotime($order->orderdate))}} | Work Order Due Date : {{date('d\-m\-Y',strtotime($order->workorderduedate))}}
					</td>
				</tr>
				
				@foreach($order->levels as $lvl)
				<tr class="table-label-fade font-bold">
					<td colspan="7">
						Experience Level : Level-{{$lvl->experiencelevel}} | Remuneration : INR {{$lvl->remuneration}} | {{$order->tiername}}
					</td>
				</tr>
				<tr class="table-label-fade font-bold">
					<td nowrap class="text-center width-30">S. No.</td>
					<td nowrap class="text-center width-100">Code</td>
					<td nowrap>Resource Name</td>
					<td nowrap class="text-center">Mobile Number</td>
					<td nowrap>Email</td>
					<td nowrap class="text-center">Joining Date</td>
					<td nowrap class="text-center">Status</td>
				</tr>
				@foreach($lvl->resources as $resource)
				<tr>
					<td nowrap class="text-center width-30">{{$loop->iteration}}</td>
					<td nowrap class="text-center width-100">{{$resource->employee_code}}</td>
					<td nowrap>
						{{$resource->name}}				
					</td>
					<td nowrap class="text-center width-100">{{$resource->mobilenumber}}</td>
					<td nowrap class="width-100">{{$resource->email}}</td>
					<td nowrap class="text-center width-100">
						@if($resource->deployed_date) {{date('d\-m\-Y',strtotime($resource->deployed_date))}} @endif
					</td>
					<td nowrap class="text-center width-100">{{$resource->deployment_status}}</td>
				</tr>
				@endforeach
				@endforeach
			@endforeach
			</tbody>
		</table>
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