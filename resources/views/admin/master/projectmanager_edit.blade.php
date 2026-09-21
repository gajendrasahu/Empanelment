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
@permission('update.projectmanager')
	<li class="active">
		<a data-toggle="tab" href="#home">
			<i class="green ace-icon fa fa-plus-circle bigger-120" style="vertical-align:bottom;"></i> Update Project Manager Detai
		</a>
	</li>
@endpermission	
</ul>

	
<div class="tab-content no-border" style="border:1px solid #ddd; min-height:100px; padding:0px 10px;">
@permission('update.projectmanager')
<div id="home" class="tab-pane in active" style="padding:0px 10px!important;">
	<div class="row">
		<div class="row">
			<form name="frm" id="frm" action="{{ route('update.projectmanager',$data->departmentid)}}" method="post" enctype="multipart/form-data">
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

					<div class="col-sm-6">
						Name <label id="req">*</label>
						<input type="hidden" name="form_token" id="form_token" value="{{$token}}">
						<input type="text" class="form-control" name="name" id="name" value="{{old('name',$user->name)}}" placeholder="Name" required autocomplete="off" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}"/>

						<span class="text-danger">@error('name') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
					</div>

					<div class="col-sm-3">
						Mobile Number <label id="req">(login id)*</label>
						<input type="text" class="form-control numbers" name="mobilenumber" id="mobilenumber" value="{{old('mobilenumber',$user->mobilenumber)}}" placeholder="Mobile Number" required autocomplete="off" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}"/>

						<span class="text-danger">@error('mobilenumber') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
					</div>
					<div class="col-sm-3">
						Email <label id="req">(login id)*</label>
						<input type="text" class="form-control" name="email" id="email" value="{{old('email',$user->email)}}" placeholder="Email" required autocomplete="off" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}"/>

						<span class="text-danger">@error('email') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
					</div>

					<div class="col-sm-12">&nbsp;</div>

					<div class="col-sm-3">
						Official Email <label id="req">&nbsp;</label>
						<input type="text" class="form-control" name="officialemail" id="officialemail"
							value="{{old('officialemail', $data->officialemail)}}" placeholder="Official email"
							 autocomplete="off"
							onKeyPress="return OnKeyPress(this, event)"
							tabindex="{{$t++}}" />

						<span class="text-danger">@error('officialemail') <i
							class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i>
						@enderror</span>
					</div>
					
					<div class="col-sm-9">
						Address <label id="req">&nbsp;</label>
						<input type="text" class="form-control" name="address" id="address" value="{{old('address',$data->address)}}" placeholder="Address" autocomplete="off" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}"/>

						<span class="text-danger">@error('address') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
					</div>
					<div class="col-sm-12">&nbsp;</div>
					<div class="col-sm-10">&nbsp;</div>
					<div class="col-sm-2">
					@permission('update.projectmanager')
					<button type="submit" class="btn btn-info" style="width:100%;" tabindex="{{$t++}}">Update</button>
					@endpermission
					</div>
					<div class="col-sm-12">&nbsp;</div>
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
<script src="{{ asset('panel/assets/js/datatablePaginationWithFilter.js') }}"></script>
@endsection