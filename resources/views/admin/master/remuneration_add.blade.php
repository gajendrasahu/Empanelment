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
@permission('store.remuneration')
<li class="active">
	<a data-toggle="tab" href="#home">
		<i class="green ace-icon fa fa-plus-circle bigger-120" style="vertical-align:bottom;"></i> Add Remuneration
	</a>
</li>
@endpermission
@permission('remuneration.html')
<li>
	<a data-toggle="tab" href="#recordlist" onclick="loadData(1,'{{ route('remuneration.html') }}')">
		<i class="green ace-icon fa fa-list bigger-120 datalist" style="vertical-align:bottom;"></i> Remuneration List
	</a>
</li>
@endpermission
							</ul>


							<div class="tab-content no-border" style="border:1px solid #ddd; min-height:100px; padding:0px 10px;">
							@permission('store.remuneration')
									<div id="home" class="tab-pane in active" style="padding:0px 10px!important;">
										<div class="row">
												<form name="frm" id="frm" action="{{ route('store.remuneration', 0)}}"
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
														<div class="col-sm-4">
															Category <label id="req">*</label>
															
<input type="hidden" name="form_token" id="form_token" value="{{$token}}">

<select class="chosen-select form-control" name="categoryid" id="categoryid" required onKeyPress="return OnKeyPress(this, event)" data-placeholder="Category" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}" autofocus onchange="GetRemunerationTier(this.value,'{{ route('tiers.list') }}','tierid'); HideFrm();">
	<option value=""></option>
	@foreach ($category as $itm)
		<option value="{{ $itm->categoryid }}" {{ intval(old('categoryid')) === $itm->categoryid ? 'selected' : '' }}>{{ strtoupper($itm->jobcategory) }}</option>
	@endforeach
</select>

	<span class="text-danger">@error('categoryid') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }} </i>@enderror</span>
														</div>
														<div class="col-sm-4">
															Tier <label id="req">*</label>
<select class="chosen-select form-control" name="tierid" id="tierid" required onKeyPress="return OnKeyPress(this, event)" data-placeholder="Tier"
	onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}"	onchange="GetRemunerationForm('{{route('remuneration.form')}}')">
	<option value=""></option>
</select>

															<span class="text-danger">@error('tierid') <i
																class="fa fa-hand-o-right"> {{ strtoupper($message) }} </i>
															@enderror</span>
														</div>
														<div class="col-sm-12">&nbsp;</div>
														<div class="remunerationform"></div>
														<div class="col-sm-12">&nbsp;</div>
														<div class="col-sm-10">&nbsp;</div>
													</div>
												</form>
										</div>
									</div>
									@endpermission
									@permission('remuneration.html')
									<div id="recordlist" class="tab-pane" style="padding:0px 0px!important;">
										<div class="row" style="padding:0px;">
											<div class="col-xs-12" style="padding:0px 2px;">
												<form name="pagedata" id="pagedata" action="#" method="post" onsubmit="return false;">
													<div class="table-responsive">
														<table class="table table-bordered table-striped table-hover pd-8" id="tablerecords">
															<thead>
																<tr>
																	<td colspan="8">
																		<div id="tablehead">
																			<select name="pagesize" id="pagesize"
																				class="select2"
																				onchange="loadData(1,'{{ route('remuneration.html') }}')">
																				<option value="100">100</option>
																				<option value="300">300</option>
																				<option value="500">500</option>
																			</select>
																			<select class="select2" name="catid" id="catid"
																				onKeyPress="return OnKeyPress(this, event)"
																				data-width="200" data-placeholder="Category"
																				onchange="loadData(1,'{{ route('remuneration.html') }}')">
																				<option value=""></option>
																				@foreach ($category as $itm)
																					<option value="{{ $itm->categoryid }}">
																						{{ strtoupper($itm->jobcategory) }}</option>
																				@endforeach
																			</select>
																			<select class="select2" name="sectid" id="sectid"
																				onKeyPress="return OnKeyPress(this, event)"
																				data-width="200" data-placeholder="Sector"
																				onchange="loadData(1,'{{ route('remuneration.html') }}')">
																				<option value=""></option>
																				@foreach ($sector as $itm)
																					<option value="{{ $itm->sectorid }}">
																						{{ strtoupper($itm->sectorname) }}</option>
																				@endforeach
																			</select>
																			<select class="select2" name="posid" id="posid"
																				onKeyPress="return OnKeyPress(this, event)"
																				data-width="200" data-placeholder="Position"
																				onchange="loadData(1,'{{ route('remuneration.html') }}')">
																				<option value=""></option>
																				@foreach ($position as $itm)
																					<option value="{{ $itm->positionid }}">
																						{{ strtoupper($itm->consultantposition) }}
																					</option>
																				@endforeach
																			</select>
																			<select class="select2" name="trid" id="trid"
																				onKeyPress="return OnKeyPress(this, event)"
																				data-width="100" data-placeholder="TIER NAME"
																				onchange="loadData(1,'{{ route('remuneration.html') }}')">
																				<option value=""></option>
																				@foreach ($tier as $itm)
																					<option value="{{ $itm->tierid }}">
																						{{ strtoupper($itm->tiername) }}</option>
																				@endforeach
																			</select>
																			<select class="select2" name="expid" id="expid"
																				onKeyPress="return OnKeyPress(this, event)"
																				data-width="120" data-placeholder="Experience"
																				onchange="loadData(1,'{{ route('remuneration.html') }}')">
																				<option value=""></option>
																				@foreach ($experience as $itm)
																					<option value="{{ $itm->experienceid }}">
																						{{ strtoupper($itm->workexperience) }}
																					</option>
																				@endforeach
																			</select>
																			<select class="select2" name="explevel"
																				id="explevel"
																				onKeyPress="return OnKeyPress(this, event)"
																				data-placeholder="Level"
																				onchange="loadData(1,'{{ route('remuneration.html') }}')"
																				data-width="100">
																				<option value=""></option>
																				@foreach ($levels as $itm)
																					<option value="{{ $itm }}">
																						{{ strtoupper($itm) }}</option>
																				@endforeach
																			</select>
																		</div>
																	</td>
																</tr>
																<tr>
																	<td colspan="8">
																		<div id="tablehead">
																			<select class="select2" name="rateid" id="rateid"
																				onKeyPress="return OnKeyPress(this, event)"
																				data-width="300" data-placeholder="Rate List"
																				onchange="loadData(1,'{{ route('remuneration.html') }}')">
																				<option value=""></option>
																				@foreach ($rates as $itm)
																					<option value="{{ $itm->rateid }}">
																						{{ $itm->ratelist_name }}</option>
																				@endforeach
																			</select>
																		
																			<span class="input-icon" style="float:right;">
																				<input type="text" placeholder="Search ..."
																					class="nav-search-input" id="pagesearch"
																					name="pagesearch" autocomplete="off"
																					onchange="loadData(1,'{{ route('remuneration.html') }}')"
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
																	<td nowrap style=""><b>Sector</b></td>
																	<td nowrap style=""><b>Position</b></td>
																	<td nowrap style=""><b>Tier</b></td>
																	<td nowrap style=""><b>Experience</b></td>
																	<td nowrap style=""><b>Level</b></td>
																	<td nowrap style=""><b>Remuneration</b></td>
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
	
