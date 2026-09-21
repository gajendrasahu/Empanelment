@php
$i=1
@endphp
@foreach ($data as $item)
<tr class="mytr" id="item-{{ $i }}">
    <td style="width:30px; text-align:center;" nowrap>{{ $i++ }}</td>
    <td nowrap>{{ $item->project_name }}</td>
    <td nowrap style="text-align:center;">
	@permission('update.project')
		<a href="{{ route('edit.project',Crypt::encrypt($item->projectid)) }}" title=""><i class="fa fa-edit"></i></a>
	@endpermission
	</td>
    <td nowrap style="text-align:center;">
	@permission('delete.projectstatus')
        <a title="" onclick="deleteItem('project','{{ Crypt::encrypt($item->projectid) }}',{{ $i }},'Are you sure you want to delete this record?','Project name deleted successfully')" title=""><i class="fa fa-trash"></i>
        </a>
	@endpermission
    </td>
</tr>
@endforeach
@if($i==1)
<tr>
    <td colspan="4" style="text-align:center;">
        <br>
		@include('admin.body.actionmessage')
    </td>
</tr>
@else
<tr>
<td colspan="4" style="text-align:right;">
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
			const url = '{{ route('project.html') }}';
			loadData(page, url);
		};
});
</script>

