@php
$i=1
@endphp
@foreach ($images as $img)
<div class="col-sm-2 center">
	<img src="{{ asset('storage/'.$img->productimage) }}" alt="Uploaded Image" style="width:100%; position:relative; margin-top:15px;">
	<i class="fa fa-remove" onclick="removeImages('{{ route('remove.image') }}',{{$img->imageid}})"></i>
</div>
@endforeach
