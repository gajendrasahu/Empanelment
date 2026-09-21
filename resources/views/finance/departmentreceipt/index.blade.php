@extends('admin.admin_master')
@section('admin')
<link rel="stylesheet" href="{{ asset('panel/assets/css/finance.css') }}" />
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
			<i class="green ace-icon mt-3 fa fa-list-alt bigger-120"></i> Demand Note List
		</a>
	</li>
	<li>
		<a data-toggle="tab" href="#departmentreceipt">
			<i class="green ace-icon mt-3 fa fa-list-alt bigger-120" style="vertical-align:bottom;"></i> Department Receipt List
		</a>
	</li>
	<li>
		<a data-toggle="tab" href="#departmentinvoicereceipt">
			<i class="green ace-icon mt-3 fa fa-list-alt bigger-120" style="vertical-align:bottom;"></i> Create Invoice
		</a>
	</li>
</ul>

<div class="tab-content no-border" style="min-height:100px; border-radius:0px!important;">
<div id="home" class="tab-pane in active">
	<form name="demandnote_data" id="demandnote_data" action="#" method="post" onsubmit="return false;">
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
		@if(Session::has('error'))
		
		<div class="col-sm-12 mt-10">
			<div class="alert alert-block alert-danger">
				<button type="button" class="close" data-dismiss="alert">
					<i class="ace-icon fa fa-times"></i>
				</button>
				{{ Session::get('error') }}
			</div>
		</div>
		
		@endif
	
		<div class="table-responsive">
			<table class="table table-bordered table-striped table-hover" id="tablerecords">
			
			<tr>
				<td colspan="8">
					<select name="demand_page_size" id="demand_page_size" class="select2" data-width="95">
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
					<button type="submit" class="btn btn-info gridbtn demandNoteBtn width-100" onclick="loadDemandData(1,'{{ route('finance.departmentreceipt.demandnotelist') }}')" tabindex="{{$t++}}">
						<i class="fa fa-search"></i> Search
					</button>
				</td>
			</tr>
			<tr class="myheadbg">
				<td style="width:25px;" nowrap><b>S.No.</b></td>
				<td nowrap><b>Demand Note | Date</b></td>
				<td><b>Project & Department / Manager</b></td>
				<td class="text-right"><b>Demand</b></td>
				<td class="text-right"><b>Received</b></td>
				<td class="text-right"><b>Balance</b></td>
				<td class="text-center"><b>Status</b></td>
				<td nowrap class="center"><b>Action</b></td>
			</tr>
			
			<tbody class="demandnote_tabledata">
			<tr><td class="center" colspan="8">--Demand Note List--</td></tr>
			</tbody>
			</table>
		</div>
	</form>
</div>


<div id="departmentreceipt" class="tab-pane">
	<form name="receipt_data" id="receipt_data" action="#" method="post" onsubmit="return false;">
		<div class="table-responsive">
			<table class="mytable pd-10" style="border:1px solid #ddd!important; border-collapse:collapse!important;" border="1">
			
			<tr>
				<td colspan="10" style="padding:5px!important;">
					<select name="receipt_page_size" id="receipt_page_size" class="select2" data-width="95">
						<option value="100">100</option>
						<option value="200">200</option>
						<option value="300">300</option>
						<option value="500">500</option>														
					</select>
					<select class="select2" name="receipt_project_id" id="receipt_project_id" data-width="200"	data-placeholder="Project Name">
						<option value=""></option>
						@foreach ($projects as $project)
						<option value="{{ $project->projectid }}">{{ $project->project_name }}</option>
						@endforeach
					</select>
					
					<select class="select2" name="receipt_department_id" id="receipt_department_id" data-width="200"	data-placeholder="Departments">
						<option value=""></option>
						@foreach ($departments as $itm)
							<option value="{{ $itm->departmentid }}">
								{{ strtoupper($itm->shortname) }}-{{ $itm->departmentname }}
							</option>
						@endforeach
					</select>
					
					<span class="input-icon" style="width:250px!important; height:38px!important; line-height:38px!important;">
						<input type="text" placeholder="Search ..." class="" id="receipt_pagesearch" name="receipt_pagesearch" autocomplete="off" tabindex="<?php echo $t++;?>" style="width:250px!important; border-radius:5px; height:38px!important; line-height:38px!important;" />
						<i class="ace-icon fa fa-search nav-search-icon padding-0"></i>
					</span>
					<button type="submit" class="btn btn-info gridbtn receiptNoteBtn width-100" style="margin-top:-5px!important;" onclick="loadReceiptData(1,'{{ route('finance.departmentreceipt.receiptlist') }}')" tabindex="{{$t++}}">
						<i class="fa fa-search"></i> Search
					</button>
				</td>
			</tr>
			
		
			<tbody class="receipt_tabledata">
			<tr><td class="center" colspan="10">--Department Receipt List--</td></tr>
			</tbody>
			</table>
		</div>
	</form>
</div>

