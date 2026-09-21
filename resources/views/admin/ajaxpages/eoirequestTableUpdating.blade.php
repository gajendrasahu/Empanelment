@php
$i = 1;
$eoistatus	=	"";
$now = \Carbon\Carbon::now();

$eoi_status[0]	=	'New Request';
$eoi_status[1]	=	'Drafted';
$eoi_status[2]	=	'Approved';
$eoi_status[3]	=	'Published';
$eoi_status[4]	=	'Published & Pre-Bid Stage Closed';
$eoi_status[5]	=	'Published & Submission Stage Closed';
$eoi_status[6]	=	'Published – Pre-Bid Stage';
$eoi_status[7]	=	'Pre-Bid Queries Raised – Stage Closed';
$eoi_status[8]	=	'No Participation Received by Submission Deadline';
$eoi_status[9]	=	'Participation Ongoing';
$eoi_status[10]	=	'Closed (Participation Received) – Awaiting Interview Scheduling';

@endphp
@foreach ($data as $item)
	@php
	$serial = $i++;
	if($item->eoistatus==0)
	{
		$eoistatus	=	$eoi_status[0];
	}
	else if($item->eoistatus==1)
	{
		$eoistatus	=	$eoi_status[1];
	}
	else if($item->eoistatus==2)
	{
		if(!empty($item->releasedate) && \Carbon\Carbon::parse($item->releasedate)->lt(\Carbon\Carbon::today()))
		$eoistatus	=	$eoi_status[3];
		else if(!empty($item->releasedate) && \Carbon\Carbon::parse($item->releasedate)->gte(\Carbon\Carbon::today()))
		$eoistatus	=	$eoi_status[2];
		else
		$eoistatus	=	$eoi_status[2];
	}
	else if($item->eoistatus==3)
	{
		$eoistatus	=	$eoi_status[3];
		if($item->isinprebid==0 && \Carbon\Carbon::parse($item->prebidlastdate)->lt(\Carbon\Carbon::today()))
		{
			$eoistatus	=	$eoi_status[4];
		}
		else if($item->isinprebid==0 && \Carbon\Carbon::parse($item->deadlinedate)->lt(\Carbon\Carbon::today()))
		{
			$eoistatus	=	$eoi_status[5];
		}
		else if($item->isinprebid==1 && \Carbon\Carbon::parse($item->prebidlastdate)->gte(\Carbon\Carbon::today()))
		{
			$eoistatus	=	$eoi_status[6];
		}
		else if($item->isinprebid==1 && \Carbon\Carbon::parse($item->prebidlastdate)->lt(\Carbon\Carbon::today()))
		{
			$eoistatus	=	$eoi_status[7];
		}
		if(\Carbon\Carbon::parse($item->deadlinedate)->lt(\Carbon\Carbon::today()))
		{
			$eoistatus	=	$eoi_status[8];
		}
	}
	else if($item->eoistatus==4 && $item->isordered==0)
	{
		if(\Carbon\Carbon::parse($item->deadlinedate)->gt(\Carbon\Carbon::today()))
		{
			$eoistatus	=	$eoi_status[9];
		}
		else
		{
			$eoistatus	=	$eoi_status[10];
		}
	}
	@endphp
	<tr class="eoi-row" id="item-{{ $serial }}">
		<td class="padding-10 eoi-card-cell" colspan="3">
			<div class="eoi-card" style="position:relative;">
				<div class="eoi-header">
					<div class="eoi-main">
						<div class="eoi-number-line mt-10" style="display:flex; justify-content:space-between; align-items:center;">
							<div style="display:flex; gap:10px;">
								<div class="eoi-sno" style="margin-top:2px;">{{ $serial }}.</div>
								<div class="eoi-project">{{ $item->projecttitle }}</div>
							</div>
						</div>
						<div class="mt-10" style="margin-left:20px; font-weight: 400; font-size: 12px; color: #6b7280;">
							{{ $item->eoinumber }} | @if($item->tier_choice==1) Tier - 1 @elseif($item->tier_choice==2) Tier - 2 @else Both Tier @endif | Department / Project Manager : {{$item->departmentname}}
						</div>
					</div>
					<!-- BUTTONS -->
				</div>
				<div class="eoi-timeline" style="display:flex; align-items:center; margin-bottom:30px;">
					<div class="timeline-step is-done">
						<div class="timeline-icon">
							<i class="fa fa-check timeline-check-icon"></i>
						</div>
						<div class="timeline-title">Requested on</div>
						<div class="timeline-date">{{date('d\-m\-Y',strtotime($item->creationdate))}}</div>
					</div>
					<div class="timeline-step is-done">
						<div class="timeline-icon">
							<i class="fa @if(!empty($item->senton)) fa-check @else fa-clock-o @endif timeline-check-icon"></i>
						</div>
						<div class="timeline-title">Draft Date</div>
						<div class="timeline-date">
							{{ !empty($item->senton) ? date('d-m-Y', strtotime($item->senton)) : 'TBD' }}
						</div>
					</div>
					<div class="timeline-step is-done">
						<div class="timeline-icon">
							<i class="fa fa-check timeline-check-icon"></i>
						</div>
						<div class="timeline-title">Approved</div>
						<div class="timeline-date">
							{{ !empty($item->acceptancedatetime) ? date('d-m-Y', strtotime($item->acceptancedatetime)) : 'TBD' }}
						</div>
					</div>
					<div class="timeline-step is-done">
						<div class="timeline-icon">
							<i class="fa fa-check timeline-check-icon"></i>
						</div>
						<div class="timeline-title">EoI Release Date</div>
						<div class="timeline-date">
							{{ !empty($item->releasedate) ? date('d-m-Y', strtotime($item->releasedate)) : 'TBD' }}
						</div>
					</div>
					<div class="timeline-step is-done">
						<div class="timeline-icon">
							<i class="fa fa-check timeline-check-icon"></i>
						</div>
						<div class="timeline-title">Pre-bid Query Deadline</div>
						<div class="timeline-date">
							{{ !empty($item->prebidlastdate) ? date('d-m-Y', strtotime($item->prebidlastdate)) : 'TBD' }}
						</div>
					</div>
					<div class="timeline-step is-done">
						<div class="timeline-icon">
							<i class="fa fa-check timeline-check-icon"></i>
						</div>
						<div class="timeline-title">Submission Deadline</div>
						<div class="timeline-date">
							{{ !empty($item->deadlinedate) ? date('d-m-Y, h:i A', strtotime($item->deadlinedate)) : 'TBD' }}
						</div>
					</div>
				</div>
				<span class="bg-info" style="position:absolute; top:0px; right:0px; padding:2px 5px; border-radius:0px 10px 0px 10px; font-weight:500; color:#000;">EoI Status : {{$eoistatus}}</span>

				<a class="eoi-btn action-a view-more" onclick="viewMoreData('{{route('more.eoidata')}}','{{Crypt::encrypt($item->requestid)}}')" style="outline:none; cursor:pointer; position:absolute; right:30px; bottom:5px;">
					<i class="fa fa-info-circle"></i> View More
				</a>
				
			</div>
		</td>
	</tr>
