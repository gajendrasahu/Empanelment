@extends('admin.admin_master')
@section('admin')
@php
$t=0;
use Carbon\Carbon;
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
			<li class="active">
				<a data-toggle="tab" href="#home" class="form-label font-14">
					<i class="green ace-icon fa fa-question bigger-120 datalist" style="vertical-align:text-top;"></i>Pre-bid Query
				</a>
			</li>			
			
		</ul>

	
		<div class="tab-content no-border" style="border:1px solid #ddd; min-height:100px; padding:0px 10px;">
<div id="home" class="tab-pane in active" style="padding:0px 10px!important;">
	<div class="row">
		<div class="row">
			<form name="frm" id="frm" action="{{ route('store.prebidenquiry',$recordid)}}" method="post" enctype="multipart/form-data">
				@if(Session::has('success'))
					<br>
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
					<br>
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
					<br>
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
					<br>
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
					<div class="col-sm-12 form-label font-14">
						@if($formats)
						@if($formats->queryformat!='')

						Vendors are requested to submit their queries in the prescribed format. Click the `Download Query Format` link to download the official Query Format.<br>
						<a href="{{ route('view.uploadedfile', Crypt::encrypt($formats->queryformat)) }}" target="_blank">
							<span class="label label-white middle animated flipInX f-s-10 form-label span-content">
								<i class="ace-icon fa fa-download bigger-120 icon-animated-bell"></i> Download Query Format
							</span>						
						</a>
						@endif
						@endif
						
					</div>
				
					
					<div class="col-sm-12">
<table class="pd-5 mytable mt-10" border="1">
	<tr>
		<td class="myheadbg font-bold width-250">Clause Reference and Page Number</td>
		<td>
			<input type="hidden" name="float_id" id="float_id" value="{{$recordid}}">
			<textarea name="references" id="references" class="tinymce width-full" placeholder="Clause Reference and Page Number">@if($question) {!!$question->clause_reference!!} @endif</textarea>
		</td>
	</tr>
	<tr>
		<td class="myheadbg font-bold">Clause Details</td>
		<td>
			<textarea name="details" id="details" class="tinymce width-full" placeholder="Clause Details">@if($question) {!!$question->clause_detail!!} @endif</textarea>
		</td>
	</tr>
	<tr>
		<td class="myheadbg font-bold">Queries with Justification</td>
		<td>
			<textarea name="queries" id="queries" class="tinymce width-full" placeholder="Queries with Justification">@if($question) {!!$question->queries_with_justification!!} @endif</textarea>
		</td>
	</tr>
	<tr>
		<td class="myheadbg font-bold width-250">Raised by</td>
		<td>
			<input type="text" name="contact_name" id="contact_name" value="@if($contact){{$contact->contact_name}}@endif" class="width-full" value="" placeholder="Raised by" @if($contact) @if($contact->contact_name) readonly @endif @endif>
		</td>
	</tr>
	<tr>
		<td class="myheadbg font-bold width-250">Contact Number</td>
		<td>
			<input type="text" name="contact_number" id="contact_number" class="width-full" value="@if($contact){{$contact->contact_number}}@endif" placeholder="Contact Number" @if($contact) @if($contact->contact_number) readonly @endif @endif>
		</td>
	</tr>
	<tr>
		<td class="myheadbg font-bold width-250">Designation</td>
		<td>
			<input type="text" name="contact_designation" id="contact_designation" class="width-full" value="@if($contact){{$contact->contact_designation}}@endif" placeholder="Designation" @if($contact) @if($contact->contact_designation) readonly @endif @endif>
		</td>
	</tr>
	
	<tr class="myheadbg">
		<td colspan="2" class="lh-20">
			<b>Note*</b><br>
			1) Firms shall enter each query individually in the provided fields and click on the “<b>Add Query</b>” button to save the query.<br>
			2) This process should be repeated until all queries have been entered in the system.<br>
			3) After adding all queries, firms must upload the relevant supporting document/file in the designated attachment section.<br>
			4) Finally, firms must click on the “<b>Forward to CHiPS</b>” button to complete and submit the Pre-Bid Query for final submission.
		</td>
	</tr>
	
	<tr>
		<td colspan="2" class="text-right">
			@if(Carbon::parse($floated->prebidlastdate)->isFuture() || Carbon::parse($floated->prebidlastdate)->isToday())
				@if(!$question)
				<button type="button" class="btn btn-info width-100 myfrmbtn addQueryBtn" tabindex="{{$t++}}">Add Query</button>
				@else
				<button type="button" class="btn btn-info width-100 myfrmbtn updateQueryBtn" tabindex="{{$t++}}">Update Query</button>
				@endif
			@else
				<button type="button" class="btn btn-info width-200 myfrmbtn" disabled tabindex="{{$t++}}">Pre-Bid Last Date Passed</button>
			@endif		
			
		</td>
	</tr>
