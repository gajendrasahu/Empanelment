@extends('admin.admin_master')
@section('admin')
@php
$t=0;
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
		@if(in_array(1,Session::get('actions')))
			<li class="active">
				<a data-toggle="tab" href="#home" class="form-label font-14">
					<i class="green ace-icon fa fa-plus-circle bigger-120 datalist"></i> Work Order Issued
				</a>
			</li>
		@endif
		</ul>

	
		<div class="tab-content no-border" style="border:1px solid #ddd; min-height:100px; padding:0px 10px;">
@if(in_array(1,Session::get('actions')))
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
				@csrf
				<div class="form-group">
					<div class="content-detail">
						<div class="ui-card-detail">
							<div>
								<div class="title-detail">
									<i class="material-icons-outlined">topic</i> Order Issued Successfully
								</div>
								<div class="details-detail">
									The order has been successfully issued to the selected vendor.<br>
								</div>
							</div>
						</div>

						<div class="ui-card-detail">
							<div>
								<div class="title-detail">
									<i class="fa fa-user"></i> Firm Details
								</div>
								<table class=""  style="text-transform: none!important;">
									<tr>
										<td class="padding-10 font-bold">Firm Name</td>
										<td class="padding-10">{{$vendor->companyname}}</td>
									</tr>
									<tr>
										<td class="padding-10 font-bold">Address</td>
										<td class="padding-10">{{$vendor->officelocation}}</td>
									</tr>
									<tr>
										<td class="padding-10 font-bold">Signed Order Copy</td>
										<td class="padding-10" style="position:relative;">
								<a href="{{ route('view.uploadedfile',Crypt::encrypt($order->signedcopy))}}" style="position:absolute; left:10px; top:10px;" target="_blank">
									<span class="label label-white middle animated flipInX delay-02 f-s-10 form-label span-content">
										<i class="fa fa-file-pdf-o"></i> Signed Order Copy
									</span>
								</a>
										</td>
									</tr>
									
								</table>
							</div>
						</div>

						<div class="ui-card-detail">
							<div>
								<div class="title-detail">
									<i class="material-icons-outlined">topic</i> EoI Detail
								</div>
								<table class="" style="text-transform: none!important;">
									<tr>
										<td class="padding-10 font-bold">EoI Reference No</td>
										<td class="padding-10">{{$eoi->eoinumber}}</td>
									</tr>
									<tr>
										<td class="padding-10 font-bold">EoI Title</td>
										<td class="padding-10">{{$eoi->projecttitle}}</td>
									</tr>
									<tr>
										<td class="padding-10 font-bold">Order Date</td>
										<td class="padding-10">{{date('d\-m\-Y',strtotime($order->orderdate))}}</td>
									</tr>
									<tr>
										<td colspan="2" class="padding-10">
											<a href="{{route('workorder.list')}}">
												<span class="btn btn-info">
													<i class="fa fa-angle-double-left"></i> Work Order List
												</span>							
											</a>									
										</td>
									</tr>
									<tr><td class="padding-10"></td></tr>
								</table>

							</div>
							
						</div>
					
					</div>
				</div>		
			</form>
		</div>
	</div>
</div>
@endif


</div>
</div>


						
				</div><!-- /.col -->
			</div><!-- /.row -->
		</div><!-- /.page-content -->
	
		
	</div>
</div>
<script src="{{ asset('panel/assets/js/jquery-2.1.4.min.js') }}"></script>
<script src="{{ asset('panel/assets/tinymce/tinymce.min.js') }}"></script>

<script>
</script>


<script src="{{ asset('panel/assets/js/datatablePaginationWithFilter.js') }}"></script>
@endsection