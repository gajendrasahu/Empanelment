@if($data->page_indexing!='')
<table class="section-table border">
    <tr>
        <td class="padding-8 form-label">
            <b>Table of Contents</b>
        </td>
    </tr>
    <tr>
        <td class="padding-8">
            {!! $data->page_indexing !!}
        </td>
    </tr>
</table>
<div class="page-break"></div>
@endif
@if($data->chips_objective!='')
<table class="section-table">
   <tr>
        <td class="padding-8">
            {!! str_replace(
                'dd-mm-YYYY',
                date('d-m-Y',strtotime($data->releasedate)),
                $data->chips_objective
            ) !!}

        </td>
    </tr>
</table>
<div class="page-break"></div>
@endif