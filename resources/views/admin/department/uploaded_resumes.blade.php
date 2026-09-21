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
<div class="tabbable" style="padding:0px 0px!important;">
		<ul class="nav nav-tabs padding-0">
		@if(in_array(1,Session::get('actions')))
			<li class="active"><a data-toggle="tab" href="#home"><i class="green ace-icon fa fa-download bigger-120" style="vertical-align:text-top;"></i>UPLOADED PPT`S / RESUME`S FOR EoI</a></li>
		@endif
		</ul>

	
		<div class="tab-content no-border" style="border:1px solid #ddd; min-height:100px; padding:0px 10px;">
@if(in_array(1,Session::get('actions')))
<div id="home" class="tab-pane in active" style="padding:0px 10px!important;">
	<div class="row">
		<div class="row">
			<form name="committeefrm" id="committeefrm" action="{{ route('upload.committee',Crypt::encrypt($data->requestid))}}" method="post" enctype="multipart/form-data">
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
<div class="col-sm-12">				
<table class="mytable" border="1" style="text-transform: capitalize!important;">
	<tr class="myheadbg"><td class="td-padding" style="font-size:16px; font-weight:bold;" colspan="12">PROJECT & DEPARTMENT DETAIL</td></tr>
	@if($data->eoinumber!='')
	<tr>
		<td class="mytdleft" style="width:200px; padding:4px 5px!important;" nowrap><b>EoI Number</b></td>
		<td class="mytdleft td-padding" style="padding:4px 5px!important;" nowrap>{{ $data->eoinumber }}</td>
	</tr>
	@endif
	<tr>
		<td class="mytdleft" style="width:200px; padding:4px 5px!important;" nowrap>Project Title</td>
		<td class="mytdleft td-padding" style="padding:4px 5px!important;" nowrap>{{ ucwords(strtolower($data->projecttitle)) }}</td>
	</tr>
	<tr>
		<td class="mytdleft" style="width:200px; padding:4px 5px!important;" nowrap>Project Duration</td>
		<td class="mytdleft td-padding" style="padding:4px 5px!important;" nowrap>{{ ucwords(strtolower($data->projectduration)) }}</td>
	</tr>
	<tr>
		<td class="mytdleft" style="width:200px; padding:4px 5px!important;" nowrap>Project Objective</td>
		<td class="mytdleft td-padding" style="padding:4px 5px!important;" nowrap>{{ ucwords(strtolower($data->projectobjective)) }}</td>
	</tr>
	<tr>
		<td class="mytdleft" style="width:200px; padding:4px 5px!important;" nowrap>Empanelment Type</td>
		<td class="mytdleft td-padding" style="padding:4px 5px!important;" nowrap>{{ ucwords(strtolower($data->jobcategory)) }}</td>
	</tr>
	<tr>
		<td class="mytdleft" style="width:200px; padding:4px 5px!important;" nowrap>Department Name</td>
		<td class="mytdleft td-padding" style="padding:4px 5px!important;" nowrap>{{ ucwords(strtolower($data->departmentname)) }}</td>
	</tr>
	<tr>
		<td class="mytdleft td-padding" style="width:200px; padding:4px 5px!important;" nowrap>Officer Name</td>
		<td class="mytdleft td-padding" style="padding:4px 5px!important;" nowrap>{{ ucwords(strtolower($data->name)) }}</td>
	</tr>
	<tr>
		<td class="mytdleft td-padding" style="width:200px; padding:4px 5px!important;" nowrap>Designation</td>
		<td class="mytdleft td-padding" style="padding:4px 5px!important;" nowrap>{{ ucwords(strtolower($data->designation)) }}</td>
	</tr>
	<tr>
		<td class="mytdleft td-padding" style="width:200px;" nowrap>Contact Number</td>
		<td class="mytdleft td-padding" style="padding:4px 5px!important;" nowrap>{{ ucwords(strtolower($data->mobilenumber)) }}</td>
	</tr>
	<tr>
		<td class="mytdleft td-padding" style="width:200px; padding:4px 5px!important;" nowrap>Email Id</td>
		<td class="mytdleft td-padding" style="padding:4px 5px!important; text-transform:lowercase!important;" nowrap>{{ strtolower($data->email) }}</td>
	</tr>
	<tr>
		<td class="mytdleft td-padding" style="width:200px; padding:4px 5px!important;" nowrap>Date of Submission</td>
		<td class="mytdleft td-padding" style="padding:4px 5px!important;" nowrap>{{ date('d\-m\-Y, h:i A',strtotime($data->creationdate)) }}</td>
	</tr>
