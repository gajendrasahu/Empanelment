@if($data->anyother!='')
<div class="section-block-detail">
<!--
	<div class="section-header-detail">
		<span class="material-icons">playlist_add_check</span>
		&nbsp;<h2 class="section-title-detail">Other Information (If Any)</h2>
	</div>
-->
	<p class="ui-text-block-detail">
		@php
		$anyother = $data->anyother;
		$anyother = preg_replace('/ style="[^"]*"/i', '', $anyother);
		$anyother = preg_replace('/<\/?h[1-6][^>]*>/i', '', $anyother);
		$anyother = preg_replace('/>\s+</', '><', $anyother);
		$anyother = preg_replace('/\s+/', ' ', $anyother);
		$anyother = trim($anyother);
		@endphp

		{!! $anyother !!}	
	
	</p>
</div>
@endif
