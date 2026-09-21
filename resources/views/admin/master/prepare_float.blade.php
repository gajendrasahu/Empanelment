@extends('admin.admin_master')
@section('admin')
@php
$t=1;
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
					<i class="green ace-icon fa fa-mail-forward bigger-120 datalist" style="vertical-align:text-top;"></i> Float EoI
				</a>
			</li>		
		@endif
		</ul>

	
		<div class="tab-content no-border" style="border:1px solid #ddd; min-height:100px; padding:0px 10px;">
@if(in_array(2,Session::get('actions')) || in_array(3,Session::get('actions')) || in_array(12,Session::get('actions')))
<div id="recordlist" class="tab-pane in active" style="padding:0px 0px!important;">
	<div class="row" style="padding:0px;">
		<div class="col-xs-12" style="padding:0px 2px;">
		@permission('float.eoi')
		<form name="pagedata" id="pagedata" action="{{route('float.eoi')}}" method="post" enctype="multipart/form-data">
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
				@if($errors->any())
				<br>
				<div class="col-sm-12">
				    <div class="alert alert-block alert-danger">
				    	<button type="button" class="close" data-dismiss="alert">
							<i class="ace-icon fa fa-times"></i>
						</button>
						<ul>
							@foreach($errors->all() as $error)
								<i class="fa fa-warning"></i> {{ $error }}<br>
							@endforeach
						</ul>
				    </div>
				</div>
				@endif				
				
				@csrf				
				<span class="text-danger">
					@error('vendorids')
						<div class="alert alert-block alert-danger">
							<button type="button" class="close" data-dismiss="alert">
								<i class="ace-icon fa fa-times"></i>
							</button>
							<i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i>
						</div>
					@enderror
				</span>
				<div class="form-group padding-8">
					<div class="content-detail">
						<div class="ui-card-detail">
							<div>
								<div class="title-detail">
									<i class="material-icons-outlined">topic</i> {{ $data->projecttitle }}
								</div>
								<div class="details-detail lh-30">
									<b class="form-label" style="color:black!important;">Department / Project Manager : </b>{{ ucwords(strtolower($data->departmentname)) }}<br>
									EoI Number: <b>{{ $data->eoinumber }}</b> | Requested On : <b>{{ date('d\-m\-Y, h:i A',strtotime($data->creationdate)) }}</b> | Project Duration : <b>{{ ucwords(strtolower($data->projectduration)) }} Months</b><br>
								</div>
							</div>
						</div>

						<div class="section-block-detail">
							<div class="section-header-detail">
								<span class="material-icons">compare</span> <!-- Hamburger menu icon -->
								&nbsp;<h2 class="section-title-detail">Table of Contents (Optional)</h2>
							</div>
							<table style="width:100%; border:none;">
								<tr>
									<td style="border:none;">
							@php
								$page_indexing = "";
								$page_indexing = old('page_indexing') ?: ($data->page_indexing ?: $page_indexing);
							@endphp
							<textarea class="form-control tinymce" name="page_indexing" required id="page_indexing" placeholder="Page indexing" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}">{!! $page_indexing !!}</textarea>
							<span class="text-danger">@error('page_indexing') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
									</td>
								</tr>
							</table>							
						</div>

						<div class="section-block-detail">
							<!--
							<div class="section-header-detail">
								<span class="material-icons">compare</span>
								&nbsp;<h2 class="section-title-detail">Table of Contents (Optional)</h2>
							</div>
							-->
							<table style="width:100%; border:none;">
								<tr><td><i class="fa fa-info-circle"></i> Please do not add the Expression of Interest (EOI) reference number or date. These will be added automatically.</td></tr>
							
								<tr>
									<td style="border:none;">
							@php
								if($data->releasedate)
								{
									$releasedate	=	date('d\-m\-Y',strtotime($data->releasedate));
								}
								else
								{
									$releasedate	=	'dd-mm-YYYY';
								}
								
								$chips_objective = old('chips_objective',$data->chips_objective);
							@endphp
									
							<textarea class="form-control tinymce" name="chips_objective" required id="chips_objective" placeholder="" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}">{!! $chips_objective !!}</textarea>
							<span class="text-danger">@error('chips_objective') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
									</td>
								</tr>
							</table>							
						</div>

						
						<div class="section-block-detail">
							@php $r=0; @endphp
							@if($data->tier_choice==1)
							<input type="radio" name="tierid" id="tierid" value="1" class="rdo t1" style="line-height:30px; vertical-align:base; width: 14px; height:14px; margin-left:10px;" onclick="loadData('{{ route('floatingvendor.html') }}',{{$data->requestid}})" checked> <b>Tier I</b>
							@elseif($data->tier_choice==2)
								@if($grandtotal<=50000000)
									<input type="radio" name="tierid" id="tierid" value="2" class="rdo t2" style="line-height:30px; vertical-align:base; width: 14px; height:14px; margin-left:10px;" onclick="loadData('{{ route('floatingvendor.html') }}',{{$data->requestid}})" checked> <b>Tier II</b>
								@else
									Order value is more than 5 Cr.
								@endif
							@elseif($data->tier_choice==3)								
								<input type="radio" name="tierid" id="tierid" value="3" class="rdo t0" style="line-height:30px; vertical-align:base; width: 14px; height:14px; margin-left:10px;" onclick="loadData('{{ route('floatingvendor.html') }}',{{$data->requestid}})" checked> <b>BOTH</b>
							@endif
							
							<input type="hidden" name="emailids" id="emailids" style="width:100%;">
							

							<span class="label label-white middle animated flipInX delay-02 f-s-10 form-label span-content show-eoicontent" style="float:right;" data-url="{{ route('show.eoicontent',Crypt::encrypt($data->requestid)) }}">
								<i class="fa fa-hand-o-right"></i> View EoI
							</span>
							<br><br>
							<table class="table table-bordered">
							
							
							<tr>
								<td class="padding-10" style="width:25px;" nowrap><b>S.No.</b></td>
								<td class="padding-10" nowrap style=""><b>Firm</b></td>
								<td class="padding-10" nowrap style=""><b>Emails</b></td>
								<td class="padding-10" nowrap style=""><b>Sectors</b></td>
								<td class="padding-10" nowrap style=""><b>Tier</b></td>
							</tr>
							
							<tbody class="tabledata">
								<tr><td class="center" colspan="6">--Search Record--</td></tr>
							</tbody>
							</table>
								
						</div>


						<div class="section-block-detail">
							<div class="section-header-detail">
								<span class="material-icons">compare</span> <!-- Hamburger menu icon -->
								&nbsp;<h2 class="section-title-detail">Payment & Penalty Terms</h2>
							</div>
							<table style="width:100%; border:none;">
								<tr>
									<td style="border:none;">
							@php
								$termsandcondition = "";
								$termsandcondition = old('termsandcondition',$data->termsandcondition);
							@endphp
							<textarea class="form-control tinymce" name="termsandcondition" required id="termsandcondition" placeholder="Payment & Penalty Terms" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}">{!! $termsandcondition !!}</textarea>
							<span class="text-danger">@error('termsandcondition') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
									</td>
								</tr>
							</table>							
						</div>

						<div class="section-block-detail">
							<div class="section-header-detail">
								<span class="material-icons">compare</span> <!-- Hamburger menu icon -->
								&nbsp;<h2 class="section-title-detail">Critical Information</h2>
							</div>
							<table style="width:100%; border:none;">
								<tr>
									<td style="border:none;">
							@php
								$criticalinformation = "";
								$criticalinformation = old('criticalinformation') ?: ($data->criticalinformation ?: $criticalinformation);
							@endphp
							<textarea class="form-control tinymce" name="criticalinformation" required id="criticalinformation" placeholder="Critical Information" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}">{!! $criticalinformation !!}</textarea>
							<span class="text-danger">@error('criticalinformation') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
									</td>
								</tr>
							</table>							
						</div>


						
						<div class="section-block-detail">
							<div class="grid-layout-detail cols-2">
								<table style="width:500px;">
								<tr>
								<td class="padding-5">
								<div class="ui-item-detail">
									<label>Release Date*</label>
									<div class="input-group" style="width:200px;">
									@if(old('releasedate')!='' && old('releasedate')!='01-01-1970')
									<input type="text" class="date-picker" data-date-format="dd-mm-yyyy" name="releasedate" id="releasedate" required value="{{ date('d-m-Y',strtotime(old('releasedate'))) }}" placeholder="dd-mm-YYYY" style="width:200px;">
									@else
									<input type="text" class="date-picker" data-date-format="dd-mm-yyyy" name="releasedate" id="releasedate" required value="@if($data->releasedate){{ date('d-m-Y',strtotime($data->releasedate)) }}@endif" placeholder="dd-mm-YYYY" style="width:200px;">
									@endif
									<span class="input-group-addon"><i class="fa fa-calendar bigger-110"></i></span>
									</div>
								</div>
								</td>

								<td class="padding-5">
								<div class="ui-item-detail">
									<label>Last Date of Pre-bid Query*</label>
									<div class="input-group" style="width:200px;">
									@if(old('prebidlastdate')!='' && old('prebidlastdate')!='01-01-1970')
									<input type="text" class="date-picker" data-date-format="dd-mm-yyyy" name="prebidlastdate" id="prebidlastdate" required value="{{ date('d-m-Y',strtotime(old('prebidlastdate'))) }}" placeholder="dd-mm-YYYY" style="width:200px;">
									@else
									<input type="text" class="date-picker" data-date-format="dd-mm-yyyy" name="prebidlastdate" id="prebidlastdate" required value="@if($data->prebidlastdate){{ date('d-m-Y',strtotime($data->prebidlastdate)) }}@endif" placeholder="dd-mm-YYYY" style="width:200px;">
									@endif
									<span class="input-group-addon"><i class="fa fa-calendar bigger-110"></i></span>
									</div>
								</div>
								</td>
								
								<td class="padding-5">
								<div class="ui-item-detail">
									<label>Submission Date*</label>
									<div class="input-group" style="width:200px;">
									@if(old('deadlinedate')!='' && old('deadlinedate')!='01-01-1970')
									<input type="text" class="date-picker" data-date-format="dd-mm-yyyy" name="deadlinedate" id="deadlinedate" required value="{{ date('d-m-Y',strtotime(old('deadlinedate'))) }}" placeholder="dd-mm-YYYY" style="width:200px;">
									@else
									<input type="text" class="date-picker" data-date-format="dd-mm-yyyy" name="deadlinedate" id="deadlinedate" required value="@if($data->deadlinedate){{ date('d-m-Y h:i A',strtotime($data->deadlinedate)) }}@endif" placeholder="dd-mm-YYYY" style="width:200px;">
									@endif
									<span class="input-group-addon"><i class="fa fa-calendar bigger-110"></i></span>
									</div>
								</div>
								</td>
								
								<td class="padding-5">
								<div class="ui-item-detail">
									<label>Tentative Interview Date </label>
									<div class="input-group" style="width:200px;">
									@if(old('interviewdate')!='' && old('interviewdate')!='01-01-1970')
									<input type="text" class="date-picker" data-date-format="dd-mm-yyyy" name="interviewdate" id="interviewdate" required value="{{ date('d-m-Y h:i A',strtotime(old('interviewdate'))) }}" placeholder="dd-mm-YYYY" style="width:200px;">
									@else
									<input type="text" class="date-picker interview_date" data-date-format="dd-mm-yyyy" name="interviewdate" id="interviewdate" required value="@if($data->interviewdate){{ date('d-m-Y h:i A',strtotime($data->interviewdate)) }}@endif" placeholder="dd-mm-YYYY" style="width:200px;">
									@endif
									<span class="input-group-addon"><i class="fa fa-calendar bigger-110"></i></span>
									</div>
								</div>
								</td>
								</tr>
								<tr>
									<td class="padding-5">
									<label>Place of presentations & Interviews </label>
									<div class="input-group" style="width:100%;">
									@if(old('interview_place')!='' && old('interview_place')!='01-01-1970')
									<input type="text" name="interview_place" id="interview_place" required value="{{ old('interview_place') }}"  style="width:100%;" placeholder="Place of interview">
									@else
									<input type="text" name="interview_place" id="interview_place" required value="@if($data->interview_place){{ $data->interview_place }}@endif" placeholder="Place of interview" style="width:100%;">
									@endif
									</div>
									</td>

									<td class="padding-5">
									<label>Method of Selection </label>
									<div class="input-group" style="width:100%;">
									@if(old('selection_method')!='' && old('selection_method')!='01-01-1970')
									<input type="text" name="selection_method" id="selection_method" required value="{{ old('selection_method') }}"  style="width:100%;" placeholder="Method of Selection">
									@else
									<input type="text" name="selection_method" id="selection_method" required value="@if($data->selection_method){{ $data->selection_method }}@endif" placeholder="Method of Selection" style="width:100%;">
									@endif
									</div>
									</td>
								</tr>
								<tr><td colspan="4">&nbsp;</td></tr>
								<tr>
									<td class="padding-2">
										<label>EoI File* </label>
										<input type="file" class="attachment" name="eoi_file" id="eoi_file" required style="width:100%;">
									</td>
									<td class="padding-2">
										<label>Notesheet File </label>
										<input type="file" class="attachment" name="notesheet_file" id="notesheet_file" required style="width:100%;">
									</td>
									<td colspan="2"></td>
								</tr>
								
								<tr>
								<td class="padding-5" colspan="4">
