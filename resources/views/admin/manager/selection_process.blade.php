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
						Reference the tier criteria, sector mapping, and consultant rate structure before moving a request
						ahead.
					</div>
				</div>
			</div>
			<div class="col-sm-12">
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
												&nbsp;<i class="fa fa-angle-double-right"></i> Consultancy Firm Selcetion /
												Application and web development Firm Selection
											</a>
										</h4>
									</div>
									<div class="panel-collapse collapse guidelines-panel-collapse" id="collapseOne"
										style="position:relative;">
										<select name="dispContent" id="dispContent" class="guidelines-select"
											onchange="DisplayContent()">
											<option value="1">Consultancy</option>
											<option value="2">Application & Web Development</option>
										</select>

										<div class="panel-body csfContent">

											<h5>Consultancy Firm Selcetion</h5>
											<p style="font-size: 13px; margin-top: 4px;">As per empanelment agreement,
												consultancy firms are categorised into 2 tiers. The details are mentioned
												below:
											</p>

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
											<!--
																																							<label class="inner-label">
																																								<i class="fa fa-angle-double-right"></i> Sector Wise Consultant Costs for Tier - 1
																																							</label>
																																							-->
											@foreach($tier1_sectors as $sector)
												<table class="table table-bordered table-striped" border="1"
													style="margin-top:20px;">
													<thead>
														<tr class="myheadbg">
															<td class="give-weight" colspan="6" style="color: #025964; background-color: #ffffff !important
																																				; font-size: 14px;">
																{{$loop->iteration}})
																{{ucwords(strtolower($sector->sectorname))}}
															</td>
														</tr>
														<tr class="myheadbg" style="background-color:#F2F5FF!important;">
															<td class="padding-12 give-weight center" style="width:30px;">S.No.
															</td>
															<td class="padding-12 give-weight" nowrap>Consultant Position</td>
															<td class="padding-12 give-weight" style="text-align:right;" nowrap>
																Base Price</td>
															<td class="padding-12 give-weight" style="text-align:right;" nowrap>
																Consultant Rate (Including tax)</td>
															<td class="padding-12 give-weight" style="text-align:right;"
																style="text-align:right;" nowrap>Admin Charge
																({{$t1_price->admincharge}}%)</td>
															<td class="padding-12 give-weight" style="text-align:right;" nowrap>
																Total Rate Including Admin Charges</td>
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
																		class="fa fa-inr"></i> {{$position->baseprice}}</td>
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
											<!--
																																							<label class="inner-label">
																																								<i class="fa fa-angle-double-right"></i> Sector Wise Consultant Costs for Tier - 2
																																							</label>
																																							-->
											@foreach($tier2_sectors as $sector)
												<table class="table table-bordered table-striped" border="1"
													style="margin-top:20px;">
													<thead>
														<tr>
															<td class="padding-12 give-weight"
																style="color: #025964; font-size: 14px; font-weight: 600;"
																colspan="6">
																{{$loop->iteration}})
																{{ucwords(strtolower($sector->sectorname))}}
															</td>
														</tr>
														<tr class="myheadbg" style="background-color:#F2F5FF!important;">
															<td class="padding-12 give-weight center" style="width:30px;">S.No.
															</td>
															<td class="padding-12 give-weight" nowrap>Consultant Position</td>
															<td class="padding-12 give-weight" style="text-align:right;" nowrap>
																Base Price</td>
															<td class="padding-12 give-weight" style="text-align:right;" nowrap>
																Consultant Rate (including tax)</td>
															<td class="padding-12 give-weight" style="text-align:right;"
																style="text-align:right;" nowrap>Admin Charge
																({{$t2_price->admincharge}}%)</td>
															<td class="padding-12 give-weight" style="text-align:right;" nowrap>
																Total Rate Including Admin Charges</td>
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
																		class="fa fa-inr"></i> {{$position->baseprice}}</td>
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

										<div class="panel-body awdContent">
											<h5>Application & Web Development Firm Selection</h5>
											<p style="font-size: 13px; margin-top: 4px;">
												As per empanelment agreement,
												application & web development firms are categorised into 2 tiers. The
												details
												are mentioned below:
											</p>


											<table class="table table-bordered table-striped" border="1"
												style="margin-top:20px;">
												<thead>
													<tr class="myheadbg">
														<td class="padding-12 give-weight" colspan="2" nowrap>Tier - 1</td>
													</tr>
													<tr class="myheadbg" style="background-color:#F2F5FF!important;">
														<td class="padding-12 give-weight center" style="width:30px;">S.No.
														</td>
														<td class="padding-12 give-weight" nowrap>Firm Name</td>
													</tr>
												</thead>
												<tbody>
													@foreach($awd_tier1_vendors as $index => $vendor)
														<tr>
															<td class="padding-12 center">{{ $index + 1 }}</td>
															<td class="padding-12" nowrap>
																{{ ucwords(strtolower($vendor->companyname)) }}
															</td>
														</tr>
													@endforeach
												</tbody>
											</table>
											<!-- TIER 1 PRICES -->
											<table class="table table-bordered table-striped" border="1"
												style="margin-top:20px;">
												<thead>
													<tr class="myheadbg">
														<td class="padding-12 give-weight" colspan="8" nowrap>Man-Month Rate
															(Tier-1)</td>
													</tr>
													<tr class="myheadbg" style="background-color:#F2F5FF!important;">
														<td class="padding-12 give-weight" nowrap>Level</td>
														<td class="padding-12 give-weight" style="text-align:right;" nowrap>
															Remuneration<br>(A)</td>
														<td class="padding-12 give-weight" style="text-align:right;" nowrap>
															Operating Margin
															({{$pricing1->operatingmargin}}%)<br>B=(A*{{$pricing1->operatingmargin}}%)
														</td>
														<td class="padding-12 give-weight" style="text-align:right;" nowrap>
															With Operating Margin<br>(C=A+B)</td>
														<td class="padding-12 give-weight" style="text-align:right;" nowrap>
															Tax ({{$pricing1->tax}}%)<br>(D=C*{{$pricing1->tax}}%)</td>
														<td class="padding-12 give-weight" style="text-align:right;" nowrap>
															With Tax<br>(E=C+D)</td>
														@if($pricing1->admincharge != 0)
															<td class="padding-12 give-weight" style="text-align:right;" nowrap>
																CHiPS Admin Charge
																({{$pricing1->admincharge}}%)<br>(F=E*{{$pricing1->admincharge}}%)
															</td>
														@endif
														<td class="padding-12 give-weight" style="text-align:right;" nowrap>
															Total Man Month Rate<br>(E+F)
														</td>
													</tr>
												</thead>
												<tbody>
													@foreach($tier1_rate as $rate)
														<tr>
															<td class="padding-12 center">{{ $rate->experiencelevel }}</td>
															<td class="padding-12" style="text-align:right;">
																<i class="fa fa-inr"></i> {{$rate->remuneration}}
															</td>
															<td class="padding-12" style="text-align:right;">
																<i class="fa fa-inr"></i> {{$rate->operatingvalue}}
															</td>
															<td class="padding-12" style="text-align:right;">
																<i class="fa fa-inr"></i> {{$rate->withoperating}}
															</td>
															<td class="padding-12" style="text-align:right;">
																<i class="fa fa-inr"></i> {{$rate->taxvalue}}
															</td>
															<td class="padding-12" style="text-align:right;">
																<i class="fa fa-inr"></i> {{$rate->withtax}}
															</td>
															@if($pricing1->admincharge != 0)
																<td class="padding-12" style="text-align:right;">
																	<i class="fa fa-inr"></i> {{$rate->admincharge}}
																</td>
															@endif
															<td class="padding-12" style="text-align:right;">
																<i class="fa fa-inr"></i> {{$rate->withadmin}}
															</td>

														</tr>
													@endforeach
												</tbody>
											</table>
											<!--TIER - 2 DETAILS -->
											<table class="table table-bordered table-striped" border="1"
												style="margin-top:20px;">
												<thead>
													<tr class="myheadbg">
														<td class="padding-12 give-weight" colspan="2" nowrap>Tier - 2</td>
													</tr>
													<tr class="myheadbg" style="background-color:#F2F5FF!important;">
														<td class="padding-12 give-weight center" style="width:30px;">S.No.
														</td>
														<td class="padding-12 give-weight" nowrap>Firm Name</td>
													</tr>
												</thead>
												<tbody>
													@foreach($awd_tier2_vendors as $index => $vendor)
														<tr>
															<td class="padding-12 center">{{ $index + 1 }}</td>
															<td class="padding-12" nowrap>
																{{ ucwords(strtolower($vendor->companyname)) }}
															</td>
														</tr>
													@endforeach
												</tbody>
											</table>
											<table class="table table-bordered table-striped" border="1"
												style="margin-top:20px;">
												<thead>
													<tr class="myheadbg">
														<td class="padding-12 give-weight" colspan="8" nowrap>Man-Month Rate
															(Tier-2)</td>
													</tr>
													<tr class="myheadbg" style="background-color:#F2F5FF!important;">
														<td class="padding-12 give-weight" nowrap>Level</td>
														<td class="padding-12 give-weight" style="text-align:right;" nowrap>
															Remuneration<br>(A)</td>
														<td class="padding-12 give-weight" style="text-align:right;" nowrap>
															Operating Margin
															({{$pricing2->operatingmargin}}%)<br>B=(A*{{$pricing2->operatingmargin}}%)
														</td>
														<td class="padding-12 give-weight" style="text-align:right;" nowrap>
															With Operating Margin<br>(C=A+B)</td>
														<td class="padding-12 give-weight" style="text-align:right;" nowrap>
															Tax ({{$pricing1->tax}}%)<br>(D=C*{{$pricing1->tax}}%)</td>
														<td class="padding-12 give-weight" style="text-align:right;" nowrap>
															With Tax<br>(E=C+D)</td>
														@if($pricing2->admincharge != 0)
															<td class="padding-12 give-weight" style="text-align:right;" nowrap>
																CHiPS Admin Charge
																({{$pricing1->admincharge}}%)<br>(F=E*{{$pricing2->admincharge}}%)
															</td>
														@endif
														<td class="padding-12 give-weight" style="text-align:right;" nowrap>
															Total Man Month Rate<br>(E+F)
														</td>
													</tr>
												</thead>
												<tbody>
													@foreach($tier2_rate as $rate)
														<tr>
															<td class="padding-12 center">{{ $rate->experiencelevel }}</td>
															<td class="padding-12" style="text-align:right;">
																<i class="fa fa-inr"></i> {{$rate->remuneration}}
															</td>
															<td class="padding-12" style="text-align:right;">
																<i class="fa fa-inr"></i> {{$rate->operatingvalue}}
															</td>
															<td class="padding-12" style="text-align:right;">
																<i class="fa fa-inr"></i> {{$rate->withoperating}}
															</td>
															<td class="padding-12" style="text-align:right;">
																<i class="fa fa-inr"></i> {{$rate->taxvalue}}
															</td>
															<td class="padding-12" style="text-align:right;">
																<i class="fa fa-inr"></i> {{$rate->withtax}}
															</td>
															@if($pricing2->admincharge != 0)
																<td class="padding-12" style="text-align:right;">
																	<i class="fa fa-inr"></i> {{$rate->admincharge}}
																</td>
															@endif
															<td class="padding-12" style="text-align:right;">
																<i class="fa fa-inr"></i> {{$rate->withadmin}}
															</td>

														</tr>
													@endforeach
												</tbody>

											</table>

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
												project manager for Publishing EoI (Expression of Interest)
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
														<p class="eoi-section-description">Choose the appropriate
															consultant/AWD tier</p>
													</div>
												</div>

												<div class="eoi-section">
													<div class="eoi-section-icon"><i class="fa fa-user-plus"></i></div>
													<div class="eoi-section-content">
														<h3 class="eoi-section-title">Number of Resources with Eligibility
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
															Expression of Interest and submits it to the Project Manager for
															approval.</p>
													</div>
												</div>

												<div class="eoi-section">
													<div class="eoi-section-icon"><i class="fa fa-eye"></i></div>
													<div class="eoi-section-content">
														<h3 class="eoi-section-title">Project Manager Review</h3>
														<p class="eoi-section-description">The Project Manager reviews the
															EoI and approves it, providing feedback wherever necessary.</p>
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
														<h3 class="eoi-section-title">Pre-bid Query CSF / AWD</h3>
														<p class="eoi-section-description">Firms put forward their queries
															regarding EoI.</p>
													</div>
												</div>

												<div class="eoi-section">
													<div class="eoi-section-icon"><i class="fa fa-envelope-o"></i></div>
													<div class="eoi-section-content">
														<h3 class="eoi-section-title">Submission of Pre-bid Queries</h3>
														<p class="eoi-section-description">
															Pre-bid queries received from consultancy firms/ AWD are
															forwarded to the concerned Project Manager through Empanelment
															team.
														</p>
													</div>
												</div>

												<div class="eoi-section">
													<div class="eoi-section-icon"><i class="fa fa-pencil-square-o"></i></div>
													<div class="eoi-section-content">
														<h3 class="eoi-section-title">Preparation of Responses by User
															Department</h3>
														<p class="eoi-section-description">The PM prepares the responses to
															the pre-bid queries and sends them to Empanelment Team.</p>
													</div>
												</div>
												<div class="eoi-section">
													<div class="eoi-section-icon"><i class="fa fa-newspaper-o"></i></div>
													<div class="eoi-section-content">
														<h3 class="eoi-section-title">Publication of Pre-bid Responses by
															CHiPS</h3>
														<p class="eoi-section-description">CHiPS publishes the pre-bid
															responses received from the user department/concerned PMs.</p>
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
												&nbsp;<i class="fa fa-angle-double-right"></i> Formation of Evaluation
												Committee
											</a>
										</h4>
									</div>
									<div class="panel-collapse collapse guidelines-panel-collapse" id="collapseFive">
										<div class="panel-body">

											<div class="eoi-sections">
												<div class="eoi-section">
													<div class="eoi-section-icon"><i class="fa fa-gavel"></i></div>
													<div class="eoi-section-content">
														<h3 class="eoi-section-title">Committee Formation</h3>
														<p class="eoi-section-description">As per the empanelment selection
															process,evaluation committee is formed to assess the proposal
															received.</p>
													</div>
												</div>

												<div class="eoi-section">
													<div class="eoi-section-icon"><i class="fa fa-user"></i></div>
													<div class="eoi-section-content">
														<h3 class="eoi-section-title">Member Selection</h3>
														<p class="eoi-section-description">Three members for the evaluation
															committee is nominated by concernec PM/user department.</p>
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
														<h3 class="eoi-section-title">Presentation And Interview</h3>
														<p class="eoi-section-description">Concerned PM suggests interview
															date.</p>
													</div>
												</div>

												<div class="eoi-section">
													<div class="eoi-section-icon"><i class="fa fa-list-alt"></i></div>
													<div class="eoi-section-content">
														<h3 class="eoi-section-title">Criteria Evaluation</h3>
														<p class="eoi-section-description">Evaluation committee assesses the
															proposal based on criteria defined in EoI.</p>
													</div>
												</div>

												<div class="eoi-section">
													<div class="eoi-section-icon"><i class="fa fa-trophy"></i></div>
													<div class="eoi-section-content">
														<h3 class="eoi-section-title">Final Score</h3>
														<p class="eoi-section-description">CSF/AWD firm is finalised based
															on cumulative score.</p>
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
													<div class="eoi-section-icon"><i class="fa fa-briefcase"></i></div>
													<div class="eoi-section-content">
														<h3 class="eoi-section-title">CHiPS issues work order to the
															selected firm.</h3>
													</div>
												</div>


												<div class="eoi-section">
													<div class="eoi-section-icon"><input type="checkbox" name="confirmation"
															id="confirmation"></div>
													<div class="eoi-section-content">
														<h3 class="eoi-section-title" style="margin-top:5px;">I hereby
															confirm that I have carefully read the EoI selection process
															outlined above, and I agree to comply with the same..</h3>

													</div>

												</div>

											</div>
											<a href="{{route('add.pmhiring')}}">
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
		setTimeout(function () { DisplayContent(); }, 2000);
		function DisplayContent() {
			var val = document.getElementById('dispContent').value;
			if (val == 1) {
				$(".csfContent").css("display", "");
				$(".awdContent").css("display", "");
			}
			else {
				$(".csfContent").css("display", "none");
				$(".awdContent").css("display", "");
			}
		}

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