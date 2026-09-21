<div class="pull-right tableTools-container" style="margin-top:-35px;"></div>

<div>
<table id="dynamic-table" class="table table-striped table-bordered">
<thead>
<tr>
    <th class="myrectd" nowrap><b>S.NO.</b></th>
    <th nowrap>USER NAME</th>
    <th nowrap style="text-align:center;">DATE</th>
    <th nowrap>FEEDBACK / SUGESSION</th>
    <th nowrap style="text-align:center; width:75px;">MARK AS VIEWED</th>
    <th nowrap style="text-align:center; width:75px;">MARKED BY</th>
</tr>
</thead>
<tbody>

@php
$i=1
@endphp
@foreach ($data as $item)
<tr id="item-{{ $i }}">
    <td style="width:30px;" class="myrectd" nowrap>{{ $i++ }}</td>
    <td nowrap>{{ $item->Name }}</td>
    <td nowrap style="text-align:center;">{{ date('d\-m\-Y, h:i A',strtotime($item->creationdate)) }}</td>
    <td nowrap>{{ $item->feedback }}</td>
    <td nowrap style="text-align:center;">
        <input type="checkbox" name="{{$item->id}}" id="{{$item->id}}" onclick="Mark({{$item->id}})" @if($item->viewstatus==1) {{ 'checked disabled' }} @endif>
    </td>
    <td nowrap style="text-align:center;">{{ $item->viewedby }}</td>
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

<script>
function Mark(id)
{
    bootbox.confirm('DO YOU WANT TO VERIFY THIS USER RECORD!',function(result){
        if(result)
        {
            $.ajax({
                url: "{{ route('mark.save') }}", // Replace with your route
                type: "POST",
                headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},                
                data: {id:id},
                success: function(response) {
                    if(response.result==='success')
                    {
                        setTimeout(function(){
                            loadData(1,'{{ route('feedback.list') }}')
                        },2000);                        
                        alert('FEEDBACK / SUGESSION MARKED AS VIEWED SUCCESSFULLY!');                        
                    }
                },
                error: function(xhr, status, error) {
                    alert(xhr.responseText);
                }
            });         
        }
    })
}
</script>