<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student Report - {{ $personal['name'] }}</title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            color: #333333;
            font-size: 10.5px;
            line-height: 1.25;
        }

        /* Header */
        .print-header {
            border-bottom: 2px solid #222222;
            padding-bottom: 6px;
            margin-bottom: 8px;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
        }
        .header-table td {
            border: none;
            padding: 0;
            vertical-align: middle;
        }
        .college-name {
            font-size: 15px;
            font-weight: bold;
            color: #111111;
            text-transform: uppercase;
        }
        .trust-name {
            font-size: 12px;
            color: #555555;
        }
        .college-info {
            font-size: 10.5px;
            color: #666666;
        }
        .report-title {
            text-align: center;
            font-size: 13px;
            font-weight: bold;
            margin-top: 6px;
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #111111;
        }

        /* Report Section Block (Prevents orphan titles & keeps sections intact) */
        .report-section {
            page-break-inside: avoid;
            break-inside: avoid;
            margin-bottom: 6px;
        }

        /* Details Section */
        .details-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 4px;
        }
        .details-table td {
            border: none;
            padding: 3px 5px;
            vertical-align: top;
            font-size: 10.5px;
        }
        .details-table td.label {
            font-weight: bold;
            width: 12%;
            color: #555555;
        }
        .details-table td.value {
            color: #111111;
        }
        .student-photo-cell {
            width: 80px;
            text-align: right;
            vertical-align: top;
        }
        .student-photo {
            width: 70px;
            height: 70px;
            border: 1px solid #cccccc;
            border-radius: 4px;
        }

        /* Section Titles */
        .section-title {
            font-size: 10.5px;
            font-weight: bold;
            text-transform: uppercase;
            background-color: #f1f1f1;
            padding: 4px 7px;
            margin-top: 4px;
            margin-bottom: 4px;
            border-left: 3px solid #333333;
            letter-spacing: 0.5px;
            color: #222222;
            page-break-after: avoid;
            break-after: avoid;
        }

        /* Report Tables */
        .report-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 4px;
            page-break-inside: avoid;
            break-inside: avoid;
        }
        .report-table th, .report-table td {
            border: 1px solid #dddddd;
            padding: 4px 6px;
            text-align: left;
            font-size: 10px;
        }
        .report-table th {
            background-color: #f7f7f7;
            font-weight: bold;
            color: #333333;
        }
        .report-table tr:nth-child(even) td {
            background-color: #fafafa;
        }

        /* Badges */
        .badge {
            display: inline-block;
            padding: 1px 4px;
            font-size: 9px;
            font-weight: bold;
            border-radius: 3px;
            text-transform: uppercase;
        }
        .badge-success { background-color: #e2f5ec; color: #1f7a52; border: 1px solid #b8ebcf; }
        .badge-danger { background-color: #fbeae9; color: #b32d24; border: 1px solid #f6c8c5; }
        .badge-warning { background-color: #fff4e5; color: #b36b00; border: 1px solid #ffe0b3; }
        .badge-info { background-color: #e5f6fd; color: #0077b3; border: 1px solid #b3e5fc; }
    </style>
</head>
<body>

    <!-- College Header -->
    <div class="print-header">
        <table class="header-table">
            <tr>
                <td style="width: 75px; text-align: left;">
                    @if(!empty($logo_src))
                        <img src="{{ $logo_src }}" alt="Logo" style="width: 65px; height: auto;">
                    @endif
                </td>
                <td style="text-align: center; line-height: 1.4;">
                    <span class="trust-name">Shree Patel Vidhyarthi Ashram Sanchalit</span><br>
                    <strong class="college-name">Shree Patel Vidhya Mandir Science College</strong><br>
                    <span class="college-info">Veraval Road, Keshod - 362 220. Mo. 96874 51774</span>
                </td>
                <td style="width: 75px;"></td>
            </tr>
        </table>
    </div>

    <div class="report-title">
        Student Report
    </div>

    <!-- Personal & Academic Details Section -->
    <div class="report-section">
        <div class="section-title">Personal Details</div>
        <table class="details-table">
            <tr>
                <td class="label" style="width: 14%;">GR No. :</td>
                <td class="value" style="width: 56%;">{{ $personal['gr_no'] }}</td>
                <td class="student-photo-cell" rowspan="2" style="width: 30%;">
                    @if(!empty($student_photo_src))
                        <img class="student-photo" src="{{ $student_photo_src }}" alt="Student Photo">
                    @endif
                </td>
            </tr>
            <tr>
                <td class="label">Name :</td>
                <td class="value">{{ $personal['name'] }}</td>
            </tr>
        </table>

        <div class="section-title" style="margin-top: 4px;">Academic Details</div>
        <table class="details-table">
            <tr>
                <td class="label" style="width: 10%;">Batch :</td>
                <td class="value" style="width: 22%;">{{ $academic['batch'] }}</td>
                <td class="label" style="width: 10%;">Class :</td>
                <td class="value" style="width: 22%;">{{ $academic['class'] }}</td>
                <td class="label" style="width: 14%;">Semester :</td>
                <td class="value" style="width: 22%;">{{ $academic['semester'] }}</td>
            </tr>
        </table>
    </div>

    <!-- 1. Fees Section -->
    <div class="report-section">
        <div class="section-title">Fees Details</div>
        <table class="report-table">
            <thead>
                <tr>
                    <th style="width: 8%;">No.</th>
                    <th>Semester</th>
                    <th>Total Fees</th>
                    <th>Paid Fees</th>
                </tr>
            </thead>
            <tbody>
                @forelse($fees as $index => $fee)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $fee['semester'] }}</td>
                        <td>{{ $fee['total_fees'] }}</td>
                        <td>{{ $fee['paid_fees'] }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" style="text-align: center; color: #888;">No fees records available.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- 2. Attendance Section -->
    <div class="report-section">
        <div class="section-title">Attendance Details</div>
        <table class="report-table">
            <thead>
                <tr>
                    <th style="width: 8%;">No.</th>
                    <th>Month</th>
                    <th>Total Working Days</th>
                    <th>Present Days</th>
                    <th>Absent Days</th>
                </tr>
            </thead>
            <tbody>
                @forelse($attendance as $index => $att)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $att['month'] }}</td>
                        <td>{{ $att['total_days'] }}</td>
                        <td>{{ $att['present_days'] }}</td>
                        <td>{{ $att['absent_days'] }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" style="text-align: center; color: #888;">No attendance records available.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- 3. Test Section -->
    <div class="report-section">
        <div class="section-title">Test Details</div>
        <table class="report-table">
            <thead>
                <tr>
                    <th style="width: 8%;">No.</th>
                    <th>Subject</th>
                    <th>Date</th>
                    <th>Total Marks</th>
                    <th>Obtained Marks</th>
                </tr>
            </thead>
            <tbody>
                @forelse($tests as $index => $test)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $test['subject'] }}</td>
                        <td>{{ $test['date'] }}</td>
                        <td>{{ $test['total_marks'] }}</td>
                        <td>{{ $test['obtained_marks'] }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" style="text-align: center; color: #888;">No tests recorded for this semester.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- 4. Assignments Section -->
    <div class="report-section">
        <div class="section-title">Assignments</div>
        <table class="report-table">
            <thead>
                <tr>
                    <th style="width: 8%;">No.</th>
                    <th>Subject</th>
                    <th>Date</th>
                    <th>Status</th>
                    <th>Remarks</th>
                </tr>
            </thead>
            <tbody>
                @forelse($assignments as $index => $assign)
                    @php
                        $statusLower = strtolower($assign['status']);
                        $badgeClass = 'badge-warning';
                        if ($statusLower === 'complete' || $statusLower === 'submitted' || $statusLower === 'approved') {
                            $badgeClass = 'badge-success';
                        } elseif ($statusLower === 'incomplete' || $statusLower === 'rejected') {
                            $badgeClass = 'badge-danger';
                        }
                    @endphp
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $assign['subject'] }}</td>
                        <td>{{ $assign['date'] }}</td>
                        <td><span class="badge {{ $badgeClass }}">{{ ucfirst($assign['status']) }}</span></td>
                        <td>{{ $assign['remarks'] }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" style="text-align: center; color: #888;">No assignments assigned.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- 5. Assessments Section -->
    <div class="report-section">
        <div class="section-title">Assessments</div>
        <table class="report-table">
            <thead>
                <tr>
                    <th style="width: 8%;">No.</th>
                    <th>Subject</th>
                    <th>Performance</th>
                    <th>Remarks</th>
                </tr>
            </thead>
            <tbody>
                @forelse($assessments as $index => $assess)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $assess['subject'] }}</td>
                        <td>{{ $assess['performance'] }}</td>
                        <td>{{ $assess['remarks'] }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" style="text-align: center; color: #888;">No assessments recorded.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Result Section -->
    <div class="report-section">
        <div class="section-title">Result Details</div>
        <table class="report-table">
            <thead>
                <tr>
                    <th style="width: 8%;">No.</th>
                    <th>Semester / Exam</th>
                    <th>Total Marks</th>
                    <th>Obtained Marks</th>
                    <th>Percentage</th>
                </tr>
            </thead>
            <tbody>
                @forelse($results as $index => $res)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $res['semester'] }}</td>
                        <td>{{ $res['total_marks'] }}</td>
                        <td>{{ $res['obtained_marks'] }}</td>
                        <td>{{ $res['percentage'] }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" style="text-align: center; color: #888;">No result records available.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- 6. Achievements Section -->
    <div class="report-section">
        <div class="section-title">Achievements</div>
        <table class="report-table">
            <thead>
                <tr>
                    <th style="width: 8%;">No.</th>
                    <th>Event Name</th>
                    <th>Rank</th>
                    <th>Date</th>
                    <th>Remark</th>
                </tr>
            </thead>
            <tbody>
                @forelse($achievements as $index => $ach)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $ach['event'] }}</td>
                        <td>{{ $ach['rank'] }}</td>
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

    <!-- 7. Complaints Section -->
    <div class="report-section">
        <div class="section-title">Complaints</div>
        <table class="report-table">
            <thead>
                <tr>
                    <th style="width: 8%;">No.</th>
                    <th>Complaint</th>
                    <th>Date</th>
                    <th>Faculty</th>
                </tr>
            </thead>
            <tbody>
                @forelse($complaints as $index => $comp)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $comp['complaint'] }}</td>
                        <td>{{ $comp['date'] }}</td>
                        <td>{{ $comp['faculty'] }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" style="text-align: center; color: #888;">No complaints recorded.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

</body>
</html>
