@php
$i=1
@endphp
<table class="mytable" border="1" style="text-transform: none!important;">
	<tr class="">
		<td class="padding-10 font-14 form-label" style="position:relative; line-height:35px;">
		{{$eoi->eoinumber}} | {{$eoi->departmentname}}<br>Project Name : {{$eoi->projecttitle}}
		</td>
	</tr>
	@if($data)
		@foreach($data as $item)
		<tr>
			<td class="padding-10 font-14" style="position:relative;">
			<i class="fa fa-calendar"></i> Posted on : {{date('d\-m\-Y, h:i A',strtotime($item->creationdate))}} | <i class="fa fa-building-o"></i> Posted By : <b>{{$item->postedby}}</b><br><br>
			@if($item->attachment!='')
			<a href="{{ route('view.uploadedfile', Crypt::encrypt($item->attachment)) }}" target="_blank" class="action-a" style="position:absolute; top:10px; right:10px;">
				<span class="label label-white middle animated flipInX delay-02 f-s-10 form-label span-content">
					<i class="fa fa-file-pdf-o" style="margin-top:-10px!important;"></i> View File
				</span>
			</a>
			@endif
			@if($item->message!='')
			<div class="padding-10 prebidcontent">
				{!!$item->message!!}
			</div>
			@endif
			</td>
		</tr>
		@endforeach
	@endif
	<tr></tr>
</table>
