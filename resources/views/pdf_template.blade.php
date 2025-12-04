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

        /* Header using table for better PDF compatibility */
        .header-box {
            border: 2px solid #000;
            padding: 0;
            width: 100%;
            margin-bottom: 20px;
        }

        .header-table {
            width: 100%;
            border-collapse: collapse;
        }

        .header-table td {
            padding: 12px;
            vertical-align: middle;
            border: none;
        }

        .logo-cell {
            width: 100px;
            text-align: center;
        }

        .logo-circle {
            width: 70px;
            height: 70px;
            background: #5ba3e0;
            border-radius: 50%;
            color: #fff;
            font-size: 42px;
            font-weight: bold;
            text-align: center;
            line-height: 70px;
            margin: 0 auto;
        }

        .title-cell {
            width: 40%;
        }

        .company-title {
            font-size: 22px;
            font-weight: bold;
            margin: 0 0 5px 0;
            letter-spacing: 0.5px;
        }

        .sub-title {
            font-size: 11px;
            color: #666;
        }

        .info-cell {
            width: 40%;
            text-align: right;
            font-size: 10px;
            line-height: 16px;
        }

        .info-cell strong {
            font-size: 11px;
            display: block;
            margin-bottom: 3px;
        }

        /* Content Table */
        .content-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 0;
        }

        .content-table th {
            background: #4a98d8;
            color: #fff;
            font-weight: bold;
            padding: 8px;
            border: 1px solid #1f6fb2;
            font-size: 12px;
        }

        .content-table td {
            padding: 8px;
            border: 1px solid #ccc;
            font-size: 11px;
        }

        /* Responsive adjustments for smaller pages */
        @media print {
            body {
                margin: 0;
                padding: 10px;
            }
            
            .company-title {
                font-size: 18px;
            }
            
            .logo-circle {
                width: 60px;
                height: 60px;
                line-height: 60px;
                font-size: 36px;
            }
        }

        /* For A4 Portrait */
        @page {
            size: A4 portrait;
            margin: 15mm;
        }

  .image-gallery {
    /* 1. Use the Grid display model */
    display: grid; 
    
    /* 2. Define two columns (1fr 1fr means two equal fractions of space) */
    grid-template-columns: 1fr 1fr;
    
    /* 3. Add space between the images */
    gap: 20px; /* Adjust this value as needed for spacing */
}

/* Ensure the image takes up the full width of its card */
.image-card img {
    width: 100%;
    max-height: 200px;
    object-fit: cover;
}
    </style>
</head>

<body>

    <!-- Header Section using Table -->
    <div class="header-box">
        <table class="header-table">
            <tr>
                <td class="logo-cell">
                    <div class="logo-circle">O</div>
                </td>
                <td class="title-cell">
                    <div class="company-title">CRYOTECH MIDDLE EAST</div>
                    <div class="sub-title">cryo-gas tank depot and services</div>
                </td>
                <td class="info-cell">
                    <strong>CRYOTECH MIDDLE EAST L.L.C.</strong>
                    Dubai Investment Park 2, P.O.Box 7427, Dubai, UAE<br>
                    Tel : +971-4-8855169<br>
                    Website : www.cryotechme.com<br>
                    Email: service@cryotechme.com
                </td>
            </tr>
        </table>
    </div>

    <!-- Details Table -->
    <table class="content-table">
        <tr>
            <th>Tank Type:</th>
            <th>Unit Number:</th>
            <th>Customer:</th>
            <th>Survey Type:</th>
        </tr>
        <tr>
            <td>{{ $data['Tank_Type'] ?? 'N/A' }}</td>
            <td>{{ $data['Unit_Number'] ?? 'N/A' }}</td>
            <td>{{ $data['Customer_Name'] ?? 'N/A' }}</td>
            <td>{{ $data['Survey_Type'] ?? 'N/A' }}</td>
        </tr>

     <!-- row 2 --> 
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

         <!-- row 3 --> 
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


        <!-- row 4 --> 
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


        <!-- row 4 --> 
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


         <!-- row 5 --> 
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
<!-- Comments Heading -->
<div style="font-weight: bold; margin-top: 20px; margin-bottom: 5px; font-size: 14px;">
    Comments
</div>

<!-- Comments Table -->
<table style="width:100%; border-collapse:collapse; margin-bottom:20px;">
    <tbody>
        <tr>
            <td style="padding:12px; border:1px solid #000; min-height:40px; white-space: pre-wrap; text-align: left;">
                {{ $data['Comments'] ?? 'N/A' }}
            </td>
        </tr>
    </tbody>
</table> 
 
   <div class="image-gallery">
    @foreach($data['images'] as $img)
        <div class="image-card"> <img src="{{ $img['image_data'] }}" 
                 alt="Inspection Image"
                 style="width: 45%; max-height: 200px; object-fit: cover;">
            <p>{{ $img['description'] ?? 'No description' }}</p>
        </div>
    @endforeach
</div>
 

<div style="font-weight: bold; margin-top: 20px; margin-bottom: 5px; font-size: 14px;">
    Customer Signature   
</div>
 

<table style="width:100%;  margin-bottom:20px;">
    <tbody>
        <tr><td style="padding:12px;  min-height:40px; text-align: left;">Customer Name: {{ $data['signature']['custSignatureName'] }}</td></tr>
        <tr>
            <td style="padding:12px;  min-height:40px; text-align: left;">
             <img src="data:image/png;base64,{!! $data['signature']['signature_data'] !!}" 
             alt="Signature"
             style="max-width:100%; height:auto;">

            </td>
        </tr>

         <tr><td style="padding:12px;   min-height:40px; text-align: left;">Date: {{ $data['signature']['date'] }}</td></tr>

    </tbody>
</table>



</body>
</html>