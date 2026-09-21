
<div class="modal-body" style="padding:10px 20px;">

<div class="table-responsive printableData">
<table class="table-bordered pd-5 mytable" style="border:1px solid #eee; border-collapse:collapsed;" border="1">
@php
$sno	=	1;
@endphp
@foreach($data as $vendor)
@php
	$total_eois		=	0;
	$total_orders	=	0;
	$total_resource	=	0;
	$total_tax		=	0;
	$total_value	=	0;
@endphp
<tr>
	<td class="v-top" style="padding:0px!important; width:100%;">
		@if($vendor->orders)
		<table style="width:100%; border:1px solid #eee; border-collapse:collapsed;" border="1">
			<tr class="bg-primary">
				<td colspan="9" nowrap style="width:350px!important;"><b>Firm Name - {{$vendor->shortname}}</b></td>
			</tr>
			<tr class="myheadbg">
				<td class="text-center" nowrap><b>S. No.</b></td>
				<td class=""><b>EoI Number</b></td>
				<td class=""><b>Order Number</b></td>
				<td class="text-center"><b>Date</b></td>
				<td class="text-center"><b>Due Date</b></td>
				<td class="text-center"><b>Resource</b></td>
				<td class="text-center"><b>Order Value</b></td>
				<td class="text-center"><b>Tax Value</b></td>
				<td class="text-center"><b>Total Value</b></td>
			</tr>
			@foreach($vendor->orders as $order)
			<tr>
				<td nowrap class="text-center">{{$sno++}}</td>
				<td nowrap>
					@if($order->project_name) {{$order->project_name}} <br> @endif
					@if($order->releasedate)Date - {{date('d\-m\-Y',strtotime($order->releasedate))}} <br> @endif
					@if($order->eoinumber)<b>{{$order->eoinumber}}</b> [{{$order->detail_count}}] <br>@endif
					EoI Value - {{$order->eoi_total_value}}
				</td>
				<td nowrap class="">{{$order->ordernumber}} {{$order->deployedCount}} @if($order->isExtended) <i class="fa fa-info-circle" title="Extended"></i> @endif</td>
				<td nowrap class="text-center">@if($order->orderdate){{date('d\-m\-Y',strtotime($order->orderdate))}}@endif</td>
				<td nowrap class="text-center">@if($order->workorderduedate){{date('d\-m\-Y',strtotime($order->workorderduedate))}}@endif</td>
				<td nowrap class="text-center">
				@php
				$dates	=	DB::table('eoi_resource_deployment')
							->select('startDate','endDate')
							->where('orderid',$order->orderid)
							->whereIn('deployment_status',['Pending','Active','Extended','Released'])
							->get();
				@endphp
				@if($dates)
				<table class="mytable" style="border:1px solid #eee; border-collapse:collsped;" border="1">
					<tr><td>Start [{{$order->orderid}}]</td><td>End</td></tr>
					@foreach($dates as $dt)
					<tr>
						<td>@if($dt->startDate){{date('d\-m\-Y',strtotime($dt->startDate))}}@endif</td>
						<td>@if($dt->endDate){{date('d\-m\-Y',strtotime($dt->endDate))}}@endif</td>
					</tr>
					@endforeach
				</table>
				@endif
				</td>
				<td nowrap class="text-center">{{$order->order_resource_value}}</td>
				<td nowrap class="text-center">{{$order->order_tax_value}}</td>
				<td nowrap class="text-center">{{$order->order_value}}</td>
			</tr>
			@php
				$total_resource	=	$total_resource+$order->order_resource_value;
				$total_tax		=	$total_tax+$order->order_tax_value;
				$total_value	=	$total_value+$order->order_value;			
			@endphp
			@endforeach
			<tr>
				<td colspan="6" class="text-right"><b>Total</b></td>
				<td class="text-center"><b>{{$total_resource}}</b></td>
				<td class="text-center"><b>{{$total_tax}}</b></td>
				<td class="text-center"><b>{{$total_value}}</b></td>
			</tr>
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


<script>
document.querySelectorAll('.format-indian').forEach(function(el) {
    el.innerText = formatIndianNumber(el.dataset.value);
});
</script>