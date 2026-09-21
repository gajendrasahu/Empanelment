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
					<i class="green ace-icon fa fa-mail-reply bigger-120 datalist" style="vertical-align:text-top;"></i>Reply – To CHiPS
				</a>
			</li>
		</ul>

	
		<div class="tab-content no-border" style="border:1px solid #ddd; min-height:100px; padding:0px 10px;">
<div id="home" class="tab-pane in active" style="padding:0px 10px!important;">
	<div class="row">
		<div class="row">
			<form name="frm" id="frm" action="{{route('store.pmreply')}}" method="post" enctype="multipart/form-data">
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
					<div class="col-sm-12 tabledata">
						
					</div>
					<div class="col-sm-12">&nbsp;</div>
					<div class="col-sm-12">
						Pre-bid Query Response <label id="req">&nbsp;</label>
						<input type="hidden" name="requestid" id="requestid" value="{{Crypt::encrypt($eoi->requestid)}}">
<textarea class="form-control" name="prebidreply" id="prebidreply" placeholder="Pre-bid Query Response...">{{old('prebidreply')}}</textarea>
						
						<span class="text-danger">@error('prebidreply') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
					</div>
					<div class="col-sm-12">&nbsp;</div>
					<div class="col-sm-12 form-label font-14">
					@if($formats)
					@if($formats->queryformat!='')
						<br>
						Departments are requested to submit their responses to pre-bid queries in the prescribed Response Format.<br>
						<a href="{{ route('view.uploadedfile', Crypt::encrypt($formats->responseformat)) }}" target="_blank">
							<span class="label label-white middle animated flipInX f-s-10 form-label span-content">
								<i class="ace-icon fa fa-download bigger-120 icon-animated-bell"></i> Download Response Format
							</span>						
						</a>
					@endif
					@endif
					</div>
					<div class="col-sm-12">&nbsp;</div>
					<div class="col-sm-4">
						Attach File <label id="req">*</label>
						<input type="file" class="form-control" required name="attachment" id="attachment" accept=".pdf" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}"/>						
						<span class="text-danger">@error('attachment') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
					</div>
					
					<div class="col-sm-6">&nbsp;</div>
					<div class="col-sm-2" align="left">
					@if(!$exists)
						<button type="submit" class="btn btn-info myfrmbtn" tabindex="{{$t++}}" style="text-transform:none!important;">
							<i class="fa fa-mail-reply icon-animated-bell"></i> Reply to CHiPS
						</button>
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
setTimeout(function() { $('.datalist').trigger('click'); },1000);
jQuery(function($) {	

	$('#attachment').ace_file_input({
		no_file:'No File ...',
		btn_choose:'Attachment (required)',
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
	selector: '#prebidreply',
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

</script>
<script src="{{ asset('panel/assets/js/datatablePaginationWithFilter.js') }}"></script>
@endsection