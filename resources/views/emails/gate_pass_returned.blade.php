<!DOCTYPE html>
<html lang="en" xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Gate Pass Closed</title>
    <!--[if mso]>
    <noscript>
        <xml>
            <o:OfficeDocumentSettings>
                <o:PixelsPerInch>96</o:PixelsPerInch>
            </o:OfficeDocumentSettings>
        </xml>
    </noscript>
    <![endif]-->
    <style type="text/css">
        body, table, td, p, a { -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; }
        table, td { mso-table-lspace: 0pt; mso-table-rspace: 0pt; border-collapse: collapse; }
        img { -ms-interpolation-mode: bicubic; border: 0; }
        body { margin: 0; padding: 0; background-color: #f4f4f4; font-family: Arial, Helvetica, sans-serif; }
    </style>
</head>
<body style="margin:0; padding:0; background-color:#f4f4f4; font-family:Arial,Helvetica,sans-serif;">

<!-- Outer wrapper -->
<table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="background-color:#f4f4f4;">
    <tr>
        <td align="center" style="padding:16px 10px;">

            <!-- Main container -->
            <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="620" style="background-color:#ffffff; border:1px solid #dddddd;">

                <!-- Header -->
                <tr>
                    <td bgcolor="#00695c" style="background-color:#00695c; padding:12px 20px;">
                        <p style="margin:0; font-family:Arial,Helvetica,sans-serif; font-size:18px; font-weight:bold; color:#ffffff;">Gate Pass Return Completed</p>
                        <p style="margin:4px 0 0 0; font-family:Arial,Helvetica,sans-serif; font-size:12px; color:rgba(255,255,255,0.85);">All returnable materials have been received and verified by Security</p>
                    </td>
                </tr>

                <!-- Body -->
                <tr>
                    <td style="padding:16px 20px;">

                        <!-- Details table -->
                        <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="border:1px solid #dddddd; margin-bottom:12px;">
                            <tr>
                                <td width="38%" style="padding:5px 12px; border-bottom:1px solid #eeeeee; font-family:Arial,Helvetica,sans-serif; font-size:13px; color:#888888;">Gate Pass No</td>
                                <td style="padding:5px 12px; border-bottom:1px solid #eeeeee; font-family:Arial,Helvetica,sans-serif; font-size:13px; color:#333333; font-weight:bold;">{{ $gatePass->gate_pass_no ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td width="38%" style="padding:5px 12px; border-bottom:1px solid #eeeeee; font-family:Arial,Helvetica,sans-serif; font-size:13px; color:#888888;">WO Number</td>
                                <td style="padding:5px 12px; border-bottom:1px solid #eeeeee; font-family:Arial,Helvetica,sans-serif; font-size:13px; color:#333333; font-weight:bold;">{{ $gatePass->wo_number ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td width="38%" style="padding:5px 12px; border-bottom:1px solid #eeeeee; font-family:Arial,Helvetica,sans-serif; font-size:13px; color:#888888;">Customer</td>
                                <td style="padding:5px 12px; border-bottom:1px solid #eeeeee; font-family:Arial,Helvetica,sans-serif; font-size:13px; color:#333333; font-weight:bold;">{{ $gatePass->customer_name ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td width="38%" style="padding:5px 12px; border-bottom:1px solid #eeeeee; font-family:Arial,Helvetica,sans-serif; font-size:13px; color:#888888;">Site</td>
                                <td style="padding:5px 12px; border-bottom:1px solid #eeeeee; font-family:Arial,Helvetica,sans-serif; font-size:13px; color:#333333; font-weight:bold;">{{ $gatePass->site ?? '-' }}</td>
                            </tr>
                            @if(!empty($gatePass->department_name))
                            <tr>
                                <td width="38%" style="padding:5px 12px; border-bottom:1px solid #eeeeee; font-family:Arial,Helvetica,sans-serif; font-size:13px; color:#888888;">Department</td>
                                <td style="padding:5px 12px; border-bottom:1px solid #eeeeee; font-family:Arial,Helvetica,sans-serif; font-size:13px; color:#333333; font-weight:bold;">{{ $gatePass->department_name }}</td>
                            </tr>
                            @endif
                            @if(!empty($gatePass->vehicle_registration_number))
                            <tr>
                                <td width="38%" style="padding:5px 12px; border-bottom:1px solid #eeeeee; font-family:Arial,Helvetica,sans-serif; font-size:13px; color:#888888;">Vehicle Reg. No.</td>
                                <td style="padding:5px 12px; border-bottom:1px solid #eeeeee; font-family:Arial,Helvetica,sans-serif; font-size:13px; color:#333333; font-weight:bold;">{{ $gatePass->vehicle_registration_number }}</td>
                            </tr>
                            @endif
                            <tr>
                                <td width="38%" style="padding:5px 12px; border-bottom:1px solid #eeeeee; font-family:Arial,Helvetica,sans-serif; font-size:13px; color:#888888;">Technician</td>
                                <td style="padding:5px 12px; border-bottom:1px solid #eeeeee; font-family:Arial,Helvetica,sans-serif; font-size:13px; color:#333333; font-weight:bold;">
                                    {{ $gatePass->technician_name ?? '-' }}@if(!empty($gatePass->technician_email)) &lt;{{ $gatePass->technician_email }}&gt;@endif
                                </td>
                            </tr>
                            @if(!empty($gatePass->driver_name))
                            <tr>
                                <td width="38%" style="padding:5px 12px; border-bottom:1px solid #eeeeee; font-family:Arial,Helvetica,sans-serif; font-size:13px; color:#888888;">Driver Name</td>
                                <td style="padding:5px 12px; border-bottom:1px solid #eeeeee; font-family:Arial,Helvetica,sans-serif; font-size:13px; color:#333333; font-weight:bold;">{{ $gatePass->driver_name }}</td>
                            </tr>
                            @endif
                            @if(!empty($gatePass->driver_mobile_no))
                            <tr>
                                <td width="38%" style="padding:5px 12px; border-bottom:1px solid #eeeeee; font-family:Arial,Helvetica,sans-serif; font-size:13px; color:#888888;">Driver Mobile No.</td>
                                <td style="padding:5px 12px; border-bottom:1px solid #eeeeee; font-family:Arial,Helvetica,sans-serif; font-size:13px; color:#333333; font-weight:bold;">{{ $gatePass->driver_mobile_no }}</td>
                            </tr>
                            @endif
                            @if(!empty($gatePass->remarks))
                            <tr>
                                <td width="38%" style="padding:5px 12px; border-bottom:1px solid #eeeeee; font-family:Arial,Helvetica,sans-serif; font-size:13px; color:#888888;">Remarks</td>
                                <td style="padding:5px 12px; border-bottom:1px solid #eeeeee; font-family:Arial,Helvetica,sans-serif; font-size:13px; color:#333333; font-weight:bold;">{{ $gatePass->remarks }}</td>
                            </tr>
                            @endif
                            <tr>
                                <td width="38%" style="padding:5px 12px; font-family:Arial,Helvetica,sans-serif; font-size:13px; color:#888888;">Closed At</td>
                                <td style="padding:5px 12px; font-family:Arial,Helvetica,sans-serif; font-size:13px; color:#00695c; font-weight:bold;">{{ now()->format('d M Y, H:i') }}</td>
                            </tr>
                        </table>

                        @if(count($items) > 0)
                        <!-- Items heading -->
                        <p style="margin:0 0 6px 0; font-family:Arial,Helvetica,sans-serif; font-size:14px; font-weight:bold; color:#00695c;">Returned Items</p>

                        <!-- Items table -->
                        <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="margin-bottom:12px;">
                            <!-- Header row -->
                            <tr>
                                <td bgcolor="#00695c" style="background-color:#00695c; padding:6px 10px; font-family:Arial,Helvetica,sans-serif; font-size:13px; font-weight:bold; color:#ffffff;" width="5%">#</td>
                                <td bgcolor="#00695c" style="background-color:#00695c; padding:6px 10px; font-family:Arial,Helvetica,sans-serif; font-size:13px; font-weight:bold; color:#ffffff;" width="25%">Item Code</td>
                                <td bgcolor="#00695c" style="background-color:#00695c; padding:6px 10px; font-family:Arial,Helvetica,sans-serif; font-size:13px; font-weight:bold; color:#ffffff;" width="40%">Item Name</td>
                                <td bgcolor="#00695c" style="background-color:#00695c; padding:6px 10px; font-family:Arial,Helvetica,sans-serif; font-size:13px; font-weight:bold; color:#ffffff;" width="15%">UOM</td>
                                <td bgcolor="#00695c" style="background-color:#00695c; padding:6px 10px; font-family:Arial,Helvetica,sans-serif; font-size:13px; font-weight:bold; color:#ffffff;" width="15%">Qty</td>
                            </tr>
                            @foreach($items as $index => $item)
                            <tr>
                                <td style="padding:5px 10px; border-bottom:1px solid #eeeeee; font-family:Arial,Helvetica,sans-serif; font-size:13px; color:#333333;">{{ $index + 1 }}</td>
                                <td style="padding:5px 10px; border-bottom:1px solid #eeeeee; font-family:Arial,Helvetica,sans-serif; font-size:13px; color:#333333;">{{ $item->item_code ?? '-' }}</td>
                                <td style="padding:5px 10px; border-bottom:1px solid #eeeeee; font-family:Arial,Helvetica,sans-serif; font-size:13px; color:#333333;">{{ $item->item_name ?? '-' }}</td>
                                <td style="padding:5px 10px; border-bottom:1px solid #eeeeee; font-family:Arial,Helvetica,sans-serif; font-size:13px; color:#333333;">{{ $item->uom ?? '-' }}</td>
                                <td style="padding:5px 10px; border-bottom:1px solid #eeeeee; font-family:Arial,Helvetica,sans-serif; font-size:13px; color:#333333; font-weight:bold;">{{ $item->issued_qty ?? '0' }}</td>
                            </tr>
                            @endforeach
                        </table>
                        @endif

                    </td>
                </tr>

                <!-- Footer -->
                <tr>
                    <td style="padding:10px 20px; border-top:1px solid #eeeeee;">
                        <p style="margin:0 0 6px 0; font-family:Arial,Helvetica,sans-serif; font-size:13px; color:#555555;">
                            Thanks,<br>Depot Repair
                        </p>
                        <p style="margin:0; font-family:Arial,Helvetica,sans-serif; font-size:12px; color:#999999; font-style:italic;">
                            This is an automated notification. Please do not reply to this email.
                        </p>
                    </td>
                </tr>

            </table>
            <!-- /Main container -->

        </td>
    </tr>
</table>
<!-- /Outer wrapper -->

</body>
</html>
