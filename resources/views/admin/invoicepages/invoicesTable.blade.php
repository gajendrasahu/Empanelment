<div class="modal-header">
	<h5 class="modal-title" style="float:left;"><b>Firm Name : {{$vendor->companyname}}</b></h5>
	<button type="button" class="btn btn-close" style="float:right;" data-bs-dismiss="modal" onclick="ClsRecords()">Close</button>
</div>
<div class="modal-body" style="max-height:500px; overflow:scroll;">
<table class="table table-bordered table-striped" style="border:1px solid #eee;" border="1" width="100%" cellpadding="6">
<thead>
<tr class="myheadbg">
	<th class="center">S.No.</th>
	<th class="center">Request Date</th>
	<th class="center">Invoice Date</th>
	<th>Invoice Number</th>
	<th class="text-right">Taxable</th>
	<th class="text-right">Tax</th>
	<th class="text-right">Invoice</th>
	<th class="text-right">Paid</th>
	<th class="text-right">Balance</th>
</tr>
</thead>
<tbody>
@foreach($data as $record)
<tr>
	<td class="center">{{$loop->iteration}}</td>
	<td class="center">{{date('d\-m\-Y',strtotime($record->request_date))}}</td>
	<td class="center">{{date('d\-m\-Y',strtotime($record->invoice_date))}}</td>
	<td>{{$record->invoice_number}}</td>
	<td class="text-right">
		<span class="format-indian" data-value="{{$record->amount_value}}"></span>
	</td>
	<td class="text-right">
		<span class="format-indian" data-value="{{$record->tax_value}}"></span>
	</td>
	<td class="text-right">
		<span class="format-indian" data-value="{{$record->invoice_value}}"></span>
	</td>
	<td class="text-right">
		<span class="format-indian" data-value="{{$record->paid_value}}"></span>
	</td>
	<td class="text-right">
		<span class="format-indian" data-value="{{$record->balance_value}}"></span>
	</td>
	
</tr>
@endforeach
@if($data->count()==0)
	<tr><td class="center" colspan="9">--No Record Found--</td></tr>
@endif
</tbody>

</table>
</div>
<div class="modal-footer"></div>

<script>
document.querySelectorAll('.format-indian').forEach(function(el) {
    el.innerText = formatIndianNumber(el.dataset.value);
});

</script>