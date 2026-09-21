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
			<div class="eoi-section-header" onclick="loadData('{{ route('orderwiseresource.html') }}')" style="cursor:pointer; padding:8px 8px 16px 8px; position:relative;">
				<div style="font-size:20px; font-weight:600; color:#2c3e50;">
					Work Order Wise Resource Detail
				</div>
				<span class="status-bds">
				  Status Abbreviations
				  <span class="bds bds-pending">P - Pending</span>
				  <span class="bds bds-active">A - Active</span>
				  <span class="bds bds-extended">E - Extended</span>
				  <span class="bds bds-released">R - Released</span>
				</span>
			</div>

		<div class="tab-content no-border" style="min-height:100px; padding:0px 10px;">
@permission('orderwiseresource.html')
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
		<form name="updateresource" id="updateresource" action="#" method="post" onsubmit="return false;">
			@csrf
			<div class="table-responsive">
				<table class="table table-bordered">
				<thead>
				<tr>
					<td style="position:relative; padding:5px!important;">
				
						<select class="select2" name="categoryid" id="categoryid" onKeyPress="return OnKeyPress(this, event)" onchange="loadData('{{ route('orderwiseresource.html') }}');GetTiersWithId(this.value,'{{ route('tiers.list') }}','tierid')" data-width="200" data-placeholder="Category Name">
							<option value="">Category Name</option>
							@foreach ($category as $itm)
							<option value="{{ $itm->categoryid }}" @if($itm->categoryid==2) selected @endif>{{ $itm->jobcategory }}</option>
							@endforeach								
						</select>
						<select class="select2" name="vendorid" id="vendorid" onKeyPress="return OnKeyPress(this, event)" onchange="loadData('{{ route('orderwiseresource.html') }}');" data-width="200" data-placeholder="Firm Name">
							<option value=""></option>
							@foreach ($vendors as $itm)
							<option value="{{ $itm->vendorid }}">{{ $itm->companyname }}</option>
							@endforeach
						</select>
						<select class="select2" name="departmentid" id="departmentid" onKeyPress="return OnKeyPress(this, event)" onchange="loadData('{{ route('orderwiseresource.html') }}');" data-width="200" data-placeholder="Department / Project Manager">
							<option value=""></option>
							<optgroup label="Department">
							@foreach ($departments as $itm)
							@if($itm->isdepartment==1)
							<option value="{{ $itm->departmentid }}">{{ $itm->shortname }}-{{ $itm->departmentname }}</option>
							@endif
							@endforeach
							<optgroup label="Project Manager">
							@foreach ($departments as $itm)
							@if($itm->ispm==1)
							<option value="{{ $itm->departmentid }}">{{ $itm->departmentname }}</option>
							@endif
							@endforeach
						</select>

						<select name="order_type" id="order_type" class="select2 selectbx" onchange="loadData('{{ route('orderwiseresource.html') }}')"  data-width="24%" data-placeholder="All Order">
							<option value="">All Order</option>
							<option value="1" selected>Active Orders</option>
							<option value="2">Extended</option>
							<option value="3">Expired</option>
							<option value="4">Expiring within 60 days</option>
						</select>
						
<button type="button" class="btn btn-info gridbtn" onClick="loadData('{{ route('orderwiseresource.html') }}')" style="position:absolute; right:5px; width:120px;">
	<i class="fa fa-database"></i> Search Data
</button>

						
					</td>
				</tr>
				<tr>
					<td style="position:relative; padding:5px!important;">
						<select name="sectorid" id="sectorid" class="select2 selectbx" onchange="loadData('{{ route('orderwiseresource.html') }}')"  data-width="200" data-placeholder="Sector">
							<option value=""></option>
							@foreach($sectors as $sector)
							<option value="{{$sector->sectorid}}">{{$sector->sectorname}}</option>
							@endforeach
						</select>
						<select name="positionid" id="positionid" class="select2 selectbx" onchange="loadData('{{ route('orderwiseresource.html') }}')"  data-width="200" data-placeholder="Position">
							<option value=""></option>
							@foreach($positions as $position)
							<option value="{{$position->positionid}}">{{$position->consultantposition}}</option>
							@endforeach
						</select>
						<select name="level_id" id="level_id" class="select2 selectbx" onchange="loadData('{{ route('orderwiseresource.html') }}')"  data-width="200" data-placeholder="Experience Level">
							<option value=""></option>
							@foreach($levels as $lvl)
							<option value="{{$lvl->experiencelevel}}">Level-{{$lvl->experiencelevel}}</option>
							@endforeach
						</select>

						<span class="input-icon" style="width:30%!important; margin-top:0px;">
							<input type="text" placeholder="Search ..." class="nav-search-input" id="pagesearch" name="pagesearch" autocomplete="off" onchange="loadData('{{ route('orderwiseresource.html') }}')" tabindex="<?php echo $t++;?>" style="width:80%!important;" />
							<i class="ace-icon fa fa-search nav-search-icon" style="margin-top:0px;"></i>
						</span>
						@permission('export.orderwiseresourcedata')
						<button type="button" class="btn btn-info gridbtn" onclick="ExportData('{{route('export.orderwiseresourcedata')}}')" style="position:absolute; right:5px; width:120px;">
							<i class="fa fa-database"></i> Export Data
						</button>
						@endpermission
						
					</td>
				</tr>
				</thead>
				<tbody class="tabledata">
				<tr><td class="center padding-8">--Search Record--</td></tr>
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

