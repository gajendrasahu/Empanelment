<table style="width:100%; border-collapse:collapse; margin-top:10px;" border="1">
	@if($data->anyother!='')
	<tr>
		<td colspan="2" class="padding-8 form-label head-bg"><b>Other Information (If Any)</b></td>
	</tr>
	<tr>
		<td colspan="2" class="padding-8">{!! $data->anyother !!}</td>
	</tr>
	@endif
</table>