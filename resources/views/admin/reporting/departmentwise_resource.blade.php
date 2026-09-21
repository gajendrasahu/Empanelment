@extends('admin.admin_master')
@section('admin')
@php
$t=1;
@endphp
<div class="main-content">
<style>
    .order-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 25px;
        font-family: Arial, sans-serif;
        font-size: 14px;
        border: 1px solid #dcdcdc;
    }

    .order-table th,
    .order-table td {
        border: 1px solid #dcdcdc;
        padding: 8px;
    }

    /* Order Header */
    .order-summary {
        background: #147E8B;
        color: #fff;
        font-size: 14px;
        font-weight: 600;
    }

    .order-summary .label {
        opacity: 0.85;
        font-weight: normal;
    }

    /* Column Header */
    .resource-head {
        background: #f4f7fb;
        color: #333;
        font-weight: 600;
        text-align: left;
    }

    /* Resource Rows */
    .resource-row:nth-child(even) {
        background: #fafafa;
    }

    .text-center {
        text-align: center;
    }
</style>
<style>
.status-bds {
  position:absolute;
  right:5px;
  top:10px;
  display: flex;
  align-items: center;
  gap: 8px;
  flex-wrap: wrap;
  font-family: Arial, sans-serif;
}

.bds {
  display: inline-block;
  padding: 4px 10px;
  border-radius: 20px;
  font-size: 10px;
  font-weight: 600;
  color: #fff;
}

.bds-pending {
  background-color: #f59e0b;
}

.bds-active {
  background-color: #10b981;
}

.bds-extended {
  background-color: #3b82f6;
}

.bds-released {
  background-color: #ef4444;
}
.bds-success {
  background-color: green;
}

.bds-total {
  background-color: purple;
}

.bds-default {
  background-color:#eee;
  color:black;
}
.width-152
{
	width:152px!important;
}
.resource-status
{
	position:absolute; 
	top:2px; 
	left:2px; 
	font-size:8px; 
	width:15px; 
	border-radius:5px; 
	color:white;
}

</style>

	<div class="main-content-inner">
			<div class="page-content" style="padding:10px 15px!important;">
				<div class="ace-settings-container" id="ace-settings-container"></div>
				<div class="row">
					<div class="col-xs-12">
					<!-- PAGE CONTENT BEGINS -->
<div class="tabbable animated fadeInUp delay-02" style="padding:0px 0px!important;">
			<div class="eoi-section-header" onclick="loadData('{{ route('departmentwiseresource.html') }}')" style="cursor:pointer; padding:8px 8px 16px 8px; position:relative;">

				<div style="font-size:20px; font-weight:600; color:#2c3e50;">
					Department / Project Manager Wise Active Resource Detail
				</div>
				<div style="position:absolute; right:5px; top:0px;">
<span>
  <input type="checkbox" name="in_house" id="in_house" onchange="loadData('{{ route('departmentwiseresource.html') }}')" style="vertical-align:text-top;">
  <label for="in_house">In House Consultants</label>&nbsp;&nbsp;&nbsp;&nbsp;
</span>

				
						<button type="button" class="btn btn-info gridbtn" style="width:100px; padding:0px!important;" onClick="loadData('{{ route('departmentwiseresource.html') }}')" style="width:120px;">
							<i class="fa fa-database"></i> Refresh Data
						</button>

						<button type="button" class="btn btn-info gridbtn" style="width:60px; padding:0px!important;" onClick="printData()">
							<i class="fa fa-print"></i> Print
						</button>
						
						<button type="button" class="btn btn-info gridbtn" style="width:60px; padding:0px!important;" onClick="clearData()">
							<i class="fa fa-remove"></i> Clear
						</button>
				</div>
			</div>

		<div class="tab-content no-border" style="min-height:100px; padding:0px 10px;">