<button type="button" style="text-transform:none!important; width:175px!important; margin-top:30px; float:left;" class="btn btn-info show-factsheet myfrmbtn" data-url="{{ route('show.factsheet',Crypt::encrypt($data->requestid)) }}">
	<i class="fa fa-hand-o-right"></i> Fact Sheet Preview
</button>
								
<button type="button" style="text-transform:none!important; width:120px!important; margin-left:5px; margin-top:30px; float:left;" class="btn btn-info show-eoipreview myfrmbtn" data-url="{{ route('show.previeweoi',Crypt::encrypt($data->requestid)) }}">
	<i class="fa fa-hand-o-right"></i> EoI Preview
</button>


<button type="button" style="text-transform:none!important; width:150px!important; float:left; margin-top:30px; margin-left:5px;" class="btn btn-info download-eoi-doc myfrmbtn" data-url="{{ route('show.previeweoi',Crypt::encrypt($data->requestid)) }}">
	<i class="fa fa-download"></i> Download EoI
</button>
								
								@if($data->isupdated==0)
								@permission('float.eoi')
								<button type="button" style="text-transform:none!important; width:175px!important; margin-top:30px; float:right;" class="btn btn-info myfrmbtn publishBtn" onclick="PublishEoi()">
									<i class="fa fa-hand-o-right"></i> Publish to Firms
								</button>
								@endpermission
								@else
									<span class="label label-white middle animated flipInX delay-02 f-s-10 form-label span-content" style="float:right; margin-top:30px;">
										<i class="fa fa-warning"></i> The department has made an update. Please mark it as read before proceeding.
									</span>
								@endif
								</td>
								</tr>
								</table>
							</div>
							
						</div>
						
						
					</div>
				</div>
		</form>
		@endpermission
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

