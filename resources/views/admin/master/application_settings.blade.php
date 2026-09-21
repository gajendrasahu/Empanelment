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
		<li class="active">
			<a data-toggle="tab" href="#home">
				Application Settings
			</a>
		</li>
		@endif
		</ul>

	
		<div class="tab-content no-border" style="border:1px solid #ddd; min-height:100px; padding:0px 10px;">
@if(in_array(1,Session::get('actions')))
<div id="home" class="tab-pane in active" style="padding:0px 10px!important;">
	<div class="row">
			<form method="POST" action="{{ route('update.settings') }}">
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
				@foreach($features as $feature)
				<div class="col-sm-12" style="display:flex; gap:5px;">
					<input type="checkbox" name="featureid[]" value="{{$feature->featureid}}" @if($feature->feature_status==1) checked @endif style="margin-top:0px;" onclick="{{route('update.settings',Crypt::encrypt($feature->featureid))}}"> {{$feature->feature}} [{{ $feature->feature_status == 1 ? 'Enabled' : 'Disabled' }}]
				</div>
				@endforeach
				<div class="col-sm-12" style="text-align:right;">
					<button type="submit" class="btn btn-info">Update</button>
				</div>

<div class="col-sm-12" style="text-align:right; margin-top:15px;">
    <button type="button" id="send_work_order_expiry_notification" class="btn btn-warning">
        Send Work Order Expiry Notification
    </button>

    <span id="notification_response" style="margin-left:10px;"></span>
</div>				
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
$(document).ready(function() {

    $('#send_work_order_expiry_notification').click(function() {

        bootbox.confirm({
            title:	'Confirm',
            message:	'Are you sure you want to send work order expiry notifications?',
            buttons: {
                confirm: {
                    label: 'Yes, Send',
                    className: 'btn-warning'
                },
                cancel: {
                    label: 'Cancel',
                    className: 'btn-default'
                }
            },
            callback: function(result) {

                if(!result)
				{
                    return;
                }

                var button = $('#send_work_order_expiry_notification');

                button.prop('disabled', true);
                button.text('Processing...');

                $.ajax({
                    url: "{{ route('send.work.order.expiry.notification') }}",
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}"
                    },
                    success: function(response) {
                        if(response.status === 'success')
						{
                            bootbox.alert({
                                title: 'Success',
                                message: response.message
                            });
                        }
						else
						{
                            bootbox.alert({
                                title: 'Error',
                                message: response.message
                            });
                        }
                    },
                    error: function(xhr) {
                        var message = 'Something went wrong while processing the request.';
                        if(xhr.responseJSON && xhr.responseJSON.message)
						{
                            message = xhr.responseJSON.message;
                        }
                        bootbox.alert({
                            title: 'Error',
                            message: message
                        });
                    },
                    complete: function() {
                        button.prop('disabled', false);
                        button.text('Send Work Order Expiry Notification');
                    }
                });

            }
        });

    });

});
</script>


<script src="{{ asset('panel/assets/js/datatablePaginationWithFilter.js') }}"></script>

@endsection