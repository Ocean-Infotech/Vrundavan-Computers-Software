<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Summary Report - {{ $personal['name'] }}</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        body {
            font-family: Arial, Helvetica, sans-serif;
            color: #333;
            font-size: 11px;
            line-height: 1.25;
            background: #fff;
            padding: 15px;
        }

        /* Print Header */
        .print-header {
            border-bottom: 2px solid #222;
            padding-bottom: 6px;
            margin-bottom: 8px;
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
            font-size: 16px;
            font-weight: bold;
            color: #111;
            text-transform: uppercase;
        }
        .trust-name {
            font-size: 12px;
            color: #555;
            font-weight: bold;
        }
        .college-info {
            font-size: 10.5px;
            color: #666;
        }
        .report-title {
            text-align: center;
            font-size: 13px;
            font-weight: bold;
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #111;
            background: #f1f4f8;
            padding: 4px;
            border-radius: 3px;
        }

        /* Student Details Table */
        .details-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
            border: 1px solid #d5dbe0;
            background: #fafbfc;
        }
        .details-table td {
            padding: 4px 8px;
            vertical-align: middle;
            font-size: 11px;
            border: none;
        }
        .details-table td.label {
            font-weight: bold;
            width: 12%;
            color: #555;
        }
        .details-table td.value {
            width: 38%;
            color: #111;
        }

        /* Section Title Bar */
        .section-header-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            margin-bottom: 5px;
        }
        .section-title-cell {
            background-color: #f1f4f8;
            color: #1e293b;
            font-size: 11.5px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 5px 10px;
            border: 1px solid #cbd5e1;
            border-left: 4px solid #334155;
        }

        /* Split 2-Column Grid */
        .split-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
        }
        .split-col {
            width: 49.2%;
            vertical-align: top;
            padding: 0;
        }
        .split-gap {
            width: 1.6%;
            padding: 0;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10.5px;
            margin-bottom: 8px;
        }
        .data-table th, .data-table td {
            border: 1px solid #d5dbe0;
            padding: 4.5px 7px;
            text-align: left;
            vertical-align: middle;
        }
        .data-table tr.col-head th {
            background-color: #f8fafc;
            color: #475569;
            font-weight: bold;
            font-size: 10px;
        }
        .data-table tbody tr:nth-child(even) td {
            background-color: #fafbfc;
        }

        .empty-state-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
        }
        .empty-state-cell {
            text-align: center;
            color: #64748b;
            background-color: #f8fafc;
            border: 1px dashed #cbd5e1;
            padding: 8px 12px;
            font-size: 11px;
            font-style: italic;
            letter-spacing: 0.3px;
        }

        .text-center { text-align: center; }

        /* Badges */
        .badge {
            display: inline-block;
            padding: 1px 5px;
            font-size: 9px;
            font-weight: bold;
            border-radius: 3px;
            text-transform: uppercase;
        }
        .badge-success { background: #e2f5ec; color: #1f7a52; }
        .badge-warning { background: #fff4e5; color: #b36b00; }
        .badge-info { background: #e5f6fd; color: #0077b3; }
        .badge-danger { background: #fbeae9; color: #b32d24; }

        .no-print-bar {
            background: #f8f9fa;
            border: 1px solid #ddd;
            padding: 8px 15px;
            margin-bottom: 15px;
            display: flex;
            justify-content: flex-end;
            border-radius: 4px;
        }
        .btn {
            padding: 5px 12px;
            font-size: 12px;
            font-weight: 600;
            border-radius: 4px;
            cursor: pointer;
            border: 1px solid #ccc;
            background: #fff;
            margin-left: 5px;
        }
        .btn-primary { background: #5c1ac3; color: #fff; border-color: #5c1ac3; }

        @media print {
            .no-print-bar { display: none !important; }
            body { margin: 0; padding: 0; }
            .split-table, .empty-state-table, .data-table { page-break-inside: avoid; }
        }
    </style>
</head>
<body>

    <div class="no-print-bar">
        <button class="btn btn-primary" onclick="window.print()">Print Report</button>
        <button class="btn" onclick="window.close()">Close</button>
    </div>

    <!-- Header Section -->
    <div class="print-header">
        <table>
            <tr>
                <td style="width: 75px; vertical-align: middle;">
                    <img src="{{ asset('uploads/logo/logo.png') }}" alt="Logo" style="max-height: 55px; max-width: 65px;">
                </td>
                <td style="text-align: center; vertical-align: middle;">
                    <div class="trust-name">Shree Patel Vidhyarthi Ashram Sanchalit</div>
                    <div class="college-name">Shree Patel Vidhya Mandir Science College</div>
                    <div class="college-info">Veraval Road, Keshod - 362 220. Mo. 96874 51774</div>
                </td>
                <td style="width: 75px;"></td>
            </tr>
        </table>
    </div>

    <div class="report-title">Student Summary Report</div>

    <!-- Student Details -->
    <table class="details-table">
        <tr>
            <td class="label">Gr No. :</td>
            <td class="value"><strong>{{ $personal['gr_no'] }}</strong></td>
            <td class="label">Course :</td>
            <td class="value">{{ $academic['course'] ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">Student Name :</td>
            <td class="value"><strong>{{ $personal['name'] }}</strong></td>
            <td class="label">Batch / Class :</td>
            <td class="value">{{ $academic['batch'] }} / {{ $academic['class'] }} ({{ $academic['semester'] }})</td>
        </tr>
    </table>

    <!-- 1. ASSIGNMENT SECTION (<= 5: Full Width, > 5: 50/50 Split) -->
    <table class="section-header-table">
        <tr><td class="section-title-cell">Assignment</td></tr>
    </table>
    @php
        $assignCount = count($assignments);
    @endphp
    @if($assignCount == 0)
        <table class="empty-state-table">
            <tr><td class="empty-state-cell">— No assignments recorded —</td></tr>
        </table>
    @elseif($assignCount <= 5)
        <table class="data-table">
            <thead>
                <tr class="col-head">
                    <th style="width: 5%;">#</th>
                    <th>Subject</th>
                    <th style="width: 18%;">Date</th>
                    <th style="width: 18%;">Status</th>
                    <th style="width: 25%;">Remarks</th>
                </tr>
            </thead>
            <tbody>
                @foreach($assignments as $idx => $a)
                    <tr>
                        <td class="text-center">{{ $idx + 1 }}</td>
                        <td>{{ $a['subject'] }}</td>
                        <td>{{ $a['date'] }}</td>
                        <td>
                            <span class="badge {{ $a['status'] === 'Submitted' ? 'badge-success' : 'badge-warning' }}">
                                {{ $a['status'] }}
                            </span>
                        </td>
                        <td>{{ $a['remarks'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        @php
            $halfAssign = ceil($assignCount / 2);
            $assignLeft = array_slice($assignments, 0, $halfAssign);
            $assignRight = array_slice($assignments, $halfAssign);
        @endphp
        <table class="split-table">
            <tr>
                <td class="split-col">
                    <table class="data-table" style="margin-bottom: 0;">
                        <thead>
                            <tr class="col-head">
                                <th style="width: 7%;">#</th>
                                <th>Subject</th>
                                <th style="width: 22%;">Date</th>
                                <th style="width: 22%;">Status</th>
                                <th style="width: 23%;">Remarks</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($assignLeft as $idx => $a)
                                <tr>
                                    <td class="text-center">{{ $idx + 1 }}</td>
                                    <td>{{ $a['subject'] }}</td>
                                    <td>{{ $a['date'] }}</td>
                                    <td>
                                        <span class="badge {{ $a['status'] === 'Submitted' ? 'badge-success' : 'badge-warning' }}">
                                            {{ $a['status'] }}
                                        </span>
                                    </td>
                                    <td>{{ $a['remarks'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </td>
                <td class="split-gap"></td>
                <td class="split-col">
                    @if(!empty($assignRight))
                        <table class="data-table" style="margin-bottom: 0;">
                            <thead>
                                <tr class="col-head">
                                    <th style="width: 7%;">#</th>
                                    <th>Subject</th>
                                    <th style="width: 22%;">Date</th>
                                    <th style="width: 22%;">Status</th>
                                    <th style="width: 23%;">Remarks</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($assignRight as $idx => $a)
                                    <tr>
                                        <td class="text-center">{{ $halfAssign + $idx + 1 }}</td>
                                        <td>{{ $a['subject'] }}</td>
                                        <td>{{ $a['date'] }}</td>
                                        <td>
                                            <span class="badge {{ $a['status'] === 'Submitted' ? 'badge-success' : 'badge-warning' }}">
                                                {{ $a['status'] }}
                                            </span>
                                        </td>
                                        <td>{{ $a['remarks'] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </td>
            </tr>
        </table>
    @endif

    <!-- 2. TEST DETAIL SECTION (<= 5: Full Width, > 5: 50/50 Split) -->
    <table class="section-header-table">
        <tr><td class="section-title-cell">Test Detail</td></tr>
    </table>
    @php
        $testCount = count($tests);
    @endphp
    @if($testCount == 0)
        <table class="empty-state-table">
            <tr><td class="empty-state-cell">— No tests recorded —</td></tr>
        </table>
    @elseif($testCount <= 5)
        <table class="data-table">
            <thead>
                <tr class="col-head">
                    <th style="width: 5%;">#</th>
                    <th>Subject</th>
                    <th style="width: 18%;">Date</th>
                    <th style="width: 14%;">Total</th>
                    <th style="width: 16%;">Obtained</th>
                </tr>
            </thead>
            <tbody>
                @foreach($tests as $idx => $t)
                    <tr>
                        <td class="text-center">{{ $idx + 1 }}</td>
                        <td>{{ $t['subject'] }}</td>
                        <td>{{ $t['date'] }}</td>
                        <td class="text-center">{{ $t['total_marks'] }}</td>
                        <td class="text-center"><strong>{{ $t['obtained_marks'] }}</strong></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        @php
            $halfTest = ceil($testCount / 2);
            $testLeft = array_slice($tests, 0, $halfTest);
            $testRight = array_slice($tests, $halfTest);
        @endphp
        <table class="split-table">
            <tr>
                <td class="split-col">
                    <table class="data-table" style="margin-bottom: 0;">
                        <thead>
                            <tr class="col-head">
                                <th style="width: 7%;">#</th>
                                <th>Subject</th>
                                <th style="width: 22%;">Date</th>
                                <th style="width: 14%;">Total</th>
                                <th style="width: 16%;">Obtained</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($testLeft as $idx => $t)
                                <tr>
                                    <td class="text-center">{{ $idx + 1 }}</td>
                                    <td>{{ $t['subject'] }}</td>
                                    <td>{{ $t['date'] }}</td>
                                    <td class="text-center">{{ $t['total_marks'] }}</td>
                                    <td class="text-center"><strong>{{ $t['obtained_marks'] }}</strong></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </td>
                <td class="split-gap"></td>
                <td class="split-col">
                    @if(!empty($testRight))
                        <table class="data-table" style="margin-bottom: 0;">
                            <thead>
                                <tr class="col-head">
                                    <th style="width: 7%;">#</th>
                                    <th>Subject</th>
                                    <th style="width: 22%;">Date</th>
                                    <th style="width: 14%;">Total</th>
                                    <th style="width: 16%;">Obtained</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($testRight as $idx => $t)
                                    <tr>
                                        <td class="text-center">{{ $halfTest + $idx + 1 }}</td>
                                        <td>{{ $t['subject'] }}</td>
                                        <td>{{ $t['date'] }}</td>
                                        <td class="text-center">{{ $t['total_marks'] }}</td>
                                        <td class="text-center"><strong>{{ $t['obtained_marks'] }}</strong></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </td>
            </tr>
        </table>
    @endif

    <!-- 3. ASSESSMENT SECTION (<= 5: Full Width, > 5: 50/50 Split) -->
    <table class="section-header-table">
        <tr><td class="section-title-cell">Assessment</td></tr>
    </table>
    @php
        $asCount = count($assessments);
    @endphp
    @if($asCount == 0)
        <table class="empty-state-table">
            <tr><td class="empty-state-cell">— No assessments recorded —</td></tr>
        </table>
    @elseif($asCount <= 5)
        <table class="data-table">
            <thead>
                <tr class="col-head">
                    <th style="width: 5%;">#</th>
                    <th>Subject</th>
                    <th style="width: 25%;">Performance</th>
                    <th style="width: 35%;">Remarks</th>
                </tr>
            </thead>
            <tbody>
                @foreach($assessments as $idx => $as)
                    <tr>
                        <td class="text-center">{{ $idx + 1 }}</td>
                        <td>{{ $as['subject'] }}</td>
                        <td><span class="badge badge-info">{{ $as['performance'] }}</span></td>
                        <td>{{ $as['remarks'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        @php
            $halfAs = ceil($asCount / 2);
            $asLeft = array_slice($assessments, 0, $halfAs);
            $asRight = array_slice($assessments, $halfAs);
        @endphp
        <table class="split-table">
            <tr>
                <td class="split-col">
                    <table class="data-table" style="margin-bottom: 0;">
                        <thead>
                            <tr class="col-head">
                                <th style="width: 7%;">#</th>
                                <th>Subject</th>
                                <th style="width: 28%;">Performance</th>
                                <th style="width: 32%;">Remarks</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($asLeft as $idx => $as)
                                <tr>
                                    <td class="text-center">{{ $idx + 1 }}</td>
                                    <td>{{ $as['subject'] }}</td>
                                    <td><span class="badge badge-info">{{ $as['performance'] }}</span></td>
                                    <td>{{ $as['remarks'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </td>
                <td class="split-gap"></td>
                <td class="split-col">
                    @if(!empty($asRight))
                        <table class="data-table" style="margin-bottom: 0;">
                            <thead>
                                <tr class="col-head">
                                    <th style="width: 7%;">#</th>
                                    <th>Subject</th>
                                    <th style="width: 28%;">Performance</th>
                                    <th style="width: 32%;">Remarks</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($asRight as $idx => $as)
                                    <tr>
                                        <td class="text-center">{{ $halfAs + $idx + 1 }}</td>
                                        <td>{{ $as['subject'] }}</td>
                                        <td><span class="badge badge-info">{{ $as['performance'] }}</span></td>
                                        <td>{{ $as['remarks'] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </td>
            </tr>
        </table>
    @endif

    <!-- 4. COMPLAINTS SECTION (<= 5: Full Width, > 5: 50/50 Split) -->
    <table class="section-header-table">
        <tr><td class="section-title-cell">Complaints</td></tr>
    </table>
    @php
        $compCount = count($complaints);
    @endphp
    @if($compCount == 0)
        <table class="empty-state-table">
            <tr><td class="empty-state-cell">— No complaints recorded —</td></tr>
        </table>
    @elseif($compCount <= 5)
        <table class="data-table">
            <thead>
                <tr class="col-head">
                    <th style="width: 5%;">#</th>
                    <th>Complaint</th>
                    <th style="width: 20%;">Date</th>
                    <th style="width: 25%;">Faculty</th>
                </tr>
            </thead>
            <tbody>
                @foreach($complaints as $idx => $c)
                    <tr>
                        <td class="text-center">{{ $idx + 1 }}</td>
                        <td style="color: #b32d24;">{{ $c['complaint'] }}</td>
                        <td>{{ $c['date'] }}</td>
                        <td>{{ $c['faculty'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        @php
            $halfComp = ceil($compCount / 2);
            $compLeft = array_slice($complaints, 0, $halfComp);
            $compRight = array_slice($complaints, $halfComp);
        @endphp
        <table class="split-table">
            <tr>
                <td class="split-col">
                    <table class="data-table" style="margin-bottom: 0;">
                        <thead>
                            <tr class="col-head">
                                <th style="width: 7%;">#</th>
                                <th>Complaint</th>
                                <th style="width: 24%;">Date</th>
                                <th style="width: 28%;">Faculty</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($compLeft as $idx => $c)
                                <tr>
                                    <td class="text-center">{{ $idx + 1 }}</td>
                                    <td style="color: #b32d24;">{{ $c['complaint'] }}</td>
                                    <td>{{ $c['date'] }}</td>
                                    <td>{{ $c['faculty'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </td>
                <td class="split-gap"></td>
                <td class="split-col">
                    @if(!empty($compRight))
                        <table class="data-table" style="margin-bottom: 0;">
                            <thead>
                                <tr class="col-head">
                                    <th style="width: 7%;">#</th>
                                    <th>Complaint</th>
                                    <th style="width: 24%;">Date</th>
                                    <th style="width: 28%;">Faculty</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($compRight as $idx => $c)
                                    <tr>
                                        <td class="text-center">{{ $halfComp + $idx + 1 }}</td>
                                        <td style="color: #b32d24;">{{ $c['complaint'] }}</td>
                                        <td>{{ $c['date'] }}</td>
                                        <td>{{ $c['faculty'] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </td>
            </tr>
        </table>
    @endif

    <!-- 5. RESULT DETAILS SECTION (<= 5: Full Width, > 5: 50/50 Split) -->
    <table class="section-header-table">
        <tr><td class="section-title-cell">Result Details</td></tr>
    </table>
    @php
        $resCount = count($results);
    @endphp
    @if($resCount == 0)
        <table class="empty-state-table">
            <tr><td class="empty-state-cell">— No results recorded —</td></tr>
        </table>
    @elseif($resCount <= 5)
        <table class="data-table">
            <thead>
                <tr class="col-head">
                    <th style="width: 5%;">#</th>
                    <th>Semester / Exam</th>
                    <th style="width: 14%;">Total</th>
                    <th style="width: 14%;">Obtained</th>
                    <th style="width: 14%;">%</th>
                </tr>
            </thead>
            <tbody>
                @foreach($results as $idx => $r)
                    <tr>
                        <td class="text-center">{{ $idx + 1 }}</td>
                        <td>{{ $r['semester'] }}</td>
                        <td class="text-center">{{ $r['total_marks'] }}</td>
                        <td class="text-center">{{ $r['obtained_marks'] }}</td>
                        <td class="text-center"><strong>{{ $r['percentage'] }}</strong></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        @php
            $halfRes = ceil($resCount / 2);
            $resLeft = array_slice($results, 0, $halfRes);
            $resRight = array_slice($results, $halfRes);
        @endphp
        <table class="split-table">
            <tr>
                <td class="split-col">
                    <table class="data-table" style="margin-bottom: 0;">
                        <thead>
                            <tr class="col-head">
                                <th style="width: 7%;">#</th>
                                <th>Semester / Exam</th>
                                <th style="width: 16%;">Total</th>
                                <th style="width: 16%;">Obtained</th>
                                <th style="width: 16%;">%</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($resLeft as $idx => $r)
                                <tr>
                                    <td class="text-center">{{ $idx + 1 }}</td>
                                    <td>{{ $r['semester'] }}</td>
                                    <td class="text-center">{{ $r['total_marks'] }}</td>
                                    <td class="text-center">{{ $r['obtained_marks'] }}</td>
                                    <td class="text-center"><strong>{{ $r['percentage'] }}</strong></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </td>
                <td class="split-gap"></td>
                <td class="split-col">
                    @if(!empty($resRight))
                        <table class="data-table" style="margin-bottom: 0;">
                            <thead>
                                <tr class="col-head">
                                    <th style="width: 7%;">#</th>
                                    <th>Semester / Exam</th>
                                    <th style="width: 16%;">Total</th>
                                    <th style="width: 16%;">Obtained</th>
                                    <th style="width: 16%;">%</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($resRight as $idx => $r)
                                    <tr>
                                        <td class="text-center">{{ $halfRes + $idx + 1 }}</td>
                                        <td>{{ $r['semester'] }}</td>
                                        <td class="text-center">{{ $r['total_marks'] }}</td>
                                        <td class="text-center">{{ $r['obtained_marks'] }}</td>
                                        <td class="text-center"><strong>{{ $r['percentage'] }}</strong></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </td>
            </tr>
        </table>
    @endif

    <!-- 6. ACHIEVEMENTS SECTION (<= 5: Full Width, > 5: 50/50 Split) -->
    <table class="section-header-table">
        <tr><td class="section-title-cell">Achievements</td></tr>
    </table>
    @php
        $achCount = count($achievements);
    @endphp
    @if($achCount == 0)
        <table class="empty-state-table">
            <tr><td class="empty-state-cell">— No achievements recorded —</td></tr>
        </table>
    @elseif($achCount <= 5)
        <table class="data-table">
            <thead>
                <tr class="col-head">
                    <th style="width: 5%;">#</th>
                    <th>Event Name</th>
                    <th style="width: 16%;">Rank</th>
                    <th style="width: 18%;">Date</th>
                    <th style="width: 25%;">Remark</th>
                </tr>
            </thead>
            <tbody>
                @foreach($achievements as $idx => $ach)
                    <tr>
                        <td class="text-center">{{ $idx + 1 }}</td>
                        <td>{{ $ach['event'] }}</td>
                        <td><span class="badge badge-warning">{{ $ach['rank'] }}</span></td>
                        <td>{{ $ach['date'] }}</td>
                        <td>{{ $ach['remark'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        @php
            $halfAch = ceil($achCount / 2);
            $achLeft = array_slice($achievements, 0, $halfAch);
            $achRight = array_slice($achievements, $halfAch);
        @endphp
        <table class="split-table">
            <tr>
                <td class="split-col">
                    <table class="data-table" style="margin-bottom: 0;">
                        <thead>
                            <tr class="col-head">
                                <th style="width: 7%;">#</th>
                                <th>Event Name</th>
                                <th style="width: 18%;">Rank</th>
                                <th style="width: 20%;">Date</th>
                                <th style="width: 24%;">Remark</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($achLeft as $idx => $ach)
                                <tr>
                                    <td class="text-center">{{ $idx + 1 }}</td>
                                    <td>{{ $ach['event'] }}</td>
                                    <td><span class="badge badge-warning">{{ $ach['rank'] }}</span></td>
                                    <td>{{ $ach['date'] }}</td>
                                    <td>{{ $ach['remark'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </td>
                <td class="split-gap"></td>
                <td class="split-col">
                    @if(!empty($achRight))
                        <table class="data-table" style="margin-bottom: 0;">
                            <thead>
                                <tr class="col-head">
                                    <th style="width: 7%;">#</th>
                                    <th>Event Name</th>
                                    <th style="width: 18%;">Rank</th>
                                    <th style="width: 20%;">Date</th>
                                    <th style="width: 24%;">Remark</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($achRight as $idx => $ach)
                                    <tr>
                                        <td class="text-center">{{ $halfAch + $idx + 1 }}</td>
                                        <td>{{ $ach['event'] }}</td>
                                        <td><span class="badge badge-warning">{{ $ach['rank'] }}</span></td>
                                        <td>{{ $ach['date'] }}</td>
                                        <td>{{ $ach['remark'] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </td>
            </tr>
        </table>
    @endif

</body>
</html>
