<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>HSC Marksheet</title>
    <style>
        body {
            font-family: dejavusans, sans-serif;
            color: #111;
            font-size: 12px;
            margin: 0;
            padding: 0;
        }
        .sheet {
            border: 2.5px solid #111;
            padding: 2px;
        }
        .inner {
            border: 1.2px solid #111;
            padding: 8px 12px 14px;
        }
        .header-img {
            width: 100%;
            max-height: 120px;
            margin-bottom: 8px;
        }
        .info-box {
            border: 1.2px solid #111;
            border-radius: 6px;
            overflow: hidden;
            margin: 4px 0 10px;
        }
        .info-head {
            background: #e9e9e9;
            border-bottom: 1.2px solid #111;
            text-align: center;
            font-size: 14px;
            font-weight: bold;
            padding: 5px 8px;
        }
        .info-body {
            padding: 8px 10px 10px;
            font-size: 12px;
        }
        .info-row {
            margin-bottom: 6px;
        }
        .label { font-weight: bold; }
        .uline {
            border-bottom: 1px solid #111;
            display: inline-block;
            padding: 0 2px 1px;
            min-width: 20px;
        }
        .two-col {
            width: 100%;
        }
        .two-col td {
            width: 50%;
            vertical-align: bottom;
            padding: 0;
            border: none;
        }
        table.marks {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
            font-size: 12px;
        }
        table.marks th,
        table.marks td {
            border: 1px solid #111;
            padding: 7px 8px;
        }
        table.marks th {
            text-align: center;
            font-weight: bold;
        }
        .sr, .max, .obt { text-align: center; }
        .total td { font-weight: bold; text-align: center; }
        table.summary {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
            font-weight: bold;
        }
        table.summary td {
            border: 1px solid #111;
            width: 25%;
            padding: 8px 6px;
        }
        .wish {
            text-align: center;
            font-style: italic;
            font-weight: bold;
            font-size: 14px;
            margin: 12px 0 8px;
        }
        table.sign {
            width: 100%;
            margin-top: 28px;
            font-size: 12px;
            font-weight: bold;
        }
        table.sign td { border: none; width: 50%; }
        .right { text-align: right; }
    </style>
</head>
<body>
@php
    $marks = $marksheet->marks ?? [];
@endphp
<div class="sheet">
    <div class="inner">
        @if (!empty($header_src))
            <img src="{{ $header_src }}" class="header-img" alt="Header">
        @endif

        <div class="info-box">
            <div class="info-head">Student Information</div>
            <div class="info-body">
                <div class="info-row">
                    <span class="label">Student Name :</span>
                    <span class="uline">{{ $studentName }}</span>
                </div>
                <div class="info-row">
                    <span class="label">Academic Year :</span>
                    {{ $marksheet->academic_year }}
                </div>
                <table class="two-col">
                    <tr>
                        <td>
                            <span class="label">Standard :</span>
                            {{ $marksheet->standard }}
                        </td>
                        <td>
                            <span class="label">Roll Number :</span>
                            <span class="uline">{{ $marksheet->roll_number ?: '' }}</span>
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <table class="marks">
            <thead>
                <tr>
                    <th style="width:10%;">Sr. No.</th>
                    <th>Subject</th>
                    <th style="width:18%;">Maximum Marks</th>
                    <th style="width:18%;">Marks Obtained</th>
                </tr>
            </thead>
            <tbody>
                @php $sr = 0; @endphp
                @foreach ($subjects as $subject)
                    @php
                        $obt = $marks[$subject['key']] ?? '';
                        $obtUpper = strtoupper(trim((string) $obt));
                        if ($obtUpper === 'NA') {
                            continue;
                        }
                        if ($marksheet->is_absent || $obtUpper === 'AB') {
                            $obt = 'AB';
                        }
                        $sr++;
                    @endphp
                    <tr>
                        <td class="sr">{{ $sr }}</td>
                        <td>{{ $subject['name'] }}</td>
                        <td class="max">{{ $subject['max'] }}</td>
                        <td class="obt">{{ $obt !== '' ? $obt : '' }}</td>
                    </tr>
                @endforeach
                <tr class="total">
                    <td colspan="2">TOTAL</td>
                    <td>{{ number_format((float) $marksheet->total_maximum, 0) }}</td>
                    <td>{{ $marksheet->is_absent || strtoupper((string) $marksheet->result) === 'ABSENT' ? 'AB' : number_format((float) $marksheet->total_obtained, 0) }}</td>
                </tr>
            </tbody>
        </table>

        <table class="summary">
            <tr>
                <td>Percentage: {{ $marksheet->is_absent ? 'AB' : number_format((float) $marksheet->percentage, 2) . ' %' }}</td>
                <td>Grade: {{ $marksheet->grade ?: '' }}</td>
                <td>Rank: {{ $marksheet->rank ?: '' }}</td>
                <td>Result: {{ $marksheet->result ?: '' }}</td>
            </tr>
        </table>

        <div class="wish">Best Wish to All Student</div>

        <table class="sign">
            <tr>
                <td>Class Teacher Signature</td>
                <td class="right">Principal Signature</td>
            </tr>
        </table>
    </div>
</div>
</body>
</html>
