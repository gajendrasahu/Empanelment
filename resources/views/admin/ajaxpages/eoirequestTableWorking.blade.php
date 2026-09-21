@php
	$i = 1;
@endphp
@foreach ($data as $item)
	@php
		$invalidDates = ['0000-00-00', '1970-01-01'];
		$timelineSteps = [
			['label' => 'Requested on', 'date' => $item->creationdate, 'format' => 'd-m-Y'],
			['label' => 'Draft Date', 'date' => $item->senton, 'format' => 'd-m-Y h:i A'],
			['label' => 'Approved by Department', 'date' => $item->acceptancedatetime, 'format' => 'd-m-Y h:i A'],
			['label' => 'Release Date of EoI by CHiPS', 'date' => $item->releasedate, 'format' => 'd-m-Y'],
			['label' => 'Last Date of Pre-bid Query', 'date' => $item->prebidlastdate, 'format' => 'd-m-Y'],
			['label' => 'Last Date for Submission of Proposals', 'date' => $item->deadlinedate, 'format' => 'd-m-Y h:i A'],
			['label' => 'Tentative Date of Presentation and Interview', 'date' => $item->interviewdate, 'format' => 'd-m-Y h:i A'],
		];
		$now = \Carbon\Carbon::now();
		$completedSteps = 0;
		$totalSteps = count($timelineSteps);
		foreach($timelineSteps as $idx => $step)
		{
			$raw = $step['date'] ?? null;
			$isValid = !empty($raw) && !in_array($raw, $invalidDates);
			$dateObj = $isValid ? \Carbon\Carbon::parse($raw) : null;
			$timelineSteps[$idx]['display'] = $isValid ? $dateObj->format($step['format']) : 'TBD';
			$timelineSteps[$idx]['isDone'] = $isValid && $dateObj->lte($now);
			if($timelineSteps[$idx]['isDone'])
			{
				$completedSteps++;
			}
		}
		$timelinePercent = $totalSteps ? round(($completedSteps / $totalSteps) * 100) : 0;

		$flags = [];
		$buttons = [];

		$buttons[] = [
			'label' => 'Send Message',
			'href' => route('communication.message', Crypt::encrypt($item->requestid)),
			'icon' => 'fa-envelope',
		];


		$buttons[] = [
			'label' => 'View File',
			'href' => route('view.uploadedfile', Crypt::encrypt($item->authorizationletter)),
			'icon' => 'fa-hand-o-right',
			'target' => '_blank',
		];

		if ($item->isinprebid) {
			$buttons[] = [
				'label' => 'Pre-bid Enquiry',
				'href' => route('prebidenquiry.list', Crypt::encrypt($item->requestid)),
				'icon' => 'fa-hand-o-right',
			];
		}

		if ($item->eoistatus == 0) {
			if($item->isSecondCall==0)
			{
				$flags[] = ['label' => 'New Request Letter', 'icon' => 'fa-suitcase', 'class' => 'eoi-flag--info'];
			}
			else
			{
				$flags[] = ['label' => 'New Request Letter (Second Call)', 'icon' => 'fa-suitcase', 'class' => 'eoi-flag--info'];
			}
		}

		if ($item->eoistatus >= 1) {
			$buttons[] = [
				'label' => 'Preview',
				'href' => route('eoipreview', Crypt::encrypt($item->requestid)),
				'icon' => 'fa-hand-o-right',
				'target' => '_blank',
			];
		}

		if ($item->eoistatus >= 2 && !Session::get('departmentId') && !Session::get('vendorId'))
		{
			$buttons[] = [
				'label' => 'Edit EoI',
				'href' => route('prepare.draft', Crypt::encrypt($item->requestid)),
				'icon' => 'fa-edit',
			];

			$buttons[] = [
				'label' => 'Update Date',
				'href' => route('update.eoidates', Crypt::encrypt($item->requestid)),
				'icon' => 'fa-edit',
			];
		}

		if($item->isupdated == 0)
		{
			if ($item->eoistatus == 0)
			{
				$buttons[] = [
					'label' => 'Draft EoI',
					'href' => route('prepare.draft', Crypt::encrypt($item->requestid)),
					'icon' => 'fa-edit',
				];
			}
			elseif ($item->eoistatus == 1)
			{
				$flags[] = ['label' => 'Sent To Department', 'icon' => 'fa-paper-plane', 'class' => 'eoi-flag--info'];
			}
			elseif($item->approvalClosed==0 && $item->eoistatus==2)
			{
				if($item->eoistatus>=2)
				{
					if($item->approvedBy)
					{
						$flags[] = ['label' => 'Approved by-'.$item->approvedBy, 'icon' => 'fa-thumbs-up', 'class' => 'eoi-flag--info'];
					}	
				}
				
				$flags[] = ['label' => 'Confirmed','icon' => 'fa-thumbs-up', 'class' => 'eoi-flag--success'];
				if(session('userId')==1)
				{
					$buttons[] = [
						'label' 	=> 	'Request Approval',
						'icon' 		=> 	'fa-hand-o-right',
						'class'		=>	'sendto-approval',
						'href'		=>	'#',
						'data-url'	=>	route('send.approvalform', Crypt::encrypt($item->requestid)),
					];	
				}
			}
			else
			{
				if($item->eoistatus>=2)
				{
					if($item->approvedBy)
					{
						$flags[] = ['label' => 'Approved by-'.$item->approvedBy, 'icon' => 'fa-thumbs-up', 'class' => 'eoi-flag--info'];
					}	
				}
				
				if($item->eoistatus == 2)
				{
					$flags[] = ['label' => 'Confirmed by Department','icon' => 'fa-thumbs-up', 'class' => 'eoi-flag--success'];
					if($item->approvedBy)
					{
						$buttons[] = [
							'label' 	=> 	'Approved',
							'icon' 		=> 	'fa-hand-o-right',
							'class'		=>	'sendto-approval',
							'href'		=>	'#',
							'data-url'	=>	route('send.approvalform', Crypt::encrypt($item->requestid)),
						];
					}
					$buttons[] = [
						'label' => 'Publish EoI',
						'href' => route('prepare.float', Crypt::encrypt($item->requestid)),
						'icon' => 'fa-bullhorn',
					];
				}
				elseif ($item->eoistatus == 3)
				{
					$flags[] = ['label' => 'EoI Published', 'icon' => 'fa-thumbs-up', 'class' => 'eoi-flag--success'];

					if($item->approvedBy)
					{
						$buttons[] = [
							'label' 	=> 	'Approved',
							'icon' 		=> 	'fa-hand-o-right',
							'class'		=>	'sendto-approval',
							'href'		=>	'#',
							'data-url'	=>	route('send.approvalform', Crypt::encrypt($item->requestid)),
						];
					}


					$buttons[] = [
						'label' => 'EoI Published',
						'onclick' =>  "ShowFirms('".Crypt::encrypt($item->requestid)."')",
						'icon' => 'fa-bullhorn',
					];
					
					if($item->notesheet_file!='')
					{
						$buttons[] = [
							'label' => 'Download Notesheet File',
							'href' => route('view.uploadedfile', Crypt::encrypt($item->notesheet_file)),
							'icon' => 'fa-download',
							'target' => '_blank',
						];
					}
					
				}
				elseif ($item->eoistatus == 4)
				{
					if($item->approvedBy)
					{
						$buttons[] = [
							'label' 	=> 	'Approved',
							'icon' 		=> 	'fa-hand-o-right',
							'class'		=>	'sendto-approval',
							'href'		=>	'#',
							'data-url'	=>	route('send.approvalform', Crypt::encrypt($item->requestid)),
						];
					}
					
					$buttons[] = [
						'label' => 'EoI Published',
						'onclick' =>  "ShowFirms('".Crypt::encrypt($item->requestid)."')",
						'icon' => 'fa-bullhorn',
					];
					
				
					if($item->isordered == 0)
					{
						$buttons[] = [
							'label' => 'Add Committee Member',
							'href' => route('prepare.committee', Crypt::encrypt($item->requestid)),
							'icon' => 'fa-plus',
						];

						$buttons[] = [
							'label' => 'Vendors',
							'href' => route('view.pptresumes', Crypt::encrypt($item->requestid)),
							'icon' => 'fa-users',
						];
					}
					else
					{
						$flags[] = ['label' => 'Work Order Issued', 'icon' => 'fa-check', 'class' => 'eoi-flag--success'];
						if($item->isdeployed==1)
						{
							$buttons[] = [
								'label' => 'Add Order',
								'href' => route('generate.workorder', Crypt::encrypt($item->requestid)),
								'icon' => 'fa-plus',
							];
						}
					}
				}
			}
		}
		else
		{
			$buttons[] = [
				'label' => 'View Update',
				'href' => route('prepare.draft', Crypt::encrypt($item->requestid)),
				'icon' => 'fa-refresh',
			];
		}
		if($item->eoistatus == 3 && date('Y\-m\-d',strtotime($item->deadlinedate))<$now && $item->isParticipated==0)
		{
			$buttons[] = [
				'label'	=> 'Extend Date',
				'href'	=> route('extend.eoidate', Crypt::encrypt($item->requestid)),
				'icon' => 'fa-calendar',
			];
		}
		if($item->isExtended==1)
		{
			$flags[] = ['label' => 'Date Extended', 'icon' => 'fa-calendar', 'class' => 'eoi-flag--info'];
		}
		

		if($item->eoi_file!='')
		{
			$buttons[] = [
				'label' => 'Download EoI',
				'href' => route('view.uploadedfile', Crypt::encrypt($item->eoi_file)),
				'icon' => 'fa-download',
				'target' => '_blank',
			];
		}
		
		if($item->corrigendum_file)
		{
			$buttons[] = [
				'label' => 'Download Corrigendum',
				'href' => route('view.uploadedfile', Crypt::encrypt($item->corrigendum_file)),
				'icon' => 'fa-download',
				'target' => '_blank',
			];
		}
	@endphp

	@php
		$serial = $i++;
	@endphp
	<tr class="eoi-row" id="item-{{ $serial }}">
		<td class="padding-10 eoi-card-cell" colspan="3">
			<div class="eoi-card">
				<div class="eoi-header">
					<div class="eoi-main">
						<div class="eoi-number-line">
							<div class="eoi-sno">{{ $serial }}.</div>
							<div class="eoi-project">{{ $item->projecttitle }}</div>
							@if(count($flags))
								<div class="eoi-flags">
									@foreach($flags as $flag)
										<span class="eoi-flag {{ $flag['class'] ?? '' }}">
											<i class="fa {{ $flag['icon'] }}"></i> {{ $flag['label'] }}
										</span>
									@endforeach
								</div>
							@endif
						</div>
						<div class="eoi-number">{{ $item->eoinumber }} [@if($item->tier_choice==1) Tier - 1 @elseif($item->tier_choice==2) Tier - 2 @else Both @endif]</div>
					</div>
					@if(count($buttons))
						<div class="eoi-actions">
							@foreach($buttons as $btn)
								<a @if(!empty($btn['href'])) href="{{ $btn['href'] }}" @endif class="eoi-btn action-a @if(!empty($btn['class'])) {{ $btn['class'] }} @endif" @if(!empty($btn['target']))
								target="{{ $btn['target'] }}" @endif @if(!empty($btn['data-url'])) data-url="{{ $btn['data-url'] }}" @endif @if(!empty($btn['onclick'])) onclick={{ $btn['onclick'] }} @endif>
									<i class="fa {{ $btn['icon'] }}"></i> {{ $btn['label'] }}
								</a>
							@endforeach
						</div>
					@endif
				</div>

				<div class="eoi-progress">
					<div class="eoi-progress-label">Progress</div>
					<div class="eoi-progress-bar">
						<span style="width: {{ $timelinePercent }}%;"></span>
					</div>
					<div class="eoi-progress-text">{{ $timelinePercent }}% Complete</div>
				</div>

				<div class="eoi-timeline" style="100vw!important">
					@foreach($timelineSteps as $step)
						<div class="timeline-step {{ $step['isDone'] ? 'is-done' : 'is-pending' }}">
							<div class="timeline-icon">
								<i class="fa {{ $step['isDone'] ? 'fa-check' : 'fa-clock-o' }} timeline-check-icon"></i>
							</div>
							<div class="timeline-title">{{ $step['label'] }}</div>
							<div class="timeline-date">{{ $step['display'] }}</div>
						</div>
					@endforeach
				</div>
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
