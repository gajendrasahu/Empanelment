@php
$i=1;
use Carbon\Carbon;
$isBrodcasted	=	0;
@endphp
<form name="my_frm" id="my_frm" action="#" enctype="multipart/form-data">
<div class="modal-body" style="max-height:450px; overflow:scroll;">
	@if($ispassed==1)
	@if(!$isForwarded)
	<table class="mytable pd-8" border="1">
		<tr><td colspan="2"><b>Forward to Department / Project Manager</b></td></tr>
		<tr>
			<td colspan="2">
				<textarea name="prebidenquiry" id="prebidenquiry" class="width-full tinymce" placeholder="Forward to department / project manager"></textarea>
			</td>
		</tr>
		<tr>
			<td class="upload-file v-top" style="border-right:1px solid #fff;">
				<input type="file" class="attachment" required name="attachment" id="attachment" accept=".pdf" onKeyPress="return OnKeyPress(this, event)" onchange="uploadAttachment()"/>
			</td>
			<td class="text-right">
			@permission('forwardto.department')
				<button type="button" class="btn btn-info myfrmbtn forwardBtn right" style="width:330px;" onclick="forwardToDepartment('{{Crypt::encrypt($eoi->requestid)}}')">
					<i class="fa fa-mail-forward"></i> Forward to Department / Project Manager
				</button>
			@endpermission
			</td>
		</tr>
	</table>
	@else
	<table class="mytable pd-8" border="1">
		<tr>
			<td>
				<b>Pre-bid query forwarded to the department / project manager on {{date('d\-m\-Y, h:i A',strtotime($isForwarded->creationdate))}}</b>
			</td>
		</tr>
		@if($isForwarded->message)
		<tr>
			<td>
			{!!$isForwarded->message!!}
			@if($isForwarded->attachment)
				<a href="{{route('view.uploadedfile',Crypt::encrypt($isForwarded->attachment))}}" class="eoi-btn" target="_blank">
				<i class="fa fa-download"></i> View File
				</a>
			@endif
			</td>
		</tr>
		@endif
	</table>
	@endif
	
	@endif

	<table class="mytable pd-8" border="1">
		<tr class="myheadbg"><td colspan="5"><b>Firm-wise Pre-Bid Queries [{{$eoi->eoinumber}}]</b></td></tr>
	@foreach($data as $record)
		<tr class="myheadbg">
			<td colspan="5" class="v-middle" style="position:relative;">
				<b>{{ $record->companyname }} [Date : {{date('d\-m\-Y, h:i A',strtotime($record->raisedon))}}]</b>

@if($record->attachment)				
<a href="{{ route('view.uploadedfile', Crypt::encrypt($record->attachment)) }}" class="eoi-btn" target="_blank" style="position:absolute; right:5px; top:2px;">
	<i class="ace-icon fa fa-file-pdf-o"></i> View File