</table>
@if($query->count()!=0)		
<table class="pd-10 mytable mt-10" border="1">
	<tr class="myheadbg font-bold">
		<td colspan="5" style="position:relative; padding:15px 10px!important;">
			Firm Name : {{Session('userName')}}
			
			<a href="{{route('download.vendorprebid',Crypt::encrypt($floated->requestid))}}" style="text-decoration:none; position:absolute; right:5px; top:5px;">
				<span class="btn btn-info forwardBtn gridbtn">
					<i class="fa fa-download icon-animated-bell "></i> Download Pre-Bid Query
				</span>			
			</a>							
			
		</td>
	</tr>
	<tr class="myheadbg font-bold">
		<td class="center width-30">S.No.</td>
		<td nowrap>Clause Reference and Page Number</td>
		<td nowrap>Clause Details</td>
		<td>Queries with Justification</td>
		<td></td>
	</tr>
	@foreach($query as $qry)
	<tr>
		<td class="center width-30">{{$loop->iteration}}</td>
		<td>{!!$qry->clause_reference!!}</td>
		<td>{!!$qry->clause_detail!!}</td>
		<td>{!!$qry->queries_with_justification!!}</td>
		<td>
			@if(Carbon::parse($floated->prebidlastdate)->isFuture() || Carbon::parse($floated->prebidlastdate)->isToday())
			<a href="{{route('prebid.enquiry',['recordid'=>$recordid,'questionid'=>Crypt::encrypt($qry->questionid)])}}">
				<i class="fa fa-edit"></i>
			</a>
			@endif
		</td>
	</tr>
	@endforeach
	<tr>
		<td colspan="2" class="upload-file v-top" style="border-right:1px solid #fff;">
			<input type="file" class="attachment" required name="attachment" id="attachment" accept=".pdf" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}" onchange="uploadAttachment()"/>
		</td>
		<td class="text-right" colspan="3">
			@if(Carbon::parse($floated->prebidlastdate)->isFuture() || Carbon::parse($floated->prebidlastdate)->isToday())
				<button type="button" class="btn btn-info myfrmbtn forwardBtn width-200 right" tabindex="{{$t++}}">
					<i class="fa fa-mail-forward"></i> Forward to CHiPS
				</button>
				@if($queries->forwardedon)
				<br><br><br>
				<span style="float:right;">Last forwarded on : {{date('d\-m\-Y, h:i A',strtotime($queries->forwardedon))}}</span>
				@endif
			@else
				<button type="button" class="btn btn-info width-200 myfrmbtn" disabled tabindex="{{$t++}}">Pre-Bid Last Date Passed</button>
			@endif		
		</td>
	</tr>
	@if($queries->attachment)
	<tr>
		<td colspan="5" class="text-left">
		The Pre-Bid Query file has been uploaded successfully.<br>
		To replace it, simply attach a new file. Replacement is allowed until the pre-bid last date.<br><br>
		<a href="{{route('view.uploadedfile',Crypt::encrypt($queries->attachment))}}" target="_blank">
			<button type="button" class="btn btn-info width-200 myfrmbtn"><i class="fa fa-download"></i> View Uploaded File</button>
		</a>
		</td>
	</tr>
	@endif
</table>
@endif

					</div>
					<div class="col-sm-12">&nbsp;</div>
				</div>		
			</form>
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
<script src="{{ asset('panel/assets/js/jquery-2.1.4.min.js') }}"></script>

<script src="{{ asset('panel/assets/js/ace-elements.min.js') }}"></script>
<script src="{{ asset('panel/assets/js/ace.min.js') }}"></script>
<script src="{{ asset('panel/assets/tinymce/tinymce.min.js') }}"></script>
<script>
jQuery(function($) {
	$('.attachment').ace_file_input({
		no_file:'No File ...',
		btn_choose:'Attachment',
		btn_change:'Change',
		btn_name:'fourthimage',
		thumbnail:false //| true | large
	});
	
	$(".ace-file-container").css("width","300px");
});	

