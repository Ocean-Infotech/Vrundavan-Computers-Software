<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Student-Wise Test Report</title>
    <style>
        body {
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            margin: 20px;
            color: #333;
            font-size: 13px;
            line-height: 1.4;
        }

        .print-header {
            border-bottom: 3px double #333;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }

        .print-header table {
            width: 100%;
            border-collapse: collapse;
        }

        .print-header td {
            border: none;
            padding: 0;
        }

        .college-name {
            font-size: 20px;
            font-weight: bold;
            color: #111;
            text-transform: uppercase;
        }

        .trust-name {
            font-size: 14px;
            color: #555;
        }

        .college-info {
            font-size: 13px;
            color: #666;
        }

        .report-title {
            text-align: center;
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 20px;
            text-transform: uppercase;
            letter-spacing: 1px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 5px;
        }

        /* Details Section */
        .details-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .details-table td {
            border: none;
            padding: 6px 10px;
            vertical-align: top;
        }

        .details-table td.label {
            font-weight: bold;
            width: 18%;
            color: #555;
        }

        .details-table td.value {
            width: 32%;
            color: #111;
        }

        /* Content Tables */
        .report-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            font-size: 13px;
        }

        .report-table th, .report-table td {
            border: 1px solid #ddd;
            padding: 8px 10px;
            text-align: left;
        }

        .report-table th {
            background-color: #f5f5f5;
            font-weight: bold;
            color: #333;
        }

        .report-table tr:nth-child(even) td {
            background-color: #fafafa;
        }

        .no-print {
            text-align: right;
            margin-bottom: 15px;
        }

        .btn {
            padding: 6px 12px;
            font-size: 13px;
            font-weight: 600;
            border-radius: 4px;
            cursor: pointer;
            border: 1px solid #ccc;
            background: #fff;
            margin-left: 5px;
        }

        .btn-primary {
            background: #333;
            color: #fff;
            border-color: #333;
        }

        @media print {
            .no-print {
                display: none;
            }
            body {
                margin: 10px;
            }
            .report-table th {
                background-color: #f1f1f1 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
    </style>
</head>

<body>
    <div class="no-print">
        <button class="btn btn-primary" onclick="window.print()">Print</button>
        <button class="btn" onclick="window.close()">Close</button>
    </div>

    <!-- College Header -->
    <div class="print-header">
        <table>
            <tr>
                <td style="width: 90px; text-align: left; vertical-align: middle;">
                    <img src="{{ asset('uploads/logo/logo.png') }}" alt="College Logo" style="width: 80px; height: auto;">
                </td>
                <td style="text-align: center; vertical-align: middle; line-height: 1.5;">
                    <span class="trust-name">Shree Patel Vidhyarthi Ashram Sanchalit</span><br>
                    <strong class="college-name">Shree Patel Vidhya Mandir Science College</strong><br>
                    <span class="college-info">Veraval Road, Keshod - 362 220. Mo. 96874 51774</span>
                </td>
                <td style="width: 90px;"></td> <!-- balancer -->
            </tr>
        </table>
    </div>

    <div class="report-title">
        Student Test Report
    </div>

    <!-- Student Details -->
    @if (!empty($studentDetails))
        <table class="details-table">
            <tr>
                <td class="label">Student Name:</td>
                <td class="value"><strong>{{ $studentDetails->student_name ?? '-' }}</strong></td>
                <td class="label">Course Name:</td>
                <td class="value">{{ $studentDetails->course_name ?? '-' }}</td>
            </tr>
            <tr>
                <td class="label">Student ID:</td>
                <td class="value">{{ $studentDetails->student_id ?? '-' }}</td>
                <td class="label">Current Semester:</td>
                <td class="value"><strong>Semester {{ $studentDetails->current_semester ?? '-' }}</strong></td>
            </tr>
            <tr>
                <td class="label">Generated On:</td>
                <td class="value" colspan="3">{{ optional($generatedAt)->format('d-m-Y h:i A') }}</td>
            </tr>
        </table>
    @else
        <table class="details-table">
            <tr>
                <td class="label">Report Type:</td>
                <td class="value">General Student-Wise Test Report</td>
                <td class="label">Generated On:</td>
                <td class="value">{{ optional($generatedAt)->format('d-m-Y h:i A') }}</td>
            </tr>
        </table>
    @endif

    <table class="report-table">
        <thead>
            <tr>
                @foreach ($columnLabels as $label)
                    <th>{{ $label }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @forelse ($tests as $test)
                <tr>
                    @foreach ($columnKeys as $columnKey)
                        <td>
                            @switch($columnKey)
                                @case('subject_name')
                                    {{ $test->subject_name ?? '-' }}
                                @break

                                @case('unit_name')
                                    {{ $test->unit_name ?? '-' }}
                                @break

                                @case('cr_date')
                                    {{ $test->cr_date ? \Carbon\Carbon::parse($test->cr_date)->format('d-m-Y') : '-' }}
                                @break

                                @case('mark')
                                    {{ $test->mark ?? '-' }}
                                @break

                                @case('student_mark')
                                    {{ $test->student_mark ?? 0 }}
                                @break

                                @default
                                    {{ data_get($test, $columnKey, '-') }}
                            @endswitch
                        </td>
                    @endforeach
                </tr>
            @empty
                <tr>
                    <td colspan="{{ max(1, count($columnLabels ?? [])) }}" style="text-align: center;">
                        No data available for the selected criteria.
                    </td>
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
