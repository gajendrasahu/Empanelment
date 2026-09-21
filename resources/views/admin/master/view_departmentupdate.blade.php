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
			<li class="active"><a data-toggle="tab" href="#home"><i class="green ace-icon fa fa-plus-circle bigger-120" style="vertical-align:text-top;"></i>EoI DETAIL</a></li>
		@endif
		</ul>

	
		<div class="tab-content no-border" style="border:1px solid #ddd; min-height:100px; padding:0px 10px;">
@if(in_array(1,Session::get('actions')))
<div id="home" class="tab-pane in active" style="padding:0px 10px!important;">
	<div class="row">
		<div class="row">
			<form name="frm" id="frm" action="{{ route('update.draft',Crypt::encrypt($data->requestid))}}" method="post" enctype="multipart/form-data">
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
				@if ($errors->any())
				<div class="col-sm-12">
				    <div class="alert alert-block alert-danger">
				    	<button type="button" class="close" data-dismiss="alert">
							<i class="ace-icon fa fa-times"></i>
						</button>
						<ul>
							@foreach ($errors->all() as $error)
								<li>{{ $error }}</li>
							@endforeach
						</ul>
				    </div>
				</div>
				@endif				
				
				@csrf
				<div class="form-group">
<div class="col-sm-5">
<table class="mytable" border="1" style="text-transform: none!important;">
	<tr class="myheadbg"><td class="td-padding" style="font-size:16px; font-weight:bold;" colspan="12">Project & Department Detail</td></tr>
	@if($data->eoinumber!='')
	<tr>
		<td class="mytdleft" style="width:200px; padding:4px 5px!important;" nowrap><b>EoI Number</b></td>
		<td class="mytdleft td-padding" style="padding:4px 5px!important;"><b>{{ $data->eoinumber }}</b></td>
	</tr>
	@endif
	<tr>
		<td class="mytdleft" style="width:200px; padding:4px 5px!important;" nowrap>Project Title</td>
		<td class="mytdleft td-padding" style="padding:4px 5px!important;">{{ ucwords(strtolower($data->projecttitle)) }}</td>
	</tr>
	<tr>
		<td class="mytdleft" style="width:200px; padding:4px 5px!important;" nowrap>Project Duration</td>
		<td class="mytdleft td-padding" style="padding:4px 5px!important;">{{ ucwords(strtolower($data->projectduration)) }} Months</td>
	</tr>
	<tr>
		<td class="mytdleft" style="width:200px; padding:4px 5px!important;" nowrap>Department Name</td>
		<td class="mytdleft td-padding" style="padding:4px 5px!important;">{{ ucwords(strtolower($data->departmentname)) }}</td>
	</tr>
	<tr>
		<td class="mytdleft td-padding" style="width:200px; padding:4px 5px!important;" nowrap>Officer Name</td>
		<td class="mytdleft td-padding" style="padding:4px 5px!important;">{{ ucwords(strtolower($data->name)) }}</td>
	</tr>
	<tr>
		<td class="mytdleft td-padding" style="width:200px; padding:4px 5px!important;" nowrap>Designation</td>
		<td class="mytdleft td-padding" style="padding:4px 5px!important;">{{ ucwords(strtolower($data->designation)) }}</td>
	</tr>
	<tr>
		<td class="mytdleft td-padding" style="width:200px;" nowrap>Contact Number</td>
		<td class="mytdleft td-padding" style="padding:4px 5px!important;">{{ ucwords(strtolower($data->mobilenumber)) }}</td>
	</tr>
	<tr>
		<td class="mytdleft td-padding" style="width:200px; padding:4px 5px!important;" nowrap>Email Id</td>
		<td class="mytdleft td-padding" style="padding:4px 5px!important; text-transform:lowercase!important;">{{ strtolower($data->email) }}</td>
	</tr>
	<tr>
		<td class="mytdleft td-padding" style="width:200px; padding:4px 5px!important;" nowrap>Date of Submission</td>
		<td class="mytdleft td-padding" style="padding:4px 5px!important;" nowrap>{{ date('d\-m\-Y, h:i A',strtotime($data->creationdate)) }}</td>
	</tr>
