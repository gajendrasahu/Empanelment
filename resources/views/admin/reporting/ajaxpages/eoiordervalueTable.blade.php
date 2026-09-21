
<div class="modal-body" style="padding:10px 20px;">

<div class="table-responsive printableData">
<table class="table-bordered pd-5 mytable" style="border:1px solid #eee; border-collapse:collapsed;" border="1">
<tr>
	<td class="text-center width-30" nowrap><b>S. No.</b></td>
	<td nowrap class="width-200"><b>Project Name & EoI Number</b></td>
</tr>
@foreach($data as $eoi)
<tr>
	<td class="text-center v-top">{{$loop->iteration}}</td>
	<td>@if($eoi->project_name) {{$eoi->project_name}}<br> @endif {{$eoi->engagementname}}<br><b>{{$eoi->eoinumber}}</b><br>Resource Count : {{$eoi->resourceCount}}<br> EoI Value : {{$eoi->eoi_total_value}}</td>
	<td style="padding:0px!important; vertical-align:top;">
		@if($eoi->orders)
		<table style="width:100%; border:1px solid #eee; border-collapse:collapsed;" border="1">
			<tr class="myheadbg">
				<td nowrap><b>Firm Name</b></td>
				<td nowrap><b>Order Number</b></td>
				<td nowrap class="text-center"><b>Order Date</b></td>
				<td nowrap class="text-center"><b>Due Date</b></td>
				<td nowrap class="text-center"><b>Resources</b></td>
				<td nowrap class="text-center"><b>Order Value</b></td>
				<td nowrap class="text-center"><b>Tax Value</b></td>
				<td nowrap class="text-center"><b>Total Value</b></td>
			</tr>
			@foreach($eoi->orders as $order)
			<tr>
				<td nowrap style="width:80px;">{{$order->shortname}}</td>
				<td nowrap>{{$order->ordernumber}}</td>
				<td nowrap class="width-100 text-center">{{date('d\-m\-Y',strtotime($order->orderdate))}}</td>
				<td nowrap class="width-100 text-center">{{date('d\-m\-Y',strtotime($order->workorderduedate))}}</td>
				<td nowrap class="width-100 text-center">{{$order->resourceCount}}</td>
				<td nowrap class="width-100 text-center">{{$order->order_resource_value}}</td>
				<td nowrap class="width-100 text-center">{{$order->order_tax_value}}</td>
				<td nowrap class="width-100 text-center">{{$order->order_value}}</td>
			</tr>
			@endforeach
		</table>
		@endif
	</td>
</tr>
@endforeach
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