</a>
@endif
			</td>
		</tr>
		<tr class="myheadbg">
			<th class="padding-8 center width-50">S.No.</th>
			<th class="padding-8" nowrap>Clause Reference</th>
			<th class="padding-8" nowrap>Clause Detail</th>
			<th class="padding-8" nowrap>Queries with Justification</th>
			<th class="padding-8" nowrap>Response</th>
		</tr>
		@foreach($record->queries as $item)
		<tr>
			<td class="center">{{$loop->iteration}}</td>
			<td>{!!$item->clause_reference!!}</td>
			<td>{!!$item->clause_detail!!}</td>
			<td>{!!$item->queries_with_justification!!}</td>
			<td>{!!$item->answer!!}</td>
		</tr>
		@endforeach
		
		@if($record->prebidstatus=='BRODCASTED') @php $isBrodcasted	=	1; @endphp @endif
		
	@endforeach
	</table>
	
	@if($replied)
	<table class="mytable pd-8" border="1">
		<tr class="myheadbg">
			<td style="position:relative;">
				<b>Pre-Bid Query Response by Department / Project Manager</b>
				<span style="position:absolute; right:5px; font-weight:600;">Date : {{date('d\-m\-Y, h:i A',strtotime($replied->creationdate))}}</span>
			</td>
		</tr>
		<tr>
			<td style="position:relative;">
			{!!$replied->message!!}
			@if($replied->attachment)
				<a href="{{ route('view.uploadedfile', Crypt::encrypt($replied->attachment)) }}" class="eoi-btn" target="_blank" style="position:absolute; right:5px; top:2px;">
					<i class="ace-icon fa fa-file-pdf-o"></i> View File
				</a>
			@endif
			@if($replied->deadlinedate)
			<b>Revised Last Date for Submission of Proposal : {{date('d\-m\-Y, h:i A',strtotime($replied->deadlinedate))}}</b>
			@endif
			</td>
		</tr>
	</table>
	@if($isBrodcasted==0)
	<table class="mytable pd-8" border="1">
		<tr class="myheadbg">
			<td colspan="4" style="position:relative;">
				<b>Publish Pre-Bid Query Response to Firms</b>
			</td>
		</tr>
		<tr>
			<td colspan="4" style="padding:0px!important;">
				<textarea name="publish_message" id="publish_message" class="width-full tinymce" placeholder="Publish to firms.."></textarea>
			</td>
		</tr>
		<tr style="height:100px;">
			<td style="width:350px; border-right:1px solid #fff;">
				Attachment (if any)<br>
				<input type="file" class="width-200" name="publish_attachment" id="publish_attachment" accept=".pdf"/>
			</td>

			<td style="border-right:1px solid #fff; font-weight:600;">
				Last Date for Submission of Proposal<br>
				<input type="text" class="interview_date width-200" required name="deadlinedate" id="deadlinedate" value="{{date('d\-m\-Y h:i A',strtotime($eoi->deadlinedate))}}" placeholder="dd-mm-YYYY 5:30 PM"/>
			</td>

			<td style="border-right:1px solid #fff; font-weight:600;">
				Tentative Date of Presentation and Interview<br>
				<input type="text" class="interview_date width-200" required name="interviewdate" id="interviewdate" value="@if($eoi->interviewdate){{date('d\-m\-Y h:i A',strtotime($eoi->interviewdate))}}@endif" placeholder="dd-mm-YYYY 5:30 PM"/>
			</td>
			<td style="text-align:right;">
			@permission('publishto.firms')
			<button type="button" class="btn btn-info myfrmbtn publishResponseBtn width-250" onclick="publishPreBidResponse('{{Crypt::encrypt($eoi->requestid)}}')">
				<i class="fa fa-mail-reply"></i> Publish Responses to Firms
			</button>
			@endpermission
				
			</td>
		
		</tr>
	</table>
	@else
	@php
	$brodcasted	=	DB::table('eoi_request_prebid_broadcast')->where('requestid',$eoi->requestid)->where('isprebid',1)->first();
	@endphp
	<table class="mytable pd-8" border="1">
		<tr class="myheadbg">
			<td colspan="4" style="position:relative;">
				<b>Published Pre-Bid Query</b>
				<span style="position:absolute; right:5px; font-weight:600;">Date : {{date('d\-m\-Y, h:i A',strtotime($brodcasted->broadcastedon))}}</span>				
			</td>
		</tr>
		<tr>
			<td colspan="4" style="position:relative;">
			{!!$brodcasted->broadcastmessage!!}
			@if($brodcasted->attachment)
				<a href="{{ route('view.uploadedfile',Crypt::encrypt($brodcasted->attachment)) }}" class="eoi-btn" target="_blank" style="position:absolute; right:5px; top:2px;">
					<i class="ace-icon fa fa-file-pdf-o"></i> View File
				</a>
			@endif
			
			</td>
		</tr>
	</table>
	@endif
	@endif
</div>
</form>
<div class="modal-footer"><a class="eoi-btn action-a" onclick="Cls()" style="float:right; cursor:pointer;">Close</a></div>


<script src="{{ asset('panel/assets/js/ace-elements.min.js') }}"></script>
<script src="{{ asset('panel/assets/js/ace.min.js') }}"></script>
<script src="{{ asset('panel/assets/tinymce/tinymce.min.js') }}"></script>

<script>
jQuery(function($) {
	$('.attachment').ace_file_input({
		no_file:'No File ...',
		btn_choose:'Attachment (optional)',
		btn_change:'Change',
		btn_name:'fourthimage',
		thumbnail:false //| true | large
	});
	$('#publish_attachment').ace_file_input({
		no_file:'No File ...',
		btn_choose:'Attachment (optional)',
		btn_change:'Change',
		btn_name:'fourthimage',
		thumbnail:false //| true | large
	});

	$(".ace-file-container").css("width","300px");


  flatpickr(".interview_date", {
  	enableTime: true,
    dateFormat: "d-m-Y h:i K",
    defaultDate: "",
	allowInput: false
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

function forwardToDepartment(requestid)
{
	$(".forwardBtn").css("display","none");
	bootbox.confirm('Once the pre-bid query is forwarded, it cannot be undone.',function(result){
		if(result)
		{
			tinymce.triggerSave(); 
			var form = $('#my_frm')[0];
			var formData = new FormData(form);
			formData.append('requestid',requestid);
			$.ajax({
				url: '{{route("forwardto.department")}}',
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
						$(".forwardBtn").css("display","");
						bootbox.alert(response.message);
					}
				},
				error: function(xhr)
				{
					$(".forwardBtn").css("display","");
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
			$(".forwardBtn").css("display","");
		}
	});
}

function publishPreBidResponse(requestid)
{
	$(".publishResponseBtn").css("display","none");
	bootbox.confirm('Publishing the pre-bid responses to firms is a final action and cannot be reversed.<br><br><b>Please confirm if you wish to proceed.</b>',function(result){
		if(result)
		{
			tinymce.triggerSave(); 
			var form = $('#my_frm')[0];
			var formData = new FormData(form);
			formData.append('requestid',requestid);
			$.ajax({
				url: '{{route("publishto.firms")}}',
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
						$(".publishResponseBtn").css("display","");
						bootbox.alert(response.message);
					}
				},
				error: function(xhr)
				{
					$(".publishResponseBtn").css("display","");
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
			$(".publishResponseBtn").css("display","");
		}
	});
}

</script>
