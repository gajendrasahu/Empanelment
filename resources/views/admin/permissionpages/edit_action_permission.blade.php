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
			<li class="active"><a data-toggle="tab" href="#home"><i class="green ace-icon fa fa-plus-circle bigger-120" style="vertical-align:bottom;"></i>Update Action Permission </a></li>
		</ul>

		<div class="tab-content no-border" style="border:1px solid #ddd; min-height:100px; padding:0px 10px;">
<div id="home" class="tab-pane in active" style="padding:0px 10px!important;">
	<div class="row">
			<form name="frm" id="frm" action="{{ route('update.actionpermission',$data->permissionid)}}" method="post" enctype="multipart/form-data">
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
					
					<div class="col-sm-12">
						Menu <label id="req">*</label>
						<input type="hidden" name="form_token" id="form_token" value="{{$token}}">
						<select class="select2 form-control" data-width="100%" name="permissionid" id="permissionid" data-placeholder="Menu Name" autofocus>
							<option value=""></option>
							@foreach ($menus as $menu)
								<option value="{{ $menu->permissionid }}" @if($menu->menuid==$data->menuid) selected @endif>
									@if(__($menu->mastermenu)!=''){{ __($menu->mastermenu) }} -@endif {{ $menu->permission_name }}
								</option>
							@endforeach
						</select>
						
						<span class="text-danger">@error('permissionid') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
					</div>
					<div class="col-sm-3">
						Permission Name <label id="req">*</label>
						<input type="text" class="form-control" name="permission_name" id="permission_name" value="{{old('permission_name',$data->permission_name)}}" placeholder="Permission Name" required autocomplete="off" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}"/>
						
						<span class="text-danger">@error('permission_name') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
					</div>
					<div class="col-sm-3">
						Route Name <label id="req">*</label>
						<input type="text" class="form-control" name="route_name" id="route_name" value="{{old('route_name',$data->route_name)}}" placeholder="Route Name" required autocomplete="off" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}"/>
						
						<span class="text-danger">@error('route_name') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
					</div>
					<div class="col-sm-3">
						Permission Type <label id="req">*</label>
						<select class="form-control" name="permission_type" id="permission_type" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}">
							<option value="ACTION" @if($data->permission_type=='ACTION') selected @endif>ACTION</option>
							<option value="AJAX" @if($data->permission_type=='AJAX') selected @endif>AJAX</option>
							<option value="LINK" @if($data->permission_type=='LINK') selected @endif>LINK</option>
						</select>
						
						<span class="text-danger">@error('route_name') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
					</div>
					<div class="col-sm-1">
						Order <label id="req">*</label>
						<input type="text" class="form-control" name="display_order" id="display_order" value="{{old('display_order',$data->display_order)}}" placeholder="0" required autocomplete="off" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}"/>
						
						<span class="text-danger">@error('display_order') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
					</div>
					<div class="col-sm-2">
						<button type="submit" class="btn btn-info myfrmbtn mt-25" tabindex="{{$t++}}">SUBMIT</button>
					</div>
				</div>		
			</form>
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
<script src="{{ asset('panel/assets/js/datatablePaginationWithFilter.js') }}"></script>
@endsection