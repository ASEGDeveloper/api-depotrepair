<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <style>
 
        body {
            font-family: DejaVu Sans, sans-serif;
            margin: 0;
            padding: 15px;
            font-size: 12px;
        }
 
        /* --- Header Styles --- */

        /* Header Table - Standard Layout */
        .pdf-header {
            width: 100%;
            border: 2px solid #ccc;
            border-radius: 6px;
            /* padding: 12px; */
            border-collapse: collapse;
            /* Added margin back for non-fixed header separation */
            margin: 0 0 15px 0;
            table-layout: fixed;
        }

        .pdf-header td {
            vertical-align: middle;
            padding: 8px;
        }

        .logo-company {
            display: flex;
            align-items: center;
            justify-content: center;

            width: 50px;
            border-radius: 100%;
            height: 50px;
            background: #4a98d8;
            color: white;
        }

        .logo-company span {
            width: 100%;
            font-size: 22px;
            padding: 4px;
        }

        .logo-block2 {
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .content {
            display: flex;
            flex-direction: column;
            align-items: baseline;
            justify-content: baseline;
            margin-left: 15px;
            padding: 5px;
        }

        .logo-block2 .content h2 {
            margin-left: 8px;
            font-weight: 400;
            font-size: 10px;
        }

        .logo-block2 .content p {
            margin-left: 8px;
            font-size: 8px;
        }

        .logo-block {
            width: 100%;
            text-align: center;
            padding: 8px;
            /* border-right: 2px solid #000; */
        }

        .company-logo {

            width: 90%;
            max-width: 240px;
            display: block;
            margin: 0 auto;
        }

        .company-info {
            /* padding: 12px; */
            width: 50%;
            padding: 8px 15px;
            font-size: 8px;
            line-height: 18px;
            text-align: right;
        }

        .company-title {
            font-size: 10px;
            font-weight: 600;
            margin: 0 0 4px 0;
        }

        .company-info a {
            color: #003399;
            text-decoration: none;
        }

        .company-info div {
            margin: 0;
            padding: 0;
        }

        /* --- Content Tables --- */

        .content-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            table-layout: fixed;
        }

        .content-table th {
            background: #4a98d8;
            color: #fff;
            padding: 8px 6px;
            border: 1px solid #ccc;
            font-size: 11px;
            font-weight: bold;
            text-align: left;
            word-wrap: break-word;
        }

        .content-table td {
            padding: 8px;
            border: 1px solid #ccc;
            font-size: 11px;
            word-wrap: break-word;
            vertical-align: top;
        }

        /* Comments Section */
        .comments-heading {
            font-weight: bold;
            margin-top: 20px;
            margin-bottom: 5px;
            font-size: 14px;
        }

        .comments-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .comments-table td {
            padding: 12px;
            border: 1px solid #000;
            white-space: pre-wrap;
        }

        /* --- Image Gallery (Fixed 2-column Grid) --- */

        .image-gallery {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            /* Two equal columns */
            gap: 15px;
            margin-bottom: 20px;
        }

        .image-card {
            width: 100%;
            margin-bottom: 0;
            page-break-inside: avoid;
            break-inside: avoid;
        }

        .image-card img {
            width: 100%;
            height: 180px;
            object-fit: cover;
            border: 1px solid #ccc;
        }

        .image-card p {
            margin-top: 5px;
            font-size: 11px;
        }

        /* --- Signature Section --- */

        .signature-heading {
            font-weight: bold;
            margin-top: 20px;
            margin-bottom: 5px;
            font-size: 14px;
        }

        .signature-table {
            width: 100%;
            margin-bottom: 20px;
            border-collapse: collapse;
        }

        .signature-table td {
            padding: 12px;

        }

        .signature-table img {
            max-width: 200px;
            height: auto;
            max-height: 150px;
        }

        .signature-block {
            margin-top: 15px;
            page-break-inside: avoid;
            break-inside: avoid;
        }

        .no-break {
            page-break-inside: avoid;
            break-inside: avoid;
        }

        .no-break-content {
            page-break-inside: avoid;
        }

        .signature-container {
            page-break-inside: avoid;
            margin-top: 40px;
        }

        .surveyor-align-right {
            text-align: right;
        }


        /* --- Print & PDF Settings --- */

        @page {
            size: A4 portrait;
            /* Reset to standard margins, no need for large top margin */
            margin-top: 7mm;
            margin-bottom: 7mm;
            margin-left: 2mm;
            margin-right: 2mm;
        }


        .pdf-header {
            /* ❌ Removed position: fixed, top, left, right, z-index, and background */
        }

        /* ❌ REMOVED: Content start position offset, as header is not fixed */
        .page-content {
            margin-top: 0;
        }

        /* Prevent breaks inside specific elements */
        .content-table tr {
            page-break-inside: avoid;
        }

        .no-break {
            page-break-inside: avoid;
        }

        .image-table img {
            max-height: 200px;
            object-fit: contain;
        }
    </style>
</head>

