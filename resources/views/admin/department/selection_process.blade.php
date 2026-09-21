@extends('admin.admin_master')
@section('admin')
	@php
		$t = 0;
	@endphp

	<div class="main-content guidelines-page" style="background-color:#fff!important;">
		<div class="main-content-inner">
			<div class="col-sm-12">&nbsp;</div>
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
			<div class="col-sm-12">
				<div class="eoi-section-header" style="padding:8px 8px 16px 8px;">
					<div style="font-size:20px; font-weight:600; color:#2c3e50;">
						EoI Guidelines
					</div>
					<div style="font-size:14px; color:#6c757d; margin:2px 0 8px;">
						Review tier-wise eligibility, sector coverage, and the detailed rate cards that guide consultancy
						selection.
					</div>
				</div>
			</div>
			<div class="col-sm-12" style="background-color:white!important;">
				<div class="tabbable animated bounceInUp" style="margin-top:20px;">
					<div class="tab-content">
						<div id="home" class="tab-pane fade in active">

							<div id="accordion" class="accordion-style1 panel-group guidelines-accordion">
								<div class="panel panel-default">
									<div class="panel-heading">
										<h4 class="panel-title">
											<a class="accordion-toggle give-weight" data-toggle="collapse"
												data-parent="#accordion" href="#collapseOne">
												<i class="ace-icon fa fa-angle-down bigger-110 pull-right"
													data-icon-hide="ace-icon fa fa-angle-down"
													data-icon-show="ace-icon fa fa-angle-right"></i>
												&nbsp;<i class="fa fa-angle-double-right"></i> Consultancy Firm Selection
											</a>
										</h4>
									</div>
									<div class="panel-collapse collapse in guidelines-panel-collapse" id="collapseOne">
										<div class="panel-body">
											<i class="fa fa-angle-double-right"></i> As per empanelment agreement,
											consultancy firms are categorised into 2 tiers. The details are mentioned below:

											<table class="table table-bordered table-striped" border="1"
												style="margin-top:20px;">
												<thead>
													<tr class="myheadbg">
														<td class="padding-12 give-weight" colspan="10" nowrap>Tier - 1</td>
													</tr>
													<tr class="myheadbg" style="background-color:#F2F5FF!important;">
														<td class="padding-12 give-weight center" style="width:30px;">S.No.
														</td>
														<td class="padding-12 give-weight" nowrap>Firm Name</td>
														@foreach($sectors as $sector)
															<td class="padding-12 center give-weight" nowrap>
																{{ ucwords(strtolower($sector->sectorname)) }}
															</td>
														@endforeach
													</tr>
												</thead>
												<tbody>
													@foreach($tier1_vendors as $index => $vendor)
														<tr>
															<td class="padding-12 center">{{ $index + 1 }}</td>
															<td class="padding-12" nowrap>
																{{ ucwords(strtolower($vendor->companyname)) }}
															</td>
															@foreach($sectors as $sector)
																<td class="padding-12 center" nowrap>
																	@if(collect($vendorSectors[$vendor->vendorid] ?? [])->pluck('sectorid')->contains($sector->sectorid))
																		<i class="fa fa-check status-check"></i>
																	@else
																		<i class="fa fa-times status-cross"></i>
																	@endif
																</td>
															@endforeach
														</tr>
													@endforeach
												</tbody>
											</table>
											<label class="inner-label"><i class="fa fa-angle-double-right"></i> Sector Wise
												Consultant Rates for Tier - 1</label>
											@foreach($tier1_sectors as $sector)
												<table class="table table-bordered table-striped" border="1"
													style="margin-top:20px;">
													<thead>
														<tr class="myheadbg">
															<td class="padding-12 give-weight" colspan="6">{{$loop->iteration}})
																{{ucwords(strtolower($sector->sectorname))}}
															</td>
														</tr>
														<tr class="myheadbg" style="background-color:#F2F5FF!important;">
															<td class="padding-12 give-weight center" style="width:30px;">S.No.
															</td>
															<td class="padding-12 give-weight" nowrap>Consultant Position</td>
															<td class="padding-12 give-weight" style="text-align:right;" nowrap>
																Consultant Rate (Including tax)</td>
															<td class="padding-12 give-weight" style="text-align:right;"
																style="text-align:right;" nowrap>Admin charge
																({{$t1_price->admincharge}}%)</td>
															<td class="padding-12 give-weight" style="text-align:right;" nowrap>
																Total rate including admin charges</td>
														</tr>
													</thead>
													<tbody>
														@php
															$sector->positions = $sector->positions->sortBy('rate')->values();
														@endphp
														@foreach($sector->positions as $position)
															<tr>
																<td class="padding-12 center">{{$loop->iteration}}</td>
																<td class="padding-12">{{$position->consultantposition}}</td>
																<td class="padding-12" style="text-align:right;"><i
																		class="fa fa-inr"></i> {{$position->rate}}</td>
																<td class="padding-12" style="text-align:right;"><i
																		class="fa fa-inr"></i> {{$position->admincharge}}</td>
																<td class="padding-12" style="text-align:right;"><i
																		class="fa fa-inr"></i> {{$position->total}}</td>
															</tr>
														@endforeach
													</tbody>
												</table>
											@endforeach
											<br>

											<!--TIER - 2 DETAILS -->
											<table class="table table-bordered table-striped" border="1"
												style="margin-top:20px;">
												<thead>
													<tr class="myheadbg">
														<td class="padding-12 give-weight" colspan="10" nowrap>Tier - 2</td>
													</tr>
													<tr class="myheadbg" style="background-color:#F2F5FF!important;">
														<td class="padding-12 give-weight center" style="width:30px;">S.No.
														</td>
														<td class="padding-12 give-weight" nowrap>Firm Name</td>
														@foreach($sectors as $sector)
															<td class="padding-12 give-weight" nowrap>
																{{ ucwords(strtolower($sector->sectorname)) }}
															</td>
														@endforeach
													</tr>
												</thead>
												<tbody>
													@foreach($tier2_vendors as $index => $vendor)
														<tr>
															<td class="padding-12 center">{{ $index + 1 }}</td>
															<td class="padding-12" nowrap>
																{{ ucwords(strtolower($vendor->companyname)) }}
															</td>
															@foreach($sectors as $sector)
																<td class="padding-12 center" nowrap>
																	@if(collect($vendorSectors[$vendor->vendorid] ?? [])->pluck('sectorid')->contains($sector->sectorid))
																		✔
																	@else
																		✘
																	@endif
																</td>
															@endforeach
														</tr>
													@endforeach
												</tbody>
											</table>
											<label class="inner-label"><i class="fa fa-angle-double-right"></i> Sector Wise
												Consultant Rates for Tier - 2</label>
											@foreach($tier2_sectors as $sector)
												<table class="table table-bordered table-striped" border="1"
													style="margin-top:20px;">
													<thead>
														<tr>
															<td class="padding-12 give-weight" colspan="6">{{$loop->iteration}})
																{{ucwords(strtolower($sector->sectorname))}}
															</td>
														</tr>
														<tr class="myheadbg" style="background-color:#F2F5FF!important;">
															<td class="padding-12 give-weight center" style="width:30px;">S.No.
															</td>
															<td class="padding-12 give-weight" nowrap>Consultant Position</td>
															<td class="padding-12 give-weight" style="text-align:right;" nowrap>
																Consultant Rate (Including tax)</td>
															<td class="padding-12 give-weight" style="text-align:right;"
																style="text-align:right;" nowrap>Admin charge
																({{$t2_price->admincharge}}%)</td>
															<td class="padding-12 give-weight" style="text-align:right;" nowrap>
																Total rate including admin charges</td>
														</tr>
													</thead>
													<tbody>
														@php
															$sector->positions = $sector->positions->sortBy('rate')->values();
														@endphp
														@foreach($sector->positions as $position)
															<tr>
																<td class="padding-12 center">{{$loop->iteration}}</td>
																<td class="padding-12">{{$position->consultantposition}}</td>
																<td class="padding-12" style="text-align:right;"><i
																		class="fa fa-inr"></i> {{$position->rate}}</td>
																<td class="padding-12" style="text-align:right;"><i
																		class="fa fa-inr"></i> {{$position->admincharge}}</td>
																<td class="padding-12" style="text-align:right;"><i
																		class="fa fa-inr"></i> {{$position->total}}</td>
															</tr>
														@endforeach
													</tbody>
												</table>
											@endforeach
											<br>

										</div>
									</div>

								</div>


								<div class="panel panel-default">
									<div class="panel-heading">
										<h4 class="panel-title">
											<a class="accordion-toggle collapsed give-weight" data-toggle="collapse"
												data-parent="#accordion" href="#collapseTwo">
												<i class="ace-icon fa fa-angle-right bigger-110 pull-right"
													data-icon-hide="ace-icon fa fa-angle-down"
													data-icon-show="ace-icon fa fa-angle-right"></i>
												&nbsp;<i class="fa fa-angle-double-right"></i> Details Required from the
												Department for Publishing EoI (Expression of Interest)
											</a>
										</h4>
									</div>
									<div class="panel-collapse collapse guidelines-panel-collapse" id="collapseTwo">
										<div class="panel-body">

											<div class="eoi-sections">
												<div class="eoi-section">
													<div class="eoi-section-icon"><i class="fa fa-file-text-o"></i></div>
													<div class="eoi-section-content">
														<h3 class="eoi-section-title">Scope of Work / ToR</h3>
														<p class="eoi-section-description">Outline the terms of reference
															and project scope</p>
													</div>
												</div>

												<div class="eoi-section">
													<div class="eoi-section-icon"><i class="fa fa-sitemap"></i></div>
													<div class="eoi-section-content">
														<h3 class="eoi-section-title">Tier</h3>
														<p class="eoi-section-description">Choose the appropriate consultant
															tier</p>
													</div>
												</div>

												<div class="eoi-section">
													<div class="eoi-section-icon"><i class="fa fa-user-plus"></i></div>
													<div class="eoi-section-content">
														<h3 class="eoi-section-title">Number of Resources With Eligibility
															Criteria</h3>
														<p class="eoi-section-description">Define required team size with
															eligibility criteria</p>
													</div>
												</div>

												<div class="eoi-section">
													<div class="eoi-section-icon"><i class="fa fa-calendar"></i></div>
													<div class="eoi-section-content">
														<h3 class="eoi-section-title">Duration</h3>
														<p class="eoi-section-description">Specify the project timeline</p>
													</div>
												</div>
											</div>
											<div class="eoi-warning">
												<div class="warning-icon"><i class="fa fa-warning"></i></div>
												<div class="warning-text">
													All the above details will be provided by the concerned department at
													the time of issuance of the EoI document.
												</div>
											</div>



										</div>
									</div>

								</div>

								<div class="panel panel-default">
									<div class="panel-heading">
										<h4 class="panel-title">
											<a class="accordion-toggle collapsed give-weight" data-toggle="collapse"
												data-parent="#accordion" href="#collapseThree">
												<i class="ace-icon fa fa-angle-right bigger-110 pull-right"
													data-icon-hide="ace-icon fa fa-angle-down"
													data-icon-show="ace-icon fa fa-angle-right"></i>
												&nbsp;<i class="fa fa-angle-double-right"></i> EoI Preparation & Demand Note
												Issuance (CHiPS)
											</a>
										</h4>
									</div>
									<div class="panel-collapse collapse guidelines-panel-collapse" id="collapseThree">
										<div class="panel-body">

											<div class="eoi-sections">
												<div class="eoi-section">
													<div class="eoi-section-icon"><i class="fa fa-paper-plane-o"></i></div>
													<div class="eoi-section-content">
														<h3 class="eoi-section-title">Prepare and Submit EoI</h3>
														<p class="eoi-section-description">The CHiPS team prepares the
															Expression of Interest and submits it to the department for
															approval.</p>
													</div>
												</div>

												<div class="eoi-section">
													<div class="eoi-section-icon"><i class="fa fa-eye"></i></div>
													<div class="eoi-section-content">
														<h3 class="eoi-section-title">Department Review</h3>
														<p class="eoi-section-description">The department reviews the EoI
															and approves it, providing feedback wherever necessary.</p>
													</div>
												</div>

												<div class="eoi-section">
													<div class="eoi-section-icon"><i class="fa fa-cloud-upload"></i></div>
													<div class="eoi-section-content">
														<h3 class="eoi-section-title">Publish EoI</h3>
														<p class="eoi-section-description">
															CHiPS publishes EoI after departmental approval.
														</p>
													</div>
												</div>

												<div class="eoi-section">
													<div class="eoi-section-icon"><i class="fa fa-calendar"></i></div>
													<div class="eoi-section-content">
														<h3 class="eoi-section-title">Duration</h3>
														<p class="eoi-section-description">Specify the project timeline</p>
													</div>
												</div>
												<div class="eoi-section">
													<div class="eoi-section-icon"><i class="fa fa-inr"></i></div>
													<div class="eoi-section-content">
														<h3 class="eoi-section-title">Demand Note</h3>
														<p class="eoi-section-description">CHiPS issues a demand note to the
															user department, directing it to release advance payment for at
															least 6 months.</p>
													</div>
												</div>
											</div>



										</div>
									</div>

								</div>


								<div class="panel panel-default">
									<div class="panel-heading">
										<h4 class="panel-title">
											<a class="accordion-toggle collapsed give-weight" data-toggle="collapse"
												data-parent="#accordion" href="#collapseFour">
												<i class="ace-icon fa fa-angle-right bigger-110 pull-right"
													data-icon-hide="ace-icon fa fa-angle-down"
													data-icon-show="ace-icon fa fa-angle-right"></i>
												&nbsp;<i class="fa fa-angle-double-right"></i> Pre-bid Query
											</a>
										</h4>
									</div>
									<div class="panel-collapse collapse guidelines-panel-collapse" id="collapseFour">
										<div class="panel-body">

											<div class="eoi-sections">
												<div class="eoi-section">
													<div class="eoi-section-icon"><i class="fa fa-question-circle"></i></div>
													<div class="eoi-section-content">
														<h3 class="eoi-section-title">Pre-bid Query (CSF)</h3>
														<p class="eoi-section-description">Firms put forward queries
															regarding the EoI.</p>
													</div>
												</div>

												<div class="eoi-section">
													<div class="eoi-section-icon"><i class="fa fa-comments-o"></i></div>
													<div class="eoi-section-content">
														<h3 class="eoi-section-title">Pre-bid Meeting by CHiPS</h3>
														<p class="eoi-section-description">CHiPS conducts the pre-bid
															meeting, addressing queries either online or offline.</p>
													</div>
												</div>

												<div class="eoi-section">
													<div class="eoi-section-icon"><i class="fa fa-envelope-o"></i></div>
													<div class="eoi-section-content">
														<h3 class="eoi-section-title">Submission of Pre-bid Queries</h3>
														<p class="eoi-section-description">
															Compiled pre-bid queries received from the consultancy firms are
															forwarded to the user department.
														</p>
													</div>
												</div>

												<div class="eoi-section">
													<div class="eoi-section-icon"><i class="fa fa-pencil-square-o"></i></div>
													<div class="eoi-section-content">
														<h3 class="eoi-section-title">Preparation of Pre-bid response by the
															user department.</h3>
														<p class="eoi-section-description">The user department prepares the
															responses to the pre-bid queries and sends them to CHiPS.</p>
													</div>
												</div>
												<div class="eoi-section">
													<div class="eoi-section-icon"><i class="fa fa-newspaper-o"></i></div>
													<div class="eoi-section-content">
														<h3 class="eoi-section-title">Publication of Pre-bid Responses by
															CHiPS</h3>
														<p class="eoi-section-description">CHiPS publishes the pre-bid
															responses received from the user department.</p>
													</div>
												</div>
											</div>



										</div>
									</div>

								</div>


								<div class="panel panel-default">
									<div class="panel-heading">
										<h4 class="panel-title">
											<a class="accordion-toggle collapsed give-weight" data-toggle="collapse"
												data-parent="#accordion" href="#collapseFive">
												<i class="ace-icon fa fa-angle-right bigger-110 pull-right"
													data-icon-hide="ace-icon fa fa-angle-down"
													data-icon-show="ace-icon fa fa-angle-right"></i>
												&nbsp;<i class="fa fa-angle-double-right"></i> Committee Formation for
												Evaluation
											</a>
										</h4>
									</div>
									<div class="panel-collapse collapse guidelines-panel-collapse" id="collapseFive">
										<div class="panel-body">

											<div class="eoi-sections">
												<div class="eoi-section">
													<div class="eoi-section-icon"><i class="fa fa-gavel"></i></div>
													<div class="eoi-section-content">
														<h3 class="eoi-section-title">Committee Selection</h3>
														<p class="eoi-section-description">As per the empanelment selection
															process, CHiPS constitutes an evaluation committee to assess the
															received bids.</p>
													</div>
												</div>

												<div class="eoi-section">
													<div class="eoi-section-icon"><i class="fa fa-user"></i></div>
													<div class="eoi-section-content">
														<h3 class="eoi-section-title">Member Selection</h3>
														<p class="eoi-section-description">In this regards CHiPS sends
															request letter to the user department to nominate minimum 3
															committee member for the evaluation committee. CHiPS also
															includes its internal team members in evaluation committee.</p>
													</div>
												</div>

											</div>



										</div>
									</div>
								</div>



								<div class="panel panel-default">
									<div class="panel-heading">
										<h4 class="panel-title">
											<a class="accordion-toggle collapsed give-weight" data-toggle="collapse"
												data-parent="#accordion" href="#collapseSix">
												<i class="ace-icon fa fa-angle-right bigger-110 pull-right"
													data-icon-hide="ace-icon fa fa-angle-down"
													data-icon-show="ace-icon fa fa-angle-right"></i>
												&nbsp;<i class="fa fa-angle-double-right"></i> Evaluation Process
											</a>
										</h4>
									</div>
									<div class="panel-collapse collapse guidelines-panel-collapse" id="collapseSix">
										<div class="panel-body">

											<div class="eoi-sections">
												<div class="eoi-section">
													<div class="eoi-section-icon"><i class="fa fa-microphone"></i>
													</div>
													<div class="eoi-section-content">
														<h3 class="eoi-section-title">Presentation & Interview </h3>
														<p class="eoi-section-description">CHiPS schedule presentation &
															interview for the received bid.</p>
													</div>
												</div>

												<div class="eoi-section">
													<div class="eoi-section-icon"><i class="fa fa-list-alt"></i></div>
													<div class="eoi-section-content">
														<h3 class="eoi-section-title">Criteria Evaluation</h3>
														<p class="eoi-section-description">Evaluation committee evaluate the
															bid based on the criteria defined in the EOI.</p>
													</div>
												</div>

												<div class="eoi-section">
													<div class="eoi-section-icon"><i class="fa fa-trophy"></i></div>
													<div class="eoi-section-content">
														<h3 class="eoi-section-title">Final Score</h3>
														<p class="eoi-section-description">Based on the cumulative score
															(C-SF Firm) finalised.</p>
													</div>
												</div>

											</div>



										</div>
									</div>
								</div>

								<div class="panel panel-default">
									<div class="panel-heading">
										<h4 class="panel-title">
											<a class="accordion-toggle collapsed give-weight" data-toggle="collapse"
												data-parent="#accordion" href="#collapseSeven">
												<i class="ace-icon fa fa-angle-right bigger-110 pull-right"
													data-icon-hide="ace-icon fa fa-angle-down"
													data-icon-show="ace-icon fa fa-angle-right"></i>
												&nbsp;<i class="fa fa-angle-double-right"></i> Work Order Issuance
											</a>
										</h4>
									</div>
									<div class="panel-collapse collapse guidelines-panel-collapse" id="collapseSeven">
										<div class="panel-body">

											<div class="eoi-sections">
												<div class="eoi-section">
													<div class="eoi-section-icon"><i class="fa fa-thumbs-o-up"></i></div>
													<div class="eoi-section-content">
														<h3 class="eoi-section-title">After receiving the advance payment
															CHiPS issue the work order to the selected C-SF </h3>
													</div>
												</div>


												<div class="eoi-section">
													<div class="eoi-section-icon"><input type="checkbox" name="confirmation"
															id="confirmation"></div>
													<div class="eoi-section-content">
														<h3 class="eoi-section-title" style="margin-top:5px;">I hereby
															confirm that I have carefully read, understood, and agree to
															comply with the complete EOI selection process as outlined
															above. </h3>

													</div>

												</div>

											</div>
											<a href="{{route('add.hiring')}}">
												<button type="button" class="btn-default right proceedBtn" disabled
													style="width:100px; margin-top:10px;">Proceed</button>
											</a>
										</div>
									</div>
								</div>


							</div>

						</div>

					</div>
				</div>
				<br>




			</div>
		</div>
	</div>
	<script src="{{ asset('panel/assets/js/jquery-2.1.4.min.js') }}"></script>
	<script>

		document.querySelectorAll('#accordion .accordion-toggle').forEach(function (toggle) {
			toggle.addEventListener('click', function () {
				var panel = toggle.closest('.panel');
				if (panel) {
					setTimeout(function () {
						window.scrollTo({
							top: panel.offsetTop,
							behavior: 'smooth'
						});
					}, 350);
				}
			});
		});

		$(document).ready(function () {
			$('#confirmation').on('change', function () {
				if ($(this).is(':checked')) {
					$('.btn-default')
						.removeClass('btn-default')
						.addClass('my-btn-default')
						.removeAttr('disabled');
				} else {
					$('.my-btn-default')
						.removeClass('my-btn-default')
						.addClass('btn-default')
						.attr('disabled', 'disabled');
				}
			});
		});
	</script>


@endsection