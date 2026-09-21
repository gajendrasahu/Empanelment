@php 
$i = 1; 
@endphp

@foreach ($data as $item)
<tr>
    <td class="text-center">{{ $loop->iteration }}</td>

    <td>{{ $item->companyname }}</td>

    <td>
        <a href="{{ route('view.uploadedfile', Crypt::encrypt($item->signedcopy)) }}"
           target="_blank"
           style="text-decoration:none; outline:none;">
            <i class="fa fa-file-pdf-o"></i> {{ $item->ordernumber }}
        </a>
    </td>

    <td class="no_wrap">
        {{ date('d\-m\-Y', strtotime($item->orderdate)) }}
    </td>

    <td>
        <form action="{{ route('workorder.updateDueDate') }}" method="POST" class="update-due-date-form" style="display:flex; align-items:center; gap:5px;">
            @csrf
            <input type="hidden" name="orderid" value="{{ $item->orderid }}">
            <input type="text" name="workorderduedate" value="{{ date('d\-m\-Y', strtotime($item->workorderduedate)) }}" class="todays_dt_blank width-100" autocomplete="off">
            <button type="submit" class="btn btn-info gridbtn width-100">Update</button>
        </form>
        <span class="due-date-message"></span>
    </td>
</tr>
@endforeach

@if($data->count() == 0)
<tr>
    <td colspan="5" class="padding-10" style="text-align:center;">
        <br>
        <i class="fa fa-warning blue nodata"></i><br>
        {!! __('messages.sorry') !!}
    </td>
</tr>
@else
<tr>
    <td colspan="5" class="padding-10" style="text-align:right;">
        {{ $data->links('vendor.pagination.default') }}
    </td>
</tr>
@endif