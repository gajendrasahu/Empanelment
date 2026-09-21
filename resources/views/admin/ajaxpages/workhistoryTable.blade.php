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
<div style="width:100%; height:500px; max-height:500px; overflow:auto;">
<table style="width:100%; border:1px solid #ddd; border-collapse:collapse;" border="1">
	<tr class="myheadbg">
		<td colspan="7" style="padding:0px 0px 0px 5px; vertical-align:middle; height:25px; line-height:25px;">CUSTOMER & SLOT DETAIL
			
<button type="button" class="btn btn-info mygridbtn" style="width:100px; float:right; background-color:skyblue!important;" onclick="Close()">CLOSE</button>				
		
		</td>
	</tr>
	<tr class="myheadbg">
		<td class="mytdleftwhite">NAME</td>
		<td class="mytdleftwhite">MOBILE NUMBER</td>
		<td class="mytdleftwhite">EMAIL</td>
		<td class="mytdleftwhite">ADDRESS</td>
		<td class="mytdleftwhite center">SERVICE DATE</td>
		<td class="mytdleftwhite center">SLOT TIME</td>
	</tr>
	<tr>
		<td class="mytd"><b>{{e($order->customername)}} @if($order->customermiddlename!='') {{e($order->customermiddlename)}} @endif  @if($order->customerlastname!='') {{e($order->customerlastname)}} @endif</b></td>
		<td class="mytd">{{e($order->customermobilenumber)}}</td>
		<td class="mytd">{{e($order->email)}}</td>
		<td class="mytd">{{e($order->completeaddress)}}</td>
		<td class="mytd center"><b>{{e(date('d\-m\-Y',strtotime($order->servicedate)))}}</b></td>
		<td class="mytd center"><b>{{e(date('h:i A',strtotime($order->slottime)))}}</b></td>
	</tr>

</table>
<table style="width:100%; border:1px solid #ddd; border-collapse:collapse;" border="1">
	<tr class="myheadbg">
		<td colspan="8" style="padding:0px 0px 0px 5px; vertical-align:middle; height:25px; line-height:25px;">WORK HISTORY
		
		</td>
	</tr>
	<tr class="myheadbg">
		<td style="width:140px;" class="mytdleftwhite center">ORDER DATE</td>
		<td style="width:140px;" class="mytdleftwhite center">ASSINGED ON</td>
		<td class="mytdleftwhite">ASSINGED TO</td>
		<td style="width:140px;" class="mytdleftwhite center">STARTED</td>
		<td style="width:80px;" class="mytdleftwhite center">START PIN</td>
		<td style="width:140px;" class="mytdleftwhite center">COMPLETED</td>
		<td style="width:80px;" class="mytdleftwhite center">END PIN</td>
		<td class="mytdleftwhite">COMPLETED BY</td>
	</tr>
	<tr>
		<td class="mytd center">{{date('d\-m\-Y, h:i A',strtotime($orderdetail->creationdate))}}</td>
		<td class="mytd center">@if($orderdetail->assignedtime!='') {{date('d\-m\-Y, h:i A',strtotime($orderdetail->assignedtime))}} @endif</td>
		<td class="mytd">{{$order->assignedname}} @if($order->assignedmiddlename!='') {{$order->assignedmiddlename}} @endif  @if($order->assignedlastname!='') {{$order->assignedlastname}} @endif @if($order->assignedname!='') - {{$order->assignednumber}} @endif</td>
		<td style="padding:0px; width:150px;" class="center">
		@if($orderdetail->workstartpin!='')
			{{ date('d\-m\-Y, h:i A',strtotime($orderdetail->workstarttime)) }}
		@else
		<button type="button" class="btn btn-info mygridbtn startbtn" style="width:100%;" onclick="SetStartTime({{$orderdetail->detailid}},'{{route('store.starttime')}}')">START TASK
		</button>
		@endif
		</td>
		<td class="mytd center">
		@if($orderdetail->workstartpin!='')
			{{ $orderdetail->workstartpin }}
		@endif
		</td>
		<td style="padding:0px; width:150px;" class="center">
		@if($orderdetail->workendpin!='')
			{{ date('d\-m\-Y, h:i A',strtotime($orderdetail->workendtime)) }}
		@else
		<button type="button" class="btn btn-info mygridbtn endbtn" onclick="SetEndTime({{$orderdetail->detailid}},'{{route('store.endtime')}}')" style="width:100%;">END TASK
		</button>
		@endif
		</td>
		<td class="mytd center">
		@if($orderdetail->workendpin!='')
			{{ $orderdetail->workendpin }}
		@endif
		</td>
		<td class="mytd">{{ $orderdetail->endedbyuser }}</td>
	</tr>
	
