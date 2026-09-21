@php
$i=1;
@endphp
@foreach ($data as $item)
<tr id="item-{{ $i++ }}" class="mytr">
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
		@if(in_array(3,Session::get('actions')) || in_array(10,Session::get('actions')))
		<button type="button" class="btn btn-info" style="margin:0px auto;"  onclick="deleteItem('removemap',{{ $item->linkid}},{{ $i }},'ARE YOU SURE YOU WANT TO DELETE THIS RECORD?','MAPPING REMOVED SUCCESSFULLY')"><i class="fa fa-trash"></i> REMOVE</button>
		@endif
	</td>
	<td style="text-align:left; padding:10px 10px;">
		<b>DESCRIPTION</b><br>{!! $item->description !!}
	</td>
</tr>
@endforeach