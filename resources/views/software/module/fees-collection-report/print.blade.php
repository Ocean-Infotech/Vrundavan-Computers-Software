<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fees Collection Report</title>
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

        .footer-note {
            text-align: center;
            border: 1px solid #000;
            padding: 20px;
            margin-top: 20px;
            font-weight: bold;
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
    @endphp

    <table class="report-sheet">
        <tr>
            <td class="report-logo-cell" rowspan="2">
                <img src="{{ $logoPath }}" alt="College Logo">
            </td>
            <td class="college-name-cell" colspan="2">
                Shree Patel Vidhya Mandir Science College
            </td>
        </tr>
        <tr>
            <td class="report-title-cell" colspan="2">
                Fees Collection Report | {{ $reportSubtitle }}
            </td>
        </tr>
        <tr>
            <th width="10%" class="text-center">No</th>
            <th width="35%">Student Name</th>
            <th width="55%">Installments</th>
        </tr>
        @forelse($students as $index => $student)
            @php
                $installmentItems = $student->installments
                    ->map(function ($installment) {
                        $amount = number_format((float) ($installment->fees ?? 0), 0, '', '');
                        $date = $installment->date
                            ? \Carbon\Carbon::parse($installment->date)->format('j-n-Y')
                            : '';

                        if ($amount === '' && $date === '') {
                            return '';
                        }

                        return $amount . '(' . $date . ')';
                    })
                    ->filter()
                    ->values();

                $installmentText = $installmentItems->isEmpty()
                    ? ''
                    : $installmentItems->implode(',');

                $first = $student->admission->first_name ?? '';
                $last = $student->admission->last_name ?? '';
                $father = $student->admission->father_name ?? '';
                $studentName = strtoupper(trim("{$first} {$last} {$father}"));
            @endphp
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>{{ $studentName }}</td>
                <td>{{ $installmentText }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="3" class="text-center">No students found for this semester.</td>
            </tr>
        @endforelse
    </table>

    <div class="footer-note">
        જે સેમેસ્ટર ની ફી કલેક્ટ કરવાની હોઈ તે સેમેસ્ટર પ્રમાણે વિદ્યાર્થીઓનું લીસ્ટ આવવું જોઈયે
    </div>
</body>
</html>
