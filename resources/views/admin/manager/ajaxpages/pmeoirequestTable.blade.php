@php
$i = 1;
$now = \Carbon\Carbon::now();
@endphp
@foreach ($data as $item)

    @php
		$deadDate = \Carbon\Carbon::parse($item->deadlinedate);
        $invalidDates = ['0000-00-00', '1970-01-01'];
        $timelineSteps = [
            ['label' => 'Requested on', 'date' => $item->creationdate, 'format' => 'd-m-Y'],
            ['label' => 'Release Date of EoI by CHiPS', 'date' => $item->releasedate, 'format' => 'd-m-Y'],
            ['label' => 'Last Date of Pre-bid Query', 'date' => $item->prebidlastdate, 'format' => 'd-m-Y'],
            ['label' => 'Last Date for Submission of Proposals', 'date' => $item->deadlinedate, 'format' => 'd-m-Y h:i A'],
            ['label' => 'Tentative Date of Presentation and Interview', 'date' => $item->interviewdate, 'format' => 'd-m-Y'],
        ];
        $now = \Carbon\Carbon::now();
        $completedSteps = 0;
        $totalSteps = count($timelineSteps);
        foreach ($timelineSteps as $idx => $step) {
            $raw = $step['date'] ?? null;
            $isValid = !empty($raw) && !in_array($raw, $invalidDates);
            $dateObj = $isValid ? \Carbon\Carbon::parse($raw) : null;
            $timelineSteps[$idx]['display'] = $isValid ? $dateObj->format($step['format']) : 'TBD';
            $timelineSteps[$idx]['isDone'] = $isValid && $dateObj->lte($now);
            if ($timelineSteps[$idx]['isDone']) {
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

        $eoiNumber = ($item->eoistatus != 0 && !empty($item->eoinumber)) ? $item->eoinumber : 'To be generated';

        if (!empty($item->authorizationletter)) {
            $buttons[] = [
                'label' => 'View File',
                'href' => route('view.uploadedfile', Crypt::encrypt($item->authorizationletter)),
                'icon' => 'fa-hand-o-right',
                'target' => '_blank',
            ];
        }

        if ($item->eoistatus >= 1) {
            $buttons[] = [
                'label' => 'Preview',
                'href' => route('pmeoipreview.printable', Crypt::encrypt($item->requestid)),
                'icon' => 'fa-hand-o-right',
                'target' => '_blank',
            ];
        }

        if ($item->isinprebid) {
            $requestIds = DB::table('eoi_request_prebid')->where('requestid', $item->requestid)->pluck('requestid');
            $exists = DB::table('eoi_request_prebid_forwarded')->whereIn('requestid', $requestIds)->exists();
            if ($exists) {
                $buttons[] = [
                    'label' => 'View Queries',
                    'href' => route('prebid.pmreply', Crypt::encrypt($item->requestid)),
                    'icon' => 'fa-hand-o-right',
                ];
            }
        }

        if($item->isInterviewSet==1)
		{
            $buttons[] = [
                'label' => 	'View Participation',
                'href' => 	route('view.pmpptresumes', Crypt::encrypt($item->requestid)),
                'icon' => 	'fa-users',
            ];
        }


        if ($item->eoistatus == 0) {
            if ($item->istrue) {
                $buttons[] = [
                    'label' => 'View EoI',
                    'href' => route('pmview.draft', Crypt::encrypt($item->requestid)),
                    'icon' => 'fa-hand-o-right',
                ];
            }
        } elseif ($item->eoistatus == 1) {
            if ($item->istrue) {
                $buttons[] = [
                    'label' => 'View Draft',
                    'href' => route('pmview.draft', Crypt::encrypt($item->requestid)),
                    'icon' => 'fa-hand-o-right',
                ];
            } else {
                $flags[] = ['label' => 'Expired', 'icon' => 'fa-exclamation-circle', 'class' => 'eoi-flag--warning'];
            }
        }
		elseif ($item->eoistatus == 2)
		{
            $buttons[] = [
                'label' => 'Edit EoI',
                'href' => route('pmview.draft', Crypt::encrypt($item->requestid)),
                'icon' => 'fa-edit',
            ];

            $buttons[] = [
                'label' 	=> 	'Committee Member',
                'onclick' 	=>  "ShowCommitteeMembers('".Crypt::encrypt($item->requestid)."')",
				'href'		=>	'',
                'icon' 		=> 	'fa-users',
            ];

            $flags[] = ['label' => 'Confirmed', 'icon' => 'fa-thumbs-up', 'class' => 'eoi-flag--success'];
        }
		elseif ($item->eoistatus == 3)
		{
            $buttons[] = [
                'label' 	=> 	'Committee Member',
                'onclick' 	=>  "ShowCommitteeMembers('".Crypt::encrypt($item->requestid)."')",
				'href'		=>	'',
                'icon' 		=> 	'fa-users',
            ];
			
            $flags[] = ['label' => 'Published', 'icon' => 'fa-thumbs-up', 'class' => 'eoi-flag--success'];
        }
		elseif ($item->eoistatus > 3) 
		{
            $buttons[] = [
                'label' 	=> 	'Committee Member',
                'onclick' 	=>  "ShowCommitteeMembers('".Crypt::encrypt($item->requestid)."')",
				'href'		=>	'',
                'icon' 		=> 	'fa-users',
            ];
			if($item->isordered>0)
			{
				$flags[] = ['label' => 'Work Order Issued', 'icon' => 'fa-check', 'class' => 'eoi-flag--success'];
			}
        }

		if($item->corrigendum_file)
		{
			$buttons[] = [
				'label' => 	'Download Corrigendum',
				'href' 	=>	route('view.uploadedfile',Crypt::encrypt($item->corrigendum_file)),
				'icon' 	=> 	'fa-download',
				'target'=> 	'_blank',
			];
			
		}
		if($item->deadlinedate && $deadDate->lt($now) && $item->isParticipated == 0)
		{
			$flags[] = ['label' => 'No Participants', 'icon' => 'fa-remove', 'class' => 'eoi-flag--warning'];
		}
		
		if(date('Y\-m\-d',strtotime($item->deadlinedate))<$now && $item->isordered==1)
		if($item->allowResourceAdd==1)
		{
			$buttons[] = [
				'label' => 	'Add Additional Resources to EoI',
				'href' 	=>	route('add.eoiresource',Crypt::encrypt($item->requestid)),
				'icon' 	=> 	'fa-users',
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
                        <div class="eoi-number">{{ $eoiNumber }}</div>
                    </div>
                    @if(count($buttons))
                        <div class="eoi-actions">
							<!--
							<a class="eoi-btn action-a" onclick="showPrice('{{ route('showprice.comparison',[Crypt::encrypt('No'),Crypt::encrypt($item->requestid)]) }}')" style="cursor:pointer;">
								<i class="fa fa-calculator"></i> Pricing
							</a>							
							-->
                            @foreach($buttons as $btn)
                                <a @if($btn['href']) href="{{ $btn['href'] }}" @endif class="eoi-btn action-a" @if(!empty($btn['target'])) target="{{ $btn['target'] }}" @endif @if(!empty($btn['onclick'])) style="cursor:pointer;" onclick="{{ $btn['onclick'] }}" @endif>
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
        <td colspan="3" class="padding-10 center">
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
        link.onclick = function(event) {
            event.preventDefault();
            const page = this.getAttribute('href').split('page=')[1];
            const url = '{{ route('pmeoirequest.html') }}';
            loadData(page, url);
        };
    });

    document.querySelectorAll('.format-indian').forEach(function(el) {
        el.innerText = formatIndianNumber(el.dataset.value);
    });
	
	
</script>
