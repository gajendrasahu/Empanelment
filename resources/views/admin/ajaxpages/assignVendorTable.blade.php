@php
$t=100;
$vid	=	DB::table('customer_order')->where('orderid','=',$orderid)->value('vendorid');
@endphp
<form name="storefrm" id="storefrm" action="#" method="post">
<table class="mytable" border="1">
	<tr class="myheadbg"><td class="mytd" colspan="6" style="color:white!important; font-size:16px;">ASSIGN VENDOR</td></tr>
	<tr>
		<td colspan="6">
		<div style="width:100%; max-height:200px; overflow:auto;">
			<table style="width:100%; border:1px solid #ddd; border-collapse:coppalse;" border="1">
				<tr class="myheadbg">
					<td class="mytd"></td>
					<td class="" style="color:white!important;">
						<input type="text" id="searchVendor" placeholder="Search by name..." style="padding:2px; width:100%;" autocomplete="off">
					</td>
					<td class="mytd" style="color:white!important;">Mobile Number</td>
					<td class="mytd" style="color:white!important;">Security Deposit</td>
					<td class="mytd" style="color:white!important;">Wallet Amount</td>
					<td class="mytd" style="color:white!important;">Availability</td>
					<td class="mytd" style="color:white!important;">Assigned</td>
				<tr>
				@foreach($vendors as $vend)
				<tr class="vendorRow">
					<td style="text-align:center;">
						<input type="radio" name="vendid" @if($vend->vendorid==$vid) checked @endif value="{{$vend->vendorid}}" @if($vend->isavailable==0) disabled @endif>
					</td>
					<td class="mytd vendorName">{{ strtoupper($vend->name) }} @if($vend->middlename!=''){{ strtoupper($vend->middlename) }}@endif @if($vend->lastname!=''){{ strtoupper($vend->lastname) }}@endif</td>
					<td class="mytd vendorName">{{$vend->mobilenumber}}</td>
					<td class="mytd">{{$vend->securitydeposit}}</td>
					<td class="mytd">{{$vend->walletamount}}</td>
					<td class="mytd vendorName" @if($vend->isavailable==1) style="color:green!important; font-weight:bold;" @endif>{{$vend->availability}}</td>
					<td class="mytd vendorName">{{$vend->assignstatus}}</td>
				</tr>
				@endforeach
				<tr></tr>
			</table>
		</div>
		</td>
	</tr>
	<tr id="assignmsg" style="display:none;">
		<td class="mytd assignmsg" style="text-align:center; font-size:16px;" colspan="2"></td>
	</tr>
</table>
<table id="addresstable" class="mytable" border="1">
	<tr class="myheadbg"><td class="mytd" colspan="8" style="color:white!important; font-size:16px;">SERVICE LOCATION</td></tr>
	<tr class="myheadbg">
		<td class="mytdcenterwhite" style="width:30px;"></td>
		<td class="mytdleftwhite" style="width:100px;">STATE</td>
		<td class="mytdleftwhite" style="width:100px;">CITY</td>
		<td class="mytdleftwhite" style="width:100px;">POSTAL CODE</td>
		<td class="mytdleftwhite">ADDRESS</td>
		<td class="mytdleftwhite" style="width:100px;">LATITUDE</td>
		<td class="mytdleftwhite" style="width:100px;">LONGITUDE</td>
		<td class="mytdleftwhite" style="width:100px;"></td>
	</tr>
	@php $i=1; @endphp
	@foreach($address as $addr)
	<tr>
		<td class="mytdcenter">
			<input type="radio" id="addressid" name="addressid" value="{{$addr->addressid}}" @if($data->addressid==$addr->addressid) checked @endif @if($data->addressid==$addr->addressid) checked @endif>
		</td>
		<td style="padding:0px;">
		<select name="state_id{{$addr->addressid}}" id="state_id{{$addr->addressid}}" style="width:100%;" class="selectbx" onchange="GetCitiesWithId(this.value,'{{ route('cities.list') }}','city_id{{$addr->addressid}}')">
			<option value="">--STATE NAME--</option>
			@foreach($state as $st)
			<option value="{{$st->stateid}}" @if($st->stateid==$addr->stateid) selected @endif>{{$st->statename}}</option>
			@endforeach
		</select>
		</td>
		<td style="padding:0px;">
		<select name="city_id{{$addr->addressid}}" id="city_id{{$addr->addressid}}" style="width:100%;" class="selectbx">
			@php
			if($addr->stateid!=0)
			{
				$city	=	DB::table('city_tbl')->where('stateid',$addr->stateid)->orderby('cityname')->get();
			}
			else
			{
				$city	=	[];
			}
			@endphp
			<option value="">--CITY NAME--</option>
			@foreach($city as $ct)
			<option value="{{$ct->cityid}}" @if($ct->cityid==$addr->cityid) selected @endif>{{$ct->cityname}}</option>
			@endforeach
		</select>
		
		</td>
		<td style="padding:0px;">
			<input type="text" name="postal_code{{$addr->addressid}}" id="postal_code{{$addr->addressid}}" value="{{$addr->postalcode}}" style="width:100%;" class="selectbx" autocomplete="off">
		</td>
		<td style="padding:0px;">
		<input type="text" name="customer_address{{$addr->addressid}}" id="customer_address{{$addr->addressid}}" value="{{$addr->address}}" style="width:100%;" class="selectbx" autocomplete="off">
		</td>
		<td style="padding:0px;">
			<input type="text" name="customer_latitude{{$addr->addressid}}" id="customer_latitude{{$addr->addressid}}" value="{{$addr->latitude}}" style="width:100%;" class="selectbx" autocomplete="off">
		</td>
		<td style="padding:0px;">
			<input type="text" name="customer_longitude{{$addr->addressid}}" id="customer_longitude{{$addr->addressid}}" value="{{$addr->longitude}}" style="width:100%;" class="selectbx" autocomplete="off">
		</td>
		<td>
		<button type="button" class="btn btn-info mygridbtn updateaddress" style="width:100%;" onclick="UpdateCustomerAddress({{$addr->addressid}})">
			<i class="fa fa-refresh"></i> UPDATE
		</button>		
		</td>
	</tr>
	@php $i=$i+1; @endphp
	@endforeach
	@if($i==1)
	<tr>
		<td class="mytdcenter" colspan="8">{{__('labels.noaddress')}}</td>
	</tr>
	@endif
	<tr><td colspan="8">&nbsp;</td></tr>
	<tr>
		<td colspan="8">