@if(app(\App\Services\PermissionService::class)->hasPermission('remuneration.html') && !app(\App\Services\PermissionService::class)->hasPermission('store.remuneration'))
	setTimeout(function() { $('.datalist').trigger('click'); },1000);
@endif
	
		function HideFrm() {
			$('.remunerationform').html("");
		}

		function loadData(page, r1)
		{
			var pagesize = document.getElementById("pagesize").value;
			var tierid = document.getElementById("trid").value;
			var rateid = document.getElementById("rateid").value;
			var categoryid = document.getElementById("catid").value;
			var experienceid = document.getElementById("expid").value;
			var experiencelevel = document.getElementById("explevel").value;
			var sectorid = document.getElementById("sectid").value;
			var positionid = document.getElementById("posid").value;
			var pagesearch = document.getElementById("pagesearch").value;
			$.get("" + r1,
			{
				page: page,
				pagesize: pagesize,
				pagesearch: pagesearch,
				rateid:rateid,
				tierid: tierid,
				categoryid: categoryid,
				experienceid: experienceid,
				experiencelevel: experiencelevel,
				sectorid: sectorid,
				positionid: positionid
			},
			function (data, status) {
				$(".tabledata").html(data);
				$(".mytr").click(function () {
					$(".mytr").removeClass("selected-color").addClass("reset-color");
					$(this).removeClass("reset-color").addClass("selected-color");
				});
			});
		}

		function GetRemunerationForm(url)
		{
			var categoryid 	= 	document.getElementById("categoryid").value;
			var tierid 		= 	document.getElementById("tierid").value;
			$.get(""+url,
			{
				categoryid:categoryid,
				tierid: tierid,
			},
			function (data, status) {
				$(".remunerationform").html(data);
				$('.chosen-select').chosen({ allow_single_deselect: true });
			});
		}

function GetRemunerationTier(categoryid,r1,htmlid)
{
	var token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
	$("#"+htmlid).empty().trigger("chosen:updated");
	$("#"+htmlid).prop("disabled", true).trigger("chosen:updated");
	$.ajax({
		url: ''+r1,
		type: 'GET',
		data: { 'categoryid': categoryid },
		dataType: 'json',
		success: function(data)
		{
			$("#"+htmlid).append("<option value=''>--Tier--</option>");
			$.each(data, function(index, option) {
			$("#"+htmlid).append("<option value='" + option.value + "'>" + option.label + "</option>");
		});			
		$("#"+htmlid).prop("disabled", false).trigger("chosen:updated");
		},
		error: function(error) {
		}
	});
}

	</script>
	<script src="{{ asset('panel/assets/js/master.js') }}"></script>
	<script src="{{ asset('panel/assets/js/datatablePaginationWithFilter.js') }}"></script>
@endsection