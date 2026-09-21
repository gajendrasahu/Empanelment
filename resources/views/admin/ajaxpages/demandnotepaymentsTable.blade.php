@php
$i=1;
@endphp
<table class="mytable" border="1">
<input type="hidden" name="orderid" id="orderid" value="{{Crypt::encrypt($order->orderid)}}">
	@foreach($notes as $note)
	@php
	$balance	=	$note->demandnoteamount-$note->amountreceived;
	@endphp
	<tr class="myheadbg form-label font-14">
		<td class="padding-5 center width-30">S.No.</td>
		<td class="padding-5">Demand Note Number</td>
		<td class="padding-5 width-100 center">Date</td>
		<td class="padding-5 width-180" style="text-align:right;">Demand Note Amount</td>
		<td class="padding-5 width-130" style="text-align:right;">Amount Received</td>
		<td class="padding-5 width-100" style="text-align:right;">Balance</td>
		<td class="padding-5 width-200">Transaction Detail</td>
		<td class="padding-5 width-200">Remark</td>
	</tr>
	<tr class="font-12 form-label">
		<td class="padding-5 center">{{$loop->iteration}}</td>
		<td class="padding-5">{{$note->demandnotenumber}}</td>
		<td class="padding-5 center">{{date('d\-m\-Y',strtotime($note->payment_date))}}
		@if($note->paymentfile!='')
		<a href="{{ asset('storage/'.$note->paymentfile) }}" target="_blank"><i class="fa fa-file-pdf-o"></i></a>
		@endif
		
		</td>		
		<td class="padding-5" style="text-align:right;">{{$note->demandnoteamount}}</td>
		<td class="padding-5" style="text-align:right;">{{$note->amountreceived}}</td>
		<td class="padding-5" style="text-align:right;">{{$balance}}</td>
		<td class="padding-5">{{$note->transactiondetail}}</td>
		<td class="padding-5">{{$note->payment_remark}}</td>
	</tr>
	@if($note->payments->count()>0)
		@foreach($note->payments as $payments)
		@php
		$balance	=	$balance-$payments->amountreceived;
		@endphp
		<tr class="font-12 form-label">
			<td class="padding-5 center"></td>
			<td class="padding-5"></td>
			<td class="padding-5 center">{{date('d\-m\-Y',strtotime($payments->payment_date))}}
			@if($payments->paymentfile!='')
			<a href="{{ asset('storage/'.$payments->paymentfile) }}" target="_blank"><i class="fa fa-file-pdf-o"></i></a>
			@endif
			</td>						
			<td class="padding-5" style="text-align:right;"></td>
			<td class="padding-5" style="text-align:right;">{{$payments->amountreceived}}</td>
			<td class="padding-5" style="text-align:right;">{{$balance}}</td>
			<td class="padding-5">{{$payments->transactiondetail}}</td>
			<td class="padding-5">{{$payments->payment_remark}}</td>
		</tr>	
		@endforeach
	@endif
	@endforeach
</table>
