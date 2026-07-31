<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance Report</title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            margin: 0;
            padding: 20px;
            color: #000;
        }

        .report-sheet {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .report-sheet th,
        .report-sheet td {
            border: 1px solid #000;
            padding: 8px;
            font-size: 14px;
        }

        .report-sheet th {
            font-weight: 700;
            background-color: #f2f2f2;
        }

        .report-logo-cell {
            width: 105px;
            text-align: center;
            padding: 8px 6px;
            background: #fff;
            vertical-align: middle;
        }

        .report-logo-cell img {
            width: 88px;
            height: 88px;
            object-fit: contain;
            display: block;
            margin: 0 auto;
        }

        .college-name-cell {
            text-align: center;
            font-size: 22px;
            font-weight: 700;
            line-height: 1.35;
            padding: 12px 10px;
            background: #fff;
        }

        .report-title-cell {
            text-align: center;
            font-size: 17px;
            font-weight: 700;
            line-height: 1.35;
            padding: 10px;
            background-color: #ffffff;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        @media print {
            body {
                padding: 0;
            }

            .report-logo-cell,
            .college-name-cell,
            .report-title-cell {
                background-color: #ffffff !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
    </style>
</head>
<body>
    @php
        $logoPath = file_exists(public_path('uploads/logo/report_logo.png'))
            ? asset('uploads/logo/report_logo.png')
            : asset('admin/assets/images/report_logo.png');
    @endphp

    <table class="report-sheet">
        <tr>
            <td class="report-logo-cell" rowspan="2">
                <img src="{{ $logoPath }}" alt="College Logo">
            </td>
            <td class="college-name-cell" colspan="7">
                Shree Patel Vidhya Mandir Science College
            </td>
        </tr>
        <tr>
            <td class="report-title-cell" colspan="7">
                Attendance Report
            </td>
        </tr>
        <tr>
            <th width="5%" class="text-center">No</th>
            <th width="15%">Biometric ID</th>
            <th width="30%">Student Name</th>
            <th width="25%">Course - Batch - Class</th>
            <th width="10%" class="text-center">Present</th>
            <th width="10%" class="text-center">Absent</th>
            <th width="10%" class="text-center">Total Days</th>
        </tr>
        @forelse($records as $index => $record)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>{{ $record['biometric_id'] ?: '-' }}</td>
                <td>{{ $record['student_name'] }}</td>
                <td>{{ $record['course_batch_class'] }}</td>
                <td class="text-center" style="font-weight: bold; color: green;">{{ $record['present_days'] }}</td>
                <td class="text-center" style="font-weight: bold; color: red;">{{ $record['absent_days'] }}</td>
                <td class="text-center" style="font-weight: bold; color: blue;">{{ $record['total_days'] }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="7" class="text-center">No attendance records found.</td>
            </tr>
        @endforelse
    </table>

    <script type="text/javascript">
        window.onload = function() {
            window.print();
        }
    </script>
</body>
</html>
