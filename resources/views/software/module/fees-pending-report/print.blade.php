<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fees Pending Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .header h2, .header h3 {
            margin: 5px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #000;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
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
            button.print-btn {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div style="text-align: right; margin-bottom: 20px;">
        <button class="print-btn" onclick="window.print()" style="padding: 8px 16px; background: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer;">Print</button>
    </div>

    <div class="header">
        <h2>Fees Pending Report</h2>
        <h3>{{ $course ? $course->course_name : 'Course' }} - {{ $batch ? $batch->batch : 'Batch' }} | Sem - {{ $semester_id ?? '...' }}</h3>
    </div>

    <table>
        <thead>
            <tr>
                <th width="10%" class="text-center">No</th>
                <th width="60%">Student Name</th>
                <th width="30%" class="text-right">Pending Fees</th>
            </tr>
        </thead>
        <tbody>
            @forelse($students as $index => $student)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>
                        {{ $student->admission->first_name ?? '' }}
                        {{ $student->admission->last_name ?? '' }}
                        {{ $student->admission->father_name ?? '' }}
                    </td>
                    <td class="text-right">{{ number_format($student->pending_fees, 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" class="text-center">No students found with pending fees for this semester.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <script>
        window.onload = function() {
            // window.print();
        };
    </script>
</body>
</html>
