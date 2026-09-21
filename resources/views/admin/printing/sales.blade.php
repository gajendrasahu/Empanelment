<html>
<title>SALES INVOICE</title>
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
				<td class="label headcenter" colspan="2"><b>TAX INVOICE</b></td>
			</tr>
			<tr style="height:20px;">
				<td class="label" colspan="2">
					<span style="float:left;">INVOICE NUMBER : <b>{{$data->voucherno}}</b></span>
					<span style="float:right;">INVOICE DATE : {{ date('d\-m\-Y',strtotime($data->salesdate)) }}</span>
				</td>
			</tr>
			<tr style="height:20px;">
				<td class="label" colspan="2">PARTY DETAIL</td>
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
						<td class="headcenter" style="width:70px;">MRP</td>
						<td class="headcenter" style="width:70px;">RATE</td>
						<td class="headcenter" style="width:40px;">TAX</td>												
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
						<td class="headcenter" nowrap>@if($dt->batchnumber!='') {{$dt->batchnumber}} @else - @endif</td>
						<td class="headcenter" nowrap>@if($dt->expiry!='') {{$dt->expiry}} @else - @endif</td>
						<td class="headcenter" nowrap>{{number_format($dt->mrp,'2','.','')}}</td>
						<td class="headcenter" nowrap>{{number_format($dt->rate,'2','.','')}}</td>
						<td class="headcenter" nowrap>@if($dt->gst!=0) {{$dt->gst}} @else - @endif</td>
						<td class="headcenter" nowrap>{{$dt->quantity}}</td>
						<td class="headcenter" nowrap>@if($dt->freeqty!=0) {{$dt->freeqty}} @else - @endif</td>
						<td class="headcenter" nowrap>@if($dt->discrate!=0) {{number_format($dt->discrate,'2','.','')}} @else - @endif</td>
						<td class="headcenter" nowrap>@if($dt->discount!=0) {{number_format($dt->discount,'2','.','')}} @else - @endif</td>
						<td class="headright" nowrap>{{number_format($dt->taxable,'2','.','')}}</td>
					</tr>
					@php $i=$i+1; @endphp
					@endforeach
					<tr>
						<td colspan="9" rowspan="7" style="vertical-align:bottom!important;">
							<table style="border-collapse:collapse; border:1px solid #eee; float:left; font-size:12px; bottom:0;" border="1">
								<tr>
									<td colspan="2" class="headcenter thead">BANK DETAILS</td>
								</tr>
								<tr>
									<td class="headleft">A/C HOLDER NAME</td>
									<td class="headleft">{{$company->accountholder}}</td>
								</tr>
								<tr>
									<td class="headleft">A/C NUMBER</td>
									<td class="headleft">{{$company->accountnumber}}</td>
								</tr>
								<tr>
									<td class="headleft">BANK NAME</td>
									<td class="headleft">{{$company->bankname}}</td>
								</tr>
								<tr>
									<td class="headleft">IFSC CODE</td>
									<td class="headleft">{{$company->ifsccode}}</td>
								</tr>
							</table>
							<table style="border-collapse:collapse; border:1px solid #eee; width:300px; float:right;" border="1">
								<tr>
									<td colspan="6" class="headcenter thead">TAX DETAILS</td>
								</tr>
								<tr>
									<td class="headcenter thead">MRP</td>
									<td class="headcenter thead">AMOUNT</td>
									<td class="headcenter thead">SGST%</td>
									<td class="headcenter thead">SGST</td>
									<td class="headcenter thead">CGST%</td>
									<td class="headcenter thead">CGST</td>
								</tr>
								@foreach($tax as $tax)
								<tr>
									<td class="headcenter">{{number_format($tax->mrp,'2','.','')}}</td>
									<td class="headcenter">{{number_format($tax->taxable,'2','.','')}}</td>
									<td class="headcenter">{{number_format($tax->gst/2,'2','.','')}}</td>
									<td class="headcenter">{{number_format($tax->gstvalue/2,'2','.','')}}</td>
									<td class="headcenter">{{number_format($tax->gst/2,'2','.','')}}</td>
									<td class="headcenter">{{number_format($tax->gstvalue/2,'2','.','')}}</td>
								</tr>
								@endforeach
							</table>
						</td>
						<td class="headleft thead" colspan="2">TOTAL</td>
						<td class="headright thead">{{number_format($data->taxable-$data->freight-$data->packing,'2','.','')}}</td>
					</tr>
					<tr>
						<td class="headleft thead" colspan="2">FREIGHT</td>
						<td class="headright thead">{{number_format($data->freight,'2','.','')}}</td>
					</tr>
					<tr>
						<td class="headleft thead" colspan="2">PACKING</td>
						<td class="headright thead">{{number_format($data->packing,'2','.','')}}</td>
					</tr>
					<tr>
						<td class="headleft thead" colspan="2" nowrap>TAXABLE AMOUNT</td>
						<td class="headright thead">{{number_format($data->taxable,'2','.','')}}</td>
					</tr>
					<tr>
						<td class="headleft thead" colspan="2" nowrap>TAX AMOUNT</td>
						<td class="headright thead">{{number_format($data->gstvalue,'2','.','')}}</td>
					</tr>
					<tr>
						<td class="headleft thead" colspan="2" nowrap>ROUND UP</td>
						<td class="headright thead">{{number_format($data->roundup,'2','.','')}}</td>
					</tr>
					<tr>
						<td class="headleft thead" colspan="2" nowrap>INVOICE AMOUNT</td>
						<td class="headright thead">{{number_format($data->netamount,'2','.','')}}</td>
					</tr>
				</table>
				</td>
			</tr>
			<tr style="height:20px;">
				<td colspan="2" class="headleft" style="border:1px solid #eee; border-top:none;"><b>AMOUNT IN WORDS :</b> {{strtoupper($inwords)}} ONLY</td>
			</tr>
			<tr style="height:20px;">
				<td colspan="2" class="notes">
					<b>Notes :</b><br>
					@php $ind=0; @endphp
					@foreach($notes as $not)
					@php $ind=$ind+1; @endphp
					{{$ind}}) {{$not->notes}}<br>
					@endforeach
				</td>
			</tr>
			<tr style="height:20px;">
				<td colspan="2" class="declaration"><b>Declaration :</b>{{$declaration->declaration}}</td>
			</tr>
			<tr>
				<td colspan="2"></td>
			</tr>
			<tr style="height:70px;">
				<td class="label headright" style="vertical-align:middle;" colspan="2"><b>FOR,</b> {{$company->companyname}}</td>
			</tr>
		</table>
	</td>
</tr>
</table>
</center>
</body>
</html>