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
					<i class="green ace-icon fa fa-close bigger-120 datalist" style="vertical-align:text-top;"></i> Cancellation Confirmation
				</a>
			</li>
		@endif
		</ul>

	
		<div class="tab-content no-border" style="border:1px solid #ddd; min-height:100px; padding:0px 10px;">
<div id="home" class="tab-pane in active" style="padding:0px 10px!important;">
	<div class="row">
			<form name="frm" id="frm" action="{{ route('cancel.confirmation',Crypt::encrypt($data->orderid))}}" method="post" enctype="multipart/form-data">
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
									<i class="material-icons-outlined">topic</i> {{ $data->subject }}
								</div>
								<div class="details-detail lh-30">
									<input type="hidden" name="form_token" id="form_token" value="{{$token}}">
									
									<b>@if($data->ispm==0) Department @else Project Manager @endif : </b>{{ $data->departmentname}}<br>
									Work Order Number: <b>{{$data->ordernumber}}</b>
									|									
									 Work Order Date : <b>{{ date('d\-m\-Y, h:i A',strtotime($data->orderdate)) }}</b> @if($data->workorderduedate)| Work Order Due Date : <b>{{ date('d\-m\-Y',strtotime($data->workorderduedate)) }}@endif</b><br>
									
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
									<li>This action will permanently cancel the work order.</li>
									<li>Once cancelled, the work order cannot be reopened or reverted.</li>
									<li>No new activities, updates, or transactions can be performed on this work order after closure.</li>
									<li>Any pending processes related to this work order may be affected.</li>
									<li>Please ensure all work order activities have been completed before proceeding.</li>
								</ul>
								<b>Are you sure you want to cancel this work order?</b>
							</div>

							<table class="mytable" style="border:none!important;">
								<tr>
									<td style="width:200px!important;">
									<input type="text" class="todays_dt_blank form-control" name="cancelled_on" id="cancelled_on" required value="{{ old('cancelled_on') }}" placeholder="Cancel date (dd-mm-YYYY)" style="width:200px;">
									</td>
									<td class="padding-10" style="width:350px!important;">
										<input type="file" required name="cancellation_file" id="cancellation_file" accept="application/pdf" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}">
									</td>
									<td>
									@if($data->isClosed==0 && $data->isCancelled==0)
									<button type="button" class="btn btn-info myfrmbtn width-130 close-btn" style="float:right;">
										<i class="fa fa-close"></i> Cancel Order
									</button>
									@else
									<span class="bg bg-warning padding-10" style="float:right;"><b>Work Order Cancelled</b></span>
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

$('#cancellation_file').ace_file_input({
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