<div class="pull-right tableTools-container" style="margin-top:-35px;"></div>

<div>
<table id="dynamic-table" class="table table-striped table-bordered">
<thead>
<tr>
    <th class="myrectd" nowrap><b>S.NO.</b></th>
    <th nowrap># TAG TITLE</th>
	<th nowrap style="text-align:center;">DISPLAY ORDER</th>
    <th nowrap style="text-align:center;">DATE</th>
    <th nowrap style="text-align:left; width:150px;">CREATED BY</th>
	<th nowrap style="text-align:center; width:30px;"></th>
</tr>
</thead>
<tbody>
@php
$i=1
@endphp
@foreach ($data as $item)
<tr id="item-{{ $i }}">
    <td style="width:30px;" class="myrectd" nowrap>{{ $i++ }}</td>
    <td nowrap>{{ $item->hashtag }}</td>
    <td nowrap style="text-align:center;">{{ $item->displayorder }}</td>
	<td nowrap style="text-align:center;">{{ date('d\-m\-Y, h:i A',strtotime($item->creationdate)) }}</td>
    <td nowrap style="text-align: left;">@if($item->userid==0) {{ $item->Admin }} (ADMIN) @else {{ $item->Name }} (USER) @endif</td>
    <td nowrap style="text-align:center;">
        <a title="" class="myactionlink" onclick="deleteItem('hashtag',{{ $item->id}},{{ $i }},'ARE YOU SURE YOU WANT TO DELETE THIS RECORD?','HASH TAG DELETED SUCCESSFULLY')" title=""><i class="fa fa-trash"></i>
        </a>
    </td>

</tr>
@endforeach
@if($i==1)
<tr>
    <td colspan="5" style="text-align:center;">
        <br>
        <i class="fa fa-warning blue nodata"></i><br>
        SORRY!<br>
        THE SYSTEM DID NOT FIND THE DATA YOU ARE LOOKING FOR.
    </td>
</tr>
@endif
</tbody>
</table>
</div>

