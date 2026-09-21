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
								@if(in_array(1, Session::get('actions')))
									<li class="active"><a data-toggle="tab" href="#home"><i
												class="green ace-icon fa fa-refresh bigger-120"
												style="vertical-align:bottom;"></i> REQUEST APPROVAL / REJECTION</a></li>
								@endif
							</ul>


							<div class="tab-content no-border"
								style="border:1px solid #ddd; min-height:100px; padding:0px 10px;">
								@if(in_array(1, Session::get('actions')))
									<div id="home" class="tab-pane in active" style="padding:0px 10px!important;">
										<div class="row">
											<div class="row">
												<form name="frm" id="frm"
													action="{{ route('approval.departmentrequest', $data->departmentid)}}"
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
															NAME <label id="req">*</label>
															<input type="hidden" name="form_token" id="form_token"
																value="{{$token}}">
															<input type="hidden" name="action_type" id="action_type" value="">
															<input type="text" class="form-control" name="name" id="name"
																value="{{old('name', $user->name)}}" placeholder="NAME" required
																autocomplete="off" onKeyPress="return OnKeyPress(this, event)"
																tabindex="{{$t++}}" />

															<span class="text-danger">@error('name') <i
																class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i>
															@enderror</span>
														</div>

														<div class="col-sm-3">
															MOBILE NUMBER <label id="req">(login id)*</label>
															<input type="number" class="form-control numbers"
																name="mobilenumber" id="mobilenumber"
																value="{{old('mobilenumber', $user->mobilenumber)}}"
																placeholder="MOBILE NUMBER" required autocomplete="off"
																onKeyPress="return OnKeyPress(this, event)"
																tabindex="{{$t++}}" />

															<span class="text-danger">@error('mobilenumber') <i
																class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i>
															@enderror</span>
														</div>
														<div class="col-sm-3">
															EMAIL ID <label id="req">(login id)*</label>
															<input type="text" class="form-control" name="email" id="email"
																value="{{old('email', $user->email)}}" placeholder="EMAIL"
																required autocomplete="off"
																onKeyPress="return OnKeyPress(this, event)"
																tabindex="{{$t++}}" />

															<span class="text-danger">@error('email') <i
																class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i>
															@enderror</span>
														</div>

														<div class="col-sm-3">
															DEPARTMENT NAME <label id="req">*</label>
															<input type="text" class="form-control" name="departmentname"
																id="departmentname"
																value="{{old('departmentname', $data->departmentname)}}"
																placeholder="DEPARTMENT NAME" required autocomplete="off"
																onKeyPress="return OnKeyPress(this, event)"
																tabindex="{{$t++}}" />

															<span class="text-danger">@error('departmentname') <i
																class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i>
															@enderror</span>
														</div>
														<div class="col-sm-12">&nbsp;</div>
														<div class="col-sm-3">
															SHORT NAME <label id="req">*</label>
															<input type="text" class="form-control" name="shortname"
																id="shortname" value="{{old('shortname', $data->shortname)}}"
																placeholder="SHORT NAME" required autocomplete="off"
																onKeyPress="return OnKeyPress(this, event)"
																tabindex="{{$t++}}" />

															<span class="text-danger">@error('shortname') <i
																class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i>
															@enderror</span>
														</div>
														<div class="col-sm-9">
															ADDRESS <label id="req">&nbsp;</label>
															<input type="text" class="form-control" name="address" id="address"
																value="{{old('address', $data->address)}}" placeholder="ADDRESS"
																autocomplete="off" onKeyPress="return OnKeyPress(this, event)"
																tabindex="{{$t++}}" />

															<span class="text-danger">@error('address') <i
																class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i>
															@enderror</span>
														</div>

														<div class="col-sm-12">&nbsp;</div>
														<div class="col-sm-12">
															APPROVAL / REJECTION REMARK <label id="req">*</label>
															<input type="text" class="form-control" name="remark" id="remark"
																value="{{old('remark', $data->remark)}}" placeholder="REMARK"
																required autocomplete="off"
																onKeyPress="return OnKeyPress(this, event)"
																tabindex="{{$t++}}" />

															<span class="text-danger">@error('remark') <i
																class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i>
															@enderror</span>
														</div>
														<div class="col-sm-12">&nbsp;</div>
														<div class="col-sm-2" align="right">
															<button type="submit" id="approveBtn" class="btn btn-info myfrmbtn"
																tabindex="{{$t++}}"><i class="fa fa-check"></i> APPROVE</button>
														</div>
														<div class="col-sm-8" align="right"></div>
														<div class="col-sm-2" align="right">
															<button type="submit" id="rejectBtn" class="btn btn-info myfrmbtn"
																style="background-color:orange!important; border-color:orange!important; outline:none!important;"
																tabindex="{{$t++}}"><i class="fa fa-remove"></i> REJECT</button>
														</div>

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
	<script>
		$(document).ready(function () {
			let clickedButton = '';

			$('#approveBtn').click(function (e) {
				e.preventDefault();
				clickedButton = 'approve';
				$('#action_type').val('1');
				validateAndConfirm();
			});

			$('#rejectBtn').click(function (e) {
				e.preventDefault();
				clickedButton = 'reject';
				$('#action_type').val('-1');
				validateAndConfirm();
			});

			function validateAndConfirm() {
				let isValid = true;

				$('[required]').css('border-color', '');

				$('[required]').each(function () {
					if ($.trim($(this).val()) === '') {
						isValid = false;
						$(this).css('border-color', 'red');
					}
				});

				if (!isValid) {
					bootbox.alert('All fields marked with * are mandatory.');
					return;
				}

				// Show confirmation message
				let confirmMsg = clickedButton === 'approve' ?
					'Are you sure you want to <b>APPROVE</b> this request?' :
					'Are you sure you want to <b>REJECT</b> this request?';

				bootbox.confirm('' + confirmMsg, function (result) {
					if (result) {
						$('form').off('submit').submit(); // Unbind and submit
					}
				});
				/*
				if (confirm(confirmMsg)) {
					$('form').off('submit').submit(); // Unbind and submit
				}
				*/
			}
		});
	</script>
	<script src="{{ asset('panel/assets/js/datatablePaginationWithFilter.js') }}"></script>
@endsection