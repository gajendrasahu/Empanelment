@extends('admin.admin_master')
@section('admin')
@php
$t=0;
@endphp

<div class="main-content">
<style>
.ace-file-input .ace-file-container
{
	height:35px!important;
}
.ace-file-input .ace-file-container:before
{
	line-height:30px!important;
}
</style>
	<div class="main-content-inner">
			<div class="page-content" style="padding:10px 15px!important;">
				<div class="ace-settings-container" id="ace-settings-container"></div>
				<div class="row">
					<div class="col-xs-12">
					<!-- PAGE CONTENT BEGINS -->
<div class="tabbable animated fadeInUp delay-02" style="padding:0px 0px!important;">
		<ul class="nav nav-tabs padding-0">
		@if(in_array(1,Session::get('actions')))
			<li class="active">
				<a data-toggle="tab" href="#home" class="form-label">
					<i class="green ace-icon fa fa-close bigger-120 datalist" style="vertical-align:text-top;"></i> Project Closure Confirmation
				</a>
			</li>
		@endif
		</ul>

	
		<div class="tab-content no-border" style="border:1px solid #ddd; min-height:100px; padding:0px 10px;">
<div id="home" class="tab-pane in active" style="padding:0px 10px!important;">
	<div class="row">
			<form name="frm" id="frm" action="{{ route('close.confirmation',Crypt::encrypt($data->requestid))}}" method="post" enctype="multipart/form-data">
				@csrf
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
				<br>
				<div class="col-sm-12">
				    <div class="alert alert-block alert-danger">
				    	<button type="button" class="close" data-dismiss="alert">
							<i class="ace-icon fa fa-times"></i>
						</button>
						<ul>
							@foreach ($errors->all() as $error)
								<i class="fa fa-warning"></i> {{ $error }}<br>
							@endforeach
						</ul>
				    </div>
				</div>
				@endif				
				
				@csrf
				<div class="form-group">
					<div class="content-detail">
					
						<div class="ui-card-detail">
							<div>
								<div class="title-detail">
									<i class="material-icons-outlined">topic</i> {{ $data->projecttitle }}
								</div>
								<div class="details-detail lh-30">
									<input type="hidden" name="form_token" id="form_token" value="{{$token}}">
									
									<b>Department / Project Manager : </b>{{ $data->name}}<br>
									EoI Number: {{$data->eoinumber}}
									|									
									 Requested on : <b>{{ date('d\-m\-Y, h:i A',strtotime($data->creationdate)) }}</b> | Project Duration : <b>{{ ucwords(strtolower($data->projectduration)) }} Months</b><br>
									
								</div>
							</div>
						</div>
						
						<div class="section-block-detail">
							<div class="section-header-detail">
								<span class="material-icons">playlist_add_check</span> <!-- Hamburger menu icon -->
								&nbsp;<h2 class="section-title-detail">Please review carefully before proceeding.</h2>
							</div>
							<div class="grid-layout-detail cols-2">
								<ul>
									<li>This action will permanently close the project.</li>
									<li>Once closed, the project cannot be reopened or reverted.</li>
									<li>All associated work orders will be marked as Inactive.</li>
									<li>No new activities, updates, or transactions can be performed on this project after closure.</li>
									<li>Any pending processes related to this project may be affected.</li>
									<li>Please ensure all project activities have been completed before proceeding.</li>
								</ul>
								<b>Are you sure you want to close this project?</b>
							</div>

					<table class="mytable" style="border:none!important;">
						<tr>
							<td style="width:200px!important;">
							<input type="text" class="todays_dt_blank form-control" name="closedOn" id="closedOn" required value="{{ old('closedOn') }}" placeholder="Closing date (dd-mm-YYYY)" style="width:200px;">
							</td>
							<td class="padding-10" style="width:350px!important;">
								<input type="file" required name="closure_file" id="closure_file" accept="application/pdf" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}">
							</td>
							<td>
							@if($data->isClosed==0 && $data->isordered==1)
							<button type="button" class="btn btn-info myfrmbtn width-130 close-btn" style="float:right;">
								<i class="fa fa-close"></i> Close Project
							</button>
							@else
							<span class="bg bg-warning padding-10" style="float:right;"><b>Project and Work Orders Closed</b></span>
							@endif
							</td>
						</tr>
					</table>

						</div>
						

<div class="col-sm-12">
	

	
</div>
						
						
						
						
					</div>
				

<div class="col-sm-12">&nbsp;</div>
				</div>		
			</form>
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
<script src="{{ asset('panel/assets/tinymce/tinymce.min.js') }}"></script>
<script src="{{ asset('panel/assets/js/ace-elements.min.js') }}"></script>
<script src="{{ asset('panel/assets/js/ace.min.js') }}"></script>

<script>

$('.close-btn').on('click', function(e) {
	bootbox.confirm('Do you confirm this action?',function(result){
		if(result)
		{
			$("#frm").submit();
		}
	});
});

$('#closure_file').ace_file_input({
	no_file: 'No File ...',
	btn_choose: 'Supporting Document (if any)',
	btn_change: 'Change',
	btn_name: 'fourthimage',
	thumbnail: false //| true | large
});

</script>
<script src="{{ asset('panel/assets/js/master.js') }}"></script>
<script src="{{ asset('panel/assets/js/datatablePaginationWithFilter.js') }}"></script>
@endsection