<div class="pull-right tableTools-container" style="margin-top:-35px;"></div>

<div>
<table id="dynamic-table" class="table table-striped table-bordered">
<thead>
<tr>
    <th class="myrectd" nowrap><b>S.NO.</b></th>
    <th nowrap style="width:100px; text-align:center;">CATEGORY ICON</th>
    <th nowrap>SERVICE NAME</th>
    <th nowrap>PARENT CATEGORY</th>
    <th nowrap>SERVICE CATEGORY</th>
	<th nowrap style="text-align:center; width:130px;">DISPLAY ORDER</th>
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
    <td nowrap style="text-align:center;">
        <img src="{{ asset('storage/'.$item->categoryicon) }}" alt="Uploaded Image" style="width:75px;">
    </td>
    <td nowrap>{{ $item->servicename }}</td>
    <td nowrap>{{ $item->parentcategory }}</td>
    <td nowrap>{{ $item->servicecategory }}</td>    
	<td nowrap style="text-align:center;">{{ $item->displayorder }}</td>
    <td nowrap style="text-align:center;">{{ date('d\-m\-Y, h:i A',strtotime($item->creationdate)) }}</td>
    <td nowrap style="text-align: left;">{{ $item->createdby }}</td>
    <td nowrap style="text-align:center;"><a href="{{ route('edit.servicecategory',$item->servicecategoryid) }}" title="" class="myactionlink"><i class="fa fa-edit"></i></a></td>
    <td nowrap style="text-align:center;">
        <a title="" class="myactionlink" onclick="deleteItem('servicecategory',{{ $item->servicecategoryid}},{{ $i }},'ARE YOU SURE YOU WANT TO DELETE THIS RECORD?','SERVICE CATEGORY DELETED SUCCESSFULLY')" title=""><i class="fa fa-trash"></i>
        </a>
    </td>
</tr>
@endforeach
@if($i==1)
<tr>
    <td colspan="10" style="text-align:center;">
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

