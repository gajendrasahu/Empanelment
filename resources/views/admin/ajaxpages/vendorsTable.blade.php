@php
$i = 1;
@endphp
@foreach ($data as $item)
	<tr class="mytr pd-8" id="item-{{ $i }}">
		<td style="width:30px; text-align:center;" nowrap>{{ $i++ }}</td>
		<td nowrap class="first-column-name">{{ $item->name}}</td>
		<td>{{ strtolower($item->email)}}<br>{{$item->pass_word}}</td>
		<td nowrap>{{ $item->tiername}}</td>
		<td nowrap>
			@foreach($item->sectors as $sector)
				<i class="fa fa-angle-double-right"></i> {{$sector->sectorname}}<br>
			@endforeach
		</td>
		<td nowrap>
			@foreach($item->emails as $email)
				<i class="fa fa-angle-double-right"></i> {{$email->email}}<br>
			@endforeach
		</td>
		<td nowrap style="text-align: center; padding: 0px; ">
			@permission('update.firm')
			<a href="{{ route('edit.vendor', Crypt::encrypt($item->vendorid)) }}" class="btn myfrmbtn smbtn"> Edit</a>
			@endpermission
			
			<a href="{{ route('sendmessage.vendor', Crypt::encrypt($item->vendorid)) }}" class="btn myfrmbtn smbtn" style="margin-top:3px;"> Send Message</a>
		</td>
	</tr>
@endforeach
@if($i == 1)
	<tr>
		<td colspan="6" style="text-align:center;">
			<br>
			@include('admin.body.actionmessage')
		</td>
	</tr>
@endif