</table>
</div>
<div class="col-sm-7">
<table class="mytable" border="1" style="text-transform: none!important;">
	<tr class="myheadbg"><td class="td-padding" style="font-size:16px; font-weight:bold;" colspan="12">Fact Sheet</td></tr>
	<tr>
		<td class="mytdleft" style="width:200px; padding:4px 5px!important;" nowrap>EoI reference Number</td>
		<td class="" nowrap>
			<input type="text" name="eoinumber" id="eoinumber" value="{{ old('eoinumber',$data->eoinumber) }}" required readonly class="" style="width:100%;"></td>
	</tr>
	<tr>
		<td class="mytdleft" style="width:200px; padding:4px 5px!important;" nowrap>Name of Issuer</td>
		<td class="" nowrap>
			<input type="text" name="issuername" readonly id="issuername" required value="Chhattisgarh Infotech Promotion Society (CHiPS)" style="width:100%;" placeholder="Name of Issuer">
		</td>
	</tr>
	<tr>
		<td class="mytdleft" style="width:200px; padding:4px 5px!important;" nowrap>Name Of Engagement</td>
		<td class="" nowrap>
			<input type="text" name="engagementname" id="engagementname" required value="{{ old('engagementname',$data->engagementname) }}" style="width:100%;" placeholder="Name Of Engagement">
		</td>
	</tr>
	<tr>
		<td class="mytdleft" style="width:200px; padding:4px 5px!important;" nowrap>Release Date Of EoI By CHiPS</td>
		<td class="" nowrap>
			<input type="text" name="releasedate" id="releasedate" required value="{{ old('releasedate',$data->releasedate ? date('d-m-Y',strtotime($data->releasedate)) : '') }}" placeholder="dd-mm-YYYY" style="width:100%;">
		</td>
	</tr>
	<tr>
		<td class="mytdleft" style="width:200px; padding:4px 5px!important;" nowrap>Last Date Of Pre-bid Query</td>
		<td class="" nowrap>
			<input type="text" name="prebidlastdate" id="prebidlastdate" placeholder="dd-mm-YYYY" required value="{{ old('prebidlastdate',$data->prebidlastdate ? date('d-m-Y',strtotime($data->prebidlastdate)) : '') }}" style="width:100%;">
		</td>
	</tr>
	<tr>
		<td class="mytdleft" style="width:200px; padding:4px 5px!important;" nowrap>Last Date For Submission Of Proposals</td>
		<td class="" nowrap>
			<input type="text" name="deadlinedate" id="deadlinedate" placeholder="dd-mm-YYYY" required value="{{ old('deadlinedate',$data->deadlinedate ? date('d-m-Y',strtotime($data->deadlinedate)) : '') }}" style="width:100%;">
		</td>
	</tr>
	<tr>
		<td class="mytdleft" style="width:200px; padding:4px 5px!important;" nowrap>Date of Presentation & Candidate Interview</td>
		<td class="" nowrap>
			<input type="text" name="interviewdate" id="interviewdate" placeholder="dd-mm-YYYY" required value="{{ old('interviewdate',$data->interviewdate ? date('d-m-Y',strtotime($data->interviewdate)) : '') }}" style="width:100%;">
		</td>
	</tr>
	<tr>
		<td class="mytdleft" style="width:200px; padding:4px 5px!important;" nowrap>Address For Communication</td>
		<td class="" nowrap>
			<textarea name="communicationaddress" required readonly id="communicationaddress" rows="3" style="width:100%;" placeholder="Address For Communication">Chief Executive Officer&#10;Chhattisgarh Infotech Promotion Society (CHiPS)&#10;State Data Centre Building, Near Police Control Room, Civil Lines, Raipur, Chhattisgarh-492001&#10;Tel : 91771-404158&#10;Email : ceochips@nic.in, empl.chips@cgchips.in</textarea>
		</td>
	</tr>
	
