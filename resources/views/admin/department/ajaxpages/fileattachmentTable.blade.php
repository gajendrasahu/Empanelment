@php
$t=1;
@endphp
<tr class="myheadbg">
	<td class="padding-8 form-label" colspan="6">
		Upload Documents
	</td>
</tr>
@if(Session::has('uploaded'))
<tr class="uploaded">
	<td colspan="6">
		<div class="alert alert-block alert-success">
			<button type="button" class="close" data-dismiss="alert">
				<i class="ace-icon fa fa-times"></i>
			</button>
			{{ Session::pull('uploaded') }}
		</div>
	
	</td>
</tr>	
@endif
@if($attachments->count()>0)
@foreach($attachments as $attach)
<tr>
	<td class="padding-8">
		<input type="text" name="attachmenttitle{{$loop->iteration}}" id="attachmenttitle{{$loop->iteration}}" required placeholder="Attachment title" autocomplete="off" class="full-wdth" value="{{$attach->attachmenttitle}}" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}">
	</td>
	<td class="padding-8 center" style="width:250px;">
		<button type="button" class="btn btn-info" style="width:120px;" onclick="updateAttachmentTitle({{$loop->iteration}},'{{Crypt::encrypt($attach->recordid)}}','{{$requestid}}')">
			<i class="fa fa-eye"></i> Update Title
		</button>

		<a href="{{ asset('storage/'.$attach->attachmentfile) }}" target="_blank">
		<button type="button" class="btn btn-info" style="width:100px;">
			<i class="fa fa-eye"></i> View File
		</button>

		</a>
	
	</td>
	<td class="padding-8 center" style="width:120px;">
	<!--
		<button type="button" class="btn btn-info" style="width:120px;" onclick="removeAttachment('{{Crypt::encrypt($attach->recordid)}}','{{$requestid}}')">
			<i class="fa fa-remove"></i> Remove File
		</button>
	-->
	</td>
</tr>

@endforeach
@endif
<tr>
	<td class="padding-8">
		<input type="text" name="attachmenttitle[]" required placeholder="Attachment title" autocomplete="off" class="full-wdth" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}">
	</td>
	<td class="padding-8" style="width:250px;">
		<input type="file" class="form-control attachment" required name="attachmentfile[]" accept="application/pdf" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}" />
	</td>
	<td class="padding-8" style="width:120px;">
		<button type="button" class="btn btn-info addBtn" style="width:120px;">
			<i class="fa fa-plus"></i> ADD
		</button>
	</td>
</tr>

<script>
jQuery(function($) {	

setTimeout(function() { 
	$(".uploaded").css("display","none"); 
	@if(Session::has('uploaded'))
	location.reload(); 
	@endif
},2000);

	$('.attachment').ace_file_input({
		no_file:'No File ...',
		btn_choose:'Attachment',
		btn_change:'Change',
		btn_name:'fourthimage',
		thumbnail:false //| true | large
	});
		
});

</script>