<body>

    <table class="pdf-header">
        <tr>
            <td style="width: 50%; text-align: left;">
                <img src="{{ @$data['logo']['logo'] }}"
                    alt="Logo"
                    style="max-width: 200px; height: auto;">
            </td>
            <td class="company-info" style="text-align: right;">
                <div class="company-title">CRYOTECH MIDDLE EAST L.L.C.</div>
                <div>Dubai Investment Park 2, P.O.Box 7427, Dubai, UAE</div>
                <div>Tel : +971-4-8855169</div>
                <div>Website : <a href="https://www.cryotechme.com">www.cryotechme.com</a></div>
                <div>Email: <a href="mailto:service@cryotechme.com">service@cryotechme.com</a></div>
            </td>
        </tr>
    </table> 


    <div class="page-content">
        <table class="content-table">
            <tr>
                <th style="width: 25%;">Tank Type:</th>
                <th style="width: 25%;">Unit Number:</th>
                <th style="width: 25%;">Customer:</th>
                <th style="width: 25%;">Survey Type:</th>
            </tr>
            <tr>
                <td>{{ @$data['Tank_Type'] ?? 'N/A' }}</td>
                <td>{{ @$data['Unit_Number'] ?? 'N/A' }}</td>
                <td>{{ @$data['Customer_Name'] ?? 'N/A' }}</td>
                <td>{{ @$data['Survey_Type'] ?? 'N/A' }}</td>
            </tr>

            <tr>
                <th>Manufacturer</th>
                <th>Un Portable Tank Type</th>
                <th>Survey Date (MM/DD/YYYY)</th>
                <th>Location of Inspection</th>
            </tr>
            <tr>
                <td>{{ @$data['Manufacturer'] ?? 'N/A' }}</td>
                <td>{{ @$data['Un_Portable_Tank_Type'] ?? 'N/A' }}</td>
                <td> {{ isset($data['Survey_Date']) ? \Carbon\Carbon::parse($data['Survey_Date'])->format('m/d/Y') : 'N/A' }}</td>
                <td>{{ @$data['Location_of_Inspection'] ?? 'N/A' }}</td>
            </tr>


            <tr>
                <th>Manufacturer Serial No</th>
                <th>MAWP</th>
                <th>Inner tank Material</th>
                <th>Outer tank material</th>
            </tr>
            <tr>
                <td>{{ @$data['manufacturerSerialNo'] ?? 'N/A' }}</td>
                <td>{{ @$data['mawp'] ?? 'N/A' }}</td>
                <td>{{ @$data['Inner_Tank_Material'] ?? 'N/A' }}</td>
                <td>{{ @$data['Outer_Tank_Material'] ?? 'N/A' }}</td>
            </tr>

            <tr>
                <th>Gross Weight(kg)</th>
                <th>Tare Weight(kg)</th>
                <th>Capacity(L)</th>
                <th>Next CSC Due</th>
            </tr>
            <tr>
                <td>{{ @$data['Max_Gross_Weight_kg'] ?? 'N/A' }}</td>
                <td>{{ @$data['Tare_Weight_kg'] ?? 'N/A' }}</td>
                <td>{{ @$data['Capacity_L'] ?? 'N/A' }}</td>
               <td> 
                   {{ isset($data['Next_CSC_Due']) ? strtoupper(\Carbon\Carbon::parse($data['Next_CSC_Due'])->format('M/Y')) : 'N/A' }}
 
                </td>
            </tr>

            <tr>
                <th>Initial test (MM-YY)</th>
                <th>Last 2.5yr Test (MM-YY)</th>
                <th>Last 5 yr. test (MM-YY)</th>
                <th>Next test Due (MM-YY)</th>
            </tr>
            <tr>
                <td>  
                 {{ isset($data['Initial_Test_MMM_YY']) ? strtoupper(\Carbon\Carbon::parse($data['Initial_Test_MMM_YY'])->format('M/Y')) : 'N/A' }}
 
                </td>
                <td>  
                 {{ isset($data['Last_2_5yr_Test_MMM_YY']) ? strtoupper(\Carbon\Carbon::parse($data['Last_2_5yr_Test_MMM_YY'])->format('M/Y')) : 'N/A' }}

                </td>
                <td>
                  {{ isset($data['Last_5yr_Test_MMM_YY']) ? strtoupper(\Carbon\Carbon::parse($data['Last_5yr_Test_MMM_YY'])->format('M/Y')) : 'N/A' }}

               </td>
                <td> 

                  {{ isset($data['Next_Test_Due_MMM_YY']) ? strtoupper(\Carbon\Carbon::parse($data['Next_Test_Due_MMM_YY'])->format('M/Y')) : 'N/A' }} 
                </td>
            </tr>

            <tr>
                <th>Last Cargo</th>
                <th>Vacuum reading</th>
                <th>Results : Accepted</th>
                <th>Surveyor</th>
            </tr>
            <tr>
                <td>{{ @$data['Last_Cargo'] ?? 'N/A' }}</td>
                <td>{{ @$data['Vacuum_Reading'] ?? 'N/A' }}</td>
                <td>{{ @$data['Results'] ?? 'N/A' }}</td>
                <td>{{ @$data['Surveyor'] ?? 'N/A' }}</td>
            </tr>


            


        </table>


        <table class="content-table">

            <tr>
                <td colspan="4" style="padding: 6px; font-weight: bold; background: rgb(241, 241, 241); border: 1px solid rgb(0, 0, 0);">
                    Comments:
                </td>
            </tr>

            <tr>
                <td colspan="4"
                    style="padding: 8px; border: 1px solid rgb(0, 0, 0); white-space: pre-line; min-height: 35px;">
                    {{ ltrim(@$data['Comments'] ?? 'N/A', " \t\n\r\0\x0B") }}
                </td>
            </tr>
        </table>



        <table width="100%" cellspacing="0" cellpadding="0"
       style="border-collapse: collapse; margin: 0;">
    <tbody>
        @foreach(array_chunk($data['images'], 2) as $imageRow)
        <tr class="no-break">
            @foreach($imageRow as $img)
            <td style="width: 50%; padding: 1mm; text-align: center; vertical-align: top;">

                <!-- Fixed image container -->
                <div style="
                    width: 100%;
                    height: 220px;
                    overflow: hidden;
                    border: 1px solid #888;
                ">
                    <img src="{{ $img['image_data'] }}"
                         alt="Inspection Image"
                         style="
                            width: 100%;
                            height: 100%;
                            object-fit: cover;
                            display: block;
                         ">
                </div>

                <div style="font-size: 10.5pt; padding-top: 1mm;">
                    {{ $img['description'] ?? 'No description' }}
                </div>

            </td>
            @endforeach

            @if(count($imageRow) < 2)
                <td style="width: 50%;"></td>
            @endif
        </tr>
        @endforeach
    </tbody>