<div id="recordlist" class="tab-pane in active" style="padding:0px 0px!important;">
	<div class="row" style="padding:0px;">
		@if(Session::has('success'))
		<div class="col-sm-12" style="padding:0px;">
			<div class="alert alert-block alert-success">
				<button type="button" class="close" data-dismiss="alert">
					<i class="ace-icon fa fa-times"></i>
				</button>
				{{ Session::get('success') }}
			</div>
		</div>
		@endif
		<div class="col-xs-12">
		<form name="resource_reporting" id="resource_reporting" action="#" method="post" onsubmit="return false;">
			@csrf
			<div class="table-responsive">
				<table class="table table-bordered">
				
				<tr>
					<td style="padding:5px!important; font-weight:600;">
					
						<select class="select2" name="categoryid" id="categoryid" onKeyPress="return OnKeyPress(this, event)" onchange="getVendors(this.value); loadData('{{ route('departmentwiseresource.html') }}')" data-width="190" data-placeholder="Category Name">
							<option value="">Category Name</option>
							@foreach ($category as $itm)
							<option value="{{ $itm->categoryid }}" @if($itm->categoryid==2) selected @endif>{{ $itm->jobcategory }}</option>
							@endforeach								
						</select>
						
						<select class="select2" name="vendorid" id="vendorid" onKeyPress="return OnKeyPress(this, event)" data-width="190" data-placeholder="Firm Name">
							<option value=""></option>
							@foreach ($vendors as $itm)
							<option value="{{ $itm->vendorid }}">{{ $itm->companyname }}</option>
							@endforeach
						</select>
						
						<select class="select2" name="departmentid" id="departmentid" onKeyPress="return OnKeyPress(this, event)" data-width="190" data-placeholder="Departments">
							<option value=""></option>
							@foreach ($departments as $itm)
							<option value="{{ $itm->departmentid }}">{{ $itm->shortname }}-{{ $itm->departmentname }}</option>
							@endforeach
						</select>
						
						<select class="select2" name="managerid" id="managerid" onKeyPress="return OnKeyPress(this, event)" data-width="190" data-placeholder="Project Managers">
							<option value=""></option>
							@foreach ($managers as $itm)
							<option value="{{ $itm->departmentid }}">{{ $itm->name }}</option>
							@endforeach
						</select>

						<select name="sectorid" id="sectorid" class="select2 selectbx" data-width="190" data-placeholder="Sector">
							<option value=""></option>
							@foreach($sectors as $sector)
							<option value="{{$sector->sectorid}}">{{$sector->sectorname}}</option>
							@endforeach
						</select>						
						
					</td>
				</tr>
				<tr>
					<td style="position:relative; padding:5px!important; font-weight:600;">
						<select name="positionid" id="positionid" class="select2 selectbx" data-width="190" data-placeholder="Position">
							<option value=""></option>
							@foreach($positions as $position)
							<option value="{{$position->positionid}}">{{$position->consultantposition}}</option>
							@endforeach
						</select>

						<select name="level_id" id="level_id" class="select2 selectbx" onchange="loadData('{{ route('departmentwiseresource.html') }}')"  data-width="190" data-placeholder="Experience Level">
							<option value=""></option>
							@foreach($levels as $lvl)
							<option value="{{$lvl->experiencelevel}}">Level-{{$lvl->experiencelevel}}</option>
							@endforeach
						</select>
						<select name="format_id" id="format_id" class="select2 selectbx" onchange="loadData('{{ route('departmentwiseresource.html') }}')"  data-width="190" data-placeholder="Report Format">
							<option value="1" selected>Report Format - 1</option>
							<option value="2">Report Format - 2</option>
							<option value="3">Report Format - 3</option>
							<option value="4">Report Format - 4</option>
						</select>

						<span class="input-icon" style="position:absolute; top:2px; width:190px!important;">
							<input type="text" placeholder="Search ..." class="nav-search-input" id="pagesearch" name="pagesearch" autocomplete="off" onchange="loadData('{{ route('departmentwiseresource.html') }}')" tabindex="<?php echo $t++;?>" style="width:100%!important;" />
							<i class="ace-icon fa fa-search nav-search-icon" style="margin-top:0px;"></i>
						</span>

						<select name="projectid" id="projectid" class="select2" onchange="loadData('{{ route('departmentwiseresource.html') }}')"  data-width="190" data-placeholder="Project Name">
							<option value=""></option>
							@foreach($projects as $project)
							<option value="{{$project->projectid}}">{{$project->project_name}}</option>
							@endforeach
						</select>

					</td>
				</tr>
				
				<tbody class="tabledata">
				<tr><td class="center padding-8">--Search Record--</td></tr>
				</tbody>
				</table>
			</div>
		</form>
		</div>
	</div>
</div>



</div>
</div>


						
				</div><!-- /.col -->
			</div><!-- /.row -->
		</div><!-- /.page-content -->
	
		
	</div>
</div>

<div class="modal fade left" id="myModal" role="dialog" data-backdrop="static">
	<div class="modal-lg-dialog">
		<div class="modal-content" style="font-size:12px; width:80%; margin:0 auto; margin-top:10px; background-color:white;">
		
		</div>
	</div>
</div>

<div class="modal fade left" id="resourceModal" role="dialog" data-backdrop="static">
	<div class="modal-lg-dialog">
		<div class="modal-content resourceContent" style="font-size:12px; width:90%; margin:0 auto; margin-top:10px; background-color:white;">
		
		</div>
	</div>
</div>

<script src="{{ asset('panel/assets/js/jquery-2.1.4.min.js') }}"></script>
<script>
    var resourceReportUrl 	= "{{ route('resource.report') }}";
	var resourceAwdReportUrl= "{{ route('awdresource.report') }}";
	var vendorListUrl 		= "{{ route('vendors.by.category') }}";
    var csrfToken = "{{ csrf_token() }}";
