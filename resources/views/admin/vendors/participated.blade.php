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
			<li @if(!in_array(1,Session::get('actions'))) class="active" @endif><a data-toggle="tab" href="#recordlist"><i class="green ace-icon fa fa-list bigger-120 datalist" style="vertical-align:bottom;"></i> PARTICIPATION DONE</a></li>
		@endif
		</ul>

	
		<div class="tab-content no-border" style="border:1px solid #ddd; min-height:100px; padding:0px 10px;">


<div id="recordlist" class="tab-pane in active" style="padding:0px 0px!important;">
	<div class="row" style="padding:0px;">
		<div class="col-sm-12" style="padding:10px;">
			<div class="alert alert-block alert-success">
				<button type="button" class="close" data-dismiss="alert">
					<i class="ace-icon fa fa-times"></i>
				</button>
				<i class="fa fa-hand-o-right"></i> Your participation request has been successfully accepted<br>
				<i class="fa fa-hand-o-right"></i> Please proceed to the <a href="{{route('participated.eois')}}" style="text-decoration:none;"><b>Participated EoI</b></a> section to fill in the Candidate/Employee details.
			</div>
		</div>
		<div class="col-xs-12" style="padding:0px 2px;">
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

<script src="{{ asset('panel/assets/js/jquery-2.1.4.min.js') }}"></script>
<script>
setTimeout(function() { $('.datalist').trigger('click'); },1000);

function loadData(page,r1)
{
	var pagesize	=	document.getElementById("pagesize").value;
	var categoryid	=	document.getElementById("categoryid").value;
	var pagesearch	=	document.getElementById("pagesearch").value;
	var frmdate		=	document.getElementById("frmdate").value;
	var todate		=	document.getElementById("todate").value;
	$.get(""+r1,
	{
		page:page,
		pagesize:pagesize,
		pagesearch:pagesearch,
		categoryid:categoryid,
		todate:todate,
	},
	function(data, status){
		$(".tabledata").html(data);
		$(".mytr").click(function(){
			$(".mytr").removeClass("selected-color").addClass("reset-color");
			$(this).removeClass("reset-color").addClass("selected-color");
		});
	});
}

function viewEoiRequestData(r1,requestid)
{
	$.get(""+r1,
	{
		requestid:requestid,
	},
	function(data, status){
		$("#myModal").modal("show");
		$(".modal-content").html(data);
	});
}
function Cls()
{
	$("#myModal").modal("hide");
	$(".modal-content").html("");	
}

</script>
<script src="{{ asset('panel/assets/js/master.js') }}"></script>
<script src="{{ asset('panel/assets/js/datatablePaginationWithFilter.js') }}"></script>
@endsection