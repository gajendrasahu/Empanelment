@extends('admin.admin_master')
@section('admin')
@php
$t=1;
@endphp
@php
	$canAutoOpen = app(\App\Services\PermissionService::class)->hasPermission('notification.html') && !app(\App\Services\PermissionService::class)->hasPermission('store.notification');
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
		@permission('store.notification')
		<li class="active">
			<a data-toggle="tab" href="#home">
				<i class="green ace-icon fa fa-plus-circle bigger-120" style="vertical-align:bottom;"></i> Send Notification
			</a>
		</li>
		@endpermission
		@permission('notification.html')
		<li @if($canAutoOpen) class="active" @endif>
			<a data-toggle="tab" href="#recordlist" onclick="loadData(1,'{{ route('notification.html') }}')">
				<i class="green ace-icon fa fa-list bigger-120 datalist" style="vertical-align:bottom;"></i> Sent Notification
			</a>
		</li>
		@endpermission
		</ul>
		
		<div class="tab-content no-border" style="border:1px solid #ddd; min-height:100px; padding:0px 10px;">
@permission('store.notification')
<div id="home" class="tab-pane in active" style="padding:0px 10px!important;">
	<div class="row">
		<div class="row">
			<form name="frm" id="frm" action="{{ route('store.notification')}}" method="post" enctype="multipart/form-data">		
				@if(Session::has('success'))
				<div class="col-sm-12">
					<div class="alert alert-block alert-success">
				    	<button type="button" class="close" data-dismiss="alert">
							<i class="ace-icon fa fa-times"></i>
						</button>
						{{ Session::get('success') }}!						
					</div>
				</div>
				@endif
				@if(Session::has('fail'))
				<div class="col-sm-12">
					<div class="alert alert-block alert-danger">
				    	<button type="button" class="close" data-dismiss="alert">
							<i class="ace-icon fa fa-times"></i>
						</button>
						{{ Session::get('fail') }}
					</div>
				</div>
				@endif
				@if(Session::has('duplicate'))
				<div class="col-sm-12">
				    <div class="alert alert-block alert-danger">
				    	<button type="button" class="close" data-dismiss="alert">
							<i class="ace-icon fa fa-times"></i>
						</button>
				    	<i class="fa fa-warning"></i> {{ Session::get('duplicate') }}
				    </div>
				</div>
				@endif			
				@csrf
				<div class="form-group">
					<div class="col-sm-2">
						Notification Date<label id="req">*</label>
						<input type="hidden" name="form_token" id="form_token" value="{{$token}}">
						<input type="text" class="form-control todays_dt" name="notification_date" id="notification_date" value="{{ old('notification_date') }}" autocomplete="off" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}"/>

						<span class="text-danger">@error('notification_date') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
					</div>
					<div class="col-sm-10">
						Subject / Title <label id="req">*</label>
						<input type="text" class="form-control" name="notification_title" id="notification_title" value="{{old('notification_title')}}" placeholder="Notification title" autocomplete="off" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}">
						
						<span class="text-danger">@error('notification_title') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
					</div>
					<div class="col-sm-12 mt-10">
						Notification Content <label id="req">*</label>
						<textarea class="form-control tinymce" name="notification_content" id="notification_content" value="{{old('notification_content')}}" placeholder="Notification content" required autocomplete="off" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}"></textarea>
						
						<span class="text-danger">@error('notification_content') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
					</div>

					<div class="col-sm-3 mt-10">
						Attachment <i>(optional)</i>
						<input type="file" class="form-control" name="notification_attachment" id="notification_attachment" autocomplete="off" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}">
						
						<span class="text-danger">@error('notification_content') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
					</div>
					
					<div class="col-sm-12"></div>
					<div class="col-sm-3">
						<label style="display: flex; align-items: center; gap: 8px; margin-bottom: 10px;">
							<input type="checkbox" id="select_all_dept">
							<span>Select All Department</span>
						</label>
						
						<div style="border: 1px solid #ddd; padding: 10px; width: 100%;">
							<input type="text" id="department_search" placeholder="Search Department..." style="width: 100%; margin-bottom: 10px; padding: 5px;">
							<div style="max-height: 252px; overflow-y: auto;">
								@foreach($users as $usr)
									@if($usr->isdepartment === 1)
										<div class="department-row" style="display: flex; align-items: flex-start; margin-bottom: 5px;">
											<input type="checkbox" class="department_checkbox" name="department_ids[]"  value="{{$usr->departmentid}}" style="margin-top: 4px; margin-right: 8px;">
											<span class="department-text">{{$usr->name}}</span>
										</div>
									@endif
								@endforeach
							</div>
						</div>
					</div>
					<div class="col-sm-3">
						<label style="display: flex; align-items: center; gap: 8px; margin-bottom: 10px;">
							<input type="checkbox" id="select_all_manager">
							<span>Select All Project Manager</span>
						</label>
						<div style="border: 1px solid #ddd; padding: 10px; width: 100%;">
							<input type="text" id="manager_search" placeholder="Search Manager..." style="width: 100%; margin-bottom: 10px; padding: 5px;">
							<div style="max-height:252px; overflow-y: auto;">
								@foreach($users as $usr)
									@if($usr->ispm === 1)
										<div class="manager-row" style="display: flex; align-items: flex-start; margin-bottom: 5px;">
											<input type="checkbox" class="manager_checkbox" name="manager_ids[]" value="{{$usr->departmentid}}" style="margin-top: 4px; margin-right: 8px;">
											<span class="manager-text">{{$usr->name}}</span>
										</div>
									@endif
								@endforeach
							</div>
						</div>
					</div>
					<div class="col-sm-3">
						<label style="display: flex; align-items: center; gap: 8px; margin-bottom: 10px;">
							<input type="checkbox" id="select_all_vendor">
							<span>Select All CSF Firm</span>
						</label>
						<div style="border: 1px solid #ddd; padding: 10px; width: 100%;">
							<input type="text" id="vendor_search" placeholder="Search Vendor..." style="width: 100%; margin-bottom: 10px; padding: 5px;">
							<div style="max-height: 300px; overflow-y: auto;">
								@foreach($firms as $usr)
									@if($usr->categoryid === 2)
										<div class="vendor-row" style="display: flex; align-items: flex-start; margin-bottom: 5px;">
											<input type="checkbox" class="vendor_checkbox" name="vendor_ids[]" value="{{$usr->vendorid}}" style="margin-top: 4px; margin-right: 8px;">
											<span class="vendor-text">{{$usr->name}}</span>
										</div>
									@endif
								@endforeach
							</div>
						</div>
					</div>
					<div class="col-sm-3">
						<label style="display: flex; align-items: center; gap: 8px; margin-bottom: 10px;">
							<input type="checkbox" id="select_all_vendorawd">
							<span>Select All AWD Firm</span>
						</label>
						<div style="border: 1px solid #ddd; padding: 10px; width: 100%;">
							<input type="text" id="vendorawd_search" placeholder="Search Vendor..." style="width: 100%; margin-bottom: 10px; padding: 5px;">
							<div style="max-height: 252px; overflow-y: auto;">
								@foreach($firms as $usr)
									@if($usr->categoryid === 1)
										<div class="vendorawd-row" style="display: flex; align-items: flex-start; margin-bottom: 5px;">
											<input type="checkbox" class="vendorawd_checkbox" name="vendor_ids[]" value="{{$usr->vendorid}}" style="margin-top: 4px; margin-right: 8px;">
											<span class="vendorawd-text">{{$usr->name}}</span>
										</div>
									@endif
								@endforeach
							</div>
						</div>
					</div>
					<div class="col-sm-9"></div>
					<div class="col-sm-3 text-right">
					@permission('store.notification')
						<button type="button" class="btn btn-info myfrmbtn form-control mt-20 notificationBtn" tabindex="{{$t++}}">
							<i class="fa fa-envelope"></i> Submit
						</button>
					@endpermission
					</div>
					
					<div class="col-sm-12">&nbsp;</div>
				</div>		
			</form>
		</div>
	</div>