@endforeach
@if($i == 1)
	<tr>
		<td class="padding-10" colspan="3" style="text-align:center;">
			<br>
			<i class="fa fa-warning blue nodata"></i><br>
			{!! __('messages.sorry') !!}
		</td>
	</tr>
@else
	<tr>
		<td colspan="3" style="text-align:right;">
			{{ $data->links('vendor.pagination.default') }}
		</td>
	</tr>
@endif

<script>
	const paginationLinks = document.querySelectorAll('.pagination a');
	paginationLinks.forEach(link => {
		link.onclick = function (event) {
			event.preventDefault(); // Prevent default link behavior
			const page = this.getAttribute('href').split('page=')[1]; // Extract page number from URL
			const url = '{{ route('eoirequest.html') }}'; // URL generated by Laravel route
			loadData(page, url); // Call custom function with page number and URL
		};
	});


$('.sendto-approval').on('click', function () {
	
    var docUrl = $(this).data('url');
    var params = {
    };
	
    $(".modal-xl").css("width", "90%");
    $('#approvalModal').modal('show');
	$('#approvalData').html("Please wait...");
    $.get(docUrl, params, function (data) {
        setTimeout(function () {
            $('#approvalData').html(data.approvalform);
        }, 2000);
    }).fail(function () {
        $('#approvalData').html('<p class="text-danger">Failed to load EoI.</p>');
    });
	
});
	
</script>
