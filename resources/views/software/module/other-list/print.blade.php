<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Other List</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            color: #000;
        }

        .print-action {
            text-align: right;
            margin-bottom: 20px;
        }

        .print-btn {
            padding: 8px 16px;
            background: #007bff;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .header {
            text-align: center;
            margin-bottom: 18px;
        }

        .header h2,
        .header h4 {
            margin: 6px 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 8px 6px;
            font-size: 14px;
        }

        th {
            text-align: left;
            font-weight: bold;
        }

        .text-center {
            text-align: center;
        }

        .bottom-note {
            margin-top: 18px;
            text-align: center;
            font-size: 15px;
        }

        @media print {
            body {
                padding: 0;
            }

            .print-action {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="print-action">
        <button class="print-btn" onclick="window.print()">Print</button>
    </div>

    <div class="header">
        <h2>{{ $page_heading ?: 'Other List' }}</h2>
        <h4>
            {{ $course?->course_name ?? 'Course' }} - {{ $batch?->batch_name ?? 'Batch' }}
            | Semester - {{ $semester_id ?? '-' }}
        </h4>
    </div>

    <table>
        <thead>
            <tr>
                <th width="8%" class="text-center">No</th>
                <th width="36%">Name</th>
                <th width="14%">Gender</th>
                <th width="16%">Fees</th>
                <th width="26%">Note</th>
            </tr>
        </thead>
        <tbody>
            @forelse($students as $index => $student)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $student->admission->full_name ?? '-' }}</td>
                    <td>{{ $student->admission->gender ?? '-' }}</td>
                    <td></td>
                    <td></td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center">No students found for selected filters.</td>
                </tr>
            @endforelse

            @for ($i = 0; $i < $blankRows; $i++)
                <tr>
                    <td class="text-center"></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
            @endfor
        </tbody>
    </table>

    {{-- <div class="bottom-note">
        સેમેસ્ટર પ્રમાણે બીલો નીચે લખવાના રહેશે (Ex. Exam Form, Tour List)
    </div> --}}
</body>
</html>
