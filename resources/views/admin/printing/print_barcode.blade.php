<html>
<title>BARCODE PRINTING</title>
<head></head>
<body>
@for($i=0;$i<$barcode->quantity;$i++)
<div style="text-align:left; width:150px; height:60px; border:1px solid #eee; vertical-align:middle!important;">
@if($barcode->pname==1)
<span style="font-size:10px;">{{substr($barcode->productname,0,25)}}</span><br>
@endif
<img src="{{ asset('storage/'.$barcode->barcodeurl) }}" alt="Image" style="width:150px; position:relative;"><br>
<span style="width:100%; position:relative; font-size:10px; letter-spacing:11px;">{{$barcode->barcode}}</span><br>
<span style="font-size:10px;">
@if($barcode->pmrp==1) MRP-{{$barcode->mrp}} INR @endif @if($barcode->sp==1) SP-{{$barcode->salesprice}} INR @endif
</span>
<br>
</div>
@endfor
</body>
</html>