
<div class="modal-body" style="padding:10px 20px;">

<div class="table-responsive printableData">
<table class="table-bordered pd-5 mytable" style="border:1px solid #eee; border-collapse:collapsed;" border="1">
@php
$eoi_index		=	0;
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
		@if($vendor->eois)
		<table style="width:100%; border:1px solid #eee; border-collapse:collapsed;" border="1">
			<tr class="bg-primary">
				<td colspan="2" nowrap style="width:350px!important;"><b>{{$vendor->shortname}} - EoI</b></td>
			</tr>
			@foreach($vendor->eois as $eoi)
			@php
			$total_eois++;
			$eoi_index++;
			@endphp
			<tr>
				<td class="v-top" style="width:350px!important;"><b>{{$eoi_index}}) {{$eoi->eoinumber}}</b><br>Release Date : {{date('d\-m\-Y',strtotime($eoi->releasedate))}}<br>Resources : {{$eoi->eoiresourceCount}}<br>EoI Value : {{$eoi->eoi_total_value}}</td>
				<td class="v-top" style="padding:0px!important; width:100%;">
					@if($eoi->orders)
					<table style="width:100%; border:1px solid #eee; border-collapse:collapsed;" border="1">
						<tr class="myheadbg">
							<td nowrap><b>Order Number</b></td>
							<td nowrap class="text-center"><b>Date</b></td>
							<td nowrap class="text-center"><b>Due Date</b></td>
							<td nowrap class="text-center"><b>Resource</b></td>
							<td nowrap class="text-center width-100"><b>Value</b></td>
							<td nowrap class="text-center width-100"><b>Tax</b></td>
							<td nowrap class="text-center width-100"><b>Total</b></td>
						</tr>
						@foreach($eoi->orders as $order)
						<tr>
							<td><b>{{$order->ordernumber}} @if($order->isExtended==1) <i class="fa fa-info-circle" title="Extended"></i> @endif</b></td>
							<td nowrap class="text-center">{{date('d\-m\-Y',strtotime($order->orderdate))}}</td>
							<td nowrap class="text-center">{{date('d\-m\-Y',strtotime($order->workorderduedate))}}</td>
							<td class="text-center width-100">{{$order->orderresourceCount}}</td>
							<td class="text-center width-100">{{$order->order_resource_value}}</td>
							<td class="text-center width-100">{{$order->order_tax_value}}</td>
							<td class="text-center width-100">{{$order->order_value}}</td>
						</tr>
						@php
							$total_orders++;
							$total_resource	=	$total_resource+$order->order_resource_value;
							$total_tax		=	$total_tax+$order->order_tax_value;
							$total_value	=	$total_value+$order->order_value;
						@endphp
						@endforeach
					</table>
					@endif
				</td>
			</tr>
			@endforeach
		</table>
		@endif
	</td>
</tr>
<tr class="myheadbg text-center">
	<td><b>Summary for {{$vendor->shortname}} : Total EoI(s) : {{$total_eois}} | Total Order(s) : {{$total_orders}} | Total Resource Value : {{$total_resource}} | Total Tax Value : {{$total_tax}} | Total Value : {{$total_value}}</b></td>
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