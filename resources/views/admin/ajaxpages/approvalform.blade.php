@php
$r=0;
@endphp
<form name="sendtoapproval" id="sendtoapproval" method="post" action="">
@if(!$lastRequest)
@php
$r++;
@endphp
	<table class="pd-8 width-600">
		<tr>
			<td class="width-100 v-top">Select Name</td>
			<td class="width-200 v-top">
				<input type="hidden" name="request_id" id="request_id" value="{{Crypt::encrypt($requestid)}}">
				<input type="hidden" name="form_token" id="form_token" value="{{$token}}">
				<select name="user_id" id="user_id" class="width-full">
					<option value="">--Select Name--</option>
					@foreach($approvalUsers as $usrs)
					<option value="{{$usrs->userid}}">{{$usrs->name}}</option>
					@endforeach
				</select>
			</td>
		</tr>
		<tr>
			<td class="v-top width-100">Remark</td>
			<td class="v-top">
				<textarea name="approval_remark" id="approval_remark" rows="5" class="width-full" placeholder="Remark..."></textarea>
			</td>
		</tr>
	</table>
@elseif($lastRequest && ($lastRequest->touserid==session('userId')))
@php
$r++;
@endphp

	<table class="pd-8 width-600">
		<tr>
			<td class="width-100 v-top">Select Name</td>
			<td class="width-200 v-top">
				<input type="hidden" name="request_id" id="request_id" value="{{Crypt::encrypt($requestid)}}">
				<input type="hidden" name="form_token" id="form_token" value="{{$token}}">
				<select name="user_id" id="user_id" class="width-full">
					<option value="">--Select Name--</option>
					@foreach($approvalUsers as $usrs)
					<option value="{{$usrs->userid}}">{{$usrs->name}}</option>
					@endforeach
				</select>
			</td>
		</tr>
		<tr>
			<td class="v-top width-100">Remark</td>
			<td class="v-top">
				<textarea name="approval_remark" id="approval_remark" rows="5" class="width-full" placeholder="Remark..."></textarea>
			</td>
		</tr>
	</table>
@endif	
</form>

<table class="table pd-8 width-full" border="">
	<thead>
	<tr class="myheadbg">
		<td class="center width-30">S.No.</td>
		<td nowrap>Project Title</td>
		<td nowrap class="center">Request Date</td>
		<td>From</td>
		<td>To</td>
		<td>Remark</td>
		<td class="center" nowrap>Approval Date & Time</td>
	</tr>
	</thead>
	<tbody>
	@foreach($requests as $request)
	<tr>
		<td class="center">{{$loop->iteration}}</td>
		<td class="width-200">{{$request->projecttitle}}</td>
		<td nowrap class="center">@if($request->requestdate){{date('d\-m\-Y, h:i A',strtotime($request->requestdate))}}@endif</td>
		<td>{{$request->fromname}}</td>
		<td>{{$request->toname}}</td>
		<td class="width-200">{{$request->remark}}</td>		
		<td class="center">@if($request->closingdate){{date('d\-m\-Y, h:i A',strtotime($request->closingdate))}}@endif</td>
	</tr>
	@endforeach
	@if($requests->count()==0)
	<tr><td colspan="7" class="center">--No record Found--</td></tr>
	@endif
	</tbody>
</table>


<script>

@if($r==0)
	$(".sendApprovalBtn").css('display','none');
@endif

$('.sendApprovalBtn').on('click', function(e) {
	e.preventDefault();
	var form = $('#sendtoapproval')[0];
	var formData = new FormData(form);
	$.ajax({
		url: '{{route("sendto.approval")}}',
		method: 'POST',
		data: formData,
		processData: false,
		contentType: false,
		success: function(response) {
			if(response.status===200)
			{
				
				
				setTimeout(function() { loadData(1, '{{ route('eoirequest.html') }}'); window.location.reload(); },2000);
				bootbox.alert(response.message);
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
});

</script>
