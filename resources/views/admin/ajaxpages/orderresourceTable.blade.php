@php
$i=1;
@endphp
@if($categoryid==2)
<table class="mytable" border="1" style="text-transform: none!important; margin-top:0px;">
	<tr class="myheadbg">
		<td class="padding-5 form-label center" style="width:30px;">S.No.</td>
		<td class="padding-5 form-label">Sector</td>
		<td class="padding-5 form-label">Position</td>
		<td class="padding-5 form-label">Name</td>
		<td class="padding-5 form-label">Mobile Number</td>
		<td class="padding-5 form-label">Email</td>
		<td class="padding-5 form-label center">Work Order Issuance Date</td>
		<td class="padding-5 form-label center">Status</td>
		<td class="padding-5 form-label" style="text-align:right;">Remuneration</td>
	</tr>
	<tbody>
	@foreach($resources as $resource)
	<tr>
		<td class="padding-5 center">{{$loop->iteration}}</td>
		<td class="padding-5">{{ucwords(strtolower($resource->sectorname))}}</td>
		<td class="padding-5">{{ucwords(strtolower($resource->consultantposition))}}</td>
		<td class="padding-5">{{$resource->name}}</td>
		<td class="padding-5">{{$resource->mobilenumber}}</td>
		<td class="padding-5">{{$resource->email}}</td>
		<td class="padding-5 center">@if($resource->deployment_date!=NULL && $resource->deployment_date!='1970-01-01'){{date('d\-m\-Y',strtotime($resource->deployment_date))}}@endif</td>
		<td class="padding-5 center">{{$resource->deployment_status}}</td>
		<td class="padding-5 form-label format-indian" style="text-align:right;" data-value="{{ number_format($resource->remuneration,'2','.','') }}"></td>
	</tr>
	@endforeach
	</tbody>
</table>
@else
<table class="mytable" border="1" style="text-transform: none!important; margin-top:0px;">
	<tr class="myheadbg">
		<td class="padding-5 form-label center" style="width:30px;">S.No.</td>
		<td class="padding-5 form-label">Role</td>
		<td class="padding-5 form-label">Experience Level</td>
		<td class="padding-5 form-label">Name</td>
		<td class="padding-5 form-label">Mobile Number</td>
		<td class="padding-5 form-label">Email</td>
		<td class="padding-5 form-label center">Work Order Issuance Date</td>
		<td class="padding-5 form-label center">Status</td>
		<td class="padding-5 form-label" style="text-align:right;">Remuneration</td>
	</tr>
	<tbody>
	@foreach($resources as $resource)
	<tr>
		<td class="padding-5 center">{{$loop->iteration}}</td>
		<td class="padding-5">{{ucwords(strtolower($resource->role))}}</td>
		<td class="padding-5">{{$resource->experience}}<br>[L-{{$resource->experiencelevel}}]</td>
		<td class="padding-5">{{$resource->name}}</td>
		<td class="padding-5">{{$resource->mobilenumber}}</td>
		<td class="padding-5">{{$resource->email}}</td>
		<td class="padding-5 center">@if($resource->deployment_date!=NULL && $resource->deployment_date!='1970-01-01'){{date('d\-m\-Y',strtotime($resource->deployment_date))}}@endif</td>
		<td class="padding-5 center">{{$resource->deployment_status}}</td>
		<td class="padding-5 form-label format-indian" style="text-align:right;" data-value="{{ number_format($resource->remuneration,'2','.','') }}"></td>
	</tr>
	@endforeach
	</tbody>
</table>
	
@endif
<script src="{{ asset('panel/assets/js/datatablePaginationWithFilter.js') }}"></script>
<script>
document.querySelectorAll('.format-indian').forEach(function(el) {
    el.innerText = formatIndianNumber(el.dataset.value);
});

</script>
