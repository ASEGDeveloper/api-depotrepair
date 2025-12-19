<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Inspection Report</title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 14px;
            color: #333;
            line-height: 1.6;
        }
        .container {
            max-width: 650px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            margin-bottom: 20px;
        }
        .footer {
            margin-top: 30px;
            font-size: 13px;
        }
    </style>
</head>
<body>
<div class="container">

    <div class="header">
        <strong>Dear {{ $name ?? 'Customer' }},</strong>
    </div>

    {{-- OFF-HIRE Inspection --}}
    @if($surveyType === 'Off-hire')
        <p>
            Please find attached <strong>OFF-Hire inspection report</strong> for the tank container
            <strong>{{ $itemNumber }}</strong> received in
            <strong>Cryotech Middle East</strong> on
            <strong>{{ $surveyDate }}</strong>.
        </p>

        <p>
            If you have any questions or require further information, please do not hesitate to contact us.
        </p>

       

    {{-- ON-HIRE Inspection --}}
    @elseif($surveyType === 'On-hire')
        <p>
            Please find attached <strong>ON-Hire inspection report</strong> for the tank container
            <strong>{{ $itemNumber }}</strong> released from
            <strong>Cryotech Middle East</strong> on
            <strong>{{ $surveyDate }}</strong>.
        </p>

        <p>
            If you have any questions, please do not hesitate to contact us.
        </p>
    @endif

    <div class="footer">
        <p>
            Best regards,<br>
            <strong>Cryotech Middle East</strong>
        </p>
    </div>

</div>
</body>
</html>
