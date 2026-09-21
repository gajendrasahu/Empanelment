@extends('admin.admin_master')
@section('admin')
@php
$t=1;
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
		@if(in_array(2,Session::get('actions')) || in_array(3,Session::get('actions')) || in_array(12,Session::get('actions')))
			<li @if(!in_array(1,Session::get('actions'))) class="active" @endif><a data-toggle="tab" href="#recordlist" onclick="loadData(1,'{{ route('customerorder.html') }}')" class="datalist"><i class="green ace-icon fa fa-list bigger-120" style="vertical-align:bottom;"></i> {{__('labels.orderlisttab')}}</a></li>
		@endif

		</ul>

	
		<div class="tab-content no-border" style="border:1px solid #ddd; min-height:100px; padding:0px 10px;">
@if(in_array(2,Session::get('actions')) || in_array(3,Session::get('actions')) || in_array(12,Session::get('actions')))
<div id="recordlist" class="tab-pane in active" style="padding:0px 0px!important;">
	<div class="row" style="padding:0px;">
		<div class="col-xs-12" style="padding:0px 2px;">
		<form name="pagedata" id="pagedata" action="#" method="post" onsubmit="return false;">
			<div class="table-responsive">			
				<table class="table table-bordered table-striped" id="tablerecords">
				<thead>
			
				<tr>
					<td colspan="9">
					<div id="tablehead">
						<input type="hidden" name="searchorder" id="searchorder" value="">
						<select name="pagesize" id="pagesize" class="selectbx" onchange="loadData(1,'{{ route('customerorder.html') }}')">
							<option value="50">50</option>
							<option value="100">100</option>
							<option value="300">300</option>
							<option value="500">500</option>														
						</select>
						<input type="date" name="frmdate" id="frmdate" value="{{ \Carbon\Carbon::now()->subDays(30)->format('Y-m-d') }}" class="selectbx">
						<input type="date" name="todate" id="todate" value="{{ \Carbon\Carbon::now()->addDays(30)->format('Y-m-d') }}" class="selectbx">
						<select class="select2" name="slot" id="slot" onKeyPress="return OnKeyPress(this, event)"  data-placeholder="SLOT TIME" onchange="loadData(1,'{{ route('customerorder.html') }}');" data-width="100">
							<option value=""></option>
							@foreach ($slots as $itm)
							<option value="{{ $itm->slot }}">{{ date('h:i A',strtotime($itm->slot)) }}</option>
							@endforeach
						</select>
						<select class="select2" name="catid" id="catid" onKeyPress="return OnKeyPress(this, event)"  data-placeholder="{{__('labels.categoryname')}}" onchange="loadData(1,'{{ route('customerorder.html') }}')" data-width="170">
							<option value=""></option>
							@foreach ($categories as $itm)
							<option value="{{ $itm->categoryid }}">{{ strtoupper($itm->category) }}</option>
							@endforeach
						</select>
						<button type="button" onclick="loadData(1,'{{ route('customerorder.html') }}')" class="btn btn-info selectbx"><i class="fa fa-search"></i> SEARCH RECORD</button>
						<span class="input-icon" style="float:right;">
							<input type="text" placeholder="Search ..." class="nav-search-input" id="pagesearch" name="pagesearch" autocomplete="off" onchange="loadData(1,'{{ route('customerorder.html') }}')" tabindex="<?php echo $t++;?>" />
							<i class="ace-icon fa fa-search nav-search-icon" style="margin-top:-3px;"></i>
						</span>
						
					</div>
					</td>
				</tr>
				
				</thead>
				<tbody class="tabledata">
					<tr><td colspan="9" class="center">--Search Record--</td></tr>
				</tbody>
				</table>
			</div>
		</form>
		</div>
	</div>
</div>
@endif
</div>
</div>


						<div class="hr hr32 hr-dotted"></div>
				</div><!-- /.col -->
			</div><!-- /.row -->
		</div><!-- /.page-content -->
	
		
	</div>
</div>

	</div>
</div>

<div class="modal fade left" id="assignvendor" role="dialog" data-backdrop="static">
	<div class="modal-lg-dialog">
		<div class="modal-content assignvendor" style="font-size:12px; margin:0 auto; margin-top:10px; width:90%!important;">
		</div>
	</div>
</div>

