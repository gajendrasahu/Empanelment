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
        .total { text-align: right; margin-top: 20px; }
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
        <p>Receipt #:1</p>
    </div>

    <p>Customer: Gajendra</p>

    <table class="table">
        <thead>
            <tr>
                <th>S.No.</th>
				<th>Description</th>
                <th>Qty</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
		
                <tr>
                    <td>1</td>
					<td>Description</td>
                    <td>1</td>
                    <td>400</td>
                </tr>
        </tbody>
    </table>

    <div class="total">
        <h4>Total: 400</h4>
    </div>
</body>
</html>