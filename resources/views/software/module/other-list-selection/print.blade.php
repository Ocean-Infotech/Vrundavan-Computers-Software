<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Other List Selection</title>
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
            padding: 8px 6px;
            font-size: 14px;
        }

        .report-sheet th {
            font-weight: 700;
            text-align: left;
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

        .bottom-note {
            margin-top: 18px;
            text-align: center;
            font-size: 15px;
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

        $reportSubtitle = sprintf(
            '%s - %s | Sem - %s',
            $course?->course_name ?? 'Course',
            $batch?->batch_name ?? 'Batch',
            $semester_id ?? '-'
        );

        $reportTitle = ($page_heading ?: 'Other List Selection') . ' | ' . $reportSubtitle;
    @endphp

    <table class="report-sheet">
        <tr>
            <td class="report-logo-cell" rowspan="2">
                <img src="{{ $logoPath }}" alt="College Logo">
            </td>
            <td class="college-name-cell" colspan="4">
                Shree Patel Vidhya Mandir Science College
            </td>
        </tr>
        <tr>
            <td class="report-title-cell" colspan="4">
                {{ $reportTitle }}
            </td>
        </tr>
        <tr>
            <th width="8%" class="text-center">No</th>
            <th width="36%">Name</th>
            <th width="14%">Gender</th>
            <th width="16%">Fees</th>
            <th width="26%">Note</th>
        </tr>
        @forelse($students as $index => $student)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>{{ strtoupper($student->admission->full_name ?? '-') }}</td>
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
    </table>

    <div class="bottom-note">
        સેમેસ્ટર પ્રમાણે બીલો નીચે લખવાના રહેશે (Ex. Exam Form, Tour List)
    </div>

    <script>
        window.onload = function() {
            window.print();
        };
    </script>
</body>
</html>