<table class="mytable" border="1">
<tr class="myheadbg"><td class="mytdleftwhite" style="font-size:14px;" colspan="7">{{__('labels.newaddress')}}</td></tr>
<tr>
	<td style="padding:0px; width:175px;">
		<select class="select2" name="statid" id="statid" onKeyPress="return OnKeyPress(this, event)"  data-placeholder="STATE NAME" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}"  data-width="175" onchange="GetCitiesWithId(this.value,'{{ route('cities.list') }}','ctyid')">
			<option value=""></option>
			@foreach ($state as $itm)
			<option value="{{ $itm->stateid }}">{{ strtoupper($itm->statename) }}</option>
			@endforeach
		</select>
	</td>
	<td style="padding:0px; width:175px;">
		<select class="select2" name="ctyid" id="ctyid" onKeyPress="return OnKeyPress(this, event)"  data-placeholder="CITY NAME" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}"  data-width="175" onchange="GetAreaNamesWithId(this.value,'{{ route('areanames.list') }}','arid')">
			<option value=""></option>
		</select>
	</td>
	<td style="padding:0px; width:100px;">
	<input type="text" class="selectbx" name="postalcode" id="postalcode" value="" placeholder="{{__('labels.postalcode')}}" autocomplete="off" onKeyPress="return OnKeyPress(this, event)" style="width:100px; margin-top:4px; font-size:12px;" />
	
	</td>
	<td style="padding:0px;">
		<input type="text" class="selectbx" style="width:100%; font-size:12px;" name="completeaddress" id="completeaddress" value="{{old('completeaddress')}}" placeholder="ENTER COMPLETE ADDRESS" autocomplete="off"/>
	</td>
	<td style="padding:0px; width:100px;">
		<input type="text" class="selectbx" style="width:100px; font-size:12px;" name="latitude" id="latitude" value="{{old('latitude')}}" placeholder="LATITUDE" autocomplete="off"/>
	</td>
	<td style="padding:0px; width:100px;">
		<input type="text" class="selectbx" style="width:100px; font-size:12px;" name="longitude" id="longitude" value="{{old('longitude')}}" placeholder="LONGITUDE" autocomplete="off"/>
	</td>
</tr>
<tr id="storeaddress" style="display:none;">
	<td class="mytd storeaddress" style="text-align:center; font-size:14px;" colspan="7"></td>
</tr>

<tr>
	<td colspan="7" style="padding:0px;">
		<button type="button" style="float:right; width:150px;" class="btn btn-info mygridbtn addressbtn" onclick="addCustomerAddress({{$data->customerid}})">ADD NEW ADDRESS</button>
	</td>
</tr>
</table>
		</td>
	</tr>
	
	<tr><td colspan="8">&nbsp;</td></tr>
	<tr>
		<td colspan="8">
			<button type="button" style="float:right; width:100px;" class="btn btn-info mygridbtn" onclick="Close()">CLOSE</button>
			
			<button type="button" style="float:right; width:150px; margin-right:5px;" class="btn btn-info mygridbtn" onclick="AssignVendor('{{route('store.assignvendor')}}','{{Crypt::encrypt($orderid)}}')">ASSIGN VENDOR</button>
		</td>
	</tr>
	<tr><td colspan="8">&nbsp;</td></td>
