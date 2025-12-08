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

        /* Header Table - Fixed Layout */
        .pdf-header {
            width: 100%;
            border: 2px solid #000;
            border-radius: 6px;
            padding: 0;
            border-collapse: collapse;
            margin: 0 0 15px 0;
            table-layout: fixed;
        }

        .pdf-header td {
            vertical-align: middle;
            padding: 0;
        }

        .logo-block {
            width: 30%;
            text-align: center;
            padding: 8px;
            border-right: 2px solid #000;
        }

        .company-logo {
            width: 90%;
            max-width: 240px;
            display: block;
            margin: 0 auto;
        }

        .company-info {
            width: 70%;
            padding: 8px 15px;
            font-size: 13px;
            line-height: 18px;
        }

        .company-title {
            font-size: 18px;
            font-weight: bold;
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

        /* Content Table - Fixed Column Widths */
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

        /* Image Gallery - Fixed Grid - 2 images per row */
        .image-gallery {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            width: 100%;
            margin-bottom: 20px;
        }

        .image-card {
            width: 100%;
            break-inside: avoid;
            display: flex;
            flex-direction: column;
        }

        .image-card img {
            width: 100%;
            height: 200px;
            object-fit: cover;
            display: block;
            border: 1px solid #ccc;
        }

        .image-card p {
            margin: 5px 0 0 0;
            font-size: 11px;
            text-align: center;
            padding: 5px;
        }

        /* Signature Section */
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
            border: 1px solid #ccc;
        }

        .signature-table img {
            max-width: 100%;
            height: auto;
            max-height: 150px;
        }

        /* Print Settings */
        @page {
            size: A4 portrait;
            margin-top: 120px;
            margin-bottom: 15mm;
            margin-left: 15mm;
            margin-right: 15mm;
        }

        /* Header on every page */
        thead {
            display: table-header-group;
        }

        .page-header {
            position: running(header);
        }

        @page {
            @top-center {
                content: element(header);
            }
        }

        @media print {
            body {
                margin: 0;
                padding: 0;
            }

            .company-title {
                font-size: 16px;
            }

            .content-table th,
            .content-table td {
                font-size: 10px;
                padding: 6px 4px;
            }

            /* Fixed header on all pages */
            .pdf-header {
                position: fixed;
                top: 10;
                left: 0;
                right: 0;
                z-index: 1000;
                background: white;
                margin: 0;
            }

            .page-content {
                margin-top: 140px;
            }

            /* Prevent breaks inside specific elements */
            .content-table tr {
                page-break-inside: avoid;
            }

            .image-card {
                page-break-inside: avoid;
                margin-top: 140px;
            }

            .image-gallery {
                display: grid;
                grid-template-columns: repeat(2, 1fr);
                gap: 15px;
            }
        }


       .signature-top {
    margin-top: 200px;
}

.no-break {
    page-break-inside: avoid;
    break-inside: avoid;
}

        .signature-block {
            margin-top: 15px;
        }

        .signature-table img {
            max-width: 200px;
            
        }
    </style>
</head>

