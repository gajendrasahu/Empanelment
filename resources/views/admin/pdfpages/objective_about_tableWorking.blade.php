@if($data->projectobjective!='')
<table class="pd-10" style="width:100%; bottom:10px;">
	<tr>
		<td colspan="2" class="form-label">
			<b>2. Project Introduction / Objective</b>
		</td>
	</tr>
	<tr>
		<td colspan="2">{!! $data->projectobjective !!}</td>
	</tr>
</table>
@endif
@if($data->aboutproject!='')	
<table class="pd-10" style="width:100%; bottom:10px;">
	<tr>
		<td colspan="2" class="form-label"><b>About Project</b></td>
	</tr>
	<tr>
		<td colspan="2">{!! $data->aboutproject !!}</td>
	</tr>
</table>	
@endif
@if($data->scopeofwork!='')
<table class="pd-10" style="width:100%; bottom:10px;">
	<tr>
		<td colspan="2" class="form-label"><b>3. Scope of Work</b></td>
	</tr>
	<tr>
		<td colspan="2">{!! $data->scopeofwork !!}</td>
	</tr>
</table>
@endif
