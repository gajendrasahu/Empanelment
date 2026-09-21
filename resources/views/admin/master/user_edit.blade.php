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
		@permission('update.user')
			<li class="active">
				<a data-toggle="tab" href="#home">
					<i class="green ace-icon fa fa-plus-circle bigger-120" style="vertical-align:bottom;"></i> {{__('labels.updateusertab')}}
				</a>
			</li>
		@endif
		</ul>

	
		<div class="tab-content no-border" style="border:1px solid #ddd; padding:0px 10px;">
@permission('update.user')
<div id="home" class="tab-pane in active" style="padding:0px 11px!important;">
	<div class="row">
		<div class="row">
			<form name="frm" id="frm" action="{{ route('update.user',$data->userid)}}" method="post" enctype="multipart/form-data">
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
							value="{{old('name',$data->name)}}" placeholder="Name" required
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
							value="{{old('mobilenumber',$data->mobilenumber)}}"
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
							value="{{old('email',$data->email)}}" placeholder="Enter email address"
							required autocomplete="off"
							onKeyPress="return OnKeyPress(this, event)"
							tabindex="{{$t++}}" />

						<span class="text-danger">@error('email') <i
							class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i>
						@enderror</span>
					</div>
					<div class="col-sm-2">
						Role <label id="req">*</label>
						<select class="form-control" name="roleid" id="roleid" required onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}">
							<option value="">--Role--</option>
							@foreach($roles as $itm)
							<option value="{{$itm->roleid}}" @if($itm->roleid==old('roleid',$data->roleid)) selected @endif>{{$itm->role}}</option>
							@endforeach
						</select>

						<span class="text-danger">@error('gender') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }} </i> @enderror</span>
					</div>
					
					<div class="col-sm-12">&nbsp;</div>
					<div class="col-sm-12" align="right">
					@permission('update.user')
						<button type="submit" class="btn btn-info myfrmbtn width-100"
							tabindex="{{$t++}}">Submit</button>
					@endpermission
					</div>
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
@endsection