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
<div class="tabbable" style="padding:0px 0px!important;">
		<ul class="nav nav-tabs padding-0">
		@if(in_array(1,Session::get('actions')))
			<li class="active">
				<a data-toggle="tab" href="#home">
					<i class="green ace-icon fa fa-plus-circle bigger-120" style="vertical-align:bottom;"></i> SEND NOTIFICATION
				</a>
			</li>
		@endif
		</ul>

	
		<div class="tab-content no-border" style="border:1px solid #ddd; min-height:100px; padding:0px 10px;">
@if(in_array(1,Session::get('actions')))
<div id="home" class="tab-pane in active" style="padding:0px 10px!important;">
	<div class="row">
		<div class="row">
			<form name="frm" id="frm" action="{{ route('update.notification',$data->id)}}" method="post" enctype="multipart/form-data">		
				@if(Session::has('success'))
				<div class="col-sm-12">
					<div class="alert alert-block alert-success">
				    	<button type="button" class="close" data-dismiss="alert">
							<i class="ace-icon fa fa-times"></i>
						</button>
						{{ Session::get('success') }}!						
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
					<div class="col-sm-2">
						Send To<label id="req">*</label>
						<input type="hidden" name="form_token" id="form_token" value="{{$token}}">
						<select class="chosen-select form-control" name="sendto" id="sendto" required onKeyPress="return OnKeyPress(this, event)"  data-placeholder="SEND TO" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}" autofocus>
							<option value=""></option>
							<option value="ALLCUSTOMER" @if(old('sendto',$data->sendto)=='ALLCUSTOMER') selected @endif>ALL CUSTOMER</option>
							<option value="CUSTOMERWITHORDER" @if(old('sendto',$data->sendto)=='CUSTOMERWITHORDER') selected @endif>CUSTOMER WITH ORDERS</option>
							<option value="VENDOR" @if(old('sendto',$data->sendto)=='VENDOR') selected @endif>VENDOR</option>							
						</select>

						<span class="text-danger">@error('branchid') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }} </i> @enderror</span>
					</div>
					<div class="col-sm-2">
						Notification Type<label id="req">*</label>
						<select class="chosen-select form-control" name="notificationtype" id="notificationtype" required onKeyPress="return OnKeyPress(this, event)"  data-placeholder="TYPE" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}" autofocus>
							<option value=""></option>
							<option value="INAPP" @if(old('notificationtype',$data->notificationtype)=='INAPP') selected @endif>IN APP</option>
						</select>

						<span class="text-danger">@error('notificationtype') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }} </i> @enderror</span>
					</div>
					<div class="col-sm-3">
						{{__('labels.branchname')}}<label id="req">*</label>
						<select class="chosen-select form-control" name="branchid" id="branchid" required onKeyPress="return OnKeyPress(this, event)" data-placeholder="{{__('labels.branchname')}}" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}">
							<option value=""></option>
							@foreach ($branch as $itm)
							<option value="{{ $itm->branchid }}" {{ intval(old('branchid',$data->branchid))===$itm->branchid ? 'selected' : '' }}>{{ strtoupper($itm->branchname)}}</option>
							@endforeach
							
						</select>

						<span class="text-danger">@error('branchid') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }} </i> @enderror</span>
					</div>
					<div class="col-sm-3">
						SEND / SCHEDULE NOTIFICATION<label id="req">*</label>
						<select class="chosen-select form-control" name="schedule" id="schedule" required onKeyPress="return OnKeyPress(this, event)"  data-placeholder="SCHEDULE NOTIFICATION" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}">
							<option value=""></option>
							<option value="ONETIME" @if(old('schedule',$data->schedule)=='ONETIME') selected @endif>ONE TIME</option>
							<option value="EVERYDAY" @if(old('schedule',$data->schedule)=='EVERYDAY') selected @endif>EVERY DAY</option>
							<option value="MONDAY" @if(old('schedule',$data->schedule)=='MONDAY') selected @endif>EVERY MONDAY</option>
							<option value="TUESDAY" @if(old('schedule',$data->schedule)=='TUESDAY') selected @endif>EVERY TUESDAY</option>
							<option value="WEDNESDAY" @if(old('schedule',$data->schedule)=='WEDNESDAY') selected @endif>EVERY WEDNESDAY</option>
							<option value="THURSDAY" @if(old('schedule',$data->schedule)=='THURSDAY') selected @endif>EVERY THURSDAY</option>
							<option value="FRIDAY" @if(old('schedule',$data->schedule)=='FRIDAY') selected @endif>EVERY FRIDAY</option>
							<option value="SATURDAY" @if(old('schedule',$data->schedule)=='SATURDAY') selected @endif>EVERY SATURDAY</option>
							<option value="SUNDAY" @if(old('schedule',$data->schedule)=='SUNDAY') selected @endif>EVERY SUNDAY</option>
							
						
						</select>

						<span class="text-danger">@error('schedule') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }} </i> @enderror</span>
					</div>
					<div class="col-sm-2">
						Notification Date<label id="req" class="onetime">&nbsp;</label>
						<input type="date" class="form-control" name="notificationdate" id="notificationdate" value="{{ old('notificationdate',$data->notificationdate) }}" autocomplete="off" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}"/>

						<span class="text-danger">@error('notificationdate') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
					</div>
					<div class="col-sm-12">&nbsp;</div>					
					<div class="col-sm-2">
						Start Date<label id="req" class="other">&nbsp;</label>
						<input type="date" class="form-control" name="startdate" id="startdate" value="{{ old('startdate',$data->startdate) }}" autocomplete="off" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}"/>

						<span class="text-danger">@error('startdate') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
					</div>
					<div class="col-sm-2">
						End Date<label id="req">&nbsp;</label>
						<input type="date" class="form-control" name="enddate" id="enddate" value="{{ old('enddate',$data->enddate) }}" autocomplete="off" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}"/>

						<span class="text-danger">@error('enddate') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
					</div>
					<div class="col-sm-2">
						Time <label id="req">*</label>
						<input type="time" class="form-control" name="notificationtime" id="notificationtime" value="{{ old('notificationtime',$data->notificationtime) }}" autocomplete="off" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}"/>

						<span class="text-danger">@error('notificationtime') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
					</div>
					
					<div class="col-sm-6">
						Notification Title <label id="req">*</label>
						<input type="text" class="form-control" name="notificationtitle" id="notificationtitle" value="{{old('notificationtitle',$data->notificationtitle)}}" placeholder="NOTIFICATION TITLE" autocomplete="off" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}">
						
						<span class="text-danger">@error('notificationtitle') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
					</div>
					<div class="col-sm-12">&nbsp;</div>
					<div class="col-sm-12">
						Message <label id="req">*</label>
						<input type="text" class="form-control" name="message" id="message" value="{{old('message',$data->message)}}" placeholder="MESSAGE" required autocomplete="off" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}">
						
						<span class="text-danger">@error('message') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
					</div>
					
					<div class="col-sm-12">&nbsp;</div>
					<div class="col-sm-12 donotshow">
