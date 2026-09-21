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
					<i class="green ace-icon fa fa-list-alt bigger-120 datalist"></i> Deployment Detail
				</a>
			</li>
		@endif
		</ul>

	
		<div class="tab-content no-border" style="border:1px solid #ddd; min-height:100px; padding:0px 10px;">
@if(in_array(1,Session::get('actions')))
<div id="home" class="tab-pane in active" style="padding:0px 10px!important;">
	<div class="row">
		
			<form name="setdeploymentdate" id="setdeploymentdate" action="{{route('update.vendordeployment',Crypt::encrypt($order->orderid))}}" method="post" enctype="multipart/form-data">
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
						<div class="ui-card-detail" style="line-height:25px;">
							<div>
								<div class="title-detail">
									<i class="material-icons-outlined">fact_check</i> Order Number : {{ $order->ordernumber }}
								</div>
								<div class="details-detail">
									<b class="form-label">Order Date : </b>{{ date('d\-m\-Y',strtotime($order->orderdate)) }}							
								</div>
							</div>
						</div>
						<div class="ui-card-detail">
							<div>
								<div class="title-detail">
									<i class="material-icons-outlined">topic</i> {{ $order->project_name }}
								</div>
								<div class="details-detail" style="line-height:25px;">
									<b>Department / Project Manager : </b>{{ ucwords(strtolower($order->departmentname)) }}<br>
									@if($order->eoinumber)EoI Number: <b>{{ $order->eoinumber }}</b> | Requested On : <b>{{ date('d\-m\-Y, h:i A',strtotime($order->orderdate)) }}</b> | @endif Project Duration : <b>{{ ucwords(strtolower($order->project_duration)) }} Months</b><br>
									
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
									<td class="padding-10 font-bold">Sector & Position</td>
									<td class="padding-10 font-bold" nowrap>Employee Code & Name</td>
									<td class="padding-10 font-bold">Mobile Number & Email</td>
									<td class="padding-10 font-bold center" nowrap>Joining Date</td>
								</tr>
								@php
								$totalmanmonth		=	0;
								$adminchargetotal	=	0;
								$grandtotal			=	0;
								$actives			=	0;
								$pendings			=	0;
								@endphp
								@foreach($detail as $index=>$req)
								<tr>
									<td class="padding-10 center v-top">{{$loop->iteration}}</td>
									<td class="padding-10">
										<p>{{ucwords(strtolower($req->sectorname))}}</p><p>{{$req->consultantposition}}</p>{{$req->experience}}<br>
										@if($req->deployment_status=='Active')
										<span class="eoi-flag eoi-flag--success">
										<i class="fa fa-thumbs-up"></i> {{$req->deployment_status}}
										</span>	
										@elseif($req->deployment_status=='Released')
										<span class="eoi-flag eoi-flag--success">
										<i class="fa fa-hand-stop-o"></i> {{$req->deployment_status}}
										</span><br><br>
										<span class="eoi-flag eoi-flag--success">
										Date : {{date('d\-m\-Y',strtotime($req->lastdate))}}
										</span>
										@else
										<span class="eoi-flag eoi-flag--success">
										{{$req->deployment_status}}
										</span>
										
										@endif
										
									</td>
									<td class="padding-10">
										<input type="hidden" name="deploymentid[]" value="{{Crypt::encrypt($req->deploymentid)}}">
										<input type="text" name="employee_code[]" value="{{old('employee_code.'.$index,$req->employee_code)}}" class="form-control" placeholder="Exm. EM-109" autocomplete="off">
										<input type="text" name="candidatename[]" value="{{old('candidatename.' . $index, $req->name)}}" class="form-control" placeholder="Name" autocomplete="off">
									</td>
									<td class="padding-10">
										<input type="text" name="mobilenumber[]" value="{{old('mobilenumber.' . $index, $req->mobilenumber)}}" class="form-control" placeholder="Mobile number" autocomplete="off">
										<input type="text" name="email[]" value="{{old('email.' . $index, $req->email)}}" class="form-control" placeholder="Email" autocomplete="off">
									</td>
									<td class="padding-10 width-150 v-top">
										<input type="text" name="deployeddate[]" @if($req->deployed_date)value="{{date('d\-m\-Y',strtotime($req->deployed_date))}}"@endif class="form-control todays_dt_blank" placeholder="dd-mm-YYYY" autocomplete="off">
									</td>
								</tr>
								@if($req->deployment_status==='Pending') @php $pendings++ @endphp @endif
								@endforeach
								<tr>
									<td class="padding-10" style="text-align:right;" colspan="7">
									<a href="{{route('vendor.orderslist')}}">
										<button type="button" class="btn btn-info" style="float:left;">
											<i class="fa fa-arrow-left"></i> Back
										</button>
									</a>
									@if($pendings>0)
									@if($order->isActiveOrder==1 && \Carbon\Carbon::parse($order->workorderduedate)->gte(today()))
									<button type="button" class="btn btn-info" onclick="SetDeploymentDate()">
										<i class="fa fa-refresh"></i> Update Deployment
									</button>
									@endif
									@else
									@if($order->isActiveOrder==1 && \Carbon\Carbon::parse($order->workorderduedate)->gte(today()))
									<button type="button" class="btn btn-info" onclick="SetDeploymentDate()">
										<i class="fa fa-info-circle"></i> All Resource Deployed & Verified
									</button>
									@endif
									@endif
									</td>
								</tr>
								
							</table>
							@else
							<table class="mytable" border="1" style="text-transform: none!important;">
								<tr class="myheadbg">
									<td class="center padding-10 font-bold" style="width:30px;">S.No.</td>
									<td class="padding-10 font-bold">Position & Experience</td>
									<td class="padding-10 font-bold">Employee Code & Name</td>
									<td class="padding-10 font-bold">Mobile Number & Email</td>
									<td class="padding-10 font-bold center">Joining Date</td>
								</tr>
								@php
								$totalmanmonth		=	0;
								$adminchargetotal	=	0;
								$grandtotal			=	0;
								$actives			=	0;
								$pendings			=	0;
								@endphp
								@foreach($detail as $index=>$req)
								<tr>
									<td class="padding-10 center v-top">{{$loop->iteration}}</td>
									<td class="padding-10">
									{{ucwords(strtolower($req->role))}}<br>[L-{{$req->experiencelevel}}]<br>{{$req->experience}}<br>
										@if($req->deployment_status=='Active')
										<span class="eoi-flag eoi-flag--success">
										<i class="fa fa-thumbs-up"></i> {{$req->deployment_status}}
										</span>	
										@elseif($req->deployment_status=='Released')
										<span class="eoi-flag eoi-flag--success">
										<i class="fa fa-hand-stop-o"></i> {{$req->deployment_status}}
										</span><br><br>
										<span class="eoi-flag eoi-flag--success">
										Date : {{date('d\-m\-Y',strtotime($req->lastdate))}}
										</span>
										@else
										<span class="eoi-flag eoi-flag--success">
										{{$req->deployment_status}}
										</span>
										
										@endif
									
									</td>
									<td class="padding-10">
										<input type="hidden" name="deploymentid[]" value="{{Crypt::encrypt($req->deploymentid)}}">
										<input type="text" name="employee_code[]" value="{{old('employee_code.'.$index,$req->employee_code)}}" class="form-control" placeholder="Exm. EM-109" autocomplete="off">
										<input type="text" name="candidatename[]" value="{{old('candidatename.' . $index, $req->name)}}" class="form-control" placeholder="Name" autocomplete="off">
									</td>
									<td class="padding-10">
										<input type="text" name="mobilenumber[]" value="{{old('mobilenumber.' . $index, $req->mobilenumber)}}" class="form-control" placeholder="Mobile number" autocomplete="off">
										<input type="text" name="email[]" value="{{old('email.' . $index, $req->email)}}" class="form-control" placeholder="Email" autocomplete="off">

									</td>
									<td class="padding-10 width-150 v-top">
										<input type="text" name="deployeddate[]" @if($req->deployed_date)value="{{date('d\-m\-Y',strtotime($req->deployed_date))}}"@endif class="form-control todays_dt_blank" placeholder="dd-mm-YYYY" autocomplete="off">
									</td>
								</tr>
								@if($req->deployment_status==='Pending') @php $pendings++ @endphp @endif
								@endforeach
								<tr>
									<td class="padding-10" style="text-align:right;" colspan="7">
									<a href="{{route('vendor.orderslist')}}">
										<button type="button" class="btn btn-info" style="float:left;">
											<i class="fa fa-arrow-left"></i> Back
										</button>
									</a>
									@if($pendings>0)
									@if($order->isActiveOrder==1 && \Carbon\Carbon::parse($order->workorderduedate)->gte(today()))
									<button type="button" class="btn btn-info" onclick="SetDeploymentDate()">
										<i class="fa fa-refresh"></i> Update Deployment
									</button>
									@endif
									@else
									@if($order->isActiveOrder==1 && \Carbon\Carbon::parse($order->workorderduedate)->gte(today()))
									<button type="button" class="btn btn-info" onclick="SetDeploymentDate()">
										<i class="fa fa-info-circle"></i> All Resource Deployed & Verified
									</button>									
									@endif
									@endif
									</td>
								</tr>
								
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
function SetDeploymentDate()
{
	let codes 	=	document.getElementsByName("employee_code[]");
    let names 		=	document.getElementsByName("candidatename[]");
    let numbers 	=	document.getElementsByName("mobilenumber[]");
    let emails 		=	document.getElementsByName("email[]");
	let deployeds 	=	document.getElementsByName("deployeddate[]");


    let atLeastOneFilled = false;

    for(let i = 0; i < names.length; i++)
	{
		if(deployeds[i].value.trim()!== "" || codes[i].value.trim()!== "" || names[i].value.trim()!== "" || numbers[i].value.trim()!== "" || emails[i].value.trim()!== "")
		{
            atLeastOneFilled = true;
            break;
        }
    }

    if (!atLeastOneFilled) {
        bootbox.alert("Please update at least one record.");
        return;
    }
	finalSubmit();
}
function finalSubmit()
{
	bootbox.confirm('Do you want to submit the deployment date details?',function(result){
		if(result)
		{
			$("#setdeploymentdate").submit();
		}
	});
}
</script>


<script src="{{ asset('panel/assets/js/datatablePaginationWithFilter.js') }}"></script>
@endsection