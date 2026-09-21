<html>
<title>PURCHASE DETAIL</title>
<head>
<link rel="stylesheet" href="{{ asset('panel/assets/font-awesome/4.5.0/css/font-awesome.min.css')}}" />
<link rel="stylesheet" href="{{ asset('panel/assets/css/content.css')}}" />
</head>
<body style="font-size:12px; margin:0 auto;">
<center>
<table class="printingtable">
<tr class="height20">
	<td>
		<table class="innertable">
			@if($company->headerimg=='')
			<tr style="height:20px;">
				<td style="text-align:left; width:250px; vertical-align:top;">
					<img src="{{ asset('storage/'.$company->companylogo) }}" style="width:250px; margin-top:0px;">
				</td>
				<td style="text-align:right; vertical-align:middle!important; padding:0px 5px;">
					<span style="line-height:25px; font-size:24px; font-weight:bold; letter-spacing:5px;">{{$company->companyname}}</span><br>
					<span style="line-height:15px; font-size:12px; font-size:10px;">{{$company->address}}</span><br>
					<span style="line-height:15px; font-size:12px; font-size:12px;"><i class="fa fa-envelope"></i> {{$company->email}} | <i class="fa fa-globe"></i>  {{$company->website}}</span><br>
					<span style="line-height:15px; font-size:12px; font-size:12px;">Contact : {{$company->phonenumber}} | GSTIN : <b>{{$company->gstnumber}}</b></span>
				</td>
			</tr>
			@endif
			<tr style="height:20px;">
				<td class="label headcenter" colspan="2"><b>PURCHASE VOUCHER</b></td>
			</tr>
			<tr style="height:20px;">
				<td class="label" colspan="2">
					<span style="float:left;">VOUCHER NUMBER : <b>{{$data->voucherstring}}</b></span>
					<span style="float:right;">DATE : {{ date('d\-m\-Y',strtotime($data->creationdate)) }}</span>
				</td>
			</tr>
			<tr style="height:20px;">
				<td class="label" colspan="2">SUPPLIER DETAIL</td>
			</tr>
			<tr style="height:20px;">
				<td class="content" colspan="2">
					<b>{{$data->companyname}}</b><br>
					<span>{{ucwords($data->billingaddress)}}, {{ucwords($data->pincode)}}</span><br>
					<span>Email : {{ucwords($data->email)}}</span><br>
					<span>Contact : {{ucwords($data->mobilenumber)}}</span><br>
				</td>
			</tr>
			<tr style="height:20px;">
				<td colspan="2" style="text-align:center;">
				<table class="itemtable" border="1">
					<tr class="thead">
						<td class="headcenter" style="width:25px;">S.NO.</td>
						<td class="headleft">ITEM NAME</td>
						<td class="headcenter" style="width:100px;">BATCH</td>
						<td class="headcenter" style="width:50px;">EXP</td>
						<td class="headcenter" style="width:40px;">TAX</td>
						<td class="headcenter" style="width:70px;">RATE</td>
						<td class="headcenter" style="width:40px;">QTY</td>
						<td class="headcenter" style="width:40px;">F. QTY</td>
						<td class="headcenter" style="width:40px;">DISC(%)</td>
						<td class="headcenter" style="width:70px;">DISCOUNT</td>
						<td class="headright" style="width:70px;">TAXABLE</td>
					</tr>
					@php $i=1; @endphp
					@foreach($datadetail as $dt)
					<tr>
						<td class="headcenter">{{$i}}</td>
						<td class="headleft">{{$dt->productname}} @if($dt->variationname!='') {{$dt->variationname}} @endif  @if($dt->variationvalue!='') {{$dt->variationvalue}} @endif</td>
						<td class="headcenter" nowrap>{{$dt->batchnumber}}</td>
						<td class="headcenter" nowrap>{{$dt->expiry}}</td>
						<td class="headcenter" nowrap>@if($dt->gst!=0) {{$dt->gst}} @endif</td>
						<td class="headcenter" nowrap>{{number_format($dt->rate,'2','.','')}}</td>
						<td class="headcenter" nowrap>{{$dt->quantity}}</td>
						<td class="headcenter" nowrap>@if($dt->freeqty!=0) {{$dt->freeqty}} @endif</td>
						<td class="headcenter" nowrap>@if($dt->discrate!=0) {{number_format($dt->discrate,'2','.','')}} @endif</td>
						<td class="headcenter" nowrap>@if($dt->discount!=0) {{number_format($dt->discount,'2','.','')}} @endif</td>
						<td class="headright" nowrap>{{number_format($dt->taxable,'2','.','')}}</td>
					</tr>
					@php $i=$i+1; @endphp
					@endforeach
					<tr>
						<td colspan="9" rowspan="7" style="vertical-align:bottom;">
							<table style="border-collapse:collapse; border:1px solid #eee; width:300px; float:right;" border="1">
								<tr>
									<td class="headcenter thead">AMOUNT</td>
									<td class="headcenter thead">SGST%</td>
									<td class="headcenter thead">SGST</td>
									<td class="headcenter thead">CGST%</td>
									<td class="headcenter thead">CGST</td>
								</tr>
								@foreach($tax as $tax)
								<tr>
									<td class="headcenter">{{number_format($tax->taxable,'2','.','')}}</td>
									<td class="headcenter">{{number_format($tax->gst/2,'2','.','')}}</td>
									<td class="headcenter">{{number_format($tax->gstvalue/2,'2','.','')}}</td>
									<td class="headcenter">{{number_format($tax->gst/2,'2','.','')}}</td>
									<td class="headcenter">{{number_format($tax->gstvalue/2,'2','.','')}}</td>
								</tr>
								@endforeach
							</table>
						</td>
						<td class="headleft thead">TOTAL</td>
						<td class="headright thead">{{number_format($data->taxable-$data->freight-$data->packing,'2','.','')}}</td>
					</tr>
					<tr>
						<td class="headleft thead">FREIGHT</td>
						<td class="headright thead">{{number_format($data->freight,'2','.','')}}</td>
					</tr>
					<tr>
						<td class="headleft thead">PACKING</td>
						<td class="headright thead">{{number_format($data->packing,'2','.','')}}</td>
					</tr>
					<tr>
						<td class="headleft thead" nowrap>TAXABLE AMOUNT</td>
						<td class="headright thead">{{number_format($data->taxable,'2','.','')}}</td>
					</tr>
					<tr>
						<td class="headleft thead" nowrap>TAX AMOUNT</td>
						<td class="headright thead">{{number_format($data->gstvalue,'2','.','')}}</td>
					</tr>
					<tr>
						<td class="headleft thead" nowrap>ROUND UP</td>
						<td class="headright thead">{{number_format($data->roundup,'2','.','')}}</td>
					</tr>
					<tr>
						<td class="headleft thead" nowrap>INVOICE AMOUNT</td>
						<td class="headright thead">{{number_format($data->netamount,'2','.','')}}</td>
					</tr>
				</table>
				</td>
			</tr>
			<tr>
				<td colspan="2" style="text-align:center;"></td>
			</tr>
			<tr style="height:20px;">
				<td class="label headcenter" colspan="2"><span style="float:right;"><b>PREPARED BY : {{$data->createdbyname}}</b></span></td>
			</tr>
		</table>
	</td>
</tr>
</table>
</center>
</body>
</html>