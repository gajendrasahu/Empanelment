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
		<form name="mpr_data" id="mpr_data" action="#" method="post" onsubmit="return false;">
			@csrf
			<div class="table-responsive">
				<table class="table table-bordered pd-10">
				<thead>
				<tr>
					<td colspan="5">
						<select class="select2" name="mpr_month" id="mpr_month" onKeyPress="return OnKeyPress(this, event)" onchange="LoadMpr()" data-width="150" data-placeholder="Month">
							<option value=""></option>
							@foreach ($months as $itm)
							<option value="{{ $itm['month_number'] }}">{{ $itm['month'] }}</option>
							@endforeach
						</select>

						<select class="select2" name="mpr_year" id="mpr_year" onKeyPress="return OnKeyPress(this, event)" onchange="LoadMpr()" data-width="150" data-placeholder="Year">
							<option value=""></option>
							@foreach ($years as $itm)
							<option value="{{ $itm }}">{{ $itm }}</option>
							@endforeach
						</select>
		<div style="float:right;">
			<span style="border:1px solid #eee; padding:2px 20px;">P-Present</span>
			<span style="border:1px solid #eee; padding:2px 20px;">A-Absent</span>
			<span style="border:1px solid #eee; padding:2px 20px;">L-Leave</span>
			<span style="border:1px solid #eee; padding:2px 20px;">H-Holiday</span>
		</div>
					</td>
				</tr>
				<tr>
					<td class="center width-50">Date</td>
					<td class="center width-50">Day</td>
					<td class="center width-50">Status</td>
					<td>Work Detail</td>
					<td class="center width-100">&nbsp;</td>
				</tr>
				
				</thead>
				<tbody class="tabledata">
				<tr><td colspan="5" class="center">Please select MPR Month and Year</td></tr>
				</tbody>
				</table>
			</div>
		</form>
		</div>
	</div>

					
				</div><!-- /.col -->
			</div><!-- /.row -->
		</div>
	</div>
</div>

<script src="{{ asset('panel/assets/js/jquery-2.1.4.min.js') }}"></script>
<script>

$(document).ready(function() {
	$.ajaxSetup({
		headers: {
			'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
		}
	});

});

function LoadMpr()
{
	var mpr_month	=	$("#mpr_month").val();
	var mpr_year	=	$("#mpr_year").val();
	if(mpr_month!='' && mpr_year!='')
	{
		var form = $('#mpr_data')[0];
		var formData = new FormData(form);
		$.ajax({
			url: '{{route("monthlyreport.html")}}',
			method: 'POST',
			data: formData,
			processData: false,
			contentType: false,
			success: function(response) {
				$(".tabledata").html(response.html);
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
}

</script>
<script src="{{ asset('panel/assets/js/master.js') }}"></script>
<script src="{{ asset('panel/assets/js/datatablePaginationWithFilter.js') }}"></script>
@endsection
