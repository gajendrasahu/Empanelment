<!DOCTYPE html>
<html>
<head>
    <title>EoI Published</title>
</head>
<body style="margin:0; padding:0; font-family:Arial, sans-serif; color:#000000;">

<!-- HEADER -->
<table width="100%" cellpadding="0" cellspacing="0" style="width:100%; height:11.69in; text-align:center; border:none; background-color:#95b3d7;">
    <tr>
        <td style="width:50%;"></td>
        <td style="width:30%; background-color:#ffffff; padding:10px; text-align:left;">

            <table width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;">
                <tr>
                    <td style="background-color:#1f487c; color:#ffffff; text-align:center; height:300px; border:1px solid #000000; vertical-align:bottom; padding:10px;">
                        <p style="margin:0; font-size:14px; line-height:20px;">
                            Chhattisgarh Infotech Promotion Society (CHiPS),<br>
                            State Data Centre Building, Near Police Control Room,<br>
                            Civil Lines, Raipur, Chhattisgarh–492001<br>
                            Tel.: +91-771-4014158<br>
                            Email: ceochips@nic.in
                        </p>
                    </td>
                </tr>
            </table>

            <div style="margin-top:15px;">
                <h3 style="margin:0 0 10px 0;">Expression of Interest (EoI)</h3>
                {{$data->projecttitle}}
            </div>

        </td>
        <td style="width:20%;"></td>
    </tr>
</table>

<!-- TABLE OF CONTENTS -->
@if($data->page_indexing!='')
<table width="100%" cellpadding="0" cellspacing="0" border="1" style="border-collapse:collapse; margin-top:10px;">
    <tr>
        <td colspan="2" style="padding:8px; font-weight:500; background-color:#e4e6e9;">
            <b>Table of Contents</b>
        </td>
    </tr>
    <tr>
        <td colspan="2" style="padding:8px;">
            {!! $data->page_indexing !!}
        </td>
    </tr>
</table>
@endif

<!-- FACT SHEET -->
<table width="100%" cellpadding="0" cellspacing="0" border="1" style="border-collapse:collapse; margin-top:10px;">
    <tr>
        <td colspan="2" style="padding:8px; font-weight:500; background-color:#e4e6e9;">
            <b>Fact Sheet</b>
        </td>
    </tr>

    <tr>
        <td style="padding:8px; font-weight:500;">Name of Issuer</td>
        <td style="padding:8px;">{{ $data->issuername }}</td>
    </tr>
    <tr>
        <td style="padding:8px; font-weight:500;">Name of the Engagement</td>
        <td style="padding:8px;">{{ $data->engagementname }}</td>
    </tr>
    <tr>
        <td style="padding:8px; font-weight:500;">Release Date of EoI by CHiPS</td>
        <td style="padding:8px;">{{ date('d-m-Y',strtotime($data->releasedate)) }}</td>
    </tr>
    <tr>
        <td style="padding:8px; font-weight:500;">Last Date of Pre-Bid Query</td>
        <td style="padding:8px;">{{ date('d-m-Y',strtotime($data->prebidlastdate)) }}</td>
    </tr>
    <tr>
        <td style="padding:8px; font-weight:500;">Last Date for Submission of Proposals</td>
        <td style="padding:8px;">{{ date('d-m-Y, h:i A',strtotime($data->deadlinedate)) }}</td>
    </tr>
    @if($data->interviewdate!='')
    <tr>
        <td style="padding:8px; font-weight:500;">Tentative Date of Presentation & Interview</td>
        <td style="padding:8px;">{{ date('d-m-Y',strtotime($data->interviewdate)) }}</td>
    </tr>
    @endif
    <tr>
        <td style="padding:8px; font-weight:500;">Address of Communication</td>
        <td style="padding:8px;">{!! $data->communicationaddress !!}</td>
    </tr>
</table>

<!-- PROJECT DETAILS -->
<table width="100%" cellpadding="0" cellspacing="0" border="1" style="border-collapse:collapse; margin-top:10px;">
    <tr>
        <td colspan="2" style="padding:8px; font-weight:500; background-color:#e4e6e9;"><b>Project Objective</b></td>
    </tr>
    <tr>
        <td colspan="2" style="padding:8px;">{!! $data->projectobjective !!}</td>
    </tr>
    @if($data->aboutproject!='')
    <tr>
        <td colspan="2" style="padding:8px; font-weight:500; background-color:#e4e6e9;"><b>About Project</b></td>
    </tr>
    <tr>
        <td colspan="2" style="padding:8px;">{!! $data->aboutproject !!}</td>
    </tr>
    @endif
    <tr>
        <td colspan="2" style="padding:8px; font-weight:500; background-color:#e4e6e9;"><b>Scope of Work</b></td>
    </tr>
    <tr>
        <td colspan="2" style="padding:8px;">{!! $data->scopeofwork !!}</td>
    </tr>
    <tr>
        <td colspan="2" style="padding:8px; font-weight:500; background-color:#e4e6e9;"><b>Special Condition of Contract</b></td>
    </tr>
    <tr>
        <td colspan="2" style="padding:8px;">{!! $data->anyother !!}</td>
    </tr>
