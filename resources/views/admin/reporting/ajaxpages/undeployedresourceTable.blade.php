@php
    $totalAssociate = 0;
    $totalConsultant = 0;
    $totalSenior = 0;
    $totalPrincipal = 0;
    $totalManaging = 0;
    $grandTotal = 0;
@endphp


@if($format_id==1 && $categoryid==2)
@php
	$totalEng	=	0;
	$totalFin	=	0;
	$totalGov  	= 	0;
	$totalHeal 	= 	0;
	$totalInfo 	= 	0;
	$totalSoc 	= 	0;


    $totalAssociate = 0;
    $totalConsultant = 0;
    $totalSenior = 0;
    $totalPrincipal = 0;
    $totalManaging = 0;

    $grandTotalResources = 0;
@endphp
<tr>
<td class="">

<div class="table-responsive printableData">
    <table class="table table-bordered table-striped table-hover">

        <thead>
			<tr class="bg-primary text-white">
				<td colspan="14">
					<span>
					  <span class="bds bds-default width-152">Eng. - Engineering,</span>
					  <span class="bds bds-default width-152">Fin. - Finance & Leagal,</span>
					  <span class="bds bds-default width-152">Gov. - Governance,</span>
					  <span class="bds bds-default width-152">Hea. - Healthcare,</span>
					  <span class="bds bds-default width-152">IT. - Information Technology,</span>
					  <span class="bds bds-default width-152">Soc. - Social & Other Sector,</span>
					</span>
				
				</td>
			</tr>
			<tr class="bg-primary text-white">
				<td colspan="14">
					<span>
					  <span class="bds bds-pending width-152">A.C. - Associate Consultant,</span>
					  <span class="bds bds-active width-152">CO. - Consultant,</span>
					  <span class="bds bds-extended width-152">S.C. - Senior Consultant,</span>
					  <span class="bds bds-released width-152">P.C. - Principal Consultant,</span>
					  <span class="bds bds-success width-152">M.C. - Managing Consultant,</span>
					  <span class="bds bds-total width-152">T.R. - Total Resources,</span>
					</span>
				
				</td>
			</tr>
            <tr class="bg-primary text-white">
                <td nowrap class="width-30">S.No.</td>
                <td nowrap>Order Number</td>
                <td nowrap class="text-center">Eng.</td>
                <td nowrap class="text-center">Fin.</td>
                <td nowrap class="text-center">Gov.</td>
				<td nowrap class="text-center">Hea.</td>
				<td nowrap class="text-center">I.T.</td>
				<td nowrap class="text-center">Soc.</td>
                <td class="text-center"><span class="bds bds-pending">A.C.</span></td>
                <td class="text-center"><span class="bds bds-active">CO.</span></td>
                <td class="text-center"><span class="bds bds-extended">S.C.</span></td>
                <td class="text-center"><span class="bds bds-released">P.C.</span></td>
                <td class="text-center"><span class="bds bds-success">M.C.</span></td>
                <td class="text-center"><span class="bds bds-total">T.R.</span></td>
            </tr>
        </thead>

        <tbody>

        @forelse($data as $key => $row)

            @php
                $totalEng += $row->engineering;
                $totalFin += $row->finance;
				$totalGov += $row->governance;
				$totalHeal += $row->health;
				$totalInfo += $row->information;				
                $totalSoc += $row->social;

                $totalAssociate += $row->associate_consultant;
                $totalConsultant += $row->consultant;
                $totalSenior += $row->senior_consultant;
                $totalPrincipal += $row->principal_consultant;
                $totalManaging += $row->managing_consultant;

                $grandTotalResources += $row->total_resources;
            @endphp

            <tr>
                <td class="center width-30">{{ $key + 1 }}</td>
                <td>{{ $row->ordernumber }}</td>

                <td class="text-center">
					<span @if($row->engineering>0) onclick="FetchUndeployedResource({{$format_id}},{{$row->orderid}},{{$row->departmentid}},{{$categoryid}},4,0) @endif" style="cursor:pointer;">{{ $row->engineering }}</span>
				</td>
                <td class="text-center">
					<span @if($row->finance>0) onclick="FetchUndeployedResource({{$format_id}},{{$row->orderid}},{{$row->departmentid}},{{$categoryid}},2,0) @endif" style="cursor:pointer;">{{ $row->finance }}</span>
				</td>
                <td class="text-center">
					<span @if($row->governance>0) onclick="FetchUndeployedResource({{$format_id}},{{$row->orderid}},{{$row->departmentid}},{{$categoryid}},3,0) @endif" style="cursor:pointer;">{{ $row->governance }}</span>
				</td>
				<td class="text-center">
					<span @if($row->health>0) onclick="FetchUndeployedResource({{$format_id}},{{$row->orderid}},{{$row->departmentid}},{{$categoryid}},5,0) @endif" style="cursor:pointer;">{{ $row->health }}</span>
				</td>
				<td class="text-center">
					<span @if($row->information>0) onclick="FetchUndeployedResource({{$format_id}},{{$row->orderid}},{{$row->departmentid}},{{$categoryid}},1,0) @endif" style="cursor:pointer;">{{ $row->information }}</span>
				</td>
				<td class="text-center">
					<span @if($row->social>0) onclick="FetchUndeployedResource({{$format_id}},{{$row->orderid}},{{$row->departmentid}},{{$categoryid}},6,0) @endif" style="cursor:pointer;">{{ $row->social }}</span>
				</td>

                <td class="text-center">
					<span @if($row->associate_consultant>0) onclick="FetchUndeployedResource({{$format_id}},{{$row->orderid}},{{$row->departmentid}},{{$categoryid}},0,5) @endif" style="cursor:pointer;">{{ $row->associate_consultant }}</span>
				</td>
                <td class="text-center">
					<span @if($row->consultant>0) onclick="FetchUndeployedResource({{$format_id}},{{$row->orderid}},{{$row->departmentid}},{{$categoryid}},0,4) @endif" style="cursor:pointer;">{{ $row->consultant }}</span>
				</td>
                <td class="text-center">
					<span @if($row->senior_consultant>0) onclick="FetchUndeployedResource({{$format_id}},{{$row->orderid}},{{$row->departmentid}},{{$categoryid}},0,3) @endif" style="cursor:pointer;">{{ $row->senior_consultant }}</span>
				</td>
                <td class="text-center">
					<span @if($row->principal_consultant>0) onclick="FetchUndeployedResource({{$format_id}},{{$row->orderid}},{{$row->departmentid}},{{$categoryid}},0,2) @endif" style="cursor:pointer;">{{ $row->principal_consultant }}</span>
				</td>
                <td class="text-center">
					<span @if($row->managing_consultant>0) onclick="FetchUndeployedResource({{$format_id}},{{$row->orderid}},{{$row->departmentid}},{{$categoryid}},0,1) @endif" style="cursor:pointer;">{{ $row->managing_consultant }}</span>
				</td>

                <td class="text-center font-weight-bold">
                    <span @if($row->total_resources>0) onclick="FetchUndeployedResource({{$format_id}},{{$row->orderid}},{{$row->departmentid}},{{$categoryid}},0,0) @endif" style="cursor:pointer;">{{ $row->total_resources }}</span>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="15" class="text-center">No Records Found</td>
            </tr>

        @endforelse

        </tbody>

        @if(count($data))
            <tr style="font-weight:bold;background:#f5f5f5;">
                <td colspan="2" class="text-right">
                    Grand Total
                </td>

                <td class="text-center">{{ $totalEng }}</td>
                <td class="text-center">{{ $totalFin }}</td>
                <td class="text-center">{{ $totalGov }}</td>
				<td class="text-center">{{ $totalHeal }}</td>
				<td class="text-center">{{ $totalInfo }}</td>
				<td class="text-center">{{ $totalSoc }}</td>

                <td class="text-center">{{ $totalAssociate }}</td>
                <td class="text-center">{{ $totalConsultant }}</td>
                <td class="text-center">{{ $totalSenior }}</td>
                <td class="text-center">{{ $totalPrincipal }}</td>
                <td class="text-center">{{ $totalManaging }}</td>

                <td class="text-center">
                    {{ $grandTotalResources }}
                </td>
            </tr>
        @endif

    </table>
</div>
</td>
</tr>
@endif
