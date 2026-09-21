@extends('admin.admin_master')
@section('admin')
	@php
		$t = 1;
		
	@endphp
	<div class="main-content">
	<style>
.select2-results__group {
    background-color: #f0f0f0;
    color: #000;
    font-weight: bold;
    padding: 5px;
}

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

	</style>
		<div class="main-content-inner">
			<div class="page-content" style="padding:10px 15px!important;">
				<div class="ace-settings-container" id="ace-settings-container"></div>
				<div class="row">
					<div class="col-xs-12">
						<!-- PAGE CONTENT BEGINS -->
						<div class="tabbable animated bounceInUp" style="padding:0px 0px!important; position:relative;">							
								<div class="eoi-section-header" onclick="loadData(1,'{{ route('summary.html') }}')">

									<div style="font-size:20px; font-weight:600; color:#2c3e50;">
										EoI & Order Value
									</div>							
								</div>
								
							
							<div class="tab-content no-border" style="min-height:100px; padding:0px 10px;">

									<div id="recordlist" class="tab-pane in active" style="padding:0px 0px!important;">
										<div class="row" style="padding:0px;">
											<div class="col-xs-12">
												<form name="pagedata" id="pagedata" action="#" method="post"
													onsubmit="return false;">
													@csrf
<div class="table-responsive">
<table class="table-bordered pd-10 mytable">
	<thead>
		<tr>
			<td>
				<select class="select2" name="project_id" id="project_id" data-placeholder="Project Name" data-width="180">
					<option value=""></option>
					@foreach($projects as $project)
					<option value="{{$project->projectid}}">{{$project->project_name}}</option>
					@endforeach
				</select>
			
				<select class="select2" name="category_id" id="category_id" data-placeholder="All" data-width="180">
					<option value=""></option>
					@foreach($category as $cat)
					<option value="{{$cat->categoryid}}" @if($cat->categoryid==2) selected @endif>{{$cat->jobcategory}}</option>
					@endforeach
				</select>

				<select class="select2" name="vendor_id" id="vendor_id" data-placeholder="Firm Name" data-width="180">
					<option value=""></option>
					@foreach($firms as $item)
					<option value="{{$item->vendorid}}">{{$item->shortname}} - {{$item->companyname}}</option>
					@endforeach
				</select>

				<select class="select2" name="department_id" id="department_id" data-placeholder="Department" data-width="180">
					<option value=""></option>
					@foreach($departments as $item)
					<option value="{{$item->userid}}">{{$item->shortname}} - {{$item->departmentname}}</option>
					@endforeach
				</select>

				<select class="select2" name="manager_id" id="manager_id" data-placeholder="Project Manager" data-width="180">
					<option value=""></option>
					@foreach($managers as $item)
					<option value="{{$item->userid}}">{{$item->departmentname}}</option>
					@endforeach
				</select>
			</td>
		</tr>
		<tr>
			<td style="position:relative;">
				<select class="select2" name="record_type" id="record_type" data-placeholder="Record Type" data-width="250">
					<option value="1">All Active Orders</option>
					<option value="2">Before 1 July 2025 Active Orders</option>
					<option value="8">Before 1 July 2025 All Orders</option>
					<option value="3">After 1 July 2025 - All</option>
					<option value="4">After 1 July 2025 - Active</option>
					<option value="5">After 1 July 2025 - Extended</option>
					<option value="6">After 1 July 2025 - Expired</option>
					<option value="7">All Order</option>
				</select>
				<!--
				<div style="position:absolute; right:375px; top:10px;">
					<button type="button" class="btn btn-info gridbtn" style="width:100px; padding:0px!important;" onclick="showData('{{route('eoiordervalue.html')}}')" style="width:120px;">
						<i class="fa fa-database"></i> Show Data
					</button>
				</div>
				-->
				<!--
				<div style="position:absolute; right:130px; top:10px;">
					<button type="button" class="btn btn-info gridbtn" style="width:275px; padding:0px!important;" onclick="showData('{{route('firmwise.eoiordervalue')}}')" style="width:120px;">
						<i class="fa fa-database"></i> Firm Wise (EoI & Order Data)
					</button>
				</div>
				-->
				<div style="position:absolute; right:130px; top:10px;">
					<button type="button" class="btn btn-info gridbtn" style="width:100px; padding:0px!important;" onclick="showData('{{route('firmwise.testdata')}}')">
						<i class="fa fa-eye"></i> View Data
					</button>
				</div>

				<div style="position:absolute; right:20px; top:10px;">
					<button type="button" class="btn btn-info gridbtn" style="width:100px; padding:0px!important;" onclick="exportData('{{route('firmwise.testdata.export')}}')">
						<i class="fa fa-download"></i> Export
					</button>
				</div>
				<!--
				<div style="position:absolute; right:490px; top:10px;">
					<button type="button" class="btn btn-info gridbtn" style="width:100px; padding:0px!important;" onclick="showData('{{route('firmwise.eoiorderresource')}}')" style="width:120px;">
						<i class="fa fa-database"></i> With Resource
					</button>
				</div>
				-->

				
			</td>
		</tr>		
	</thead>
	<tbody class="tabledata">
		<tr><td class="center"></td></tr>
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

