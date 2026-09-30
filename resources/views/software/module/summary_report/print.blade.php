<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Summary Report - {{ $personal['name'] }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 10mm;
        }
        body {
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 10px;
            color: #333;
            font-size: 11px;
            line-height: 1.3;
        }
        .print-header {
            border-bottom: 2px solid #333;
            padding-bottom: 8px;
            margin-bottom: 12px;
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
            font-size: 18px;
            font-weight: bold;
            color: #111;
            text-transform: uppercase;
        }
        .trust-name {
            font-size: 14px;
            color: #555;
        }
        .college-info {
            font-size: 11px;
            color: #666;
        }
        .report-title {
            text-align: center;
            font-size: 14px;
            font-weight: bold;
            margin: 8px 0;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #2c3e50;
            background: #f4f6f9;
            padding: 4px;
            border-radius: 4px;
        }

        /* Details Section */
        .details-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
            background: #fafafa;
            border: 1px solid #e0e0e0;
            border-radius: 4px;
        }
        .details-table td {
            border: none;
            padding: 4px 8px;
            vertical-align: top;
            font-size: 11px;
        }
        .details-table td.label {
            font-weight: bold;
            width: 15%;
            color: #555;
        }
        .details-table td.value {
            width: 35%;
            color: #111;
        }
        .student-photo-cell {
            width: 75px;
            text-align: right;
            vertical-align: middle;
            padding: 4px !important;
        }
        .student-photo {
            width: 65px;
            height: 65px;
            object-fit: cover;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        /* 2-Column Grid for Print */
        .two-col-grid {
            width: 100%;
            border-collapse: separate;
            border-spacing: 10px 0;
            margin-bottom: 8px;
            page-break-inside: avoid;
        }
        .two-col-grid > tbody > tr > td {
            width: 50%;
            vertical-align: top;
            padding: 0;
            border: none;
        }

        .report-card {
            border: 1px solid #d5dbe0;
            border-radius: 4px;
            padding: 6px;
            background: #fff;
            min-height: 120px;
        }
        .section-title {
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
            background-color: #f1f4f8;
            padding: 4px 8px;
            margin: -6px -6px 6px -6px;
            border-bottom: 1px solid #d5dbe0;
            border-left: 3px solid #5c1ac3;
            letter-spacing: 0.5px;
            color: #333;
        }
        .report-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10px;
        }
        .report-table th, .report-table td {
            border: 1px solid #e2e8f0;
            padding: 4px 6px;
            text-align: left;
        }
        .report-table th {
            background-color: #f8fafc;
            font-weight: 600;
            color: #475569;
        }
        .report-table tr:nth-child(even) td {
            background-color: #fafbfc;
        }

        .badge {
            display: inline-block;
            padding: 1px 4px;
            font-size: 9px;
            font-weight: bold;
            border-radius: 2px;
            text-transform: uppercase;
        }
        .badge-success { background-color: #e2f5ec; color: #1f7a52; }
        .badge-danger { background-color: #fbeae9; color: #b32d24; }
        .badge-warning { background-color: #fff4e5; color: #b36b00; }
        .badge-info { background-color: #e5f6fd; color: #0077b3; }

        .no-print-bar {
            background: #f5f5f5;
            padding: 8px 15px;
            border-bottom: 1px solid #ddd;
            margin-bottom: 15px;
            text-align: right;
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
            .two-col-grid { page-break-inside: avoid; }
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
            <td class="label" style="width: 12%;">Gr No. :</td>
            <td class="value" style="width: 38%;"><strong>{{ $personal['gr_no'] }}</strong></td>
            <td class="label" style="width: 14%;">Course :</td>
            <td class="value" style="width: 36%;">{{ $academic['course'] ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">Student Name :</td>
            <td class="value"><strong>{{ $personal['name'] }}</strong></td>
            <td class="label">Batch / Class :</td>
            <td class="value">{{ $academic['batch'] }} / {{ $academic['class'] }} ({{ $academic['semester'] }})</td>
        </tr>
    </table>

    <!-- ROW 1: Assignment | Assessment -->
    <table class="two-col-grid">
        <tr>
            <!-- Left: Assignment -->
            <td>
                <div class="report-card">
                    <div class="section-title">Assignment</div>
                    <table class="report-table">
                        <thead>
                            <tr>
                                <th style="width: 8%;">No</th>
                                <th>Subject</th>
                                <th>Date</th>
                                <th>Status</th>
                                <th>Remarks</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($assignments as $idx => $a)
                                <tr>
                                    <td>{{ $idx + 1 }}</td>
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
                                    <td colspan="5" style="text-align: center; color: #888;">No assignments recorded.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </td>

            <!-- Right: Assessment -->
            <td>
                <div class="report-card">
                    <div class="section-title">Assessment</div>
                    <table class="report-table">
                        <thead>
                            <tr>
                                <th style="width: 8%;">No</th>
                                <th>Subject</th>
                                <th>Performance</th>
                                <th>Remarks</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($assessments as $idx => $as)
                                <tr>
                                    <td>{{ $idx + 1 }}</td>
                                    <td>{{ $as['subject'] }}</td>
                                    <td><span class="badge badge-info">{{ $as['performance'] }}</span></td>
                                    <td>{{ $as['remarks'] }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" style="text-align: center; color: #888;">No assessments recorded.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </td>
        </tr>
    </table>

    <!-- ROW 2: Test Detail | Complaints -->
    <table class="two-col-grid">
        <tr>
            <!-- Left: Test Detail -->
            <td>
                <div class="report-card">
                    <div class="section-title">Test Detail</div>
                    <table class="report-table">
                        <thead>
                            <tr>
                                <th style="width: 8%;">No</th>
                                <th>Subject</th>
                                <th>Date</th>
                                <th>Total</th>
                                <th>Obtained</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($tests as $idx => $t)
                                <tr>
                                    <td>{{ $idx + 1 }}</td>
                                    <td>{{ $t['subject'] }}</td>
                                    <td>{{ $t['date'] }}</td>
                                    <td>{{ $t['total_marks'] }}</td>
                                    <td><strong>{{ $t['obtained_marks'] }}</strong></td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" style="text-align: center; color: #888;">No tests recorded.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </td>

            <!-- Right: Complaints -->
            <td>
                <div class="report-card">
                    <div class="section-title">Complaints</div>
                    <table class="report-table">
                        <thead>
                            <tr>
                                <th style="width: 8%;">No</th>
                                <th>Complaint</th>
                                <th>Date</th>
                                <th>Faculty</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($complaints as $idx => $c)
                                <tr>
                                    <td>{{ $idx + 1 }}</td>
                                    <td style="color: #b32d24;">{{ $c['complaint'] }}</td>
                                    <td>{{ $c['date'] }}</td>
                                    <td>{{ $c['faculty'] }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" style="text-align: center; color: #888;">No complaints recorded.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </td>
        </tr>
    </table>

    <!-- ROW 3: Result Details | Achievements -->
    <table class="two-col-grid">
        <tr>
            <!-- Left: Result Details -->
            <td>
                <div class="report-card">
                    <div class="section-title">Result Details</div>
                    <table class="report-table">
                        <thead>
                            <tr>
                                <th style="width: 8%;">No</th>
                                <th>Semester / Exam</th>
                                <th>Total</th>
                                <th>Obtained</th>
                                <th>%</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($results as $idx => $r)
                                <tr>
                                    <td>{{ $idx + 1 }}</td>
                                    <td>{{ $r['semester'] }}</td>
                                    <td>{{ $r['total_marks'] }}</td>
                                    <td>{{ $r['obtained_marks'] }}</td>
                                    <td><strong>{{ $r['percentage'] }}</strong></td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" style="text-align: center; color: #888;">No result records available.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </td>

            <!-- Right: Achievements -->
            <td>
                <div class="report-card">
                    <div class="section-title">Achievements</div>
                    <table class="report-table">
                        <thead>
                            <tr>
                                <th style="width: 8%;">No</th>
                                <th>Event Name</th>
                                <th>Rank</th>
                                <th>Date</th>
                                <th>Remark</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($achievements as $idx => $ach)
                                <tr>
                                    <td>{{ $idx + 1 }}</td>
                                    <td>{{ $ach['event'] }}</td>
                                    <td><span class="badge badge-warning">{{ $ach['rank'] }}</span></td>
                                    <td>{{ $ach['date'] }}</td>
                                    <td>{{ $ach['remark'] }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" style="text-align: center; color: #888;">No achievements recorded.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </td>
        </tr>
    </table>

</body>
</html>
