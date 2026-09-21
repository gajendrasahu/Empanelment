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
					<i class="green ace-icon fa fa-list-alt bigger-120 datalist"></i> Work Order Detail
				</a>
			</li>
		@endif
		</ul>

	
		<div class="tab-content no-border" style="border:1px solid #ddd; min-height:100px; padding:0px 10px;">
@if(in_array(1,Session::get('actions')))
<div id="home" class="tab-pane in active" style="padding:0px 10px!important;">
	<div class="row">
		<div class="row">
			<form name="frm" id="frm" action="{{route('update.workorderdate',Crypt::encrypt($order->orderid))}}" method="post" enctype="multipart/form-data">
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
				
				@csrf
				<div class="form-group">
<div class="col-sm-12">				
<table class="mytable" style="text-transform: none!important; border:0px!important;">
	<tr>
		<td class="center" colspan="3">
			<img src="{{ asset('panel/assets/images/basic/logo.png') }}" alt="Login Image" style="height: 50px; vertical-align: middle;">
		</td>
	</tr>
	<tr><td class="center" colspan="3">OFFICE OF CHiPS</td></tr>
	<tr><td class="center" colspan="3">State Data Center, Civil Lines</td></tr>
	<tr><td class="center" colspan="3">Raipur - 492001</td></tr>
	<tr>
		<td style="text-align:left; width:33%;" class="padding-2">Phone : 771-4014158</td>
		<td style="width:33%;" class="center padding-2">Fax:0771 :4066205</td>
		<td class="padding-2" style="text-align:right; width:33%;">Email: ccochips@nic.in</td>
	</tr>
	<tr>
		<td class="left padding-2 font-bold" colspan="2">Order No.* : 
		<input type="text" name="orderno" id="orderno" value="{{old('orderno')}}" required class="selectbx font-12 font-normal" style="width:100px; vertical-align:middle; font-weight:normal;" placeholder="Number" autocomplete="off">{{$order->ordernumber}}
		
		<span class="text-danger">@error('orderno') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }} </i> @enderror</span>
		</td>
		<td class="right padding-2 font-bold">
			Raipur Dated*: 
			<input type="text" name="setorderdate" id="setorderdate" required class="selectbx" placeholder="dd-mm-YYYY" style="width:150px; vertical-align:middle; font-weight:normal;">
			<span class="text-danger">@error('setorderdate') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }} </i> @enderror</span>
		</td>
	</tr>
	<tr>
		<td colspan="3" class="padding-2">
		To, 
		<div class="tocompany">
		{{ucwords(strtolower($vendor->companyname))}}<br>
		{{$vendor->officelocation}}
		</div>
		</td>
	</tr>
	<tr>
		<td colspan="3" class="padding-2">Subject : {{$order->subject}}</td>
	</tr>
	<tr>
		<td colspan="3" class="padding-2">
		Refrences, 
		<div class="tocompany">
		1) {{$order->refrence}}, Dated : {{date('d\-m\-Y',strtotime($eoi->creationdate))}}<br>
		2) Agreement Dated : {{date('d\-m\-Y',strtotime($order->agreementdate))}}<br>
		3) LOI Number : {{$order->loinumber}}<br>
		4) Tender No : {{$order->tendernumber}}
		</div>
		</td>
	</tr>
	<tr>
		<td class="padding-2" colspan="3">
			With reference to above cited subject, manpower will be required to be deployed for <b>{{$eoi->projecttitle}}</b>.
		</td>
	</tr>
	<tr>
		<td class="padding-2" colspan="3">
			You are hereby instructed to provide resources for the following positions under <b>{{$eoi->projecttitle}}</b> from the date of deployment to as per duration mentioned in Eol..
		</td>
	</tr>
	<tr>
		<td colspan="3" class="padding-2">Details of the resources and financial calculation is tabulated below: -</td>
	</tr>
	<tr>
		<td colspan="3" class="padding-2">
			<table class="table" border="1">
				<tr><td colspan="8" class="padding-5">Project Name : {{$eoi->projecttitle}}</td></tr>
				<tr>
					<td class="padding-5 center" style="width:30px;">S.No.</td>
					<td class="padding-5">Domain / Sector</td>
					@if($eoi->categoryid==2)
					<td class="padding-5">Position</td>
					@endif
					<td class="padding-5 center">Duration<br>(B)</td>
					<td class="padding-5 center">Man,Month Rate<br>(C=C*B)</td>
					@if($eoi->categoryid==1)
					<td class="padding-5 center">With {{$order->operatingmargin}}% Agency Margin<br>(D=C*{{$order->operatingmargin}}%+C)</td>
					@endif
					<td class="padding-5 center">Including Per Month Per Resource Rate<br>(E=D*{{$order->tax}}%+D)</td>
					<td class="padding-5 center">Admin Charge<br>(F=E*{{$order->admincharge}}%+E)</td>
				</tr>
				@php
				$c=0;
				$d=0;
				$e=0;
				$f=0;
				@endphp
				@foreach($detail as $det)
				<tr>
					<td class="center padding-5" style="vertical-align:top;" nowrap>{{$loop->iteration}}</td>
					<td class="padding-5" nowrap>
						@if($order->categoryid==1)
						{{$det->role}} [L{{$det->experiencelevel}}]
						@elseif($order->categoryid==2)
							{{ucwords(strtolower($det->sectorname))}} <i>[{{$det->tiername}}]</i>
						@endif
					</td>
					@if($eoi->categoryid==2)
					<td class="padding-5" nowrap>{{$det->consultantposition}}</td>
					@endif
					<td class="center padding-5" nowrap>{{$det->duration}} Months</td>
					<td class="center padding-5" nowrap>{{$det->remuneration}}*{{$det->duration}}={{$det->remuneration*$det->duration}}</td>
					@if($eoi->categoryid==1)
					<td class="center padding-5" nowrap>{{number_format(($det->remuneration*$det->duration+$det->operatingvalue),'2','.','')}}</td>
					@endif
					<td class="center padding-5" nowrap>{{number_format(($det->remuneration*$det->duration+$det->operatingvalue+$det->taxvalue),'2','.','')}}</td>
					<td class="center padding-5" nowrap>{{number_format(($det->remuneration*$det->duration+$det->operatingvalue+$det->taxvalue+$det->admincharge),'2','.','')}}</td>
				</tr>
				@php
				$c=$c+$det->remuneration*$det->duration;
				$d=$d+$det->remuneration*$det->duration+$det->operatingvalue;
				$e=$d+$det->remuneration*$det->duration+$det->operatingvalue+$det->taxvalue;
				$f=$f+$det->remuneration*$det->duration+$det->operatingvalue+$det->taxvalue+$det->admincharge;
				@endphp
				@endforeach
				<tr>
					<td colspan="4" style="text-align:right;"><b>Total</b></td>
					<td class="center"><b><i class="fa fa-inr"></i> {{$order->totalremuneration}}</b></td>
					<td class="center"><b><i class="fa fa-inr"></i> {{$order->totalbudget}}</b></td>
					@if($eoi->categoryid==1)
					<td class="center"><b><i class="fa fa-inr"></i> {{$order->totalremuneration}}</b></td>
					@endif
					<td class="center"><b><i class="fa fa-inr"></i> {{$order->workorderamount}}</b></td>
				</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td colspan="3" class="padding-2">
		Terms & conditions, 
		<div class="tocompany">
		{!!$order->termsandcondition!!}
		</div>
		</td>
	</tr>
	<tr>
		<td class="padding-2" colspan="2">
		@if($order->signedby!='Chief Executive Officer (CEO)')
			(As approved by CEO, CHiPS)
		@endif
		</td>
		<td class="padding-2" style="text-align:right;">{{$order->signedby}}<br>CHiPS</td>
	</tr>
	<tr>
		<td>
		<input type="file" name="signedcopy" id="signedcopy" accept="application/pdf" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}" data-width="200" style="width:200px!important;" required />
		</td>
		<td style="width:100px!important;">
			<button type="button" class="btn btn-info" style="width:100px; margin-left:20px;" onclick="checkSubmit()">Update</button>
		</td>
		<td style="width:100px!important;"></td>
	</tr>
	<tr>
		<td colspan="3" class="center">&nbsp;</td>
	</tr>
</table>
</div>
<div class="col-sm-12">&nbsp;</div>
<div class="col-sm-12">&nbsp;</div>
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
<script src="{{ asset('panel/assets/js/ace-elements.min.js') }}"></script>
<script src="{{ asset('panel/assets/js/ace.min.js') }}"></script>

<script>
jQuery(function($) {	

	$('#signedcopy').ace_file_input({
		no_file:'No File ...',
		btn_choose:'Signed Copy of Order (mandatory)',
		btn_change:'Change',
		btn_name:'fourthimage',
		thumbnail:false //| true | large
	});
});

function checkSubmit()
{
	const	orderno		=	document.getElementById("orderno").value;
	const	setorderdate=	document.getElementById("setorderdate").value;
	const	fileInput	=	document.getElementById("signedcopy");
	
	if(orderno=='')
	{
		bootbox.alert("Please enter order number.");
		return false;		
	}

	if(setorderdate=='')
	{
		bootbox.alert("Please enter order date.");
		return false;		
	}
	
	if(!fileInput.value)
	{
		bootbox.alert("Please attach signed copy of work order");
		return false;
	}
	$("#frm").submit();
}
</script>


<script src="{{ asset('panel/assets/js/datatablePaginationWithFilter.js') }}"></script>
@endsection