<div class="modal fade" id="eoiModal">
  <div class="modal-dialog modal-xl" style="width:8.27in;">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" style="float:left;">EoI Detail</h5>
        <!-- Proper close button for BS3 -->
        <button type="button" class="close" style="float:right;" data-dismiss="modal">&times;</button>
      </div>
      <div class="modal-body" id="eoiContent">
        Loading...
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
<script src="{{ asset('panel/assets/js/jquery-2.1.4.min.js') }}"></script>
<script src="{{ asset('panel/assets/js/ace-elements.min.js') }}"></script>
<script src="{{ asset('panel/assets/js/ace.min.js') }}"></script>
<script src="{{ asset('panel/assets/tinymce/tinymce.min.js') }}"></script>
<script>
setTimeout(function() { loadData('{{ route('floatingvendor.html') }}',{{$data->requestid}}); },1000);

tinymce.init({
    selector: 'textarea.tinymce',
    promotion: false,
    branding: false,

    min_height:100,
    autoresize_min_height:100,
    autoresize_max_height:700,
    autoresize_bottom_margin:10,

    toolbar_mode: 'floating',
    plugins: 'autoresize code lists charmap preview table',
    toolbar: 'undo redo | formatselect | bold underline | alignleft aligncenter alignright alignjustify | bullist numlist',

    menubar: false,
    statusbar: false,
    resize: true,

    content_style: `
        body {
            font-family: "Times New Roman", serif;
            font-size: 14px;
            line-height: 1.5;
        }
    `,

    setup: function(editor) {
        editor.on('change keyup', function() {
            editor.save();
        });
    }
});

