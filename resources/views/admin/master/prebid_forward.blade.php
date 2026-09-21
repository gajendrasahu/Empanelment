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
					<i class="green ace-icon fa fa-mail-forward bigger-120 datalist" style="vertical-align:text-top;"></i> Pre-bid Query Forward / Publish
				</a>
			</li>
		</ul>

	
		<div class="tab-content no-border" style="border:1px solid #ddd; min-height:100px; padding:0px 10px;">
<div id="home" class="tab-pane in active" style="padding:0px 10px!important;">
	<div class="row">
		<div class="row">
			<form name="frm" id="frm" action="{{route('store.forward')}}" method="post" enctype="multipart/form-data">
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
						Message / Query / Reply <label id="req">&nbsp;</label>
						<input type="hidden" name="requestid" id="requestid" value="{{Crypt::encrypt($eoi->requestid)}}">
						<input type="hidden" name="form_token" id="form_token" value="{{$token}}">
						<textarea class="form-control" name="prebidenquiry" id="prebidenquiry" placeholder="Message / Query / Reply">{{old('prebidenquiry')}}</textarea>
						
						<span class="text-danger">@error('prebidenquiry') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
					</div>
					<div class="col-sm-12">&nbsp;</div>
					<div class="col-sm-4">
						Attach File <label id="req">*</label>
						<input type="file" class="form-control" name="attachment" id="attachment" accept=".pdf,.doc,.docx" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}"/>						
						<span class="text-danger">@error('attachment') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
					</div>
					<div class="col-sm-3">&nbsp;</div>
					<div class="col-sm-3" align="left">
						<button type="submit" class="btn btn-info myfrmbtn" id="fwdBtn" tabindex="{{$t++}}">
							<i class="fa fa-mail-forward icon-animated-bell"></i> Forward to Department
						</button>
					</div>
					<div class="col-sm-2" align="left">
					<a href="{{ route('prebidenquiry.list',Crypt::encrypt($eoi->requestid))}}">
						<button type="button" class="btn btn-info myfrmbtn" style="width:130px;">
							<i class="fa fa-arrow-left"></i> Back
						</button>
					</a>
					</div>
					<div class="col-sm-12">&nbsp;</div>
					<div class="col-sm-12">			
						<label style="display:flex; align-items:flex-start; gap: 10px; font-size: 14px; line-height: 1.5;">
						  <input type="checkbox" name="declaration" id="declaration" style="width: 16px; height: 16px; margin-top:3px;" tabindex="{{$t++}}">
						  <span>
							Do you confirm that the pre-bid query replies are final and ready to be published to vendors?
						  </span>
						</label>
					</div>
					<div class="col-sm-12">&nbsp;</div>
					
					<div class="col-sm-3">
						<label>Submission Date*</label>
						<input type="text" class="date-picker" data-date-format="dd-mm-yyyy" name="deadlinedate" id="deadlinedate" required value="{{ date('d-m-Y h:i A',strtotime(old('deadlinedate',$eoi->deadlinedate))) }}" placeholder="dd-mm-YYYY" style="width:200px;">
					</div>

					<div class="col-sm-3">
						<label>Tentative Interview Date</label>
						<input type="text" class="date-picker interview_date" data-date-format="dd-mm-yyyy" name="interviewdate" id="interviewdate" required value="@if($eoi->interviewdate){{ date('d-m-Y h:i A',strtotime(old('interviewdate',$eoi->interviewdate))) }}@endif" placeholder="dd-mm-YYYY 10:00 AM" style="width:200px;">
					</div>

					<div class="col-sm-3">
						<br>
						<button type="submit" class="btn btn-info publishtovendors myfrmbtn" id="publishtovendors" disabled tabindex="{{$t++}}">
							<i class="fa fa-bullhorn icon-animated-bell"></i> Publish to Firms
						</button>
					</div>
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


document.getElementById('declaration').addEventListener('change', function () {
	document.getElementById('publishtovendors').disabled = !this.checked;
	document.getElementById('fwdBtn').disabled = this.checked;
});
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
	selector: '#prebidenquiry',
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