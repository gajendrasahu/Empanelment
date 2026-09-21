@php
use Illuminate\Support\Facades\DB;
$i=1;
$ordstatus	=	[];
$ordstatus[0]	=	'PENDING';
$ordstatus[1]	=	'ASSIGNED';
$ordstatus[2]	=	'STARTED';
$ordstatus[3]	=	'COMPLETED';
$ordstatus[-1]	=	'CANCELLED';
@endphp
<tr>
	<td colspan="8">
		<table style="width:100%; border:1px solid #ddd; border-collapse:collapse;" border="1">
			<tr class="myheadbg">
				<td colspan="7">CUSTOMER & SLOT DETAIL
<button type="button" class="btn btn-info mygridbtn orderrecord" style="width:100px; float:right; background-color:skyblue!important;" data-id="{{ $order->orderid }}">
	<i class="fa fa-refresh"></i> RELOAD
</button>				
				
				</td>
			</tr>
			<tr class="myheadbg">
				<td class="mytdwhite">NAME</td>
				<td class="mytdwhite">MOBILE NUMBER</td>
				<td class="mytdwhite">EMAIL</td>
				<td class="mytdwhite">ADDRESS</td>
				<td class="mytdwhite center">SERVICE DATE</td>
				<td class="mytdwhite center">SLOT TIME</td>
				<td class="mytdwhite center"></td>
			</tr>
			<tr>
				<td class="mytd"><b>{{e($customer->name)}} @if($customer->middlename!='') {{e($customer->middlename)}} @endif  @if($customer->lastname!='') {{e($customer->lastname)}} @endif</b></td>
				<td class="mytd">{{e($customer->mobilenumber)}}</td>
				<td class="mytd">{{e($customer->email)}}</td>
				<td class="mytd">{{e($customer->completeaddress)}}</td>
				<td class="mytd center"><b>{{e(date('d\-m\-Y',strtotime($order->servicedate)))}}</b></td>
				<td class="mytd center"><b>{{e(date('h:i A',strtotime($order->slottime)))}}</b></td>
				<td style="padding:0px;">
				<button type="button" class="btn btn-info mygridbtn vendorassignment" style="width:100%;" onclick="ViewVendors('{{route('order.assignvendor')}}','{{Crypt::encrypt($order->orderid)}}')">
					<i class="fa fa-user"></i> VENDOR & ADDRESS
				</button>
				</td>
			</tr>
		</table>
		<table style="width:100%; border:1px solid #ddd; border-collapse:collapse;" border="1">
			<tr class="myheadbg">
				<td colspan="9">TASK DETAILS</td>
			</tr>
			<tr class="myheadbg">
				<td class="mytdwhite center" style="width:50px;">S.NO.</td>
				<td class="mytdwhite">SERVICE TITLE</td>
				<td class="mytdwhite text-right" style="width:80px;">MRP</td>
				<td class="mytdwhite text-right" style="width:80px;">TAXABLE</td>
				<td class="mytdwhite text-right" style="width:110px;">QUANTITY</td>
				<td class="mytdwhite text-right" style="width:80px;">TOTAL</td>
				<td class="center" style="width:100px;">WORK STATUS</td>
				<td class="center" style="width:70px;"></td>
			</tr>
			@php $r=0; @endphp
			@foreach($orderdetail as $detail)
			@php
				$r=$r+1;
				$categoryname	=	"";
			@endphp
			
			<tr>
				<td class="mytdwhite center" style="width:50px;">{{$r}}</td>
				<td class="mytd">{{ ucwords(strtolower($detail->servicetitle)) }}</td>
				<td class="mytd text-right">{{ $detail->mrp }}</td>
				<td class="mytd text-right">{{ $detail->taxable }}</td>
				<td class="mytd text-right">{{ $detail->quantity }}</td>
				<td class="mytd text-right">{{ $detail->taxable*$detail->quantity }}</td>
				<td class="center">{{$ordstatus[$detail->orderstatus]}}</td>
				<td style="padding:0px;">
					@if($detail->orderstatus!=0)
					<button type="button" class="btn btn-info mygridbtn myrecord" onclick="WorkHistory({{$detail->orderid}},{{$detail->detailid}})" style="width:100%;"><i class="fa fa-eye"></i> VIEW</button>
					@endif
				</td>
			</tr>
			@php $categoryname	=	$detail->category; @endphp
			@endforeach
			<tr>
				<td colspan="4" style="text-align:left;">CATEGORY NAME : <b>{{$categoryname}}</b></td>
				<td style="text-align:right;">TOTAL TAXABLE</td>
				<td style="text-align:right;">{{ $order->totaltaxable }}</td>
				<td style="text-align:right;"></td>
				<td style="text-align:right;"></td>
			</tr>
			@if($order->visitingcharge!=0)
			<tr>
				<td colspan="5" style="text-align:right;">VISITING CHARGE</td>
				<td style="text-align:right;">{{ $order->visitingcharge }}</td>
				<td style="text-align:right;"></td>
				<td style="text-align:right;"></td>
			</tr>
			@endif
			<tr>
				<td colspan="4"><b>SERVICE LOCATION</b> <b><i class="fa fa-angle-double-down"></i></b></td>			
				<td style="text-align:right;">TOTAL TAX</td>
				<td style="text-align:right;">{{ $order->totaltaxvalue }}</td>
				<td style="text-align:right;"></td>
				<td style="text-align:right;"></td>
			</tr>
			<tr>
				<td colspan="4">{{$address->address}}, {{$address->postalcode}}</td>					
				<td style="text-align:right;">GRAND TOTAL</td>
				<td style="text-align:right;">{{ $order->netpayable }}</td>
				<td style="text-align:right;"></td>
				<td style="text-align:right;">
				
				</td>
			</tr>
			<tr>
				<td colspan="8">
					<a target="_blank" href="https://www.google.com/maps?q={{$address->latitude}},{{$address->longitude}}" style="float:left; margin-right:5px;">
					<span style="width: 18px; height: 18px; background-color: #007bff; border-radius: 50%; vertical-align:text-center; text-align:center; justify-content: center; align-items: center; display: flex;">
						<i class="fa fa-map-marker" style="color:white; font-size:16px;"></i>
					</span>
					</a>
					Latitude : {{$address->latitude}} | Longitude : {{$address->longitude}}
				</td>			
			</tr>
		</table>
		<table style="width:100%; border:1px solid #ddd; border-collapse:collapse;" border="1">
			<tr class="myheadbg">
				<td colspan="10">TRANSACTION DETAILS</td>
			</tr>
			<tr class="myheadbg">
				<td class="center" style="width:50px;">S.NO.</td>
				<td class="center">DATE</td>
				<td class="center">AMOUNT</td>
				<td class="center">TYPE</td>
				<td class="mytdwhite" style="width:150px;">ORDER ID</td>
				<td class="mytdwhite" style="width:150px;">PAYMENT ID</td>
				<td class="mytdwhite">METHOD</td>
				<td class="mytdwhite" style="width:200px;">REMARK</td>
				<td class="center">FILE</td>
				<td class="center"></td>
			</tr>
			@php $r=0; @endphp
			@foreach($transactions as $transaction)
			@php $r=$r+1; @endphp
			<tr>
				<td class="center">{{$r}}</td>
				<td class="center">
				@if($transaction->invoicefile=='')
				{{e(date('d\-m\-Y, h:i A',strtotime($transaction->paymentdatetime)))}}
				@else
				{{e(date('d\-m\-Y, h:i A',strtotime($transaction->invoicedate)))}}
				@endif
				</td>
				<td class="center">{{e($transaction->netamount)}}</td>
				<td class="center">
					@if($transaction->receiptfile!='') RECEIPT @endif
					@if($transaction->invoicefile!='') INVOICE @endif
				</td>
				<td class="mytd">{{e($transaction->razorpay_order_id)}}</td>
				<td class="mytd">{{e($transaction->razorpay_payment_id)}}</td>
				<td class="mytd">{{e($transaction->paymentmethod)}}</td>
				<td class="mytd">{{e($transaction->paymentremark)}}</td>
				<td class="" style="padding:0px;">
				@if($transaction->paymentfile!='' && $transaction->paymentfile!='NA')
				<a href="{{ asset('storage/'.$transaction->paymentfile) }}" style="text-decoration:none;" target="_blank">
					<button type="button" class="btn btn-info mygridbtn" style="width:100%;">VIEW</button>
				</a>
				@endif
				</td>
				<td class="" style="padding:0px;">
				@if($transaction->receiptfile!='' && $transaction->receiptfile!='NA')
				<a href="{{ asset('storage/receipts/'.$transaction->receiptfile) }}" target="_blank" style="text-decoration:none;">
					<button type="button" class="btn btn-info mygridbtn" style="width:100%;">RECEIPT</button>
				</a>
				@endif
				@if($transaction->invoicefile!='' && $transaction->invoicefile!='NA')
				<a href="{{ asset('storage/invoices/'.$transaction->invoicefile) }}" target="_blank" style="text-decoration:none;">
					<button type="button" class="btn btn-info mygridbtn" style="width:100%;">INVOICE</button>
				</a>
				@endif
				</td>
			</tr>
			@endforeach
		</table>
		
	</td>
</tr>