function loadData(r1,requestid)
{
	var tierid			=	$('input[name="tierid"]:checked').val();
	$.get(""+r1,
	{
		tierid:tierid,
		requestid:requestid
	},
	function(data, status){
		$(".tabledata").html(data);
		$('.allchk').prop('checked',false);
		$('.chk').prop('checked',false);		
		setTimeout(function() { bindAllCheckboxLogic(); },2000);
	});
}

function updateValsField() {
  var values = $('.chk:checked').map(function() {
    return this.value;
  }).get().join(',');
  
  $('#vendorids').val(values);
  
  setTimeout(function() { updateEmails(); },1000);
}

function updateEmails() {
 
  var emails = $('.allmail:checked').map(function() {
    return this.value;
  }).get().join(',');
	$('#emailids').val(emails);
}

$(document).ready(function() {
  $('#allchk').on('click', function() {
    $('.chk').prop('checked', this.checked);
	$('.allmail').prop('checked', this.checked);
    updateValsField();
  });

  $(document).on('click', '.chk', function() {
    updateValsField();
    var allChecked = $('.chk').length === $('.chk:checked').length;
    $('#allchk').prop('checked', allChecked);
  });
});

</script>
<script>
function bindForemailCheckboxes() {
    document.querySelectorAll('.foremail').forEach(function (checkbox) {
        checkbox.addEventListener('change', function () {
            const id = this.getAttribute('data-id');
            const checked = this.checked;
            const related = document.querySelectorAll('.vendor_emails'+id);
            related.forEach(cb => {
                cb.checked = checked;
            });
        });
    });
	
}

