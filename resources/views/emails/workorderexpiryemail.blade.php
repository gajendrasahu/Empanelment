<!DOCTYPE html>
<html>
<head>
    <title>Your Email</title>
</head>
<body>
<div style="font-family: Arial, Helvetica, sans-serif; font-size: 14px; line-height: 1.6; color: #333;">

    <p>Dear Sir/Madam,</p>

    <p>This is a reminder regarding the upcoming expiry of the following Work Order:</p>

    <table style="border-collapse: collapse; margin: 10px 0 20px 0;">
        @if($data->eoinumber)
        <tr>
            <td style="padding: 4px 15px 4px 0;"><strong>EoI No.:</strong></td>
            <td style="padding: 4px 0;">{{ $data->eoinumber }}</td>
        </tr>
        @endif

        @if($data->eoinumber)
        <tr>
            <td style="padding: 4px 15px 4px 0;"><strong>Project Name:</strong></td>
            <td style="padding: 4px 0;">{{ $data->engagementname }}</td>
        </tr>
        @endif

        <tr>
            <td style="padding: 4px 15px 4px 0;"><strong>Work Order No.:</strong></td>
            <td style="padding: 4px 0;">{{ $data->ordernumber ?? '' }}</td>
        </tr>

        <tr>
            <td style="padding: 4px 15px 4px 0;"><strong>Work Order Date:</strong></td>
            <td style="padding: 4px 0;">
                {{ date('d\-m\-Y', strtotime($data->orderdate)) }}
            </td>
        </tr>

        <tr>
            <td style="padding: 4px 15px 4px 0;"><strong>Work Order Expiry Date:</strong></td>
            <td style="padding: 4px 0;">
                {{ date('d\-m\-Y', strtotime($data->workorderduedate)) }}
            </td>
        </tr>

        <tr>
            <td style="padding: 4px 15px 4px 0;"><strong>Time Remaining:</strong></td>
            <td style="padding: 4px 0;">
                <strong>{{ $remainingDays }} days</strong>
            </td>
        </tr>
    </table>

    <p>
        You are requested to kindly provide the necessary directions regarding the
        further continuation/extension of the Work Order.
    </p>

    <p style="margin-top: 20px; font-size: 13px; color: #666;">
        <strong>Disclaimer:</strong> If you have already provided your response or necessary directions regarding this Work Order, please ignore this email.
    </p>

    <p style="margin-top: 25px;">
        <strong>Regards,</strong><br>
        <strong>EMPL Team</strong><br>
        <strong>Chhattisgarh Infotech Promotion Society (CHiPS)</strong><br>
        SDC Building 2nd Floor, Civil Lines<br>
        Raipur - 492001
    </p>

</div>
</body>
</html>
