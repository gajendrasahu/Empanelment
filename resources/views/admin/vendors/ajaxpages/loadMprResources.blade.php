<table class="mytable pd-8" border="1">
	<tr class="myheadbg form-label font-14">
		<td class="center width-60">S. No.</td>
		@if($order->categoryid==2)
		<td class="">Sector</td>
		<td class="">Position</td>
		@elseif($order->categoryid==1)
		<td class="">Position</td>
		<td class="">Experience</td>
		@endif
		<td class="">Name</td>
		<td class="text-center">Mobile Number</td>
		<td class="text-center">Joining Date</td>
		<td class="text-center">Release Date</td>
		<td class="text-center">Man Month Rate (Inc. tax)</td>
		<td class="text-center">Status</td>
		<td class="center width-30"></td>
	</tr>
	@php
	$records	=	0;
	$completed	=	0;
	@endphp
	@foreach($resources as $resource)
	@php
	$records	=	$records+1;
	@endphp
	<tr class="form-label">
		<td class="center v-top">{{$loop->iteration}}</td>
		@if($order->categoryid==2)
		<td class="v-top">{{ucwords(strtolower($resource->sectorname))}}</td>
		<td class="v-top">{{ucwords(strtolower($resource->consultantposition))}}</td>
		@elseif($order->categoryid==1)
		<td class="v-top">{{ucwords(strtolower($resource->role))}}</td>
		<td class="v-top">{{ucwords(strtolower($resource->experience))}}<br>[L-{{$resource->experiencelevel}}]</td>		
		@endif
		<td class="v-top width-150" nowrap style="position:relative;">{{$resource->name}}
		@if($resource->status=='')
		<span class="label label-warning middle animated flipInX delay-02" style="position:absolute; left:0px; bottom:0px; border-radius:0px 45px 0px 0px; width:100%; font-size:10px; height:18px; ">MPR Status : Pending</span>
		@else
		<span class="label label-success middle animated flipInX delay-02" style="position:absolute; left:0px; bottom:0px; border-radius:0px 45px 0px 0px; width:100%; font-size:10px; ">MPR Status : {{$resource->status}}</span>
		@if($resource->status=='Completed')
		@php $completed	=	$completed+1; @endphp
		@endif
		@endif
		</td>
		<td class="v-top text-center">{{$resource->mobilenumber}}</td>
		<td class="v-top text-center">{{date('d\-m\-Y',strtotime($resource->deployed_date))}}</td>
		<td class="v-top text-center">@if($resource->lastdate){{date('d\-m\-Y',strtotime($resource->lastdate))}}@else - @endif</td>
		<td class="v-top text-center">{{$resource->remuneration}} INR</td>
		<td class="v-top text-center">{{$resource->deployment_status}}</td>
		<td class="center width-30 v-top">
		@if($resource->deployment_status!='Submitted')
			<span type="button" class="btn btn-info gridbtn br-3" onclick="loadResourceMpr('{{route('load.resourcempr')}}','{{Crypt::encrypt($order->orderid)}}',{{$mprid}},{{$month}},{{$year}},{{$resource->deploymentid}})">
				<i class="ace-icon fa fa-hand-o-right bigger-120"></i> View
			</span>
		@else
			<span type="button" class="btn btn-info gridbtn br-3">
				<i class="ace-icon fa fa-hand-o-right bigger-120"></i> Submitted
			</span>
		@endif
		</td>
	</tr>
	@endforeach
	@if($records==$completed && $records!=0)

	<tr>
		<td colspan="10">
		<table style="width:100%!important;">
			<tr>
				<td style="width:500px;">
					<input type="text" class="width-full" name="remark_mpr" id="remark_mpr" onKeyPress="return OnKeyPress(this, event)" placeholder="Remark..." />
				</td>
				<td>
					@if($order->categoryid==2)
					<input type="file" class="group_mpr_file" name="group_mpr_file" id="group_mpr_file" accept="application/pdf" onKeyPress="return OnKeyPress(this, event)" style="width:100%!important; padding:20px!important; height:30px!important;" />
					@endif
				</td>
				<td style="width:300px;">
					<button type="button" class="btn btn-info gridbtn" onclick="submitMprToDepartment('{{route('submit.mpr')}}','{{Crypt::encrypt($order->orderid)}}',{{$mprid}},{{$month}},{{$year}})">
						<i class="fa fa-save"></i> @if($order->ispm==0) Submit to Department for Approval @else Submit to Project Manager for Approval @endif
					</button>
				</td>
			</tr>
		</table>
		</td>
	</tr>
	@endif
	@if($records==0)
	<tr>
		<td colspan="10" class="center">--No Record Found--</td>
	</tr>
	@endif
