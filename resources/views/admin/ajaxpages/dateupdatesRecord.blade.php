@php
if($field_name=='releasedate')
$label	=	"Release Date Updates";
else if($field_name=='prebidlastdate')
$label	=	"Pre-bid Last Date Updates";	
else if($field_name=='deadlinedate')
$label	=	"Submission Deadline Updates";	
else
$label	=	"Interview Date Updates";	
@endphp
<table class="mytable" border="1">
<tr>
	<td class="padding-5 form-label" colspan="4">{{$label}}</td>
</tr>

<tr>
	<td class="center padding-5 form-label">S.No.</td>
	<td class="center padding-5 form-label">Updated on</td>
	<td class="center padding-5 form-label">Old Value</td>
	<td class="center padding-5 form-label">New Value</td>
</tr>
@foreach ($data as $item)
<tr class="mytr">
	<td class="center padding-5 width-30">{{$loop->iteration}}</td>
	<td class="center padding-5 width-100">{{date('d\-m\-Y, h:i A',strtotime($item->updated_at))}}</td>
	@if($item->field_name!='deadlinedate')
	<td class="center padding-5 width-100">{{date('d\-m\-Y',strtotime($item->old_value))}}</td>
	<td class="center padding-5 width-100">{{date('d\-m\-Y',strtotime($item->new_value))}}</td>
	@else
	<td class="center padding-5 width-100">{{date('d\-m\-Y, h:i A',strtotime($item->old_value))}}</td>
	<td class="center padding-5 width-100">{{date('d\-m\-Y, h:i A',strtotime($item->new_value))}}</td>
	@endif
</tr>
@endforeach
@if($data->isEmpty())
<tr>
    <td colspan="4" class="padding-5" style="text-align:center;">--No Record Found--</td>
</tr>
@else
@endif

