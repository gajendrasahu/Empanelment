@php
    $total_1 = 0;
	$total_2 = 0;
	$total_3 = 0;
	$total_4 = 0;
	$total_5 = 0;
	$total_6 = 0;
	$total_7 = 0;
	$total_8 = 0;
	$total_9 = 0;
	$total_10 = 0;
	$total_11 = 0;
	$total_12 = 0;
	$total_13 = 0;
	$total_14 = 0;
	$total_15 = 0;
	$total_16 = 0;
	$total_17 = 0;
	$total_18 = 0;

    $grandTotal = 0;
	$total_orders=	0;
@endphp


@if($format_id==1 && $categoryid==1)
<tr>
<td class="">
<div class="table-responsive printableData">
    <table class="table table-bordered table-striped" style="border:1px solid #ddd!important;">
        <thead>
			<tr class="text-white">
				<td colspan="4" style="border:1px solid #ddd!important;"><span class="bds bds-total">T.R. - Total Resources</span></td>
				<td style="border:1px solid #ddd!important;" colspan="18" class="text-center">Levels</td>
				<td style="border:1px solid #ddd!important;"></td>
			</tr>
            <tr class="bg-primary text-white">
                <td style="border:1px solid #ddd!important;" class="center width-30">S.No.</td>
                <td style="border:1px solid #ddd!important;" nowrap>Order Number</td>
				<td style="border:1px solid #ddd!important;" nowrap>Project Manager</td>
                <td style="border:1px solid #ddd!important;">Firm</td>
                <td style="border:1px solid #ddd!important;" class="text-center">1</td>
                <td style="border:1px solid #ddd!important;" class="text-center">2</td>
                <td style="border:1px solid #ddd!important;" class="text-center">3</td>
                <td style="border:1px solid #ddd!important;" class="text-center">4</td>
                <td style="border:1px solid #ddd!important;" class="text-center">5</td>
                <td style="border:1px solid #ddd!important;" class="text-center">6</td>
				<td style="border:1px solid #ddd!important;" class="text-center">7</td>
				<td style="border:1px solid #ddd!important;" class="text-center">8</td>
				<td style="border:1px solid #ddd!important;" class="text-center">9</td>
				<td style="border:1px solid #ddd!important;" class="text-center">10</td>
				<td style="border:1px solid #ddd!important;" class="text-center">11</td>
				<td style="border:1px solid #ddd!important;" class="text-center">12</td>
				<td style="border:1px solid #ddd!important;" class="text-center">13</td>
				<td style="border:1px solid #ddd!important;" class="text-center">14</td>
				<td style="border:1px solid #ddd!important;" class="text-center">15</td>
				<td style="border:1px solid #ddd!important;" class="text-center">16</td>
				<td style="border:1px solid #ddd!important;" class="text-center">17</td>
				<td style="border:1px solid #ddd!important;" class="text-center">18</td>
				<td style="border:1px solid #ddd!important;" class="text-center" nowrap><span class="bds bds-total">T.R.</span></td>
            </tr>
        </thead>

        <tbody>
            @forelse($data as $key => $row)

                @php
                    $total_1 += $row->l1;
					$total_2 += $row->l2;
					$total_3 += $row->l3;
					$total_4 += $row->l4;
					$total_5 += $row->l5;
					$total_6 += $row->l6;
					$total_7 += $row->l7;
					$total_8 += $row->l8;
					$total_9 += $row->l9;
					$total_10 += $row->l10;
					$total_11 += $row->l11;
					$total_12 += $row->l12;
					$total_13 += $row->l13;
					$total_14 += $row->l14;
					$total_15 += $row->l15;
					$total_16 += $row->l16;
					$total_17 += $row->l17;
					$total_18 += $row->l18;
                    $grandTotal += $row->total_resources;
                @endphp

                <tr>
                    <td style="border:1px solid #ddd!important;" class="center width-30">{{ $key + 1 }}</td>

                    <td style="border:1px solid #ddd!important;" nowrap onclick="setSearchingData('{{$row->work_order_no}}')" title="{{$row->work_order_no}}">
						{{ $row->work_order_no }}
					</td>

                    <td style="border:1px solid #ddd!important;" nowrap onclick="setManager({{$row->departmentid}})" title="{{$row->department_name}}">@if($row->department!='PM'){{ $row->department }}@else {{ $row->department_name }} @endif</td>
                    
					<td style="border:1px solid #ddd!important;" nowrap onclick="setFirm({{$row->vendorid}})" title="{{$row->vendor_name}}">
						{{ $row->vendor }}
					</td>

                    <td style="border:1px solid #ddd!important;" class="text-center">
						<span @if($row->l1>0) onclick="FetchAwdResource({{$format_id}},{{$row->departmentid}},{{$row->vendorid}},{{$row->orderid}},{{$categoryid}},1) @endif" style="cursor:pointer;"> @if($row->l1>0){{ $row->l1 }} @else - @endif
						</span>
					<span>
					</td>
					<td style="border:1px solid #ddd!important;" class="text-center">
						<span @if($row->l2>0) onclick="FetchAwdResource({{$format_id}},{{$row->departmentid}},{{$row->vendorid}},{{$row->orderid}},{{$categoryid}},2) @endif" style="cursor:pointer;"> @if($row->l2>0){{ $row->l2 }} @else - @endif
						</span>
					</td>
					<td style="border:1px solid #ddd!important;" class="text-center">
						<span @if($row->l3>0) onclick="FetchAwdResource({{$format_id}},{{$row->departmentid}},{{$row->vendorid}},{{$row->orderid}},{{$categoryid}},3) @endif" style="cursor:pointer;"> @if($row->l3>0){{ $row->l3 }} @else - @endif
						</span>
					</td>
					<td style="border:1px solid #ddd!important;" class="text-center">
						<span @if($row->l4>0) onclick="FetchAwdResource({{$format_id}},{{$row->departmentid}},{{$row->vendorid}},{{$row->orderid}},{{$categoryid}},4) @endif" style="cursor:pointer;"> @if($row->l4>0){{ $row->l4 }} @else - @endif
						</span>
					</td>
					<td style="border:1px solid #ddd!important;" class="text-center">
						<span @if($row->l5>0) onclick="FetchAwdResource({{$format_id}},{{$row->departmentid}},{{$row->vendorid}},{{$row->orderid}},{{$categoryid}},5) @endif" style="cursor:pointer;"> @if($row->l5>0){{ $row->l5 }} @else - @endif
						</span>
					</td>
					<td style="border:1px solid #ddd!important;" class="text-center">
						<span @if($row->l6>0) onclick="FetchAwdResource({{$format_id}},{{$row->departmentid}},{{$row->vendorid}},{{$row->orderid}},{{$categoryid}},6) @endif" style="cursor:pointer;"> @if($row->l6>0){{ $row->l6 }} @else - @endif
						</span>
					</td>
					<td style="border:1px solid #ddd!important;" class="text-center">
						<span @if($row->l7>0) onclick="FetchAwdResource({{$format_id}},{{$row->departmentid}},{{$row->vendorid}},{{$row->orderid}},{{$categoryid}},7) @endif" style="cursor:pointer;"> @if($row->l7>0){{ $row->l7 }} @else - @endif
						</span>
					</td>
					<td style="border:1px solid #ddd!important;" class="text-center">
						<span @if($row->l8>0) onclick="FetchAwdResource({{$format_id}},{{$row->departmentid}},{{$row->vendorid}},{{$row->orderid}},{{$categoryid}},8) @endif" style="cursor:pointer;"> @if($row->l8>0){{ $row->l8 }} @else - @endif
						</span>
					</td>
					<td style="border:1px solid #ddd!important;" class="text-center">
						<span @if($row->l9>0) onclick="FetchAwdResource({{$format_id}},{{$row->departmentid}},{{$row->vendorid}},{{$row->orderid}},{{$categoryid}},9) @endif" style="cursor:pointer;"> @if($row->l9>0){{ $row->l9 }} @else - @endif
						</span>
					</td>
					<td style="border:1px solid #ddd!important;" class="text-center">
						<span @if($row->l10>0) onclick="FetchAwdResource({{$format_id}},{{$row->departmentid}},{{$row->vendorid}},{{$row->orderid}},{{$categoryid}},10) @endif" style="cursor:pointer;"> @if($row->l10>0){{ $row->l10 }} @else - @endif
						</span>
					</td>
					<td style="border:1px solid #ddd!important;" class="text-center">
						<span @if($row->l11>0) onclick="FetchAwdResource({{$format_id}},{{$row->departmentid}},{{$row->vendorid}},{{$row->orderid}},{{$categoryid}},11) @endif" style="cursor:pointer;"> @if($row->l11>0){{ $row->l11 }} @else - @endif
						</span>
					</td>
					<td style="border:1px solid #ddd!important;" class="text-center">
						<span @if($row->l12>0) onclick="FetchAwdResource({{$format_id}},{{$row->departmentid}},{{$row->vendorid}},0,{{$categoryid}},12) @endif" style="cursor:pointer;"> @if($row->l12>0){{ $row->l12 }} @else - @endif
						</span>
					</td>
					<td style="border:1px solid #ddd!important;" class="text-center">
						<span @if($row->l13>0) onclick="FetchAwdResource({{$format_id}},{{$row->departmentid}},{{$row->vendorid}},{{$row->orderid}},{{$categoryid}},13) @endif" style="cursor:pointer;"> @if($row->l13>0){{ $row->l13 }} @else - @endif
						</span>
					</td>
					<td style="border:1px solid #ddd!important;" class="text-center">
						<span @if($row->l14>0) onclick="FetchAwdResource({{$format_id}},{{$row->departmentid}},{{$row->vendorid}},{{$row->orderid}},{{$categoryid}},14) @endif" style="cursor:pointer;"> @if($row->l14>0){{ $row->l14 }} @else - @endif
						</span>
					</td>
					<td style="border:1px solid #ddd!important;" class="text-center">
						<span @if($row->l15>0) onclick="FetchAwdResource({{$format_id}},{{$row->departmentid}},{{$row->vendorid}},{{$row->orderid}},{{$categoryid}},15) @endif" style="cursor:pointer;"> @if($row->l15>0){{ $row->l15 }} @else - @endif
						</span>
					</td>
					<td style="border:1px solid #ddd!important;" class="text-center">
						<span @if($row->l16>0) onclick="FetchAwdResource({{$format_id}},{{$row->departmentid}},{{$row->vendorid}},{{$row->orderid}},{{$categoryid}},16) @endif" style="cursor:pointer;"> @if($row->l16>0){{ $row->l16 }} @else - @endif
						</span>
					</td>
					<td style="border:1px solid #ddd!important;" class="text-center">
						<span @if($row->l17>0) onclick="FetchAwdResource({{$format_id}},{{$row->departmentid}},{{$row->vendorid}},{{$row->orderid}},{{$categoryid}},17) @endif" style="cursor:pointer;"> @if($row->l17>0){{ $row->l17 }} @else - @endif
						</span>
					</td>
					<td style="border:1px solid #ddd!important;" class="text-center">
						<span @if($row->l18>0) onclick="FetchAwdResource({{$format_id}},{{$row->departmentid}},{{$row->vendorid}},{{$row->orderid}},{{$categoryid}},18) @endif" style="cursor:pointer;"> @if($row->l18>0){{ $row->l18 }} @else - @endif
						</span>
					</td>

                    <td style="border:1px solid #ddd!important;" class="text-center font-weight-bold">
						<span @if($row->total_resources>0) onclick="FetchAwdResource({{$format_id}},{{$row->departmentid}},{{$row->vendorid}},{{$row->orderid}},{{$categoryid}},0) @endif" style="cursor:pointer;"> {{ $row->total_resources }}
						</span>
					
					</td>
                </tr>

            @empty
                <tr>
                    <td style="border:1px solid #ddd!important;" colspan="23" class="text-center">
                        No records found.
                    </td>
                </tr>
            @endforelse
        </tbody>

        @if(count($data))
        
            <tr style="font-weight:bold; background:#f5f5f5;">
                <td style="border:1px solid #ddd!important;" colspan="4" class="text-right">
                    Grand Total
                </td>

                <td style="border:1px solid #ddd!important;" class="text-center width-30">{{ $total_1 }}</td>
				<td style="border:1px solid #ddd!important;" class="text-center width-30">{{ $total_2 }}</td>
				<td style="border:1px solid #ddd!important;" class="text-center width-30">{{ $total_3 }}</td>
				<td style="border:1px solid #ddd!important;" class="text-center width-30">{{ $total_4 }}</td>
				<td style="border:1px solid #ddd!important;" class="text-center width-30">{{ $total_5 }}</td>
				<td style="border:1px solid #ddd!important;" class="text-center width-30">{{ $total_6 }}</td>
				<td style="border:1px solid #ddd!important;" class="text-center width-30">{{ $total_7 }}</td>
				<td style="border:1px solid #ddd!important;" class="text-center width-30">{{ $total_8 }}</td>
				<td style="border:1px solid #ddd!important;" class="text-center width-30">{{ $total_9 }}</td>
				<td style="border:1px solid #ddd!important;" class="text-center width-30">{{ $total_10 }}</td>
				<td style="border:1px solid #ddd!important;" class="text-center width-30">{{ $total_11 }}</td>
				<td style="border:1px solid #ddd!important;" class="text-center width-30">{{ $total_12 }}</td>
				<td style="border:1px solid #ddd!important;" class="text-center width-30">{{ $total_13 }}</td>
				<td style="border:1px solid #ddd!important;" class="text-center width-30">{{ $total_14 }}</td>
				<td style="border:1px solid #ddd!important;" class="text-center width-30">{{ $total_15 }}</td>
				<td style="border:1px solid #ddd!important;" class="text-center width-30">{{ $total_16 }}</td>
				<td style="border:1px solid #ddd!important;" class="text-center width-30">{{ $total_17 }}</td>
				<td style="border:1px solid #ddd!important;" class="text-center width-30">{{ $total_18 }}</td>
                <td style="border:1px solid #ddd!important;" class="text-center width-30">{{ $grandTotal }}</td>
            </tr>
        
        @endif
    </table>
</div>
</td>
</tr>
@endif


