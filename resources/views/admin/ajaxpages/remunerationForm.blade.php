@php
$t=3;
@endphp
@if($categoryid==2)
<div class="col-sm-3">
	CONSULTANT SECTOR <label id="req">*</label>
	<select class="chosen-select form-control" name="sectorid" id="sectorid" required onKeyPress="return OnKeyPress(this, event)"  data-placeholder="SECTOR NAME" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}" >
		<option value=""></option>
		@foreach ($sector as $itm)
		<option value="{{ $itm->sectorid }}" {{ intval(old('sectorid'))===$itm->sectorid ? 'selected' : '' }}>
			{{ strtoupper($itm->sectorname) }}
		</option>
		@endforeach
	</select>

	<span class="text-danger">@error('sectorid') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }} </i> @enderror</span>
</div>
<div class="col-sm-6">
	CONSULTANT POSITION <label id="req">*</label>
	<select class="chosen-select form-control" name="positionid" id="positionid" required onKeyPress="return OnKeyPress(this, event)"  data-placeholder="SECTOR NAME" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}" >
		<option value=""></option>
		@foreach ($position as $itm)
		<option value="{{ $itm->positionid }}" {{ intval(old('positionid'))===$itm->positionid ? 'selected' : '' }}>{{ strtoupper($itm->consultantposition) }}</option>
		@endforeach
	</select>

	<span class="text-danger">@error('positionid') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }} </i> @enderror</span>
</div>
@endif
@if($categoryid==1)
<div class="col-sm-3">
	EXPERIENCE <label id="req">*</label>
	<select class="chosen-select form-control" name="experienceid" id="experienceid" required onKeyPress="return OnKeyPress(this, event)"  data-placeholder="EXPERIENCE" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}" autofocus>
		<option value=""></option>
		@foreach ($experience as $itm)
		<option value="{{ $itm->experienceid }}" {{ intval(old('experienceid',session('experienceid')))===$itm->experienceid ? 'selected' : '' }}>{{ strtoupper($itm->workexperience) }}</option>
		@endforeach
	</select>

	<span class="text-danger">@error('experienceid') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }} </i> @enderror</span>
</div>

<div class="col-sm-3">
	LEVEL <label id="req">*</label>
	<input type="text" class="form-control numbers" name="experiencelevel" id="experiencelevel" value="{{old('experiencelevel')}}" placeholder="LEVEL" required autocomplete="off" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}"/>

	<span class="text-danger">@error('experiencelevel') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
</div>
@endif
<div class="col-sm-3">
	REMUNERATION <label id="req">*</label>
	<input type="text" class="form-control numbers" name="remuneration" id="remuneration" value="{{old('remuneration')}}" placeholder="REMUNERATION" required autocomplete="off" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}"/>

	<span class="text-danger">@error('remuneration') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
</div>
@if($categoryid==2)
<div class="col-sm-10">&nbsp;</div>
@endif

@permission('store.remuneration')
<div class="col-sm-2">
	<button type="submit" class="btn btn-info myfrmbtn" style="margin-top:25px;"	tabindex="{{$t++}}">SUBMIT</button>
</div>
@endpermission
