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
				<i class="green ace-icon fa fa-plus-circle bigger-120" style="vertical-align:bottom;"></i> Work Order List
			</a>
		</li>
	</ul>

	<div class="tab-content no-border" style="border:1px solid #ddd; min-height:100px; padding:0px 10px;">
		<div id="home" class="tab-pane in active" style="padding:0px 10px!important;">
			<div class="row">
				
					<div class="table-responsive">			
						<table class="table table-bordered table-striped table-hover" id="tablerecords">
						<thead>
						<tr>
							<td colspan="5">
							<div id="tablehead">
								<select name="pagesize" id="pagesize" class="select2" onchange="loadData(1,'{{ route('workorderforduedate.html') }}')">
									<option value="15">15</option>
									<option value="100">100</option>
									<option value="300">300</option>
									<option value="500">500</option>														
								</select>
								<span class="input-icon" style="float:right;">
									<input type="text" placeholder="Search ..." class="nav-search-input" id="pagesearch" name="pagesearch" autocomplete="off" onchange="loadData(1,'{{ route('workorderforduedate.html') }}')" tabindex="<?php echo $t++;?>" />
									<i class="ace-icon fa fa-search nav-search-icon" style="margin-top:-3px;"></i>
								</span>
								<button type="button" class="btn btn-info" onclick="loadData(1,'{{ route('workorderforduedate.html') }}')">
									<i class="fa fa-search"></i> Search
								</button>
							</div>
							</td>
						</tr>
						<tr class="myhead">
							<td style="width:25px;" nowrap><b>S.No.</b></td>
							<td nowrap style=""><b>Firm Name</b></td>
							<td nowrap style=""><b>Work Order Number</b></td>
							<td class="no_wrap"><b>Order Date</b></td>
							<td nowrap style=""><b>Order Due Date</b></td>
						</tr>
						</thead>
						<tbody class="tabledata">
							<tr><td colspan="5" class="text-center">--Search Records--</td>
						</tbody>
						</table>
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
<script src="{{ asset('panel/assets/js/jquery-2.1.4.min.js') }}"></script>
<script>

setTimeout(function(){
	
	loadData(1,'{{route('workorderforduedate.html')}}');
},2000);

function loadData(page,r1)
{
	var pagesize	=	document.getElementById("pagesize").value;
	var pagesearch	=	document.getElementById("pagesearch").value;
	$.get(""+r1,
	{
		page:page,
		pagesize:pagesize,
		pagesearch:pagesearch,
	},
	function(data, status){
		$(".tabledata").html(data);
		
		const paginationLinks = document.querySelectorAll('.pagination a');
		paginationLinks.forEach(link => {
			link.onclick = function(event) {
				event.preventDefault();
				const page 	= 	this.getAttribute('href').split('page=')[1];
				const url	= 	'{{ route('workorderforduedate.html') }}';
				loadData(page, url);
			};
		});

		flatpickr(".todays_dt_blank", {
			dateFormat: "d-m-Y",
			defaultDate: "",
			allowInput: true
		});

		$(document).on('submit', '.update-due-date-form', function(e) {

			e.preventDefault();

			let form = $(this);
			let button = form.find('button[type="submit"]');
			let message = form.siblings('.due-date-message');

			button.prop('disabled', true);
			button.text('Updating...');

			$.ajax({
				url: form.attr('action'),
				type: 'POST',
				data: form.serialize(),

				success: function(response) {

					button.prop('disabled', false);
					button.text('Update');

					message
						.text('Updated successfully')
						.css('color', 'green');

					setTimeout(function() {
						message.text('');
					}, 3000);
				},

				error: function(xhr) {

					button.prop('disabled', false);
					button.text('Update');

					let errorMessage = 'Unable to update due date.';

					if (xhr.responseJSON && xhr.responseJSON.message) {
						errorMessage = xhr.responseJSON.message;
					}

					message
						.text(errorMessage)
						.css('color', 'red');
				}
			});

		});
		
	});
}

</script>

<script src="{{ asset('panel/assets/js/datatablePaginationWithFilter.js') }}"></script>
@endsection