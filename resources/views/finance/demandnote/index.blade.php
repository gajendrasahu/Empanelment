@extends('admin.admin_master')
@section('admin')
@php
$t=0;
@endphp

<div class="main-content">
	<div class="main-content-inner">
			<div class="page-content" style="padding:10px 15px!important;">
				<div class="ace-settings-container" id="ace-settings-container"></div>
				<div class="row">
					<div class="col-xs-12">
					<!-- PAGE CONTENT BEGINS -->
<div class="tabbable" style="padding:0px 0px!important;">
		<ul class="nav nav-tabs padding-0">
			<li class="active">
				<a data-toggle="tab" href="#home">
					<i class="green ace-icon fa fa-list-alt bigger-120 datalist" style="vertical-align:bottom;"></i> EoI's
				</a>
			</li>
			<li>
				<a data-toggle="tab" href="#demandnote_list">
					<i class="green ace-icon fa fa-list-alt bigger-120 datalist" style="vertical-align:bottom;"></i> Demand Note List
				</a>
			</li>
		</ul>

		<div class="tab-content no-border" style="min-height:100px; border-radius:0px!important;">
<div id="home" class="tab-pane in active">
		<form name="eoi_data" id="eoi_data" action="#" method="post" onsubmit="return false;">
			<div class="table-responsive">
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
			
				<table class="table table-bordered table-striped table-hover" id="tablerecords">
				<thead>
				<tr>
					<td colspan="5">
						<select name="page_size" id="page_size" class="select2" data-width="90">
							<option value="100">100</option>
							<option value="200">200</option>
							<option value="300">300</option>
							<option value="500">500</option>														
						</select>
						<select class="select2" name="project_id" id="project_id" data-width="200"	data-placeholder="Project Name">
							<option value=""></option>
							@foreach ($projects as $project)
							<option value="{{ $project->projectid }}">{{ $project->project_name }}</option>
							@endforeach
						</select>
						
						<select class="select2" name="department_id" id="department_id" data-width="200"	data-placeholder="Departments">
							<option value=""></option>
							@foreach ($departments as $itm)
								<option value="{{ $itm->userid }}">
									{{ strtoupper($itm->shortname) }}-{{ $itm->departmentname }}
								</option>
							@endforeach
						</select>
						
						<span class="input-icon" style="width:250px!important; height:38px!important; line-height:38px!important;">
							<input type="text" placeholder="Search ..." class="" id="pagesearch" name="pagesearch" autocomplete="off" tabindex="<?php echo $t++;?>" style="width:250px!important; border-radius:5px; height:38px!important; line-height:38px!important;" />
							<i class="ace-icon fa fa-search nav-search-icon padding-0"></i>
						</span>
						<button type="submit" class="btn btn-info gridbtn width-100" onclick="loadData(1,'{{ route('finance.demandnote.eoilist') }}')" tabindex="{{$t++}}"><i class="fa fa-search"></i> Search</button>
					</td>
				</tr>
				<tr class="myhead">
					<td style="width:25px;" nowrap><b>S.No.</b></td>
					<td style="width:200px;" nowrap><b>Project Name</b></td>
					<td style="width:450px;"><b>EoI Number</b></td>
					<td style="width:450px;"><b>Department Name</b></td>
					<td nowrap class="center"><b>Demand Note | Value | Received</b></td>
				</tr>
				</thead>
				<tbody class="tabledata">
				<tr><td class="center" colspan="5">--EoI List--</td></tr>
				</tbody>
				</table>
			</div>
		</form>
</div>

