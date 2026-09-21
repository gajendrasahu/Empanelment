@extends('admin.admin_master')
@section('admin')
@php
@endphp
<div class="main-content">
<style>
    .header-container {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 15px;
    }

    .legend-box {
        display: inline-block;
        padding: 5px 10px;
        margin-left: 8px;
        border-radius: 4px;
        font-weight: 600;
        font-size: 14px;
    }
    table { width: 100%; border-collapse: collapse; }

    th, td {
        border: 1px solid #ccc;
        width: 14.28%;
        height: 60px;
        text-align: center;
        vertical-align: top;
    }

    .present { background-color: #c8f7c5; }   /* Green */
    .absent { background-color: #f7c5c5; }    /* Red */
    .leave { background-color: #f7e7c5; }     /* Yellow */
    .holiday { background-color: #c5d9f7; }   /* Blue */

    .legend-box {
        display: inline-block;
        padding: 6px 12px;
        margin-right: 10px;
        border-radius: 4px;
        font-weight: bold;
        color: #333;
    }
</style>
	<div class="main-content-inner">
		<div class="col-sm-12 height-space"></div>
		<div class="col-sm-12">

<div class="header-container">

    <h2>{{ $currentMonth->format('F Y') }}</h2>
    <div>
        <span class="legend-box present">Present ({{ $totals['P'] }})</span>
        <span class="legend-box absent">Absent ({{ $totals['A'] }})</span>
        <span class="legend-box leave">Leave ({{ $totals['L'] }})</span>
        <span class="legend-box holiday">Holiday ({{ $totals['H'] }})</span>
    </div>
</div>

<div>
	<a href="{{ route('monthly.attendance', ['user_id' => $userId, 'month' => $previousMonth]) }}">
		← Previous
	</a>
	|
	<a href="{{ route('monthly.attendance', ['user_id' => $userId, 'month' => $nextMonth]) }}">
		Next →
	</a>
</div>

<br>

<table>
    <tr>
        <th>Sun</th>
        <th>Mon</th>
        <th>Tue</th>
        <th>Wed</th>
        <th>Thu</th>
        <th>Fri</th>
        <th>Sat</th>
    </tr>

    @php
        $firstDayOfWeek = \Carbon\Carbon::parse($calendarDays[0]['date'])->dayOfWeek;
        $dayCounter = 0;
    @endphp

    <tr>
        {{-- Blank cells before first day --}}
        @for ($i = 0; $i < $firstDayOfWeek; $i++)
            <td></td>
            @php $dayCounter++; @endphp
        @endfor

        @foreach ($calendarDays as $day)

            @php
                $class = '';
                if ($day['status'] == 'P') $class = 'present';
                elseif ($day['status'] == 'A') $class = 'absent';
                elseif ($day['status'] == 'L') $class = 'leave';
                elseif ($day['status'] == 'H') $class = 'holiday';
            @endphp

			<td class="{{ $class }}">

				<!-- Date -->
				<div style="font-weight: bold;">
					{{ $day['day'] }}
				</div>

				<!-- Status -->
				@if($day['status'])
					<div style="font-size:14px; font-weight:600;">
						<span style="border-radius:45px; padding:0px 5px; background-color:white;">{{ $day['status'] }}</span>
					</div>
				@endif

				@if($day['in_time'] || $day['out_time'])
					<div style="font-size:11px; margin-top:4px;">
						{{ date('h:i A', strtotime($day['in_time'])) }} - @if($day['out_time']){{ date('h:i A', strtotime($day['out_time'])) }}@else UN MARKED @endif
					</div>
				@endif

			</td>
            @php $dayCounter++; @endphp

            @if ($dayCounter % 7 == 0)
                </tr><tr>
            @endif

        @endforeach

        {{-- Fill remaining cells --}}
        @while ($dayCounter % 7 != 0)
            <td></td>
            @php $dayCounter++; @endphp
        @endwhile
    </tr>
</table>
			
		</div>
		<div class="col-sm-12">&nbsp;</div>
		<div class="col-sm-12">&nbsp;</div>
	</div>
</div>
@endsection