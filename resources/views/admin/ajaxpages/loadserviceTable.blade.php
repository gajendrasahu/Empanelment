@php
$i=1
@endphp

@foreach ($data as $item)
<tr id="itemm-{{ $i }}" class="mytr">
    <td style="text-align:left; padding:10px 0px; width:100px;">
		@if($item->firstpic!='')
        <img src="{{ asset('storage/'.$item->firstpic) }}" alt="Uploaded Image" style="width:150px; position:relative;">
		@else
		<img src="{{ asset('storage/uploads/images/default.jpg') }}" alt="Uploaded Image" style="width:100px; position:relative;">
		@endif
    </td>
	<td style="text-align:left; padding:10px 10px; width:100px;" nowrap>
		<b>{{ $item->servicetitle }}</b><br>
		REQUIRED TIME : {{$item->requiredtime}} MIN<br>
		MRP : {{ $item->mrp }} INR<br>
		DISCOUNT : {{ $item->discount }}%<br>
		TAXABLE : {{ $item->taxable }} INR<br>
		GST (%) : {{ $item->igst }}%<br>
		<b>PAYABLE PRICE : {{ $item->finalprice }} INR</b>
		<br>
		@if(in_array(9,Session::get('actions')))
		<form name="frm" id="frm" action="{{ route('map.servicetag',$item->serviceid)}}" method="post">
		@csrf
			<input type="hidden" name="serviceid" id="serviceid" value="{{$item->serviceid}}">
			<input type="hidden" name="categoryid" id="categoryid" value="{{$categoryid}}">
			<input type="hidden" name="tagid" id="tagid" value="{{$tagid}}">
			<button type="submit" class="btn btn-info" style="margin:0px auto;">TAG</button>
		</form>
		@endif
	</td>
	<td style="text-align:left; padding:10px 10px;">
		<b>DESCRIPTION</b><br>{!! $item->description !!}
	</td>
	
</tr>
@endforeach