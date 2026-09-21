@extends('admin.admin_master')
@section('admin')
@php
$t=0;
use Carbon\Carbon;
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
					<i class="green ace-icon fa fa-folder-o bigger-120 datalist" style="vertical-align:text-top;"></i>EoI Detail
				</a>
			</li>			
		@endif
		</ul>

	
		<div class="tab-content no-border" style="border:1px solid #ddd; min-height:100px; padding:0px 10px;">
@if(in_array(1,Session::get('actions')))
<div id="home" class="tab-pane in active" style="padding:0px 10px!important;">
	<div class="row">
		
			<form name="participate" id="participate" action="{{ route('participate.eoi',Crypt::encrypt($data->floatid))}}" method="post" enctype="multipart/form-data">
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
				<br>
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
					<div class="content-detail">
						@if(!session('subUserId'))
						<div class="ui-card-detail">
							<div>
								<div class="title-detail">
									<i class="fa fa-user"></i> Assign EoI to User (Optional)
								</div>
								<div class="details-detail lh-25" style="display: flex; align-items: center; gap: 10px;">
								<select name="subuserid" id="subuserid">
									<option value="">--Select Name--</option>
									@foreach($subusers as $subuser)
									<option value="{{$subuser->userid}}" @if($subuser->userid==$data->subuserid) selected @endif>{{$subuser->name}}</option>
									@endforeach
								</select>
			@if($data->subuserid)
			<button type="button" class="btn btn-info myfrmbtn assignBtn" onclick="AssignUser('{{Crypt::encrypt($data->floatid)}}')">Move to Other User</button>
			@else
			<button type="button" class="btn btn-info myfrmbtn assignBtn" onclick="AssignUser('{{Crypt::encrypt($data->floatid)}}')">Submit</button>
			@endif
								</div>
							</div>							
						</div>
						@endif
						<div class="ui-card-detail">
							<div>
								<div class="title-detail">
									<i class="material-icons-outlined">topic</i> {{ $data->projecttitle }} 
								</div>
								<div class="details-detail lh-25">
									<input type="hidden" name="floatid" id="floatid" value="{{Crypt::encrypt($data->floatid)}}">
									<b>Department/Project Manager : </b>{{ $data->departmentname }}<br>
									EoI Number: <b>{{ $data->eoinumber }}</b> | Requested On : <b>{{ date('d\-m\-Y, h:i A',strtotime($data->creationdate)) }}</b>
								</div>
							</div>							
						</div>
						
						@include('admin/viewpages/page_indexing')
						@include('admin/viewpages/factsheet')
						
						@include('admin/viewpages/objective_without_editor')
						
						<div class="section-block-detail">
							<div class="section-header-detail">
								<span class="material-icons">group_add</span> <!-- Hamburger menu icon -->
								&nbsp;<h2 class="section-title-detail">Team Requirement</h2>
							</div>
							<div class="table-responsive">
							@if($tier_html!=''){!!$tier_html!!}@endif
							</div>
							
						</div>

						@include('admin.viewpages.evaluation_without_editor')
						
					</div>
				
<div class="col-sm-12">&nbsp;</div>
<div class="col-sm-12">