@php
$cids	=	explode(',',$data->categoryids);
$oldItems = old('catids',$cids, []);
@endphp					
						<input type="checkbox" id="select-all" style="vertical-align:text-top;"> <b>SELECT ALL SUB CATEGORY</b> <i>(To notify all vendors/customers, leave the sub-category unselected.)</i><br>
						<input type="text" id="categorySearch" class="form-control" placeholder="Search sub category..." style="margin: 5px 0;">
						<div style="width:100%; max-height:200px; overflow:auto;" id="categoryList">
							@foreach($category as $itm)
							<div class="category-item">
							<input type="checkbox" class="catids" name="catids[]" value="{{ $itm->categoryid }}" style="vertical-align:text-top;" {{ in_array($itm->categoryid, $oldItems) ? 'checked' : '' }}> {{$itm->category}}
							</div>							
							@endforeach
						</div>
					</div>
					<div class="col-sm-12">&nbsp;</div>
					<div class="col-sm-10">&nbsp;</div>
					<div class="col-sm-2" align="left" style="vertical-align:top!important;">
						<button type="button" class="btn btn-info myfrmbtn" onclick="Validate()" tabindex="{{$t++}}">{{__('common.submit')}}</button>
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


						<div class="hr hr32 hr-dotted"></div>
				</div><!-- /.col -->
			</div><!-- /.row -->
		</div><!-- /.page-content -->
	
		
	</div>
