@if($data->evaluationprocess!='')
<div class="section-block">

    <div class="section-heading">
	{{$data->evaluation_index++}}. Evaluation Process
    </div>

    <div class="content-block">
		{!! $data->evaluationprocess !!}
    </div>

</div>	
@endif



@if($data->termsandcondition!='')
<div class="section-block">

    <div class="section-heading">
        {{$data->evaluation_index++}}. Payment & Penalty Terms
    </div>

    <div class="content-block">
            {!! str_replace(
                'Please provide project name',
                $data->engagementname,
                $data->termsandcondition
            ) !!}
    </div>

</div>	
@endif



@if($data->criticalinformation!='')

@php

if($data->deadlinedate!='')
{
    $data->criticalinformation = str_replace(
        'dd/mm/YYYY',
        '<b>'.date('d-m-Y, h:i A',strtotime($data->deadlinedate)).'</b>',
        $data->criticalinformation
    );
}

@endphp

<div class="section-block">

    <div class="section-heading">
        {{$data->evaluation_index++}}. Critical Information
    </div>

    <div class="content-block">
	{!! $data->criticalinformation !!}
    </div>

</div>	
@endif


@if($data->documentrequired!='')
<div class="section-block">

    <div class="section-heading">
        {{$data->evaluation_index++}}. Documents Required To Participate In This EoI
    </div>

    <div class="content-block">
	{!! $data->documentrequired !!}
    </div>

</div>	
@endif