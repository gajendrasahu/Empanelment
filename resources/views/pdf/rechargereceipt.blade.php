<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Receipt</title>
    <style>
        body { font-family: sans-serif; }
        .header { text-align: center; margin-bottom: 30px; }
        .company {
            font-weight: bold;
            font-size: 18px;
        }
        .contact {
            font-size: 12px;
            margin-bottom: 20px;
        }
        .table { width: 100%; border-collapse: collapse; }
        .table th, .table td { border: 1px solid #000; padding: 8px; }
        .table th { background-color: #f2f2f2; }
        .total { text-align: right;}
		.height { height:20px!important;}

    .footer {
        position: fixed;
        bottom: 0;
        left: 0;
        right: 0;
        height: 40px;
        font-size: 10px;
        text-align: center;
        border-top: 1px solid #000;
        padding-top: 5px;
    }
	
	.signature
	{
		font-size:14px;
		font-weight:bold;
	}
    </style>
</head>
<body>
	<table style="width:100%;">
		<thead>
		<tr>
			<td>
				<div class="company">TSD SOLUTIONS PRIVATE LIMITED</div>
				<div class="contact">
					4th Floor , Above PNB bank katoratalab , Raipur , Chhattisgarh<br>
					Pincode : 492001<br>
					Phone No - 8815586006
				</div>
				
			</td>
			<td style="vertical-align:top; float:right;">
				<img src="https://admin.thescrewdriver.in/panel/assets/images/basic/logoscrew.jpg" alt="Logo" style="width:150px; float:right;">
			</td>
		</tr>
		</thead>
		<tbody>
		<tr>
			<td style="vertical-align:top;">
				<div class="company">RECEIVED FROM</div>
				{{ $vendor->name }} @if($vendor->middlename!=''){{$vendor->middlename}}@endif @if($vendor->lastname!=''){{$vendor->lastname}}@endif<br>
				{{$vendor->completeaddress}}<br>
				Phone No : {{$vendor->mobilenumber}}<br>Email : {{$vendor->email}}
			</td>
			<td style="float:right; vertical-align:top;" nowrap>
				Receipt No &nbsp;&nbsp;&nbsp;: {{$receipt->receiptnumber}}<br>
				Receipt Date : {{date('d\-m\-Y',strtotime($receipt->paymentdatetime))}}
			</td>
		</tr>
		<tr>
			<td colspan="2" style="vertical-align:top;">
				<table class="table">
					<tr class="height">
						<th style="text-align:center; width:50px;">S.NO.</th>
						<th style="text-align:left; padding:0px 2px;">DESCRIPTION</th>
						<th style="text-align:right; width:100px;">AMOUNT</th>
					</tr>
					<tr>
						<td class="height" style="text-align:center;">1</td>
						<td class="height" style="text-align:left; padding:0px 2px;">WALLET RECHARGE</td>
						<td class="height" style="text-align:right; padding:0px 2px;">{{ number_format($receipt->cramount, 2) }}</td>
					</tr>
				</table>
			</td>
		</tr>
		<tr>
			<td class="height" colspan="2">Terms & Condition</td>
		</tr>
		</tbody>
	</table>
    <div class="footer">
        <div style="float:right;"><span class="signature">Authorized Signature</span><br>THIS IS COMPUTER GENERATED INVOICE SIGNATURE IS NOT REQUIRED</div>
    </div></body>
</html>