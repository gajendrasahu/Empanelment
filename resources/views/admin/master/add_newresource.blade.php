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
				<a data-toggle="tab" class="form-label lh-20 font-14" style="vertical-align:middle!important;">
					<i class="green ace-icon fa fa-plus-circle bigger-120 datalist"></i> Add New Resource
					<p>{{strtoupper($eoi->eoinumber)}}</p>
				</a>
			</li>
		@endif
		</ul>

	
		<div class="tab-content no-border" style="border:1px solid #ddd; min-height:100px; padding:0px 10px;">
@if(in_array(1,Session::get('actions')))
<div id="home" class="tab-pane in active" style="padding:0px 10px!important;">
	<div class="row">
		<div class="row">
			<form name="frm" id="frm" action="{{ route('store.neworder',Crypt::encrypt($eoi->requestid))}}" method="post" enctype="multipart/form-data">
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
									<i class="material-icons-outlined">topic</i> {{ $eoi->projecttitle }}
								</div>
								<div class="details-detail lh-30">
									<b>Department / Project Manager : </b>{{ ucwords(strtolower($eoi->departmentname)) }}<br>
									
								</div>
							</div>
						</div>

						<div class="ui-card-detail">
							<div>
								<div class="title-detail">
									<i class="material-icons-outlined">contact_mail</i> {{ ucwords(strtolower($vendor->companyname)) }} [{{$vendor->tiername}}]
								</div>
								<div class="details-detail lh-30">
									<b>Address : </b>{{ $vendor->officelocation }}<br>
									
								</div>
							</div>
						</div>
						
						<div class="section-block-detail">
							<div class="section-header-detail">
								<span class="material-icons">group_add</span>
								<h2 class="section-title-detail">&nbsp;Add Resource Detail</h2>
							</div>
							<div class="grid-layout-detail cols-4" style="padding:0px 0px; gap:0px;">
							@if($eoi->categoryid==2)
							<div class="col-sm-6 padding-0">
							<table class="mytable pd-8" border="1" style="text-transform: none!important; margin-top:10px;">
								<tr>
									<td class="form-label">Sector*</td>
									<td>
										<input type="hidden" name="categoryid" id="categoryid" value="{{$eoi->categoryid}}">
										<input type="hidden" name="tierid" id="tierid" value="{{$vendor->tierid}}">
										<select name="sectorid" id="sectorid" class="width-full">
											<option value="">--Sector--</option>
											@foreach($sectors as $sector)
											<option value="{{$sector->sectorid}}">{{$sector->sectorname}}</option>
											@endforeach
										</select>
									</td>
								</tr>
								<tr>
									<td class="form-label">Position*</td>
									<td>
										<select name="positionid" id="positionid" class="width-full">
											<option value="">--Position--</option>
										</select>
									</td>
								</tr>
								<tr>
									<td class="form-label">Experience (as per empanelment)*</td>
									<td>
										<input type="text" name="experience" id="experience" readonly placeholder="Experience" class="width-full">
									</td>
								</tr>
								<tr>
									<td class="form-label">Qualification*</td>
									<td>
										<input type="text" name="qualification" id="qualification" placeholder="qualification" class="width-full">
									</td>
								</tr>
								<tr>
									<td class="form-label">Deployment Type*</td>
									<td>
										<select name="deploymenttype" id="deploymenttype" class="form-control employmenttype width-full" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}" onchange="PopulateDuration()">
											<option value="FULL TIME">FULL TIME</option>
											<option value="PART TIME">PART TIME</option>
										</select>
										
									</td>
								</tr>
								<tr>
									<td class="form-label">Duration*</td>
									<td>
										<select name="duration"  id="duration" onchange="CalculateBudget()" class="width-full">
												<option value="">--Duration--</option>
										</select>
									</td>
								</tr>
								<tr>
									<td class="form-label">Remark</td>
									<td>
										<input type="text" name="remark"  id="remark" class="width-full" placeholder="Remark">
									</td>
								</tr>
								<tbody>
								</tbody>
							</table>
							</div>
							<br>
							
							@endif
							</div>

						</div>
						
						
						<!-- INCLUDE OBJECTIVE ABOUT SCOPE CONTENT -->

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

<div class="modal fade" id="resourceModal" style="margin-top:-20px;" data-backdrop="static">
  <div class="modal-dialog modal-xl" style="width:95%;">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title form-label" style="float:left;">Resource Detail</h5>    
      </div>
      <div class="modal-body" id="resourceContent" style="min-height:200px; max-height:450px; overflow-y:scroll;">Loading...</div>
      <div class="modal-footer">
        <button type="button" class="btn btn-close" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<script src="{{ asset('panel/assets/js/jquery-2.1.4.min.js') }}"></script>
<script src="{{ asset('panel/assets/js/ace-elements.min.js') }}"></script>
<script src="{{ asset('panel/assets/js/ace.min.js') }}"></script>

<script>

jQuery(function($) {	

	$('#notesheet').ace_file_input({
		no_file:'No File ...',
		btn_choose:'Upload Note Sheet',
		btn_change:'Change',
		btn_name:'fourthimage',
		thumbnail:false
	});
	$('#signedcopy').ace_file_input({
		no_file:'No File ...',
		btn_choose:'Attach a Signed Order Copy*',
		btn_change:'Change',
		btn_name:'fourthimage',
		thumbnail:false
	});
	
	$(".ace-file-container").css("width","370px");
});


</script>

<script src="{{ asset('panel/assets/js/datatablePaginationWithFilter.js') }}"></script>
<script>
document.querySelectorAll('.format-indian').forEach(function(el) {
    el.innerText = formatIndianNumber(el.dataset.value);
});

</script>
@endsection