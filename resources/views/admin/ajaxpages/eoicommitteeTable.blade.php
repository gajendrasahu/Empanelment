@php
$i=1;
@endphp
@foreach ($data as $item)
<tr>
	<td class="center padding-5">
	{{$loop->iteration}}
	</td>
	<td class="padding-2">
	{{$item->name}}
	</td>
	<td class="padding-2">
		{{$item->mobilenumber}}
	</td>
	<td class="padding-2">
		{{$item->designation}}
	</td>
	<td class="padding-2">
		{{$item->remark}}
	</td>
	<td class="padding-2">
		{{$item->usertype}}
	</td>
</tr>
@endforeach