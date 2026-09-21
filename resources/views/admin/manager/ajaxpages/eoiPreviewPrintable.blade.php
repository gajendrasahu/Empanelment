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
  background-image: url('https://vaarnikaenterprises.com/portal/panel/assets/images/basic/template-bg.png');
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
	height: 300px;          /* required */
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
</style>
</head>
<body>
<center>
<table style="width:8.27in; height:11.69in!important; text-align:center; border:none!important; background-color:#95b3d7;">
	<tr>
		<td style="width:40%;">&nbsp;</td>
		<td style="width:50%; background-color:white; padding:10px; text-align:left;">
			<div class="address">
<p style="vertical-align:bottom;">Chhattisgarh Infotech Promotion Society
(CHiPS), State Data Centre Building, Near
Police Control Room, Civil Lines, Raipur,
Chhattisgarh–492001<br>Tel.: +91-771-
4014158<br>Email: ceochips@nic.in				
</p>
			</div>
			<div class="expression">
			<br>
			<span style="font-size:30px; color:#4f80bb;">Expression of Interest<br>(EoI)</span><br><br>
			<span style="font-size:30px; font-weight:700; line-height:40px;"><br>{{$data->projecttitle}}</span>
			</div>
		</td>
		<td style="width:10%;">&nbsp;</td>
	</tr>
</table>

<div class="page-break"></div>
<table style="width:8.27in; height:10.5in!important; text-align:left; border:none!important;">
<tr><td>

@include('admin.viewpages.page_indexing_table')
@include('admin.viewpages.factsheet_table')
@include('admin.viewpages.objective_about_table')


@if($tier1_html!=''){!!$tier1_html!!}@endif

@include('admin.viewpages.evaluation_table')
<div style="width:100%; text-align:center;">
***End of Document***
</div>
</td></tr></table>
</center>

<script src="{{ asset('panel/assets/js/datatablePaginationWithFilter.js') }}"></script>
<script>
document.querySelectorAll('.format-indian').forEach(function(el) {
    el.innerText = formatIndianNumber(el.dataset.value);
});

</script>

</body>
</html>