<div id="departmentinvoicereceipt" class="tab-pane">
	<form name="invoice_receipt_data" id="invoice_receipt_data" action="#" method="post" onsubmit="return false;">
		<div class="table-responsive">
			<table class="mytable pd-10" style="border:1px solid #ddd!important; border-collapse:collapse!important;" border="1">
			
			<tr>
				<td colspan="8" style="padding:5px!important;">
					<select name="receipt_list_page_size" id="receipt_list_page_size" class="select2" data-width="95">
						<option value="100">100</option>
						<option value="200">200</option>
						<option value="300">300</option>
						<option value="500">500</option>														
					</select>
				
					<select class="select2" name="receipt_list_department_id" id="receipt_list_department_id" data-width="200"	data-placeholder="Departments">
						<option value=""></option>
						@foreach ($departments as $itm)
							<option value="{{ $itm->departmentid }}">
								{{ strtoupper($itm->shortname) }}-{{ $itm->departmentname }}
							</option>
						@endforeach
					</select>
					
					<span class="input-icon" style="width:250px!important; height:38px!important; line-height:38px!important;">
						<input type="text" placeholder="Search ..." class="" id="receipt_list_pagesearch" name="receipt_list_pagesearch" autocomplete="off" tabindex="<?php echo $t++;?>" style="width:250px!important; border-radius:5px; height:38px!important; line-height:38px!important;" />
						<i class="ace-icon fa fa-search nav-search-icon padding-0"></i>
					</span>
					<button type="button" class="btn btn-info gridbtn width-100" style="margin-top:-5px!important;" onclick="loadReceiptForInvoice(1,'{{ route('finance.departmentreceipt.receipts') }}')" tabindex="{{$t++}}">
						<i class="fa fa-search"></i> Search
					</button>
				</td>
			</tr>
			<tr class="myheadbg">
				<td style="width:25px;" nowrap><b>S.No.</b></td>
				<td nowrap class="text-center"><b>Select</b></td>
				<td><b>Receipt Number</b></td>
				<td class="text-center"><b>Date</b></td>
				<td><b>Demand Note</b></td>
				<td class="text-right"><b>Gross Receipt</b></td>
				<td class="text-right"><b>Invoiced</b></td>
				<td class="text-right"><b>Balance</b></td>
			</tr>
			
		
			<tbody class="receipt_list_tabledata">
			<tr><td class="center" colspan="8">--Receipt List--</td></tr>
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
	function loadReceiptForInvoice(page, r1)
	{
		
		var pagesize 	=	document.getElementById("receipt_list_page_size").value;
		var departmentid= 	document.getElementById("receipt_list_department_id").value;
		var pagesearch 	= 	document.getElementById("receipt_list_pagesearch").value;
		if(!departmentid)
		{
			bootbox.alert('Department selection is required to get the receipt list. Please select a department.');
			return false;
		}
		$.get(""+r1,
		{
			page:page,
			pagesize:pagesize,
			pagesearch:pagesearch,
			departmentid:departmentid
		},
		function (data, status) {
			$(".receipt_list_tabledata").html(data);
			
			const paginationLinks	=	document.querySelectorAll('.pagination a');
			
			paginationLinks.forEach(link => {
				link.onclick = function (event) {
					event.preventDefault(); // Prevent default link behavior
					const page = this.getAttribute('href').split('page=')[1]; // Extract page number from URL
					const url = '{{ route('finance.departmentreceipt.receipts') }}'; // URL generated by Laravel route
					loadReceiptForInvoice(page, url); // Call custom function with page number and URL
				};
			});

			
		});
	}

	function loadReceiptData(page, r1)
	{
		var pagesize 	= document.getElementById("receipt_page_size").value;
		var projectid 	= document.getElementById("receipt_project_id").value;
		var departmentid= document.getElementById("receipt_department_id").value;
		var pagesearch 	= document.getElementById("receipt_pagesearch").value;
		
		$.get(""+r1,
		{
			page:page,
			pagesize:pagesize,
			pagesearch:pagesearch,
			departmentid:departmentid,
			projectid:projectid
		},
		function (data, status) {
			$(".receipt_tabledata").html(data);
			
			const paginationLinks	=	document.querySelectorAll('.pagination a');
			
			paginationLinks.forEach(link => {
				link.onclick = function (event) {
					event.preventDefault(); // Prevent default link behavior
					const page = this.getAttribute('href').split('page=')[1]; // Extract page number from URL
					const url = '{{ route('finance.departmentreceipt.receiptlist') }}'; // URL generated by Laravel route
					loadReceiptData(page, url); // Call custom function with page number and URL
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
					const url = '{{ route('finance.departmentreceipt.demandnotelist') }}'; // URL generated by Laravel route
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

$(document).on('click','.btnCancelDepartmentPayment',function()
{
	let paymentId	=	$(this).data('id');

	bootbox.confirm({
		title: 'Cancel Department Payment',
		message: 'Are you sure you want to cancel this Department Payment?',
		buttons: {
			confirm: {
				label: 'Yes, Cancel',
				className: 'btn-danger'
			},
			cancel: {
				label: 'No',
				className: 'btn-secondary'
			}
		},
		callback: function(result)
		{
			if(result)
			{
				$.ajax({
					url: "{{ route('finance.departmentreceipt.cancel','ID') }}".replace('ID',paymentId),
					type: 'POST',
					data: {
						_token: "{{ csrf_token() }}"
					},
					beforeSend: function()
					{
						$('.btnCancelDepartmentPayment').css(
							'pointer-events',
							'none'
						);
					},
					success: function(response)
					{
						if(response.status == 1)
						{
							bootbox.alert(
								response.message,
								function()
								{
									window.location.href = response.redirect;
								}
							);
						}
						else
						{
							bootbox.alert(response.message);
						}
					},
					error: function(xhr)
					{
						let message = 'Something went wrong.';

						if(xhr.responseJSON && xhr.responseJSON.message)
						{
							message = xhr.responseJSON.message;
						}
						bootbox.alert(message);
					},
					complete: function()
					{
						$('.btnCancelDepartmentPayment').css(
							'pointer-events',
							''
						);
					}
				});
			}
		}
	});
});
</script>

<script src="{{ asset('panel/assets/js/datatablePaginationWithFilter.js') }}"></script>
@endsection