</table>
</div>
<div class="col-sm-12">
<div class="table-responsive">
<table class="table table-bordered table-striped table-hover mytable" style="text-transform: none!important;" border="1">
	<tr class="primary-bgcolor"><td class="mytdleftwhite padding-5 font-bold" colspan="15">Added Expression of Interest List</td></tr>
	<tr class="primary-bgcolor color-white font-12 font-bold">
		<td class="mytdleftwhite padding-5 center" style="width:50px;">S.No.</td>
		@if($data->categoryid==1)
		<td class="mytdleftwhite padding-5" nowrap>Role</td>
		
		@elseif($data->categoryid==2)
		<td class="mytdleftwhite padding-5" nowrap>Sector</td>
		<td class="mytdleftwhite padding-5" nowrap>Position</td>
		@endif
		<td class="mytdleftwhite padding-5" nowrap>Tier</td>		
		<td class="mytdleftwhite padding-5" nowrap>Experience <i class="fa fa-info-circle" title="As per empanelment"></i></td>
		<td class="mytdleftwhite padding-5" nowrap>Deployment Type</td>
		@if($data->categoryid==1)
		<td class="mytdleftwhite padding-5" nowrap>Qualification</td>
		@endif
		<td class="mytdleftwhite padding-5" nowrap>Duration</td>
		<td class="mytdleftwhite padding-5" nowrap>Rate <i class="fa fa-info-circle" title="Man Month Rate Including Tax"></i></td>
		<td class="mytdleftwhite padding-5" nowrap>Remark</td>
	</tr>
	<tbody class="eoidata">
	@php $i=1; $total=0; @endphp
	@foreach($detail as $detail)
	
	<tr class="tr-20 font-12">
		<td nowrap class="mytd padding-5 center">{{$i}}</td>
		@if($data->categoryid==2)
		<td nowrap class="mytd padding-5">{{$detail->sectorname}}</td>
		<td nowrap class="mytd padding-5">{{$detail->consultantposition}}</td>
		@else
		<td nowrap class="mytd padding-5">{{$detail->role}}</td>
		@endif
		<td nowrap class="mytd padding-5">{{$detail->tiername}}</td>
		@if($data->categoryid==1)
		<td nowrap class="mytd padding-5">{{$detail->workexperience}} [L-{{$detail->experiencelevel}}]</td>

		@else
		<td nowrap class="mytd padding-5">{{$detail->experience}}</td>
		<td nowrap class="mytd padding-5">{{$detail->employmenttype}}</td>
		@endif
		@if($data->categoryid==1)
		<td nowrap class="mytd padding-5">{{$detail->qualification}}</td>
		@endif
		<td nowrap class="mytd padding-5">{{$detail->duration}} Months</td>
		<td nowrap class="mytd padding-5">{{$detail->budget}}</td>
		<td class="mytd padding-5">{{$detail->remark}}</td>
	</tr>
	@php $i=$i+1; @endphp
	@php $total=$total+$detail->total; @endphp
	@endforeach
	<tr>
		<td class="padding-5 font-14 font-bold" colspan="7" style="text-align:right;">Cost of Resources (Incuding Tax)</td>
		<td class="padding-5 font-14 font-bold">{{$data->totalbudget}}</td>
		<td class="padding-5 font-14 font-bold">&nbsp;</td>
	</tr>
	<tr>
		<td class="padding-5 font-14 font-bold" colspan="7" style="text-align:right;">CHiPS Admin Charge (5%)</td>
		<td class="padding-5 font-14 font-bold">{{$data->totaladmincharge}}</td>
		<td class="padding-5 font-14 font-bold">&nbsp;</td>
	</tr>
	<tr>
		<td class="padding-5 font-14 font-bold" colspan="7" style="text-align:right;">Grand Total</td>
		<td class="padding-5 font-14 font-bold">{{$data->totalamount}}</td>
		<td class="padding-5 font-14 font-bold">&nbsp;</td>
	</tr>
	@if($i==1)
	<tr>
		<td colspan="9" class="center">--NO RECORD ADDED--</td>
	</tr>
	@endif
	</tbody>
</table>
</div>
</div>
<div class="col-sm-12">&nbsp;</div>
<div class="col-sm-12">
<table class="mytable" border="1" style="text-transform: capitalize!important;">
	<tr class="primary-bgcolor"><td class="mytdleftwhite" style="padding:5px!important;"><b>About Project</b></td></tr>
	<tr>
		<td class="padding-2">
		{!! $data->aboutproject !!}
		@if($data->aboutproject=='')
			DATA NOT AVAILABLE
		@endif
		</td>
	</tr>
</table>

<table class="mytable" border="1" style="text-transform: capitalize!important;">
	<tr class="primary-bgcolor"><td class="mytdleftwhite" style="padding:5px!important;"><b>Project Objective</b></td></tr>
	<tr>
		<td class="padding-2">
		{!! $data->projectobjective !!}
		@if($data->projectobjective=='')
			DATA NOT AVAILABLE
		@endif
		</td>
	</tr>
