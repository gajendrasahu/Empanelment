<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Draft Expression of Interest (EoI) Form - CHiPS</title>
	<link rel="stylesheet" href="{{ asset('panel/assets/font-awesome/4.5.0/css/font-awesome.min.css')}}" />	
    <style>
        /* Print-friendly CSS with no external links */
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        
        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
            line-height: 1.2;
            color: #333;
            background-color: #fff;
            padding: 10px;
        }
        
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: #fff;
        }
        
        /* Header styles */
        .header {
            text-align: center;
            border-bottom: 2px solid #147E8B;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }
        
        .logo-section {
            margin-bottom: 8px;
        }
        
        .logo-placeholder {
            width: 60px;
            height: 40px;
            background-color: #147E8B;
            color: white;
            display: inline-block;
            text-align: center;
            line-height: 40px;
            font-weight: bold;
            margin-bottom: 5px;
            font-size: 12px;
        }
        
        .org-name {
            font-size: 11px;
            color: #666;
            margin-bottom: 3px;
        }
        
        .portal-name {
            font-size: 14px;
            font-weight: bold;
            color: #2c5530;
            margin-bottom: 5px;
        }
        
        .form-title {
            font-size: 12px;
            font-weight: bold;
            color: #333;
            background-color: #f8f9fa;
            padding: 6px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        
        /* Department section */
        .department-section {
            background-color: #f8f9fa;
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 12px;
            margin-bottom: 15px;
        }
        
        .department-header {
            background-color: #147E8B;
            color: white;
            padding: 5px 10px;
            margin: -12px -12px 10px -12px;
            font-weight: bold;
            border-radius: 3px 3px 0 0;
            font-size: 11px;
        }
        
        .dept-info {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
            margin-bottom: 8px;
        }
        
        .info-item {
            display: flex;
            flex-direction: column;
        }
        
        .info-label {
            font-weight: bold;
            color: #555;
            margin-bottom: 2px;
            font-size: 9px;
        }
        
        .info-value {
            color: #333;
            padding: 2px 0;
            border-bottom: 1px solid #eee;
            font-size: 10px;
        }
        
        /* Table styles */
        .requirements-section {
            margin-bottom: 15px;
        }
        
        .section-title {
            font-size: 11px;
            font-weight: bold;
            color: #2c5530;
            margin-bottom: 8px;
            padding-bottom: 3px;
            border-bottom: 2px solid #2c5530;
        }
        
        .requirements-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
            font-size: 9px;
        }
        
        .requirements-table th {
            background-color: #147E8B;
            color: white;
            padding: 6px 4px;
            text-align: left;
            font-weight: bold;
            border: 1px solid #2c5530;
        }
        
        .requirements-table td {
            padding: 4px;
            border: 1px solid #ddd;
            vertical-align: top;
        }
        
        .requirements-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        
        .requirements-table tr:hover {
            background-color: #f5f5f5;
        }
        
        .total-budget {
            text-align: right;
            font-weight: bold;
            background-color: #e8f5e8;
            padding: 6px;
            border: 2px solid #2c5530;
            margin-top: 5px;
            font-size: 10px;
        }
        
        /* Content sections */
        .content-section {
            margin-bottom: 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            overflow: hidden;
        }
        
        .content-header {
            background-color: #f8f9fa;
            padding: 6px 10px;
            font-weight: bold;
            color: #2c5530;
            border-bottom: 1px solid #ddd;
            font-size: 10px;
        }
        
        .content-body {
            padding: 8px;
            min-height: 30px;
            background-color: #fff;
            font-size: 9px;
        }
        
        /* Conditions section */
        .conditions-section {
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 4px;
            padding: 10px;
            margin-bottom: 15px;
        }
        
        .conditions-text {
            font-size: 9px;
            line-height: 1.3;
            text-align: justify;
        }
        
        /* Signature section */
        .signature-section {
            margin-top: 20px;
            text-align: right;
        }
        
        .signature-box {
            border: 2px solid #2c5530;
            width: 200px;
            height: 60px;
            margin-left: auto;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            color: #666;
            font-size: 9px;
        }
        
        /* Print styles */
        @media print {
            body {
                padding: 0;
                font-size: 9px;
                margin: 0;
            }
            
            .container {
                max-width: none;
                margin: 0;
            }
            
            .header {
                border-bottom: 2px solid #000;
                margin-bottom: 10px;
                padding-bottom: 8px;
            }
            
            .logo-placeholder {
                background-color: #000;
            }
            
            .portal-name, .section-title {
                color: #000;
            }
            
            .department-header {
                background-color: #000;
            }
            
            .requirements-table th {
                background-color: #000;
                border-color: #000;
            }
            
            .total-budget {
                border-color: #000;
            }
            
            .signature-box {
                border-color: #000;
            }
            
            .department-section,
            .requirements-section,
            .content-section,
            .conditions-section,
            .signature-section {
                page-break-inside: avoid;
                margin-bottom: 8px;
            }
            
            @page {
                margin: 0.5in;
                size: A4;
            }
        }
        
        /* Responsive design */
        @media (max-width: 768px) {
            .dept-info {
                grid-template-columns: 1fr;
            }
            
            .requirements-table {
                font-size: 10px;
            }
            
            .requirements-table th,
            .requirements-table td {
                padding: 6px 4px;
            }
        }
		.center
		{
			text-align:center!important;
		}
		.f-right
		{
			text-align:right!important;
		}
    </style>
</head>
<body>
    <div class="container">
        <!-- Header Section -->
        <div class="header">
            <div class="logo-section">
                <img src="{{asset('panel/assets/images/basic/logo.png')}}" style="width:100px;">
            </div>
            <div class="org-name">Chhattisgarh Infotech Promotion Society | Government of Chhattisgarh</div>
            <div class="form-title">EoI</div>
        </div>
        
        <!-- Department Information -->
        <div class="department-section">
            <div class="department-header">Department</div>
            <div class="dept-info">
                <div class="info-item">
                    <span class="info-label">Department Name:</span>
                    <span class="info-value">{{$department->departmentname}}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Designation:</span>
                    <span class="info-value">{{$department->designation}}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Contact Number:</span>
                    <span class="info-value">{{$user->mobilenumber}}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Email Id:</span>
                    <span class="info-value">{{$user->email}}</span>
                </div>
            </div>
        </div>
        
        <!-- Requirements Table -->
        <div class="requirements-section">
            <div class="section-title">Consultant Requirements</div>
            <table class="requirements-table">
                <thead>
                    <tr><th colspan="7">Resource Details According to Tier-1 Pricing Structure</th></tr>
                    </tr>
                    <tr>
                        <th class="center">S.No.</th>
                        <th>Sector</th>
                        <th>Position</th>
						<th>Experience & Qualification</th>
						<th>Remark</th>
                        <th>Duration</th>
                        <th class="f-right">Man Month Rate (Including tax)</th>
                    </tr>
                </thead>
                <tbody>
				@php
				$total	=	0;
				@endphp
				@foreach($eoidata as $eoi)
                    <tr>
                        <td class="center">{{$loop->iteration}}</td>
                        <td>{{ucwords(strtolower($eoi->sectorname))}}</td>
                        <td>{{$eoi->consultantposition}}</td>
                        <td>{{$eoi->experience}}<br>{{$eoi->qualification}}</td>
						<td>{{$eoi->remark}}</td>
                        <td>{{$eoi->duration}} Months</td>
                        <td class="f-right">{{$eoi->budget}}</td>
                    </tr>
					@php
					@endphp
				@endforeach
				@php
				@endphp
                </tbody>
				<tfoot>
					<tr>
						<td class="f-right info-label" colspan="6">Cost of Resources (Incuding Tax)</td>
						<td id="total-cell" class="f-right info-label"><i class="fa fa-inr"></i> {{$totalmanmonth}}</td>
					</tr>
					<tr>
						<td class="f-right info-label" colspan="6">CHiPS Admin Charge ({{$admincharge}}%)</td>
						<td id="admincost-cell" class="f-right info-label">{{$adminchargetotal}}</td>
					</tr>
					<tr class="total-budget">
						<td class="f-right info-label" colspan="6">Grand Total</td>
						<td id="grand-cell" class="f-right info-label">{{$grandtotal}}</td>
					</tr>
				</tfoot>
            </table>


            <table class="requirements-table">
                <thead>
                    <tr><th colspan="7">Resource Details According to Tier-2 Pricing Structure</th></tr>
                    </tr>
                    <tr>
                        <th class="center">S.No.</th>
                        <th>Sector</th>
                        <th>Position</th>
						<th>Experience & Qualification</th>
						<th>Remark</th>
                        <th>Duration</th>
                        <th class="f-right">Man Month Rate (Including tax)</th>
                    </tr>
                </thead>
                <tbody>
				@php
				$total	=	0;
				@endphp
				@foreach($eoidata1 as $eoi)
                    <tr>
                        <td class="center">{{$loop->iteration}}</td>
                        <td>{{ucwords(strtolower($eoi->sectorname))}}</td>
                        <td>{{$eoi->consultantposition}}</td>
                        <td>{{$eoi->experience}}<br>{{$eoi->qualification}}</td>
						<td>{{$eoi->remark}}</td>
                        <td>{{$eoi->duration}} Months</td>
                        <td class="f-right">{{$eoi->budget}}</td>
                    </tr>
					@php
					@endphp
				@endforeach
				@php
				@endphp
                </tbody>
				<tfoot>
					<tr>
						<td class="f-right info-label" colspan="6">Cost of Resources (Incuding Tax)</td>
						<td id="total-cell" class="f-right info-label"><i class="fa fa-inr"></i> {{$totalmanmonth1}}</td>
					</tr>
					<tr>
						<td class="f-right info-label" colspan="6">CHiPS Admin Charge ({{$admincharge}}%)</td>
						<td id="admincost-cell" class="f-right info-label">{{$adminchargetotal1}}</td>
					</tr>
					<tr class="total-budget">
						<td class="f-right info-label" colspan="6">Grand Total</td>
						<td id="grand-cell" class="f-right info-label">{{$grandtotal1}}</td>
					</tr>
				</tfoot>
            </table>
        </div>
        
        <div class="content-section">
            <div class="content-header">Project Objective</div>
            <div class="content-body">
			{!!$projectobjective!!}
            </div>
        </div>

        <!-- About Project Section -->
        <div class="content-section">
            <div class="content-header">About Project</div>
            <div class="content-body">
			{!!$aboutproject!!}
            </div>
        </div>
        
        <!-- Scope Of Work Section -->
        <div class="content-section">
            <div class="content-header">Scope Of Work</div>
            <div class="content-body">
                {!!$scope!!}
            </div>
        </div>
        
        <!-- Conditions Section -->
        <div class="conditions-section">
            <div class="section-title">Condition</div>
            <div class="conditions-text">
                I hereby confirm that all the information provided above is accurate and submitted by me. I understand that this 
                requirement is essential for my department's project, and I take full responsibility for the authenticity of the details. I 
                agree to comply with CHiPS policies and acknowledge that the request will be processed as per internal guidelines.
            </div>
        </div>
        
        <!-- Signature Section -->
        <div class="signature-section">
            <div class="signature-box">
                Approving Authority (Signature & Seal)
            </div>
        </div>
    </div>

<script src="{{ asset('panel/assets/js/jquery-2.1.4.min.js') }}"></script>

<script src="{{ asset('panel/assets/js/datatablePaginationWithFilter.js') }}"></script>
</body>
</html>