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
<div class="tabbable animated fadeInUp delay-02" style="padding:0px 0px!important;">
		<ul class="nav nav-tabs padding-0">
		@if(in_array(2,Session::get('actions')) || in_array(3,Session::get('actions')) || in_array(12,Session::get('actions')))
			<li @if(!in_array(1,Session::get('actions'))) class="active" @endif>
				<a data-toggle="tab" class="form-label font-14" href="#recordlist" onclick="loadData(1,'{{ route('pmmpr.html') }}')">
					<i class="green ace-icon fa fa-th-list bigger-120 datalist"></i> Submitted MPR(s)
				</a>
			</li>
		@endif
		</ul>

	
		<div class="tab-content no-border" style="border:1px solid #ddd; min-height:100px; padding:0px 10px;">

@if(in_array(2,Session::get('actions')) || in_array(3,Session::get('actions')) || in_array(12,Session::get('actions')))
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
		<form name="pagedata" id="pagedata" action="#" method="post" onsubmit="return false;">
			@csrf
			<div class="table-responsive">
				<table class="table-bordered pd-10" style="border:1px solid #eee; border-collapse:collapsed;" border="1">
				<tr>
					<td colspan="6">
						<select name="pagesize" id="pagesize" class="select2" onchange="loadData(1,'{{ route('pmmpr.html') }}')" data-width="90" data-placeholder="RECORDS">
							<option value="50">50</option>
							<option value="100">100</option>
							<option value="300">300</option>
							<option value="500">500</option>														
						</select>

						<select class="select2" name="vendorid" id="vendorid" onKeyPress="return OnKeyPress(this, event)" onchange="loadData(1,'{{ route('pmmpr.html') }}');" data-width="175" data-placeholder="Firm Name">
							<option value=""></option>
							@foreach ($vendors as $itm)
							<option value="{{ $itm->vendorid }}">{{ strtoupper($itm->companyname) }}</option>
							@endforeach
						</select>

						<select class="select2" name="orderid" id="orderid" onKeyPress="return OnKeyPress(this, event)" onchange="loadData(1,'{{ route('pmmpr.html') }}');" data-width="175" data-placeholder="Order Number">
							<option value=""></option>
							@foreach ($orders as $itm)
							<option value="{{ $itm->orderid }}">{{ strtoupper($itm->ordernumber) }}</option>
							@endforeach
						</select>

						<select class="select2" name="month" id="month" onKeyPress="return OnKeyPress(this, event)" onchange="loadData(1,'{{ route('pmmpr.html') }}');" data-width="175" data-placeholder="All Month">
							<option value=""></option>
							@for($i=1;$i<=12;$i++)
							<option value="{{ $i }}">{{ DateTime::createFromFormat('!m', $i)->format('F'); }}</option>
							@endfor
						</select>
						<select class="select2" name="year" id="year" onKeyPress="return OnKeyPress(this, event)" onchange="loadData(1,'{{ route('pmmpr.html') }}');" data-width="175">
							@for($i=date('Y');$i>=2025;$i--)
							<option value="{{ $i }}">{{ $i }}</option>
							@endfor
						</select>

						<select class="select2" name="approval" id="approval" onKeyPress="return OnKeyPress(this, event)" onchange="loadData(1,'{{ route('pmmpr.html') }}');" data-width="175">
							<option value="0">Pending</option>
							<option value="1">Approved</option>
						</select>
						
					
					</td>
				</tr>
				<tr>
					<td colspan="6" style="padding:0px!important;">
						<span class="input-icon" style="width:100%!important;">
							<input type="text" placeholder="Search ..." class="nav-search-input" id="pagesearch" name="pagesearch" autocomplete="off" onchange="loadData(1,'{{ route('pmmpr.html') }}')" tabindex="<?php echo $t++;?>" @if($search!='')value="{{str_replace('-','/',$search)}}"@endif style="width:100%!important;"/>
							<i class="ace-icon fa fa-search nav-search-icon" style="margin-top:-3px;"></i>
						</span>
				</td>
				<tr class="myheadbg">
					<td colspan="3"></td>
					<td colspan="2" class="text-center"><b>Amount</b></td>
					<td nowrap class="width-100"></td>
				</tr>
				<tr class="myheadbg">
					<td style="width:25px;" nowrap><b>S.No.</b></td>
					<td nowrap><b>Order Detail</b></td>
					<td nowrap><b>MPR Detail</b></td>
					<td nowrap class="width-100 text-center"><b>Submitted</b></td>
					<td nowrap class="width-100 text-center"><b>Approved</b></td>
					<td nowrap class="width-100"></td>
				</tr>
				<tbody class="tabledata">
				<tr><td colspan="6" class="center">--Search Record--</td></tr>
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


						
				</div><!-- /.col -->
			</div><!-- /.row -->
		</div><!-- /.page-content -->
	
		
	</div>
</div>


<div class="modal fade" id="viewMpr" style="margin-top:-20px;" data-backdrop="static">
  <div class="modal-dialog modal-xl" style="width:95%;">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title form-label" style="float:left;"></h5>    
      </div>
      <div class="modal-body" id="mprContent" style="min-height:200px; max-height:450px; overflow-y:scroll;">Loading...</div>
      <div class="modal-footer">
        <button type="button" class="btn btn-close" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<script src="{{ asset('panel/assets/js/jquery-2.1.4.min.js') }}"></script>
<script>

$('.btn-close').on('click', function (e)
{
	$('#mprContent').html('');
	$('#viewMpr').modal('hide');
});

function viewMprManager(r1,mprid)
{
	$.get(""+r1,
	{
		mprid:mprid,
	},
	function(data, status){
		if(data.status==400)
		{
			bootbox.alert(data.message);
		}
		else if(data.status==500)
		{
			bootbox.alert(data.message);
		}
		else
		{
			$('#viewMpr').modal('show');
			$('#mprContent').html(data.data);
		}
	});
}


setTimeout(function() { $('.datalist').trigger('click'); },1000);

$(document).ready(function() {
	$.ajaxSetup({
		headers: {
			'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
		}
	});	
});

function loadData(page,r1)
{
	var pagesize	=	document.getElementById("pagesize").value;
	var pagesearch	=	document.getElementById("pagesearch").value;
	var vendorid	=	document.getElementById("vendorid").value;
	var orderid		=	document.getElementById("orderid").value;
	var month		=	document.getElementById("month").value;
	var year		=	document.getElementById("year").value;
	var approval	=	document.getElementById("approval").value;
	
	$.get(""+r1,
	{
		page:page,
		pagesize:pagesize,
		pagesearch:pagesearch,
		vendorid:vendorid,
		orderid:orderid,
		month:month,
		year:year,
		approval:approval
	},
	function(data, status){
		$(".tabledata").html(data);
		$(".mytr").click(function(){
			$(".mytr").removeClass("selected-color").addClass("reset-color");
			$(this).removeClass("reset-color").addClass("selected-color");
		});
	});
}

</script>
<script src="{{ asset('panel/assets/js/master.js') }}"></script>
<script src="{{ asset('panel/assets/js/datatablePaginationWithFilter.js') }}"></script>
@endsection