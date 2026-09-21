<div class="section-block-detail" style="font-size:13px!important;">
	<div class="section-header-detail">
		<span class="material-icons">playlist_add_check</span> <!-- Hamburger menu icon -->
		&nbsp;<h2 class="section-title-detail">Introduction / Objective</h2>										
	</div>
	<p class="ui-text-block-detail font-13">
		@php
		$projectObjective = $data->projectobjective;

		// Remove all inline styles
		$projectObjective = preg_replace('/ style="[^"]*"/i', '', $projectObjective);

		// Remove <h1> to <h6> tags
		$projectObjective = preg_replace('/<\/?h[1-6][^>]*>/i', '', $projectObjective);
		@endphp

		{!! $projectObjective !!}	
	</p>
</div>
@if($data->aboutproject!='')
<div class="section-block-detail" style="font-size:13px!important;">
	<div class="section-header-detail">
		<span class="material-icons">playlist_add_check</span> <!-- Hamburger menu icon -->
		&nbsp;<h2 class="section-title-detail">About Project</h2>
	</div>
	<p class="ui-text-block-detail font-12">
		@php
		$aboutproject = $data->aboutproject;

		// Remove all inline styles
		$aboutproject = preg_replace('/ style="[^"]*"/i', '', $aboutproject);

		// Remove <h1> to <h6> tags
		$aboutproject = preg_replace('/<\/?h[1-6][^>]*>/i', '', $aboutproject);
		@endphp

		{!! $aboutproject !!}	
	
	</p>
</div>
@endif
<div class="section-block-detail" style="font-size:13px!important;">
	<div class="section-header-detail">
		<span class="material-icons">playlist_add_check</span> <!-- Hamburger menu icon -->
		&nbsp;<h2 class="section-title-detail">Scope of Work</h2>
	</div>
	<p class="ui-text-block-detail font-13">
		@php
		$scopeofwork = $data->scopeofwork;

		// Remove all inline styles
		$scopeofwork = preg_replace('/ style="[^"]*"/i', '', $scopeofwork);

		// Remove <h1> to <h6> tags
		$scopeofwork = preg_replace('/<\/?h[1-6][^>]*>/i', '', $scopeofwork);
		@endphp

		{!! $scopeofwork !!}	
	
	</p>
</div>
