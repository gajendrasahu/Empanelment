<table style="width:100%; border-collapse:collapse; margin-top:10px;" border="1">
	@if($data->page_indexing!='')
	<tr>
		<td colspan="2" class="padding-8 form-label head-bg"><b>Table of Contents</b></td>
	</tr>
	<tr>
		<td colspan="2" class="padding-8">{!! $data->page_indexing !!}</td>
	</tr>
	@endif
</table>
@if($data->chips_objective!='')
<table style="width:100%; border-collapse:collapse; margin-top:5px;" border="1">
	@if($data->eoistatus==1)
	<tr>
		<td colspan="2" class="padding-8" style="border-bottom:1px solid #fff; text-align:center;">
			Expression of Interest (EOI) <b>{{$data->engagementname}}</b>
			<br><br>
			<span style="float:left;">Ref. No. @if($data->eoinumber) {{$data->eoinumber}} @endif</span>
			<span style="float:right;">Dated : @if($data->releasedate) {{date('d\-m\-Y',strtotime($data->releasedate))}} @else dd-mm-YYYY @endif</span>
		</td>
	</tr>
	@endif
	<tr>
		<td colspan="2" class="padding-8">{!! $data->chips_objective !!}</td>
	</tr>
</table>
@endif