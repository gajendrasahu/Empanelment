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
<div class="tabbable" style="padding:0px 0px!important;">
<div class="eoi-section-header" style="padding:8px 8px 16px 8px;">

	<div style="font-size:20px; font-weight:600; color:#2c3e50;">
		LOI Based Work Order
	</div>

	<div style="font-size:14px; color:#6c757d; margin:2px 0px; 8px 0">
		Create a new work order by capturing project scope, dates, and commercial terms in one
		place.
	</div>

</div>
<div class="tab-content no-border" style="min-height:100px; padding:0px 10px;">
	<div id="home" class="tab-pane in active" style="padding: 0 16px;">
		<div class="row">
			<div class="row">
<form name="frm" id="frm" action="#" data-url="{{ route('store.workorder') }}" method="post" enctype="multipart/form-data">
@csrf
@permission('store.directworkorder')
<div class="form-group">
		@foreach($category as $cat)
		<a href="{{route('loibased.order',Crypt::encrypt($cat->categoryid))}}">
			<button type="button" class="btn btn-info" style="margin-right:10px;">{{$cat->jobcategory}}</button>
		</a>
		@endforeach
</div>
@endpermission
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
jQuery(function ($) {

	$('#momfile').ace_file_input({
		no_file: 'No File ...',
		btn_choose: 'Upload MoM*',
		btn_change: 'Change',
		btn_name: 'fourthimage',
		thumbnail: false //| true | large
	});

	$('#markingsheet').ace_file_input({
		no_file: 'No File ...',
		btn_choose: 'Upload Marking Sheet*',
		btn_change: 'Change',
		btn_name: 'fourthimage',
		thumbnail: false //| true | large
	});
	$('#notesheet').ace_file_input({
		no_file: 'No File ...',
		btn_choose: 'Upload Note Sheet*',
		btn_change: 'Change',
		btn_name: 'fourthimage',
		thumbnail: false //| true | large
	});
	$('#signedcopy').ace_file_input({
		no_file: 'No File ...',
		btn_choose: 'Upload Order Copy*',
		btn_change: 'Change',
		btn_name: 'fourthimage',
		thumbnail: false //| true | large
	});

});


</script>
<script src="{{ asset('panel/assets/js/master.js') }}"></script>
<script src="{{ asset('panel/assets/js/datatablePaginationWithFilter.js') }}"></script>
@endsection