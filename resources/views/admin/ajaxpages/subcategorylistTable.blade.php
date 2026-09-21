@php
$i=1
@endphp
<div class="row">
<div class="col-sm-12 myheadbg">SUB CATEGORIES</div>
@foreach ($data as $item)
<div class="col-sm-4" style="padding:2px 5px;">
	<input type="checkbox" id="{{$item->categoryid}}" onclick="AddStructure({{$item->categoryid}},'{{ route('store.payoutstructure') }}')" style="vertical-align:text-top; font-size:14px;"> {{$item->category}}
</div>
@endforeach
</div>

