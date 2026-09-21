<div class="pull-right tableTools-container" style="margin-top:-35px;"></div>

<div>
<table id="dynamic-table" class="table table-striped table-bordered">
<thead>
<tr>
    <th class="myrectd" nowrap><b>S.NO.</b></th>
    <th nowrap>SUB CATEGORY</th>
    <th nowrap>CONTENT</th>
    <th nowrap>LINK</th>
	<th nowrap style="text-align:center; width:130px;">FILE</th>
    <th nowrap style="text-align:center; width:100px;">DATE</th>
    <th nowrap style="text-align:left; width:150px;">CREATED BY</th>
    <th style="width:25px;"></th>
    <th style="width:25px;"></th>
</tr>
</thead>
<tbody>
@php
$i=1
@endphp
@foreach ($data as $item)
<tr id="item-{{ $i }}">
    <td style="width:30px;" class="myrectd" nowrap>{{ $i++ }}</td>
    <td nowrap>{{ $item->subcategory }}</td>
    <td nowrap>{{ $item->content }}</td>
    <td nowrap>{{ $item->contentlink }}</td>
    <td nowrap style="text-align:center;">
    @if($item->contenttype=='IMAGE')
    <a href="{{ asset('storage/'.$item->contentfile) }}" target="_blank"><img src="{{ asset('storage/'.$item->contentfile) }}" alt="Uploaded Image" style="width:150px;"></a>
    @elseif($item->contenttype=='VIDEO')
    <video width="150" controls>
        <source src="{{ asset('storage/'.$item->contentfile) }}" type="video/mp4">
    </video>
    <br><a href="{{ asset('storage/'.$item->contentfile) }}" target="_blank">VIEW</a>
    @elseif($item->contenttype=='PDF')
    <a href="{{ asset('storage/'.$item->contentfile) }}" target="_blank">DOWNLOAD</a>
    @endif
    </td>
    <td nowrap style="text-align:center;">{{ date('d\-m\-Y, h:i A',strtotime($item->creationdate)) }}</td>
    <td nowrap style="text-align: left;">{{ $item->createdby }}</td>
    <td nowrap style="text-align:center;"><a href="{{ route('edit.content',$item->contentid) }}" title="" class="myactionlink"><i class="fa fa-edit"></i></a></td>
    <td nowrap style="text-align:center;">
        <a title="" class="myactionlink" onclick="deleteItem('content',{{ $item->contentid}},{{ $i }},'ARE YOU SURE YOU WANT TO DELETE THIS RECORD?','CONTENT DELETED SUCCESSFULLY')" title=""><i class="fa fa-trash"></i>
        </a>
    </td>
</tr>
@endforeach
@if($i==1)
<tr>
    <td colspan="9" style="text-align:center;">
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

