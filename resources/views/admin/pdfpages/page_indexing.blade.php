<div class="section-block-detail">
	<div class="section-header-detail">
		<span class="material-icons">playlist_add_check</span> <!-- Hamburger menu icon -->
		&nbsp;<h2 class="section-title-detail">Table of Contents</h2>
	</div>
	<p class="ui-text-block-detail">{!! $data->page_indexing !!}</p>
</div>
@if($data->chips_objective)
<div class="section-block-detail">
	<p class="ui-text-block-detail">{!! $data->chips_objective !!}</p>
</div>
@endif
