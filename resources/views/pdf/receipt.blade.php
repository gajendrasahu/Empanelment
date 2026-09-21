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
    </style>
</head>
<body>
    <div class="header">
        <div class="company">TSD SOLUTIONS PRIVATE LIMITED</div>
        <div class="contact">
            C/o Sunil Jagyasi Amlidih, Piush Colony, Mana D/c, Raipur, Chattisgarh, India, 492001<br>
            Email: info@thescrewdriver.in | Phone: +91 9775 655579
        </div>
        <h2>Receipt</h2>
        <p>Receipt #: {{ $receipt->receiptnumber }}<br>Date: {{ date('d\-m\-Y h:i A',strtotime($receipt->paymentdatetime)) }}</p>
    </div>

    <p>Customer: {{ $customer->name }} @if($customer->middlename!=''){{$customer->middlename}}@endif @if($customer->lastname!=''){{$customer->lastname}}@endif</p>

    <table class="table">
        <thead>
            <tr>
                <th style="text-align:center;">S.No.</th>
				<th style="text-align:left; padding:0px 2px;">Description</th>
                <th style="text-align:center;">Qty</th>
                <th style="text-align:center;">Subtotal</th>
            </tr>
        </thead>
        <tbody>
			@php $r=0; @endphp
            @foreach ($services as $item)
			@php $r=$r+1; @endphp
                <tr>
                    <td style="text-align:center;">{{ $r }}</td>
					<td style="text-align:left; padding:0px 2px;">{{ $item->servicetitle }}</td>
                    <td style="text-align:center;">{{ $item->quantity }}</td>
                    <td style="text-align:center;">{{ number_format($item->payable, 2) }}</td>
                </tr>
            @endforeach
				<tr>
					<td class="total" colspan="3"><h4>Total</h4></td>
					<td style="text-align:center;"><h4>{{ number_format($receipt->netamount,'2','.','') }}</h4></td>
				</tr>
        </tbody>
    </table>

</body>
</html>