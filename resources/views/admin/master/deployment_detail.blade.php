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

<li class="active">
	<a data-toggle="tab" href="#home" class="form-label font-14">
		<i class="green ace-icon fa fa-list-alt bigger-120 datalist"></i> Resource Deployment
	</a>
</li>
</ul>

	
		<div class="tab-content no-border" style="border:1px solid #ddd; min-height:100px; padding:0px 10px;">

<div id="home" class="tab-pane in active" style="padding:0px 10px!important;">
	<div class="row">
			<form name="setdeploymentdate" id="setdeploymentdate" action="{{route('update.deploymentdate',Crypt::encrypt($order->orderid))}}" method="post" enctype="multipart/form-data">
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
				<div class="col-sm-12" style="margin-top:20px!important;">
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
									<i class="material-icons-outlined">fact_check</i> Order Number : {{ $order->ordernumber }}
								</div>
								<div class="details-detail">
								<b class="form-label">Order Date : <b>{{ date('d\-m\-Y',strtotime($order->orderdate)) }}</b> @if($order->workorderduedate)| Order Due Date : <b>{{ date('d\-m\-Y',strtotime($order->workorderduedate)) }}</b> @endif<br>
								
								</div>
								
							</div>
								<a href="{{route('view.uploadedfile',Crypt::encrypt($order->signedcopy))}}" target="_blank" class="action-a">
									<span class="label label-white middle animated flipInX delay-02 f-s-10 form-label span-content">
										<i class="fa fa-hand-o-right icon-animated-bell "></i> Signed Work Order File
									</span>
								</a>
						</div>
						<div class="ui-card-detail">
							<div>
								<div class="title-detail">
									<i class="material-icons-outlined">topic</i> {{ $order->project_name }}
								</div>
								<div class="details-detail lh-25">
									<b>@if($order->ispm==0)Department @else Project Manager @endif : </b>{{ $order->departmentname }}<br>
									@if($order->eoinumber)EoI Number: <b>{{ $order->eoinumber }}</b> | Release Date : {{ date('d\-m\-Y',strtotime($order->releasedate)) }}@endif 
									
								</div>
							</div>
						</div>
						<div class="ui-card-detail">
							<div>
								<div class="title-detail">
									<i class="material-icons-outlined">topic</i> {{ $vendor->companyname }}
								</div>
								<div class="details-detail">
									<label class="form-label">Official Email :</label> {{$vendor->officialemail}}
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
										<span class="eoi-flag eoi-flag--danger">
										<i class="fa fa-hand-stop-o"></i> {{$req->deployment_status}}
										</span><br><br>
										@if($req->lastdate)
											<span class="eoi-flag eoi-flag--danger">
												Date : {{date('d\-m\-Y',strtotime($req->lastdate))}}
											</span>
										@endif
										@else
										<span class="eoi-flag eoi-flag--success">
										{{$req->deployment_status}}
										</span>
										
										@endif
										@if($req->replaced_by_deployment_id!=0)
										<span class="eoi-flag eoi-flag--danger">
										<i class="fa fa-angle-double-right"></i> Replaced
										</span>
										@endif
									</td>
									<td class="padding-10 v-top">
										<input type="hidden" name="deploymentid[]" value="{{Crypt::encrypt($req->deploymentid)}}">
										<input type="text" name="employee_code[]" value="{{old('employee_code.'.$index,$req->employee_code)}}" class="form-control" placeholder="Exm. EM-109" autocomplete="off">

										<input type="text" name="candidatename[]" value="{{old('candidatename.' . $index, $req->name)}}" class="form-control" placeholder="Name" autocomplete="off">										
									</td>
									<td class="padding-10 v-top">
										<input type="text" name="mobilenumber[]" value="{{old('mobilenumber.' . $index, $req->mobilenumber)}}" class="form-control" placeholder="Mobile number" autocomplete="off">

										<input type="text" name="email[]" value="{{old('email.' . $index, $req->email)}}" class="form-control" placeholder="Email" autocomplete="off">

									</td>
									<td class="padding-10 v-top" style="text-align:center; width:100px;">
									@if($req->deployed_date)
									<input type="text" name="deployed_date[]" value="{{old('deployed_date.' . $index, date('d\-m\-Y',strtotime($req->deployed_date)))}}" class="form-control width-100 todays_dt_blank" placeholder="dd-mm-YYYY" autocomplete="off">
									@else
									<input type="text" name="deployed_date[]" value="{{old('deployed_date.' . $index, $req->deployed_date)}}" class="form-control width-100 todays_dt_blank" placeholder="dd-mm-YYYY" autocomplete="off">
									@endif
										@if($req->deployed_date)
									@endif
									@if($req->deployment_status=='Active')
									@permission('store.replaceresource')
									<a href="{{ route('workorder.replaceresource', Crypt::encrypt($req->deploymentid)) }}" class="btn btn-info gridbtn mt-5 padding-0">
										<i class="fa fa-refresh"></i> Replace
									</a>
									@endpermission
									@endif
									</td>
								</tr>
								@endforeach
								@if($order->isExtended==0)
								@if($detail->count()!=0)
								<tr>
									<td class="padding-10" style="text-align:right;" colspan="6">
									@permission('update.deploymentdate')
										<button type="button" class="btn btn-info" onclick="SetDeploymentDate()">
											<i class="fa fa-calendar"></i> Update
										</button>
									@endpermission
									</td>
								</tr>
								@endif
								@endif
							</table>
							@endif
							@if($order->categoryid==1)
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
								@endphp
								@foreach($detail as $index=>$req)
								<tr>
									<td class="padding-10 center v-top">{{$loop->iteration}}</td>
									<td class="padding-10">
									{{ucwords(strtolower($req->role))}}<br>Level-{{$req->experiencelevel}}<br>{{$req->experience}}
									<br>
										@if($req->deployment_status=='Active')
										<span class="eoi-flag eoi-flag--success">
										<i class="fa fa-thumbs-up"></i> {{$req->deployment_status}}
										</span>	
										@elseif($req->deployment_status=='Released')
										<span class="eoi-flag eoi-flag--success">
										<i class="fa fa-hand-stop-o"></i> {{$req->deployment_status}}
										</span><br><br>
										@if($req->lastdate) 
											<span class="eoi-flag eoi-flag--success">
												Date : {{date('d\-m\-Y',strtotime($req->lastdate))}}
											</span>
										@endif
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
									<td class="padding-10 v-top center width-100">
									@if($req->deployed_date)
									<input type="text" name="deployed_date[]" value="{{old('deployed_date.' . $index, date('d\-m\-Y',strtotime($req->deployed_date)))}}" class="form-control width-100 todays_dt_blank" placeholder="dd-mm-YYYY" autocomplete="off">
									@else
									<input type="text" name="deployed_date[]" value="{{old('deployed_date.' . $index, $req->deployed_date)}}" class="form-control width-100 todays_dt_blank" placeholder="dd-mm-YYYY" autocomplete="off">
									@endif
										@if($req->deployed_date)
									@endif
									@if($req->deployment_status=='Active')
									@permission('store.replaceresource')
									<a href="{{ route('workorder.replaceresource', Crypt::encrypt($req->deploymentid)) }}" class="btn btn-info gridbtn mt-5 padding-0">
										<i class="fa fa-refresh"></i> Replace
									</a>
									@endpermission
									@endif
								
									</td>
								</tr>
								@endforeach
								@if($order->isExtended==0)
								@if($detail->count()!=0)
								<tr>
									<td class="padding-10" style="text-align:right;" colspan="7">
										@permission('update.deploymentdate')
										<button type="button" class="btn btn-info" onclick="SetDeploymentDate()">
											<i class="fa fa-calendar"></i> Update
										</button>
										@endpermission
									</td>
								</tr>
								@endif
								@endif
							</table>

							@endif
							</div>
						</div>
						
					</div>

				</div>		
			</form>
			<form name="addresource" id="addresource" method="post" action="{{route('workorder.addresource')}}" encrypt="multipart/form-data">
			<div class="form-group">
			<div class="content-detail" style="margin-top:-40px;">
			<div class="section-block-detail">
			<div class="table-responsive">
			@if($order->categoryid==2)
			<table class="mytable pd-10" border="1" style="text-transform: none!important;">
				<tr class="myheadbg">
					<td class="font-bold">Sector & Position</td>
					<td class="font-bold" nowrap>Code & Name</td>
					<td class="font-bold">Mobile Number & Email</td>
					<td class="font-bold">Start & End Date</td>
					<td class="font-bold center" nowrap>Joining Date & Deployment Type</td>
				</tr>
				<tr>
					<td>
					<input type="hidden" name="order_id" id="order_id" value="{{Crypt::encrypt($order->orderid)}}">
					<input type="hidden" name="category_id" id="category_id" value="{{Crypt::encrypt($order->categoryid)}}">
					<input type="hidden" name="vendor_id" id="vendor_id" value="{{Crypt::encrypt($order->vendorid)}}">
					<select name="sector_id" id="sector_id" class="form-control">
						<option value="">--Sector--</option>
						@foreach($sectors as $sector)
						<option value="{{$sector->sectorid}}">{{$sector->sectorname}}</option>
						@endforeach
					</select>
					<select name="position_id" id="position_id" class="form-control">
						<option value="">--Position--</option>
						@foreach($positions as $position)
						<option value="{{$position->positionid}}">{{$position->consultantposition}}</option>
						@endforeach
					</select>
					</td>
					<td>
						<input type="text" name="resource_code" id="resource_code" class="form-control" placeholder="Code">
						<input type="text" name="resource_name" id="resource_name" class="form-control" placeholder="Name">
					</td>
					<td>
						<input type="text" name="resource_mobile" id="resource_mobile" class="form-control" placeholder="Mobile Number">
						<input type="text" name="resource_email" id="resource_email" class="form-control" placeholder="Email">
					</td>
					<td>
						<input type="text" name="resource_start_date" id="resource_start_date" class="form-control todays_dt_blank" placeholder="Start (dd-mm-YYYY)">
						<input type="text" name="resource_end_date" id="resource_end_date" class="form-control todays_dt_blank" placeholder="End (dd-mm-YYYY)">
					</td>
					<td class="width-100">
						<input type="text" name="resource_deployed_date" id="resource_deployed_date" class="todays_dt_blank form-control" placeholder="dd-mm-YYYY">

						<select name="deployment_type" id="deployment_type" class="form-control">
							<option value="FULL TIME">FULL TIME</option>
							<option value="PART TIME">PART TIME</option>
						</select>
					</td>
				</tr>
				<tr>
					<td colspan="5">
					@permission('workorder.addresource')
						<button type="button" class="form-control btn btn-info add_resource_btn width-150 right">
							<i class="fa fa-user"></i> Add Resource
						</button>					
					@endpermission
					</td>
				</tr>
			</table>
			@endif
			@if($order->categoryid==1)
			<table class="mytable pd-10" border="1" style="text-transform: none!important;">
				<tr class="myheadbg">
					<td class="font-bold">Role</td>
					<td class="font-bold" nowrap>Code & Name</td>
					<td class="font-bold">Mobile Number & Email</td>
					<td class="font-bold center" nowrap>Joining Date</td>
				</tr>
				<tr>
					<td>
					<input type="hidden" name="order_id" id="order_id" value="{{Crypt::encrypt($order->orderid)}}">
					<input type="hidden" name="category_id" id="category_id" value="{{Crypt::encrypt($order->categoryid)}}">
					<input type="hidden" name="vendor_id" id="vendor_id" value="{{Crypt::encrypt($order->vendorid)}}">
					<select name="sector_id" id="sector_id" class="form-control">
						<option value="">--Sector--</option>
						@foreach($sectors as $sector)
						<option value="{{$sector->sectorid}}">{{$sector->sectorname}}</option>
						@endforeach
					</select>
					<select name="position_id" id="position_id" class="form-control">
						<option value="">--Position--</option>
						@foreach($positions as $position)
						<option value="{{$position->positionid}}">{{$position->consultantposition}}</option>
						@endforeach
					</select>
					</td>
					<td>
						<input type="text" name="resource_code" id="resource_code" class="form-control" placeholder="Code">
						<input type="text" name="resource_name" id="resource_name" class="form-control" placeholder="Name">
					</td>
					<td>
						<input type="text" name="resource_mobile" id="resource_mobile" class="form-control" placeholder="Mobile Number">
						<input type="text" name="resource_email" id="resource_email" class="form-control" placeholder="Email">
					</td>
					<td class="width-100">
						<input type="text" name="resource_deployed_date" id="resource_deployed_date" class="todays_dt_blank form-control" placeholder="dd-mm-YYYY">
						@permission('workorder.addresource')
						<button type="button" class="form-control btn btn-info add_resource_btn">
							<i class="fa fa-user"></i> Add Resource
						</button>
						@endpermission
					</td>
				</tr>
			</table>
			@endif
			</div>
			</div>
			</div>
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
function SetDeploymentDate()
{
	let codes 			=	document.getElementsByName("employee_code[]");
    let names 			=	document.getElementsByName("candidatename[]");
    let numbers 		=	document.getElementsByName("mobilenumber[]");
    let emails 			=	document.getElementsByName("email[]");
	let deployed_dates 	=	document.getElementsByName("deployed_date[]");

    let atLeastOneFilled = false;

    for(let i = 0; i < names.length; i++)
	{
		/*
		if(codes[i].value.trim()!== "" || names[i].value.trim()!== "" || numbers[i].value.trim()!== "" || emails[i].value.trim()!== "")
		{
            atLeastOneFilled = true;
            break;
        }
		*/
    }
	/*
    if (!atLeastOneFilled) {
        bootbox.alert("Please update at least one record.");
        return;
    }
	*/
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

$(document).on('click', '.add_resource_btn', function () {
	
	bootbox.confirm('Are you sure you want to proceed?',function(result){
		if(result)
		{
			let form = $('#addresource');

			$.ajax({
				url: form.attr('action'),
				type: 'POST',
				data: form.serialize(),
				dataType: 'json',

				beforeSend: function () {
					$('.add_resource_btn').prop('disabled', true);
				},

				success: function (response) {

					$('.add_resource_btn').prop('disabled', false);

					// Laravel validation response handled manually
					if(response.code == 200) {
						bootbox.alert(response.message || 'Resource added successfully.');
						location.reload();
					} else {
						bootbox.alert(response.message || 'Something went wrong.');
					}
				},

				error: function (xhr) {

					$('.add_resource_btn').prop('disabled', false);

					// Laravel validation errors
					if (xhr.status === 422) {

						let errors = xhr.responseJSON.errors;
						let messages = [];

						$.each(errors, function (key, value) {
							messages.push(value[0]);
						});

						bootbox.alert(messages.join("\n"));
					}
					else if (xhr.responseJSON && xhr.responseJSON.message) {
						bootbox.alert(xhr.responseJSON.message);
					}
					else {
						bootbox.alert('An unexpected error occurred.');
					}
				}
			});
			
		}
	});

});
</script>


<script src="{{ asset('panel/assets/js/datatablePaginationWithFilter.js') }}"></script>
@endsection