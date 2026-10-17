@extends('admin.admin_master')
@section('admin')
@php
$t=0;
@endphp

<div class="main-content">
<link rel="stylesheet" href="{{ asset('panel/assets/css/finance_dashboard.css') }}" />

	<div class="main-content-inner">
		<div class="page-content" style="padding:10px 15px!important;">
			<div class="ace-settings-container" id="ace-settings-container"></div>

			<div class="row">
				<div class="col-xs-12">

					<div class="tabbable" style="padding:0px 0px!important;">

						<ul class="nav nav-tabs padding-0" style="position:relative;">
							<li class="active">
								<a data-toggle="tab" href="#home">
									<i class="green ace-icon fa fa-list-alt bigger-120 datalist" style="vertical-align:bottom;"></i> Department-wise Fund Balance
								</a>
							</li>
						</ul>

						<div class="tab-content no-border" style="min-height:100px;border-radius:0px!important;">
							<div id="home" class="tab-pane in active">

								<form name="department_fund_balance_data" id="department_fund_balance_data" action="#" method="post" onsubmit="return false;">

									<div class="table-responsive">

										<table class="table table-bordered" id="tablerecords">

											<tr>

												<td>

													<select class="select2" name="financial_year_filter" id="financial_year_filter" data-width="150">

														<option value="">All Financial Years</option>

														@foreach($financialYears as $financialYear)
															<option value="{{ $financialYear }}">{{ $financialYear }}</option>
														@endforeach

													</select>

													<select class="select2" name="department_filter" id="department_filter" data-width="250">

														<option value="">All Departments</option>

														@foreach($departments as $department)
															<option value="{{ $department->userid }}">{{ $department->departmentname }}@if(!empty($department->shortname)) ({{ $department->shortname }}) @endif</option>
														@endforeach

													</select>

													<select class="select2" name="fund_type" id="fund_type" data-width="150">

														<option value="">All Fund Types</option>
														<option value="VENDOR_FUNDING">Consultancy Funding</option>
														<option value="SERVICE_CHARGE">Admin Charge</option>

													</select>

													<select class="select2" name="report_format" id="report_format" data-width="100">

														<option value="1">Table</option>
														<option value="2">Cards</option>

													</select>

													<button type="button" class="btn btn-info gridbtn width-100" onclick="loadDepartmentFundBalanceData('{{ route('finance.department.fundbalance.data') }}')" tabindex="{{$t++}}">
														<i class="fa fa-search"></i> Search
													</button>

												</td>

											</tr>

										</table>

										<div class="departmentFundBalanceData"></div>

									</div>

								</form>

							</div>
						</div>

					</div>

				</div>
			</div>

		</div>
	</div>
</div>

<script src="{{ asset('panel/assets/js/jquery-2.1.4.min.js') }}"></script>

<script>

	function loadDepartmentFundBalanceData(r1)
	{
		var financial_year = document.getElementById("financial_year_filter").value;
		var departmentid = document.getElementById("department_filter").value;
		var fund_type = document.getElementById("fund_type").value;
		var report_format = document.getElementById("report_format").value;

		$.get("" + r1,
		{
			financial_year:financial_year,
			departmentid:departmentid,
			fund_type:fund_type,
			report_format:report_format
		},
		function(data,status)
		{
			$(".departmentFundBalanceData").html(data);
		});
	}

</script>

<script src="{{ asset('panel/assets/js/datatablePaginationWithFilter.js') }}"></script>
@endsection