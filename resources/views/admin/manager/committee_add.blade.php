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
								@if(in_array(1, Session::get('actions')))
									<li class="active">
										<a data-toggle="tab" class="form-label" href="#home">
											<i class="green ace-icon fa fa-plus-circle bigger-120 li-icons"
												style="vertical-align:bottom;"></i>Add Committee Member
										</a>
									</li>
								@endif
								@if(in_array(2, Session::get('actions')) || in_array(3, Session::get('actions')) || in_array(12, Session::get('actions')))
									<li @if(!in_array(1, Session::get('actions'))) class="active" @endif>
										<a data-toggle="tab" class="form-label" href="#recordlist"
											onclick="loadData(1,'{{ route('pmcommittee.html') }}')">
											<i class="green ace-icon fa fa-list bigger-120 datalist"
												style="vertical-align:bottom;"></i> Committee Member List
										</a>
									</li>
								@endif
							</ul>


							<div class="tab-content no-border"
								style="border:1px solid #ddd; min-height:100px; padding:0px 10px;">
								@if(in_array(1, Session::get('actions')))
									<div id="home" class="tab-pane in active" style="padding:0px 10px!important;">
										<div class="row">
											<div class="row">
												<form name="frm" id="frm" action="{{ route('store.pmcommittee', 0)}}"
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
													@csrf
													<div class="form-group">

														<div class="col-sm-3">
															Name <label id="req">*</label>
															<input type="hidden" name="form_token" id="form_token"
																value="{{$token}}">
															<input type="text" class="form-control" name="name" id="name"
																value="{{old('name')}}" placeholder="Name" required
																autocomplete="off" onKeyPress="return OnKeyPress(this, event)"
																tabindex="{{$t++}}" />

															<span class="text-danger">@error('name') <i
																class="fa fa-hand-o-right"> {{ $message }}</i>
															@enderror</span>
														</div>
														<div class="col-sm-3">
															Designation <label id="req">*</label>
															<input type="text" class="form-control" required name="designation"
																id="designation" value="{{old('designation')}}"
																placeholder="Designation" autocomplete="off"
																onKeyPress="return OnKeyPress(this, event)"
																tabindex="{{$t++}}" />

															<span class="text-danger">@error('designation') <i
																class="fa fa-hand-o-right"> {{ $message }}</i>
															@enderror</span>
														</div>
														<div class="col-sm-3">
															Department <label id="req">&nbsp;</label>
															<input type="text" class="form-control" name="department"
																id="department" value="{{old('department')}}"
																placeholder="Department name.." autocomplete="off"
																onKeyPress="return OnKeyPress(this, event)"
																tabindex="{{$t++}}" />

															<span class="text-danger">@error('department') <i
																class="fa fa-hand-o-right"> {{ $message }}</i>
															@enderror</span>
														</div>

														<div class="col-sm-3">
															Mobile Number <label id="req">&nbsp;</label>
															<input type="text" class="form-control" name="mobilenumber"
																id="mobilenumber" value="{{old('mobilenumber')}}"
																placeholder="Mobile Number" autocomplete="off"
																onKeyPress="return OnKeyPress(this, event)"
																tabindex="{{$t++}}" />

															<span class="text-danger">@error('mobilenumber') <i
																class="fa fa-hand-o-right"> {{ $message }}</i>
															@enderror</span>
														</div>
														<div class="col-sm-12">&nbsp;</div>
														<div class="col-sm-3">
															Email <label id="req">*</label>
															<input type="text" class="form-control" name="email" required
																id="email" value="{{old('email')}}" placeholder="Email"
																autocomplete="off" onKeyPress="return OnKeyPress(this, event)"
																tabindex="{{$t++}}" />

															<span class="text-danger">@error('email') <i
																class="fa fa-hand-o-right"> {{ $message }}</i>
															@enderror</span>
														</div>
														<div class="col-sm-2 float-btn">

															<button type="submit" class="btn btn-info myfrmbtn"
																tabindex="{{$t++}}">Submit</button>
														</div>
														<div class="col-sm-12">&nbsp;</div>
													</div>
												</form>
											</div>
										</div>
									</div>
								@endif
								@if(in_array(2, Session::get('actions')) || in_array(3, Session::get('actions')) || in_array(12, Session::get('actions')))
									<div id="recordlist"
										class="tab-pane @if(!in_array(1, Session::get('actions'))) in active @endif"
										style="padding:0px 0px!important;">
										<div class="row" style="padding:0px;">
											<div class="col-xs-12">
												<form name="pagedata" id="pagedata" action="#" method="post"
													onsubmit="return false;">
													<div class="table-responsive">
														<table class="table table-bordered table-striped table-hover"
															id="tablerecords">
															<thead>
																<tr>
																	<td class="padding-5" colspan="8">
																		<select name="pagesize" id="pagesize" class="select2"
																			onchange="loadData(1,'{{ route('pmcommittee.html') }}')" data-width="90">
																			<option value="15">15</option>
																			<option value="100">100</option>
																			<option value="300">300</option>
																			<option value="500">500</option>
																		</select>
																		<span class="input-icon" style="float:right;">
																			<input type="text" placeholder="Search ..."
																				class="nav-search-input" id="pagesearch"
																				name="pagesearch" autocomplete="off"
																				onchange="loadData(1,'{{ route('pmcommittee.html') }}')"
																				tabindex="<?php echo $t++;?>" />
																			<i class="ace-icon fa fa-search nav-search-icon"
																				style="margin-top:-3px;"></i>
																		</span>
																	</td>
																</tr>
																<tr>
																	<td class="padding-10" style="width:70px!important;" nowrap><b>S. No.</b></td>
																	<td class="padding-10" nowrap style=""><b>Name</b></td>
																	<td class="padding-10" nowrap style=""><b>Designation</b>
																	</td>
																	<td class="padding-10" nowrap style=""><b>Department</b>
																	</td>
																	<td class="padding-10" nowrap style=""><b>Mobile Number</b>
																	</td>
																	<td class="padding-10" nowrap style=""><b>Email</b></td>
																	<td class="padding-10" style="width:25px;"></td>
																	<td class="padding-10" style="width:25px;"></td>
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
		@if(!in_array(1, Session::get('actions')))
			setTimeout(function () { $('.datalist').trigger('click'); }, 1000);
		@endif


		function loadData(page, r1) {

			var pagesize = document.getElementById("pagesize").value;
			var pagesearch = document.getElementById("pagesearch").value;
			$.get("" + r1,
				{
					page: page,
					pagesize: pagesize,
					pagesearch: pagesearch,
				},
				function (data, status) {
					$(".tabledata").html(data);
					$(".mytr").click(function () {
						$(".mytr").removeClass("selected-color").addClass("reset-color");
						$(this).removeClass("reset-color").addClass("selected-color");
					});
				});
		}

	</script>

	<script src="{{ asset('panel/assets/js/datatablePaginationWithFilter.js') }}"></script>
@endsection