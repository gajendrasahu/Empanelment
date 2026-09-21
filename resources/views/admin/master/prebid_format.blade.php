@extends('admin.admin_master')
@section('admin')
	@php
		$t = 0;
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
@permission('store.format')
<li class="active">
	<a data-toggle="tab" href="#home">
		<i class="green ace-icon fa fa-plus-circle bigger-120" style="vertical-align:bottom;"></i>
		ADD PRE BID QUERY / RESPONSE FORMAT
	</a>
</li>
@endpermission
								
							</ul>


							<div class="tab-content no-border"
								style="border:1px solid #ddd; min-height:100px; padding:0px 10px;">
								@permission('store.format')
									<div id="home" class="tab-pane in active" style="padding:0px 10px!important;">
										<div class="row">
											<div class="row">
												<form name="frm" id="frm" action="{{ route('store.format', 0)}}" method="post"
													enctype="multipart/form-data">
													@if(Session::has('success'))
														<div class="col-sm-12">
															<div class="alert alert-block alert-success">
																<button type="button" class="close" data-dismiss="alert">
																	<i class="ace-icon fa fa-times"></i>
																</button>
																<i class="fa fa-success"></i> {{ Session::get('success') }}
															</div>
														</div>
													@endif
													@if(Session::has('fail'))
														<div class="col-sm-12">
															<div class="alert alert-block alert-danger">
																<button type="button" class="close" data-dismiss="alert">
																	<i class="ace-icon fa fa-times"></i>
																</button>
																<i class="fa fa-warning"></i> {{ Session::get('fail') }}
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
													@if($errors->any())
														<br>
														<div class="col-sm-12">
															<div class="alert alert-block alert-danger">
																<button type="button" class="close" data-dismiss="alert">
																	<i class="ace-icon fa fa-times"></i>
																</button>
																<ul>
																	@foreach ($errors->all() as $error)
																		<li>{{ $error }}</li>
																	@endforeach
																</ul>
															</div>
														</div>
													@endif

													@csrf
													<div class="form-group">

														<div class="col-sm-4">
															Query Format <label id="req">(vendors)*</label>
															<input type="hidden" name="form_token" id="form_token"
																value="{{$token}}">
															<input type="file" class="form-control" name="queryformat"
																id="queryformat" accept=".pdf"
																onKeyPress="return OnKeyPress(this, event)"
																tabindex="{{$t++}}" />
															<span class="text-danger">@error('queryformat') <i
																class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i>
															@enderror</span>

														</div>
														<div class="col-sm-4">
															Response Format <label id="req">(Department)*</label>
															<input type="hidden" name="form_token" id="form_token"
																value="{{$token}}">
															<input type="file" class="form-control" name="responseformat"
																id="responseformat" accept=".pdf"
																onKeyPress="return OnKeyPress(this, event)"
																tabindex="{{$t++}}" />
															<span class="text-danger">@error('responseformat') <i
																class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i>
															@enderror</span>

														</div>
														<div class="col-sm-2">
														@permission('store.format')
															<button type="submit" class="btn btn-info myfrmbtn mt-20" tabindex="{{$t++}}">UPDATE</button>
														@endpermission
														</div>


												</form>

											</div>
											<div class="col-sm-12">&nbsp;</div>
											<div class="col-sm-12">
												<div class="col-sm-4">
													@if($formats)
														@if($formats->queryformat != '')
															<a href="{{ route('view.uploadedfile', Crypt::encrypt($formats->queryformat)) }}"
																target="_blank">
																<button type="button" class="btn btn-info myfrmbtn"
																	tabindex="{{$t++}}"><i class="fa fa-file-pdf-o"></i>
																	View Pre-Bid Query Format</button>
															</a>
														@endif
													@endif
												</div>
												<div class="col-sm-4">
													@if($formats)
														@if($formats->responseformat != '')
															<a href="{{ route('view.uploadedfile', Crypt::encrypt($formats->responseformat)) }}"
																target="_blank">
																<button type="button" class="btn btn-info myfrmbtn"
																	tabindex="{{$t++}}"><i class="fa fa-file-pdf-o"></i>
																	View Pre-Bid Response Format</button>
															</a>
														@endif
													@endif
												</div>
												<div class="col-sm-12">&nbsp;</div>
											</div>
										</div>
									</div>
							</div>
							@endpermission
						</div>



					</div><!-- /.col -->
				</div><!-- /.row -->
			</div><!-- /.page-content -->


		</div>
	</div>
	<script src="{{ asset('panel/assets/js/jquery-2.1.4.min.js') }}"></script>
	<script src="{{ asset('panel/assets/js/ace-elements.min.js') }}"></script>
	<script src="{{ asset('panel/assets/js/ace.min.js') }}"></script>
	<script>
		$('#queryformat').ace_file_input({
			no_file: 'No File ...',
			btn_choose: 'Pre Bid Query Format',
			btn_change: 'Change',
			btn_name: 'btnname',
			thumbnail: false //| true | large
		})

		$('#responseformat').ace_file_input({
			no_file: 'No File ...',
			btn_choose: 'Pre Bid Response Format',
			btn_change: 'Change',
			btn_name: 'btnname',
			thumbnail: false //| true | large
		})
	</script>
	<script src="{{ asset('panel/assets/js/datatablePaginationWithFilter.js') }}"></script>
@endsection