@extends('admin.admin_master')
@section('admin')
@php
$t=0;
$preferred[1]	=	'Tier - I';
$preferred[2]	=	'Tier - II';
$preferred[3]	=	'Both';

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
				<a data-toggle="tab" href="#home" class="form-label">
					<i class="green ace-icon fa fa-calendar bigger-120 datalist" style="vertical-align:text-top;"></i> Extend Date
				</a>
			</li>
		@endif
		</ul>

	
		<div class="tab-content no-border" style="border:1px solid #ddd; min-height:100px; padding:0px 10px;">
@if(in_array(1,Session::get('actions')))
<div id="home" class="tab-pane in active" style="padding:0px 10px!important;">
	<div class="row">
		<div class="row">
			<form name="frm" id="frm" action="{{ route('store.eoiextension',Crypt::encrypt($data->requestid))}}" method="post" enctype="multipart/form-data">
				@csrf
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
								<i class="fa fa-warning"></i> {{ $error }}<br>
							@endforeach
						</ul>
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
								<div class="details-detail lh-30">
									<input type="hidden" name="form_token" id="form_token" value="{{$token}}">
									
									<b>Department / Project Manager : </b>{{ $data->departmentname}}<br>
									EoI Number: {{$data->eoinumber}}
									|									
									 Requested on : <b>{{ date('d\-m\-Y, h:i A',strtotime($data->creationdate)) }}</b> | Project Duration : <b>{{ ucwords(strtolower($data->projectduration)) }} Months</b><br>
									
								</div>
							</div>
						</div>
						
						<div class="section-block-detail">
							<div class="section-header-detail">
								<span class="material-icons">playlist_add_check</span> <!-- Hamburger menu icon -->
								&nbsp;<h2 class="section-title-detail">Fact Sheet</h2>
							</div>
							<div class="grid-layout-detail cols-2">
								<div class="ui-item-detail">
									<label>Name of Issuer</label>
									Chhattisgarh Infotech Promotion Society (CHiPS)
								</div>
								<div class="ui-item-detail">
									<label>Name of Engagement</label>
									{{$data->engagementname}}
								</div>
								<div class="ui-item-detail">
									<label>Release Date of EoI By CHiPS <i class="fa fa-info-circle show-updates" data-url="{{ route('get.updates',[Crypt::encrypt('releasedate'),Crypt::encrypt($data->requestid)]) }}"></i></label>
									{{date('d\-m\-Y',strtotime($data->releasedate))}}
								</div>
								<div class="ui-item-detail">
									<label>Last Date of Pre-bid Query <i class="fa fa-info-circle show-updates" data-url="{{ route('get.updates',[Crypt::encrypt('prebidlastdate'),Crypt::encrypt($data->requestid)]) }}"></i></label>
									<div class="input-group" style="width:350px;">
									<input type="text" name="prebidlastdate" id="prebidlastdate" placeholder="dd-mm-YYYY" required value="{{ old('prebidlastdate',$data->prebidlastdate ? date('d-m-Y',strtotime($data->prebidlastdate)) : '') }}" style="width:350px;">
									<span class="input-group-addon"><i class="fa fa-calendar bigger-110"></i></span>
									
									</div>
									<span class="text-danger">@error('prebidlastdate') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
								</div>
								<div class="ui-item-detail">
									<label>Last Date for Submission of Proposals <i class="fa fa-info-circle show-updates" data-url="{{ route('get.updates',[Crypt::encrypt('deadlinedate'),Crypt::encrypt($data->requestid)]) }}"></i></label>
									<div class="input-group" style="width:350px;">
									<input type="text" name="deadlinedate" id="deadlinedate" placeholder="dd-mm-YYYY hh:mm AM/PM" required value="{{ old('deadlinedate', $data->deadlinedate ? date('d-m-Y h:i A', strtotime($data->deadlinedate)) : '') }}" style="width:350px;">
									<span class="input-group-addon"><i class="fa fa-calendar bigger-110"></i></span>
									
									</div>
									<span class="text-danger">@error('deadlinedate')<i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
								</div>
								<div class="ui-item-detail">
									<label>Tentative Date of Presentation and Interview <i class="fa fa-info-circle show-updates" data-url="{{ route('get.updates',[Crypt::encrypt('interviewdate'),Crypt::encrypt($data->requestid)]) }}"></i></label>
									<div class="input-group" style="width:350px;">
									
									<input type="text" name="interviewdate" id="interviewdate" placeholder="dd-mm-YYYY hh:mm AM/PM" required value="@if($data->interviewdate!='' && $data->interviewdate!='1970-01-01'){{ old('interviewdate',$data->interviewdate ? date('d-m-Y h:i A',strtotime($data->interviewdate)) : '') }}@endif" style="width:350px;">
									
									<span class="input-group-addon"><i class="fa fa-calendar bigger-110"></i></span>
									<span class="text-danger">@error('interviewdate') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
									</div>
								</div>
								<div class="ui-item-detail" style="grid-column: span 2;">
									<label>Address for Communication</label>
Chief Executive Officer&#10;Chhattisgarh Infotech Promotion Society (CHiPS)&#10;State Data Centre Building, Near Police Control Room, Civil Lines, Raipur, Chhattisgarh-492001<br>Tel : 91771-404158<br>Email : ceochips@nic.in, empl.chips@cgchips.in									
								</div>
							</div>
							
						</div>
						@if($data->eoistatus == 3 && date('Y\-m\-d',strtotime($data->deadlinedate))<date('Y\-m\-d'))
						<div class="section-block-detail">
							<table style="width:100%;">
								<tr>
									<td style="text-align:right;">
<button type="button" class="btn btn-info myfrmbtn width-100 extension-btn">Submit</button>									
									</td>
								</tr>
							</table>
						</div>
						@endif


<div class="col-sm-12">
	

	
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
<script src="{{ asset('panel/assets/tinymce/tinymce.min.js') }}"></script>

<script>

$('.extension-btn').on('click', function(e) {
	bootbox.confirm('Are you sure you want to extend the deadline?<br><br>This action cannot be reverted. The concerned firms and the department will be notified via email.',function(result){
		if(result)
		{
			$("#frm").submit();
		}
	});
});

</script>
<script src="{{ asset('panel/assets/js/master.js') }}"></script>
<script src="{{ asset('panel/assets/js/datatablePaginationWithFilter.js') }}"></script>
@endsection