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
			<li class="active"><a data-toggle="tab" href="#home"><i class="green ace-icon fa fa-building bigger-120" style="vertical-align:text-top;"></i>VENDOR DETAIL</a></li>
		@endif
		</ul>

	
		<div class="tab-content no-border" style="border:1px solid #ddd; min-height:100px; padding:0px 10px;">
@if(in_array(1,Session::get('actions')))
<div id="home" class="tab-pane in active" style="padding:0px 10px!important;">
	<div class="row">
		<div class="row">
			<form name="committeefrm" id="committeefrm" action="{{ route('upload.committee',Crypt::encrypt($participation->participationid))}}" method="post" enctype="multipart/form-data">
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
	<tr class="myheadbg"><td class="td-padding" style="font-size:16px; font-weight:bold;" colspan="12">VENDOR DETAIL</td></tr>
	<tr>
		<td class="mytdleft" style="width:200px; padding:4px 5px!important;" nowrap>Company Name</td>
		<td class="mytdleft td-padding" style="padding:4px 5px!important;" nowrap>{{ ucwords(strtolower($vendor->companyname)) }}</td>
	</tr>
	<tr>
		<td class="mytdleft" style="width:200px; padding:4px 5px!important;" nowrap>Gst Number</td>
		<td class="mytdleft td-padding" style="padding:4px 5px!important;" nowrap>{{ $vendor->gstnumber }}</td>
	</tr>
	<tr>
		<td class="mytdleft" style="width:200px; padding:4px 5px!important;" nowrap>PAN</td>
		<td class="mytdleft td-padding" style="padding:4px 5px!important;" nowrap>{{ $vendor->pannumber }}</td>
	</tr>
	<tr>
		<td class="mytdleft" style="width:200px; padding:4px 5px!important;" nowrap>Certificate Number</td>
		<td class="mytdleft td-padding" style="padding:4px 5px!important;" nowrap>{{ $vendor->certificatenumber }}</td>
	</tr>
	<tr>
		<td class="mytdleft" style="width:200px; padding:4px 5px!important;" nowrap>Contact Person</td>
		<td class="mytdleft td-padding" style="padding:4px 5px!important;" nowrap>{{ ucwords(strtolower($vendor->contactperson)) }}</td>
	</tr>
	<tr>
		<td class="mytdleft" style="width:200px; padding:4px 5px!important;" nowrap>Designation</td>
		<td class="mytdleft td-padding" style="padding:4px 5px!important;" nowrap>{{ $vendor->designation }}</td>
	</tr>
	<tr>
		<td class="mytdleft td-padding" style="width:200px; padding:4px 5px!important;" nowrap>Official Email</td>
		<td class="mytdleft td-padding" style="padding:4px 5px!important;" nowrap>{{ $vendor->officialemail }}</td>
	</tr>
</table>
</div>
				
<div class="col-sm-12">
@if($categoryid==1)			
<table class="mytable" border="1" style="text-transform: capitalize!important;">
	<tr class="myheadbg"><td class="td-padding" style="font-size:16px; font-weight:bold;" colspan="12">RESUMES / PPT</td></tr>
	@foreach($requestdetail as $index=>$req)
	<tr class="myheadbg">
		<td class="mytdwhite padding-2" style="width:20px;" nowrap>{{$index+1}}) {{strtoupper($req->tiername)}} | Role : {{$req->role}} | Qualification : {{$req->qualification}} | Experience : {{$req->workexperience}} [L-{{$req->experiencelevel}}] | Duration : {{$req->duration}} Months | Required : {{$req->requirednumber}}</td>
	</tr>
	@if($req->resumes)
	<tr>
		<td class="padding-0">
			<table class="mytable" border="1" style="text-transform: capitalize!important;">
				<tr class="myheadbg">
					<td class="center" style="width:20px;">Status</td>
					<td class="padding-2">Name</td>
					<td class="padding-2">Remark</td>
					<td class="padding-2 center" style="width:130px;">Uploaded Resume</td>
				</tr>
				@foreach($req->resumes as $resume)
				<tr>
					<td class="center" style="width:20px;">
						<select name="reviewstatus{{$resume->resumeid}}" id="reviewstatus{{$resume->resumeid}}">
							<option value="0">--PENDING--</option>
							<option value="1" @if($resume->reviewstatus==1) selected @endif>UNDER REVIEW</option>
							<option value="2" @if($resume->reviewstatus==2) selected @endif>HOLD REVIEW</option>
							<option value="3" @if($resume->reviewstatus==3) selected @endif>REJECTED</option>
							<option value="4" @if($resume->reviewstatus==4) selected @endif>SELECTED</option>
						</select>
					</td>
					<td class="padding-2" nowrap>
						@if($resume->reviewstatus==0)
						<span class="gray1">{{$resume->name}}</span>
						@elseif($resume->reviewstatus==1)
						<span class="blue1">{{$resume->name}}</span>
						@elseif($resume->reviewstatus==2)
						<span class="orange1">{{$resume->name}}</span>
						@elseif($resume->reviewstatus==3)
						<span class="red1">{{$resume->name}}</span>
						@elseif($resume->reviewstatus==4)
						<span class="green1">{{$resume->name}}</span>
						@endif
					</td>
					<td class="padding-0">
						<input type="text" name="remark{{$resume->resumeid}}" id="remark{{$resume->resumeid}}" value="{{$resume->remark}}" style="margin-top:-2px!important; width:100%;" placeholder="Update remark" class="selectbx">
					</td>
					<td class="padding-0 center">
						@if($resume->resume)
						<a href="{{ asset('storage/'.$resume->resume) }}" target="_blank" class="btn btn-info mygridbtn">
							<i class="fa fa-file-pdf-o"></i> VIEW
						</a>
						@endif
					</td>
				</tr>
				@endforeach
			</table>
		</td>
	</tr>
	@endif
	@endforeach
