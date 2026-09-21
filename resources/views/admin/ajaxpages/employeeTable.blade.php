@php
$i=1
@endphp
@foreach($data as $item)
<tr id="item-{{ $i }}">
    <td class="myheadbg" nowrap colspan="2">
		{{ $i++ }}) Account Login Id : {{$item->loginid}} | Verification Status : @if($item->verificationstatus==1) {{'ACTIVE'}} @else {{'INACTIVE'}} @endif | @if($item->verificationstatus==1) <label class="valid">VERIFIED <i class="fa fa-info-circle" title="VERIFIED BY {{$item->verifiedby}} ON {{date('d\-m\-Y, h:i A',strtotime($item->verifiedon))}}"></i></label> @else <label class="invalid">UN VERIFIED</label>  @endif
		<span style="margin-right:10px; float:right;">
		@if(in_array(3,Session::get('actions')))
			<a title="" class="myactionlink" onclick="deleteItem('employee','{{ Crypt::encrypt($item->vendorid)}}',{{ $i }},'{{__('messages.confirmation')}}','{{__('messages.deleted')}}')" title="">
				<i class="fa fa-trash" style="color:white!important;"></i>
			</a>
		@endif
		</span>
		<span style="margin-right:15px; float:right;">
		@if(in_array(2,Session::get('actions')))
			<a href="{{ route('edit.employee',Crypt::encrypt($item->vendorid)) }}" title="" class="myactionlink">
				<i class="fa fa-edit" style="color:white!important;"></i>
			</a>
		@endif	
		</span>		
	</td>
</tr>
<tr id="item-{{ intval($i-1) }}">
    <td nowrap style="width:100px;">
	@if($item->profilepic!='')
        <a href="{{ asset('storage/'.$item->profilepic) }}" target="_blank">
			<img src="{{ asset('storage/'.$item->profilepic) }}" alt="Uploaded Image" style="width:100px;">
		</a>
	@else
		<img src="{{ asset('storage/uploads/images/avatar.png') }}" alt="Uploaded Image" style="width:100px;">
	@endif
    </td>
	<td>
		<table class="mytable" border="1">
			<tr>
				<td class="mybgtd" colspan="6"><b>{{__('labels.basicdetail')}}</b></td>
			</tr>
			<tr>
				<td class="mybgtd">{{__('labels.name')}}</td>
				<td class="mybgtd">{{__('labels.mobilenumber')}}</td>
				<td class="mybgtd">{{__('labels.email')}}</td>
				<td class="mybgtd">{{__('labels.aadharnumber')}}</td>
				<td class="mybgtd">{{__('labels.pannumber')}}</td>
				<td class="mybgtd">DRIVING LICENCE</td>
			</tr>
			<tr>
				<td class="" style="vertical-align:top;">{{$item->name}} {{$item->middlename}} {{$item->lastname}}
				@if($item->verificationstatus==0)
				<!--<button type="button" class="btn btn-success no-hover verifybtn{{$i}}" style="width:100px; padding:0px 5px!important; height:25px; line-height:0px; float:right;" onclick="VerifyVendor('{{Crypt::encrypt($item->vendorid)}}','{{ route('verify.employee') }}',{{$i}})">VERIFY</button>-->
				@endif
				</td>
				<td class="" style="vertical-align:top;">{{$item->mobilenumber}}</td>
				<td class="" style="vertical-align:top;">{{ucwords($item->email)}}</td>
				<td class="" style="vertical-align:top;">
					{{$item->aadhaarnumber}}
					@if($item->aadhaarbackfile!='')
						<a href="{{ asset('storage/'.$item->aadhaarbackfile) }}" target="_blank">
							<i class="fa fa-download" style="float:right; margin-top:3px;"></i>
						</a> 
					@endif
					@if($item->aadhaarfrontfile!='')
						<a href="{{ asset('storage/'.$item->aadhaarfrontfile) }}" target="_blank">
							<i class="fa fa-download" style="float:right; margin-top:3px; margin-right:10px;"></i>
						</a> 
					@endif
				</td>
				<td class="" style="vertical-align:top;">
					{{$item->pannumber}}
					@if($item->panfile!='')
						<a href="{{ asset('storage/'.$item->panfile) }}" target="_blank">
							<i class="fa fa-download" style="float:right; margin-top:3px;"></i>
						</a> 
					@endif
				</td>
				<td class="" style="vertical-align:top;">
					{{$item->drivinglicence}}
					@if($item->licencefile!='')
						<a href="{{ asset('storage/'.$item->licencefile) }}" target="_blank">
							<i class="fa fa-download" style="float:right; margin-top:3px;"></i>
						</a> 
					@endif
				</td>
			</tr>
			<tr>
				<td class="mybgtd" colspan="6"><b>{{__('labels.addressdetail')}}</b></td>
			</tr>
			<tr>
				<td class="mybgtd">{{__('labels.statename')}}</td>
				<td class="mybgtd">{{__('labels.cityname')}}</td>
				<td class="mybgtd">{{__('labels.postalcode')}}</td>
				<td class="mybgtd"></td>
				<td class="mybgtd"></td>
				<td class="mybgtd"></td>
			</tr>
			<tr>
				<td class="" style="vertical-align:top;">{{$item->statename}}</td>
				<td class="" style="vertical-align:top;">{{$item->cityname}}</td>
				<td class="" style="vertical-align:top;">{{$item->postalcode}}</td>
				<td class=""></td>
				<td class=""></td>
				<td class=""></td>
			</tr>
			<tr>
				<td class="" style="vertical-align:top;" colspan="6">Address : {{$item->completeaddress}}</td>
			</tr>
			<tr>
				<td class="mybgtd" colspan="6"><b>NOMINEE DETAIL</b></td>
			</tr>
			<tr>
				<td class="mybgtd">NOMINEE NAME</td>
				<td class="mybgtd">RELATION</td>
				<td class="mybgtd">DATE OF BIRTH</td>
				<td class="center mybgtd">DOCUMENT</td>
				<td class="mybgtd">CATEGORY</td>
				<td class="mybgtd"></td>
			</tr>
			<tr>
				<td class="" style="vertical-align:top;">{{$item->nomineename}}</td>
				<td class="" style="vertical-align:top;">{{$item->relation}}</td>
				<td class="" style="vertical-align:top;">{{$item->nomineedob}}</td>
				<td class="center">&nbsp;
					@if($item->documentfile!='')
						<a href="{{ asset('storage/'.$item->documentfile) }}" target="_blank">
							<i class="fa fa-download" style="margin-top:3px;"></i>
						</a> 
					@endif				
				</td>
				<td class="">{!!$item->cats!!}</td>
				<td class=""></td>
			</tr>
			
		</table>
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
		const url = '{{ route('employee.html') }}'; // URL generated by Laravel route
		loadData(page, url); // Call custom function with page number and URL
	};
});
</script>
