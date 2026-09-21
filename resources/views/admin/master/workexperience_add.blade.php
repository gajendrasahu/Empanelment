@extends('admin.admin_master')
@section('admin')
@php
$t=0;
@endphp

<div class="main-content">
	<div class="main-content-inner">
			<div class="page-content" style="padding:10px 15px!important;">
				<div class="ace-settings-container" id="ace-settings-container"></div>
				<div class="row">
					<div class="col-xs-12">
					<!-- PAGE CONTENT BEGINS -->
<div class="tabbable" style="padding:0px 0px!important;">
		<ul class="nav nav-tabs padding-0">
			<li class="active"><a data-toggle="tab" href="#home"><i class="green ace-icon fa fa-plus-circle bigger-120" style="vertical-align:bottom;"></i>Add Work Experience </a></li>
			@permission('workexperience.html')
			<li><a data-toggle="tab" href="#recordlist" onclick="loadData(1,'{{ route('workexperience.html') }}')"><i class="green ace-icon fa fa-list bigger-120 datalist" style="vertical-align:bottom;"></i> Work Experience List</a></li>
			@endpermission
		</ul>

		<div class="tab-content no-border" style="border:1px solid #ddd; min-height:100px; padding:0px 10px;">
<div id="home" class="tab-pane in active" style="padding:0px 10px!important;">
	<div class="row">
			<form name="frm" id="frm" action="{{ route('store.workexperience',0)}}" method="post" enctype="multipart/form-data" data-url="{{ route('send.otpsms')}}">
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
				@if(Session::has('permission_error'))
				<div class="col-sm-12">
					<div class="alert alert-block alert-danger">
				    	<button type="button" class="close" data-dismiss="alert">
							<i class="ace-icon fa fa-times"></i>
						</button>
						{{ Session::get('permission_error') }}
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
				@csrf
				<div class="form-group">
					
					<div class="col-sm-3">
						Work Experience <label id="req">*</label>
						<input type="hidden" name="form_token" id="form_token" value="{{$token}}">
						<input type="text" class="form-control" name="workexperience" id="workexperience" value="{{old('workexperience')}}" placeholder="Work Experience" autofocus required autocomplete="off" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}"/>
						
						<span class="text-danger">@error('workexperience') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
					</div>

					<div class="col-sm-2" align="left">
					@permission('store.workexperience')
						<button type="submit" class="btn btn-info myfrmbtn mt-25" tabindex="{{$t++}}">SUBMIT</button>
					@endpermission
					</div>
					<!--
					<div class="col-sm-2" align="left">
						<button type="button" class="btn btn-info myfrmbtn sendSmsBtn" tabindex="{{$t++}}">Send</button>
					</div>
					-->
				</div>		
			</form>
	</div>
</div>
@permission('workexperience.html')
<div id="recordlist" class="tab-pane" style="padding:0px 0px!important;">
	<div class="row" style="padding:0px;">
		<div class="col-xs-12">
		<form name="pagedata" id="pagedata" action="#" method="post" onsubmit="return false;">
			<div class="table-responsive">			
				<table class="table table-bordered table-striped table-hover" id="tablerecords">
				<thead>
				<tr>
					<td colspan="5">
					<div id="tablehead">
						<select name="pagesize" id="pagesize" class="select2" onchange="loadData(1,'{{ route('workexperience.html') }}')">
							<option value="15">15</option>
							<option value="100">100</option>
							<option value="300">300</option>
							<option value="500">500</option>														
						</select>
						<span class="input-icon" style="float:right;">
							<input type="text" placeholder="Search ..." class="nav-search-input" id="pagesearch" name="pagesearch" autocomplete="off" onchange="loadData(1,'{{ route('workexperience.html') }}')" tabindex="<?php echo $t++;?>" />
							<i class="ace-icon fa fa-search nav-search-icon" style="margin-top:-3px;"></i>
						</span>
					</div>
					</td>
				</tr>
				<tr class="myhead">
					<td style="width:25px;" nowrap><b>S.No.</b></td>
					<td nowrap style=""><b>Work Experience</b></td>
					<td style="width:25px;"></td>
					<td style="width:25px;"></td>
				</tr>
				</thead>
				<tbody class="tabledata">
				</tbody>
				</table>
			</div>
		</form>
		</div>
	</div>
</div>
@endpermission

</div>
</div>


						
				</div><!-- /.col -->
			</div><!-- /.row -->
		</div><!-- /.page-content -->
	
		
	</div>
</div>
<script src="{{ asset('panel/assets/js/jquery-2.1.4.min.js') }}"></script>
<script>
@if(app(\App\Services\PermissionService::class)->hasPermission('workexperience.html') && !app(\App\Services\PermissionService::class)->hasPermission('store.workexperience'))
	setTimeout(function() { $('.datalist').trigger('click'); },1000);
@endif


$('.sendSmsBtn').on('click', function(e) {
	e.preventDefault();
	var form = $('#frm')[0];
	var formData = new FormData(form);
	$.ajax({
		url: $('#frm').data('url'),
		method: 'POST',
		data: formData,
		processData: false,
		contentType: false,
		success: function(response)
		{
			bootbox.alert(response.message);
		},
		error: function(xhr) {
			let message = '';

			if (xhr.responseJSON?.errors) {
				$.each(xhr.responseJSON.errors, function(key, val) {
					message += '<i class="fa fa-hand-o-right"></i> ' + val + '<br>';
				});
			} else if (xhr.responseJSON?.message) {
				message = xhr.responseJSON.message;
			} else {
				message = 'Something went wrong. Please try again.';
			}

			bootbox.alert(message);
		}
	});
});

function ActivateBtn()
{
	var form 			=	document.getElementById('frm');
	var submitButton 	=	document.getElementById('myfrmbtn');
	var requiredFields 	=	form.querySelectorAll('[required]');
	var allValid		=	true;
	requiredFields.forEach(field => {
		if (!field.value.trim()) {
			allValid = false;
		}
	});
	if(allValid)
	{
		$(".myfrmbtn").prop("disabled","");
	}
}
function loadData(page,r1)
{
	var pagesize	=	document.getElementById("pagesize").value;
	var pagesearch	=	document.getElementById("pagesearch").value;
	$.get(""+r1,
	{
		page:page,
		pagesize:pagesize,
		pagesearch:pagesearch,
	},
	function(data, status){
		$(".tabledata").html(data);
		$(".mytr").click(function(){
			$(".mytr").removeClass("selected-color").addClass("reset-color");
			$(this).removeClass("reset-color").addClass("selected-color");
		});
	});
}

</script>

<script src="{{ asset('panel/assets/js/datatablePaginationWithFilter.js') }}"></script>
@endsection