</div>
@endpermission
@permission('notification.html')
<div id="recordlist" class="tab-pane @if($canAutoOpen) in active @endif">
	<div class="row">
		<div class="col-xs-12">
		<form name="pagedata" id="pagedata" action="#" method="post" onsubmit="return false;">
			<div class="table-responsive padding-10">			
				<table class="mytable pd-10" border="1" style="width:100%; border-collapse:collapse; border:1px solid #eee;" border="1">
				<thead>
				<tr>
					<td colspan="4">
					<div style="display:flex; gap:5px;">
						<select name="pagesize" id="pagesize" class="select2" onchange="loadData(1,'{{ route('notification.html') }}')" data-width="85">
							<option value="50">50</option>
							<option value="100">100</option>
							<option value="300">300</option>
							<option value="500">500</option>														
						</select>
						<input type="text" class="todays_dt padding-8 br-5 width-100" name="frmdate" id="frmdate" value="" onchange="loadData(1,'{{ route('notification.html') }}')">
						<input type="text" class="todays_dt padding-8	 br-5 width-100" name="todate" id="todate" value="" onchange="loadData(1,'{{ route('notification.html') }}')">

						<select name="deptid" id="deptid" class="select2" onchange="loadData(1,'{{ route('notification.html') }}')" data-placeholder="Department">
							<option value=""></option>
							@foreach($users as $dept)
							@if($dept->isdepartment==1)
							<option value="{{$dept->departmentid}}">{{$dept->name}}</option>
							@endif
							@endforeach
						</select>

						<select name="pmid" id="pmid" class="select2" onchange="loadData(1,'{{ route('notification.html') }}')" data-placeholder="Project Manager">
							<option value=""></option>
							@foreach($users as $pm)
							@if($pm->ispm==1)
							<option value="{{$pm->departmentid}}">{{$pm->name}}</option>
							@endif
							@endforeach
						</select>

						<select name="vid" id="vid" class="select2" onchange="loadData(1,'{{ route('notification.html') }}')" data-placeholder="Firm Name">
							<option value=""></option>
							@foreach($firms as $firm)
							<option value="{{$firm->vendorid}}">{{$firm->name}}</option>
							@endforeach
						</select>
						
					</div>
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
@endpermission
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
<script src="{{ asset('panel/assets/js/ace-elements.min.js') }}"></script>
<script src="{{ asset('panel/assets/js/ace.min.js') }}"></script>
<script src="{{ asset('panel/assets/tinymce/tinymce.min.js') }}"></script>
<script>