<div class="modal fade left" id="myModal" role="dialog" data-backdrop="static">
	<div class="modal-lg-dialog">
		<div class="modal-content" style="font-size:12px; width:80%; margin:0 auto; margin-top:10px; background-color:white;">
		
		</div>
	</div>
</div>
<div class="modal fade left" id="resourceModal" role="dialog" data-backdrop="static">
	<div class="modal-lg-dialog">
		<div class="modal-content resourceDetail" style="font-size:12px; width:90%; margin:0 auto; margin-top:10px; background-color:white;">
		
		</div>
	</div>
</div>

<script src="{{ asset('panel/assets/js/jquery-2.1.4.min.js') }}"></script>
<script>

function CloseThis()
{
	$('#resourceModal').modal('hide');
	$('.resourceDetail').html('');
}

function viewResourceDetail(r1)
{
    let formData = {
        _token:$('meta[name="csrf-token"]').attr('content'),
    };

    $.ajax({
        url: ""+r1,
        type: "POST",
        data: formData,
        success: function (response)
		{
            if(response.status == 200)
			{
				$('#resourceModal').modal('show');
				$('.resourceDetail').html(response.htmlData);
            }
			else
			{
               bootbox.alert('Something went wrong: ' + response.message);
            }
        },
        error: function (xhr) {
            if(xhr.responseJSON && xhr.responseJSON.errors)
            {
                var errors = xhr.responseJSON.errors;
                var allMessages = '';

                $.each(errors, function(field, messages) {
                    $.each(messages, function(index, msg) {
                        allMessages += msg + '<br>';
                    });
                });
                bootbox.alert(allMessages);
            }
            else
            {
                bootbox.alert('Something went wrong');
            }
        }
    });	
}

$(document).ready(function() {
	$.ajaxSetup({
		headers: {
			'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
		}
	});	

	loadData('{{ route('orderwiseresource.html') }}');
});

function ExportData(url)
{

	var categoryid		=	document.getElementById("categoryid").value;
	var vendorid		=	document.getElementById("vendorid").value;
	var departmentid	=	document.getElementById("departmentid").value;
	var sectorid		=	document.getElementById("sectorid").value;
	var positionid		=	document.getElementById("positionid").value;
	var experiencelevel	=	document.getElementById("level_id").value;
	var order_type  	= 	document.getElementById("order_type").value;
	var pagesearch		=	document.getElementById("pagesearch").value;

	var params	=	new URLSearchParams({
						categoryid:categoryid,
						vendorid:vendorid,
						departmentid:departmentid,
						sectorid:sectorid,
						positionid:positionid,
						experiencelevel:experiencelevel,
						order_type:order_type,
						pagesearch:pagesearch
					});
	window.location.href = url + '?' + params.toString();		
	
}
function loadData(r1)
{
	var categoryid		=	document.getElementById("categoryid").value;
	var vendorid		=	document.getElementById("vendorid").value;
	var departmentid	=	document.getElementById("departmentid").value;
	var sectorid		=	document.getElementById("sectorid").value;
	var positionid		=	document.getElementById("positionid").value;
	var experiencelevel	=	document.getElementById("level_id").value;
	var order_type  	= 	document.getElementById("order_type").value;
	var pagesearch		=	document.getElementById("pagesearch").value;

	$.get(""+r1,
	{
		categoryid:categoryid,
		vendorid:vendorid,
		departmentid:departmentid,
		sectorid:sectorid,
		positionid:positionid,
		experiencelevel:experiencelevel,
		order_type:order_type,
		pagesearch:pagesearch,
	},
	function(data, status){
		$(".tabledata").html(data);
	});
}


$(document).on('click', '.updateResource', function(e){
	
	var deployment_status	=	document.getElementById("deployment_status").value;
	var released_date		=	document.getElementById("released_date").value;
	if(deployment_status=='Released' && released_date=='')
	{
		bootbox.alert("Release Date is required when Deployment Status is Released.");
		return false;
	}
	if(deployment_status!='Released' && released_date!='')
	{
		bootbox.alert("If the release date is set, then the deployment status must be Released.");
		return false;
	}
	bootbox.confirm('Do you confirm this update?',function(result){
		if(result)
		{
		
			var btn = $(this);
			btn.prop('disabled', true);

			$.ajax({
				url: $('#updateResourceDetail').attr('action'),
				type: 'POST',
				data: $('#updateResourceDetail').serialize(),
				dataType: 'json',

				success: function(response){

					btn.prop('disabled', false);

					if(response.status==200)
					{
						bootbox.alert(response.message);
						setTimeout(function(){ loadData('{{ route('orderwiseresource.html') }}'); CloseThis() },2000);
					}
					else
					{
						bootbox.alert(response.message);
					}
				},

				error: function(xhr){

					btn.prop('disabled', false);

					if(xhr.status === 422)
					{
						let errors = xhr.responseJSON.errors;
						let msg = '';

						$.each(errors, function(key, value){
							msg += value[0] + "\n";
						});

						bootbox.alert(msg);
					}
					else
					{
						bootbox.alert('Something went wrong. Please try again.');
					}
				}
			});
			
		}
	});
});

</script>
<script src="{{ asset('panel/assets/js/master.js') }}"></script>
<script src="{{ asset('panel/assets/js/datatablePaginationWithFilter.js') }}"></script>
@endsection
