<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>EoI PREVIEW</title>
	<link rel="shortcut icon" href="{{ asset('panel/assets/images/basic/logo.png') }}">
	<link rel="stylesheet" href="{{ asset('panel/assets/css/eoiprinting.css') }}" />  
	<link rel="stylesheet" href="{{ asset('panel/assets/css/multi-form.css') }}" />  
</head>
<body>
<table style="width:8.27in; height:11.69in; max-height:11.69in;">
	<tr class="primary-bgcolor" style="height:20px;">
		<td colspan="2" class="color-white padding-0-20 font-20 font-bold">
		Draft Expression of Interest (EoI)  Form
		</td>
	</tr>
	<tr class="tr-20">
		<td colspan="2">
			<div style="display: inline-block; vertical-align: middle;">
				<img src="{{ asset('panel/assets/images/basic/logo.png') }}" alt="Login Image" style="height: 50px; vertical-align: middle;">
			</div>
			<div style="display: inline-block; vertical-align: middle; margin-left: 10px;">
				<span id="banner-text">Chhattisgarh Infotech Promotion Society | Government of Chhattisgarh</span><br>
				<h2 class="primary-color" style="margin: 0; display: inline-block;">CHIPS Empanelment Portal</h2>
			</div>

		</td>
	</tr>
	<tr class="primary-bgcolor" style="height:20px;">
		<td colspan="2" class="color-white padding-0-10 font-16">Department</td>
	</tr>
	<tr class="tr-20">
		<td class="padding-0-10 font-14 wdth-200">Department Name</td>
		<td class="font-14">: {{$department->departmentname}}</td>
	</tr>
	<tr class="tr-20">
		<td class="padding-0-10 font-14">Officer Name</td>
		<td class="font-14">: {{$user->name}}</td>
	</tr>
	<tr class="tr-20">
		<td class="padding-0-10 font-14">Designation</td>
		<td class="font-14">: {{$department->designation}}</td>
	</tr>
	<tr class="tr-20">
		<td class="padding-0-10 font-14">Contact Number</td>
		<td class="font-14">: {{$user->mobilenumber}}</td>
	</tr>
	<tr class="tr-20">
		<td class="padding-0-10 font-14">Email Id</td>
		<td class="font-14">: {{$user->email}}</td>
	</tr>
	<tr class="tr-20">
		<td class="padding-0-10 font-14">Date of Submission</td>
		<td class="font-14">: {{date('d\-m\-Y')}}</td>
	</tr>
	<tr class="tr-20">
		<td colspan="2">
		<table style="width:100%; border:1px solid #eee; border-collapse:collapse;" border="1" class="">
			<tr class="primary-bgcolor color-white font-12 font-bold">
				<td class="text-center padding-5">S.No.</td>
				<td class="default-td padding-5">Sector</td>
				<td class="default-td padding-5">Position</td>
				<td class="default-td padding-5">Exp. Level</td>
				<td class="default-td padding-5">Duration</td>
				<td class="default-td padding-5">Man Month Rate (Including tax)</td>
				<td class="default-td padding-5">Remark</td>
			</tr>
			@php $i=0; $total=0; $grandtotal=0; $totalmanmonth=0; $adminchargetotal=0; @endphp
			@foreach($eoidata as $eoi)
			<tr class="tr-20 font-12">
				<td class="v-top text-center padding-5">{{$loop->iteration}}</td>
				<td class="default-td padding-5">{{$eoi->sectorname}}</td>
				<td class="default-td padding-5">{{$eoi->consultantposition}}</td>
				<td class="default-td padding-5">{{$eoi->experience}}</td>
				<td class="default-td padding-5">{{$eoi->duration}} Months</td>
				<td class="text-right padding-5">{{$eoi->budget}}</td>
				<td class="text-right padding-5">{{$eoi->remark}}</td>
			</tr>
			@php
				$total				=	$total+$eoi->budget;				
			@endphp
			@endforeach
			@php
			$admincost	=	round(($total*$admincharge)/100,2);
			@endphp
			
			<tr>
				<td colspan="5" style="text-align:right;">Cost of Resources (Incuding Tax)</td>
				<td style="text-align:right;">{{$total}}</td>
			</tr>
			<tr>
				<td colspan="5" style="text-align:right;">CHiPS Admin Charge ({{$admincharge}}%)</td>
				<td style="text-align:right;">{{$admincost}}</td>
			</tr>
			<tr>
				<td colspan="5" style="text-align:right;">Grand Total</td>
				<td style="text-align:right;">{{$total+$admincost}}</td>
			</tr>
		</table>
		</td>
	</tr>
	@if($aboutproject!='')
	<tr class="tr-20 primary-bgcolor color-white">
		<td colspan="2" class="padding-0-2 font-14 font-bold">About Project</td>
	</tr>
	<tr class="tr-20">
		<td colspan="2" style="vertical-align:top;" class="font-14 border-1">{!!$aboutproject!!}</td>
	</tr>
	@endif
	<tr class="tr-20 primary-bgcolor color-white">
		<td colspan="2" class="padding-0-5 font-14 font-bold">Scope Of Work</td>
	</tr>
	<tr class="tr-20">
		<td colspan="2" style="vertical-align:top;" class="font-14 border-1">{!!$scope!!}</td>
	</tr>
	@if($anyother!='')
	<!--
	<tr class="tr-20 primary-bgcolor color-white">
		<td colspan="2" class="padding-0-2 font-14 font-bold">Any Other</td>
	</tr>
	-->
	<tr class="tr-20">
		<td colspan="2" style="vertical-align:top;" class="font-14 border-1">{!!$anyother!!}</td>
	</tr>
	@endif
	<tr>
		<td colspan="2">&nbsp;</td>
	</tr>
	<tr class="tr-20">
	  <td class="text-left" colspan="2" style="padding: 10px; text-transform: none;">
		<label style="display:flex; align-items:flex-start; gap: 10px; font-size: 14px; line-height: 1.5;">
		  <input type="checkbox" name="declaration" id="declaration" style="width: 16px; height: 16px; margin-top:3px;" checked disabled>
		  <span style="text-align:justify!important;">
			I hereby confirm that all the information provided above is accurate and submitted by me. I understand that this requirement is essential for my department's project, and I take full responsibility for the authenticity of the details. I agree to comply with CHiPS policies and acknowledge that the request will be processed as per internal guidelines.
		  </span>
		</label>
	  </td>
	</tr>
	<tr class="tr-20">
		<td colspan="2" class="padding-0-10 font-14 font-bold text-right">Approving Authority (Signature & Seal)</td>
	</tr>
</table>

</body>
</html>
