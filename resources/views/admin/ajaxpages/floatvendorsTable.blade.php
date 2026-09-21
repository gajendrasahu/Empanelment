@php
$i=0;
$j=0;
@endphp
@foreach ($data as $item)
@php
$j=$j+1;
$i=$i+1;
@endphp 
<tr class="mytr" id="item-{{ $i }}">
    <td style="width:30px; text-align:center;" nowrap>{{ $loop->iteration }}</td>
	<td nowrap>{{ $item->companyname }}</td>
	<td nowrap>
	@foreach($item->emails as $email)
	<input type="checkbox" name="vendor_emails[]" class="vendor_emails{{$item->vendorid}} allmail" checked value="{{$email->recordid}}" style="vertical-align:text-bottom;" onclick="bindAllCheckboxLogic()"> {{$email->email}}<br>
	@endforeach
	</td>
	<td nowrap>
	@foreach($item->sectors as $sector)
	<i class="fa fa-angle-double-right"></i> {{$sector->sectorname}}<br>
	@endforeach
	</td>

	<td nowrap>{{ strtoupper($item->tiername)	 }}</td>
</tr>
@endforeach
@if($data->isEmpty())
<tr>
    <td colspan="5" style="text-align:center;">
        <br>
		@include('admin.body.actionmessage')
    </td>
</tr>
@else
@endif