</table>

<table class="mytable" border="1" style="text-transform: capitalize!important;">
	<tr class="primary-bgcolor"><td class="mytdleftwhite" style="padding:5px!important;"><b>Scope Of Work</b></td></tr>
	<tr>
		<td class="padding-2">
		{!! $data->scopeofwork !!}
		</td>
	</tr>
</table>

@if($data->anyother!='')
<table class="mytable" border="1" style="text-transform: capitalize!important;">
	<tr class="primary-bgcolor"><td class="mytdleftwhite" style="padding:5px!important;"><b>Other Project Requirement</b></td></tr>
	<tr>
		<td class="padding-2">
		{!! $data->anyother !!}
		</td>
	</tr>
</table>
@endif

@if($data->termsandcondition!='')
<table class="mytable" border="1" style="text-transform: capitalize!important;">
	<tr class="primary-bgcolor"><td class="mytdleftwhite" style="padding:5px!important;"><b>Payment & Penalty Terms</b></td></tr>
	<tr>
		<td class="padding-2">
		{!! $data->termsandcondition !!}
		</td>
	</tr>
</table>
@endif

</div>
<div class="col-sm-12">&nbsp;</div>
<div class="col-sm-12">
<table class="mytable" border="1" style="text-transform: none!important;">
	@if(!$attachments->isEmpty())
	<tr><td class="text-center">&nbsp;</td></tr>		
	<tr>
		<td>
			<table class="mytable" border="1" style="text-transform:none!important; width:350px!important;">
				<tr class="myheadbg">
					<td class="padding-5" colspan="6">
						ATTACHMENTS
					</td>
				</tr>
				<tr class="myheadbg">
					<td class="padding-2">Attachment Title</td>
					<td class="padding-2"></td>
				</tr>
				@foreach($attachments as $attach)
				<tr>
					<td class="padding-2" nowrap>{{$attach->attachmenttitle}}</td>
					<td class="padding-0" style="width:70px;">
						<a href="{{ asset('storage/'.$attach->attachmentfile) }}" target="_blank" class="btn btn-info mygridbtn">
							<i class="fa fa-file-pdf-o"></i> VIEW
						</a>
					</td>
				</tr>
				@endforeach
			</table>
		
		</td>
	</tr>
	@endif

	@if($updates->count()>0)
	<tr><td class="text-center">&nbsp;</td></tr>
	<tr>
		<td>
			<table class="mytable" style="width:100%;" border="1" style="text-transform: none!important;">
				<tr class="primary-bgcolor"><td colspan="5" class="mytdleftwhite padding-5"><b>Mark Department Updates as Read</b></td></tr>
				<tr class="primary-bgcolor">
					<td class="mytdleftwhite padding-5 center" style="width:30px;">S.No.</td>
					<td class="mytdleftwhite padding-5 center" style="width:30px;"></td>
					<td class="mytdleftwhite padding-5 center" style="width:130px;">Updated On</td>
					<td class="mytdleftwhite padding-5">Narration</td>
				</tr>
				@foreach($updates as $update)
				<tr>
					<td class="padding-5 center">{{$loop->iteration}}</td>
					<td class="padding-5 center">
						@if($update->isviewed==0)
						<input type="checkbox" onclick="MarkAsRead('{{route('mark.asread')}}','{{Crypt::encrypt($update->recordid)}}')">
						@else
						<input type="checkbox" disabled checked>
						@endif
					</td>
					<td class="padding-5 center">{{date('d\-m\-Y, h:i A',strtotime($update->updatedon))}}</td>
					<td class="padding-5" style="text-transform: none!important;">{!!$update->particular!!}</td>
				</tr>
				@endforeach
			</table>			
		</td>
	</tr>
	@endif

	@if($data->eoistatus<3 && $data->isupdated==0)
	<tr><td class="text-center">&nbsp;</td></tr>
	<tr class="finalsubmit">
		<td class="text-center">
			<table style="width:100%;">
				<tr><td>&nbsp;</td></tr>
				<tr>
					<td>
						<button type="button" class="btn btn-info mygridbtn" id="updateDraftBtn" style="width:350px; float:left;">
							<i class="fa fa-save"></i> UPDATE DRAFT AND TERMS AND CONDITIONS DETAIL
						</button>
						
						<button type="button" class="btn btn-info mygridbtn" id="frmSubmit" style="width:400px; float:right;">
							<i class="fa fa-save"></i> SUBMIT AND SEND DRAFT TO DEPARTMENT (IF COMPLETED)
						</button>
					</td>
				</tr>
			</table>
		</td>
	</tr>
	@endif
