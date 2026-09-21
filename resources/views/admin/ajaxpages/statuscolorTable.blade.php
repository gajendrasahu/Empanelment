@php
$i=1
@endphp
@foreach ($data as $item)
<tr class="mytr" id="item-{{ $i }}">
    <td style="width:30px; text-align:center;" nowrap>{{ $i++ }}</td>
    <td nowrap>{{ $item->statusname }}</td>
	<td nowrap style="text-align:center; background-color:{{ $item->statuscolor }}">&nbsp;</td>
    <td nowrap style="text-align:center;">
	@if(in_array(2,Session::get('actions')))
		@if(Session::get('loginId')===1 || Session::get('loginId')===$item->createdby)
		<a href="{{ route('edit.statuscolor',Crypt::encrypt($item->statuscolorid)) }}" title=""><i class="fa fa-edit"></i></a></td>
		@endif
	@endif
    <td nowrap style="text-align:center;">
	@if(in_array(3,Session::get('actions')))
		@if(Session::get('loginId')===1 || Session::get('loginId')===$item->createdby)
        <a title="" onclick="deleteItem('statuscolor','{{ Crypt::encrypt($item->statuscolorid) }}',{{ $i }},'ARE YOU SURE YOU WANT TO DELETE THIS RECORD?','STATUS COLOR DELETED SUCCESSFULLY')" title=""><i class="fa fa-trash"></i>
        </a>
		@endif
	@endif
    </td>
</tr>
@endforeach
@if($i==1)
<tr>
    <td colspan="5" style="text-align:center;">
        <br>
		@include('admin.body.actionmessage')
    </td>
</tr>
@else
<tr>
<td colspan="5" style="text-align:right;">
{{ $data->links('vendor.pagination.default') }}
</td>
</tr>
@endif

<script>
const paginationLinks = document.querySelectorAll('.pagination a');
	paginationLinks.forEach(link => {
		link.onclick = function(event) {
			event.preventDefault();
			const page = this.getAttribute('href').split('page=')[1];
			const url = '{{ route('statuscolor.html') }}';
			loadData(page, url);
		};
});
</script>

