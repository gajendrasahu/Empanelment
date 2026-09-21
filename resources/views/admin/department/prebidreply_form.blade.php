@extends('admin.admin_master')
@section('admin')
@php
$t=0;
@endphp

<div class="main-content">
<style>
.ace-file-container
{
	height:33px!important;
	line-height:33px!important;
	vertical-align:middle!important;
	padding:2px 5px!important;
}
</style>
	<div class="main-content-inner">
			<div class="page-content" style="padding:10px 15px!important;">
				<div class="ace-settings-container" id="ace-settings-container"></div>
				<div class="row">
					<div class="col-xs-12">
					<!-- PAGE CONTENT BEGINS -->
<div class="tabbable animated fadeInUp delay-02" style="padding:0px 0px!important;">
		<ul class="nav nav-tabs padding-0">
			<li class="active form-label font-14">
				<a data-toggle="tab" href="#home" onclick="loadData('{{ route('prebid.querieshtml') }}')">
					Pre-Bid Queries Received
				</a>
			</li>
		</ul>

		<div class="tab-content no-border" style="border:1px solid #ddd; min-height:100px; padding:0px 10px;">
<div id="home" class="tab-pane in active" style="padding:0px 10px!important;">
	<div class="row">
		<div class="row">
			<form name="frm" id="frm" action="{{route('store.reply')}}" method="post" enctype="multipart/form-data">
				@if(Session::has('success'))
				<div class="col-sm-12">
					<div class="alert alert-block alert-success">
				    	<button type="button" class="close" data-dismiss="alert">
							<i class="ace-icon fa fa-times"></i>
						</button>
						{{ Session::get('success') }}
					</div>
				</div>
				@endif
				@if(Session::has('fail'))
				<div class="col-sm-12">
					<div class="alert alert-block alert-danger">
				    	<button type="button" class="close" data-dismiss="alert">
							<i class="ace-icon fa fa-times"></i>
						</button>
						{{ Session::get('fail') }}
					</div>
				</div>
				@endif
				@if(Session::has('duplicate'))
				<div class="col-sm-12">
				    <div class="alert alert-block alert-danger">
				    	<button type="button" class="close" data-dismiss="alert">
							<i class="ace-icon fa fa-times"></i>
						</button>
				    	<i class="fa fa-warning"></i> {{ Session::get('duplicate') }}
				    </div>
				</div>
				@endif			
				@if ($errors->any())
					<div class="col-sm-12">
						<div class="alert alert-block alert-danger">
							<button type="button" class="close" data-dismiss="alert">
								<i class="ace-icon fa fa-times"></i>
							</button>
							@foreach ($errors->all() as $error)
								<i class="fa fa-warning"></i> {{ $error }}<br>
							@endforeach
						</div>
					</div>
				@endif		
				
				@csrf
				<div class="form-group">
	<table class="mytable pd-8" border="1">
		<tr class="myheadbg">
			<td colspan="5" class="v-middle" style="position:relative;">
				@if($forward->creationdate)<b>Forwarded on {{date('d\-m\-Y',strtotime($forward->creationdate))}} at {{date('h:i A',strtotime($forward->creationdate))}} by : CHiPS</b>@endif
			</td>
		</tr>
		<tr>
			<td colspan="5" style="position:relative;">
			{!!$forward->message!!}
			
			@if($forward->attachment)
			<a class="eoi-btn action-a" href="{{route('view.uploadedfile',Crypt::encrypt($forward->attachment))}}" style="position:absolute; cursor:pointer; right:5px; top:-2px;" target="_blank">
				View File
			</a>
			@endif
			
			</td>
		</tr>
	</table>
	<table class="mytable pd-8" border="1">
		<tr class="myheadbg">
			<td colspan="5" class="v-middle" style="position:relative;">
				<b>Pre-Bid Queries Received</b>
			</td>
		</tr>
		<tr class="myheadbg">
			<td colspan="5" class="lh-20">
				<b>Note*</b><br>
				1) Please review all the pre-bid queries listed below carefully.<br>
				2) Provide your response for each query individually by clicking on the “<b>Update Response</b>” button against the respective query.<br>
				3) Ensure that all queries are responded to one by one.<br>
				4) After completing responses for all queries, please click the “<b>Respond/Submit</b>” button to finalize and submit the responses.
			</td>
		</tr>
		
		<tr class="myheadbg">
			<th class="padding-8 center width-50">S.No.</th>
			<th class="padding-8" nowrap>Clause Reference</th>
			<th class="padding-8" nowrap>Clause Detail</th>
			<th class="padding-8" nowrap>Queries with Justification</th>
			<th class="padding-8" nowrap>
			@if($data->count()!=0)
			<a href="{{route('download.deptpmprebid',Crypt::encrypt($eoi->requestid))}}" style="text-decoration:none; margin-top:0px!important; margin-bottom:0px!important;">
				<span class="btn btn-info forwardBtn gridbtn">
					<i class="fa fa-download icon-animated-bell "></i> Download Pre-Bid Query
				</span>			
			</a>											
			@endif
			</th>
		</tr>
		@foreach($data as $item)
		<tr>
			<td class="center">{{$loop->iteration}}</td>
			<td>{!!$item->clause_reference!!}</td>
			<td>{!!$item->clause_detail!!}</td>
			<td>{!!$item->queries_with_justification!!}</td>
			<td>
				{!!$item->answer!!}
