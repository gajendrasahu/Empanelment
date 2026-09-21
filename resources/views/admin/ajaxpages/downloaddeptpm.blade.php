@php
$i=1;
use Carbon\Carbon;
@endphp
<table class="pd-8" border="1" style="width:100%!important; border:1px solid #ddd; border-collapse:collapse;">
	<tr class="myheadbg"><td colspan="5" style="padding:5px!important; border:1px solid #ddd;">Project Name : {{$eoi->projecttitle}}</td></tr>
	<tr class="myheadbg"><td colspan="5" style="padding:5px!important; border:1px solid #ddd;">EoI Number : {{$eoi->eoinumber}}</td></tr>
	<tr>
		<td style="padding:5px!important; width:30px; text-align:center; border:1px solid #ddd;">S.No.</td>
		<td style="padding:5px!important; border:1px solid #ddd;" nowrap>Clause Reference</td>
		<td style="padding:5px!important; border:1px solid #ddd;" nowrap>Clause Detail</td>
		<td style="padding:5px!important; border:1px solid #ddd;" nowrap>Queries with Justification</td>
		<td style="padding:5px!important; border:1px solid #ddd;" nowrap>Response</td>
	</tr>
@foreach($data as $record)
	<tr>
		<td style="padding:5px!important; border:1px solid #ddd; text-align:center;" class="center">{{$loop->iteration}}</td>
		<td style="padding:5px!important; border:1px solid #ddd;">{!!$record->clause_reference!!}</td>
		<td style="padding:5px!important; border:1px solid #ddd;">{!!$record->clause_detail!!}</td>
		<td style="padding:5px!important; border:1px solid #ddd;">{!!$record->queries_with_justification!!}</td>
		<td style="padding:5px!important; border:1px solid #ddd;">{!!$record->answer!!}</td>
	</tr>
@endforeach
</table>
	
