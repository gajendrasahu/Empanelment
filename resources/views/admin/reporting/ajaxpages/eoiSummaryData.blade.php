<div class="modal-body" style="padding:10px 20px;">
	<div class="table-responsive printableData">
		<table class="table table-bordered font-12">
			<tbody>
				@php
				$preProject	=	"";
				@endphp
				@foreach($data as $eoi)
				@if($preProject!=$eoi->project_name)
				<tr class="table-label-fade font-bold">
					<td colspan="3">{{$eoi->project_name}}</td>
				</tr>
				<tr class="table-label-fade font-bold">
					<td nowrap class="text-center width-30">S. No.</td>
					<td nowrap>EoI Detail</td>
					<td nowrap></td>
				</tr>
				@php
				$preProject	=	$eoi->project_name;
				@endphp
				@endif
				<tr>
					<td class="width-30 text-center">{{$loop->iteration}}
					@if($eoi->isClosed==1)
					<span>
						<span class="bds bds-pending">Closed</span>
					</span>
					@endif
					</td>
					<td><b>{{$eoi->eoinumber}}</b><br><br><b>{{$eoi->departmentname}}</b><br><br>{{$eoi->engagementname}}</td>
					<td class="padding-0">
						<table class="width-full mytable pd-5" border="1">
						<tr class="table-label-fade font-bold">
							<td nowrap colspan="4"></td>
							<td class="text-center" colspan="4">Order Wise Resource Detail</td>
							<td class="text-center" colspan="2">Invoice</td>
						</tr>
						<tr class="table-label-fade font-bold">
							<td nowrap>Order Number</td>
							<td nowrap>Firm</td>
							<td nowrap class="text-center">Order Date</td>
							<td nowrap class="text-center">Due Date</td>
							<td nowrap>Total</td>
							<td nowrap>Active</td>
							<td nowrap>Released</td>
							<td nowrap>Undeployed</td>
							<td nowrap class="text-center">Submitted</td>
							<td nowrap class="text-center">Paid</td>
						</tr>
						@foreach($eoi->orders as $order)
							<tr>
								<td nowrap class="font-bold">{{$order->ordernumber}}</td>
								<td nowrap class="font-bold width-100">{{$order->shortname}}</td>
								<td nowrap class="text-center width-100">{{date('d\-m\-Y',strtotime($order->orderdate))}}</td>
								<td nowrap class="text-center width-100">{{date('d\-m\-Y',strtotime($order->workorderduedate))}}</td>
								<td class="width-30 text-center">{{$order->total_resource}}</td>
								<td class="width-30 text-center">{{$order->active_total}}</td>
								<td class="width-30 text-center">{{$order->released_total}}</td>
								<td class="width-30 text-center">{{$order->pending_total}}</td>
								<td class="width-100 text-center">{{$order->invoice_submitted}}</td>
								<td class="width-100 text-center">{{$order->invoice_paid}}</td>
							</tr>
						@endforeach
						</table>
					</td>
				</tr>
				@endforeach
				@if($data->count()==0)
				<tr><td colspan="3" class="text-center">--No Record--</td></tr>
				@endif
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
		<a data-bs-dismiss="modal" onclick="printData()" class="eoi-btn" style="cursor:pointer; margin-right:10px;">
			<i class="fa fa-print"></i> Print
		</a>
	</div>

</div>