@if($item->answer)
<br>
@endif
@if($prebid->prebidstatus!='BRODCASTED' && $prebid->prebidstatus!='REPLIED')
<button type="button"
    class="btn btn-info myfrmbtn forwardBtn width-200 right"
    data-url="{{route('updateprebid.response',Crypt::encrypt($eoi->requestid))}}"
    data-value="{{Crypt::encrypt($item->questionid)}}"
    data-clause_reference="{{ $item->clause_reference }}"
    data-clause_detail="{{ $item->clause_detail }}"
    data-justification="{{ $item->queries_with_justification }}"
	data-answer="{{ $item->answer }}">
    
    <i class="fa fa-mail-forward"></i> Update Response
</button>
@endif
			</td>
		</tr>
		@endforeach
	</table>

					
				</div>		
			</form>

			
		</div>
	</div>
	
	<div class="row">
		<div class="col-sm-12 pd-5">
	<form name="submitResponse" id="submitResponse" action="{{route('submitprebid.response',Crypt::encrypt($eoi->requestid))}}" method="post" enctype="multipart/form-data">
	@csrf
	@if(!$replied)
	<table class="mytable pd-8" border="1">
		<tr class="myheadbg"><td colspan="2"><b>Reply to CHiPS</b></td></tr>
		<tr>
			<td colspan="2">
				<textarea name="prebid_reply" id="prebid_reply" class="width-full tinymce" placeholder="Reply to CHiPS.."></textarea>
			</td>
		</tr>
		<tr style="border-top:1px solid #fff;">
			<td style="border-right:1px solid #fff;">
				Revised Last Date for Submission of Proposal <b>(Optional)</b><br>
				<input type="text" class="todays_date" required name="deadlinedate" id="deadlinedate" placeholder="dd-mm-YYYY 5:30 PM"/>
			</td>
	
			<td style="width:300px; ">
				<input type="file" class="attachment width-200" required name="reply_attachment" id="reply_attachment" accept=".pdf"/>
			</td>
		</tr>
		<tr style="border-top:1px solid #fff;">
			<td style="border-right:1px solid #fff;">
				Last Date for Submission of Proposals<br>
				{{date('d\-m\-Y, h:i A',strtotime($eoi->deadlinedate))}}
			</td>
			
			<td style="text-align:right;">
				<button type="button" class="btn btn-info myfrmbtn submitResponseBtn width-200" onclick="submitPreBidResponse()">
					<i class="fa fa-mail-reply"></i> Submit Response
				</button>
			</td>
		</tr>
	</table>
	@else
	<table class="mytable pd-8" border="1">
		<tr class="myheadbg"><td colspan="2"><b>Replied to CHiPS on {{date('d\-m\-Y',strtotime($replied->creationdate))}} at {{date('h:i A',strtotime($replied->creationdate))}}</b></td></tr>
		<tr>
			<td colspan="2" style="position:relative;">
			{!!$replied->message!!}
			
			@if($replied->deadlinedate)
				<b>Revised Last Date for Submission of Proposal : {{date('d\-m\-Y, h:i A',strtotime($replied->deadlinedate))}}</b>
			@endif
			@if($replied->attachment)
			<a class="eoi-btn action-a" href="{{route('view.uploadedfile',Crypt::encrypt($replied->attachment))}}" style="position:absolute; cursor:pointer; right:5px; top:-2px;" target="_blank">
				View File
			</a>
			@endif
			
			</td>
		</tr>
	</table>
		
	@endif
	</form>
		<br><br>
		</div>
	</div>
	
</div>


</div>
</div>


						
				</div><!-- /.col -->
			</div><!-- /.row -->
		</div><!-- /.page-content -->
	
		
	</div>
</div>



<form name="updateResponse" id="updateResponse" action="#" method="post" enctype="multipart/form-data">
@csrf

<div class="modal fade" id="responseModal">
  <div class="modal-dialog modal-xl" style="width:95%; margin-top:0px;">
    <div class="modal-content" id="responseContent">
		<div class="modal-body" style="max-height:500px; overflow:scroll;">
			<input type="hidden" name="question_id" id="question_id" value="">
			<table class="mytable pd-5" border="1">
				<tr class="myheadbg">
					<td nowrap><b>Clause Reference</b></td>
					<td nowrap><b>Clause Detail</b></td>
					<td nowrap><b>Queries with Justification</b></td>
				</tr>
				<tr>
					<td class="clause_refrence"></td>
					<td class="clause_detail"></td>
					<td class="justification"></td>
				</tr>
				<tr>
					<td colspan="3">
						<textarea class="tinymce" name="prebid_response" id="prebid_response" placeholder="Write your response here..."></textarea>
					</td>
				</tr>
			</table>
		</div>
		<div class="modal-footer">

			<button type="button" class="eoi-btn action-a updateQueryResponse" id="updateQueryResponse" style="float:right; cursor:pointer;" onclick="UpdatePreBidResponse()">
				Update Response
			</button>

			<a class="eoi-btn action-a" onclick="Cls()" style="float:right; cursor:pointer;">
				Close
			</a>
			
		</div>
    </div>
  </div>
