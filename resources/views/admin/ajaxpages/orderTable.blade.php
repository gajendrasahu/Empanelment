@php
use Illuminate\Support\Facades\DB;
$i=1
@endphp
				<tr>
					<td colspan="2">
<span class="label label-primary"><input type="radio" onclick="loadData(1,'{{ route('order.html') }}');" name="orderstatus" value="0" @if($orderstatus==0) checked @endif> PENDING <span class="badge badge-warning">{{$counts->pending}}</span></span>

<span class="label label-primary"><input type="radio" onclick="loadData(1,'{{ route('order.html') }}');" name="orderstatus" value="1" @if($orderstatus==1) checked @endif> ASSIGNED <span class="badge badge-warning">{{$counts->assigned}}</span></span>

<span class="label label-primary"><input type="radio" onclick="loadData(1,'{{ route('order.html') }}');" name="orderstatus" value="2" @if($orderstatus==2) checked @endif> STARTED <span class="badge badge-warning">{{$counts->started}}</span></span>

<span class="label label-primary"><input type="radio" onclick="loadData(1,'{{ route('order.html') }}');" name="orderstatus" value="4" @if($orderstatus==4) checked @endif> ON HOLD <span class="badge badge-warning">{{$counts->onhold}}</span></span>

<span class="label label-primary"><input type="radio" onclick="loadData(1,'{{ route('order.html') }}');" name="orderstatus" value="3" @if($orderstatus==3) checked @endif> COMPLETED <span class="badge badge-warning">{{$counts->completed}}</span></span>

<span class="label label-primary"><input type="radio" onclick="loadData(1,'{{ route('order.html') }}');" name="orderstatus" value="-1" @if($orderstatus==-1) checked @endif> CANCELLED <span class="badge badge-warning">{{$counts->cancelled}}</span></span>

<span class="label label-primary"><input type="radio" onclick="loadData(1,'{{ route('order.html') }}');" name="orderstatus" value="10" @if($orderstatus==10) checked @endif> UN PAID <span class="badge badge-warning">{{$counts->unpaid}}</span></span>
					</td>
				</tr>

