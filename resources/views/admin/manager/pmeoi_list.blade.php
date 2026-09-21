@extends('admin.admin_master')
@section('admin')
@php
$t = 1;
@endphp
@php $status = is_null($eoi_status) ? null : (int)$eoi_status; @endphp
<div class="main-content">
	<div class="main-content-inner">
			<div class="page-content" style="padding:10px 15px!important;">
				<div class="ace-settings-container" id="ace-settings-container"></div>
				<div class="row">
					<div class="col-xs-12">
					<!-- PAGE CONTENT BEGINS -->
<div class="tabbable animated bounceInUp" style="padding:0px 0px!important;">
		@if(in_array(2,Session::get('actions')) || in_array(3,Session::get('actions')) || in_array(12,Session::get('actions')))
			<div class="eoi-section-header" onclick="loadData(1,'{{ route('pmeoirequest.html') }}')">
				<div style="font-size:20px; font-weight:600; color:#2c3e50;">
					EoI Draft List
				</div>
				<div style="font-size:14px; color:#6c757d; margin:2px 0px; 8px 0">
					Review draft and in-progress EoIs, keep timelines on track, and continue preparation.
				</div>
			</div>
		@endif

		<div class="tab-content no-border" style="min-height:100px; padding:0px 10px;">

@if(in_array(2,Session::get('actions')) || in_array(3,Session::get('actions')) || in_array(12,Session::get('actions')))
<div id="recordlist" class="tab-pane in active" style="padding:0px 0px!important;">
	<div class="row" style="padding:0px;">
		@if(Session::has('success'))
		<div class="col-sm-12" style="padding:10px;">
			<div class="alert alert-block alert-success" style="border-radius:0px!important;">
				<button type="button" class="close" data-dismiss="alert">
					<i class="ace-icon fa fa-times"></i>
				</button>
				<i class="fa fa-thumbs-up"></i> {{ Session::get('success') }}
			</div>
		</div>
		@endif
		@if(Session::has('fail'))
		<div class="col-sm-12" style="padding:10px;">
			<div class="alert alert-block alert-warning" style="border-radius:0px!important;">
				<button type="button" class="close" data-dismiss="alert">
					<i class="ace-icon fa fa-times"></i>
				</button>
				<i class="fa fa-warning"></i> {{ Session::get('fail') }}
			</div>
		</div>
		@endif
		@if($errors->any())
			<div class="col-sm-12 padding-10">
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
		<div class="col-xs-12">
		<form name="pagedata" id="pagedata" action="#" method="post" onsubmit="return false;">
			@csrf
			<div class="table-responsive">
				<table class="table table-bordered table-striped table-hover" id="tablerecords">
				<thead>
				<tr>
					<td class="padding-10" colspan="3">
						<div class="table-filters-row">
							<div class="filters-group">
								<select name="pagesize" id="pagesize" class="select2 selectbx" onchange="loadData(1,'{{ route('pmeoirequest.html') }}')" data-width="70" data-placeholder="RECORDS">
									<option value="50">50</option>
									<option value="100">100</option>
									<option value="300">300</option>
									<option value="500">500</option>
								</select>

								<select class="select2" name="categoryid" id="categoryid" onchange="loadData(1,'{{ route('pmeoirequest.html') }}')" data-width="250" data-placeholder="Select Category">
									<option value=""></option>
									@foreach($category as $cat)
									<option value="{{$cat->categoryid}}">{{$cat->jobcategory}}</option>
									@endforeach
								</select>

								<select name="eoi_status" id="eoi_status" class="select2" onchange="loadData(1,'{{ route('pmeoirequest.html') }}')" data-width="200" data-placeholder="All Status">
									<option value="" @if(is_null($status)) selected @endif>All Status</option>
									<option value="0" @if($status===0) selected @endif>Request</option>
									<option value="1" @if($status===1) selected @endif>Draft</option>
									<option value="2" @if($status===2) selected @endif>Approved</option>
									<option value="3" @if($status===3) selected @endif>Published</option>
									<option value="4" @if($status===4) selected @endif>Work Order Issued</option>
								</select>

								<span class="input-icon">
									<input type="text" placeholder="Search EoI and press enter" class="nav-search-input selectbx" id="pagesearch" name="pagesearch" autocomplete="off" onchange="loadData(1,'{{ route('pmeoirequest.html') }}')" tabindex="<?php echo $t++;?>" />
									<i class="ace-icon fa fa-search nav-search-icon"></i>
								</span>
							</div>
						</div>
					</td>
				</tr>
				{{-- Card-style rows only (no column headers) --}}
				</thead>
				<tbody class="tabledata">
				<tr><td colspan="3" class="center padding-8">--Search Record--</td></tr>
				</tbody>
				</table>
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

<div class="modal fade left" id="myModal" role="dialog" data-backdrop="static">
	<div class="modal-lg-dialog">
		<div class="modal-content" style="font-size:12px; width:80%; margin:0 auto; margin-top:10px; background-color:white;">
		
		</div>
	</div>
</div>

<div class="modal fade" id="committeeModal">
	<div class="modal-dialog modal-xl" style="width:95%; margin-top:0px;">
		<div class="modal-content" id="committeeContent"></div>
	</div>
