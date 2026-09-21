@php
	$i = 1;
	use Carbon\Carbon;
@endphp

@foreach ($data as $item)

	@php
		$invalidDates = ['0000-00-00','0000-00-00 00:00:00','1970-01-01','1970-01-01 00:00:00'];
		
		if($item->isSecondCall==0)
		{
			$timelineSteps = [
				['label' => 'Release Date of EoI by CHiPS', 'date' => $item->releasedate, 'format' => 'd-m-Y'],
				['label' => 'Last Date of Pre-bid Query', 'date' => $item->prebidlastdate, 'format' => 'd-m-Y'],
				['label' => 'Last Date for Submission of Proposals', 'date' => $item->deadlinedate, 'format' => 'd-m-Y h:i A'],
				['label' => 'Tentative Date of Presentation and Interview', 'date' => $item->interviewdate, 'format' => 'd-m-Y'],
			];
		}
		else
		{
			$timelineSteps = [
				['label' => 'Release Date of EoI by CHiPS', 'date' => $item->releasedate, 'format' => 'd-m-Y'],
				['label' => 'Last Date for Submission of Proposals', 'date' => $item->deadlinedate, 'format' => 'd-m-Y h:i A'],
				['label' => 'Tentative Date of Presentation and Interview', 'date' => $item->interviewdate, 'format' => 'd-m-Y'],
			];			
		}
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

		$serial = $i++;
		$flags = [];
		$buttons = [];
		if($item->isClosed==0)
		{
			$buttons[] = [
				'label' => 'Send Message',
				'href' => route('communication.message', Crypt::encrypt($item->requestid)),
				'icon' => 'fa-envelope',
			];
		}
		// View EoI (original logic)
		if($item->eoistatus >= 3)
		{
			$buttons[] = [
				'label' => 'View EoI',
				'href' => route('floated.view', Crypt::encrypt($item->floatid)),
				'icon' => 'fa-hand-o-right',
			];

			if($item->eoi_file != '')
			{
				$buttons[] = [
					'label' => 'Download EoI',
					'href' => route('view.uploadedfile', Crypt::encrypt($item->eoi_file)),
					'icon' => 'fa-download',
					'target' => '_blank',
				];
			}
			
			if($item->inprebid==1)
			{
				$buttons[] = [
					'label' =>	'Pre-Bid Query',
					'href' 	=> 	route('prebid.enquiry',Crypt::encrypt($item->floatid)),
					'icon' 	=> 	'fa-hand-o-right',
				];
			}
			
		}

		if($item->eoistatus >= 3 && $item->isparticipated == 1 && $item->signedcopyofeoi != '')
		{
			$flags[] = ['label' => 'Participated', 'icon' => 'fa-paper-plane', 'class' => 'eoi-flag--info'];

			$buttons[] = [
				'label'	=>	'View Resumes',
				'href' 	=> 	route('fillform.view', Crypt::encrypt($item->floatid)),
				'icon' 	=> 	'fa-users',
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
		if($item->isClosed==1)
		{
			$flags = [];
			$flags[] = ['label' => 'Closed', 'icon' => 'fa-close', 'class' => 'eoi-flag--info'];
		}
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
						<div class="eoi-number">{{ $item->eoinumber }}</div>
					</div>

					@if(count($buttons))
						<div class="eoi-actions">
							@foreach($buttons as $btn)
								<a 
									@if(!empty($btn['href'])) href="{{ $btn['href'] }}" @endif
									class="eoi-btn action-a @if(!empty($btn['class'])) {{ $btn['class'] }} @endif"
									@if(!empty($btn['target'])) target="{{ $btn['target'] }}" @endif
								>
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

				<div class="eoi-timeline">
					@foreach($timelineSteps as $step)
						<div class="timeline-step {{ $step['isDone'] ? 'is-done' : 'is-pending' }}">
							<div class="timeline-icon">
								<i class="fa {{ $step['isDone'] ? 'fa-check' : 'fa-clock-o' }}"></i>
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
		<td style="text-align:center;">
			<br>
			<i class="fa fa-warning blue nodata"></i><br>
			{!! __('messages.sorry') !!}
		</td>
	</tr>
@else
	<tr>
		<td style="text-align:right;">
			{{ $data->links('vendor.pagination.default') }}
		</td>
	</tr>
@endif

<script>
	document.querySelectorAll('.pagination a').forEach(link => {
		link.onclick = function(e) {
			e.preventDefault();
			const page = this.getAttribute('href').split('page=')[1];
			const url = '{{ route('floatedeoi.html') }}';
			loadData(page, url);
		};
	});
</script>