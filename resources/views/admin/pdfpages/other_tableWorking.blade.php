@if($data->anyother!='')
<table class="pd-10" style="width:100%; bottom:10px;">
	<tr>
		<td colspan="2" class="form-label">
			<b>5. Other Information</b>
		</td>
	</tr>
	<tr>
		<td colspan="2">{!! $data->anyother !!}</td>
	</tr>
	
</table>
@endif