<table style="width:100%; border-collapse:collapse; margin-top:10px;" border="1">
	@if($data->projectobjective!='')
	<tr>
		<td colspan="2" class="padding-8 form-label head-bg"><b>Introduction / Objective</b></td>
	</tr>
	<tr>
		<td colspan="2" class="padding-8">{!! $data->projectobjective !!}</td>
	</tr>
	@endif
	@if($data->aboutproject!='')	
	<tr>
		<td colspan="2" class="padding-8 form-label head-bg"><b>About Project</b></td>
	</tr>
	<tr>
		<td colspan="2" class="padding-8">{!! $data->aboutproject !!}</td>
	</tr>
	@endif
	<tr>
		<td colspan="2" class="padding-8 form-label head-bg"><b>Scope of Work</b></td>
	</tr>
	<tr>
		<td colspan="2" class="padding-8">{!! $data->scopeofwork !!}</td>
	</tr>
</table>
