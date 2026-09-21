<div class="section-block-detail">
	<div class="section-header-detail">
		<span class="material-icons">playlist_add_check</span> <!-- Hamburger menu icon -->
		&nbsp;<h2 class="section-title-detail">Introduction / Objective</h2>										
	</div>
	<p class="ui-text-block-detail">{!! $data->projectobjective !!}</p>
</div>
@if($data->aboutproject!='')
<div class="section-block-detail">
	<div class="section-header-detail">
		<span class="material-icons">playlist_add_check</span> <!-- Hamburger menu icon -->
		&nbsp;<h2 class="section-title-detail">About Project</h2>
	</div>
	<p class="ui-text-block-detail">{!! $data->aboutproject !!}</p>
</div>
@endif
<div class="section-block-detail">
	<div class="section-header-detail">
		<span class="material-icons">playlist_add_check</span> <!-- Hamburger menu icon -->
		&nbsp;<h2 class="section-title-detail">Scope of Work</h2>
	</div>
	<table style="width:100%; border:none;">
		<tr>
			<td style="border:none;">
				@php
					$scopeofwork = "";
					$scopeofwork = old('scopeofwork') ?: ($data->scopeofwork ?: $anyother);
				@endphp
				<textarea class="form-control" name="scopeofwork" required id="scopeofwork" placeholder="Special condition (if any)" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}">{!! $scopeofwork !!}</textarea>
				<span class="text-danger">@error('scopeofwork') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
			</td>
		</tr>
	</table>

</div>
@if($data->anyother!='')
<div class="section-block-detail">
	<div class="section-header-detail">
		<span class="material-icons">playlist_add_check</span> <!-- Hamburger menu icon -->
		&nbsp;<h2 class="section-title-detail">Other Information (If Any)</h2>
	</div>
	<table style="width:100%; border:none;">
		<tr>
			<td style="border:none;">
				@php
					$anyother = "";
					$anyother = old('anyother') ?: ($data->anyother ?: $anyother);
				@endphp
				<textarea class="form-control" name="anyother" required id="anyother" placeholder="Special condition (if any)" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}">{!! $anyother !!}</textarea>
				<span class="text-danger">@error('anyother') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
			</td>
		</tr>
	</table>
</div>
@endif
