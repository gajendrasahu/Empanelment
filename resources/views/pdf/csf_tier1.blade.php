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

</style>
</head>
<body>
  <div class="a4-table-container">
	<table style="width:100%; text-align:center; border:none!important;">
		<tr>
			<td class="center" style="border:none!important; text-align:center;">
				<img src="https://vaarnikaenterprises.com/portal/panel/assets/images/basic/chips-label.png" style="border:none!important; width:80%!important; margin:10px auto;">
			</td>
		</tr>
		<tr style="height:100px; border:none; ">
			<td style="width:100%; height:100px; border:none; text-align:center; vertical-align:middle; padding:50px 0px; font-size:24px;">
				Expression of Interest (EOI)<br>
				for The Project Title<br>{{ $data->projecttitle }}
			</td>
		</tr>

		<tr style="height:50px; border:none; ">
			<td style="width:100%; height:50px; border:none; text-align:center; vertical-align:middle;">
				<img src="https://vaarnikaenterprises.com/portal/panel/assets/images/basic/logo.png" style="border:none!important; margin:50px auto;">
				<br>
				Chhattisgarh Infotech Promotion Society<br>
(CHiPS), State Data Center Building, Near<br>
Police Control Room, Civil Lines,<br>
Raipur, Chhattisgarh-492001.<br>Email- ceochips@nic.in

			</td>
		</tr>
	</table>
  </div>
  <br>
<table style="width:100%; border-collapse:collapse;" border="1">
	<tr>
		<td colspan="2" class="padding-8 form-label head-bg"><b>Project Detail</b></td>
	</tr>

	<tr>
		<td class="padding-8 form-label">EOI Number</td>
		<td class="padding-8">{{ $data->eoinumber }}</td>
	</tr>
	<tr>
		<td class="padding-8 form-label">Requested On</td>
		<td class="padding-8">{{ date('d\-m\-Y, h:i A',strtotime($data->creationdate)) }}</td>
	</tr>
	<tr>
		<td class="padding-8 form-label">Project Duration</td>
		<td class="padding-8">{{ $data->projectduration }} Months</td>
	</tr>
</table>
<div style="width:100%; text-align:center;">
***End of Document***
</div>

</body>
</html>