</table>







        <div class="signature-container">

            <table width="100%" cellspacing="0" cellpadding="6" style="border-collapse: collapse;">
                <tr class="no-break">

                    {{-- LEFT SIDE — CUSTOMER SIGNATURE --}}
                    @if(!empty($data['signature']['custSignatureName']))
                    <td style="width: 50%; vertical-align: top; padding: 8px;">

                        <div style="font-weight: bold; margin-bottom: 6px;">Customer Signature</div>

                        <table width="100%" style="font-size: 12px;">
                            <tr>
                                <td>
                                    <strong>Customer Name:</strong>
                                    {{ $data['signature']['custSignatureName'] ?? 'N/A' }}
                                </td>
                            </tr>

                            <tr>
                                <td>
                                    @if(!empty($data['signature']['signature_data']))
                                    <img src="data:image/png;base64,{{ $data['signature']['signature_data'] }}"
                                        alt="Customer Signature"
                                        style="max-width: 200px; height: auto; margin-top: 5px;">
                                    @endif
                                </td>
                            </tr>

                            <tr>
                                <td><strong>Date:</strong> {{ $data['signature']['date'] ?? 'N/A' }}</td>
                            </tr>
                        </table>

                    </td>
                    @else
                    {{-- If no customer signature, left cell stays empty --}}
                    <td style="width: 50%; padding: 8px;"></td>
                    @endif



                    {{-- RIGHT SIDE — SURVEYOR SIGNATURE (Always stays Right) --}}
                    <td class="surveyor-align-right" style="width: 50%; vertical-align: top; padding: 8px; text-align: right;">

                        @if(!empty($data['surveyor']['Surveyor_Name']))
                        {{-- The div containing the heading will be right-aligned by the parent <td>'s style --}}
                        <div style="font-weight: bold; margin-bottom: 6px;">Surveyor Signature</div>

                        <table width="100%" style="font-size: 12px;">
                            <tr>
                                {{-- MODIFICATION 1: Ensure the Name field is right-aligned --}}
                                <td style="text-align: right;">
                                    <strong>Surveyor Name:</strong>
                                    {{ $data['surveyor']['Surveyor_Name'] ?? 'N/A' }}
                                </td>
                            </tr>

                            <tr>
                                {{-- MODIFICATION 2: Ensure the Signature image block is right-aligned --}}
                                <td style="text-align: right;">
                                    @if(!empty($data['surveyor']['signature_data']))
                                    <img
                                        src="data:image/png;base64,{{ $data['surveyor']['signature_data'] }}"
                                        alt="Surveyor Signature"
                                        style="max-width: 200px; height: auto; margin-top: 5px;">
                                    @endif
                                </td>
                            </tr>

                            <tr>
                                {{-- MODIFICATION 3: Ensure the Date field is right-aligned --}}
                                <td style="text-align: right;"><strong>Date:</strong> {{ $data['surveyor']['date'] ?? 'N/A' }}</td>
                            </tr>
                        </table>
                        @endif

                    </td>

                </tr>
            </table>

        </div>

    </div>
</body>

</html>