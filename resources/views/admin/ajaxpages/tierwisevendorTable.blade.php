@php
$i=1;
@endphp
@foreach($data as $item)
<table class="mytable" border="1" style="text-transform:none!important">
	<tr class="myheadbg"><td class="padding-5 form-label" colspan="3">{{$item->tiername}}</td></tr>
	<tr class="myheadbg">
		<td class="padding-5 form-label center" style="width:30px;">S.No.</td>
		<td class="padding-5 form-label">Name of Bidder</td>
		<td class="padding-5 form-label" style="width:300px;">Empanelled Category </td>
	</tr>
	@foreach($item->vendors as $vendor)
	<tr>
		<td class="padding-5 v-top center">{{$i++}}</td>
		<td class="padding-5 form-label v-top">{{$vendor->name}}</td>
		<td class="padding-5">
		@foreach($vendor->sectors as $sector)
		<i class="fa fa-angle-double-right"></i> {{$sector->sectorname}}<br>
		@endforeach
		</td>
	</tr>
	@endforeach
</table>
@endforeach
