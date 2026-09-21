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
					<i class="green ace-icon fa fa-list-alt bigger-120 datalist"></i> Resource Replcement
				</a>
			</li>
		</ul>

	
		<div class="tab-content no-border" style="border:1px solid #ddd; min-height:100px; padding:0px 10px;">
<div id="home" class="tab-pane in active" style="padding:0px 10px!important;">
	<div class="row">
		
			<form name="replacementfrm" id="replacementfrm" action="{{route('store.replaceresourcevendor',Crypt::encrypt($deployment->deploymentid))}}" method="post" enctype="multipart/form-data">
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
								<b class="form-label">Order Date : </b>{{ date('d\-m\-Y',strtotime($order->orderdate)) }} | Order Date : <b>{{ date('d\-m\-Y',strtotime($order->orderdate)) }}</b> | Work Order Due Date : {{ date('d\-m\-Y',strtotime($order->workorderduedate)) }}<br>
								
								</div>
								
							</div>
						</div>
						<div class="ui-card-detail">
							<div>
								<div class="title-detail">
									<i class="material-icons-outlined">topic</i> {{ $order->project_name }}
								</div>
								<div class="details-detail lh-25">
									<b>@if($order->ispm==0) Department @else Project Manager @endif : </b>{{ $order->departmentname }}<br>
									@if($order->eoinumber)EoI Number: <b>{{ $order->eoinumber }} @endif</b>
									
								</div>
							</div>
						</div>
						<div class="ui-card-detail">
							<div>
								<div class="title-detail">
									<i class="material-icons-outlined">topic</i> {{ $order->companyname }}
								</div>
								<div class="details-detail">
									<label class="form-label">Official Email :</label> {{$order->officialemail}}
								</div>
							</div>
						</div>
						<div class="section-block-detail">
							<div class="section-header-detail" style="position:relative;">
								<span class="material-icons-outlined">group_add</span>
								&nbsp;<h2 class="section-title-detail">Resource Details</h2>
								<div style="position:absolute; right:0px;">
									<input type="hidden" name="deploymentid" value="{{$deployment->deploymentid}}">
										Set Release Date for <b>{{$deployment->name}}</b> : <input type="text" name="release_date" id="release_date" class="todays_dt_blank width-100" placeholder="dd-mm-YYYY" style="height:40px!important; padding:6px 10px; border-radius:4px;">
								</div>
							</div>
							<div class="table-responsive">
							@if($order->categoryid==2)
							<table class="mytable" border="1" style="text-transform: none!important;">
								<tr class="myheadbg">
									<td class="padding-10 font-bold">Sector</td>
									<td class="padding-10 font-bold">Position</td>
									<td class="padding-10 font-bold" nowrap>Code</td>
									<td class="padding-10 font-bold" nowrap>Name</td>
									<td class="padding-10 font-bold">Mobile Number</td>
									<td class="padding-10 font-bold">Email</td>
									<td class="padding-10 font-bold center" nowrap>Joining Date</td>
								</tr>
								<tr>
									<td class="padding-10">
										<p>{{ucwords(strtolower($deployment->sectorname))}}</p>
									
									</td>
									<td class="padding-10">
										<p>{{$deployment->consultantposition}}</p>
									</td>
									<td class="padding-10 v-top">
										{{$deployment->employee_code}}
									</td>
									<td class="padding-10 v-top">
										{{$deployment->name}}
									</td>
									<td class="padding-10 v-top">
										{{$deployment->mobilenumber}}
									</td>
									<td class="padding-10 v-top">
										{{$deployment->email}}
									</td>
									<td class="padding-10 v-top" style="text-align:center; width:100px;">
									@if($deployment->deployed_date)
										{{date('d\-m\-Y',strtotime($deployment->deployed_date))}}
									@endif
									</td>
								</tr>
								<tr class="myheadbg">
									<td class="padding-10 font-bold" colspan="7">Replaced By Resource <i style="float:right;">Resource details can be updated later.</i></td>
								</tr>
								<tr>
									<td class="padding-0">
										<select name="sectorid" id="sectorid" style="border-radius:0px!important; height:100%!important; width:100%;">
											@foreach($sectors as $sector)
											<option value="{{$sector->sectorid}}" @if($sector->sectorid==$deployment->sectorid) selected @endif>{{ucwords(strtolower($sector->sectorname))}}</option>
											@endforeach
										</select>
									</td>
									<td class="padding-0">
										<select name="positionid" id="positionid" style="border-radius:0px!important; height:100%!important; width:100%;">
											@foreach($positions as $position)
											<option value="{{$position->positionid}}" @if($position->positionid==$deployment->positionid) selected @endif>{{$position->consultantposition}}</option>
											@endforeach
										</select>
									</td>
									<td class="padding-0">
										<input type="text" name="employee_code" id="employee_code" placeholder="Employee code" style="border-radius:0px!important; width:100%;" autocomplete="off">
									</td>
									<td class="padding-0">
										<input type="text" name="name" id="name" placeholder="Name" style="border-radius:0px!important; width:100%;" autocomplete="off">
									</td>
									<td class="padding-0">
										<input type="text" name="mobilenumber" id="mobilenumber" placeholder="Mobile number" style="border-radius:0px!important; width:100%;" autocomplete="off">
									</td>
									<td class="padding-0">
										<input type="text" name="email" id="email" placeholder="Email" style="border-radius:0px!important; width:100%;" autocomplete="off">
									</td>
									<td class="padding-0">
										<input type="text" name="joining_date" id="joining_date" class="todays_dt_blank" placeholder="Joining Date" style="border-radius:0px!important; width:100%;" autocomplete="off">
									</td>
								</tr>
								<tr>
									<td colspan="3" class="padding-10" style="border-right:none!important;">
										<input type="file" required name="supporting_document" id="supporting_document" accept="application/pdf" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}" />
									</td>
									<td colspan="4" class="padding-10" style="text-align:right; border-left:none!important;">
										<button type="button" class="btn btn-info gridbtn width-100" onclick="finalSubmit()">Submit</button>
									</td>
								</tr>
							</table>
							@endif
							@if($order->categoryid==1)
							<table class="mytable" border="1" style="text-transform: none!important;">
								<tr class="myheadbg">
									<td class="padding-10 font-bold">Position</td>
									<td class="padding-10 font-bold">Level</td>
									<td class="padding-10 font-bold">Employee Code</td>
									<td class="padding-10 font-bold">Name</td>
									<td class="padding-10 font-bold">Mobile Number</td>
									<td class="padding-10 font-bold">Email</td>
									<td class="padding-10 font-bold center">Joining Date</td>
								</tr>
								<tr>
									<td class="padding-10">
									{{ucwords(strtolower($deployment->role))}}
									</td>
									<td class="padding-10">
									Level-{{$deployment->experiencelevel}}							
									</td>
									<td class="padding-10">
									{{$deployment->employee_code}}
									</td>
									<td class="padding-10">
									{{$deployment->name}}
									</td>
									<td class="padding-10">
									{{$deployment->mobilenumber}}
									</td>
									<td class="padding-10">
									{{$deployment->email}}
									</td>
									<td class="padding-10 v-top center">
									@if($deployment->deployed_date)
									{{date('d\-m\-Y',strtotime($deployment->deployed_date))}}
									@endif
									</td>
								</tr>
								<tr class="myheadbg">
									<td class="padding-10 font-bold" colspan="7">Replaced By Resource <i style="float:right;">Resource details can be updated later.</i></td>
								</tr>
								<tr>
									<td class="padding-0">
										<input type="text" name="role" id="role" placeholder="Role" style="border-radius:0px!important; width:100%;" autocomplete="off">
									</td>
									<td class="padding-0">
										<select name="experiencelevel" id="experiencelevel" style="border-radius:0px!important; height:100%!important; width:100%;">
											@foreach($levels as $lvl)
											<option value="{{$lvl->experiencelevel}}" @if($lvl->experiencelevel==$deployment->experiencelevel) selected @endif>Level-{{$lvl->experiencelevel}}</option>
											@endforeach
										</select>
									</td>
									<td class="padding-0">
										<input type="text" name="employee_code" id="employee_code" placeholder="Employee code" style="border-radius:0px!important; width:100%;" autocomplete="off">
									</td>
									<td class="padding-0">
										<input type="text" name="name" id="name" placeholder="Name" style="border-radius:0px!important; width:100%;" autocomplete="off">
									</td>
									<td class="padding-0">
										<input type="text" name="mobilenumber" id="mobilenumber" placeholder="Mobile number" style="border-radius:0px!important; width:100%;" autocomplete="off">
									</td>
									<td class="padding-0">
										<input type="text" name="email" id="email" placeholder="Email" style="border-radius:0px!important; width:100%;" autocomplete="off">
									</td>
									<td class="padding-0">
										<input type="text" name="joining_date" id="joining_date" class="todays_dt_blank" placeholder="dd-mm-YYYY" style="border-radius:0px!important; width:100%;" autocomplete="off">
									</td>
								</tr>
								<tr>
									<td colspan="3" class="padding-10" style="border-right:none!important;">
										<input type="file" required name="supporting_document" id="supporting_document" accept="application/pdf" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}" />
									</td>
									<td colspan="4" class="padding-10" style="text-align:right; border-left:none!important;">
										<button type="button" class="btn btn-info gridbtn width-100" onclick="finalSubmit()">Submit</button>
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
function finalSubmit()
{
	var release_date	=	document.getElementById("release_date").value;
	if(!release_date)
	{
		bootbox.alert("Please provide the release date.");
		return false;
	}
	bootbox.confirm('Do you want to submit the replacement details?',function(result){
		if(result)
		{
			$("#replacementfrm").submit();
		}
	});
}

jQuery(function($) {	

	$('#supporting_document').ace_file_input({
		no_file:'No File ...',
		btn_choose:'Supporting Documents for Release (if any)',
		btn_change:'Change',
		btn_name:'fourthimage',
		thumbnail:false //| true | large
	});
});
</script>


<script src="{{ asset('panel/assets/js/datatablePaginationWithFilter.js') }}"></script>
@endsection