</table>
</div>
				
<div class="col-sm-12">				
<table class="mytable" border="1" style="text-transform: capitalize!important;">
	<tr class="myheadbg"><td class="td-padding" style="font-size:16px; font-weight:bold;" colspan="8">PARTICIPATED VENDORS LIST</td></tr>
	<tr class="myheadbg">
		<td class="mytdwhite padding-2 center" style="width:20px;" nowrap>S.No.</td>
		<td class="mytdleftwhite padding-2" nowrap>Vendor / Company Name</td>
		<td class="mytdleftwhite padding-2" nowrap>Contact Person</td>
		<td class="mytdleftwhite padding-2" nowrap>Address</td>
		<td class="center padding-2" nowrap>Participation Date</td>
		<td class="center padding-2" nowrap>Interview Date</td>
		<td class="center padding-2" nowrap></td>
	</tr>
	@php $r=0; @endphp
	@foreach($vendors as $vendor)
	@php $r=$r+1; @endphp
	<tr>
		<td class="center padding-2 v-top">{{$r}}</td>
		<td class="mytdleft padding-2 v-top">{{$vendor->companyname}}</td>
		<td class="mytdleft padding-2 v-top">
			{{$vendor->contactperson}}<br>Designation : {{$vendor->designation}}<br>Email : <span style="text-transform:lowercase!important;">{{$vendor->officialemail}}</span>
		</td>
		<td class="mytdleft padding-2 v-top">{{$vendor->officelocation}}</td>
		<td class="center padding-2 v-top">{{date('d\-m\-Y, h:i A',strtotime($vendor->participationdate))}}</td>
		<td class="center padding-0 v-top">
			{{ date('d\-m\-Y',strtotime($vendor->interviewdate)) }}
		</td>
		<td class="center v-top" style="width:100px;">
		@if($vendor->ispassed==1)
			@if($vendor->isenabled==1)
			<a href="{{ route('dept.viewparticipations',Crypt::encrypt($vendor->participationid))}}">
				<button type="button" class="btn btn-info mygridbtn" style="width:150px;"><i class="fa fa-eye"></i> View Resume / PPT</button>
			</a>
			@endif
		@endif
		</td>
		
	</tr>
	@endforeach
	<tr>
		<td colspan="7" style="text-align:right;">
			<button type="button" class="btn btn-info mygridbtn" onclick="history.back()" style="width:130px; float:right;">
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
@endif


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

	$('.attachment').ace_file_input({
		no_file:'No File ...',
		btn_choose:'Resume',
		btn_change:'Change',
		btn_name:'fourthimage',
		thumbnail:false //| true | large
	});

		
});


function SetInterviewDate(rl,participationid)
{
	bootbox.confirm('Do you want to set interview date?',function(result){
		if(result)
		{
			var interviewdate	=	$("#interviewdate"+participationid).val();
			$.ajax({
				url: rl,
				type: 'POST',
				data: {'participationid':participationid,'interviewdate':interviewdate},
				headers: {
					'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') // CSRF token
				},
				success: function (response) {
					if(response.status==200)
					{
						bootbox.alert({
							message: "Interview date updated successfully",
							callback: function () {
								setTimeout(function() { $(".no-skin").css("padding-right",""); },500);
							}
						});						
					}
				},
				error: function (xhr) {
					if (xhr.responseJSON && xhr.responseJSON.errors) {
						var errors = xhr.responseJSON.errors;
						var allMessages = '';

						$.each(errors, function(field, messages) {
							$.each(messages, function(index, msg) {
								allMessages += msg + '<br>';
							});
						});
						bootbox.alert(allMessages);
					}			
				}
			});
		}
	});	
}

</script>


<script src="{{ asset('panel/assets/js/datatablePaginationWithFilter.js') }}"></script>
@endsection