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
					<!-- PAGE CONTENT BEGINS -->
<div class="tabbable" style="padding:0px 0px!important;">
		<ul class="nav nav-tabs padding-0" style="position:relative;">
			<li class="active">
				<a data-toggle="tab" href="#home">
					<i class="green ace-icon fa fa-list-alt bigger-120 datalist" style="vertical-align:bottom;"></i> Project Wise Payment Summary
				</a>
				
			</li>

			
<span style="position:absolute; top:3px; right:10px;">

  <input type="checkbox" name="in_house" id="in_house" style="vertical-align:text-top;">
  <label for="in_house">In House Consultants</label>&nbsp;&nbsp;&nbsp;&nbsp;

<span class="input-icon" style="width:250px!important; height:40px!important; line-height:40px!important;">
	<input type="text" placeholder="Search EoI number..." class="" id="page_search" name="page_search" autocomplete="off" tabindex="<?php echo $t++;?>" style="width:250px!important; border-radius:5px; height:38px!important; line-height:38px!important;" />
	<i class="ace-icon fa fa-search nav-search-icon padding-0"></i>
</span>


</span>

		</ul>

		<div class="tab-content no-border" style="min-height:100px; border-radius:0px!important;">
<div id="home" class="tab-pane in active">
		<form name="eoi_data" id="eoi_data" action="#" method="post" onsubmit="return false;">
			
				@if(Session::has('success'))
				
				<div class="col-sm-12 mt-10">
					<div class="alert alert-block alert-success">
				    	<button type="button" class="close" data-dismiss="alert">
							<i class="ace-icon fa fa-times"></i>
						</button>
						{{ Session::get('success') }}
					</div>
				</div>
				
				@endif
				<div class="table-responsive">
				<table class="table table-bordered" id="tablerecords">
					
					<tr>
						<td>
							<select class="select2" name="financial_year" id="financial_year" data-width="120">
								@foreach($financialYears as $financialYear)
									<option value="{{ $financialYear }}">{{ $financialYear }}</option>
								@endforeach
							</select>
						
							<select class="select2" name="category_id" id="category_id" data-width="100"	data-placeholder="Departments">
								<option value="2">CSF</option>
								<option value="1">AWD</option>
							</select>
							
							<select class="select2" name="manager_id" id="manager_id" data-width="200"	data-placeholder="Project Manager">
								<option value=""></option>
								@foreach ($managers as $manager)
								<option value="{{ $manager->userid }}">{{ $manager->departmentname }}</option>
								@endforeach
							</select>
							
							<select class="select2" name="department_id" id="department_id" data-width="200" data-placeholder="Departments">
								<option value=""></option>
								@foreach ($departments as $itm)
									<option value="{{ $itm->userid }}">
										{{ strtoupper($itm->shortname) }}-{{ $itm->departmentname }}
									</option>
								@endforeach
							</select>

							<select class="select2" name="report_format" id="report_format" data-width="100">
								<option value="1">Table</option>
								<option value="2">Cards</option>
							</select>



						

							<button type="button" class="btn btn-info gridbtn width-100" onclick="loadData('{{ route('finance.projectwise.payment') }}')" tabindex="{{$t++}}">
								<i class="fa fa-search"></i> Search
							</button>
						</td>
					</tr>
				
				</table>
				<div class="paymentData"></div>
				</div>
		</form>
</div>

</div>
</div>


						
				</div><!-- /.col -->
			</div><!-- /.row -->
		</div><!-- /.page-content -->
	
		
	</div>
</div>
<script src="{{ asset('panel/assets/js/jquery-2.1.4.min.js') }}"></script>
<script>
	function loadData(r1) {
		var categoryid 		= document.getElementById("category_id").value;
		var managerid 		= document.getElementById("manager_id").value;
		var departmentid	= document.getElementById("department_id").value;
		var pagesearch 		= document.getElementById("page_search").value;
		var in_house 		= document.getElementById("in_house").checked;
		var financial_year	= document.getElementById("financial_year").value;
		var report_format	= document.getElementById("report_format").value;
		$.get("" + r1,
		{
			categoryid:categoryid,
			managerid:managerid,
			departmentid:departmentid,
			pagesearch:pagesearch,
			in_house:in_house,
			financial_year:financial_year,
			report_format:report_format
		},
		function (data, status) {
			$(".paymentData").html(data);			
		});
	}
</script>

<script src="{{ asset('panel/assets/js/datatablePaginationWithFilter.js') }}"></script>
@endsection