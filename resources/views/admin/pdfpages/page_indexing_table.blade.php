@if($data->page_indexing!='')
<div class="section-block">

    <div class="section-heading">
        Table of Contents
    </div>

    <div class="content-block">
        {!! $data->page_indexing !!}
    </div>

</div>	

<div class="page-break"></div>

@endif


@if($data->chips_objective!='')
<div class="section-block">
	@if($data->engagementname)<div style="text-align:center; font-weight:600; padding:5px; margin-bottom:10px;">Expression of Interest (EOI) for {{$data->engagementname}}</div>@endif
	<div style="font-weight:600; position:relative; margin-bottom:10px;">
		<span>Ref. No : {{$data->eoinumber}}</span>
		<span style="position:absolute; right:0px;">Date : @if($data->releasedate){{date('d\-m\-Y',strtotime($data->releasedate))}}@else dd-mm-YYYY @endif</span>
	</div>
    <div class="content-block">
	{!! str_replace(
		'dd-mm-YYYY',
		date('d-m-Y',strtotime($data->releasedate)),
		$data->chips_objective
	) !!}
    </div>

</div>	
<div class="page-break"></div>
@endif