<div class="modal fade left" id="orderpricing" role="dialog" data-backdrop="static">
	<div class="modal-lg-dialog">
		<div class="modal-content orderpricing" style="font-size:12px; margin:0 auto; margin-top:10px; width:90%!important;">
		</div>
	</div>
</div>


<div class="modal fade left" id="historydetail" role="dialog" data-backdrop="static">
	<div class="modal-lg-dialog">
		<div class="modal-content historydetail" style="font-size:12px; margin:0 auto; margin-top:10px; width:90%!important;">
		</div>
	</div>
</div>

<script src="{{ asset('panel/assets/js/jquery-2.1.4.min.js') }}"></script>
<script>
function loadData(page,r1)
{
	var searchorder		=	document.getElementById("searchorder").value;
	var pagesize		=	document.getElementById("pagesize").value;
	var pagesearch		=	document.getElementById("pagesearch").value;
	var categoryid		=	document.getElementById("catid").value;
	var slot			=	document.getElementById("slot").value;
	var frmdate			=	document.getElementById("frmdate").value;
	var todate			=	document.getElementById("todate").value;
	var orderstatus		=	0;
	var selected		=	document.querySelector('input[name="orderstatus"]:checked');
	if(selected)
	{
		orderstatus=selected.value;
	}
	else
	{
		orderstatus		=	0;
	}

	$.ajax({
		url: ''+r1,
		type: 'GET',
		data: {
			_token: '{{ csrf_token() }}',
			page:page,
			pagesize:pagesize,
			pagesearch:pagesearch,
			categoryid:categoryid,
			orderstatus:orderstatus,
			slot:slot,
			frmdate:frmdate,
			todate:todate,
			searchorder:searchorder
		},
		success: function(response)
		{
			$(".tabledata").html(response);
			$(".mytr").click(function(){
				$(".mytr").removeClass("selected-color").addClass("reset-color");
				$(this).removeClass("reset-color").addClass("selected-color");
			});
		},
	});
}

function AssignVendor(r1,detailid)
{
	var addressid	=	$("input[name='addressid']:checked").val();
	var vendorid	=	document.getElementById("vendid").value;
	var employeeid	=	document.getElementById("employeeid").value;
	if(vendorid=='' || addressid=='')
	{
		bootbox.alert("PLEASE SELECT VENDOR NAME AND SERVICE ADDRESS DETAIL");
		return false;
	}
	$.get(""+r1,
	{
		detailid:detailid,
		vendorid:vendorid,
		employeeid:employeeid,
		addressid:addressid
	},
	function(data, status){
		if(data.status==200)
		{
			$("#assignmsg").css("display","");
			$("#assignmsg").addClass("bg-success");
			$(".assignmsg").html(data.message);
			setTimeout(function(){
				$(".assignvendor").html("");
				$("#assignvendor").modal("hide");
				loadData(1,'{{ route("customerorder.html") }}');				
			},3000);
		}
		if(data.status==201)
		{
			$("#assignmsg").css("display","");
			$("#assignmsg").addClass("bg-warning");
			$(".assignmsg").html(data.message);
			setTimeout(function(){
				$(".assignmsg").html("");
				$("#assignmsg").css("display","none");
			},10000);
		}

	});
}
function ViewVendors(r1,orderid)
{
	$.get(""+r1,
	{
		orderid:orderid,
	},
	function(data, status){
		$("#assignvendor").modal("show");					
		$(".assignvendor").html(data);
		$(".select2").css('width','200px').select2({
			allowClear: true
		}).on("select2:unselecting", function (e) {
			$(this).data('unselecting', true);
		}).on("select2:opening", function (e) {
		if ($(this).data('unselecting')) {
			$(this).removeData('unselecting');
			e.preventDefault();
			}
		});
		$(".select2").css('margin-top','0px');
	});
}

function Close()
{

	$("#assignvendor").modal("hide");
	$(".orderpricing").html("");
	$("#orderpricing").modal("hide");
	$(".historydetail").html("");
	$("#historydetail").modal("hide");
}

setTimeout(function() { $('.datalist').trigger('click'); },1000);
function togglePasswordVisibility() {
	var passwordInput = $('#loginpassword');
	var toggleIcon = $('.toggle-password img');
	if(passwordInput.attr('type') === 'password')
	{
		passwordInput.attr('type', 'text');
	}
	else
	{
		passwordInput.attr('type', 'password');
	}
}


