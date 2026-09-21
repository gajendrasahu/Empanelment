@if($data->projectobjective!='')
<div class="section-block">

    <div class="section-heading">
        2. Project Introduction / Objective
    </div>

    <div class="content-block">
		{!! $data->projectobjective !!}
    </div>

</div>	
@endif


@if($data->scopeofwork!='')
<div class="section-block">

    <div class="section-heading">
        3. Scope of Work
    </div>

    <div class="content-block">
		{!! $data->scopeofwork !!}
		
    </div>

</div>	

@endif