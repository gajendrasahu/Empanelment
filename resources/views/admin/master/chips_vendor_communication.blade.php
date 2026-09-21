@extends('admin.admin_master')
@section('admin')
@php
$t=1;
@endphp

<div class="main-content">
	<div class="main-content-inner" style="background-color:white;">
		<div class="page-content" style="padding:10px 15px!important;">
			<div class="ace-settings-container" id="ace-settings-container"></div>
			<div class="row">
				<div class="col-xs-12">
				
<form name="communication" id="communication" action="{{route('storevendor.message')}}" enctype="multipart/form-data">
@csrf
<div  style="height:250px; max-height:250px; overflow:scroll;">
<table class="mytable pd-8" border="1">
	<tr class="myheadbg">
		<td colspan="5" class="v-middle" style="position:relative;">
			<b>{{$data->companyname}}</b>			
		</td>
	</tr>
	<tbody>
	@foreach($messages as $record)
		@if($record->sentby=='CHiPS' && session('userType')!='VENDOR')
		<tr>
			<td class="padding-8" style="text-align:right;">
				<span style="">Posted on : {{date('d\-m\-Y, h:i A',strtotime($record->sent_on))}} by : <b>{{$record->sentby}}</b></span><br>
				<span style="">{{strip_tags($record->message)}}</span><br><br>
				@if($record->attachment)
					<a href="{{ route('view.uploadedfile', Crypt::encrypt($record->attachment)) }}" class="eoi-btn" target="_blank" style="right:5px;">
						<i class="ace-icon fa fa-file-pdf-o"></i> View File
					</a>
				@endif
				
			</td>
		</tr>	
		@else
		<tr>
			<td class="padding-8" style="position:relative; text-align:left;">
				<span style="">Posted on : {{date('d\-m\-Y, h:i A',strtotime($record->sent_on))}} by : <b>{{$record->sentby}}</b></span><br>
				<span style="">{!!strip_tags($record->message)!!}</span><br><br>
				@if($record->attachment)
					<a href="{{ route('view.uploadedfile', Crypt::encrypt($record->attachment)) }}" class="eoi-btn" target="_blank">
						<i class="ace-icon fa fa-file-pdf-o"></i> View File
					</a>
				@endif
			</td>
		</tr>	
		@endif
	@endforeach
	<tbody>
	@if($messages->count()==0)
	<tr>
		<td class="padding-8" style="text-align:center;">
		--No Message Found--
		</td>
	</tr>			
	@endif
</table>
</div>
<table class="mytable pd-8" border="1">
	<tr class="myheadbg">
		<td colspan="2" style="position:relative;">
			<b>Message*</b>
		</td>
	</tr>
	<tr>
		<td colspan="2" style="padding:0px!important;">
			<textarea name="message" id="message" class="width-full" placeholder="write your message / reply.."></textarea>
		</td>
	</tr>
	<tr style="height:100px;">
		<td style="width:350px; border-right:1px solid #fff;">
			Attachment (if any)<br>
			<input type="file" class="width-200" name="attachment" id="attachment" accept=".pdf"/>
		</td>

		<td style="text-align:right;">
			<button type="button" class="btn btn-info myfrmbtn sendBtn width-250" onclick="sendMessage('{{Crypt::encrypt($data->userid)}}')">
				<i class="fa fa-mail-reply"></i> Send Message
			</button>
			
		</td>
	
	</tr>
</table>

</form>
				</div>
			</div>
		</div>
	</div>
</div>

<script src="{{ asset('panel/assets/js/jquery-2.1.4.min.js') }}"></script>
<script src="{{ asset('panel/assets/js/ace-elements.min.js') }}"></script>
<script src="{{ asset('panel/assets/js/ace.min.js') }}"></script>
<script src="{{ asset('panel/assets/tinymce/tinymce.min.js') }}"></script>

<script>
$(document).ready(function() {
	$.ajaxSetup({
		headers: {
			'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
		}
	});	
});

jQuery(function($) {
	$('#attachment').ace_file_input({
		no_file:'No File ...',
		btn_choose:'Attachment (optional)',
		btn_change:'Change',
		btn_name:'fourthimage',
		thumbnail:false //| true | large
	});
	
});
tinymce.init({
    selector: 'textarea.tinymce',
    promotion: false,
    branding: false,

    min_height:100,
    autoresize_min_height:100,
    autoresize_max_height:700,
    autoresize_bottom_margin:10,

    toolbar_mode: 'floating',
    plugins: 'autoresize code lists charmap preview table',
    toolbar: 'undo redo | formatselect | bold underline | alignleft aligncenter alignright alignjustify | bullist numlist',

    menubar: false,
    statusbar: false,
    resize: true,

    content_style: `
        body {
            font-family: "Times New Roman", serif;
            font-size: 14px;
            line-height: 1.5;
        }
    `,

    setup: function(editor) {
        editor.on('change keyup', function() {
            editor.save();
        });
    }
});

function sendMessage(userid)
{
	$(".sendBtn").css("display","none");
	bootbox.confirm('Do you confirm this action?',function(result){
		if(result)
		{
			tinymce.triggerSave(); 
			var form = $('#communication')[0];
			var formData = new FormData(form);
			formData.append('userid',userid);
			$.ajax({
				url: '{{route("storevendor.message")}}',
				method: 'POST',
				data: formData,
				processData: false,
				contentType: false,
				success:function(response)
				{
					if(response.status===200)
					{
						bootbox.alert(response.message);
						setTimeout(function(){ window.location.reload(); },5000);
					}
					else
					{
						$(".sendBtn").css("display","");
						bootbox.alert(response.message);
					}
				},
				error: function(xhr)
				{
					$(".sendBtn").css("display","");
					let errors	=	xhr.responseJSON?.errors;
					let message	=	'';
					$.each(errors, function(key, val)
					{
						message += '<i class="fa fa-hand-o-right"></i> '+ val + '<br>';
					});
					bootbox.alert(message);
				}
			});
		}
		else
		{
			$(".sendBtn").css("display","");
		}
	});
}

</script>
<script src="{{ asset('panel/assets/js/master.js') }}"></script>
<script src="{{ asset('panel/assets/js/datatablePaginationWithFilter.js') }}"></script>
@endsection