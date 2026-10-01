<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Summary Report - {{ $personal['name'] }}</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            color: #333333;
            font-size: 8pt;
            line-height: 1.25;
            margin: 0;
            padding: 0;
        }

        .header-table {
            width: 100%;
            border-bottom: 2px solid #333333;
            padding-bottom: 5px;
            margin-bottom: 8px;
        }

        .college-name {
            font-size: 13pt;
            font-weight: bold;
            color: #111111;
            text-transform: uppercase;
        }

        .trust-name {
            font-size: 10pt;
            color: #555555;
            font-weight: bold;
        }

        .college-info {
            font-size: 7.5pt;
            color: #666666;
        }

        .report-banner {
            text-align: center;
            font-size: 9.5pt;
            font-weight: bold;
            background-color: #f1f4f8;
            color: #2c3e50;
            padding: 4px 6px;
            border-radius: 3px;
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Student info box */
        .info-table {
            width: 100%;
            border: 1px solid #d5dbe0;
            background-color: #fafbfc;
            margin-bottom: 8px;
            border-collapse: collapse;
        }

        .info-table td {
            padding: 3.5px 6px;
            vertical-align: middle;
            font-size: 8pt;
        }

        .info-label {
            color: #555555;
            font-weight: bold;
            width: 13%;
        }

        .info-val {
            color: #111111;
            width: 37%;
        }

        /* Section Header Table */
        .section-header-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 6px;
            margin-bottom: 3px;
        }

        .section-title-cell {
            background-color: #f1f4f8;
            color: #1e293b;
            font-size: 7.8pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            padding: 3.5px 7px;
            border: 1px solid #cbd5e1;
            border-left: 4px solid #334155;
        }

        /* Split 2-column table */
        .split-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 6px;
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
            font-size: 7.2pt;
            margin-bottom: 6px;
        }

        .data-table th, .data-table td {
            border: 1px solid #d5dbe0;
            padding: 3px 5px;
            text-align: left;
            vertical-align: middle;
        }

        .data-table tr.col-head th {
            background-color: #f8fafc;
            color: #475569;
            font-weight: bold;
            font-size: 7pt;
        }

        .data-table tbody tr:nth-child(even) td {
            background-color: #fafbfc;
        }

        .empty-state-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 6px;
        }

        .empty-state-cell {
            text-align: center;
            color: #64748b;
            background-color: #f8fafc;
            border: 1px dashed #cbd5e1;
            padding: 6px 10px;
            font-size: 7.5pt;
            font-style: italic;
            letter-spacing: 0.2px;
        }

        .text-center {
            text-align: center;
        }

        .badge {
            display: inline-block;
            padding: 1.5px 4px;
            font-size: 6.5pt;
            font-weight: bold;
            border-radius: 2px;
            text-transform: uppercase;
        }

        .badge-success { background-color: #e2f5ec; color: #1f7a52; }
        .badge-warning { background-color: #fff4e5; color: #b36b00; }
        .badge-info { background-color: #e5f6fd; color: #0077b3; }
        .badge-danger { background-color: #fbeae9; color: #b32d24; }
    </style>
</head>
<body>

    <!-- Header -->
    <table class="header-table">
        <tr>
            <td style="width: 50px; vertical-align: middle;">
                @if(!empty($logo_src))
                    <img src="{{ $logo_src }}" style="height: 42px; max-width: 48px;">
                @endif
            </td>
            <td style="text-align: center; vertical-align: middle;">
                <div class="trust-name">Shree Patel Vidhyarthi Ashram Sanchalit</div>
                <div class="college-name">Shree Patel Vidhya Mandir Science College</div>
                <div class="college-info">Veraval Road, Keshod - 362 220. Mo. 96874 51774</div>
            </td>
            <td style="width: 50px;"></td>
        </tr>
    </table>

    <div class="report-banner">Student Summary Report</div>

    <!-- Student Details Box -->
    <table class="info-table">
        <tr>
            <td class="info-label">Gr No. :</td>
            <td class="info-val"><strong>{{ $personal['gr_no'] }}</strong></td>
            <td class="info-label">Course :</td>
            <td class="info-val">{{ $academic['course'] ?? '-' }}</td>
        </tr>
        <tr>
            <td class="info-label">Name :</td>
            <td class="info-val"><strong>{{ $personal['name'] }}</strong></td>
            <td class="info-label">Batch / Class :</td>
            <td class="info-val">{{ $academic['batch'] }} / {{ $academic['class'] }} ({{ $academic['semester'] }})</td>
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
                    @php
                        $s = strtolower($a['status'] ?? '');
                        $badgeClass = ($s === 'complete' || $s === 'completed' || $s === 'submitted') ? 'badge-success' : (($s === 'incomplete') ? 'badge-danger' : 'badge-warning');
                        $statusText = ($s === 'complete' || $s === 'completed' || $s === 'submitted') ? 'Complete' : (($s === 'incomplete') ? 'Incomplete' : 'Pending');
                    @endphp
                    <tr>
                        <td class="text-center">{{ $idx + 1 }}</td>
                        <td>{{ $a['subject'] }}</td>
                        <td>{{ $a['date'] }}</td>
                        <td>
                            <span class="badge {{ $badgeClass }}">
                                {{ $statusText }}
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
                <td class="split-col" valign="top">
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
                                @php
                                    $s = strtolower($a['status'] ?? '');
                                    $badgeClass = ($s === 'complete' || $s === 'completed' || $s === 'submitted') ? 'badge-success' : (($s === 'incomplete') ? 'badge-danger' : 'badge-warning');
                                    $statusText = ($s === 'complete' || $s === 'completed' || $s === 'submitted') ? 'Complete' : (($s === 'incomplete') ? 'Incomplete' : 'Pending');
                                @endphp
                                <tr>
                                    <td class="text-center">{{ $idx + 1 }}</td>
                                    <td>{{ $a['subject'] }}</td>
                                    <td>{{ $a['date'] }}</td>
                                    <td>
                                        <span class="badge {{ $badgeClass }}">
                                            {{ $statusText }}
                                        </span>
                                    </td>
                                    <td>{{ $a['remarks'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </td>
                <td class="split-gap"></td>
                <td class="split-col" valign="top">
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
                                    @php
                                        $s = strtolower($a['status'] ?? '');
                                        $badgeClass = ($s === 'complete' || $s === 'completed' || $s === 'submitted') ? 'badge-success' : (($s === 'incomplete') ? 'badge-danger' : 'badge-warning');
                                        $statusText = ($s === 'complete' || $s === 'completed' || $s === 'submitted') ? 'Complete' : (($s === 'incomplete') ? 'Incomplete' : 'Pending');
                                    @endphp
                                    <tr>
                                        <td class="text-center">{{ $halfAssign + $idx + 1 }}</td>
                                        <td>{{ $a['subject'] }}</td>
                                        <td>{{ $a['date'] }}</td>
                                        <td>
                                            <span class="badge {{ $badgeClass }}">
                                                {{ $statusText }}
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
                <td class="split-col" valign="top">
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
                <td class="split-col" valign="top">
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
                <td class="split-col" valign="top">
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
                <td class="split-col" valign="top">
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
                <td class="split-col" valign="top">
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
                <td class="split-col" valign="top">
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
                <td class="split-col" valign="top">
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
                <td class="split-col" valign="top">
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
                <td class="split-col" valign="top">
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
                <td class="split-col" valign="top">
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

    <!-- 7. ATTENDANCE SECTION (Month-wise) -->
    <table class="section-header-table">
        <tr><td class="section-title-cell">Attendance Details (Month-wise)</td></tr>
    </table>
    @php
        $attCount = count($attendance ?? []);
    @endphp
    @if($attCount == 0)
        <table class="empty-state-table">
            <tr><td class="empty-state-cell">— No attendance records found —</td></tr>
        </table>
    @elseif($attCount <= 5)
        <table class="data-table">
            <thead>
                <tr class="col-head">
                    <th style="width: 5%;">#</th>
                    <th>Month</th>
                    <th style="width: 22%;">Total Working Days</th>
                    <th style="width: 20%;">Present Days</th>
                    <th style="width: 20%;">Absent Days</th>
                    <th style="width: 18%;">Holidays</th>
                </tr>
            </thead>
            <tbody>
                @foreach($attendance as $idx => $att)
                    <tr>
                        <td class="text-center">{{ $idx + 1 }}</td>
                        <td><strong>{{ $att['month'] }}</strong></td>
                        <td class="text-center">{{ $att['total_days'] }}</td>
                        <td class="text-center" style="color: #1f7a52; font-weight: bold;">{{ $att['present_days'] }}</td>
                        <td class="text-center" style="color: #b32d24; font-weight: bold;">{{ $att['absent_days'] }}</td>
                        <td class="text-center">{{ $att['holiday_days'] ?? 0 }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        @php
            $halfAtt = ceil($attCount / 2);
            $attLeft = array_slice($attendance, 0, $halfAtt);
            $attRight = array_slice($attendance, $halfAtt);
        @endphp
        <table class="split-table">
            <tr>
                <td class="split-col" valign="top">
                    <table class="data-table" style="margin-bottom: 0;">
                        <thead>
                            <tr class="col-head">
                                <th style="width: 6%;">#</th>
                                <th>Month</th>
                                <th style="width: 22%;">Total</th>
                                <th style="width: 20%;">Present</th>
                                <th style="width: 20%;">Absent</th>
                                <th style="width: 18%;">Holidays</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($attLeft as $idx => $att)
                                <tr>
                                    <td class="text-center">{{ $idx + 1 }}</td>
                                    <td><strong>{{ $att['month'] }}</strong></td>
                                    <td class="text-center">{{ $att['total_days'] }}</td>
                                    <td class="text-center" style="color: #1f7a52; font-weight: bold;">{{ $att['present_days'] }}</td>
                                    <td class="text-center" style="color: #b32d24; font-weight: bold;">{{ $att['absent_days'] }}</td>
                                    <td class="text-center">{{ $att['holiday_days'] ?? 0 }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </td>
                <td class="split-gap"></td>
                <td class="split-col" valign="top">
                    @if(!empty($attRight))
                        <table class="data-table" style="margin-bottom: 0;">
                            <thead>
                                <tr class="col-head">
                                    <th style="width: 6%;">#</th>
                                    <th>Month</th>
                                    <th style="width: 22%;">Total</th>
                                    <th style="width: 20%;">Present</th>
                                    <th style="width: 20%;">Absent</th>
                                    <th style="width: 18%;">Holidays</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($attRight as $idx => $att)
                                    <tr>
                                        <td class="text-center">{{ $halfAtt + $idx + 1 }}</td>
                                        <td><strong>{{ $att['month'] }}</strong></td>
                                        <td class="text-center">{{ $att['total_days'] }}</td>
                                        <td class="text-center" style="color: #1f7a52; font-weight: bold;">{{ $att['present_days'] }}</td>
                                        <td class="text-center" style="color: #b32d24; font-weight: bold;">{{ $att['absent_days'] }}</td>
                                        <td class="text-center">{{ $att['holiday_days'] ?? 0 }}</td>
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