tinymce.init({
	selector: '#prebidenquiry',
	promotion: false,
	branding: false,
	height: 350,
	autoresize_max_height: 490,
	toolbar_mode: 'floating',
	plugins: 'lists charmap preview table',
	toolbar: 'undo redo | formatselect | bold italic underline strikethrough | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent',
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


@if(Carbon::parse($floated->prebidlastdate)->isFuture() || Carbon::parse($floated->prebidlastdate)->isToday())
	
$('.addQueryBtn').on('click', function(e) {
	$(".addQueryBtn").prop("disabled","disabled");
	$(".addQueryBtn").css("display","none");
	bootbox.confirm('Do you confirm this action?',function(result){
		if(result)
		{
			e.preventDefault();
			var form = $('#frm')[0];
			var formData = new FormData(form);
			$.ajax({
				url: '{{route("store.query")}}',
				method: 'POST',
				data: formData,
				processData: false,
				contentType: false,
				success: function(response) {
					if(response.status===200)
					{
						bootbox.alert(response.message, function () {
							window.location.reload();
						});						
						return false;
					}
					else
					{
						$(".addQueryBtn").css("display","");			
						$(".addQueryBtn").prop("disabled","");
						bootbox.alert('<span style="color:red;">'+response.message+'</span>');
						return false;
					}
				},
				error: function(xhr) {
					let errors = xhr.responseJSON?.errors;
					let message = '';
					$.each(errors, function(key, val) {
						message += '<i class="fa fa-hand-o-right"></i> '+ val + '<br>';
					});
					bootbox.alert(message);
					$(".addQueryBtn").css("display","");			
					$(".addQueryBtn").prop("disabled","");
				}
			});
		}
		else
		{
			$(".addQueryBtn").css("display","");			
			$(".addQueryBtn").prop("disabled","");
		}
	});
});

$('.updateQueryBtn').on('click', function(e) {
	$(".updateQueryBtn").prop("disabled","disabled");
	$(".updateQueryBtn").css("display","none");
	bootbox.confirm('Do you confirm this action?',function(result){
		if(result)
		{
			e.preventDefault();
			var form = $('#frm')[0];
			var formData = new FormData(form);
			$.ajax({
				url: '{{route("update.query")}}',
				method: 'POST',
				data: formData,
				processData: false,
				contentType: false,
				success: function(response) {
					if(response.status===200)
					{
						bootbox.alert(response.message, function () {
							window.location.href = response.redirect_url;
						});						
						return false;
					}
					else
					{
						$(".updateQueryBtn").css("display","");			
						$(".updateQueryBtn").prop("disabled","");
						bootbox.alert('<span style="color:red;">'+response.message+'</span>');
						return false;
					}
				},
				error: function(xhr) {
					let errors = xhr.responseJSON?.errors;
					let message = '';
					$.each(errors, function(key, val) {
						message += '<i class="fa fa-hand-o-right"></i> '+ val + '<br>';
					});
					bootbox.alert(message);
					$(".updateQueryBtn").css("display","");			
					$(".updateQueryBtn").prop("disabled","");
				}
			});
		}
		else
		{
			$(".updateQueryBtn").css("display","");			
			$(".updateQueryBtn").prop("disabled","");
		}
	});
});


function uploadAttachment()
{
    var fileInput = $('#attachment')[0];

    if(fileInput.files.length===0)
	{
        return;
    }

    var form 	=	$('#frm')[0];
    var formData= 	new FormData(form);

    $.ajax({
        url: '{{route("upload.prebidfile")}}',
        method: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {

            if(response.status === 200)
            {
                bootbox.alert(response.message, function () {
                    window.location.href = response.redirect_url;
                });
            }
            else
            {
                bootbox.alert('<span style="color:red;">'+response.message+'</span>');
            }
        },
        error: function(xhr) {

            let errors 	= 	xhr.responseJSON?.errors;
            let message =	'';

            $.each(errors, function(key, val) {
                message += '<i class="fa fa-hand-o-right"></i> '+ val + '<br>';
            });

            bootbox.alert(message);
        }
    });
}

$('.forwardBtn').on('click', function(e) {
	$(".forwardBtn").prop("disabled","disabled");
	$(".forwardBtn").css("display","none");
	bootbox.confirm('Are you sure you want to forward this?<br>After confirmation, it will appear in the CHiPS portal.',function(result){
		if(result)
		{
			e.preventDefault();
			var form = $('#frm')[0];
			var formData = new FormData(form);
			$.ajax({
				url: '{{route("forward.prebidquery")}}',
				method: 'POST',
				data: formData,
				processData: false,
				contentType: false,
				success: function(response) {
					if(response.status===200)
					{
						bootbox.alert(response.message, function () {
							window.location.href = response.redirect_url;
						});						
						return false;
					}
					else
					{
						$(".forwardBtn").css("display","");			
						$(".forwardBtn").prop("disabled","");
						bootbox.alert('<span style="color:red;">'+response.message+'</span>');
						return false;
					}
				},
				error: function(xhr) {
					let errors = xhr.responseJSON?.errors;
					let message = '';
					$.each(errors, function(key, val) {
						message += '<i class="fa fa-hand-o-right"></i> '+ val + '<br>';
					});
					bootbox.alert(message);
					$(".forwardBtn").css("display","");			
					$(".forwardBtn").prop("disabled","");
				}
			});
		}
		else
		{
			$(".updateQueryBtn").css("display","");			
			$(".updateQueryBtn").prop("disabled","");
		}
	});
});

@endif
</script>
<script src="{{ asset('panel/assets/js/datatablePaginationWithFilter.js') }}"></script>
@endsection