function bindSelectAllCheckbox() {
    const selectAll = document.getElementById('selectAllEmails');

    if (!selectAll) return;

    selectAll.addEventListener('change', function () {
        const allChecked = this.checked;

        // Get all .foremail checkboxes
        document.querySelectorAll('.foremail').forEach(function (checkbox) {
            checkbox.checked = allChecked;

            const id = checkbox.getAttribute('data-id');
            const related = document.querySelectorAll('.vendor_emails'+id);
            related.forEach(cb => {
                cb.checked = allChecked;
            });
        });
    });
	
}

function bindAllCheckboxLogic() {
    bindForemailCheckboxes();
    //bindSelectAllCheckbox();
	updateEmails();
}


$('.show-eoicontent').on('click', function () {
    var docUrl = $(this).data('url');

    $(".modal-xl").css("width","90%");
    $(".modal-title").html("EoI in Detail");
    $('#eoiContent').html('Loading...');
    $('#eoiModal').modal('show');

    $.get(docUrl,function (data) {
        setTimeout(function() { $('#eoiContent').html(data.formhtml); },2000);
    }).fail(function () {
        $('#eoiContent').html('<p class="text-danger">Failed to load EoI.</p>');
    });
});


$('.show-factsheet').on('click', function () {
	var releasedate = $('input[name="releasedate"]').val();
	var prebidlastdate = $('input[name="prebidlastdate"]').val();
	var deadlinedate = $('input[name="deadlinedate"]').val();
	
	if(releasedate=='' || prebidlastdate=='' || deadlinedate=='')
	{
		bootbox.alert('Please enter the Release Date, Pre-Bid Last Date, and Submission Last Date.');
		return false;
	}
	
    var docUrl = $(this).data('url');

    var params = {
        releasedate: $('input[name="releasedate"]').val(),
        prebidlastdate: $('input[name="prebidlastdate"]').val(),
        deadlinedate: $('input[name="deadlinedate"]').val(),
		interviewdate: $('input[name="interviewdate"]').val()
    };	
    $(".modal-xl").css("width","90%");
    $(".modal-title").html("Fact Sheet Preview");
    $('#eoiContent').html('Loading...');
    $('#eoiModal').modal('show');

    $.get(docUrl,params, function (data) {
        setTimeout(function() { $('#eoiContent').html(data.formhtml); },2000);
    }).fail(function () {
        $('#eoiContent').html('<p class="text-danger">Failed to load EoI.</p>');
    });
});