</table>
	

	<span class="text-danger">@error('termsandcondition') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
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
<script src="{{ asset('panel/assets/tinymce/tinymce.min.js') }}"></script>


<script>


    tinymce.init({
        selector: '#draftdetail',
        promotion: false,
        branding: false,
        height: 350,
        autoresize_max_height: 490,
        toolbar_mode: 'floating',
        plugins: 'code advlist autolink lists charmap preview table searchreplace save',
        toolbar: 'undo redo | formatselect | bold italic underline strikethrough | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link | searchreplace',
        setup: function (editor) {
                // Add custom CSS to the editor content area
                editor.on('init', function () {
                    var contentArea = editor.getContainer().querySelector('.tox-edit-area__iframe');
                    if (contentArea) {
                        contentArea.style.overflow = 'auto';
                    }
                });
            }
    });

    tinymce.init({
        selector: '#termsandcondition',
        promotion: false,
        branding: false,
        height: 350,
        autoresize_max_height: 490,
        toolbar_mode: 'floating',
        plugins: 'code advlist autolink lists charmap preview table searchreplace save',
        toolbar: 'undo redo | formatselect | bold italic underline strikethrough | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link | searchreplace',
        setup: function (editor) {
                // Add custom CSS to the editor content area
                editor.on('init', function () {
                    var contentArea = editor.getContainer().querySelector('.tox-edit-area__iframe');
                    if (contentArea) {
                        contentArea.style.overflow = 'auto';
                    }
                });
            }
    });


$(document).ready(function() {

	$('#updateDraftBtn').on('click', function(e) {
		e.preventDefault();
		tinymce.triggerSave();
		var form = $('#frm')[0];
		var formData = new FormData(form);
		$.ajax({
			url: '{{route("save.draft")}}',
			method: 'POST',
			data: formData,
			processData: false,
			contentType: false,
			success: function(response) {
				if(response.status === 200)
				{
					setTimeout(function() { $(".finalsubmit").css("display",""); },2000);
					bootbox.alert("DRAFT AND TERMS AND CONDITIONS DETAIL SAVED SUCCESSFULLY!");
					return false;
				}
				else
				{
					bootbox.alert('Something went wrong: ' + response.message);
					return false;
				}
			},
			error: function(xhr) {
				let errors = xhr.responseJSON?.errors;
				let message = '';
				$.each(errors, function(key, val) {
					message += '<i class="fa fa-hand-o-right"></i> '+ val + '<br>';
				});
				bootbox.alert(message);
			}
		});
	});
	
	$('#frmSubmit').on('click', function(e) {
		e.preventDefault();

        const releasedate = new parseDMY($("#releasedate").val());
        const prebidlastdate = new parseDMY($("#prebidlastdate").val());
        const deadlinedate = new parseDMY($("#deadlinedate").val());
        const interviewdate = new parseDMY($("#interviewdate").val());

        // Clear previous error messages if any
        $(".date-error").remove();

        // Validation logic
        if(!(releasedate < prebidlastdate && prebidlastdate < deadlinedate && deadlinedate < interviewdate))
		{
			
			bootbox.alert('<b style="color:red;">The dates must follow a valid chronological order: Release Date < Pre-bid Query Last Date < Deadline < Interview Date.</b>');
			return false;
        }

		
		bootbox.confirm('Do you want to submit and send draft to department?',function(result){
			if(result)
			{
				document.forms['frm'].submit();
			}
		});
	});

	
});	

function parseDMY(dateStr) {
	// Expects input as "dd-mm-yyyy"
	const parts = dateStr.split("-");
	const day = parseInt(parts[0], 10);
	const month = parseInt(parts[1], 10) - 1; // Month is 0-indexed
	const year = parseInt(parts[2], 10);
	return new Date(year, month, day);
}
</script>

<script src="{{ asset('panel/assets/js/master.js') }}"></script>
<script src="{{ asset('panel/assets/js/datatablePaginationWithFilter.js') }}"></script>
@endsection