@extends('admin.admin_master')
@section('admin')
@php
$t=0;
@endphp

<div class="main-content">
<style>
.ace-file-container
{
	height:33px!important;
	line-height:33px!important;
	vertical-align:middle!important;
	padding:2px 5px!important;
}
</style>
	<div class="main-content-inner">
			<div class="page-content" style="padding:10px 15px!important;">
				<div class="ace-settings-container" id="ace-settings-container"></div>
				<div class="row">
					<div class="col-xs-12">
					<!-- PAGE CONTENT BEGINS -->
<div class="tabbable" style="padding:0px 0px!important;">
		<ul class="nav nav-tabs padding-0">
			<li class="active"><a data-toggle="tab" href="#home" onclick="loadData('{{ route('prebid.querieshtml') }}')"><i class="green ace-icon fa fa-angle-double-right bigger-120 datalist" style="vertical-align:text-top;"></i>Official Reply to Pre-bid Query</a></li>
		</ul>

	
		<div class="tab-content no-border" style="border:1px solid #ddd; min-height:100px; padding:0px 10px;">
<div id="home" class="tab-pane in active" style="padding:0px 10px!important;">
	<div class="row">
		<div class="row">
			<form name="frm" id="frm" action="" method="post" enctype="multipart/form-data">
				@if(Session::has('success'))
				<div class="col-sm-12">
					<div class="alert alert-block alert-success">
				    	<button type="button" class="close" data-dismiss="alert">
							<i class="ace-icon fa fa-times"></i>
						</button>
						{{ Session::get('success') }}
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
				@if ($errors->any())
					<div class="col-sm-12">
						<div class="alert alert-block alert-danger">
							<button type="button" class="close" data-dismiss="alert">
								<i class="ace-icon fa fa-times"></i>
							</button>
							@foreach ($errors->all() as $error)
								<i class="fa fa-warning"></i> {{ $error }}<br>
							@endforeach
						</div>
					</div>
				@endif		
				
				@csrf
				<div class="form-group">
					<div class="col-sm-12 tabledata">
<table class="mytable" border="1" style="text-transform: none!important;">
	<tr class="myheadbg">
		<td class="" style="padding:10px;">
			EoI Number : {{$eoi->eoinumber}} | Project Name : {{$eoi->projecttitle}}
		</td>
	</tr>
	@if($data)
		<tr>
			<td class="padding-5">
			Posted On : {{date('d\-m\-Y, h:i A',strtotime($data->broadcastedon))}}</b><br><br>
			{!!$data->broadcastmessage!!}
			@if($data->attachment!='')
			<br><br>
			<a href="{{ asset('storage/' . $data->attachment) }}" target="_blank" class="btn-success" style="padding:2px 5px; text-decoration:none;">
				<i class="fa fa-file-o"></i> View File
			</a>
			@endif
			</td>
		</tr>
	@endif
	<tr>
		<td class="" style="text-align:right;">
			<button type="button" class="btn btn-info myfrmbtn" onclick="history.back()" style="width:130px;">
				<i class="fa fa-arrow-left"></i> Back
			</button>		
		</td>
	</tr>
</table>
						
					</div>
					<div class="col-sm-12">&nbsp;</div>
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
<script src="{{ asset('panel/assets/js/jquery-2.1.4.min.js') }}"></script>
<script src="{{ asset('panel/assets/js/ace-elements.min.js') }}"></script>
<script src="{{ asset('panel/assets/js/ace.min.js') }}"></script>
<script>

jQuery(function($) {	

	$('#attachment').ace_file_input({
		no_file:'No File ...',
		btn_choose:'Attachment (if any)',
		btn_change:'Change',
		btn_name:'fourthimage',
		thumbnail:false //| true | large
	});
});	

</script>
<script src="{{ asset('panel/assets/js/datatablePaginationWithFilter.js') }}"></script>
@endsection