</div>




	
	</div>
</div>
<script src="{{ asset('panel/assets/js/jquery-2.1.4.min.js') }}"></script>
<script src="{{ asset('panel/assets/js/ace-elements.min.js') }}"></script>
<script src="{{ asset('panel/assets/js/ace.min.js') }}"></script>

<script>
$('#select-all').on('change', function() {
    $('.catids').prop('checked', this.checked);

	var selectedCategories = [];

	$('.catids:checked').each(function () {
		selectedCategories.push($(this).val());
	});
});

$(document).on('change', '.catids', function() {

    // If not all items are checked, uncheck "Select All"
    if ($('.catids').length !== $('.catids:checked').length) {
        $('#select-all').prop('checked', false);
    } else {
        $('#select-all').prop('checked', true);
    }

	var selectedCategories = [];

	$('.catids:checked').each(function () {
		selectedCategories.push($(this).val());
	});

});

document.getElementById('categorySearch').addEventListener('input', function () {
    let filter = this.value.toLowerCase();
    let items = document.querySelectorAll('#categoryList .category-item');

    items.forEach(function (item) {
        let text = item.textContent.toLowerCase();
        item.style.display = text.includes(filter) ? '' : 'none';
    });
});

$(document).ready(function() {

    $('#sendto').on('change', function() {
        if ($(this).val() === 'ALLCUSTOMER') {
            $('.donotshow').hide();
        } else {
            $('.donotshow').show();
        }
    }).trigger('change'); // Trigger once on load

    $('#schedule').on('change', function() {
        if ($(this).val()== 'ONETIME') {
            $('.onetime').html('*');
			$('.other').html('&nbsp;');
        } else if ($(this).val()!= 'ONETIME') {
            $('.other').html('*');
			$('.onetime').html('&nbsp;');
        }
		else
		{
            $('.other').html('&nbsp;');
			$('.onetime').html('&nbsp;');			
		}
    }); // Trigger once on load


    window.Validate = function () {
        var isvalid = true;
        var errorMsg = "";

        // Helper to show error
        function showError(message) {
            bootbox.alert(message);
            isvalid = false;
        }

        // Get values
        var sendto = $('#sendto').val();
        var notificationtype = $('#notificationtype').val();
        var branchid = $('#branchid').val();
        var schedule = $('#schedule').val();
        var notificationdate = $('#notificationdate').val();
		var startdate = $('#startdate').val();
		var enddate = $('#enddate').val();
        var notificationtime = $('#notificationtime').val();
        var notificationtitle = $('#notificationtitle').val().trim();
        var message = $('#message').val().trim();
        var categorychecked = $('.catids:checked').length;

        // Validate required fields
        if (!sendto) return showError("Please select Send To");
        if (!notificationtype) return showError("Please select Notification Type");
        if (!branchid) return showError("Please select Branch");
        if (!schedule) return showError("Please select Schedule");
        if (!notificationtitle) return showError("Notification title is required");
        if (!message) return showError("Message is required");

        // If schedule is ONETIME, date is required
        if (schedule === 'ONETIME') {
			
            if (!notificationdate) return showError("Notification date is required for ONETIME schedule");
        }
        if (schedule!='ONETIME') {
            if (!startdate) return showError("Start date is required for your schedule selection");
        }

        // Validate time is at least 5 mins ahead

        if (isvalid) {
			$(".myfrmbtn").prop("disabled","disabled");
			bootbox.confirm('Please confirm your submission. You will not be able to make changes afterward.',function(result){
				if(result)
				{
					$('#frm').submit();
				}
				else
				{
					$(".myfrmbtn").prop("disabled","");
					
				}
			});
            
			
        }
    };
});
</script>

<script src="{{ asset('panel/assets/js/master.js') }}"></script>
<script src="{{ asset('panel/assets/js/datatablePaginationWithFilter.js') }}"></script>
@endsection