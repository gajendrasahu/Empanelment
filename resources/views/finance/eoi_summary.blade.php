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
										EoI Summary
									</div>

									<span style="position:absolute; right:5px; top:15px;">
									  <input type="checkbox" name="in_house" id="in_house" style="vertical-align:text-top;">
									  <label for="in_house">In House Consultants</label>
									</span>
									
								</div>
								
							
							<div class="tab-content no-border" style="min-height:100px; padding:0px 10px;">

								@if(in_array(2, Session::get('actions')) || in_array(3, Session::get('actions')) || in_array(12, Session::get('actions')))
									<div id="recordlist" class="tab-pane in active" style="padding:0px 0px!important;">
										<div class="row" style="padding:0px;">
											<div class="col-xs-12">
												<form name="pagedata" id="pagedata" action="#" method="post"
													onsubmit="return false;">
													@csrf
													<div class="table-responsive">
<div class="table-responsive">
<table class="table table-bordered pd-10">
	<thead>
		<tr>
			<td colspan="12">
				<select class="select2" name="project_id" id="project_id" data-placeholder="Project Name" data-width="195">
					<option value=""></option>
					@foreach($projects as $project)
					<option value="{{$project->projectid}}">{{$project->project_name}}</option>
					@endforeach
				</select>
			
				<select class="select2" name="categoryid" id="categoryid" data-placeholder="All" data-width="195" onchange="getVendors(this.value)">
					<option value=""></option>
					@foreach($category as $cat)
					<option value="{{$cat->categoryid}}" @if($cat->categoryid==2) selected @endif>{{$cat->jobcategory}}</option>
					@endforeach
				</select>

				<select class="select2" name="vendorid" id="vendorid" data-placeholder="Firm Name" data-width="195">
					<option value=""></option>
					@foreach($firms as $item)
					<option value="{{$item->vendorid}}">{{$item->shortname}} - {{$item->companyname}}</option>
					@endforeach
				</select>

				<select class="select2" name="departmentid" id="departmentid" data-placeholder="Department" data-width="195">
					<option value=""></option>
					@foreach($departments as $item)
					<option value="{{$item->departmentid}}">{{$item->shortname}} - {{$item->departmentname}}</option>
					@endforeach
				</select>

				<select class="select2" name="managerid" id="managerid" data-placeholder="Project Manager" data-width="195">
					<option value=""></option>
					@foreach($managers as $item)
					<option value="{{$item->departmentid}}">{{$item->departmentname}}</option>
					@endforeach
				</select>
			</td>
		</tr>
		<tr>
			<td style="position:relative;">
				<span class="input-icon" style="position:absolute; top:2px; width:395px!important;">
					<input type="text" placeholder="Search ..." class="nav-search-input" id="pagesearch" name="pagesearch" autocomplete="off" tabindex="<?php echo $t++;?>" style="width:100%!important;" />
					<i class="ace-icon fa fa-search nav-search-icon" style="margin-top:0px;"></i>
				</span>
				<select class="select2" name="record_type" id="record_type" data-placeholder="Report Format" data-width="195">
					<option value="1" selected>Active EoIs</option>
					<option value="2">All EoIs</option>
					<option value="3">Closed EoIs</option>
				</select>

				<select class="select2" name="format_id" id="format_id" data-placeholder="Report Format" data-width="195">
					<option value="1" selected>Format 1</option>
					<option value="2">Format 2</option>
				</select>

				<div style="position:absolute; right:20px; top:10px;">
					<button type="button" class="btn btn-info gridbtn" style="width:100px; padding:0px!important;" onclick="showData('{{route('show.summarydetail')}}')" style="width:120px;">
						<i class="fa fa-database"></i> Show Data
					</button>
				</div>
			
			</td>
		</tr>
	</thead>
	<tbody class="tabledata">
		<tr><td class="center" colspan="12"></td></tr>
	</tbody>
</table>
</div>
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

<div class="modal fade left" id="recordModal" role="dialog" data-backdrop="static">
	<div class="modal-lg-dialog">
		<div class="modal-content recordContent" style="font-size:12px; width:100%; margin:0 auto; margin-top:10px; background-color:white;">
		
		</div>
	</div>
</div>

<script src="{{ asset('panel/assets/js/jquery-2.1.4.min.js') }}"></script>
<script>
	var vendorListUrl 		= "{{ route('vendors.by.category') }}";
    var csrfToken = "{{ csrf_token() }}";
</script>

<script src="{{ asset('panel/assets/js/undeployed_resource_report.js') }}"></script>
<script>
function CloseThis()
{
	$('#recordModal').modal('hide');
	$('.recordContent').html('');
}
function clearData()
{
	$('#vendorid').val('').trigger('change');
	$('#departmentid').val('').trigger('change');
	$('#managerid').val('').trigger('change');
	$('#format_id').val('').trigger('change');
}
$(document).ready(function() {
	$.ajaxSetup({
		headers: {
			'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
		}
	});	
});

function showData(rl)
{
	var project_id	=	document.getElementById("project_id").value;
	var categoryid	=	document.getElementById("categoryid").value;
	var vendorid	=	document.getElementById("vendorid").value;
	var departmentid=	document.getElementById("departmentid").value;
	var managerid	=	document.getElementById("managerid").value;
	var format_id  	= 	document.getElementById("format_id").value;
	var in_house 	= 	document.getElementById("in_house").checked;
	var pagesearch  = 	document.getElementById("pagesearch").value;
	var record_type = 	document.getElementById("record_type").value;

	let formData = new FormData();
	formData.append('project_id',project_id);
	formData.append('categoryid',categoryid);
	formData.append('vendorid',vendorid);
	formData.append('departmentid',departmentid);
	formData.append('managerid',managerid);
	formData.append('format_id',format_id);
	formData.append('pagesearch',pagesearch);
	formData.append('record_type',record_type);
	formData.append('in_house',in_house);
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

</script>
<script src="{{ asset('panel/assets/js/master.js') }}"></script>
<script src="{{ asset('panel/assets/js/datatablePaginationWithFilter.js') }}"></script>
@endsection