function loadData(page, r1) {
	var pagesize 	= 	document.getElementById("pagesize").value;
	var departmentid= 	document.getElementById("deptid").value;
	var managerid	= 	document.getElementById("pmid").value;
	var vendorid	= 	document.getElementById("vid").value;
	var frmdate		= 	document.getElementById("frmdate").value;
	var todate		= 	document.getElementById("todate").value;
	$.get("" + r1,
	{
		page: page,
		pagesize: pagesize,
		departmentid: departmentid,
		managerid: managerid,
		vendorid: vendorid,
		frmdate:frmdate,
		todate:todate
	},
	function (data, status) {
		$(".tabledata").html(data);
	});
}


tinymce.init({
    selector: 'textarea.tinymce',
    promotion: false,
    branding: false,
    plugins: 'autoresize code advlist autolink lists charmap preview table searchreplace save',
    toolbar_mode: 'floating',
    toolbar: 'undo redo | formatselect | bold italic underline strikethrough | alignleft aligncenter alignright alignjustify | bullist numlist | table',
    menubar: false,
    statusbar: false,
    min_height: 60,
    autoresize_min_height: 60,
    autoresize_max_height: 490,
    autoresize_bottom_margin: 10,
    resize: true,
    setup: function (editor) {
        editor.on('init', function () {
            editor.getBody().style.fontSize = '14px';
            editor.getBody().style.lineHeight = '1.5';
        });
    }
});

