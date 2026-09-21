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

@permission('vendors.html')
<li class="active">
	<a data-toggle="tab" href="#recordlist" onclick="loadData(1,'{{ route('vendors.html') }}')">
		<i class="green ace-icon fa fa-list bigger-120" style="vertical-align:bottom;"></i>
		Empanelled Firms
	</a>
</li>
@endpermission
@permission('store.firm')
<li>
	<a data-toggle="tab" href="#home" onclick="loadData(1,'{{ route('vendors.html') }}')">
	<i class="green ace-icon fa fa-building-o bigger-120" style="vertical-align:bottom;"></i> Add Firm</a>
</li>
@endpermission
@permission('store.email')
<li>
	<a data-toggle="tab" href="#addemail">
		<i class="green ace-icon fa fa-envelope bigger-120" style="vertical-align:bottom;"></i> Add Email
	</a>
</li>
@endpermission

							</ul>


							<div class="tab-content no-border" style="min-height:100px; padding: 0 10px;">
								@permission('store.firm')
								<div id="home" class="tab-pane">
										<div class="row">
											
												<form name="frm" id="frm" action="{{ route('store.firm', 0)}}" method="post"
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
														<div class="row col-sm-12">
															<div class="col-sm-4">
																<label class="field-label">
																	Firm Name <span id="req">*</span>
																</label>

																<input type="hidden" name="form_token" id="form_token"
																	value="{{$token}}">

																<input type="text" class="form-control" name="companyname"
																	id="companyname" value="{{old('companyname')}}"
																	placeholder="Enter firm name" required autocomplete="off"
																	onKeyPress="return OnKeyPress(this, event)"
																	tabindex="{{$t++}}" />

																<span class="text-danger">
																	@error('companyname')
																		<i class="fa fa-hand-o-right">{{ strtoupper($message) }}</i>
																	@enderror
																</span>
															</div>

															<div class="col-sm-4">
																<label class="field-label">
																	Firm Short Name <span id="req">*</span>
																</label>

																<input type="text" class="form-control" name="shortname"
																	id="shortname" value="{{old('shortname')}}"
																	placeholder="Enter firm's short name" autocomplete="off"
																	onKeyPress="return OnKeyPress(this, event)"
																	tabindex="{{$t++}}" />

																<span class="text-danger">
																	@error('shortname')
																		<i class="fa fa-hand-o-right">{{ strtoupper($message) }}</i>
																	@enderror
																</span>
															</div>

															<div class="col-sm-4">
																<label class="field-label">
																	Email <span id="req">(login id)*</span>
																</label>

																<input type="text" class="form-control" name="email" id="email"
																	value="{{old('email')}}" placeholder="Enter email address"
																	required autocomplete="off"
																	onKeyPress="return OnKeyPress(this, event)"
																	tabindex="{{$t++}}" />

																<span class="text-danger">
																	@error('email')
																		<i class="fa fa-hand-o-right">{{ strtoupper($message) }}</i>
																	@enderror
																</span>
															</div>
														</div>

														<div class="row col-sm-12">
															<div class="col-sm-4">
																<label class="field-label">
																	Mobile Number
																</label>

																<input type="number" class="form-control numbers"
																	name="mobilenumber" id="mobilenumber"
																	value="{{old('mobilenumber')}}"
																	placeholder="Enter mobile number" autocomplete="off"
																	onKeyPress="return OnKeyPress(this, event)"
																	tabindex="{{$t++}}" />

																<span class="text-danger">
																	@error('mobilenumber')
																		<i class="fa fa-hand-o-right">{{ strtoupper($message) }}</i>
																	@enderror
																</span>
															</div>

															<div class="col-sm-4">
																<label class="field-label">
																	LOI Number <span id="req">*</span>
																</label>

																<input type="text" class="form-control" name="loinumber"
																	id="loinumber" value="{{old('loinumber')}}"
																	placeholder="Enter LOI Number" required autocomplete="off"
																	onKeyPress="return OnKeyPress(this, event)"
																	tabindex="{{$t++}}" />

																<span class="text-danger">
																	@error('loinumber')
																		<i class="fa fa-hand-o-right">{{ strtoupper($message) }}</i>
																	@enderror
																</span>
															</div>

															<div class="col-sm-4">
																<label class="field-label">
																	Category <span id="req">*</span>
																</label>

																<select class="form-control" name="categoryid" id="categoryid"
																	autocomplete="off"
																	onKeyPress="return OnKeyPress(this, event)"
																	tabindex="{{$t++}}" onchange="DisplaySectors()">
																	<option value="">Select Firm Category</option>
																	@foreach($category as $cats)
																		<option value="{{$cats->categoryid}}"
																			@if($cats->categoryid == old('categoryid')) selected
																			@endif>
																			{{$cats->jobcategory}}
																		</option>
																	@endforeach
																</select>

																<span class="text-danger">
																	@error('categoryid')
																		<i class="fa fa-hand-o-right">{{ strtoupper($message) }}</i>
																	@enderror
																</span>
															</div>
														</div>

														<div class="row col-sm-12">
															<div class="col-sm-4">
																<label class="field-label">
																	Tier <span id="req">*</span>
																</label>

																<select class="form-control" name="tierid" id="tierid" required
																	autocomplete="off"
																	onKeyPress="return OnKeyPress(this, event)"
																	tabindex="{{$t++}}">
																	<option value="">Select Firm Tier</option>
																	@foreach($tier as $tiers)
																		<option value="{{$tiers->tierid}}"
																			@if($tiers->tierid == old('tierid')) selected @endif>
																			{{$tiers->tiername}}
																		</option>
																	@endforeach
																</select>

																<span class="text-danger">
																	@error('tierid')
																		<i class="fa fa-hand-o-right">{{ strtoupper($message) }}</i>
																	@enderror
																</span>
															</div>

															<div class="col-sm-8">
																<label class="field-label">
																	Office Address <span id="req">*</span>
																</label>

																<input type="text" class="form-control" name="address"
																	id="address" value="{{old('address')}}"
																	placeholder="Enter office address" required
																	autocomplete="off"
																	onKeyPress="return OnKeyPress(this, event)"
																	tabindex="{{$t++}}" />

																<span class="text-danger">
																	@error('address')
																		<i class="fa fa-hand-o-right">{{ strtoupper($message) }}</i>
																	@enderror
																</span>
															</div>
														</div>

														<div class="col-sm-6">
															<div class="sectors">
																<label class="field-label">
																	Applicable Sectors (Select all that apply)
																</label>

																<span class="text-danger">
																	@error('sector')
																		<i class="fa fa-hand-o-right">{{ strtoupper($message) }}</i>
																	@enderror
																</span><br>

																@foreach($sectors as $sector)
																	<label class="font-12"
																		style="display:inline-flex;align-items:center;gap:6px;line-height:20px;">
																		<input type="checkbox" name="sector[]"
																			value="{{ $sector->sectorid }}" style="margin-top:2px;"
																			{{ in_array($sector->sectorid, old('sector', [])) ? 'checked' : '' }}>
																		{{ $sector->sectorname }}
																	</label><br>
																@endforeach
															</div>
														</div>

														<div class="col-sm-12" align="left">
														@permission('store.firm')
															<button type="submit" class="btn btn-info myfrmbtn width-100"
																tabindex="{{$t++}}">
																Submit
															</button>
														@endpermission
														</div>

													</div>

												</form>
											
										</div>
									</div>
									@endpermission
									@permission('vendors.html')
									<div id="recordlist" class="tab-pane in active"
										style="padding:0px 0px!important;">
										<div class="row" style="padding:0px;">
											<div class="col-xs-12">
												<form name="pagedata" id="pagedata" action="#" method="post"
													onsubmit="return false;">
													@if(Session::has('success'))
														<div class="alert alert-block alert-success">
															<button type="button" class="close" data-dismiss="alert">
																<i class="ace-icon fa fa-times"></i>
															</button>
															{{ Session::get('success') }}
														</div>
													@endif
													@if(Session::has('fail'))
														<div class="alert alert-block alert-success">
															<button type="button" class="close" data-dismiss="alert">
																<i class="ace-icon fa fa-times"></i>
															</button>
															{{ Session::get('fail') }}
														</div>
													@endif
													@if(Session::has('duplicate'))
														<div class="alert alert-block alert-success">
															<button type="button" class="close" data-dismiss="alert">
																<i class="ace-icon fa fa-times"></i>
															</button>
															{{ Session::get('duplicate') }}
														</div>
													@endif

													<div class="table-responsive">

														<table class="table table-bordered table-hover" id="tablerecords">
															<thead>
																<tr>
																	<td colspan="7" class="table-filter-area">
																		<div id="tablehead">
																			<select class="select2 shadcn-select"
																				name="cat_id" id="cat_id"
																				onKeyPress="return OnKeyPress(this, event)"
																				data-width="200" data-placeholder="Firm"
																				onchange="loadData(1,'{{ route('vendors.html') }}')">
																				<option value=""></option>
																				@foreach ($category as $itm)
																					<option value="{{ $itm->categoryid }}">
																						{{ $itm->jobcategory }}
																					</option>
																				@endforeach
																			</select>
																			<select class="select2 shadcn-select" name="tier_id"
																				id="tier_id"
																				onKeyPress="return OnKeyPress(this, event)"
																				data-width="100" data-placeholder="Tier"
																				onchange="loadData(1,'{{ route('vendors.html') }}')">
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
																					onchange="loadData(1,'{{ route('vendors.html') }}')"
																					tabindex="<?php echo $t++;?>" />
																				<i class="ace-icon fa fa-search nav-search-icon"
																					style="margin-top:-3px;"></i>
																			</span>

																		</div>
																	</td>
																</tr>

																<tr class="myhead pd-8">
																	<td style="width:25px;" nowrap><b>S.No.</b></td>
																	<td nowrap style=""><b>Firm Name</b></td>
																	<td nowrap style=""><b>Credentials</b></td>
																	<td nowrap style=""><b>Tier</b></td>
																	<td nowrap style=""><b>Sectors</b></td>
																	<td nowrap style=""><b>Emails</b></td>
																	<td nowrap style="">Action</td>
																</tr>
															</thead>
															<tbody class="tabledata">
																<tr>
																	<td class="center" colspan="7">--Search Record--</td>
																</tr>
															</tbody>
														</table>
													</div>
												</form>
											</div>
										</div>
									</div>
									@endpermission
								@permission('store.email')
								<div id="addemail" class="tab-pane" style="padding:0px 10px!important;">
									<div class="row">
									<form name="emailfrm" id="emailfrm" action="{{ route('store.email')}}" method="post" enctype="multipart/form-data">
									@csrf
									<div class="form-group">
											<div class="col-sm-3">
												<label class="field-label">
													Firm <span id="req">*</span>
												</label>

												<select class="form-control" name="vendors_id" id="vendors_id"
													autofocus required
													onKeyPress="return OnKeyPress(this, event)"
													data-placeholder="Firm Name" tabindex="{{$t++}}">
													<option value="">Select firm</option>
													@foreach ($vendor as $itm)
														<option value="{{ $itm->vendorid }}" {{ intval(old('vendors_id')) === $itm->vendorid ? 'selected' : '' }}>
															{{ $itm->name }}
														</option>
													@endforeach
												</select>

												<span class="text-danger">
													@error('vendorid')
														<i class="fa fa-hand-o-right"> {{ strtoupper($message) }}
														</i>
													@enderror
												</span>
											</div>

											<div class="col-sm-4">
												<label class="field-label">
													Email <span id="req">*</span>
												</label>

												<input type="email" class="form-control" name="vendors_email" id="vendors_email"
													value="{{ old('vendors_email') }}" placeholder="Email" required
													autocomplete="off"
													onKeyPress="return OnKeyPress(this, event)"
													tabindex="{{$t++}}" />

												<span class="text-danger">
													@error('email')
														<i class="fa fa-hand-o-right">
															{{ strtoupper($message) }}</i>
													@enderror
												</span>
											</div>

											<!-- Button column -->
											<div class="col-sm-2">
											@permission('store.email')
											<button type="button" class="btn btn-info myfrmbtn addEmailBtn btn-align-input" onclick="saveEmails()" tabindex="{{$t++}}">
													SUBMIT
											</button>
											@endpermission
											</div>

										
									</div>

								</form>
										
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
	<script>
		setTimeout(function () { loadData(1, '{{ route('vendors.html') }}'); DisplaySectors(); }, 1000);

		function DisplaySectors() {
			var val = document.getElementById("categoryid").value;
			if (val == '2') {
				$(".sectors").css("display", "");
			}
			else {
				$(".sectors").css("display", "none");
			}
		}

		function loadData(page, r1) {
			var pagesearch = document.getElementById("pagesearch").value;
			var tierid = document.getElementById("tier_id").value;
			var categoryid = document.getElementById("cat_id").value;
			$.get("" + r1,
				{
					pagesearch: pagesearch,
					tierid: tierid,
					categoryid: categoryid
				},
				function (data, status) {
					$(".tabledata").html(data);
					$(".mytr").click(function () {
						$(".mytr").removeClass("selected-color").addClass("reset-color");
						$(this).removeClass("reset-color").addClass("selected-color");
					});
				});
		}

		function saveEmails() {
			var form = $('#emailfrm')[0];
			var formData = new FormData(form);
			formData.append('_token', $('meta[name="csrf-token"]').attr('content'));
			$.ajax({
				url: '{{route("store.email")}}',
				method: 'POST',
				data: formData,
				processData: false,
				contentType: false,
				success: function (response) {
					if (response.status === 200) {
						setTimeout(function () { $(".addEmailBtn").css("display", ""); $("#email").val(""); }, 2000);
						bootbox.alert("Email stored successfully!");
						return false;
					}
					else {
						bootbox.alert('Something went wrong: ' + response.message);
						return false;
					}
				},
				error: function (xhr) {
					let errors = xhr.responseJSON?.errors;
					let message = '';
					$.each(errors, function (key, val) {
						message += '<i class="fa fa-hand-o-right"></i> ' + val + '<br>';
					});
					bootbox.alert(message);
				}
			});
		}
	</script>
	<script src="{{ asset('panel/assets/js/master.js') }}"></script>
	<script src="{{ asset('panel/assets/js/datatablePaginationWithFilter.js') }}"></script>
@endsection