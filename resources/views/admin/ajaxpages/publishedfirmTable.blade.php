<table class="table table-bordered table-striped table-hover pd-8">
<tr><td colspan="3">Published Date: {{date('d\-m\-Y',strtotime($eoi->floatdate))}}</td></tr>
<tr>
	<td class="center width-30">S.No.</td>
	<td>Firm Name</td>
	<td>Tier</td>
</tr>
@foreach ($data as $item)
<tr>
    <td class="center width-30" nowrap>{{ $loop->iteration }}</td>
	<td nowrap>{{ $item->companyname}}</td>
	<td nowrap>{{ $item->tiername}}</td>	
</tr>
@endforeach
@if(!$data)
<tr>
    <td colspan="3" style="text-align:center;">
        <br>
		@include('admin.body.actionmessage')
    </td>
</tr>
@endif
</table>