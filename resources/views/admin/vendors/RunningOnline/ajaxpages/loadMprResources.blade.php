
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
		<td class="">Mobile Number</td>
		<td class="">Email</td>
		<td class="">Work Order Issuance Date</td>
		<td class="">Status</td>
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
		<td class="v-top">{{$resource->name}}
		@if($resource->status=='')
		<span class="label label-warning middle animated flipInX delay-02 f-s-20 form-label span-content" style="float:right;">Pending</span>
		@else
		<span class="label label-success middle animated flipInX delay-02 f-s-20 form-label span-content" style="float:right;">{{$resource->status}}</span>
		@if($resource->status=='Completed')
		@php $completed	=	$completed+1; @endphp
		@endif
	
		@endif
		</td>
		<td class="v-top">{{$resource->mobilenumber}}</td>
		<td class="v-top">{{$resource->email}}</td>
		<td class="v-top">{{date('d\-m\-Y',strtotime($resource->deployed_date))}}</td>
		<td class="v-top">{{$resource->deployment_status}}</td>
		<td class="center width-30 v-top">
		@if($resource->deployment_status!='Submitted')
			<span type="button" class="btn btn-info br-3" onclick="loadResourceMpr('{{route('load.resourcempr')}}','{{Crypt::encrypt($order->orderid)}}',{{$mprid}},{{$month}},{{$year}},{{$resource->deploymentid}})">
				<i class="ace-icon fa fa-hand-o-right bigger-120"></i> @if($resource->status=='') Attendance @else View @endif
			</span>
		@else
			<span type="button" class="btn btn-info br-3">
				<i class="ace-icon fa fa-hand-o-right bigger-120"></i> Submitted
			</span>
			
		@endif
		</td>
	</tr>
	@endforeach
	@if($records==$completed && $records!=0)
	<tr>
		<td colspan="9" style="text-align:right;">
			<button type="button" class="btn btn-info" onclick="submitMprToDepartment('{{route('submit.mpr')}}','{{Crypt::encrypt($order->orderid)}}',{{$mprid}},{{$month}},{{$year}})">
				<i class="fa fa-save"></i> Submit for Approval
			</button>
		</td>
	</tr>
	@endif
	@if($records==0)
	<tr>
		<td colspan="9" class="center">--No Record Found--</td>
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
$(".modal-footer").append(
  '<button type="button" class="btn btn-info backBtn" onclick="loadMonths(\'{{ route('mpr.months') }}\', \'{{ Crypt::encrypt($order->orderid) }}\')">' +
    '<i class="fa fa-arrow-left"></i> Back' +
  '</button>'
);

$(".modal-footer").append(
  '<label class="form-label backBtn" style="float:left;">MPR For : '+month+'-'+year+'</label>'
);

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
	bootbox.confirm('Do you want to submit the MPR to the department?',function(result){
		
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
						$('#paymentContent').html('<div style="width:100%; text-align:center;"><h2><i class="fa fa-thumbs-up"></i><br><br>MPR details submitted to the department successfully!</h2></div>');
						
						setTimeout(function(){ location.reload(); },3000);
					}			
					if(response.status==400)
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

</script>