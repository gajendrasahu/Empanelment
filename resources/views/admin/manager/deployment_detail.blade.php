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
					<i class="green ace-icon fa fa-list-alt bigger-120 datalist"></i> Deployment Verification
				</a>
			</li>
		@endif
		</ul>

	
		<div class="tab-content no-border" style="border:1px solid #ddd; min-height:100px; padding:0px 10px;">
@if(in_array(1,Session::get('actions')))
<div id="home" class="tab-pane in active" style="padding:0px 10px!important;">
	<div class="row">
			<form name="setdeploymentdate" id="setdeploymentdate" action="{{route('update.pmdeploymentverification',Crypt::encrypt($order->orderid))}}" method="post" enctype="multipart/form-data">
				@if(Session::has('success'))
				<div class="col-sm-12" style="margin-top:20px!important;">
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
				@if($errors->has('orderno'))
				<div class="col-sm-12">
				    <div class="alert alert-block alert-danger">
				    	<button type="button" class="close" data-dismiss="alert">
							<i class="ace-icon fa fa-times"></i>
						</button>
				    	<span class="text-danger">@error('orderno') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }} </i> @enderror</span>
				    </div>
				</div>
				@endif
				@if($errors->has('setorderdate'))
				<div class="col-sm-12">
				    <div class="alert alert-block alert-danger">
				    	<button type="button" class="close" data-dismiss="alert">
							<i class="ace-icon fa fa-times"></i>
						</button>
				    	<span class="text-danger">@error('setorderdate') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }} </i> @enderror</span>
				    </div>
				</div>
				@endif
				@if($errors->any())
				<div class="col-sm-12" style="margin-top:20px!important;">
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
					<div class="content-detail">
						<div class="ui-card-detail">
							<div>
								<div class="title-detail">
									<i class="material-icons-outlined">topic</i> {{ $order->project_name }}
								</div>
								<div class="details-detail" style="line-height:25px;">
									<b>Vendor Name : </b>{{ ucwords(strtolower($vendor->companyname)) }}<br>
									Order No : <b>{{ $order->ordernumber }}</b> | Order Date : <b>{{ date('d\-m\-Y, h:i A',strtotime($order->orderdate)) }}</b><br>
									
								</div>
							</div>
						</div>
						<div class="section-block-detail">
							<div class="section-header-detail">
								<span class="material-icons-outlined">group_add</span>
								&nbsp;<h2 class="section-title-detail">Resource Details</h2>
							</div>
							<div class="table-responsive">
							@if($order->categoryid==2)
							<table class="mytable" border="1" style="text-transform: none!important;">
								<tr class="myheadbg">
									<td class="center padding-10 font-bold" style="width:30px;">S.No.</td>
									<td class="padding-10 font-bold">Sector / Position</td>
									<td class="padding-10 font-bold">Resource Detail</td>
									<td class="padding-10 center font-bold width-130">Date Of Joining</td>
									<td class="padding-10 center font-bold width-130">Deployment Status</td>
									<td class="padding-10 center font-bold width-130"></td>
								</tr>
								@php
								$totalmanmonth		=	0;
								$adminchargetotal	=	0;
								$grandtotal			=	0;
								$isexists			=	0;
								@endphp
								@foreach($detail as $index=>$req)
								<tr>
									<td class="padding-10 center v-top">{{$loop->iteration}}</td>
									<td class="padding-10 v-top">
										<p>{{$req->sectorname}}</p><p>{{$req->consultantposition}}</p>{{$req->experience}}
									</td>
									<td class="padding-10 v-top">
										
										{{$req->employee_code}}<br>{{$req->name}}<br>{{$req->mobilenumber}}<br>{{$req->email}}
									</td>
									<td class="padding-10 v-top center">
										@if(!is_null($req->deployed_date)){{date('d\-m\-Y',strtotime($req->deployed_date))}}@endif
									</td>
									<td class="padding-10 v-top center">
									{{$req->deployment_status}}
									</td>
									<td class="padding-10 v-top center">
										@if(!is_null($req->deployed_date))
											@if($req->deployment_status=='Active')
											<input type="checkbox" name="v[]" disabled checked><br>
											@else
											@php
											$isexists++;
											@endphp
											<input type="checkbox" name="verification[{{ Crypt::encrypt($req->deploymentid) }}]" value="1">
											@endif
										@endif
																		
									</td>
								</tr>
								@endforeach
								@if($isexists>0)
								<tr>
									<td class="padding-10" style="text-align:right;" colspan="7">
										<button type="button" class="btn btn-info" onclick="SetVerification()">
											<i class="fa fa-save"></i> Verify
										</button>
									</td>
								</tr>
								@endif
							</table>
							@endif
							@if($order->categoryid==1)
							<table class="mytable" border="1" style="text-transform: none!important;">
								<tr class="myheadbg">
									<td class="center padding-10 font-bold" style="width:30px;">S.No.</td>
									<td class="padding-10 font-bold">Position & Experience</td>
									<td class="padding-10 font-bold">Code</td>
									<td class="padding-10 font-bold">Resource Detail</td>
									<td class="padding-10 center font-bold width-130">Joining Date</td>
									<td class="padding-10 center font-bold width-150">Joining Status</td>
									<td class="padding-10 center font-bold width-130"></td>
								</tr>
								@php
								$totalmanmonth		=	0;
								$adminchargetotal	=	0;
								$grandtotal			=	0;
								$isexists			=	0;
								@endphp
								@foreach($detail as $index=>$req)
								<tr>
									<td class="padding-10 center v-top">{{$loop->iteration}}</td>
									<td class="padding-10 v-top">
										{{$req->role}}<br>[L-{{$req->experiencelevel}}]<br>{{$req->experience}}
									</td>
									<td class="padding-10 v-top">
										
										{{$req->employee_code}}
									</td>
									<td class="padding-10 v-top">
										
										{{$req->name}}<br>{{$req->mobilenumber}}<br>{{$req->email}}
									</td>
									<td class="padding-10 v-top center">
										@if(!is_null($req->deployed_date)){{date('d\-m\-Y',strtotime($req->deployed_date))}}@endif
									</td>
									<td class="padding-10 v-top center">
									{{$req->deployment_status}}
									</td>
									<td class="padding-10 v-top center">
										@if(!is_null($req->deployed_date))
											@if($req->deployment_status=='Active')
											<i class="fa fa-check"></i>
											@else
											@php
											$isexists++;
											@endphp
											<input type="checkbox" name="verification[{{ Crypt::encrypt($req->deploymentid) }}]" value="1">
											@endif
										@endif
																		
									</td>
								</tr>
								@endforeach
								@if($isexists>0)
								<tr>
									<td class="padding-10" style="text-align:right;" colspan="7">
										<button type="button" class="btn btn-info" onclick="SetVerification()">
											<i class="fa fa-save"></i> Verify
										</button>
									</td>
								</tr>
								@endif
							</table>
							@endif
							</div>
						</div>
						
					</div>
					<div class="col-sm-12">&nbsp;</div>
				</div>		
			</form>
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
<script src="{{ asset('panel/assets/js/ace-elements.min.js') }}"></script>
<script src="{{ asset('panel/assets/js/ace.min.js') }}"></script>

<script>
function SetVerification() {

	if($('input[name^="verification"]:checked').length === 0)
	{
		bootbox.alert("Please select at least one record.");
		return;
	}

	bootbox.confirm(
		'Do you confirm this verification? Once completed, it cannot be reverted.',
		function(result){
			if(result)
			{
				$("#setdeploymentdate").submit();
			}
		}
	);

}
</script>


<script src="{{ asset('panel/assets/js/datatablePaginationWithFilter.js') }}"></script>
@endsection