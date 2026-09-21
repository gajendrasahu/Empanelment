@php
$i=1;
$t=0;
use Carbon\Carbon;
@endphp
@foreach($data as $record)
<tr style="background-color:white!important;">
	<td class="center">{{date('d',strtotime($record->work_date))}}</td>
	<td class="center">{{Carbon::now()->setISODate(now()->year, now()->isoWeek(), $record->work_day)->format('D');}}</td>
	<td class="center">{{$record->status}}</td>
	<td style="padding:2px!important;">
		@if($record->status!='A' && $record->status!='H')
		<textarea name="work_detail[]" class="width-full" placeholder="Enter work details for this date"></textarea>
		@else
		<input type="hidden" name="work_detail[]" class="width-full">
		@endif
	</td>
	<td style="padding:4px!important;">
		<button type="button" class="btn myfrmbtn" style="border-radius:0px!important;">Update</button>
	</td>
</tr>
@endforeach