</div>
<div class="modal fade" id="comparisionModal">
  <div class="modal-dialog modal-xl" style="width:95%; margin-top:0px;">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" style="float:left;">Resource Cost by Duration</h5>
        <button type="button" class="btn btn-close" style="float:right;" data-bs-dismiss="modal" onclick="ClsResource()">Close</button>
      </div>
      <div class="modal-body" id="comparisionContent">
        Loading...
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-close" data-bs-dismiss="modal" onclick="ClsResource()">Close</button>
      </div>
    </div>
  </div>
</div>

<script src="{{ asset('panel/assets/js/jquery-2.1.4.min.js') }}"></script>
<script>
$(document).ready(function () {
	loadData(1, '{{ route('pmeoirequest.html') }}');
});

function showPrice(rl)
{
	let formData = new FormData();
	formData.append('_token', '{{ csrf_token() }}');
	$.ajax({
		url: ''+rl,
		method: 'POST',
		data: formData,
		processData: false,
		contentType: false,
		success: function(response) {
			$('#comparisionModal').modal('show');
			$('#comparisionContent').html(response.formhtml);
		},
		error: function(xhr) {
			bootbox.alert('An error occurred while saving the data.');
		}
	});
}

function loadData(page,r1)
{
	var pagesize	= 	document.getElementById("pagesize").value;
	var pagesearch	= 	document.getElementById("pagesearch").value;
	var categoryid	= 	document.getElementById("categoryid").value;
	var eoi_status	= 	document.getElementById("eoi_status").value;
	$.get(""+r1,
	{
		page:page,
		pagesize:pagesize,
		pagesearch:pagesearch,
		categoryid:categoryid,
		eoi_status:eoi_status
	},
	function(data, status){
		$(".tabledata").html(data);
		$(".mytr").click(function(){
			$(".mytr").removeClass("selected-color").addClass("reset-color");
			$(this).removeClass("reset-color").addClass("selected-color");
		});
	});
}

function viewEoiRequestData(r1,requestid)
{
	$.get(""+r1,
	{
		requestid:requestid,
	},
	function(data, status){
		$("#myModal").modal("show");
		$(".modal-content").html(data);
	});
}
function Cls()
{
	$("#myModal").modal("hide");
	$(".modal-content").html("");	
}
function ClsResource()
{
	$('#comparisionModal').modal('hide');
	$('#comparisionContent').html("");
}

function ShowCommitteeMembers(requestid)
{
	$.ajax({
		url: '{{route('eoi.committeemember')}}',
		type: 'POST',
		data: {'requestid':requestid},
		headers: {
			'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
		},
		success: function (response) {
			if(response.status==200)
			{
				$('#committeeModal').modal('show');
				$('#committeeContent').html(response.html);				
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
function ClsList()
{
	$('#committeeModal').modal('hide');
	$('#committeeContent').html("");
}


$(document).on('click', '.updateBtn', function () {
    var row		=	$(this).closest('tr');
    var recordid= 	$(this).data('recordid');
	var requestid= 	$(this).data('requestid');
    var memberid= 	row.find('select[name="memberid[]"]').val();

    $.ajax({
        url: '{{ route('eoicommitteemember.update') }}',
        type: 'POST',
        data: {
            recordid: recordid,
            memberid: memberid,
			requestid:requestid
        },
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function (response) {
            if(response.status == 200) {
				bootbox.alert({
					message: response.message,
					callback: function () {
						ShowCommitteeMembers(response.requestid);
					}
				});                
            }
			else
			{
				bootbox.alert(response.message);
				return false;
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
                setTimeout(function() { $(".no-skin").css("padding-right",""); }, 5000);
            }
        }
    });

});


$(document).on('click', '.removeBtn', function () {
    var row		=	$(this).closest('tr');
    var recordid= 	$(this).data('recordid');
	var requestid= 	$(this).data('requestid');
    $.ajax({
        url: '{{ route('eoicommitteemember.remove') }}',
        type: 'POST',
        data: {
            recordid: recordid,
			requestid:requestid
        },
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function (response) {
            if(response.status == 200) {
				bootbox.alert({
					message: response.message,
					callback: function () {
						ShowCommitteeMembers(response.requestid);
					}
				});                
            }
			else
			{
				bootbox.alert(response.message);
				return false;
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
                setTimeout(function() { $(".no-skin").css("padding-right",""); }, 5000);
            }
        }
    });

});

$(document).on('click', '.addMoreBtn', function () {
    var memid	=	$("#memid").val();
	var requestid= 	$(this).data('requestid');
    $.ajax({
        url: '{{ route('eoicommitteemember.add') }}',
        type: 'POST',
        data: {
            memid: memid,
			requestid:requestid
        },
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function (response) {
            if(response.status == 200) {
				bootbox.alert({
					message: response.message,
					callback: function () {
						ShowCommitteeMembers(response.requestid);
					}
				});                
            }
			else
			{
				bootbox.alert(response.message);
				return false;
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
                setTimeout(function() { $(".no-skin").css("padding-right",""); }, 5000);
            }
        }
    });

});

</script>
<script src="{{ asset('panel/assets/js/master.js') }}"></script>
<script src="{{ asset('panel/assets/js/datatablePaginationWithFilter.js') }}"></script>
@endsection
