@php
$i=1
@endphp
@foreach ($data as $item)
<label class="arealabels" style="">
	<input type="checkbox" name="areaid[]" class="areaid" value="{{$item->areaid}}"> {{$item->areaname}}
</label>
@endforeach
<script>
$(document).ready(function() {
    // This function will trigger when the checkbox state changes
    $('.areaid').on('change', function() {
        // Get the values of all selected checkboxes
        var selectedValues = $('.areaid:checked').map(function() {
            return $(this).val();
        }).get().join(','); // Join them with a comma
        
        $("#areaids").val(selectedValues); // Display the result in the console or use it elsewhere
    });
});
</script>