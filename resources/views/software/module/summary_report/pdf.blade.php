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
            line-height: 1.2;
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
            padding: 4px;
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
            padding: 3px 6px;
            vertical-align: middle;
            font-size: 8pt;
        }

        .info-label {
            color: #555555;
            font-weight: bold;
            width: 14%;
        }

        .info-val {
            color: #111111;
            width: 32%;
        }

        /* Two column layout table */
        .layout-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 6px;
        }

        .col-left {
            width: 49%;
            vertical-align: top !important;
        }

        .col-spacer {
            width: 2%;
            vertical-align: top !important;
        }

        .col-right {
            width: 49%;
            vertical-align: top !important;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 7pt;
            margin-bottom: 4px;
        }

        .section-title {
            background-color: #f1f4f8;
            color: #1e293b;
            font-size: 7.5pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 3.5px 6px;
            text-align: left;
            border: 1px solid #cbd5e1;
            border-left: 4px solid #334155;
        }

        .data-table th, .data-table td {
            border: 1px solid #d5dbe0;
            padding: 2.5px 4px;
            text-align: left;
            vertical-align: middle;
        }

        .data-table tr.col-head th {
            background-color: #f8fafc;
            color: #475569;
            font-weight: bold;
            font-size: 6.8pt;
            border-top: none;
        }

        .data-table tbody tr:nth-child(even) td {
            background-color: #fafbfc;
        }

        .text-center {
            text-align: center;
        }

        .badge {
            display: inline-block;
            padding: 1px 3px;
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
            <td style="width: 55px; vertical-align: middle;">
                @if(!empty($logo_src))
                    <img src="{{ $logo_src }}" style="height: 45px; max-width: 50px;">
                @endif
            </td>
            <td style="text-align: center; vertical-align: middle;">
                <div class="trust-name">Shree Patel Vidhyarthi Ashram Sanchalit</div>
                <div class="college-name">Shree Patel Vidhya Mandir Science College</div>
                <div class="college-info">Veraval Road, Keshod - 362 220. Mo. 96874 51774</div>
            </td>
            <td style="width: 55px;"></td>
        </tr>
    </table>

    <div class="report-banner">Student Summary Report</div>

    <!-- Student Details Box -->
    <table class="info-table">
        <tr>
            <td class="info-label" style="width: 12%;">Gr No. :</td>
            <td class="info-val" style="width: 38%;"><strong>{{ $personal['gr_no'] }}</strong></td>
            <td class="info-label" style="width: 14%;">Course :</td>
            <td class="info-val" style="width: 36%;">{{ $academic['course'] ?? '-' }}</td>
        </tr>
        <tr>
            <td class="info-label">Name :</td>
            <td class="info-val"><strong>{{ $personal['name'] }}</strong></td>
            <td class="info-label">Batch / Class :</td>
            <td class="info-val">{{ $academic['batch'] }} / {{ $academic['class'] }} ({{ $academic['semester'] }})</td>
        </tr>
    </table>

    <!-- ROW 1: Assignment | Assessment -->
    <table class="layout-table">
        <tr>
            <td class="col-left" valign="top" style="vertical-align: top; width: 49%;">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th colspan="5" class="section-title">Assignment</th>
                        </tr>
                        <tr class="col-head">
                            <th style="width: 6%;">#</th>
                            <th>Subject</th>
                            <th style="width: 20%;">Date</th>
                            <th style="width: 20%;">Status</th>
                            <th style="width: 22%;">Remarks</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($assignments as $idx => $a)
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
                        @empty
                            <tr>
                                <td colspan="5" class="text-center" style="color: #888;">No assignments recorded.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </td>
            <td class="col-spacer" valign="top" style="vertical-align: top; width: 2%;"></td>
            <td class="col-right" valign="top" style="vertical-align: top; width: 49%;">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th colspan="4" class="section-title">Assessment</th>
                        </tr>
                        <tr class="col-head">
                            <th style="width: 6%;">#</th>
                            <th>Subject</th>
                            <th style="width: 26%;">Performance</th>
                            <th style="width: 32%;">Remarks</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($assessments as $idx => $as)
                            <tr>
                                <td class="text-center">{{ $idx + 1 }}</td>
                                <td>{{ $as['subject'] }}</td>
                                <td><span class="badge badge-info">{{ $as['performance'] }}</span></td>
                                <td>{{ $as['remarks'] }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center" style="color: #888;">No assessments recorded.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </td>
        </tr>
    </table>

    <!-- ROW 2: Test Detail | Complaints -->
    <table class="layout-table">
        <tr>
            <td class="col-left" valign="top" style="vertical-align: top; width: 49%;">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th colspan="5" class="section-title">Test Detail</th>
                        </tr>
                        <tr class="col-head">
                            <th style="width: 6%;">#</th>
                            <th>Subject</th>
                            <th style="width: 20%;">Date</th>
                            <th style="width: 14%;">Total</th>
                            <th style="width: 16%;">Obtained</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($tests as $idx => $t)
                            <tr>
                                <td class="text-center">{{ $idx + 1 }}</td>
                                <td>{{ $t['subject'] }}</td>
                                <td>{{ $t['date'] }}</td>
                                <td class="text-center">{{ $t['total_marks'] }}</td>
                                <td class="text-center"><strong>{{ $t['obtained_marks'] }}</strong></td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center" style="color: #888;">No tests recorded.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </td>
            <td class="col-spacer" valign="top" style="vertical-align: top; width: 2%;"></td>
            <td class="col-right" valign="top" style="vertical-align: top; width: 49%;">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th colspan="4" class="section-title">Complaints</th>
                        </tr>
                        <tr class="col-head">
                            <th style="width: 6%;">#</th>
                            <th>Complaint</th>
                            <th style="width: 22%;">Date</th>
                            <th style="width: 26%;">Faculty</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($complaints as $idx => $c)
                            <tr>
                                <td class="text-center">{{ $idx + 1 }}</td>
                                <td style="color: #b32d24;">{{ $c['complaint'] }}</td>
                                <td>{{ $c['date'] }}</td>
                                <td>{{ $c['faculty'] }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center" style="color: #888;">No complaints recorded.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </td>
        </tr>
    </table>

    <!-- ROW 3: Result Details | Achievements -->
    <table class="layout-table">
        <tr>
            <td class="col-left" valign="top" style="vertical-align: top; width: 49%;">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th colspan="5" class="section-title">Result Details</th>
                        </tr>
                        <tr class="col-head">
                            <th style="width: 6%;">#</th>
                            <th>Semester / Exam</th>
                            <th style="width: 16%;">Total</th>
                            <th style="width: 16%;">Obtained</th>
                            <th style="width: 16%;">%</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($results as $idx => $r)
                            <tr>
                                <td class="text-center">{{ $idx + 1 }}</td>
                                <td>{{ $r['semester'] }}</td>
                                <td class="text-center">{{ $r['total_marks'] }}</td>
                                <td class="text-center">{{ $r['obtained_marks'] }}</td>
                                <td class="text-center"><strong>{{ $r['percentage'] }}</strong></td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center" style="color: #888;">No results recorded.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </td>
            <td class="col-spacer" valign="top" style="vertical-align: top; width: 2%;"></td>
            <td class="col-right" valign="top" style="vertical-align: top; width: 49%;">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th colspan="5" class="section-title">Achievements</th>
                        </tr>
                        <tr class="col-head">
                            <th style="width: 6%;">#</th>
                            <th>Event Name</th>
                            <th style="width: 16%;">Rank</th>
                            <th style="width: 20%;">Date</th>
                            <th style="width: 24%;">Remark</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($achievements as $idx => $ach)
                            <tr>
                                <td class="text-center">{{ $idx + 1 }}</td>
                                <td>{{ $ach['event'] }}</td>
                                <td><span class="badge badge-warning">{{ $ach['rank'] }}</span></td>
                                <td>{{ $ach['date'] }}</td>
                                <td>{{ $ach['remark'] }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center" style="color: #888;">No achievements recorded.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </td>
        </tr>
    </table>

</body>
</html>
