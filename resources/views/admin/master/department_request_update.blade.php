@extends('admin.admin_master')
@section('admin')
	@php
		$t = 1;
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
								@permission('update.departmentrequest')
									<li class="active"><a data-toggle="tab" href="#home"><i
												class="green ace-icon fa fa-plus-circle bigger-120"
												style="vertical-align:bottom;"></i> Update Department</a></li>
								@endpermission
							</ul>


							<div class="tab-content no-border"
								style="border:1px solid #ddd; min-height:100px; padding:0px 10px;">
								@if(in_array(1, Session::get('actions')))
									<div id="home" class="tab-pane in active" style="padding:0px 10px!important;">
										<div class="row">
											<div class="row">
												<form name="frm" id="frm"
													action="{{ route('update.departmentrequest', $data->departmentid)}}"
													method="post" enctype="multipart/form-data">
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
														<div class="alert alert-danger">
															<strong>There were some problems with your input:</strong>
															<ul>
																@foreach ($errors->all() as $error)
																	<li>{{ $error }}</li>
																@endforeach
															</ul>
														</div>
													@endif
													@csrf
													<div class="form-group">

														<div class="col-sm-3">
															Department Name <label id="req">*</label>
															<input type="hidden" name="form_token" id="form_token"
																value="{{$token}}">
															<input type="text" class="form-control" name="departmentname"
																id="departmentname"
																value="{{old('departmentname', $data->departmentname)}}"
																placeholder="Department Name" required autocomplete="off"
																onKeyPress="return OnKeyPress(this, event)"
																tabindex="{{$t++}}" />

															<span class="text-danger">@error('departmentname') <i
																class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i>
															@enderror</span>
														</div>
														<div class="col-sm-3">
															Short Name <label id="req">*</label>
															<input type="text" class="form-control" name="shortname"
																id="shortname" value="{{old('shortname', $data->shortname)}}"
																placeholder="Short Name" required autocomplete="off"
																onKeyPress="return OnKeyPress(this, event)"
																tabindex="{{$t++}}" />

															<span class="text-danger">@error('shortname') <i
																class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i>
															@enderror</span>
														</div>

														<div class="col-sm-3">
															Mobile Number <label id="req">*</label>
															<input type="number" class="form-control numbers" required
																name="mobilenumber" id="mobilenumber"
																value="{{old('mobilenumber', $user->mobilenumber)}}"
																placeholder="Mobile Number" autocomplete="off"
																onKeyPress="return OnKeyPress(this, event)"
																tabindex="{{$t++}}" />

															<span class="text-danger">@error('mobilenumber') <i
																class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i>
															@enderror</span>
														</div>
														<div class="col-sm-3">
															Email <label id="req">(login id)*</label>
															<input type="text" class="form-control" name="email" id="email"
																value="{{old('email', $user->email)}}" placeholder="Email"
																required autocomplete="off"
																onKeyPress="return OnKeyPress(this, event)"
																tabindex="{{$t++}}" />

															<span class="text-danger">@error('email') <i
																class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i>
															@enderror</span>
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
															<input type="text" class="form-control" name="address" id="address"
																value="{{old('address', $data->address)}}" placeholder="Address"
																autocomplete="off" onKeyPress="return OnKeyPress(this, event)"
																tabindex="{{$t++}}" />

															<span class="text-danger">@error('address') <i
																class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i>
															@enderror</span>
														</div>
														<div class="col-sm-12">&nbsp;</div>
														<div class="col-sm-10">&nbsp;</div>
														<div class="col-sm-2">
														@permission('update.departmentrequest')
															<button type="submit" class="btn btn-info" style="width:100%;"
																tabindex="{{$t++}}">Update</button>
														@endpermission
														</div>
														<div class="col-sm-12">&nbsp;</div>
													</div>
												</form>
											</div>
										</div>
									</div>
								@endif


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