$('.show-eoipreview').on('click', function () {

	var releasedate = $('input[name="releasedate"]').val();
	var prebidlastdate = $('input[name="prebidlastdate"]').val();
	var deadlinedate = $('input[name="deadlinedate"]').val();
	
	if(releasedate=='' || prebidlastdate=='' || deadlinedate=='')
	{
		bootbox.alert('Please enter the Release Date, Pre-Bid Last Date, and Submission Last Date.');
		return false;
	}

    var docUrl = $(this).data('url');
	tinymce.triggerSave();
    var params = {
        releasedate: $('input[name="releasedate"]').val(),
        prebidlastdate: $('input[name="prebidlastdate"]').val(),
        deadlinedate: $('input[name="deadlinedate"]').val(),
		interviewdate: $('input[name="interviewdate"]').val(),
		interview_place: $('input[name="interview_place"]').val(),
		selection_method: $('input[name="selection_method"]').val(),
		page_indexing: tinymce.get('page_indexing').getContent(),
		chips_objective: tinymce.get('chips_objective').getContent(),
		termsandcondition: tinymce.get('termsandcondition').getContent(),
		criticalinformation: tinymce.get('criticalinformation').getContent(),
		isPreview:1,
    };
	
    $(".modal-xl").css("width","9in");
    $(".modal-title").html("EoI Preview");
    $('#eoiContent').html('Loading...');
    $('#eoiModal').modal('show');

    $.get(docUrl,params, function (data) {
        setTimeout(function() { $('#eoiContent').html(data.formhtml); },2000);
    }).fail(function () {
        $('#eoiContent').html('<p class="text-danger">Failed to load EoI.</p>');
    });
});

// Clear content when modal fully hidden
$('#eoiModal').on('hidden.bs.modal', function () {
    $('#eoiContent').html('');
});


jQuery(function($) {	

	$('#eoi_file').ace_file_input({
		no_file:'No File ...',
		btn_choose:'Eoi File*',
		btn_change:'Change',
		btn_name:'fourthimage',
		thumbnail:false //| true | large
	});

	$('#notesheet_file').ace_file_input({
		no_file:'No File ...',
		btn_choose:'Notesheet File',
		btn_change:'Change',
		btn_name:'fourthimage',
		thumbnail:false //| true | large
	});

});

</script>
<script>
function PublishEoi()
{
	bootbox.confirm('<b>Are you sure you want to publish this EoI?</b><br>Once published, the EoI will be visible to vendors and cannot be reverted.',function(result){
		if(result)
		{
			$(".publishBtn").prop("disabled","disabled");
			const form = document.forms['pagedata'];
			form.submit(); 
		}
	});
}


$('.download-eoi-doc').on('click', function () {
	var releasedate = $('input[name="releasedate"]').val();
	var prebidlastdate = $('input[name="prebidlastdate"]').val();
	var deadlinedate = $('input[name="deadlinedate"]').val();

	if(releasedate=='' || prebidlastdate=='' || deadlinedate=='')
	{
		bootbox.alert('Please enter the Release Date, Pre-Bid Last Date, and Submission Last Date.');
		return false;
	}

    var docUrl = $(this).data('url');

    var params = {
        releasedate: $('input[name="releasedate"]').val(),
        prebidlastdate: $('input[name="prebidlastdate"]').val(),
        deadlinedate: $('input[name="deadlinedate"]').val(),
        interviewdate: $('input[name="interviewdate"]').val(),
		interview_place: $('input[name="interview_place"]').val(),
		selection_method: $('input[name="selection_method"]').val(),

		page_indexing: tinymce.get('page_indexing').getContent(),
        chips_objective: tinymce.get('chips_objective').getContent(),
        termsandcondition: tinymce.get('termsandcondition').getContent(),
        criticalinformation: tinymce.get('criticalinformation').getContent(),
		isPreview:0,
    };

    // Create dynamic form (POST request)
    var form = $('<form method="POST" action="' + docUrl + '"></form>');

    // CSRF (Laravel)
    form.append('<input type="hidden" name="_token" value="' + $('meta[name="csrf-token"]').attr('content') + '">');

    // Append all params
    $.each(params, function (key, value) {
        form.append('<textarea name="' + key + '">' + value + '</textarea>');
    });

    $('body').append(form);
    form.submit();
});

</script>
<script src="{{ asset('panel/assets/js/master.js') }}"></script>
<script src="{{ asset('panel/assets/js/datatablePaginationWithFilter.js') }}"></script>
@endsection