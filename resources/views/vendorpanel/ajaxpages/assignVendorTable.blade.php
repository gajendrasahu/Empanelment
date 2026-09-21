@php
$t=100;
@endphp
<form name="storefrm" id="storefrm" action="#" method="post">
<table class="mytable" border="1">
	<tr class="myheadbg"><td class="mytd" colspan="6" style="color:white!important; font-size:16px;">ASSIGN VENDOR</td></tr>
	<tr><td class="mytd" colspan="6"><i>For a self-employed vendor with no employees, simply select the vendor's name and assign it.</i></td></tr>	
	<tr>
		<td class="mytd" style="width:200px;">VENDOR NAME *</td>
		<td>
			<select class="select2 form-control" name="vendid" id="vendid" onKeyPress="return OnKeyPress(this, event)"  data-placeholder="VENDOR NAME" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}"  data-width="100%" onchange="GetEmployees(this.value,'{{ route('employees.list') }}')">
				<option value=""></option>
				@foreach ($vendors as $itm)
				<option value="{{ $itm->vendorid }}" @if($parentvendor) @if($itm->vendorid==$parentvendor->parentvendorid) selected @endif @endif  @if($itm->vendorid==$data->vendorid) selected @endif>{{ strtoupper($itm->name) }} [{{$itm->mobilenumber}}]</option>
				@endforeach
			</select>
		</td>
	</tr>
	<tr>
		<td class="mytd">EMPLOYEE NAME</td>
		<td>
			<select class="select2 form-control" name="employeeid" id="employeeid" onKeyPress="return OnKeyPress(this, event)"  data-placeholder="EMPLOYEE NAME" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}" data-width="100%">
				<option value=""></option>
				@if($employees)
				@foreach ($employees as $itm)
				<option value="{{ $itm->vendorid }}" @if($itm->vendorid==$data->vendorid) selected @endif>{{ strtoupper($itm->name) }} [{{$itm->mobilenumber}}]</option>
				@endforeach
				@endif
			</select>
		</td>
	</tr>
	<tr id="assignmsg" style="display:none;">
		<td class="mytd assignmsg" style="text-align:center; font-size:16px;" colspan="2"></td>
	</tr>
</table>
<table id="addresstable" class="mytable" border="1">
	<tr class="myheadbg"><td class="mytd" colspan="6" style="color:white!important; font-size:16px;">SERVICE LOCATION</td></tr>
	<tr class="myheadbg">
		<td class="mytdcenterwhite" style="width:30px;"></td>
		<td class="mytdcenterwhite" style="width:30px;">S.NO.</td>
		<td class="mytdleftwhite">STATE</td>
		<td class="mytdleftwhite">CITY</td>
		<td class="mytdleftwhite">POSTAL CODE</td>
		<td class="mytdleftwhite">ADDRESS</td>
	</tr>
	@php $i=1; @endphp
	@foreach($address as $addr)
	<tr>
		<td class="mytdcenter">
			<input type="radio" id="addressid" name="addressid" value="{{$addr->addressid}}" @if($data->addressid==$addr->addressid) checked @endif @if($addr->ismaster==1) checked @endif>
		</td>
		<td class="mytdcenter">{{$i++}}</td>
		<td class="mytdleft">{{$addr->statename}}</td>
		<td class="mytdleft">{{$addr->cityname}}</td>
		<td class="mytdleft">{{$addr->postalcode}}</td>
		<td class="mytdleft">{{$addr->address}}</td>
	</tr>
	@endforeach
	@if($i==1)
	<tr>
		<td class="mytdcenter" colspan="6">{{__('labels.noaddress')}}</td>
	</tr>
	@endif
	<tr><td colspan="6">&nbsp;</td></tr>
	<tr>
		<td colspan="6">
<table class="mytable" border="1">
<tr class="myheadbg"><td class="mytdleftwhite" style="font-size:14px;" colspan="5">{{__('labels.newaddress')}}</td></tr>
<tr>
	<td style="padding:0px; width:200px;">
		<select class="select2" name="statid" id="statid" onKeyPress="return OnKeyPress(this, event)"  data-placeholder="STATE NAME" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}"  data-width="200" onchange="GetCitiesWithId(this.value,'{{ route('cities.list') }}','ctyid')">
			<option value=""></option>
			@foreach ($state as $itm)
			<option value="{{ $itm->stateid }}">{{ strtoupper($itm->statename) }}</option>
			@endforeach
		</select>
	</td>
	<td style="padding:0px; width:200px;">
		<select class="select2" name="ctyid" id="ctyid" onKeyPress="return OnKeyPress(this, event)"  data-placeholder="CITY NAME" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}"  data-width="200">
			<option value=""></option>
		</select>
	</td>
	<td style="padding:0px; width:100px;">
	<input type="text" class="selectbx" name="postalcode" id="postalcode" value="" placeholder="{{__('labels.postalcode')}}" autocomplete="off" onKeyPress="return OnKeyPress(this, event)" style="width:100px; margin-top:4px; font-size:12px;" />
	
	</td>
	<td style="padding:0px;">
		<input type="text" class="selectbx" style="width:100%; font-size:12px;" name="completeaddress" id="completeaddress" value="{{old('completeaddress')}}" placeholder="ENTER COMPLETE ADDRESS" autocomplete="off"/>
	</td>
</tr>
<tr id="storeaddress" style="display:none;">
	<td class="mytd storeaddress" style="text-align:center; font-size:14px;" colspan="4"></td>
</tr>

<tr>
	<td colspan="4" style="padding:0px;">
		<button style="float:right; width:150px;" class="btn btn-info mygridbtn addressbtn" onclick="AddAddress('{{route('store.address')}}','{{Crypt::encrypt($orderid)}}')">ADD NEW ADDRESS</button>
	</td>
</tr>
</table>
		</td>
	</tr>
	
	<tr><td colspan="6">&nbsp;</td></tr>
	<tr>
		<td colspan="6">
			<button type="button" style="float:right; width:100px;" class="btn btn-info mygridbtn" onclick="Close()">CLOSE</button>
			
			<button type="button" style="float:right; width:150px; margin-right:5px;" class="btn btn-info mygridbtn" onclick="AssignVendor('{{route('store.assignvendor')}}','{{Crypt::encrypt($orderid)}}')">ASSIGN VENDOR</button>
		</td>
	</tr>
	<tr><td colspan="6">&nbsp;</td></td>
</table>
</form>