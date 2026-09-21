@extends('admin.admin_master')
@section('admin')
@php
$t=1;
@endphp
@php
    $canAutoOpen = app(\App\Services\PermissionService::class)->hasPermission('users.html') && !app(\App\Services\PermissionService::class)->hasPermission('store.user');
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
			@permission('store.user')
			<li class="active">
				<a data-toggle="tab" href="#home">
					<i class="green ace-icon fa fa-plus-circle bigger-120" style="vertical-align:bottom;"></i> Add User
				</a>
			</li>
			@endpermission
			@permission('users.html')
			<li @if($canAutoOpen) class="active" @endif>
				<a data-toggle="tab" href="#recordlist" onclick="loadData(1,'{{ route('users.html') }}')">
					<i class="green ace-icon fa fa-list bigger-120 datalist" style="vertical-align:bottom;"></i> User List
				</a>
			</li>
			@endpermission
		</ul>

	
		<div class="tab-content no-border" style="border:1px solid #ddd; padding:0px 10px; border-radius:0px!important;">
@permission('store.user')		
<div id="home" class="tab-pane in active" style="padding:0px 11px!important; margin-top:-8px;">
	<div class="row">
		<div class="row">
			<form name="frm" id="frm" action="{{ route('store.user',0)}}" method="post" enctype="multipart/form-data">
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
				@csrf
				<div class="form-group">

					<div class="col-sm-3">
						<label class="field-label">
							Name <span class="req">*</span>
						</label>
						<input type="hidden" name="form_token" id="form_token"
							value="{{$token}}">
						<input type="text" class="form-control" name="name" id="name"
							value="{{old('name')}}" placeholder="Name" required
							autocomplete="off" onKeyPress="return OnKeyPress(this, event)"
							tabindex="{{$t++}}" />

						<span class="text-danger">@error('name') <i
							class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i>
						@enderror</span>
					</div>

					<div class="col-sm-3">
						<label class="field-label">
							Mobile Number <span class="req">*</span>
						</label>
						<input type="number" class="form-control numbers" required
							name="mobilenumber" id="mobilenumber"
							value="{{old('mobilenumber')}}"
							placeholder="Enter 10-digit mobile number" autocomplete="off"
							onKeyPress="return OnKeyPress(this, event)"
							tabindex="{{$t++}}" />

						<span class="text-danger">@error('mobilenumber') <i
							class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i>
						@enderror</span>
					</div>
					<div class="col-sm-4">
						<label class="field-label">
							Email <span class="req">(login id)*</span>
						</label>
						<input type="text" class="form-control" name="email" id="email"
							value="{{old('email')}}" placeholder="Enter email address"
							required autocomplete="off"
							onKeyPress="return OnKeyPress(this, event)"
							tabindex="{{$t++}}" />

						<span class="text-danger">@error('email') <i
							class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i>
						@enderror</span>
					</div>
					
					<div class="col-sm-2">
						@permission('store.user')
						<button type="submit" class="btn btn-info myfrmbtn width-100 mt-25" tabindex="{{$t++}}">Submit</button>
						@endpermission
					</div>
				</div>
			</form>
		</div>
	</div>
</div>
@endpermission
@permission('users.html')
<div id="recordlist" class="tab-pane @if($canAutoOpen) in active @endif" style="padding:0px 0px!important; margin-top:-8px;">
	<div class="row" style="padding:0px;">
		<div class="col-xs-12">
		<form name="pagedata" id="pagedata" action="#" method="post" onsubmit="return false;">
			<div class="table-responsive">			
				<table class="table table-bordered table-striped pd-5" id="tablerecords">
				<tr>
					<td colspan="6">
						<select name="pagesize" id="pagesize" class="select2" onchange="loadData(1,'{{ route('users.html') }}')" data-width="95">
							<option value="25">25</option>
							<option value="50">50</option>
							<option value="100">100</option>
							<option value="300">300</option>
							<option value="500">500</option>														
						</select>
						<select class="select2" name="role_id" id="role_id" onKeyPress="return OnKeyPress(this, event)"  data-placeholder="Role" onchange="loadData(1,'{{ route('users.html') }}')" data-width="150">
							<option value=""></option>
							@foreach ($roles as $itm)
							<option value="{{ $itm->roleid }}">{{$itm->role}}</option>
							@endforeach
						</select>
						<span class="input-icon" style="float:right;">
							<input type="text" placeholder="Search ..." class="nav-search-input" id="pagesearch" name="pagesearch" autocomplete="off" onchange="loadData(1,'{{ route('users.html') }}')" tabindex="<?php echo $t++;?>" />
							<i class="ace-icon fa fa-search nav-search-icon" style="margin-top:-3px;"></i>
						</span>
					</td>
				</tr>
				<tr>
					<td style="width:25px;" nowrap><b>S.No.</b></td>
					<td nowrap><b>Name</b></td>
					<td nowrap><b>Email</b></td>
					<td nowrap><b>Mobile Number</b></td>
					<td nowrap><b>Role</b></td>
					<td style="width:25px;"></td>
				</tr>
			
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


						<div class="hr hr32 hr-dotted"></div>
				</div><!-- /.col -->
			</div><!-- /.row -->
		</div><!-- /.page-content -->
	
		
	</div>
</div>


<script src="{{ asset('panel/assets/js/jquery-2.1.4.min.js') }}"></script>
<script src="{{ asset('panel/assets/js/ace-elements.min.js') }}"></script>
<script src="{{ asset('panel/assets/js/ace.min.js') }}"></script>
<script src="{{ asset('panel/assets/js/bootstrap-multiselect.min.js') }}"></script>
<script>
@if(!in_array(1,Session::get('actions')))
	setTimeout(function() { $('.datalist').trigger('click'); },1000);
@endif

function loadData(page,r1)
{
	var roleid	=	document.getElementById("role_id").value;
	var pagesearch	=	document.getElementById("pagesearch").value;
	var pagesize	=	document.getElementById("pagesize").value;
	$.get(""+r1,
	{
		page:page,
		pagesize:pagesize,
		pagesearch:pagesearch,
		roleid:roleid,
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
<script src="{{ asset('panel/assets/js/master.js') }}"></script>
<script src="{{ asset('panel/assets/js/datatablePaginationWithFilter.js') }}"></script>
@endsection