<table class="" style="text-transform:none!important; width:100%;">
	@if(!$hasParticipated)
	<tr class="finalsubmit">
		<td class="text-center">
		@php
			$deadline	= !empty($data->deadlinedate) ? \Carbon\Carbon::parse($data->deadlinedate) : null;
			$prebidLast = !empty($data->prebidlastdate) ? \Carbon\Carbon::parse($data->prebidlastdate)->setTime(17, 30, 0) : null;
			$today 		= \Carbon\Carbon::today();
		@endphp

		@if($prebidLast && $prebidLast->isFuture())
			{{-- Pre-bid still active or ending today --}}
			<button type="button" class="view-all-btn animated flipInX delay-03" disabled style="margin-right:50px;">
				<span class="fa fa-hand-o-right"></span>
				The pre-bid period is still active. Participation will open after it ends.
				<span class="fa fa-hand-o-left"></span>
			</button>
		@elseif(!$deadline && $deadline->isPast())
			{{-- Deadline already passed --}}
			<button type="button" class="view-all-btn animated flipInX delay-03" disabled style="margin-right:50px;">
				<span class="fa fa-hand-o-right"></span>
				Deadline for proposal submission is over.
				<span class="fa fa-hand-o-left"></span>
			</button>
		@elseif($deadline && $deadline->greaterThan(now()))
			<button type="button" class="view-all-btn animated flipInX delay-03" id="participateBtn" style="margin-right:50px;">
				<span class="fa fa-hand-o-right"></span> Participate Now <span class="fa fa-hand-o-left"></span>
			</button>
		@else
			<button type="button" class="view-all-btn animated flipInX delay-03" disabled style="margin-right:50px;">
				<span class="fa fa-hand-o-right"></span>
				Deadline for proposal submission is over.
				<span class="fa fa-hand-o-left"></span>
			</button>
		@endif
		
		@if(!$hasBroadcasted)
		@if($prebidLast && $prebidLast->isFuture())
		<a href="{{ route('prebid.enquiry',Crypt::encrypt($data->floatid))}}">
			<button type="button" class="view-all-btn animated flipInX delay-03">
				<span class="fa fa-comments-o"></span> Pre-bid Query
		</a>
		@endif
		@endif
		
		@if(Carbon::parse($data->deadlinedate)->isFuture())
		<button type="button" class="view-all-btn animated flipInX delay-03" style="margin-left:10px;" onclick="ShowInterest('{{route('show.interest',Crypt::encrypt($data->floatid))}}')">
			<span class="fa fa-bookmark-o"></span> Show Interest
		</button>		
		@endif
			
		</td>
	</tr>
	@else
	<tr class="finalsubmit">
		<td class="text-center">
			<button type="button" class="btn btn-info mygridbtn" disabled style="width:230px;">
				<i class="fa fa-thumbs-up"></i> Participation Already Initiated
			</button>
		</td>
	</tr>
	@endif
</table>
	

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

<script>


	$('#participateBtn').on('click', function(e) {
		e.preventDefault();
		$("#participateBtn").prop("disabled","disabled");
		bootbox.confirm('<b>Do you confirm your participation in this EoI?</b><br><br><b>Note:</b> Participation will be considered complete only after uploading the required documents (Resumes, PPT, and signed EoI copy).',function(result){
			if(result)
			{
				document.forms['participate'].submit();
			}
			else
			{
				$("#participateBtn").prop("disabled","");
			}
		});
	});
	
	
	function ShowInterest(rl)
	{
		bootbox.confirm('By clicking <b>`Show Interest`</b>.<br>You are indicating your preliminary interest in participating in this Expression of Interest (EoI).',function(result){
			if(result)
			{
				$.get(
					"" + rl,
					{
					}
				)
				.done(function (response) {

					if (typeof response === "object")
					{

						if (response.status !== 200)
						{
							bootbox.alert(response.message);
							return;
						}

						bootbox.alert(response.message);
						return;
					}
				})
				.fail(function (xhr) {

					// Fallback for server / network error
					if (xhr.responseJSON && xhr.responseJSON.message) {
						bootbox.alert(xhr.responseJSON.message);
					} else {
						bootbox.alert("An unexpected error occurred. Please try again.");
					}
				});
			}
			
		});
	}
	

@if(!session('subUserId'))
function AssignUser(floatid)
{
	$(".assignBtn").css("display","none");
	bootbox.confirm('Do you confirm this action?',function(result){
		if(result)
		{
			var subuserid	=	document.getElementById("subuserid").value;
			var form = $('#participate')[0];
			var formData = new FormData(form);
			formData.append('floatid',floatid);
			formData.append('subuserid',subuserid);
			$.ajax({
				url: '{{route("assignto.user")}}',
				method: 'POST',
				data: formData,
				processData: false,
				contentType: false,
				success:function(response)
				{
					if(response.status===200)
					{
						bootbox.alert(response.message);
						setTimeout(function(){ window.location.reload(); },3000);
					}
					else
					{
						$(".assignBtn").css("display","");
						bootbox.alert(response.message);
					}
				},
				error: function(xhr)
				{
					$(".assignBtn").css("display","");
					let errors	=	xhr.responseJSON?.errors;
					let message	=	'';
					$.each(errors, function(key, val)
					{
						message += '<i class="fa fa-hand-o-right"></i> '+ val + '<br>';
					});
					bootbox.alert(message);
				}
			});
		}
		else
		{
			$(".assignBtn").css("display","");
		}
	});
}
@endif
</script>


<script src="{{ asset('panel/assets/js/datatablePaginationWithFilter.js') }}"></script>
<script>
document.querySelectorAll('.format-indian').forEach(function(el) {
    el.innerText = formatIndianNumber(el.dataset.value);
});

</script>

@endsection