<div class="modal fade left" id="recordModal" role="dialog" data-backdrop="static">
	<div class="modal-lg-dialog">
		<div class="modal-content recordContent" style="font-size:12px; width:100%; margin:0 auto; margin-top:10px; background-color:white;">
		
		</div>
	</div>
</div>

<script src="{{ asset('panel/assets/js/jquery-2.1.4.min.js') }}"></script>

<script src="{{ asset('panel/assets/js/undeployed_resource_report.js') }}"></script>
<script>
$(document).ready(function() {
	$.ajaxSetup({
		headers: {
			'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
		}
	});
	//loadData(1, '{{ route('eoiordervalue.html') }}');
});
function CloseThis()
{
	$('#recordModal').modal('hide');
	$('.recordContent').html('');
}

function loadData(page, r1)
{
	var category_id = 	document.getElementById("category_id").value;
	var project_id 	= 	document.getElementById("project_id").value;
	var department_id= 	document.getElementById("department_id").value;
	var manager_id	= 	document.getElementById("manager_id").value;
	var vendor_id	= 	document.getElementById("vendor_id").value;
	var pagesearch	= 	document.getElementById("pagesearch").value;

	$.get("" + r1,
	{
		category_id:category_id,
		project_id:project_id,
		department_id:department_id,
		manager_id:manager_id,
		vendor_id:vendor_id,
		pagesearch:pagesearch
	},
	function (data, status) {
		$(".tabledata").html(data);
	});
}



function showData(rl)
{
	var category_id = 	document.getElementById("category_id").value;
	var project_id 	= 	document.getElementById("project_id").value;
	var department_id= 	document.getElementById("department_id").value;
	var manager_id	= 	document.getElementById("manager_id").value;
	var vendor_id	= 	document.getElementById("vendor_id").value;
	var record_type	= 	document.getElementById("record_type").value;

	let formData = new FormData();
	formData.append('category_id',category_id);
	formData.append('project_id',project_id);
	formData.append('department_id',department_id);
	formData.append('manager_id',manager_id);
	formData.append('vendor_id',vendor_id);
	formData.append('record_type',record_type);
	$.ajax({
		url: rl,
		method: 'POST',
		data: formData,
		processData: false,
		contentType: false,
		success: function(response) {
			if (response.status === 200)
			{
				$('#recordModal').modal('show');
				$('.recordContent').html(response.html);
			}
			else
			{
				bootbox.alert('Something went wrong: ' + response.message);
			}
		},
		error: function(xhr) {
			bootbox.alert('An error occurred while saving the data.');
		}
	});

}
function exportData(rl)
{
    var form = document.createElement("form");
    form.method = "POST";
    form.action = rl;

    var csrf = document.createElement("input");
    csrf.type = "hidden";
    csrf.name = "_token";
    csrf.value = document.querySelector('meta[name="csrf-token"]').content;
    form.appendChild(csrf);


    var fields = {
        category_id: document.getElementById("category_id").value,
        project_id: document.getElementById("project_id").value,
        department_id: document.getElementById("department_id").value,
        manager_id: document.getElementById("manager_id").value,
        vendor_id: document.getElementById("vendor_id").value,
        record_type: document.getElementById("record_type").value
    };


    Object.keys(fields).forEach(function(key){

        var input = document.createElement("input");
        input.type = "hidden";
        input.name = key;
        input.value = fields[key];

        form.appendChild(input);
    });


    document.body.appendChild(form);
    form.submit();
    document.body.removeChild(form);
}
function printResourceData() {
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

</script>
<script src="{{ asset('panel/assets/js/master.js') }}"></script>
<script src="{{ asset('panel/assets/js/datatablePaginationWithFilter.js') }}"></script>
@endsection
