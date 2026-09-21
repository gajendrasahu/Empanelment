@if($data->anyother!='')
<div class="section-block-detail" style="font-size:13px!important;">
	
	<div class="section-header-detail">
		<span class="material-icons">playlist_add_check</span>
		&nbsp;<h2 class="section-title-detail">Other Information (if any)</h2>
	</div>
	
	<table style="width:100%; border:none;">
		<tr>
			<td style="border:none;">
				@php
					$anyother = "";
					$anyother = old('anyother') ?: ($data->anyother ?: $anyother);
				@endphp
				<textarea class="form-control tinymce" name="anyother" required id="anyother" placeholder="Special condition (if any)" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}">{!! $anyother !!}</textarea>
				<span class="text-danger">@error('anyother') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
			</td>
		</tr>
	</table>
</div>
@endif
