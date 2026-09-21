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
												class="green ace-icon fa fa-plus-circle bigger-120"
												style="vertical-align:bottom;"></i> Add Tier Wise Charges</a></li>
								@endif
								@if(in_array(2, Session::get('actions')) || in_array(3, Session::get('actions')) || in_array(12, Session::get('actions')))
									<li @if(!in_array(1, Session::get('actions'))) class="active" @endif><a data-toggle="tab"
											href="#recordlist" onclick="loadData(1,'{{ route('charges.html') }}')"><i
												class="green ace-icon fa fa-list bigger-120 datalist"
												style="vertical-align:bottom;"></i> Tier Wise Charges List</a></li>
								@endif
							</ul>


							<div class="tab-content no-border"
								style="border:1px solid #ddd; min-height:100px; padding:0px 10px;">
								@if(in_array(1, Session::get('actions')))
									<div id="home" class="tab-pane in active" style="padding:0px 10px!important;">
										<div class="row">
											<div class="row">
												<form name="frm" id="frm" action="{{ route('store.charges', 0)}}" method="post"
													enctype="multipart/form-data">
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
														<div class="col-sm-4">
															Category <label id="req">*</label>
															<input type="hidden" name="form_token" id="form_token"
																value="{{$token}}">
															<select class="chosen-select form-control" name="categoryid"
																id="categoryid" autofocus required
																onKeyPress="return OnKeyPress(this, event)"
																data-placeholder="Category"
																onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}"
																onchange="SetOM(this.value)">
																<option value=""></option>
																@foreach ($category as $itm)
																	<option value="{{ $itm->categoryid }}" {{ intval(old('categoryid')) === $itm->categoryid ? 'selected' : '' }}>{{ strtoupper($itm->jobcategory) }}</option>
																@endforeach
															</select>

															<span class="text-danger">@error('categoryid') <i
																class="fa fa-hand-o-right"> {{ strtoupper($message) }} </i>
															@enderror</span>
														</div>

														<div class="col-sm-4">
															Tier <label id="req">*</label>
															<select class="chosen-select form-control" name="tierid" id="tierid"
																autofocus required onKeyPress="return OnKeyPress(this, event)"
																data-placeholder="Tier"
																onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}">
																<option value=""></option>
																@foreach ($tier as $itm)
																	<option value="{{ $itm->tierid }}" {{ intval(old('tierid')) === $itm->tierid ? 'selected' : '' }}>
																		{{ strtoupper($itm->tiername) }}
																	</option>
																@endforeach
															</select>

															<span class="text-danger">@error('tierid') <i
																class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i>
															@enderror</span>
														</div>
														<div class="col-sm-4 operating">
															Operating Margin <label class="om" id="req">*</label>
															<input type="text" class="form-control numbers"
																name="operatingmargin" id="operatingmargin"
																value="{{old('operatingmargin')}}"
																placeholder="Operating Margin" required autocomplete="off"
																onKeyPress="return OnKeyPress(this, event)"
																tabindex="{{$t++}}" />

															<span class="text-danger">@error('operatingmargin') <i
																class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i>
															@enderror</span>
														</div>
														<div class="col-sm-4">
															Tax (In %)<label id="req">*</label>
															<input type="text" class="form-control numbers" name="tax" id="tax"
																value="{{old('tax')}}" placeholder="Tax (In %)" required
																autocomplete="off" onKeyPress="return OnKeyPress(this, event)"
																tabindex="{{$t++}}" />

															<span class="text-danger">@error('tax') <i
																class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i>
															@enderror</span>
														</div>
														<div class="col-sm-4">
															Admin Charges (In %)<label id="req">*</label>
															<input type="text" class="form-control numbers" name="admincharge"
																id="admincharge" value="{{old('admincharge')}}"
																placeholder="(In %)" required autocomplete="off"
																onKeyPress="return OnKeyPress(this, event)"
																tabindex="{{$t++}}" />

															<span class="text-danger">@error('admincharge') <i
																class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i>
															@enderror</span>
														</div>
														<!--
														<div class="col-sm-2">
															<button type="submit" class="btn btn-info myfrmbtn mt-25"
																tabindex="{{$t++}}">SUBMIT</button>
														</div>
														-->
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
														<table class="table table-bordered table-striped table-hover pd-8"
															id="tablerecords">
															<thead>
																<tr>
																	<td colspan="8">
																		<div id="tablehead">
																			<select name="pagesize" id="pagesize"
																				class="select2"
																				onchange="loadData(1,'{{ route('charges.html') }}')">
																				<option value="15">15</option>
																				<option value="100">100</option>
																				<option value="300">300</option>
																				<option value="500">500</option>
																			</select>
																			<select class="select2" name="catid" id="catid"
																				onKeyPress="return OnKeyPress(this, event)"
																				data-placeholder="CATEGORY NAME"
																				onchange="loadData(1,'{{ route('charges.html') }}')">
																				<option value=""></option>
																				@foreach ($category as $itm)
																					<option value="{{ $itm->categoryid }}">
																						{{ strtoupper($itm->jobcategory) }}
																					</option>
																				@endforeach
																			</select>
																			<select class="select2" name="tid" id="tid"
																				onKeyPress="return OnKeyPress(this, event)"
																				data-placeholder="TIER NAME"
																				onchange="loadData(1,'{{ route('charges.html') }}')">
																				<option value=""></option>
																				@foreach ($tier as $itm)
																					<option value="{{ $itm->tierid }}">
																						{{ strtoupper($itm->tiername) }}
																					</option>
																				@endforeach
																			</select>

																			<span class="input-icon" style="float:right;">
																				<input type="text" placeholder="Search ..."
																					class="nav-search-input" id="pagesearch"
																					name="pagesearch" autocomplete="off"
																					onchange="loadData(1,'{{ route('charges.html') }}')"
																					tabindex="<?php echo $t++;?>" />
																				<i class="ace-icon fa fa-search nav-search-icon"
																					style="margin-top:-3px;"></i>
																			</span>
																		</div>
																	</td>
																</tr>
																<tr class="myhead">
																	<td style="width:25px;" nowrap><b>S.No.</b></td>
																	<td nowrap style=""><b>Category</b></td>
																	<td nowrap style=""><b>Tier</b></td>
																	<td nowrap style=""><b>Operating Margin (%)</b></td>
																	<td nowrap style=""><b>Tax (%)</b></td>
																	<td nowrap style=""><b>Admin Charge (%)</b></td>
																	<td style="width:25px;"></td>
																	<td style="width:25px;"></td>
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
			function SetOM(val) {
				if (val == 1) {
					$(".operating").css("display", "");
					$(".om").html("*");
					$("#operatingmargin").prop("required", "required");
				}
				else {
					$("#operatingmargin").prop("required", "");
					$(".om").html("");
					$(".operating").css("display", "none");
				}
			}
		function loadData(page, r1) {
			var pagesize = document.getElementById("pagesize").value;
			var pagesearch = document.getElementById("pagesearch").value;
			var categoryid = document.getElementById("catid").value;
			var tierid = document.getElementById("tid").value;
			$.get("" + r1,
				{
					page: page,
					pagesize: pagesize,
					pagesearch: pagesearch,
					categoryid: categoryid,
					tierid: tierid
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