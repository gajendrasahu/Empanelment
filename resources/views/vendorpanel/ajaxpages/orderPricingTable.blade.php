@php
$t=100;
@endphp
<form name="storefrm" id="storefrm" action="#" method="post">
<table class="mytable" border="1">
<tr class="myheadbg"><td class="mytdleftwhite" style="font-size:14px;" colspan="3">{{$servicetitle}} <span style="float:right;">BOOKED ON : {{date('d\-m\-Y, H:i A',strtotime($service->orderdate))}}</span></td></tr>
<tr class="myheadbg"><td class="mytdleftwhite" style="font-size:14px;" colspan="3">{{__('labels.addprice')}}</td></tr>
<tr>
	<td style="padding:0px;">
		<input type="text" class="selectbx" name="particular" id="particular" value="" placeholder="{{__('labels.particular')}}" autocomplete="off" onKeyPress="return OnKeyPress(this, event)" style="width:100%; margin-top:4px; font-size:12px;" tabindex="{{$t++}}" />
	</td>
	<td style="padding:0px; width:100px;">
		<input type="text" class="selectbx numbers" name="amount" id="amount" value="" placeholder="0" autocomplete="off" onKeyPress="return OnKeyPress(this, event)" style="width:100%; margin-top:4px; font-size:12px;" tabindex="{{$t++}}" />
	</td>
	<td style="padding:0px; width:100px;">
		<button type="button" style="float:right; width:100%;" class="btn btn-info mygridbtn storepricebtn" onclick="StoreOrderPrice('{{route('store.orderprice')}}','{{Crypt::encrypt($orderid)}}')" tabindex="{{$t++}}">ADD PRICE</button>
	</td style="padding:0px;">
</tr>
<tr id="storeprice" style="display:none;">
	<td class="mytd storeprice" style="text-align:center; font-size:14px;" colspan="4"></td>
</tr>
</table>
<table id="addresstable" class="mytable" border="1">
	<tr class="myheadbg"><td class="mytd" colspan="5" style="color:white!important; font-size:16px;">ADDED PRICE</td></tr>
	<tr class="myheadbg">
		<td class="mytdcenterwhite" style="width:30px;">S.NO.</td>
		<td class="mytdleftwhite">{{__('labels.particular')}}</td>
		<td class="mytdleftwhite" style="width:100px;">{{__('labels.amount')}}</td>
		<td class="mytdleftwhite" style="width:100px; text-align:right;"></td>
		<td class="mytdleftwhite" style="width:100px; text-align:right;"></td>
	</tr>
	@php $i=1; $total=0; @endphp
	@if($data)
	@foreach($data as $itm)
	<tr>
		<td class="mytdcenter">{{$i++}}</td>
		<td class="">
			<input type="text" class="selectbx" style="width:100%;" name="particular{{$itm->recordid}}" id="particular{{$itm->recordid}}" value="{{$itm->particular}}" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}">
		</td>
		<td class="" style="width:100px;">
			<input type="text" class="selectbx" style="width:100px;" name="amount{{$itm->recordid}}" id="amount{{$itm->recordid}}" value="{{$itm->amount}}" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}">
		</td>
		<td class="" style="width:100px; text-align:right;">
			<button type="button" style="float:right; width:100%;" disabled class="btn btn-info mygridbtn storepricebtn{{$itm->recordid}}" tabindex="{{$t++}}">UPDATE</button>
		</td>
		<td class="" style="width:100px;">
			<button type="button" style="width:100%;" disabled class="btn btn-info mygridbtn delbtn{{$itm->recordid}}" tabindex="{{$t++}}">DELETE</button>
		</td>
	</tr>
	@php $total=$total+$itm->amount; @endphp
	@endforeach
	@endif
	@if($i==1)
	<tr>
		<td class="mytdcenter" colspan="5">--{{__('labels.nopricing')}}--</td>
	</tr>
	@else
	<tr class="myheadbg">
		<td class="mytd" style="text-align:right; color:white!important; font-size:14px;" colspan="2"><b>TOTAL</b></td>
		<td class="mytd" style="width:100px; color:white!important; font-size:14px;"><i class="fa fa-inr"></i> {{$total}}</td>
		<td class="mytdleftwhite" style="width:100px; text-align:right;"></td>
		<td class="mytdleftwhite" style="width:100px; text-align:right;"></td>
	</tr>	
	@endif
	<tr>
		<td style="padding:0px; text-align:center;" colspan="5">
			<button type="button" class="btn btn-info mygridbtn" style="width:100px;" onclick="Close()">CLOSE</button>
		</td>
	</tr>
	<tr><td colspan="6">&nbsp;</td></td>
	
</table>
</form>