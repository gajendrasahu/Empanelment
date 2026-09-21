@php
	$i = 1;
	$duration	=	[];
	$duration[1]	=	'Today';
	$duration[2]	=	'Last Day';
	$duration[3]	=	'Last Week';
	$duration[4]	=	'Older';
@endphp
<tr class="myheadbg">
	<td>
        <div class="notice-row">
            <label><input type="checkbox" class="notice_check" name="notices[]" value="1"> Latest EoI Requests</label>
            <label><input type="checkbox" class="notice_check" name="notices[]" value="2"> Approved EoI Requests</label>
            <label><input type="checkbox" class="notice_check" name="notices[]" value="3"> Pre-Bid Queries</label>
            <label><input type="checkbox" class="notice_check" name="notices[]" value="4"> Pre-Bid Responses</label>
            <label><input type="checkbox" class="notice_check" name="notices[]" value="5"> EoI Participation by Firms</label>
            <label><input type="checkbox" class="notice_check" name="notices[]" value="6"> Upcoming Dates</label>
        </div>
	</td>
</tr>
<tr>
	<td>
<div class="notice-container">
	<div class="notice-board-window" data-notice="1">
		<h1 class="notice-board-heading">Latest EoI Requests</h1>
		<div class="notice-list">
			@foreach($eois as $eoi)
			<div class="notice-item">
				<span class="notice-icon"><i class="fa fa-bullhorn"></i></span>
				<div class="notice-text">
					<p class="notice-content">{{ $eoi->name }} requested a new EoI.</p>
					<span class="notice-date">{{ date('d\-m\-Y, h:i A',strtotime($eoi->creationdate)) }}</span><br>
					@if($eoi->duration==1)
					<span class="notice-today">{{$duration[$eoi->duration]}}</span>
					@elseif($eoi->duration==2)
					<span class="notice-lastday">{{$duration[$eoi->duration]}}</span>
					@elseif($eoi->duration==3)
					<span class="notice-lastweek">{{$duration[$eoi->duration]}}</span>
					@else
					<span class="notice-old">{{$duration[$eoi->duration]}}</span>
					@endif
				</div>
			</div>
			@endforeach
		</div>
	</div>	

	<div class="notice-board-window" data-notice="2">
		<h1 class="notice-board-heading">Approved EoI Requests</h1>

		<div class="notice-list">
			@foreach($approved as $approve)
			<div class="notice-item">
				<span class="notice-icon"><i class="fa fa-check"></i></span>
				<div class="notice-text">
					<p class="notice-content">{{ $approve->name }} approved the EoI: {{ $approve->projecttitle }}.</p>
					<span class="notice-date">{{ date('d\-m\-Y, h:i A',strtotime($approve->acceptancedatetime)) }}</span><br>
					@if($approve->duration==1)
					<span class="notice-today">{{$duration[$approve->duration]}}</span>
					@elseif($approve->duration==2)
					<span class="notice-lastday">{{$duration[$approve->duration]}}</span>
					@elseif($approve->duration==3)
					<span class="notice-lastweek">{{$duration[$approve->duration]}}</span>
					@else
					<span class="notice-old">{{$duration[$approve->duration]}}</span>
					@endif
					
				</div>
			</div>
			@endforeach
		</div>
	</div>	

	<div class="notice-board-window" data-notice="3">
		<h1 class="notice-board-heading">Pre-Bid Queries</h1>

		<div class="notice-list">
			@foreach($prebids as $prebid)
			<div class="notice-item">
				<span class="notice-icon"><i class="fa fa-question"></i></span>
				<div class="notice-text">
					<p class="notice-content">Pre-bid query raised by {{ $prebid->companyname }} for the EoI: {{ $prebid->projecttitle }}.</p>
					<span class="notice-date">{{ date('d\-m\-Y, h:i A',strtotime($prebid->raisedon)) }}</span><br>
					@if($prebid->duration==1)
					<span class="notice-today">{{$duration[$prebid->duration]}}</span>
					@elseif($prebid->duration==2)
					<span class="notice-lastday">{{$duration[$prebid->duration]}}</span>
					@elseif($prebid->duration==3)
					<span class="notice-lastweek">{{$duration[$prebid->duration]}}</span>
					@else
					<span class="notice-old">{{$duration[$prebid->duration]}}</span>
					@endif
				</div>
			</div>
			@endforeach
		</div>
	</div>	

	<div class="notice-board-window" data-notice="4">
		<h1 class="notice-board-heading">Pre-Bid Responses</h1>

		<div class="notice-list">
			@foreach($responses as $response)
			<div class="notice-item">
				<span class="notice-icon"><i class="fa fa-reply"></i></span>
				<div class="notice-text">
					<p class="notice-content">{{ $response->fromname }} has responded to the pre-bid query for the EoI: {{ $response->projecttitle }}.</p>
					<span class="notice-date">{{ date('d\-m\-Y, h:i A',strtotime($response->creationdate)) }}</span><br>
					@if($response->duration==1)
					<span class="notice-today">{{$duration[$response->duration]}}</span>
					@elseif($response->duration==2)
					<span class="notice-lastday">{{$duration[$response->duration]}}</span>
					@elseif($response->duration==3)
					<span class="notice-lastweek">{{$duration[$response->duration]}}</span>
					@else
					<span class="notice-old">{{$duration[$response->duration]}}</span>
					@endif
				</div>
			</div>
			@endforeach
		</div>
	</div>	

	<div class="notice-board-window" data-notice="5">
		<h1 class="notice-board-heading">EoI Participation by Firms</h1>

		<div class="notice-list">
			@foreach($participations as $participation)
			<div class="notice-item">
				<span class="notice-icon"><i class="fa fa-reply"></i></span>
				<div class="notice-text">
					<p class="notice-content">{{ $participation->companyname }} has participated in the EoI: {{ $participation->projecttitle }}.</p>
					<span class="notice-date">{{ date('d\-m\-Y',strtotime($participation->uploaded_at)) }}</span><br>
					@if($participation->duration==1)
					<span class="notice-today">{{$duration[$participation->duration]}}</span>
					@elseif($participation->duration==2)
					<span class="notice-lastday">{{$duration[$participation->duration]}}</span>
					@elseif($participation->duration==3)
					<span class="notice-lastweek">{{$duration[$participation->duration]}}</span>
					@else
					<span class="notice-old">{{$duration[$participation->duration]}}</span>
					@endif
				</div>
			</div>
			@endforeach
			@if($participations->count()==0)
			<div class="notice-item">
				<div class="notice-text">
					<p class="notice-content">No record found</p>
				</div>
			</div>
			@endif
		</div>
	</div>	

	<div class="notice-board-window" data-notice="6">
		<h1 class="notice-board-heading">Upcoming Dates</h1>

		<div class="notice-list">
			@foreach($dates as $date)
			<div class="notice-item">
				<span class="notice-icon"><i class="fa fa-calendar"></i></span>
				<div class="notice-text">
					<p class="notice-content">{{ $date->projecttitle }}</p>
					<span class="notice-date">{{ $date->notice_type }}</span><br>
					<span class="notice-date">{{ date('d\-m\-Y',strtotime($date->notice_date)) }}</span><br>
					<span class="notice-today">@if($date->remainingdays!=0){{ $date->remainingdays }} day(s) left @else Today @endif</span>
				</div>
			</div>
			@endforeach
			@if(count($dates)==0)
			<div class="notice-item">
				<div class="notice-text">
					<p class="notice-content">No record found</p>
				</div>
			</div>
			@endif
		</div>
	</div>	

</div>

	</td>
</tr>



<script>
$(document).on('change', '.notice_check', function(){

    var selected = [];

    $('.notice_check:checked').each(function(){
        selected.push($(this).val());
    });

    if(selected.length === 0){
        $('.notice-board-window').show();
    } else {
        $('.notice-board-window').hide();

        selected.forEach(function(val){
            $('.notice-board-window[data-notice="'+val+'"]').show();
        });
    }

});
</script>