$('#notification_attachment').ace_file_input({
	no_file: 'No File ...',
	btn_choose: 'Attachment (Optional)',
	btn_change: 'Change',
	btn_name: 'btnname',
	thumbnail: false //| true | large
})


$(document).ready(function () {

    $('#select_all_dept').on('change', function () {
        $('.department_checkbox').prop('checked', this.checked);
    });
    $('.department_checkbox').on('change', function () {
        if (!this.checked) {
            $('#select_all_dept').prop('checked', false);
        }
        if ($('.department_checkbox:checked').length === $('.department_checkbox').length) {
            $('#select_all_dept').prop('checked', true);
        }
    });
    $('#department_search').on('keyup', function () {
        let value = $(this).val().toLowerCase();
        $('.department-row').filter(function () {
            $(this).toggle(
                $(this).find('.department-text').text().toLowerCase().indexOf(value) > -1
            );
        });
    });

    $('#select_all_manager').on('change', function () {
        $('.manager_checkbox').prop('checked', this.checked);
    });
    $('.manager_checkbox').on('change', function () {
        if (!this.checked) {
            $('#select_all_manager').prop('checked', false);
        }
        if ($('.manager_checkbox:checked').length === $('.manager_checkbox').length) {
            $('#select_all_manager').prop('checked', true);
        }
    });
    $('#manager_search').on('keyup', function () {
        let value = $(this).val().toLowerCase();
        $('.manager-row').filter(function () {
            $(this).toggle(
                $(this).find('.manager-text').text().toLowerCase().indexOf(value) > -1
            );
        });
    });

    $('#select_all_vendor').on('change', function () {
        $('.vendor_checkbox').prop('checked', this.checked);
    });
    $('.vendor_checkbox').on('change', function () {
        if (!this.checked) {
            $('#select_all_vendor').prop('checked', false);
        }
        if ($('.vendor_checkbox:checked').length === $('.vendor_checkbox').length) {
            $('#select_all_vendor').prop('checked', true);
        }
    });
    $('#vendor_search').on('keyup', function () {
        let value = $(this).val().toLowerCase();
        $('.vendor-row').filter(function () {
            $(this).toggle(
                $(this).find('.vendor-text').text().toLowerCase().indexOf(value) > -1
            );
        });
    });

    $('#select_all_vendorawd').on('change', function () {
        $('.vendorawd_checkbox').prop('checked', this.checked);
    });
    $('.vendorawd_checkbox').on('change', function () {
        if (!this.checked) {
            $('#select_all_vendorawd').prop('checked', false);
        }
        if ($('.vendorawd_checkbox:checked').length === $('.vendorawd_checkbox').length) {
            $('#select_all_vendorawd').prop('checked', true);
        }
    });
    $('#vendorawd_search').on('keyup', function () {
        let value = $(this).val().toLowerCase();
        $('.vendorawd-row').filter(function () {
            $(this).toggle(
                $(this).find('.vendorawd-text').text().toLowerCase().indexOf(value) > -1
            );
        });
    });

});

$('.notificationBtn').on('click', function(e) {
	bootbox.confirm('Do you confirm this action?',function(result){
		if(result)
		{
			e.preventDefault();
			tinymce.triggerSave();
			var form = $('#frm')[0];
			var formData = new FormData(form);
			$.ajax({
				url: $('#frm').attr('action'),
				method: 'POST',
				data: formData,
				processData: false,
				contentType: false,
				success: function(response) {
					if(response.status === 200)
					{
						bootbox.alert({
							message: response.message,
							callback: function () {
								window.location.reload();
							}
						});			
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
	});
});

</script>

<script src="{{ asset('panel/assets/js/master.js') }}"></script>
<script src="{{ asset('panel/assets/js/datatablePaginationWithFilter.js') }}"></script>
@endsection