<div id="demandnote_list" class="tab-pane">
	<form name="demandnote_data" id="demandnote_data" action="#" method="post" onsubmit="return false;">
		<div class="table-responsive">
			<table class="table table-bordered table-striped table-hover" id="tablerecords">
			<thead>
			<tr>
				<td colspan="7">
					<select name="demand_page_size" id="demand_page_size" class="select2" data-width="90">
						<option value="100">100</option>
						<option value="200">200</option>
						<option value="300">300</option>
						<option value="500">500</option>														
					</select>
					<select class="select2" name="demand_project_id" id="demand_project_id" data-width="200"	data-placeholder="Project Name">
						<option value=""></option>
						@foreach ($projects as $project)
						<option value="{{ $project->projectid }}">{{ $project->project_name }}</option>
						@endforeach
					</select>
					
					<select class="select2" name="demand_department_id" id="demand_department_id" data-width="200"	data-placeholder="Departments">
						<option value=""></option>
						@foreach ($departments as $itm)
							<option value="{{ $itm->departmentid }}">
								{{ strtoupper($itm->shortname) }}-{{ $itm->departmentname }}
							</option>
						@endforeach
					</select>
					
					<span class="input-icon" style="width:250px!important; height:38px!important; line-height:38px!important;">
						<input type="text" placeholder="Search ..." class="" id="demand_pagesearch" name="demand_pagesearch" autocomplete="off" tabindex="<?php echo $t++;?>" style="width:250px!important; border-radius:5px; height:38px!important; line-height:38px!important;" />
						<i class="ace-icon fa fa-search nav-search-icon padding-0"></i>
					</span>
					<button type="submit" class="btn btn-info gridbtn demandNoteBtn width-100" onclick="loadDemandData(1,'{{ route('finance.demandnote.demandnotelist') }}')" tabindex="{{$t++}}">
						<i class="fa fa-search"></i> Search
					</button>
				</td>
			</tr>
			<tr class="myhead">
				<td style="width:25px;" nowrap><b>S.No.</b></td>
				<td nowrap><b>Demand Note | Date</b></td>
				<td><b>Project & Department</b></td>
				<td class="text-right"><b>Amount</b></td>
				<td class="text-right"><b>Received</b></td>				
				<td class="text-center"><b>Status</b></td>
				<td nowrap class="center"><b>Action</b></td>
			</tr>
			</thead>
			<tbody class="demandnote_tabledata">
			<tr><td class="center" colspan="7">--Demand Note List--</td></tr>
			</tbody>
			</table>
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

	function loadData(page, r1) {
		var pagesize 	= document.getElementById("page_size").value;
		var projectid 	= document.getElementById("project_id").value;
		var departmentid= document.getElementById("department_id").value;
		var pagesearch 	= document.getElementById("pagesearch").value;
		
		$.get("" + r1,
			{
				page: page,
				pagesize: pagesize,
				pagesearch: pagesearch,
				departmentid: departmentid,
				projectid:projectid
			},
			function (data, status) {
				$(".tabledata").html(data);
				const paginationLinks = document.querySelectorAll('.pagination a');
				paginationLinks.forEach(link => {
					link.onclick = function (event) {
						event.preventDefault(); // Prevent default link behavior
						const page = this.getAttribute('href').split('page=')[1]; // Extract page number from URL
						const url = '{{ route('finance.demandnote.eoilist') }}'; // URL generated by Laravel route
						loadData(page, url); // Call custom function with page number and URL
					};
				});	
				
			});
	}

	function loadDemandData(page, r1)
	{
		var pagesize 	= document.getElementById("demand_page_size").value;
		var projectid 	= document.getElementById("demand_project_id").value;
		var departmentid= document.getElementById("demand_department_id").value;
		var pagesearch 	= document.getElementById("demand_pagesearch").value;

		$.get(""+r1,
		{
			page:page,
			pagesize:pagesize,
			pagesearch:pagesearch,
			departmentid:departmentid,
			projectid:projectid
		},
		function (data, status) {
			$(".demandnote_tabledata").html(data);
			
			const paginationLinks	=	document.querySelectorAll('.pagination a');
			
			paginationLinks.forEach(link => {
				link.onclick = function (event) {
					event.preventDefault(); // Prevent default link behavior
					const page = this.getAttribute('href').split('page=')[1]; // Extract page number from URL
					const url = '{{ route('finance.demandnote.demandnotelist') }}'; // URL generated by Laravel route
					loadDemandData(page, url); // Call custom function with page number and URL
				};
			});	
			
		});
	}


$(document).on('click','.btnCancelDemandNote',function(){

    var id = $(this).data('id');

    bootbox.prompt({
        title : "Please enter cancellation reason",
        inputType : 'textarea',
        callback : function(result){
            if(result===null)
            {
                return;
            }

            result = $.trim(result);

            if(result=="")
            {
                bootbox.alert("Cancellation reason is required.");
                return false;
            }
            bootbox.confirm("Are you sure you want to cancel this Demand Note?",function(confirm){

                if(confirm)
                {
                    cancelDemandNote(id,result);
                }
            });
        }

    });

});

function cancelDemandNote(id,reason)
{

    $.ajax({
        url : "{{ url('finance/canceldemandnote') }}/"+id,
        type : "POST",
        data : {
            _token : "{{ csrf_token() }}",
            reason : reason
        },
        success:function(response)
        {
            if(response.status==1)
            {
                bootbox.alert(response.message,function(){
                    window.location.reload();
                });
            }
            else
            {
                bootbox.alert(response.message);
            }

        },
        error:function()
        {
            bootbox.alert("Something went wrong.");
        }
    });
}
</script>

<script src="{{ asset('panel/assets/js/datatablePaginationWithFilter.js') }}"></script>
@endsection