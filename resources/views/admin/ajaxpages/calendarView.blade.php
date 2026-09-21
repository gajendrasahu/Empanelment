<tr>
<td style="width:60%; vertical-align:top;">
<table class="holiday_table pd-5 mytable" border="1">
    <tr>
        <td colspan="7" class="center">

            <a onclick="calendarView('{{ route('calendar.view', ['month' => $prevMonth]) }}')"
               style="cursor:pointer; float:left; text-decoration:none;">
                <b><i class="fa fa-angle-left"></i> Previous</b>
            </a>

            <b>{{ $month->format('F Y') }}</b>

            <a onclick="calendarView('{{ route('calendar.view', ['month' => $nextMonth]) }}')"
               style="cursor:pointer; float:right; text-decoration:none;">
                <b>Next <i class="fa fa-angle-right"></i></b>
            </a>

        </td>
    </tr>

    <tr>
        <td class="center"><b>Sun</b></td>
        <td class="center"><b>Mon</b></td>
        <td class="center"><b>Tue</b></td>
        <td class="center"><b>Wed</b></td>
        <td class="center"><b>Thu</b></td>
        <td class="center"><b>Fri</b></td>
        <td class="center"><b>Sat</b></td>
    </tr>

    <tbody>
        @foreach ($calendar as $week)
            <tr>
                @foreach ($week as $day)

                    @if(!$day)
                        <td class="empty"></td>
                    @else

                        @php
                            $isHoliday = $day['is_holiday'];
                            $isWeeklyOff = $day['is_weekly_off'];

                            $bgStyle = '';
                            if ($isHoliday && !$isWeeklyOff) {
                                $bgStyle = 'background-color:#ffd6d6!important; color:black;';
                            } elseif ($isHoliday) {
                                $bgStyle = 'background-color:#ffe5e5!important; color:black;';
                            } elseif ($isWeeklyOff) {
                                $bgStyle = 'background-color:#e7f1ff!important; color:black;';
                            }
                        @endphp

                        <td style="{{ $bgStyle }}">

                            <div class="holiday_date center">
                                {{ $day['day'] }}
                            </div>

                            {{-- Holiday labels --}}
							@if (!empty($day['holiday_titles']) && !$isWeeklyOff)
								@php
									$titles = array_unique($day['holiday_titles']);
									$titleText = implode(', ', $titles);
								@endphp

								<span class="holiday_label holiday-title center"
									  title="{{ $titleText }}">
									Holiday <i class="fa fa-info-circle"></i>
								</span>
							@endif
                            {{-- Weekly off label --}}
                            @if ($isWeeklyOff)
                                <span class="holiday_label center">
                                    Weekly Off
                                </span>
                            @endif

                        </td>

                    @endif

                @endforeach
            </tr>
        @endforeach
    </tbody>

</table>
</td>
<td style="width:40%; vertical-align:top;">
<table class="mytable pd-10" border="1">
	<tr><td colspan="2"><b>Holiday List for {{ $month->format('F Y') }}</b></td></tr>
	@foreach($monthHolidays as $days)
	<tr>
		<td class="center width-100">{{date('d\-m\-Y',strtotime($days->start_date))}}</td>
		<td>{{$days->title}}</td>
	</tr>
	@endforeach
	@if($monthHolidays->count()==0)
	<tr><td colspan="2" class="center">--No Record Found--</td></tr>
	@endif
</table>
</td>
</tr>