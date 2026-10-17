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
									<i class="green ace-icon fa fa-list-alt bigger-120 datalist" style="vertical-align:bottom;"></i> Financial Year Fund Movement
								</a>
							</li>
						</ul>

						<div class="tab-content no-border" style="min-height:100px;border-radius:0px!important;">
							<div id="home" class="tab-pane in active">

								<form name="fund_movement_data" id="fund_movement_data" action="#" method="post" onsubmit="return false;">

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

													<select class="select2" name="report_format" id="report_format" data-width="100">

														<option value="1">Table</option>
														<option value="2">Cards</option>

													</select>

													<button type="button" class="btn btn-info gridbtn width-100" onclick="loadFundMovementData('{{ route('finance.fundmovement.wise') }}')" tabindex="{{$t++}}">
														<i class="fa fa-search"></i> Search
													</button>

												</td>

											</tr>

										</table>

										<div class="fundMovementData"></div>

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

	function loadFundMovementData(r1)
	{
		var financial_year = document.getElementById("financial_year_filter").value;
		var report_format = document.getElementById("report_format").value;

		$.get("" + r1,
		{
			financial_year:financial_year,
			report_format:report_format
		},
		function(data,status)
		{
			$(".fundMovementData").html(data);
		});
	}

</script>

<script src="{{ asset('panel/assets/js/datatablePaginationWithFilter.js') }}"></script>
@endsection