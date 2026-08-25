<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Test Marks Report - {{ $test->subject_name ?? '' }}</title>
    <style>
        body {
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            margin: 20px;
            color: #111;
        }
        h1, h2 {
            text-align: center;
            margin-bottom: 5px;
            margin-top: 5px;
        }
        .meta-container {
            border: 1px solid #ccc;
            padding: 15px;
            margin-bottom: 20px;
            background-color: #fcfcfc;
            border-radius: 4px;
        }
        .meta-row {
            display: flex;
            flex-wrap: wrap;
            margin-bottom: 5px;
        }
        .meta-col {
            flex: 1;
            min-width: 200px;
            margin-bottom: 5px;
            font-size: 14px;
        }
        .no-print {
            text-align: right;
            margin-bottom: 10px;
        }
        .no-print button {
            padding: 6px 12px;
            cursor: pointer;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            font-size: 13px;
        }
        th, td {
            border: 1px solid #555;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f4f7;
        }
        .text-center {
            text-align: center;
        }
        
        /* College Header Styles */
        .report-header {
            margin-bottom: 8px;
        }
        .report-header-top {
            display: flex;
            align-items: center;
            gap: 16px;
        }
        .report-logo {
            flex: 0 0 95px;
        }
        .report-logo img {
            width: 95px;
            height: 95px;
            object-fit: contain;
            display: block;
        }
        .report-header-text {
            flex: 1;
            text-align: center;
            font-family: Arial, Helvetica, sans-serif;
            line-height: 1.35;
        }
        .report-header-text .affiliation-line {
            font-size: 12px;
            font-weight: 700;
        }
        .report-header-text .college-name {
            font-size: 24px;
            font-weight: 800;
            margin: 4px 0;
        }
        .report-header-text .managed-line,
        .report-header-text .address-line {
            font-size: 13px;
            font-weight: 700;
        }
        .report-contact-row {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 12px;
            margin-top: 8px;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 13px;
            font-weight: 700;
        }
        .report-contact-row a {
            color: #1155cc;
            text-decoration: underline;
        }
        .contact-separator {
            color: #000;
            font-weight: 400;
        }
        .header-divider {
            border: none;
            border-top: 3px solid #000;
            margin: 8px 0 0;
        }

        @media print {
            .no-print {
                display: none;
            }
            body {
                margin: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="no-print">
        <button onclick="window.print()">Print</button>
        <button onclick="window.close()">Close</button>
    </div>

    @include('software.module.admission.partials.certificate-report-header')
    <br>
    <h1>Test Marks Report</h1>
    
    <div class="meta-container">
        <div class="meta-row">
            <div class="meta-col"><strong>Batch:</strong> {{ $test->batch->batch_name ?? 'N/A' }}</div>
            <div class="meta-col"><strong>Subject:</strong> {{ $test->subject_name ?? 'N/A' }}</div>
            <div class="meta-col"><strong>Unit:</strong> {{ $test->unit_name ?? 'N/A' }}</div>
        </div>
        <div class="meta-row">
            <div class="meta-col"><strong>Faculty:</strong> {{ $test->createdByUser?->name ?? 'N/A' }}</div>
            <div class="meta-col"><strong>Date & Time:</strong> {{ $test->created_at ? $test->created_at->format('d-m-Y h:i A') : 'N/A' }}</div>
            <div class="meta-col"><strong>Max Marks:</strong> {{ $test->mark ?? 'N/A' }}</div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th width="15%">Student ID</th>
                <th>Student Name</th>
                <th width="20%" class="text-center">Obtained Marks</th>
                <th width="20%">Updated By</th>
                <th width="20%">Updated At</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($students as $student)
                @php
                    $markInfo = $student->admission->testMarks->first();
                @endphp
                <tr>
                    <td>{{ $student->id }}</td>
                    <td>{{ $student->admission->first_name }} {{ $student->admission->last_name }}</td>
                    <td class="text-center">
                        <strong>
                            {{ $markInfo ? number_format($markInfo->marks, 2) : '0.00' }}
                        </strong>
                    </td>
                    <td>{{ $markInfo ? ($markInfo->updatedByUser?->name ?? $markInfo->createdByUser?->name ?? 'N/A') : '-' }}</td>
                    <td>{{ $markInfo ? ($markInfo->updated_at ? $markInfo->updated_at->format('d-m-Y h:i A') : '-') : '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center">No students registered for this test.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <script>
        window.onload = function() {
            window.print();
        };
        window.onafterprint = function() {
            window.close();
        };
    </script>
</body>
</html>
