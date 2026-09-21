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
			<li class="active">
				<a data-toggle="tab" href="#recordlist">
					<i class="green ace-icon fa fa-list bigger-120 datalist" onclick="loadData(1,'{{ route('viewnotification.html') }}')" style="vertical-align:bottom;"></i> Notification List
				</a>
			</li>
		</ul>

	
		<div class="tab-content no-border" style="border:1px solid #ddd; min-height:100px; padding:0px 10px;">

<div id="recordlist" class="tab-pane in active">
	<div class="row">
		<div class="col-xs-12">
		<form name="pagedata" id="pagedata" action="#" method="post" onsubmit="return false;">
		@csrf
			<div class="table-responsive padding-10">			
				<table class="mytable pd-10" border="1" style="width:100%; border-collapse:collapse; border:1px solid #eee;" border="1">
				<thead>
				<tr>
					<td colspan="4" style="position:relative;">
						<input type="hidden" name="notification_id" id="notification_id" value="{{$notificationid}}">
						<select name="pagesize" id="pagesize" class="select2" onchange="loadData(1,'{{ route('viewnotification.html') }}')" data-width="85">
							<option value="50">50</option>
							<option value="100">100</option>
							<option value="300">300</option>
							<option value="500">500</option>														
						</select>						
						<button type="button" class="eoi-btn action-a" style="position:absolute; right:5px;" onclick="Refresh()">
							<i class="fa fa-refresh"></i> Refresh
						</button>
					
					</td>
				</tr>
				
				<tr class="myheadbg">
					<td style="width:25px;" class="text-center" nowrap><b>S. No.</b></td>
					<td nowrap class="width-100"><b>Date</b></td>
					<td nowrap><b>Title</b></td>
					<td nowrap><b>Content</b></td>
				</tr>
				</thead>
				<tbody class="tabledata">
					<tr><td colspan="4" class="center">--Search Record--</td></tr>
				</tbody>
				</table>
			</div>
		</form>
		</div>
	</div>
</div>

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
<script src="{{ asset('panel/assets/js/jquery-2.1.4.min.js') }}"></script>
<script>
setTimeout(function() { $('.datalist').trigger('click'); },1000);

function Refresh()
{
	$("#notification_id").val("");
	$('.datalist').trigger('click');
}
function loadData(page, r1) {
	var notificationid 	= 	document.getElementById("notification_id").value;
	var pagesize 		= 	document.getElementById("pagesize").value;
	$.get("" + r1,
	{
		page:page,
		pagesize:pagesize,
		notificationid:notificationid
	},
	function (data, status) {
		$(".tabledata").html(data);
	});
}


</script>

<script src="{{ asset('panel/assets/js/master.js') }}"></script>
<script src="{{ asset('panel/assets/js/datatablePaginationWithFilter.js') }}"></script>
@endsection