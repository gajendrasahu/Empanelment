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
		@permission('upload.committee')
			<li class="active">
				<a data-toggle="tab" href="#home" class="form-label">
					<i class="green ace-icon fa fa-plus-circle bigger-120 datalist" style="vertical-align:text-top;"></i>Add Committee Members
				</a>
			</li>
		@endpermission
		</ul>

	
		<div class="tab-content no-border" style="border:1px solid #ddd; min-height:100px; padding:0px 10px;">
@if(in_array(1,Session::get('actions')))
<div id="home" class="tab-pane in active" style="padding:0px 10px!important;">
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
					<div class="content-detail">
						<div class="ui-card-detail">
							<div>
								<div class="title-detail">
									<i class="material-icons-outlined">topic</i> {{ $data->projecttitle }}
								</div>
								<div class="details-detail lh-25">
									<b>Department / Project Manager : </b>{{ ucwords(strtolower($data->departmentname)) }}<br>
									EoI Number: <b>{{ $data->eoinumber }}</b> | Requested on : <b>{{ date('d\-m\-Y, h:i A',strtotime($data->creationdate)) }}</b> | Project Duration : <b>{{ ucwords(strtolower($data->projectduration)) }} Months</b><br>
									
								</div>
							</div>
						</div>

						@include('admin.viewpages.factsheet')
						
						<div class="section-block-detail">
							<div class="section-header-detail">
								<span class="material-icons">group_add</span>
								&nbsp;<h2 class="section-title-detail">Committee Member</h2>
							</div>
							<table class="mt-5">
								<tr>
									<td class="padding-5">
										<select class="select2" name="memberid" id="memberid" required onKeyPress="return OnKeyPress(this, event)"  data-placeholder="ADD COMMITTEE MEMBER" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}" autofocus style="border-radius:0px!important;">
											<option value=""></option>
											@foreach ($members as $itm)
											<option value="{{ $itm->memberid }}" {{ intval(old('memberid'))===$itm->memberid ? 'selected' : '' }}>{{ $itm->name }} [{{ strtoupper($itm->mobilenumber) }}]</option>
											@endforeach
										</select>					
										<span class="text-danger">@error('memberid') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }} </i> @enderror</span>
									</td>
									<td class="padding-5">
									@permission('upload.committee')
									<button type="button" class="btn btn-info myfrmbtn submitBtn" tabindex="{{$t++}}">
										<i class="fa fa-plus"></i> ADD MEMBER
									</button>
									@endpermission
									</td>
								</tr>
							</table>
							<div class="table-responsive mt-5">
							<table class="mytable" border="1" style="text-transform:none!important">
								<tr class="myheadbg">
									<td class="center padding-10" style="width:35px;">S.No.</td>
									<td class="padding-10">Name</td>
									<td class="padding-10">Designation</td>
									<td class="padding-10">Department</td>
									<td class="padding-10">Mobile Number</td>
									<td class="padding-10">Email</td>
									<td class="padding-10">From</td>
								</tr>
								<tbody class="committeedata">
								@foreach($committee as $com)
								<tr>
									<td class="center padding-10">
									{{$loop->iteration}}
									</td>
									<td class="padding-10">
									{{$com->name}}
									</td>
									<td class="padding-10">
										{{$com->designation}}
									</td>
									<td class="padding-10">
										{{$com->department}}
									</td>
									<td class="padding-10">
										{{$com->mobilenumber}}
									</td>
									<td class="padding-10">
										{{$com->email}}
									</td>
									<td class="padding-10">
										{{$com->usertype}}
									</td>
								</tr>
								@endforeach
								@if($committee->count()==0)
								<tr><td class="center padding-10" colspan="7">--No Record Available--</td></tr>
								@endif
								@if($committee->count()>4)
								<tr>
									<td class="padding-10" colspan="7" style="text-align:right;">
										<a href="{{ route('view.pptresumes',Crypt::encrypt($data->requestid))}}">									
											<button type="button" class="btn btn-info myfrmbtn" style="width:200px;" tabindex="{{$t++}}">
												<i class="fa fa-users"></i> View Participations
											</button>
										</a>
									</td>
								</tr>
								@endif
								</tbody>
							</table>
							</div>
						</div>
					</div>
				
<div class="col-sm-12">&nbsp;</div>
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
<script src="{{ asset('panel/assets/js/ace-elements.min.js') }}"></script>
<script src="{{ asset('panel/assets/js/ace.min.js') }}"></script>


<script>

$('.submitBtn').on('click', function () {
	bootbox.confirm('Do you want to add committe member?',function(result){
		if(result)
		{
			$("#submitBtn").prop("disabled","disabled");
			let form = $('#committeefrm')[0];
			let formData = new FormData(form);

			$.ajax({
				url: $('#committeefrm').attr('action'),
				type: 'POST',
				data: formData,
				contentType: false,
				processData: false,
				headers: {
					'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') // CSRF token
				},
				success: function (response) {
					if(response.status==200)
					{
						bootbox.alert(response.message);
						$('#memberid').val('').trigger("chosen:updated");
						setTimeout(function(){ window.location.reload();  },2000);
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
						$("#submitBtn").prop("disabled","");
						bootbox.alert(allMessages);
					}			
				}
			});
		}
	});
});

</script>


<script src="{{ asset('panel/assets/js/datatablePaginationWithFilter.js') }}"></script>
@endsection