<body>

    <!-- Header Section - Repeats on all pages -->
    <table class="pdf-header">
        <thead>
            <tr>
                <td class="logo-block">
                     
                      <img src="{{ public_path('logo-cryotech-middle-east.png') }}" class="company-logo" alt="Cryotech Logo">
                </td>
                <td class="company-info">
                    <div class="company-title">CRYOTECH MIDDLE EAST L.L.C.</div>
                    <div>Dubai Investment Park 2, P.O.Box 7427, Dubai, UAE</div>
                    <div>Tel : +971-4-8855169</div>
                    <div>Website : <a href="https://www.cryotechme.com">www.cryotechme.com</a></div>
                    <div>Email: <a href="mailto:service@cryotechme.com">service@cryotechme.com</a></div>
                </td>
            </tr>
        </thead>
    </table>

    <div class="page-content">
        <!-- Details Table -->
        <table class="content-table">
            <tr>
                <th style="width: 25%;">Tank Type:</th>
                <th style="width: 25%;">Unit Number:</th>
                <th style="width: 25%;">Customer:</th>
                <th style="width: 25%;">Survey Type:</th>
            </tr>
            <tr>
                <td>{{ $data['Tank_Type'] ?? 'N/A' }}</td>
                <td>{{ $data['Unit_Number'] ?? 'N/A' }}</td>
                <td>{{ $data['Customer_Name'] ?? 'N/A' }}</td>
                <td>{{ $data['Survey_Type'] ?? 'N/A' }}</td>
            </tr>

            <tr>
                <th>Manufacturer</th>
                <th>Un Portable Tank Type</th>
                <th>Survey Date (MM/DD/YYYY)</th>
                <th>Location of Inspection</th>
            </tr>
            <tr>
                <td>{{ $data['Manufacturer'] ?? 'N/A' }}</td>
                <td>{{ $data['Un_Portable_Tank_Type'] ?? 'N/A' }}</td>
                <td>{{ $data['Survey_Date'] ?? 'N/A' }}</td>
                <td>{{ $data['Location_of_Inspection'] ?? 'N/A' }}</td>
            </tr>

            <tr>
                <th>Manufacturer Serial No</th>
                <th>MAWP</th>
                <th>Inner tank Material</th>
                <th>Outer tank material</th>
            </tr>
            <tr>
                <td>{{ $data['manufacturerSerialNo'] ?? 'N/A' }}</td>
                <td>{{ $data['mawp'] ?? 'N/A' }}</td>
                <td>{{ $data['Inner_Tank_Material'] ?? 'N/A' }}</td>
                <td>{{ $data['Outer_Tank_Material'] ?? 'N/A' }}</td>
            </tr>

            <tr>
                <th>Gross Weight(kg)</th>
                <th>Tare Weight(kg)</th>
                <th>Capacity(L)</th>
                <th>Next CSC Due</th>
            </tr>
            <tr>
                <td>{{ $data['Max_Gross_Weight_kg'] ?? 'N/A' }}</td>
                <td>{{ $data['Tare_Weight_kg'] ?? 'N/A' }}</td>
                <td>{{ $data['Capacity_L'] ?? 'N/A' }}</td>
                <td>{{ $data['Next_CSC_Due'] ?? 'N/A' }}</td>
            </tr>

            <tr>
                <th>Initial test (MM-YY)</th>
                <th>Last 2.5yr Test (MM-YY)</th>
                <th>Last 5 yr. test (MM-YY)</th>
                <th>Next test Due (MM-YY)</th>
            </tr>
            <tr>
                <td>{{ $data['Initial_Test_MMM_YY'] ?? 'N/A' }}</td>
                <td>{{ $data['Last_2_5yr_Test_MMM_YY'] ?? 'N/A' }}</td>
                <td>{{ $data['Last_5yr_Test_MMM_YY'] ?? 'N/A' }}</td>
                <td>{{ $data['Next_Test_Due_MMM_YY'] ?? 'N/A' }}</td>
            </tr>

            <tr>
                <th>Last Cargo</th>
                <th>Vacuum reading</th>
                <th>Results : Accepted</th>
                <th>Surveyor</th>
            </tr>
            <tr>
                <td>{{ $data['Last_Cargo'] ?? 'N/A' }}</td>
                <td>{{ $data['Vacuum_Reading'] ?? 'N/A' }}</td>
                <td>{{ $data['Results'] ?? 'N/A' }}</td>
                <td>{{ $data['Surveyor'] ?? 'N/A' }}</td>
            </tr>
        </table>

        <!-- Comments Section -->
        <div style="page-break-inside: avoid;">
            <div class="comments-heading">Comments</div>
            <table class="comments-table">
                <tbody>
                    <tr>
                        <td>{{ $data['Comments'] ?? 'N/A' }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Images -->
        <div class="image-gallery">
            @foreach($data['images'] as $img)
            <div class="image-card">
                <img src="{{ $img['image_data'] }}" alt="Inspection Image">
                <p>{{ $img['description'] ?? 'No description' }}</p>
            </div>
            @endforeach
        </div>

        <!-- Customer Signature -->
        @if(!empty($data['signature']))
        <div class="signature-block no-break signature-top" >
            <div class="signature-heading">Customer Signature</div>

            <table class="signature-table">
                <tbody>
                    <tr>
                        <td><strong>Customer Name:</strong> {{ $data['signature']['custSignatureName'] ?? 'N/A' }}</td>
                    </tr>

                    <tr>
                        <td>
                            @if(!empty($data['signature']['signature_data']))
                            <img src="data:image/png;base64,{{ $data['signature']['signature_data'] }}" alt="Signature">
                            @else
                            No Signature Available
                            @endif
                        </td>
                    </tr>

                    <tr>
                        <td><strong>Date:</strong> {{ $data['signature']['date'] ?? 'N/A' }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
        @endif


    </div><!-- End page-content -->

</body>

</html>