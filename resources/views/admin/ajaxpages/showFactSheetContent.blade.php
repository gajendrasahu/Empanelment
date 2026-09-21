@php
$t=0;
$preferred[1]	=	'Tier - I';
$preferred[2]	=	'Tier - II';
$preferred[3]	=	'Both';

@endphp


<div class="content-detail">
	@include('admin.viewpages.factsheet')
	<div class="col-sm-12"></div>
</div>

<script src="{{ asset('panel/assets/js/datatablePaginationWithFilter.js') }}"></script>
<script>
document.querySelectorAll('.format-indian').forEach(function(el) {
    el.innerText = formatIndianNumber(el.dataset.value);
});


</script>
