@extends('admin.admin_master')
@section('admin')
@php
$t = 1;
@endphp
@php
    $canAutoOpen = app(\App\Services\PermissionService::class)->hasPermission('departmentrequest.html') && !app(\App\Services\PermissionService::class)->hasPermission('store.departmentrequest');
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
@permission('store.departmentrequest')
	<li class="active">
		<a data-toggle="tab" href="#home">
			<i class="green ace-icon fa fa-plus-circle bigger-120" style="vertical-align:bottom;"></i> Add Department
		</a>
	</li>
@endpermission
@permission('departmentrequest.html')
	<li @if($canAutoOpen) class="active" @endif>
		<a data-toggle="tab" href="#recordlist" onclick="loadData(1,'{{ route('departmentrequest.html') }}')">
			<i class="green ace-icon fa fa-list bigger-120 datalist" style="vertical-align:bottom;"></i> Department List
		</a>
	</li>
@endpermission			
</ul>


							<div class="tab-content no-border"
								style="border:1px solid #ddd; min-height:100px; padding:0px 10px;">
								@permission('store.departmentrequest')
									<div id="home" class="tab-pane in active" style="padding:0px 10px!important;">
										<div class="row">
											<div class="row">
												<form name="frm" id="frm" action="{{ route('store.departmentrequest', 0)}}"
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
															<label class="field-label">
																Department Name <span class="req">*</span>
															</label>
															<input type="hidden" name="form_token" id="form_token" value="{{$token}}">
															<input type="text" class="form-control" name="departmentname"
																id="departmentname" value="{{old('departmentname')}}"
																placeholder="Department Name" required autocomplete="off"
																onKeyPress="return OnKeyPress(this, event)"
																tabindex="{{$t++}}" />

															<span class="text-danger">@error('departmentname') <i
																class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i>
															@enderror</span>
														</div>
														<div class="col-sm-3">
															<label class="field-label">
																Short Name <span class="req">*</span>
															</label>
															<input type="text" class="form-control" name="shortname"
																id="shortname" value="{{old('shortname')}}"
																placeholder="Short Name e.g. CHiPS" autocomplete="off"
																onKeyPress="return OnKeyPress(this, event)"
																tabindex="{{$t++}}" />

															<span class="text-danger">@error('shortname') <i
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
														<div class="col-sm-3">
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

														<div class="col-sm-12">&nbsp;</div>
														<div class="col-sm-3">
															<label class="field-label">
																Official Email <span class="req">&nbsp;</span>
															</label>
															<input type="text" class="form-control" name="officialemail"
																id="officialemail" value="{{old('officialemail')}}"
																placeholder="Official email address" autocomplete="off"
																onKeyPress="return OnKeyPress(this, event)"
																tabindex="{{$t++}}" />

															<span class="text-danger">@error('officialemail') <i
																class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i>
															@enderror</span>
														</div>

														<div class="col-sm-9">
															<label class="field-label">
																Address <span class="req">&nbsp;</span>
															</label>
															<input type="text" class="form-control" name="address" id="address"
																value="{{old('address')}}"
																placeholder="Enter department address" autocomplete="off"
																onKeyPress="return OnKeyPress(this, event)"
																tabindex="{{$t++}}" />

															<span class="text-danger">@error('address') <i
																class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i>
															@enderror</span>
														</div>
														<div class="col-sm-12">&nbsp;</div>
														<div class="col-sm-12" align="left">
												@permission('store.departmentrequest')
												<button type="submit" class="btn btn-info myfrmbtn width-100" tabindex="{{$t++}}">Submit</button>
												@endpermission
														</div>
													</div>
												</form>
											</div>
										</div>
									</div>
								@endpermission
								@permission('departmentrequest.html')
									<div id="recordlist"
										class="tab-pane @if($canAutoOpen) in active @endif"
										style="padding:0px 0px!important;">
										<div class="row" style="padding:0px;">
											<div class="col-xs-12">
												<form name="pagedata" id="pagedata" action="#" method="post"
													onsubmit="return false;">
													<div class="table-responsive">
														<table class="table table-bordered table-striped table-hover pd-8"
															id="tablerecords">
															<thead>
																<tr>
																	<td colspan="12">
																		<div id="tablehead">
																			<select name="pagesize" id="pagesize"
																				class="select2 selectbx"
																				onchange="loadData(1,'{{ route('departmentrequest.html') }}')"
																				data-width="70" data-placeholder="RECORDS">

																				<option value="100">100</option>
																				<option value="300">300</option>
																				<option value="500">500</option>
																			</select>

																			<span class="input-icon">
																				<input type="text"
																					placeholder="Search department and press enter"
																					class="nav-search-input selectbx"
																					id="pagesearch" name="pagesearch"
																					autocomplete="off"
																					onchange="loadData(1,'{{ route('departmentrequest.html') }}')"
																					tabindex="<?php echo $t++;?>" />
																				<i class="ace-icon fa fa-search nav-search-icon"
																					style="margin-top:-3px;"></i>
																			</span>
																		</div>
																	</td>
																</tr>
																<tr class="myhead">
																	<td style="width:25px;" nowrap><b>S.No.</b></td>
																	<td nowrap style=""><b>Name & Official Email</b></td>
																	<td nowrap style=""><b>Mobile Number</b></td>
																	<td nowrap><b>Credentials</b></td>
																	<td nowrap style=""><b>Address</b></td>
																	<td style="width:25px;"></td>
																</tr>
															</thead>
															<tbody class="tabledata">
																<tr>
																	<td colspan="5" class="center">--Search Record--</td>
																</tr>
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
		@if($canAutoOpen)
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