</table>

<!-- RESOURCE DETAILS -->
<table width="100%" cellpadding="0" cellspacing="0" border="1" style="border-collapse:collapse; margin-top:10px;">
    <tr>
        <td colspan="6" style="padding:8px; font-weight:500; background-color:#e4e6e9;"><b>Resource Details</b></td>
    </tr>
    <tr>
        <td style="padding:5px; text-align:center; width:50px;">S.No.</td>
        @if($data->categoryid==1)
        <td style="padding:5px;">Role</td>
        @elseif($data->categoryid==2)
        <td style="padding:5px;">Sector</td>
        <td style="padding:5px;">Position</td>
        @endif
        <td style="padding:5px;">Experience</td>
        @if($data->categoryid==1)
        <td style="padding:5px;">Qualification</td>
        @endif
        <td style="padding:5px;">Duration</td>
        <td style="padding:5px;">Remark</td>
    </tr>
    @php $i=1; $total=0; @endphp
    @foreach($detail as $detail)
    <tr>
        <td style="padding:5px; text-align:center;">{{$i}}</td>
        @if($data->categoryid==2)
        <td style="padding:5px;">{{ucwords(strtolower($detail->sectorname))}}</td>
        <td style="padding:5px;">{{$detail->consultantposition}}</td>
        @else
        <td style="padding:5px;">{{$detail->role}}</td>
        @endif
        @if($data->categoryid==1)
        <td style="padding:5px;">{{$detail->workexperience}} [Level-{{$detail->experiencelevel}}]</td>
        @else
        <td style="padding:5px;">{{$detail->experience}}<br>{{$detail->qualification}}</td>
        @endif
        @if($data->categoryid==1)
        <td style="padding:5px;">{{$detail->qualification}}</td>
        @endif
        <td style="padding:5px;">{{$detail->duration}} Months</td>
        <td style="padding:5px;">{{$detail->remark}}</td>
    </tr>
    @php $i++; @endphp
    @endforeach
    @if($i==1)
    <tr>
        <td colspan="6" style="padding:5px; text-align:center;">--NO RECORD ADDED--</td>
    </tr>
    @endif
</table>

<!-- EVALUATION / TERMS / DOCUMENTS -->
<table width="100%" cellpadding="0" cellspacing="0" border="1" style="border-collapse:collapse; margin-top:10px;">
    @if($data->evaluationprocess!='')
    <tr>
        <td colspan="2" style="padding:8px; font-weight:500; background-color:#e4e6e9;"><b>Evaluation Process</b></td>
    </tr>
    <tr>
        <td colspan="2" style="padding:8px;">{!! $data->evaluationprocess !!}</td>
    </tr>
    @endif

    @if($data->termsandcondition!='')
    <tr>
        <td colspan="2" style="padding:8px; font-weight:500; background-color:#e4e6e9;"><b>Payment & Penalty Terms</b></td>
    </tr>
    <tr>
        <td colspan="2" style="padding:8px;">{!! $data->termsandcondition !!}</td>
    </tr>
    @endif

    @if($data->criticalinformation!='')
    <tr>
        <td colspan="2" style="padding:8px; font-weight:500; background-color:#e4e6e9;"><b>Critical Information</b></td>
    </tr>
    <tr>
        <td colspan="2" style="padding:8px;">{!! $data->criticalinformation !!}</td>
    </tr>
    @endif

    @if($data->documentrequired!='')
    <tr>
        <td colspan="2" style="padding:8px; font-weight:500; background-color:#e4e6e9;"><b>Document Required as Response to this EoI</b></td>
    </tr>
    <tr>
        <td colspan="2" style="padding:8px;">{!! $data->documentrequired !!}</td>
    </tr>
    @endif
</table>

<!-- END -->
<div style="width:100%; text-align:center; margin-top:20px; font-weight:500;">
    ***End of Document***
</div>

</body>
</html>