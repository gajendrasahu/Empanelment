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
						<div class="tabbable animated bounceInUp" style="padding:0px 0px!important;">
							@if(in_array(2, Session::get('actions')) || in_array(3, Session::get('actions')) || in_array(12, Session::get('actions')))
								<div class="eoi-section-header" onclick="loadData(1,'{{ route('admininvoice.html') }}')"
									style="cursor:pointer; padding:8px 8px 16px 8px;">

									<div style="font-size:20px; font-weight:600; color:#2c3e50;">
										Raised Invoice List
									</div>

									<div style="font-size:14px; color:#6c757d; margin:2px 0px; 8px 0">
										Review issued invoices, verify amounts and taxes, and follow up on pending items.
									</div>

								</div>
							@endif

							<div class="tab-content no-border" style="min-height:100px; padding:0px 10px;">

								@if(in_array(2, Session::get('actions')) || in_array(3, Session::get('actions')) || in_array(12, Session::get('actions')))
									<div id="recordlist" class="tab-pane in active" style="padding:0px 0px!important;">
										<div class="row" style="padding:0px;">
											@if(Session::has('success'))
												<div class="col-sm-12" style="padding:10px;">
													<div class="alert alert-block alert-success"
														style="border-radius:0px!important;">
														<button type="button" class="close" data-dismiss="alert">
															<i class="ace-icon fa fa-times"></i>
														</button>
														<i class="fa fa-thumbs-up"></i> {{ Session::get('success') }}
													</div>
												</div>
											@endif
											<div class="col-xs-12">
												<form name="pagedata" id="pagedata" action="#" method="post"
													onsubmit="return false;">
													@csrf
													<div class="table-responsive">
														<table class="table table-bordered table-striped table-hover pd-8"
															id="tablerecords">
															<thead>
																<tr>
																	<td colspan="7">
					<div class="filters-group">
																			<select name="pagesize" id="pagesize"
																				class="select2 selectbx"
																				onchange="loadData(1,'{{ route('admininvoice.html') }}')"
																				data-width="70" data-placeholder="RECORDS">
																				<option value="50">50</option>
																				<option value="100">100</option>
																				<option value="300">300</option>
																				<option value="500">500</option>
																			</select>

																			<select class="select2" name="vendorid"
																				id="vendorid"
																				onKeyPress="return OnKeyPress(this, event)"
																				onchange="loadData(1,'{{ route('admininvoice.html') }}');"
																				data-width="250" data-placeholder="Vendor Name">
																				<option value=""></option>
																				@foreach ($vendors as $itm)
																					<option value="{{ $itm->vendorid }}">
																						{{ ucwords(strtolower($itm->companyname)) }}-{{ strtoupper($itm->shortname) }}
																					</option>
																				@endforeach
																			</select>
																			<select class="select2" name="departmentid"
																				id="departmentid"
																				onKeyPress="return OnKeyPress(this, event)"
																				onchange="loadData(1,'{{ route('admininvoice.html') }}');"
																				data-width="250"
																				data-placeholder="Department Name">
																				<option value=""></option>
																				@foreach ($departments as $itm)
																					<option value="{{ $itm->userid }}">
																						{{ strtoupper($itm->shortname) }}-{{ ucwords(strtolower($itm->departmentname)) }}
																					</option>
																				@endforeach
																			</select>

																			<select class="select2" name="orderid" id="orderid"
																				onKeyPress="return OnKeyPress(this, event)"
																				onchange="loadData(1,'{{ route('admininvoice.html') }}');"
																				data-width="250"
																				data-placeholder="Order number">
																				<option value=""></option>
																				@foreach ($orders as $itm)
																					<option value="{{ $itm->orderid }}">
																						{{ $itm->ordernumber }}
																					</option>
																				@endforeach
																			</select>

																			<span class="input-icon">
																				<input type="text" placeholder="Search..."
																					class="nav-search-input selectbx" id="pagesearch"
																					name="pagesearch" autocomplete="off"
																					onchange="loadData(1,'{{ route('admininvoice.html') }}')"
																					value="{{$search}}"
																					tabindex="<?php echo $t++;?>" />
																				<i class="ace-icon fa fa-search nav-search-icon"></i>
																			</span>

																		</div>
																	</td>
																</tr>
																<tr>
																	<td class="" style="width:25px;" nowrap><b>S.No.</b></td>
																	<td nowrap>Voucher & Order Detail</td>
																	<td nowrap>Invoice Number</td>
																	<td nowrap class="center width-100">Invoice Date</td>
																	<td nowrap style="text-align:right;">Amount</td>
																	<td nowrap style="text-align:right;">Tax Value</td>
																	<td nowrap style="text-align:right;">Net Amount</td>
																</tr>
															</thead>
															<tbody class="tabledata">
																<tr>
																	<td colspan="7" class="center">--Search Record--</td>
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

	<div class="modal fade left" id="myModal" role="dialog" data-backdrop="static">
		<div class="modal-lg-dialog">
			<div class="modal-content"
				style="font-size:12px; width:80%; margin:0 auto; margin-top:10px; background-color:white;">

			</div>
		</div>
	</div>

	<script src="{{ asset('panel/assets/js/jquery-2.1.4.min.js') }}"></script>
	<script>
		$(document).ready(function () {
			$.ajaxSetup({
				headers: {
					'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
				}
			});

			loadData(1, '{{ route('admininvoice.html') }}');
		});
		function loadData(page, r1) {
			var pagesize = document.getElementById("pagesize").value;
			var vendorid = document.getElementById("vendorid").value;
			var departmentid = document.getElementById("departmentid").value;
			var orderid = document.getElementById("orderid").value;
			var pagesearch = document.getElementById("pagesearch").value;

			$.get("" + r1,
				{
					page: page,
					pagesize: pagesize,
					pagesearch: pagesearch,
					vendorid: vendorid,
					departmentid: departmentid,
					orderid: orderid,
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
	<script src="{{ asset('panel/assets/js/master.js') }}"></script>
	<script src="{{ asset('panel/assets/js/datatablePaginationWithFilter.js') }}"></script>
@endsection
