@extends('admin.admin_master')
@section('admin')
@php
$t=1;
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
		@if(in_array(1,Session::get('actions')))
			<li class="active">
				<a data-toggle="tab" href="#home">
					<i class="green ace-icon fa fa-plus-circle bigger-120" style="vertical-align:bottom;"></i> Change Password
				</a>
			</li>
		@endif
		</ul>

	
		<div class="tab-content no-border" style="border:1px solid #ddd; min-height:100px; padding:0px 10px;">
<div id="home" class="tab-pane in active" style="padding:0px 10px!important;">
	<div class="row">
		<div class="row">
			<form name="frm" id="frm" action="{{ route('store.password')}}" method="post" enctype="multipart/form-data">
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
						Enter Old Password <label id="req">*</label>
						<input type="password" class="form-control" name="old_password" id="old_password" value="{{old('old_password')}}" placeholder="Enter old password"  autocomplete="off" required onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}"/>
						
						<span class="text-danger">@error('old_password') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
					</div>
					<div class="col-sm-3">
						Enter New Password <label id="req">*</label>
						<input type="password" class="form-control" name="user_password" id="user_password" value="{{old('user_password')}}" placeholder="Enter new password"  autocomplete="off" required onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}"/>
						
						<span class="text-danger">@error('user_password') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
					</div>
					<div class="col-sm-3">
						Confirm New Password <label id="req">*</label>
						<input type="password" class="form-control" name="user_password_confirmation" id="user_password_confirmation" value="{{old('user_password_confirmation')}}" placeholder="Enter new password"  autocomplete="off" required onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}"/>
						
						<span class="text-danger">@error('user_password_confirmation') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
					</div>
					<div class="col-sm-3" align="left">
						<button type="submit" class="btn btn-info myfrmbtn mt-25" tabindex="{{$t++}}">Update Password</button>
					</div>
					<div class="col-sm-12" style="padding:20px;">
						Password must contain:
<ul>
    <li>Must be 8–25 characters long</li>
    <li>Must contain at least one uppercase letter (A–Z)</li>
    <li>Must contain at least one lowercase letter (a–z)</li>
    <li>Must contain at least one number (0–9)</li>
    <li>Must contain at least one special character (@, #, $, %, &, *, etc.)</li>
</ul>
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
<script>
@if(!in_array(1,Session::get('actions')))
	setTimeout(function() { $('.datalist').trigger('click'); },1000);
@endif

function loadData(page,r1)
{
	var pagesize		=	document.getElementById("pagesize").value;
	var stateid			=	document.getElementById("stid").value;
	var pagesearch		=	document.getElementById("pagesearch").value;
	$.get(""+r1,
	{
		page:page,
		pagesize:pagesize,
		pagesearch:pagesearch,
		stateid:stateid,
	},
	function(data, status){
		$(".tabledata").html(data);
		$(".mytr").click(function(){
			$(".mytr").removeClass("selected-color").addClass("reset-color");
			$(this).removeClass("reset-color").addClass("selected-color");
		});
	});
}
function SetDefaultCity(r1,id,ind)
{
	$.get(""+r1,
	{
		cityid:id
	},
	function(data, status){
		if(data.success)
		bootbox.alert('DEFAULT CITY UPDATED!');
		else if(data.error)
		bootbox.alert(data.fail);
	});
}
function SetOperatingStatus(r1,id,ind)
{
	$.get(""+r1,
	{
		cityid:id
	},
	function(data, status){
		if(data.success)
		{
			bootbox.alert('OPERATING STATUS CHANGED SUCCESSFULLY!');
			loadData(1,'{{ route('city.html') }}');
		}
		else if(data.error)
		{
			bootbox.alert(data.fail);
			loadData(1,'{{ route('city.html') }}');
		}
	});
	
}

</script>

<script src="{{ asset('panel/assets/js/datatablePaginationWithFilter.js') }}"></script>
@endsection