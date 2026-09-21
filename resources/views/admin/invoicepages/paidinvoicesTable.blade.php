<div class="modal-header">
	<h5 class="modal-title" style="float:left;"><b>Firm Name : {{$vendor->companyname}}</b></h5>
	<button type="button" class="btn btn-close" style="float:right;" data-bs-dismiss="modal" onclick="ClsRecords()">Close</button>
</div>
<div class="modal-body" style="max-height:500px; overflow:scroll;">
<table class="table table-bordered table-striped" style="border:1px solid #eee;" border="1" width="100%" cellpadding="6">
<thead>
<tr class="myheadbg">
	<th class="center">S.No.</th>
	<th class="center">Payment Date</th>
	<th>Payment Method</th>
	<th class="text-right">Transaction Number & Remark</th>
	<th class="text-right">Paid Amount</th>
</tr>
</thead>
<tbody>
@foreach($data as $record)
<tr>
	<td class="center">{{$loop->iteration}}</td>
	<td class="center">{{date('d\-m\-Y',strtotime($record->payment_date))}}</td>
	<td>{{$record->payment_method}}</td>
	<td>
		{{$record->transaction_number}}
		@if($record->payment_remark)
			<br>{{$record->payment_remark}}
		@endif
	</td>
	<td class="text-right">
		<span class="format-indian" data-value="{{$record->paying_amount}}"></span>
	</td>

</tr>
@endforeach
@if($data->count()==0)
	<tr><td class="center" colspan="5">--No Record Found--</td></tr>
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