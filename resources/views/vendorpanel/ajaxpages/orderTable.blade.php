@php
$i=1
@endphp
@foreach($data as $item)
<tr id="item-{{ $i }}">
    <td class="myheadbg" nowrap colspan="2">
		{{ $i++ }}) ORDER DATE TIME : {{ date('d\-m\-Y, h:i A',strtotime($item->orderdate)) }} | CATEGORY NAME : {{$item->category}}
	</td>
</tr>
<tr>
    <td nowrap style="width:100px; text-align:center;">
	@if($item->profilepic!='')
        <a href="{{ asset('storage/'.$item->profilepic) }}" target="_blank">
			<img src="{{ asset('storage/'.$item->profilepic) }}" alt="Uploaded Image" style="width:100px;">
		</a>
	@else
		<img src="{{ asset('storage/uploads/images/avatar.png') }}" alt="Uploaded Image" style="width:100px;">
	@endif
	<br>
	<br>
	
	<button type="button" class="btn btn-info myfrmbtn selectbx no-hover" onclick="ViewVendors('{{route('order.assignvendor')}}','{{Crypt::encrypt($item->orderid)}}')">@if($item->vendorid==0) ASSIGN VENDOR @else ASSIGNED @endif</button>
	
    </td>
	<td>
		<table class="mytable" border="1">
			<tr>
				<td class="mybgtd" colspan="4"><b>{{__('labels.basicdetail')}}</b></td>
			</tr>
			<tr>
				<td class="mybgtd">{{__('labels.name')}}</td>
				<td class="mybgtd">{{__('labels.mobilenumber')}}</td>
				<td class="mybgtd" colspan="2">{{__('labels.email')}}</td>
			</tr>
			<tr>
				<td class="" style="vertical-align:top;">{{$item->customername}}</td>
				<td class="" style="vertical-align:top;">{{$item->customermobile}}</td>
				<td class="" style="vertical-align:top;" colspan="2">{{strtolower($item->email)}}</td>
			</tr>
			<tr>
				<td class="mybgtd" colspan="4"><b>{{__('labels.addressdetail')}}</b></td>
			</tr>
			<tr>
				<td class="" style="vertical-align:top;" colspan="4">{{$item->address}}</td>
			</tr>
			<tr>
				<td class="mybgtd" colspan="4"><b>{{__('labels.servicedetail')}}</b></td>
			</tr>
			<tr>
				<td class="mybgtd">{{__('labels.servicename')}}</td>
				<td class="mybgtd">{{__('labels.visitingcharge')}}</td>
				<td class="mybgtd">{{__('labels.additionalcost')}}</td>
				<td class="mybgtd">{{__('labels.totalcost')}}</td>
			</tr>
			<tr>
				<td class="" style="vertical-align:top;">{{$item->servicetitle}} ({{$item->requiredtime}} Minutes)</td>
				<td class="" style="vertical-align:top;"><i class="fa fa-inr"></i> {{$item->visitingcharge}}</td>
				<td class="" style="vertical-align:top;"><i class="fa fa-inr"></i> {{$item->additionalcost}}</td>
				<td class="" style="vertical-align:top;"><i class="fa fa-inr"></i> {{$item->visitingcharge+$item->additionalcost}}</td>
			</tr>
		</table>
		@if($item->vendorid!=0)
		<table class="mytable" border="1">
			<tr>
				<td class="mybgtd" colspan="5"><b>{{__('labels.workassigned')}}</b></td>
			</tr>
			<tr>
				<td class="mybgtd">{{__('labels.vendorname')}}</td>
				<td class="mybgtd center">{{__('labels.assignedon')}}</td>
				<td class="mybgtd center">{{__('labels.starttime')}}</td>
				<td class="mybgtd center">{{__('labels.addpricing')}}</td>
				<td class="mybgtd center">{{__('labels.endtime')}}</td>
			</tr>
			<tr>
				<td class="" style="vertical-align:top;">{{$item->vendorname}}</td>
				<td class="center" style="vertical-align:top;">{{date('d\-m\-Y, h:i A',strtotime($item->assignedtime))}}</td>
				<td class="center" style="vertical-align:top;">
				@if($item->workstarttime=='' && $item->iscancelled==0)
					<button type="button" class="btn btn-info mygridbtn startbtn" onclick="SetStartTime('{{Crypt::encrypt($item->orderid)}}','{{route('store.starttime')}}',{{$i}})">{{__('common.startwork')}}</button>
				@else
					@if($item->iscancelled==0)
						{{date('d\-m\-Y, h:i A',strtotime($item->workstarttime))}}
					@endif
				@endif
				</td>
				<td class="center">
				@if($item->workstarttime!='' && $item->iscancelled==0 && $item->workendtime=='')
					<button type="button" class="btn btn-info mygridbtn addpricebtn" onclick="ViewPricing('{{Crypt::encrypt($item->orderid)}}','{{route('order.viewprice')}}')">{{__('common.addprice')}}</button>
				@endif
				</td>
				<td class="center" style="vertical-align:top;">
				@if($item->iscancelled==0 && $item->workendtime=='')
					<button type="button" class="btn btn-info mygridbtn endbtn" onclick="SetEndTime('{{Crypt::encrypt($item->orderid)}}','{{route('store.endtime')}}',{{$i}})">{{__('common.endwork')}}</button>
				@else
					@if($item->iscancelled==0)
						{{date('d\-m\-Y, h:i A',strtotime($item->workendtime))}}
					@endif					
				@endif
				</td>
			</tr>
			<tr id="startendmsg{{$i}}" style="display:none;">
				<td class="mytd startendmsg{{$i}}" style="text-align:center; font-size:16px;" colspan="5"></td>
			</tr>
			
		</table>
		@endif
	</td>
</tr>
@endforeach
@if($i==1)
<tr>
    <td style="text-align:center;">
        <br>
        <i class="fa fa-warning blue nodata"></i><br>
        {!! __('messages.sorry') !!}
    </td>
</tr>
@else
<tr>
<td style="text-align:right;">
{{ $data->links('vendor.pagination.default') }}
</td>
</tr>
@endif

<script>
const paginationLinks = document.querySelectorAll('.pagination a');
paginationLinks.forEach(link => {
	link.onclick = function(event) {
		event.preventDefault(); // Prevent default link behavior
		const page = this.getAttribute('href').split('page=')[1]; // Extract page number from URL
		const url = '{{ route('openorder.html') }}'; // URL generated by Laravel route
		loadData(page, url); // Call custom function with page number and URL
	};
});
</script>

