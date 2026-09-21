<div class="table-responsive">
<table class="mytable" border="1" style="text-transform: none!important;">
	<tr class="myheadbg">
		<td class="center padding-10 font-bold" style="width:30px;">S.No.</td>
		<td class="padding-10 font-bold">Position & Experience</td>
		<td class="padding-10 font-bold">Name</td>
		<td class="padding-10 font-bold">Mobile Number</td>
		<td class="padding-10 font-bold">Email</td>
		<td class="padding-10 font-bold">Work Order Issuance Date</td>
	</tr>
	@php
	$totalmanmonth		=	0;
	$adminchargetotal	=	0;
	$grandtotal			=	0;
	@endphp
	@foreach($detail as $index=>$req)
	<tr>
		<td class="padding-10 center v-top">{{$loop->iteration}}</td>
		<td class="padding-10">
		{{ucwords(strtolower($req->role))}}<br>[L-{{$req->experiencelevel}}]<br>{{$req->experience}}
		</td>
		<td class="padding-10">
			<input type="hidden" name="recordid[]" value="{{Crypt::encrypt($req->recordid)}}">
			<input type="text" name="candidatename[]" value="{{old('candidatename.' . $index, $req->name)}}" class="form-control" placeholder="Name" autocomplete="off">
		</td>
		<td class="padding-10">
			<input type="text" name="mobilenumber[]" value="{{old('mobilenumber.' . $index, $req->mobilenumber)}}" class="form-control" placeholder="Mobile number" autocomplete="off">
		</td>
		<td class="padding-10">
			<input type="text" name="email[]" value="{{old('email.' . $index, $req->email)}}" class="form-control" placeholder="Email" autocomplete="off">
		</td>
		<td class="padding-10">
			@if(!old('deploymentdate.'.$index))
			<input type="text" name="deploymentdate[]" @if($req->deployment_date!='')value="{{date('d\-m\-Y',strtotime($req->deployment_date))}}"@endif class="form-control deploymentdate" placeholder="dd-mm-YYYY" autocomplete="off">
			@else
			<input type="text" name="deploymentdate[]" value="{{date('d\-m\-Y',strtotime(old('deploymentdate.'.$index)))}}" class="form-control deploymentdate" placeholder="dd-mm-YYYY" autocomplete="off">
			@endif
		</td>
	</tr>
	@endforeach
	<tr>
		<td class="padding-10" style="text-align:right;" colspan="6">
			<button type="button" class="btn btn-info" onclick="SetDeploymentDate()">
				<i class="fa fa-calendar"></i> Set Work Order Issuance Date
			</button>
		</td>
	</tr>
</table>
</div>