function SetStartTime(detailid,r1)
{
	bootbox.confirm('DO YOU WANT TO SUBMIT START TIME FOR THIS TASK!',function(result){
		if(result)
		{	
			$(".startbtn").prop("disabled","disabled");
			$.ajax({
				url: ''+r1,
				type: 'POST',
				data: {
					_token: '{{ csrf_token() }}',
					detailid:detailid,
				},
				success: function(response)
				{
					if(response.status===200)
					{
						bootbox.alert(response.message);
						setTimeout(function(){ WorkHistory(response.orderid,response.detailid); $('.orderrecord').click(); },2000);
					}
					if(response.status===400)
					{
						bootbox.alert(response.message);
					}
				},
				error: function (xhr) {
					$(".startbtn").prop("disabled","");
					if (xhr.responseJSON && xhr.responseJSON.errors) {
						var errors = xhr.responseJSON.errors;
						var allMessages = '';

						$.each(errors, function(field, messages) {
							$.each(messages, function(index, msg) {
								allMessages += field.toUpperCase() + ' : ' + msg + '<br>';
							});
						});
						bootbox.alert(allMessages);
					}			
				}
			});
		}
	});
}

function SetEndTime(detailid,r1)
{
	bootbox.confirm('DO YOU WANT TO SUBMIT END TIME FOR THIS ORDER!',function(result){
		if(result)
		{
			$(".endbtn").prop("disabled","disabled");
			$.ajax({
				url: ''+r1,
				type: 'POST',
				data: {
					_token: '{{ csrf_token() }}',
					detailid:detailid,
				},
				success: function(response)
				{
					if(response.status===200)
					{
						bootbox.alert(response.message);
						setTimeout(function(){ 
							WorkHistory(response.orderid,response.detailid); 
							$('.orderrecord').click();
						},2000);
					}
					if(response.status===400)
					{
						bootbox.alert(response.message);
					}
				},
				error: function (xhr) {
					$(".startbtn").prop("disabled","");
					if (xhr.responseJSON && xhr.responseJSON.errors) {
						var errors = xhr.responseJSON.errors;
						var allMessages = '';

						$.each(errors, function(field, messages) {
							$.each(messages, function(index, msg) {
								allMessages += field.toUpperCase() + ' : ' + msg + '<br>';
							});
						});
						bootbox.alert(allMessages);
					}			
				}
			});

		}
	});
}

$(document).on('click', '.orderrecord', function () {
	const orderid = $(this).data('id');
	$.ajax({
		url: '{{ route("vieworder.detail") }}',
		type: 'POST',
		data: {
			_token: '{{ csrf_token() }}',
			orderid:orderid
		},
		success: function(response) {
			$(".tabledata").html(response);
		},
		error: function (xhr) {
			if (xhr.responseJSON && xhr.responseJSON.errors) {
				var errors = xhr.responseJSON.errors;
				var allMessages = '';

				$.each(errors, function(field, messages) {
					$.each(messages, function(index, msg) {
						allMessages += field.toUpperCase() + ' : ' + msg + '<br>';
					});
				});
				bootbox.alert(allMessages);
			}			
		}
	});
	
});

function WorkHistory(orderid,detailid)
{
	$.ajax({
		url: '{{ route("work.history") }}',
		type: 'POST',
		data: {
			_token: '{{ csrf_token() }}',
			orderid:orderid,
			detailid:detailid
		},
		success: function(response) {
			if(response.status==400)
			{
				bootbox.alert(response.message);
			}
			else
			{
				$("#historydetail").modal("show");					
				$(".historydetail").html(response);
			}
		},
		error: function (xhr) {
			if (xhr.responseJSON && xhr.responseJSON.errors) {
				var errors	=	xhr.responseJSON.errors;
				var allMessages	=	'';

				$.each(errors, function(field, messages) {
					$.each(messages, function(index, msg) {
						allMessages += field.toUpperCase() + ' : ' + msg + '<br>';
					});
				});
				bootbox.alert(allMessages);
			}			
		}
	});
}
</script>

<script src="{{ asset('panel/assets/js/master.js') }}"></script>
<script src="{{ asset('panel/assets/js/datatablePaginationWithFilter.js') }}"></script>
@endsection