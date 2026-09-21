<!DOCTYPE html>
<html>
<head>
    <title>EoI</title>
<style>
.padding-5
{
	padding:5px;
}
.padding-8
{
	padding:8px;
}
.form-label
{
	font-weight:500;
	color:#000000;
}
.center
{
	text-align:center;
}
.head-bg
{
	background-color:#e4e6e9;
}
td
{
	vertical-align:top!important;
}

.a4-table-container {
  width: 8.27in;
  height: 11.69in;
  background-image: url('https://empl.cgstate.gov.in/panel/assets/images/basic/template-bg.png');
  background-repeat: no-repeat;
  background-position: top left;
  background-size: auto 100%;
  border:none!important;
}

.a4-table-container table, 
.a4-table-container th, 
.a4-table-container td {
  border: 1px solid #333;
}

.a4-table-container th, 
.a4-table-container td {
  padding: 8px;
  text-align: left;
}
.address
{
	width:100%;
	background-color:#1f487c;
	color:white;
	text-align:center;
	height: 40%;
	border: 1px solid #000;
	display: flex;
	align-items: flex-end;	
}
.expression
{
	
}
.page-break {
 page-break-after: always; 
}

.pd-10 td
{
	padding:10px!important;
}
.pd-5 td
{
	padding:5px!important;
}
.border td
{
	border: 1px solid #eee;
	border-collapse: collapse;		
}


</style>
</head>
<body>
<table style="width:100%; height:10.59in; text-align:center; border:none!important; background-color:#95b3d7;">
	<tr style="height:100%;">
		<td style="width:50%;"></td>
		<td style="width:40%; background-color:white; padding:10px; text-align:left;">
			<div class="address">
				<p style="vertical-align:bottom;">Chhattisgarh Infotech Promotion Society
					(CHiPS), State Data Centre Building, Near
					Police Control Room, Civil Lines, Raipur,
					Chhattisgarh–492001<br>Tel.: +91-771-
					4014158<br>Email: ceochips@nic.in				
				</p>
			</div>
			<div class="expression" style="height:60%!important; background-color:white;">
			<br>
			<span style="font-size:30px; color:#4f80bb;">Expression of Interest<br>(EoI)</span><br><br>
			<span style="font-size:16px; font-weight:700; line-height:30px;"><br>{{$data->projecttitle}}</span>
			</div>
		</td>
		<td style="width:10%;"></td>
	</tr>
</table>
@if($data->page_indexing)
	@include('admin.pdfpages.page_indexing_table')
	<div class="page-break"></div>
@else
	<div class="page-break"></div>
@endif

@include('admin.pdfpages.factsheet_table')
<div class="page-break"></div>
@include('admin.pdfpages.objective_about_table')
@if($tier_html!='')
{!!$tier_html!!}
@endif

@include('admin.pdfpages.other_table')
@include('admin.pdfpages.evaluation_table')
<div style="width:100%; text-align:center;">
***End of Document***
</div>
<script src="{{ asset('panel/assets/js/datatablePaginationWithFilter.js') }}"></script>
<script>
document.querySelectorAll('.format-indian').forEach(function(el) {
    el.innerText = formatIndianNumber(el.dataset.value);
});

</script>

<script type="text/php">
if (isset($pdf)) {
    $x = 270;
    $y = 820;
    $text = "Page {PAGE_NUM} of {PAGE_COUNT}";
    $font = $fontMetrics->get_font("Arial", "normal");
    $size = 10;
    $color = array(0,0,0);

    $pdf->page_text($x, $y, $text, $font, $size, $color);
}
</script>
</body>
</html>