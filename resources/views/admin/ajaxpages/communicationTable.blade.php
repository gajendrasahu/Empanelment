@php
$i=1;
use Carbon\Carbon;
@endphp
<tr>
<td style="width:50%!important;">
@if($data->count()!=0)
<div style="height:450px; max-height:450px; overflow:scroll;">
<table class="pd-8" style="width:100%!important;">
@if(!in_array(session('userType'),['DEPARTMENT','PROJECT MANAGER','VENDOR']))
	<tr class="myheadbg">
		<td>
		<select class="form-control" name="filter_data" id="filter_data">
			<option value="">Select Department / Project Manager / Vendor</option>
			@foreach($combined as $com)
			<option value="{{$com->recordid}}" data-type="{{$com->usrType}}">{{$com->label}}</option>
			@endforeach
		</select>
		</td>
	</tr>
@endif
@foreach($data as $item)
	<tr data-department="{{$item->departmentid}}" data-vendor="{{$item->vids}}">
		<td class="message-item" style="border-bottom:1px solid #eee!important; width:100%!important; line-height:25px;">
			<i class="fa fa-user"></i> <b>{{$item->name}}</b><br>
			<i class="fa fa-calendar"></i> {{date('d\-m\-Y, h:i A',strtotime($item->creationdate))}}
			@if(in_array(session('userType'),['DEPARTMENT','PROJECT MANAGER','VENDOR']) && session('userId')!=$item->fromuserid)
				<label class="vendor-item" style="display:flex; align-items:center; gap:4px;">
					<input type="checkbox" class="marking" style="margin-top:0px;" @if($item->seen_flag==1) checked disabled @endif onclick="MarkAsRead('{{Crypt::encrypt($item->recordid)}}')"> Mark as read
				</label>
			@endif
			
			@if(session('userType')=='ADMIN')
				@if($item->isdepartment==0 && $item->ispm==0 && $item->isvendor==0)
				<br><span style="font-size:8px;">To : {{$item->sent_to}}</span><br>
				@endif
			@endif
			<span class="message-data" data-value="{!!$item->message!!}">{!!$item->message!!}</span><br>
			@if($item->message_file!='')
			<a href="{{ route('view.uploadedfile', Crypt::encrypt($item->message_file)) }}" target="_blank" class="action-a">
				<span class="label label-white middle f-s-10 form-label span-content">
					<i class="fa fa-hand-o-right"></i> Attached
				</span>
			</a>				
			@endif
		</td>
	</tr>
@endforeach
</table>
</div>
@else
Start conversation..
@endif
</td>
<td style="width:50%!important;">
<table class="mytable pd-8" style="width:100%;">
	@if($data->count()>0)
	<tr class="myheadbg"><td colspan="3">EoI Number : <b>{{ $data->first()->eoinumber }}</b></td></tr>
	<tr><td colspan="3">{{ $data->first()->engagementname }}</td></tr>
	@endif
	<tr class="myheadbg">
		<td colspan="3" class="font-14 font-bold" style="border-bottom:1px solid #eee!important;">
			Message / Query
		</td>
	</tr>
	<tr>
		<td style="padding:0px!important;">
			<input type="hidden" name="request_id" id="request_id" value="{{$requestid}}">
			<textarea name="message" id="message" class="width-full tinymce" rows="3" placeholder="Write your message / query.."></textarea>
		</td>
	</tr>
	<tr>
		<td class="v-top">
			Attachment
			<input type="file" class="attachment" name="message_file" id="message_file">
		</td>
	</tr>
	@if(session('userType')!='ADMIN')
	<tr>
		<td class="v-top padding-5" style="text-align:right;">
			<button type="button" class="btn btn-info gridbtn sendMessageBtn" style="width:100px;">
				<i class="fa fa-envelope"></i> Send
			</button>
		</td>
	</tr>
	@else
	<tr>
		<td>
			<select name="department_id" id="department_id" placeholder="" class="form-control">
				<option value="">Select Department / Project Manager</option>
				@foreach($department as $dept)
				<option value="{{$dept->recordid}}">{{$dept->label}}</option>
				@endforeach
			</select>
		</td>
	</tr>
	<tr><td>Or</td></tr>
	<tr>
		<td class="vendor-list" style="white-space: nowrap;">
			@foreach($vendors as $vendor)
			<label class="vendor-item" style="display:flex; align-items:center; gap:8px; margin-bottom:5px; ">
				<input type="checkbox" name="vendorids[]" value="{{$vendor->recordid}}">
				<span>{{$vendor->label}}</span>
			</label>
			@endforeach
		</td>
	</tr>
	<tr>
		<td class="v-top padding-5" style="text-align:right;">
			<button type="button" class="btn btn-info gridbtn sendMessageBtn" style="width:100px;">
				<i class="fa fa-envelope"></i> Send
			</button>
		</td>
	</tr>
	@endif
</table>
</td>

</tr>
<script src="{{ asset('panel/assets/tinymce/tinymce.min.js') }}"></script>
<script>
tinymce.init({
    selector: 'textarea.tinymce',
    promotion: false,
    branding: false,
    plugins: 'autoresize code advlist autolink lists charmap preview table searchreplace save',
    toolbar_mode: 'floating',
    toolbar: 'formatselect | bold | alignleft aligncenter alignjustify | bullist numlist | table',
    menubar: false,
    statusbar: false,
    min_height: 200,
    autoresize_min_height: 200,
    autoresize_max_height: 490,
    autoresize_bottom_margin: 10,
    resize: true,
    setup: function (editor) {
        editor.on('init', function () {
            editor.getBody().style.fontSize = '14px';
            editor.getBody().style.lineHeight = '1.5';
        });
    }
});



function MarkAsRead(recordid)	
{
	var form = $('#sendmsg')[0];
	var formData = new FormData(form);
	formData.append('recordid',recordid);
	$.ajax({
		url: '{{route("markas.read")}}',
		method: 'POST',
		data: formData,
		processData: false,
		contentType: false,
		success: function(response) {
			if(response.status===200)
			{
				bootbox.alert(response.message);
				setTimeout(function(){ loadData('{{ route('communication.html') }}'); },3000);
				return false;
			}
			else
			{
				bootbox.alert('<span style="color:red;">'+response.message+'</span>');
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
}

$(document).ready(function() {
    $('#filter_data').on('change', function() {
        var selectedVal = $(this).val().toString().trim(); // selected recordid as string
        var selectedType = $('#filter_data option:selected').data('type'); // DEPT or VENDOR

        // Show all rows if no selection
        if(selectedVal === "") {
            $('table.pd-8 tr[data-department]').show();
            return;
        }

        $('table.pd-8 tr[data-department]').each(function() {
            var $tr = $(this);
            var deptVal = $tr.data('department').toString().trim(); // DEPT value
            var vendorVal = $tr.data('vendor').toString().replace(/[()\s]/g,''); // clean vendor string

            if(selectedType === 'DEPT') {
                // Show only if DEPT matches exactly
                if(deptVal === selectedVal) {
                    $tr.show();
                } else {
                    $tr.hide();
                }
            } 
            else if(selectedType === 'VENDOR') {
                // Split into array and trim each
                var vendorArr = vendorVal.split(',').map(function(v){ return v.trim(); });
                // Show only if selectedVal exactly matches one of the vendor IDs
                if(vendorArr.indexOf(selectedVal) !== -1) {
                    $tr.show();
                } else {
                    $tr.hide();
                }
            } 
            else {
                $tr.show();
            }
        });
    });
});
</script>