</div>
</form>

<script src="{{ asset('panel/assets/js/jquery-2.1.4.min.js') }}"></script>
<script src="{{ asset('panel/assets/js/ace-elements.min.js') }}"></script>
<script src="{{ asset('panel/assets/js/ace.min.js') }}"></script>
<script src="{{ asset('panel/assets/tinymce/tinymce.min.js') }}"></script>
<script>
//setTimeout(function() { $('.datalist').trigger('click'); },1000);


function Cls()
{
	$('#responseModal').modal('hide');
}

jQuery(function($) {	

	$('#attachment').ace_file_input({
		no_file:'No File ...',
		btn_choose:'Attachment (required)',
		btn_change:'Change',
		btn_name:'fourthimage',
		thumbnail:false //| true | large
	});
	$('#reply_attachment').ace_file_input({
		no_file:'No File ...',
		btn_choose:'Attachment (Optional)',
		btn_change:'Change',
		btn_name:'fourthimage',
		thumbnail:false //| true | large
	});

});	

function loadData(r1)
{
	var requestid	=	document.getElementById("requestid").value;
	$.get(""+r1,
	{
		requestid:requestid,
	},
	function(data, status){
		$(".tabledata").html(data);
	});
}

tinymce.init({
	selector: '#prebid_response',
	promotion: false,
	branding: false,
	height: 250,
	autoresize_max_height: 490,
	toolbar_mode: 'floating',
	plugins: 'code advlist autolink lists charmap preview table searchreplace save',
	toolbar: 'undo redo | formatselect | bold italic underline strikethrough | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link | searchreplace',
	setup: function (editor) {
			// Add custom CSS to the editor content area
			editor.on('init', function () {
				var contentArea = editor.getContainer().querySelector('.tox-edit-area__iframe');
				if (contentArea) {
					contentArea.style.overflow = 'auto';
				}
			});
		}
});

tinymce.init({
    selector: '#prebid_reply',
    promotion: false,
    branding: false,
    menubar: false,
    height: 250,
    autoresize_max_height: 490,
    toolbar_mode: 'floating',
    plugins: 'code advlist autolink lists charmap preview table searchreplace save',
    toolbar: 'undo redo | formatselect | bold italic underline strikethrough | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link | searchreplace',
    setup: function (editor) {
        editor.on('init', function () {
            var contentArea = editor.getContainer().querySelector('.tox-edit-area__iframe');
            if (contentArea) {
                contentArea.style.overflow = 'auto';
            }
        });
    }
});
$(document).on('click', '.forwardBtn', function(){

    var clause_reference = $(this).data('clause_reference');
    var clause_detail = $(this).data('clause_detail');
    var justification = $(this).data('justification');
	var answer = $(this).data('answer');
    var url = $(this).data('url');
    var questionid = $(this).data('value');

    $('.clause_refrence').html(clause_reference);
    $('.clause_detail').html(clause_detail);
    $('.justification').html(justification);

    $('#updateResponse').attr('action', url);

    $('#question_id').val(questionid);
	tinymce.get('prebid_response').setContent(answer);

    $('#responseModal').modal('show');

});

function UpdatePreBidResponse()
{
	$(".updateQueryResponse").css("display","none");
	bootbox.confirm('Do you confirm this action?.',function(result){
		if(result)
		{
			tinymce.triggerSave(); 
			var form = $('#updateResponse');
			var formData = new FormData(form[0]);
			$.ajax({
				url: form.attr('action'),
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
						$(".updateQueryResponse").css("display","");
						bootbox.alert(response.message);
					}
				},
				error: function(xhr)
				{
					$(".updateQueryResponse").css("display","");
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
			$(".updateQueryResponse").css("display","");
		}
	});	
}
function submitPreBidResponse()
{
	$(".submitResponseBtn").css("display","none");
	bootbox.confirm('Please confirm this action. Once confirmed, it cannot be undone.',function(result){
		if(result)
		{
			tinymce.triggerSave(); 
			var form = $('#submitResponse');
			var formData = new FormData(form[0]);
			$.ajax({
				url: form.attr('action'),
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
						$(".submitResponseBtn").css("display","");
						bootbox.alert(response.message);
					}
				},
				error: function(xhr)
				{
					$(".submitResponseBtn").css("display","");
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
			$(".submitResponseBtn").css("display","");
		}
	});	
}

</script>
<script src="{{ asset('panel/assets/js/datatablePaginationWithFilter.js') }}"></script>
@endsection