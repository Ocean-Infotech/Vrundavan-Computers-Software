<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>HSC Marksheet Report</title>
    <style>
        body {
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            margin: 20px;
            color: #111;
        }

        h1 {
            text-align: center;
            margin-bottom: 5px;
        }

        .meta {
            text-align: center;
            font-size: 13px;
            margin-bottom: 15px;
        }

        .no-print {
            text-align: right;
            margin-bottom: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            font-size: 12px;
        }

        th,
        td {
            border: 1px solid #555;
            padding: 6px 8px;
            text-align: left;
        }

        th {
            background-color: #f2f4f7;
        }

        @media print {
            .no-print {
                display: none;
            }
        }
    </style>
</head>

<body>
    <div class="no-print">
        <button onclick="window.print()">Print</button>
        <button onclick="window.close()">Close</button>
    </div>

    <h1>HSC Marksheet Report</h1>
    <div class="meta">
        <div><strong>Generated On:</strong> {{ optional($generatedAt)->format('d-m-Y h:i A') }}</div>
    </div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Student Name</th>
                <th>Roll No</th>
                <th>Exam</th>
                <th>Year</th>
                <th>Standard</th>
                <th>Marks</th>
                <th>Percentage</th>
                <th>Grade</th>
                <th>Rank</th>
                <th>Result</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($records as $index => $row)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $row->studentName() }}</td>
                    <td>{{ $row->roll_number ?: '-' }}</td>
                    <td>{{ $row->exam_title }}</td>
                    <td>{{ $row->academic_year }}</td>
                    <td>{{ $row->standard }}</td>
                    <td>{{ number_format((float) $row->total_obtained, 0) }} / {{ number_format((float) $row->total_maximum, 0) }}</td>
                    <td>{{ number_format((float) $row->percentage, 2) }}%</td>
                    <td>{{ $row->grade ?: '-' }}</td>
                    <td>{{ $row->rank ?: '-' }}</td>
                    <td>{{ $row->result ?: '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="11" style="text-align:center;">No records found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>

</html>