@foreach($data as $item)
<tr id="item-{{ $i }}">
    <td class="myheadbg" nowrap colspan="2">
		{{ $i++ }}) ORDER DATE : {{ date('d\-m\-Y',strtotime($item->servicedate)) }} | SLOT TIME : {{ date('h:i A',strtotime($item->slottime)) }}  | CATEGORY NAME : {{$item->category}}
		
		<label class="bg-primary" style="float:right; padding:0px 10px;">
			@if($orderstatus==0) PENDING @endif
			@if($orderstatus==1) ASSIGNED @endif
			@if($orderstatus==2) STARTED @endif
			@if($orderstatus==3) COMPLETED @endif
			@if($orderstatus==4) ON HOLD @endif
			@if($orderstatus==-1) CANCELLED @endif
		</label>
		@if($orderstatus==10)<label style="float:right; padding:0px 10px; background-color:orange;" class="bg-primary"> UNPAID</label> @endif
		
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
	@if($item->orderstatus!=-1 && $item->orderstatus!=3)
	<button type="button" class="btn btn-info myfrmbtn selectbx no-hover" onclick="ViewVendors('{{route('order.assignvendor')}}','{{Crypt::encrypt($item->detailid)}}')">@if($item->vendorid==0) ASSIGN VENDOR @else ASSIGNED @endif</button>
	@endif
	
	@php
	$receipts = DB::table('receipt_tbl')
					->where('orderid', '=', $item->orderid)
					->where('customerid','=',$item->customerid)
					->get();
	$isbilled	=	0;
	@endphp
	@foreach($receipts as $receipt)
		<br>
		@if($receipt->recordtype=='')
		<a href="{{ asset('storage/receipts/'.$receipt->receiptfile) }}" target="_blank" style="text-decoration:none;">
			<button type="button" class="btn btn-info myfrmbtn selectbx no-hover" style="text-decoration:none;">RECEIPT</button>
		</a> 
		@elseif($receipt->recordtype=='CUSTOMER_INVOICE')
		@php $isbilled++; @endphp
		<a href="{{ asset('storage/invoices/'.$receipt->invoicefile) }}" target="_blank" style="text-decoration:none;">
			<button type="button" class="btn btn-info myfrmbtn selectbx no-hover" style="text-decoration:none;">INVOICE</button>
		</a> 
		@endif
	@endforeach
	
		@if($isbilled==0)
		<!--<br><button type="button" id="btn{{$item->detailid}}" onclick="GenerateInvoice({{$item->orderid}},{{$item->detailid}})" class="btn btn-info myfrmbtn selectbx no-hover" style="text-decoration:none;">GENERATE INVOICE</button>-->
		@endif
	
    </td>
	<td>
		<table class="mytable" border="1">
			<tr>
				<td class="mybgtd" colspan="5"><b>CUSTOMER DETAIL</b></td>
			</tr>
			<tr>
				<td class="mybgtd">{{__('labels.name')}}</td>
				<td class="mybgtd">{{__('labels.mobilenumber')}}</td>
				<td class="mybgtd">{{__('labels.email')}}</td>
				<td class="mybgtd" colspan="2">SERVICE ADDRESS</td>
			</tr>
			<tr>
				<td class="" style="vertical-align:top;">{{$item->name}} {{$item->middlename}} {{$item->lastname}}</td>
				<td class="" style="vertical-align:top;">{{$item->mobilenumber}}</td>
				<td class="" style="vertical-align:top;">{{strtolower($item->email)}}</td>
				<td class="" style="vertical-align:top;" colspan="2">{{strtolower($item->address)}} 
					@if($item->latitude!=0 || $item->longitude!=0)
					<button type="button" class="btn btn-info selectbx" style="float:right;">
						VIEW IN MAP <i class="fa fa-map-marker"></i>
					</button>
					@endif
				</td>
			</tr>
			<tr>
				<td class="mybgtd" colspan="5"><b>SERVICE DETAIL</b></td>
			</tr>
			<tr>
				<td class="mybgtd">{{__('labels.servicename')}}</td>
				<td class="mybgtd">MRP</td>
				<td class="mybgtd">DISCOUNTED PRICE</td>
				<td class="mybgtd">GST</td>
				<td class="mybgtd">TOTAL PRICE</td>
			</tr>
			<tr>
				<td class="" style="vertical-align:top;">{{$item->servicetitle}}</td>
				<td class="" style="vertical-align:top;"><i class="fa fa-inr"></i> {{$item->servicecharge}}</td>
				<td class="" style="vertical-align:top;"><i class="fa fa-inr"></i> {{$item->taxable}}</td>
				<td class="" style="vertical-align:top;"><i class="fa fa-inr"></i> {{$item->taxvalue}}</td>
				<td class="" style="vertical-align:top;"><i class="fa fa-inr"></i> {{$item->payable}}</td>
			</tr>
			<tr>
				<td class="mybgtd" style="vertical-align:top;">CATEGORY NAME</td>
				<td class="" style="vertical-align:top;" colspan="4">{{$item->category}}</td>
			</tr>
		</table>
		@if($item->vendorid!=0)
		<table class="mytable" border="1">
			<tr>
				<td class="mybgtd" colspan="5"><b>VENDOR DETAIL</b></td>
			</tr>
			<tr>
				<td class="mybgtd">{{__('labels.vendorname')}}</td>
				<td class="mybgtd center">{{__('labels.assignedon')}}</td>
				<td class="mybgtd center">{{__('labels.starttime')}}</td>
				<td class="mybgtd center">ADD ON SERVICES</td>
				<td class="mybgtd center">{{__('labels.endtime')}}</td>
			</tr>
			<tr>
				<td class="" style="vertical-align:top;">{{$item->vendorname}}</td>
				<td class="center" style="vertical-align:top;">{{date('d\-m\-Y, h:i A',strtotime($item->assignedtime))}}</td>
				<td class="center" style="vertical-align:top;">
				
				@if($item->workstarttime=='' && $item->iscancelled==0)
					<!--
					<button type="button" class="btn btn-info mygridbtn startbtn" onclick="SetStartTime('{{Crypt::encrypt($item->detailid)}}','{{route('store.starttime')}}',{{$i}})">{{__('common.startwork')}}</button>
					-->
				@else
					@if($item->iscancelled==0)
						{{date('d\-m\-Y, h:i A',strtotime($item->workstarttime))}}
					@endif
				@endif
				
				</td>
				<td class="center">
				
				@if($item->workstarttime!='' && $item->iscancelled==0 && $item->workendtime=='')
					<!--<button type="button" class="btn btn-info mygridbtn addpricebtn" onclick="ViewPricing('{{Crypt::encrypt($item->detailid)}}','{{route('order.viewprice')}}')">{{__('common.addprice')}}</button>-->
				@endif
				
				</td>
				<td class="center" style="vertical-align:top;">
				
				@if($item->iscancelled==0 && $item->workendtime=='')
					<!--
					<button type="button" class="btn btn-info mygridbtn endbtn" onclick="SetEndTime('{{Crypt::encrypt($item->detailid)}}','{{route('store.endtime')}}',{{$i}})">{{__('common.endwork')}}</button>
					-->
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
		const url = '{{ route('order.html') }}'; // URL generated by Laravel route
		loadData(page, url); // Call custom function with page number and URL
	};
});
</script>

