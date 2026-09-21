@extends('admin.admin_master')
@section('admin')
@php
$t=0;
@endphp

<div class="main-content">
<style>
.ace-file-input
{
	width:300px;
}

</style>
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
					<i class="material-icons-outlined" style="font-size:20px;">contact_mail</i> {{ $vendor->companyname }}
				</a>
			</li>
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
				@if ($errors->any())
					<div class="col-sm-12">
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
									<i class="material-icons-outlined">topic</i> {{ $eoi->projecttitle }}
								</div>
								<div class="details-detail lh-25">
									@if(session('userType')=='DEPARTMENT')
									<b>Department :</b> {{ $eoi->departmentname }}<br>
									@else
									<b>Project Manager :</b> {{ $eoi->departmentname }}<br>
									@endif
									<b>{{ $eoi->eoinumber }}</b> | Requested on : <b>{{ date('d\-m\-Y, h:i A',strtotime($eoi->creationdate)) }}</b> | Project Duration : {{ $eoi->projectduration }} Months<br>
									
								</div>
							</div>
						</div>
						<div class="section-block-detail">
							<div class="section-header-detail">
								<span class="material-icons-outlined">group_add</span>
								&nbsp;<h2 class="section-title-detail">Resource Details</h2>
							</div>
							<table class="mytable" border="1" style="text-transform: capitalize!important;">
								@foreach($requestdetail as $index=>$req)
								@if($eoi->categoryid==2)
								<tr class="myheadbg">
									<td class="mytd form-label padding-10" nowrap>{{$index+1}}) Sector : {{$req->sectorname}} | Position : {{$req->consultantposition}} | Experience <i class="fa fa-info-circle" title="As per empanelment"></i> : {{$req->experience}} | Duration : {{$req->duration}} Months</td>
								</tr>
								@else
								<tr class="myheadbg">
									<td class="mytd form-label padding-10" nowrap>{{$index+1}}) Experience <i class="fa fa-info-circle" title="As per empanelment"></i> : {{$req->experience}} | Duration : {{$req->duration}} Months</td>
								</tr>
								@endif
								<tr class="">
									<td class="mytdwhite padding-10" style="width:20px;" nowrap>Remark : {!!$req->remark!!}</td>
								</tr>
								@if($req->resumes->count()>0)
								<tr>
									<td class="padding-0">
										<table class="mytable" border="1" style="text-transform: capitalize!important;">
											<tr class="">
												<td class="padding-10 font-bold" style="width:70px;">Resume</td>
												<td class="padding-10 font-bold" style="width:250px;">Name</td>
												<td class="padding-10 font-bold">Remark</td>
											</tr>
											@foreach($req->resumes as $resume)
											<tr>
												<td class="padding-10">
													<a href="{{route('view.uploadedfile',Crypt::encrypt($resume->resume))}}" target="_blank" class="btn btn-info width-100 no-hover">
														<i class="fa fa-file-pdf-o"></i> View
													</a>
												</td>
											
												<td class="padding-10 form-label" nowrap>
													<span class="form-label">{{$resume->name}}</span>
												</td>
												<td class="padding-10">
													{!!$resume->remark!!}
												</td>				
											</tr>
											@endforeach
										</table>
									</td>
								</tr>
								@else
								<tr>
									<td>
									<div class="alert-success animated flipInX delay-02 padding-5 font-14 font-bold">
									Resource Already Selected For This Role / Position
									</div>
									</td>
								</tr>
								@endif
								@endforeach
							</table>
							<table class="" style="text-transform: none!important; margin-top:20px; width:100%;">
								<tr>
									<td>
									@if($floated->presentationfile!='')
										<a href="{{route('view.uploadedfile',Crypt::encrypt($floated->presentationfile))}}" target="_blank">
											<span class="btn btn-info" style="width:200px;"><i class="fa fa-download"></i> View / Download PPT</span>
										</a>		
									@endif		
									@if($floated->signedcopyofeoi!='')
										<a href="{{route('view.uploadedfile',Crypt::encrypt($floated->signedcopyofeoi))}}" target="_blank">
											<span class="btn btn-info" style="width:300px;"><i class="fa fa-download"></i> View / Download Signed EoI File</span>
										</a>		
									@endif		
									</td>
								</tr>
								<tr class=""><td>&nbsp;</td></tr>
								<tr>
									<td class="padding-0 right">
										<a href="{{ route('view.deptpptresumes',Crypt::encrypt($eoi->requestid))}}">
										<button type="button" class="multi-btn multi-btn-info" style="width:130px;"><i class="fa fa-arrow-left"></i> Back</button>
										</a>
									</td>
								</tr>
							</table>
							
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
<script src="{{ asset('panel/assets/js/ace-elements.min.js') }}"></script>
<script src="{{ asset('panel/assets/js/ace.min.js') }}"></script>


<script>

tinymce.init({
	selector: '#termsandcondition',
	promotion: false,
	branding: false,
	height: 250,
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

	$('#momfile').ace_file_input({
		no_file:'No File ...',
		btn_choose:'Upload MoM',
		btn_change:'Change',
		btn_name:'fourthimage',
		thumbnail:false //| true | large
	});

	$('#markingsheet').ace_file_input({
		no_file:'No File ...',
		btn_choose:'Upload Marking Sheet',
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