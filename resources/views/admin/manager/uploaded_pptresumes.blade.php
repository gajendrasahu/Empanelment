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
					<i class="green ace-icon fa fa-download bigger-120 datalist" style="vertical-align:text-top;"></i>Uploaded PPT's / Resume's
					
				</a>
			</li>
		@endif
		</ul>

	
		<div class="tab-content no-border" style="border:1px solid #ddd; min-height:100px; padding:0px 10px;">
@if(in_array(1,Session::get('actions')))
<div id="home" class="tab-pane in active" style="padding:0px 10px!important;">
	<div class="row">
		<div class="row">
			<form name="frm" id="frm" action="#" method="post" enctype="multipart/form-data">
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
									<i class="material-icons-outlined">topic</i> {{ $data->projecttitle }}
								</div>
								<div class="details-detail lh-25">
									<b>Department / Project Manager :</b> {{ $data->departmentname }}<br>
									<b>{{ $data->eoinumber }}</b> | Requested on : <b>{{ date('d\-m\-Y, h:i A',strtotime($data->creationdate)) }}</b> | Project Duration : <b>{{ ucwords(strtolower($data->projectduration)) }} Months</b><br>
									
								</div>
							</div>
						</div>
						
						@include('admin.viewpages.factsheet')

						<div class="section-block-detail">
							<div class="table-responsive">
								<table class="mytable pd-8" border="1" style="text-transform:none!important">
									<tr class="myheadbg">
										<td class="center padding-10 form-label" style="width:35px;">S.No.</td>
										<td class="form-label">Requested by</td>
										<td class="form-label">Remark</td>										
										<td class="form-label">Requested on</td>
										<td class="form-label">Updated on</td>
										<td class="form-label">Interview Date & Time</td>
									</tr>
									@if($interviews->count()!=0)
									@php $isupdated=1; @endphp
									@foreach($interviews as $interview)
									<tr>
										<td class="center">{{$loop->iteration}}</td>										
										<td>{{$interview->requestedby}}</td>
										<td>{{$interview->remark}}</td>
										<td nowrap>@if($interview->requested_on){{date('d\-m\-Y, h:i A',strtotime($interview->requested_on))}}@endif</td>
										<td nowrap>@if($interview->updated_on){{date('d\-m\-Y, h:i A',strtotime($interview->updated_on))}}@endif</td>
										<td nowrap>@if($interview->updatedValue){{date('d\-m\-Y, h:i A',strtotime($interview->updatedValue))}}@endif</td>
									</tr>
									@php $isupdated=$interview->isUpdated; @endphp
									@endforeach
									@if($isupdated==0)
									<tr class="myheadbg">
										<td colspan="6" class="form-label">
											<div style="display:flex; gap:10px; align-items:center;">
											<span><b>Update Interview Date & Time</b></span>
											<input type="text" placeholder="dd-mm-YYYY hh:mm AM/PM" name="interview_date" id="interview_date" style="width:200px;" class="form-control interview_date" autocomplete="off" @if($data->interviewdate) value="{{date('d\-m\-Y h:i A',strtotime($data->interviewdate))}}" @endif>	

											<button type="button" class="btn btn-info gridbtn" style="width:220px;" onclick="updateInterviewDate('{{Crypt::encrypt($data->requestid)}}')">
												<i class="fa fa-calendar"></i> Update Interview Date & Time
											</button>
											</div>
											
										</td>
									</tr>
									@endif
									@else
									<tr><td colspan="6" class="center">--No Record Found--</td></tr>
									@endif
								</table>
							</div>
						</div>

						
						<div class="section-block-detail">
							<div class="section-header-detail">
								<span class="material-icons">playlist_add_check</span> <!-- Hamburger menu icon -->
								&nbsp;<h2 class="section-title-detail">Participated Vendors</h2>
							</div>
							<div class="table-responsive">
								<table class="mytable" border="1" style="text-transform:none!important">
									<tr class="myheadbg">
										<td class="center padding-10 form-label" style="width:35px;">S.No.</td>
										<td class="padding-10 form-label">Firm Name</td>
										<td class="padding-10 form-label center" nowrap>Participation Date</td>
										<td class="padding-10 form-label center" nowrap>Interview Date</td>
										<td class="padding-10 form-label">Interview Link</td>
										<td class="padding-10 form-label"></td>
									</tr>
									@php $r=0; @endphp
									@foreach($vendors as $vendor)
									@php $r=$r+1; @endphp
									<tr>
										<td class="center padding-10 v-top">{{$r}}
										
										</td>
										<td class="mytdleft padding-10 v-top">{{$vendor->companyname}} [{{$vendor->tiername}}]
										</td>
										<td class="center padding-10 v-top" nowrap>{{date('d\-m\-Y, h:i A',strtotime($vendor->participationdate))}}</td>
										<td class="center padding-10 v-top">@if($vendor->interviewdate && $vendor->interviewdate!='2010-00-00 00:00:00'){{ date('d\-m\-Y h:i A',strtotime($vendor->interviewdate)) }}@endif</td>
										<td class="padding-10 v-top" nowrap>{{ $vendor->interviewlink }}</td>
										<td class="padding-10 center">
											<a href="{{ route('view.pmparticipations',Crypt::encrypt($vendor->participationid))}}">
												<button type="button" class="btn btn-info width-100" style="margin-top:2px;">
													<i class="fa fa-hand-o-right"></i> View Files
												</button>
											</a>											
										</td>
										
									</tr>
									@php
									$intDate='';
									if($vendor->interviewdate!='')
									$intDate	=	date('d\-m\-Y',strtotime($vendor->interviewdate));
									
									@endphp
									@endforeach
								</table>
							</div>
						</div>
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
function parseDMY(dateStr) {
	const parts = dateStr.split("-");
	const day = parseInt(parts[0], 10);
	const month = parseInt(parts[1], 10) - 1;
	const year = parseInt(parts[2], 10);
	return new Date(year, month, day);
}


function updateInterviewDate(requestid)
{
	var interviewdate = $("#interview_date").val();
	bootbox.confirm('Do you confirm this action?.',function(result){
		if(result)
		{
			$.ajax({
				url: '{{route('updatepm.interviewdate')}}',
				type: 'POST',
				data: {'requestid':requestid,'interviewdate':interviewdate},
				headers: {
					'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') // CSRF token
				},
				success: function (response) {
					if(response.status==200)
					{
						bootbox.alert({
							message: response.message,
							callback: function () {
								setTimeout(function() { location.reload(); },1000);
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
						setTimeout(function() { $(".no-skin").css("padding-right",""); },5000);
					}			
				}
			});
		}
	});	
}

</script>


<script src="{{ asset('panel/assets/js/datatablePaginationWithFilter.js') }}"></script>
@endsection