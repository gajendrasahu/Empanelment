@if($data->evaluationprocess!='')
<div class="section-block-detail">
	<div class="section-header-detail">
		<span class="material-icons">compare</span>
		&nbsp;<h2 class="section-title-detail">Evaluation Process</h2>
	</div>			
	{!!$data->evaluationprocess!!}
</div>
@endif
@if($data->termsandcondition!='')
<div class="section-block-detail">
	<div class="section-header-detail">
		<span class="material-icons">playlist_add_check</span>
		&nbsp;<h2 class="section-title-detail">Payment & Penalty Terms</h2>
	</div>
		@php
		$termsandcondition = $data->termsandcondition;
		$termsandcondition = preg_replace('/ style="[^"]*"/i', '', $termsandcondition);
		$termsandcondition = preg_replace('/<\/?h[1-6][^>]*>/i', '', $termsandcondition);
		$termsandcondition = preg_replace('/>\s+</', '><', $termsandcondition);
		$termsandcondition = preg_replace('/\s+/', ' ', $termsandcondition);
		$termsandcondition = trim($termsandcondition);
		@endphp

		{!! $termsandcondition !!}	
	

</div>
@endif
@if($data->criticalinformation!='')
<div class="section-block-detail">
	<div class="section-header-detail">
		<span class="material-icons">info</span>
		&nbsp;<h2 class="section-title-detail">Critical Information</h2>
	</div>			
		@php
		$criticalinformation = $data->criticalinformation;
		$criticalinformation = preg_replace('/ style="[^"]*"/i', '', $criticalinformation);
		$criticalinformation = preg_replace('/<\/?h[1-6][^>]*>/i', '', $criticalinformation);
		$criticalinformation = preg_replace('/>\s+</', '><', $criticalinformation);
		$criticalinformation = preg_replace('/\s+/', ' ', $criticalinformation);
		$criticalinformation = trim($criticalinformation);
		@endphp

		{!! $criticalinformation !!}	

</div>
@endif
@if($data->documentrequired!='')
<div class="section-block-detail">
	<div class="section-header-detail">
		<span class="material-icons">file_open</span>
		&nbsp;<h2 class="section-title-detail">Documents Required to Participate in This EoI</h2>
	</div>			
		@php
		$documentrequired = $data->documentrequired;
		$documentrequired = preg_replace('/ style="[^"]*"/i', '', $documentrequired);
		$documentrequired = preg_replace('/<\/?h[1-6][^>]*>/i', '', $documentrequired);
		$documentrequired = preg_replace('/>\s+</', '><', $documentrequired);
		$documentrequired = preg_replace('/\s+/', ' ', $documentrequired);
		$documentrequired = trim($documentrequired);
		@endphp

		{!! $documentrequired !!}	

</div>
@endif
