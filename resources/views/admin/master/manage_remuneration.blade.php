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
							<ul class="nav nav-tabs padding-0">
								@if(in_array(1, Session::get('actions')))
									<li class="active">
										<a data-toggle="tab" href="#home">
											<i class="green ace-icon fa fa-plus-circle"></i>
											<span style="margin-top:-3px;">Create Price List</span>
										</a>
									</li>
								@endif
							</ul>


							<div class="tab-content no-border"
								style="border:1px solid #ddd; min-height:100px; padding:0px 10px;">
								@if(in_array(1, Session::get('actions')))
									<div id="home" class="tab-pane in active" style="padding:0px 10px!important;">
										<div class="row">
											<div class="row">
												<form name="frm" id="frm" action="{{ route('store.ratelist', 0)}}" method="post"
													enctype="multipart/form-data">
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
<div class="table-responsive">
	<table class="mytable pd-10" style="border:1px solid #eee!important; border-collapse:collapse!important;" border="1">
		<thead>
			<tr>
				<td class="center"><b>S. No.</b></td>
				<td><b>Rate List</b></td>
				<td class="center"><b>Start Date</b></td>
				<td class="center"><b>End Date</b></td>
				<td><b>Category</b></td>
				<td><b>Incremented By</b></td>
				<td></td>
			</tr>
		</thead>
		<tbody>
		@foreach($rates as $rate)
		<tr>
			<td class="center">{{$loop->iteration}}</td>
			<td>{{$rate->ratelist_name}}<br><span style="font-size:10px;">Created on : {{date('d\-m\-Y, h:i A',strtotime($rate->created_on))}}</span></td>
			<td class="center">{{date('d\-m\-Y',strtotime($rate->startDate))}}</td>
			<td class="center">{{date('d\-m\-Y',strtotime($rate->endDate))}}</td>
			<td>{{$rate->jobcategory}}</td>
			<td>{{$rate->incremented_by}}</td>
			<td>
				<a href="{{route('create.ratelist',Crypt::encrypt($rate->rateid))}}"><button type="button" class="btn btn-info gridbtn"><i class="fa fa-list"></i> Create New</button></a>
			</td>
		</tr>
		@endforeach
		</tbody>
	</table>
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
@endsection