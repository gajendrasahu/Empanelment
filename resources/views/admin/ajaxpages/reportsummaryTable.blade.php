@php 
$t=1;
@endphp
@if($data->count()>0)
<tr>
<td>
<table class="mytable table pd-10" style="border:1px solid #eee!important; border-collapse:collapse!important;" border="1">
	<tr class="myheadbg font-bold font-12">
		<td colspan="2"></td>
		<td nowrap colspan="3" class="center">Work Orders</td>
		<td nowrap colspan="5" class="center">Resources</td>
		<td nowrap colspan="3" class="center">Invoices</td>
	</tr>
	<tr class="myheadbg font-bold font-12">
		<td nowrap class="center width-70">S. No.</td>
		<td nowrap>EoI Number & Project Name</td>
		<td nowrap class="center">Total</td>
		<td nowrap class="center">Active</td>
		<td nowrap class="center">Expired</td>
		<td nowrap class="center">Total</td>
		<td nowrap class="center">Deployed</td>
		<td nowrap class="center">Active</td>
		<td nowrap class="center">Released</td>
		<td nowrap class="center">Undeployed</td>
		<td nowrap class="center">Total Value</td>
		<td nowrap class="center">Total Submitted</td>
		<td nowrap class="center">Total Paid</td>
	</tr>
	@foreach($data as $item)
	<tr>
		<td class="center">{{$loop->iteration}}</td>
		<td>{{$item->eoinumber}}<br><span class="small-text">{{$item->projecttitle}}</span></td>
		<td class="center">{{$item->total_work_orders}}</td>
		<td class="center">{{$item->active_work_orders}}</td>
		<td class="center">{{$item->expired_work_orders}}</td>
		<td class="center">{{$item->total_resources}}</td>
		<td class="center">{{$item->deployed_resources}}</td>
		<td class="center">{{$item->active_resources}}</td>
		<td class="center">{{$item->released_resources}}</td>
		<td class="center">{{$item->undeployed_resources}}</td>
		<td class="center">{{$item->order_value}}</td>
		<td class="center">{{$item->invoice_raised}}</td>
		<td class="center">{{$item->paid_amount}}</td>
	</tr>
	@endforeach
</table>
</td>
</tr>
@else
<tr><td class="center">--Please select summary type--</td></tr>
@endif