</table>
@else
<table class="mytable" border="1" style="text-transform: capitalize!important;">
	<tr class="myheadbg"><td class="td-padding" style="font-size:16px; font-weight:bold;" colspan="12">RESUMES / PPT</td></tr>
	@foreach($requestdetail as $index=>$req)
	<tr class="myheadbg">
		<td class="mytdwhite padding-2" style="width:20px;" nowrap>{{$index+1}}) {{strtoupper($req->tiername)}} | Sector : {{$req->sectorname}} | Position : {{$req->consultantposition}} | Experience : {{$req->experience}} | Duration : {{$req->duration}} Months | Required : {{$req->requirednumber}}</td>
	</tr>
	@if($req->resumes->count()>0)
	<tr>
		<td class="padding-0">
			<table class="mytable" border="1" style="text-transform: capitalize!important;">
				<tr class="myheadbg">
					<td class="center" style="width:20px;">&nbsp;</td>
					<td class="padding-2">Name</td>
					<td class="padding-2">Remark</td>
					<td class="padding-2 center" style="width:130px;">Uploaded Resume</td>
				</tr>
				@foreach($req->resumes as $resume)
				<tr>
					<td class="center" style="width:20px;">
						<select name="reviewstatus{{$resume->resumeid}}" id="reviewstatus{{$resume->resumeid}}">
							<option value="0">--PENDING--</option>
							<option value="1" @if($resume->reviewstatus==1) selected @endif>UNDER REVIEW</option>
							<option value="2" @if($resume->reviewstatus==2) selected @endif>HOLD REVIEW</option>
							<option value="3" @if($resume->reviewstatus==3) selected @endif>REJECTED</option>
							<option value="4" @if($resume->reviewstatus==4) selected @endif>SELECTED</option>
						</select>						
					</td>
					<td class="padding-2" nowrap>
						@if($resume->reviewstatus==0)
						<span class="gray1">{{$resume->name}}</span>
						@elseif($resume->reviewstatus==1)
						<span class="blue1">{{$resume->name}}</span>
						@elseif($resume->reviewstatus==2)
						<span class="orange1">{{$resume->name}}</span>
						@elseif($resume->reviewstatus==3)
						<span class="red1">{{$resume->name}}</span>
						@elseif($resume->reviewstatus==4)
						<span class="green1">{{$resume->name}}</span>
						@endif
					</td>
					<td class="padding-0">
						<input type="text" name="remark{{$resume->resumeid}}" id="remark{{$resume->resumeid}}" value="{{$resume->remark}}" style="margin-top:-2px!important; width:100%;" placeholder="Update remark" class="selectbx">
					</td>
					<td class="padding-0 center">
						@if($resume->resume)
						<a href="{{ asset('storage/' . $resume->resume) }}" target="_blank">
							<button type="button" class="btn btn-info mygridbtn" style="width:170px;"><i class="fa fa-eye"></i> {{date('d\-m\-Y, h:i A',strtotime($resume->uploaddate))}}</button>
						</a>
						@endif
					</td>
				</tr>
				@endforeach
			</table>
		</td>
	</tr>
	@endif
	@endforeach
	<tr><td>&nbsp;</td></tr>
</table>
	
@endif

</div>
<div class="col-sm-12">
<table class="mytable" border="1" style="text-transform: capitalize!important;">
	<tr>
		<td>
		@if($floated->presentationfile!='')
			<a href="{{ asset('storage/'.$floated->presentationfile) }}" target="_blank">
				<button type="button" class="btn btn-info mygridbtn" style="width:200px;"><i class="fa fa-download"></i> VIEW / DOWNLOAD PPT</button>
			</a>		
		@endif		
		</td>
	</tr>
	<tr class=""><td>&nbsp;</td></tr>
	<tr class="myheadbg"><td class="td-padding" style="font-size:16px; font-weight:bold;">REMARK</td></tr>
	<tr>
		<td class="padding-0">
		<textarea name="remark" id="remark" style="width:100%;" placeholder="Write remark if any..">{{$participation->remark}}</textarea>
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


	$('#demandnotefile').ace_file_input({
		no_file:'No File ...',
		btn_choose:'Upload Demand Note',
		btn_change:'Change',
		btn_name:'fourthimage',
		thumbnail:false //| true | large
	});
		
});

document.addEventListener('change', function (e) {
    // Match only single file input fields with class .file-input
    if (e.target && e.target.classList.contains('file-input')) {
        const input = e.target;
        const label = input.parentElement;

        if (input.files.length > 0) {
            const fileName = input.files[0].name;
            label.innerHTML = `<i class="fa fa-check"></i> 1 File Selected`;
            label.appendChild(input); // keep the input inside the label
        } else {
            label.innerHTML = '<i class="fa fa-upload"></i> Resume';
            label.appendChild(input);
        }
    }
});

</script>


<script src="{{ asset('panel/assets/js/datatablePaginationWithFilter.js') }}"></script>
@endsection