</script>

<script src="{{ asset('panel/assets/js/resource_report.js') }}"></script>
<script>
function CloseThis()
{
	$('#resourceModal').modal('hide');
	$('.resourceContent').html('');
}
function printResourceData() {
    var printContents = document.querySelector('.printableResourceData').innerHTML;

    var printWindow = window.open('','_blank');

    printWindow.document.write(`
        <html>
        <head>
            <title>Print</title>
            <style>
                table, td {
                    border: 1px solid #000 !important;
                    border-collapse: collapse;
					width:100%!important;
					padding:2px!important;
                }
				.width-30
				{
					width:40px!important;
					text-align:center!important;
				}
				.width-100
				{
					width:100px!important;
				}
				
				.text-center
				{
					text-align:center;
				}
            </style>			
        </head>
        <body>
            ${printContents}
        </body>
        </html>
    `);

    printWindow.document.close();
    printWindow.focus();
    printWindow.print();
    printWindow.close();
}

function printData() {
    var printContents = document.querySelector('.printableData').innerHTML;

    var printWindow = window.open('','_blank');

    printWindow.document.write(`
        <html>
        <head>
            <title>Print</title>
            <style>
                table, td {
                    border: 1px solid #000 !important;
                    border-collapse: collapse;
					width:100%!important;
					padding:2px!important;
                }
				.width-30
				{
					width:40px!important;
					text-align:center!important;
				}
				
				.text-center
				{
					text-align:center;
				}
            </style>			
        </head>
        <body>
            ${printContents}
        </body>
        </html>
    `);

    printWindow.document.close();
    printWindow.focus();
    printWindow.print();
    printWindow.close();
}

function clearData()
{
	$('#vendorid').val('').trigger('change');
	$('#departmentid').val('').trigger('change');
	$('#projectid').val('').trigger('change');
	$('#managerid').val('').trigger('change');
	$('#sectorid').val('').trigger('change');
	$('#positionid').val('').trigger('change');
	$('#level_id').val('').trigger('change');
	$('#pagesearch').val('');
	setTimeout(function() { loadData('{{ route('departmentwiseresource.html') }}'); },1000);
}
$(document).ready(function() {
	$.ajaxSetup({
		headers: {
			'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
		}
	});	

	loadData('{{ route('departmentwiseresource.html') }}');
});

function loadData(r1)
{
	var categoryid		=	document.getElementById("categoryid").value;
	var vendorid		=	document.getElementById("vendorid").value;
	var departmentid	=	document.getElementById("departmentid").value;
	var projectid		=	document.getElementById("projectid").value;
	var managerid		=	document.getElementById("managerid").value;
	var sectorid		=	document.getElementById("sectorid").value;
	var positionid		=	document.getElementById("positionid").value;
	var experiencelevel	=	document.getElementById("level_id").value;
	var format_id  		= 	document.getElementById("format_id").value;
	var in_house 		= 	document.getElementById("in_house").checked;
	var pagesearch		=	document.getElementById("pagesearch").value;

	$.get(""+r1,
	{
		categoryid:categoryid,
		vendorid:vendorid,
		departmentid:departmentid,
		projectid:projectid,
		managerid:managerid,
		sectorid:sectorid,
		positionid:positionid,
		experiencelevel:experiencelevel,
		format_id:format_id,
		pagesearch:pagesearch,
		in_house:in_house
	},
	function(data, status){
		$(".tabledata").html(data);
	});
}

function setSearchingData(search)
{
	document.getElementById("pagesearch").value	=	search;
	loadData('{{ route('departmentwiseresource.html') }}');
}
function setFirm(vendorid)
{
	$('#vendorid').val(vendorid).trigger('change');
	loadData('{{ route('departmentwiseresource.html') }}');
}
function setDepartment(departmentid)
{
	$('#departmentid').val(departmentid).trigger('change');
	loadData('{{ route('departmentwiseresource.html') }}');
}
function setManager(managerid)
{
	$('#managerid').val(managerid).trigger('change');
	loadData('{{ route('departmentwiseresource.html') }}');
}
function setSector(sectorid)
{
	$('#sectorid').val(sectorid).trigger('change');
	loadData('{{ route('departmentwiseresource.html') }}');
}
function setPosition(positionid)
{
	$('#positionid').val(positionid).trigger('change');
	loadData('{{ route('departmentwiseresource.html') }}');
}

</script>

<script src="{{ asset('panel/assets/js/master.js') }}"></script>
<script src="{{ asset('panel/assets/js/datatablePaginationWithFilter.js') }}"></script>
@endsection
