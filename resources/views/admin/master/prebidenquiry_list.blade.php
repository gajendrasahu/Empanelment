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
		
			<li class="active">
				<a data-toggle="tab" class="form-label font-14" href="#recordlist" onclick="loadData(1,'{{ route('prebidenquiry.html') }}')">
					<i class="green ace-icon fa fa-list bigger-120 datalist" style="vertical-align:bottom;"></i> Pre-bid Query
				</a>
			</li>
		
		</ul>

	
		<div class="tab-content no-border" style="border:1px solid #ddd; min-height:100px; padding:0px 10px;">


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
		<form name="pagedata" id="pagedata" action="#" method="post" onsubmit="return false;" enctype="multipart/form-data">
			@csrf
			<div class="table-responsive">
				<input type="hidden" name="requestid" id="requestid" value="{{$recordid}}">
				<table class="mytable table-striped table-bordered pd-10" style="border:1px solid #eee!important; border-collapse:collapse;" border="1">
				<tbody class="tabledata">
				<tr><td colspan="8" class="center">--Search Record--</td></tr>
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

<div class="modal fade left" id="myModal" role="dialog" data-backdrop="static">
	<div class="modal-lg-dialog">
		<div class="modal-content">
		
		</div>
	</div>
</div>
<div class="modal fade" id="queryModal">
  <div class="modal-dialog modal-xl" style="width:95%; margin-top:0px;">
    <div class="modal-content" id="queryContent">
	Loading...
    </div>
  </div>
</div>

<script src="{{ asset('panel/assets/js/jquery-2.1.4.min.js') }}"></script>
<script>
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
	var requestid	=	document.getElementById("requestid").value;
	$.get(""+r1,
	{
		requestid:requestid
	},
	function(data, status){
		$(".tabledata").html(data);
	});
}

function viewAllQuery(requestid)
{
	var form = $('#frm')[0];
	var formData = new FormData(form);
	formData.append('requestid',requestid);
	$.ajax({
		url: '{{route("view.allprebidquery")}}',
		method: 'POST',
		data: formData,
		processData: false,
		contentType: false,
		success: function(response) {
			if(response.status===200)
			{
				$('#queryModal').modal('show');
				$('#queryContent').html(response.formhtml);				
			}
			else
			{
				bootbox.alert(response.message);
			}
		},
		error: function(xhr) {
			let errors = xhr.responseJSON?.errors;
			let message = '';
			$.each(errors, function(key, val) {
				message += '<i class="fa fa-hand-o-right"></i> '+ val + '<br>';
			});
			bootbox.alert(message);
		}
	});
}
function Cls()
{
	$('#queryModal').modal('hide');
	$('#queryContent').html("");
}
</script>
<script src="{{ asset('panel/assets/js/master.js') }}"></script>
<script src="{{ asset('panel/assets/js/datatablePaginationWithFilter.js') }}"></script>
@endsection