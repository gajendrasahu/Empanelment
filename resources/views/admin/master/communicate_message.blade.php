@extends('admin.admin_master')
@section('admin')
	@php
		$t = 1;
		
	@endphp
	<div class="main-content">
		<div class="main-content-inner">
			<div class="page-content" style="padding:10px 15px!important;">
				<div class="ace-settings-container" id="ace-settings-container"></div>
				<div class="row">
					<div class="col-xs-12">
						<!-- PAGE CONTENT BEGINS -->
						<div class="tabbable animated bounceInUp" style="padding:0px 0px!important;">
	<div class="tab-content no-border">
		<div id="recordlist" class="tab-pane in active" style="padding:0px 0px!important;">
			<div class="row">
				<div class="col-xs-12">
				<form name="sendmsg" id="sendmsg" action="#" method="post" enctype="multipart/form-data">
					<table class="table table-bordered no-stripped" style="width:100%;">
						<tbody class="tabledata">
							<tr>
								<td class="center padding-8"><i class="fa fa-spinner fa-spin"></i> Please wait...</td>
							</tr>
						</tbody>
					</table>
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


<script src="{{ asset('panel/assets/js/jquery-2.1.4.min.js') }}"></script>
<script src="{{ asset('panel/assets/js/ace-elements.min.js') }}"></script>
<script src="{{ asset('panel/assets/js/ace.min.js') }}"></script>

<script>
	$(document).ready(function () {
		$.ajaxSetup({
			headers: {
				'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
			}
		});

		setTimeout(function() { loadData('{{ route('communication.html') }}'); },2000);
	});
	
	function loadData(r1)
	{
		var requestid	= 	'{{$requestid}}';
		var rectype		= 	'{{$rectype}}';
		var vendor_id	= 	'{{$vendor_id}}';
		$.get(""+r1,
		{
			requestid:requestid,
			rectype:rectype,
			vendor_id:vendor_id
		},
		function (data, status) {
			$(".tabledata").html(data);
			$('.attachment').ace_file_input({
				no_file:'No File ...',
				btn_choose:'Attach File',
				btn_change:'Change',
				btn_name:'fourthimage',
				thumbnail:false
			});

			
		});
	}

@if(in_array(session('userType'), ['DEPARTMENT', 'PROJECT MANAGER', 'VENDOR']))
$(document).on('click', '.sendMessageBtn', function(e) {
	
	bootbox.confirm('Do you confirm this action?',function(result){
		if(result)
		{
			e.preventDefault();
			tinymce.triggerSave();
			var form = $('#sendmsg')[0];
			var formData = new FormData(form);
			$.ajax({
				url: '{{route("sendmessage.conversation")}}',
				method: 'POST',
				data: formData,
				processData: false,
				contentType: false,
				success: function(response) {
					if(response.status===200)
					{
						bootbox.alert(response.message);
						setTimeout(function(){ loadData('{{ route('communication.html') }}'); },3000);
						return false;
					}
					else
					{
						bootbox.alert('<span style="color:red;">'+response.message+'</span>');
						return false;
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
	});
	
});

@else

$(document).on('click', '.sendMessageBtn', function(e) {

	let departmentId = $('#department_id').val();
	let vendorCount = $('input[name="vendorids[]"]:checked').length;

	if(!departmentId && vendorCount===0)
	{
		bootbox.alert('Please select either a Department / Project Manager OR at least one Firm name.');
		return false;
	}

	if(departmentId && vendorCount > 0)
	{
		bootbox.alert('You can select either Department / Project Manager OR Firms, not both.');
		return false;
	}

	
	bootbox.confirm('Do you confirm this action?',function(result){
		if(result)
		{
			e.preventDefault();
			tinymce.triggerSave();

			
			var form = $('#sendmsg')[0];
			var formData = new FormData(form);
			$.ajax({
				url: '{{route("sendmessageadmin.conversation")}}',
				method: 'POST',
				data: formData,
				processData: false,
				contentType: false,
				success: function(response) {
					if(response.status===200)
					{
						bootbox.alert(response.message);
						setTimeout(function(){ loadData('{{ route('communication.html') }}'); },3000);
						return false;
					}
					else
					{
						bootbox.alert('<span style="color:red;">'+response.message+'</span>');
						return false;
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
	});
	
});

@endif

$(document).on('click', '.clsMessageBtn', function(e) {
	
	bootbox.confirm('Do you confirm this action?',function(result){
		if(result)
		{
			e.preventDefault();
			var form = $('#sendmsg')[0];
			var formData = new FormData(form);
			$.ajax({
				url: '{{route("close.conversation")}}',
				method: 'POST',
				data: formData,
				processData: false,
				contentType: false,
				success: function(response) {
					if(response.status===200)
					{
						bootbox.alert(response.message);
						setTimeout(function(){ loadData('{{ route('communication.html') }}'); },3000);
						return false;
					}
					else
					{
						bootbox.alert('<span style="color:red;">'+response.message+'</span>');
						return false;
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
	});
	
});	

</script>
<script src="{{ asset('panel/assets/js/master.js') }}"></script>
<script src="{{ asset('panel/assets/js/datatablePaginationWithFilter.js') }}"></script>
@endsection