</table>


<script>
if ($('.goAheadBtn').length) {
    $('.goAheadBtn').hide();
}

if ($('.mark_attendance').length) {
    $('.mark_attendance').hide();
}
var month	=	"{{ \Carbon\Carbon::create()->month($month)->format('F') }}";
var year	=	"{{$year}}";
$(".modal-title").html(
    '<i class="fa fa-users"></i> Resources deployed for following project : {{$order->projecttitle}}'
);
/*
$(".modal-footer").append(
  '<button type="button" class="btn btn-info backBtn gridbtn" style="width:100px;" onclick="loadMonths(\'{{ route('mpr.months') }}\', \'{{ Crypt::encrypt($order->orderid) }}\')">' +
    '<i class="fa fa-arrow-left"></i> Back' +
  '</button>'
);

$(".modal-footer").append(
  '<label class="form-label backBtn" style="float:left;"><b>Please complete the Monthly Progress Report (MPR) for all resources associated with this order.</b></label>'
);
*/
function loadResourceMpr(r1,orderid,mprid,month,year,deploymentid)
{
	$.get(""+r1,
	{
		orderid:orderid,
		mprid:mprid,
		month:month,
		year:year,
		deploymentid:deploymentid
	},
	function(data, status){
		if(data.status==400)
		{
			bootbox.alert(data.message);
		}
		else
		{
			$('#paymentContent').html(data.data);
		}
	});
}

function submitMprToDepartment(rl,orderid,mprid,month,year)
{
	var a =	0;
	bootbox.confirm('Do you want to submit the MPR?',function(result){
		
		if(result)
		{
			var form = $('#storeattendance')[0];
			var formData = new FormData(form);
			formData.append('_token', $('meta[name="csrf-token"]').attr('content'));
			formData.append('orderid',orderid);
			formData.append('mprid',mprid);
			formData.append('month',month);
			formData.append('year',year);
			$.ajax({
				url: ''+rl,
				method: 'POST',
				data: formData,
				processData: false,
				contentType: false,
				success: function(response) {
					if(response.status==200)
					{
						@if($order->categoryid==2)
						$('#paymentContent').html('<div style="width:100%; text-align:center;"><h2><i class="fa fa-thumbs-up"></i><br><br>MPR details submitted successfully!</h2></div>');
						@else
						$('#paymentContent').html('<div style="width:100%; text-align:center;"><h2><i class="fa fa-thumbs-up"></i><br><br>MPR details submitted successfully!</h2></div>');
						@endif
						
						setTimeout(function(){ location.reload(); },3000);
					}			
					if(response.status==400 || response.status==500)
					{
						bootbox.alert(response.message);
					}			
				},
				error: function(xhr) {
					let errors = xhr.responseJSON?.errors;
					let message = '';
					$.each(errors, function(key, val) {
						message += '<i class="fa fa-hand-o-right"></i> '+ val + '<br>';
					});
					bootbox.alert(message);
					setTimeout(function() { $(".no-skin").css("padding-right", ""); }, 2000);
				}
			});
		}
	});
}

$('.group_mpr_file').ace_file_input({
	no_file:'No File ...',
	btn_choose:'Grouped MPR File* (.pdf)',
	btn_change:'Change',
	btn_name:'fourthimage',
	thumbnail:false //| true | large
});


$('.mpr_file').ace_file_input({
	no_file:'No File ...',
	btn_choose:'MPR File (.pdf)',
	btn_change:'Change',
	btn_name:'fourthimage',
	thumbnail:false //| true | large
});

$('.attendance_file').ace_file_input({
	no_file:'No File ...',
	btn_choose:'Attendance File (.pdf)',
	btn_change:'Change',
	btn_name:'fourthimage',
	thumbnail:false //| true | large
});
$('.supporting_file').ace_file_input({
	no_file:'No File ...',
	btn_choose:'Supporting File (.pdf)',
	btn_change:'Change',
	btn_name:'fourthimage',
	thumbnail:false //| true | large
});

</script>