</table>
<table style="width:100%; border:1px solid #ddd; border-collapse:collapse;" border="1">
	<tr class="myheadbg">
		<td colspan="5" class="mytdleftwhite">ON HOLD DETAIL</td>
	</tr>
	<tr>
		<td class="center" colspan="5">--NO RECORD FOUND--</td>
	</tr>
</table>
<table style="width:100%; border:1px solid #ddd; border-collapse:collapse;" border="1">
	<tr class="myheadbg">
		<td colspan="5" class="mytdleftwhite">INTER CHANGE DETAIL</td>
	</tr>
	<tr class="myheadbg">
		<td class="center" style="width:50px;">S.NO.</td>
		<td class="center" style="width:130px;">DATE</td>
		<td class="mytdleftwhite">REASON</td>
		<td class="mytdleftwhite" style="width:200px;">FROM VENDOR</td>
		<td class="mytdleftwhite" style="width:200px;">TO VENDOR</td>
	</tr>
	@php $r=0; @endphp
	@foreach($interchange as $inter)
	@php $r=$r+1; @endphp
	<tr>
		<td class="center">{{$r}}</td>
		<td class="center">{{date('d\-m\-Y, h:i A',strtotime($inter->interchangedate))}}</td>
		<td class="mytd">{{$inter->interchangereason}}</td>
		<td class="mytd">{{ucwords(strtolower($inter->from_fullname))}}</td>
		<td class="mytd">{{ucwords(strtolower($inter->to_fullname))}}</td>
	</tr>
	@endforeach
	@if($r==0)
	<tr>
		<td class="center" colspan="5">--NO INTER CHANGE DETAIL FOUND--</td>
	</tr>
	@endif
</table>
<table style="width:100%; border:1px solid #ddd; border-collapse:collapse;" border="1">
	<tr class="myheadbg">
		<td class="mytdleftwhite" style="width:50%;">START PICTURES</td>
		<td class="mytdleftwhite" style="width:50%;">END PICTURES</td>
	</tr>
	@if($photos->count()!=0)
	<tr>
		<td class="mytd" style="vertical-align:top!important;">
		@foreach($photos as $pics)
			@if($pics->picturetime=='STARTING')
			<div style="width:140px; text-align:center; margin-right:5px; float:left; margin-bottom:10px;">
			<a href="{{ asset('storage/'.$pics->pictureurl) }}" target="_blank" style="margin-left:5px!important; text-decoration:none;">
				<img src="{{ asset('storage/'.$pics->pictureurl) }}" alt="Uploaded Image" style="width:130px; margin-left:5px!important;">
				<br>{{date('d\-m\-Y, h:i A',strtotime($pics->creationdate))}}
			</a>
			</div>
			@endif
		@endforeach
		</td>
		<td class="mytd" style="vertical-align:top!important;">
		@foreach($photos as $pics)
			@if($pics->picturetime=='FINISHING')
			<div style="width:140px; text-align:center; margin-right:5px; float:left; margin-bottom:10px;">
			<a href="{{ asset('storage/'.$pics->pictureurl) }}" target="_blank" style="margin-left:5px!important; text-decoration:none;">
				<img src="{{ asset('storage/'.$pics->pictureurl) }}" alt="Uploaded Image" style="width:130px; margin-left:5px!important;">
				<br>{{date('d\-m\-Y, h:i A',strtotime($pics->creationdate))}}
			</a>
			</div>
			@endif
		@endforeach
		</td>
	</tr>
	@else
	<tr>
		<td class="center" colspan="2">--NO RECORD FOUND--</td>
	</tr>
	@endif
</table>

</div>