</table>
</form>

<script>
    $(document).ready(function () {
        $("#searchVendor").on("keyup", function () {
            var value = $(this).val().toLowerCase();
            $(".vendorRow").filter(function () {
                $(this).toggle($(this).find(".vendorName").text().toLowerCase().indexOf(value) > -1);
            });
        });
    });

function UpdateCustomerAddress(addressid)
{
	var postalcode	=	$("#postal_code"+addressid).val();
	var address		=	$("#customer_address"+addressid).val();
	var latitude	=	$("#customer_latitude"+addressid).val();
	var longitude	=	$("#customer_longitude"+addressid).val();
	var stateid		=	$("#state_id"+addressid).val();
	var cityid		=	$("#city_id"+addressid).val();
	$('[data-id="' + addressid + '"]').prop('disabled', true);
	$.ajax({
		url: '{{ route("updatecustomeraddress") }}',
		type: 'POST',
		data: {
			_token: '{{ csrf_token() }}',
			addressid: addressid,
			address:address,
			latitude:latitude,
			longitude:longitude,
			postalcode:postalcode,
			stateid:stateid,
			cityid:cityid
		},
		success: function(response)
		{
			$('[data-id="' + addressid + '"]').prop('disabled', false);
			if(response.status===200)
			{
				bootbox.alert(response.message);
			}
			if(response.status===400)
			{
				bootbox.alert(response.message);
			}
		},
		error: function (xhr) {
			$("#add"+addressid).prop("disabled","");
			if (xhr.responseJSON && xhr.responseJSON.errors) {
				var errors = xhr.responseJSON.errors;
				var allMessages = '';

				$.each(errors, function(field, messages) {
					$.each(messages, function(index, msg) {
						allMessages += field.toUpperCase() + ' : ' + msg + '<br>';
					});
				});
				bootbox.alert(allMessages);
			}			
		}
	});
	
}

function addCustomerAddress(customerid)
{
	var stateid		=	document.getElementById('statid').value;
	var cityid		=	document.getElementById('ctyid').value;
	var address		=	document.getElementById('completeaddress').value;
	var latitude	=	document.getElementById('latitude').value;
	var longitude	=	document.getElementById('longitude').value;
	var postalcode	=	document.getElementById('postalcode').value;
	$.ajax({
		url: '{{ route("addcustomeraddress") }}',
		type: 'POST',
		data: {
			_token: '{{ csrf_token() }}',
			customerid:customerid,
			stateid:stateid,
			cityid:cityid,
			address:address,
			latitude:latitude,
			longitude:longitude,
			postalcode:postalcode
		},
		success: function(response)
		{
			if(response.status===200)
			{
				bootbox.alert(response.message);
				$('.vendorassignment').click();
			}
			if(response.status===400)
			{
				bootbox.alert(response.message);
			}
		},
		error: function (xhr) {
			if (xhr.responseJSON && xhr.responseJSON.errors) {
				var errors = xhr.responseJSON.errors;
				var allMessages = '';

				$.each(errors, function(field, messages) {
					$.each(messages, function(index, msg) {
						allMessages += field.toUpperCase() + ' : ' + msg + '<br>';
					});
				});
				bootbox.alert(allMessages);
			}			
		}
		
	});
	
}


function AssignVendor(r1,orderid)
{
	var addressid	=	$("input[name='addressid']:checked").val() || "";
	var vendorid	=	$("input[name='vendid']:checked").val() || "";
	if(vendorid=='')
	{
		bootbox.alert("PLEASE SELECT VENDOR NAME");
		return false;
	}
	if(addressid=='')
	{
		bootbox.alert("PLEASE SELECT SERVICE LOCATION");
		return false;
	}
	$.get(""+r1,
	{
		orderid:orderid,
		vendorid:vendorid,
		addressid:addressid
	},
	function(data, status){
		if(data.status==200)
		{
			$("#assignmsg").css("display","");
			$("#assignmsg").addClass("bg-success");
			$(".assignmsg").html(data.message);
			setTimeout(function(){
				$(".assignvendor").html("");
				$("#assignvendor").modal("hide");
				$('.orderrecord').click();
			},3000);
		}
		if(data.status==201)
		{
			$("#assignmsg").css("display","");
			$("#assignmsg").addClass("bg-warning");
			$(".assignmsg").html(data.message);
			setTimeout(function(){
				$(".assignmsg").html("");
				$("#assignmsg").css("display","none");
			},10000);
		}

	});
}
	
</script>