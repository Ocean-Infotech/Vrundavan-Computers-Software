<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student Report - {{ $personal['name'] }}</title>
    <style>
        body {
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            margin: 20px;
            color: #333;
            font-size: 13px;
            line-height: 1.4;
        }
        .print-header {
            border-bottom: 3px double #333;
            padding-bottom: 15px;
            margin-bottom: 20px;
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
            font-size: 20px;
            font-weight: bold;
            color: #111;
            text-transform: uppercase;
        }
        .trust-name {
            font-size: 19px;
            color: #555;
        }
        .college-info {
            font-size: 14px;
            color: #666;
        }
        .report-title {
            text-align: center;
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 20px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .report-section {
            page-break-inside: avoid;
            break-inside: avoid;
            margin-bottom: 15px;
        }

        /* Details Section */
        .details-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        .details-table td {
            border: none;
            padding: 6px 10px;
            vertical-align: top;
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
            width: 110px;
            text-align: right;
            vertical-align: top;
        }
        .student-photo {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        /* Content Tables */
        .section-title {
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
            background-color: #f1f1f1;
            padding: 6px 10px;
            margin-top: 15px;
            margin-bottom: 8px;
            border-left: 3px solid #333;
            letter-spacing: 0.5px;
            page-break-after: avoid;
            break-after: avoid;
        }
        .report-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
            page-break-inside: avoid;
            break-inside: avoid;
        }
        .report-table th, .report-table td {
            border: 1px solid #ddd;
            padding: 8px 10px;
            text-align: left;
        }
        .report-table th {
            background-color: #fafafa;
            font-weight: bold;
            color: #444;
        }
        .report-table tr:nth-child(even) td {
            background-color: #fcfcfc;
        }
        .badge {
            display: inline-block;
            padding: 2px 6px;
            font-size: 11px;
            font-weight: bold;
            border-radius: 3px;
            text-transform: uppercase;
        }
        .badge-success { background-color: #e2f5ec; color: #1f7a52; border: 1px solid #b8ebcf; }
        .badge-danger { background-color: #fbeae9; color: #b32d24; border: 1px solid #f6c8c5; }
        .badge-warning { background-color: #fff4e5; color: #b36b00; border: 1px solid #ffe0b3; }
        .badge-info { background-color: #e5f6fd; color: #0077b3; border: 1px solid #b3e5fc; }

        .no-print-bar {
            background: #f5f5f5;
            padding: 10px 20px;
            border-bottom: 1px solid #ddd;
            margin-bottom: 20px;
            text-align: right;
        }
        .btn {
            padding: 6px 12px;
            font-size: 13px;
            font-weight: 600;
            border-radius: 4px;
            cursor: pointer;
            border: 1px solid #ccc;
            background: #fff;
            margin-left: 5px;
        }
        .btn-primary {
            background: #333;
            color: #fff;
            border-color: #333;
        }
        .btn-primary:hover {
            background: #111;
        }

        @media print {
            body {
                margin: 10px;
            }
            .no-print-bar {
                display: none;
            }
            .report-section {
                page-break-inside: avoid;
                break-inside: avoid;
            }
            .report-table {
                page-break-inside: avoid;
                break-inside: avoid;
            }
            .report-table th {
                background-color: #f1f1f1 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            .section-title {
                background-color: #f1f1f1 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
                page-break-after: avoid;
                break-after: avoid;
            }
            .badge-success { background-color: #e2f5ec !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .badge-danger { background-color: #fbeae9 !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .badge-warning { background-color: #fff4e5 !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .badge-info { background-color: #e5f6fd !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            
            .page-break {
                page-break-before: always;
            }
        }
    </style>
</head>
<body>

    <div class="no-print-bar">
        <button class="btn btn-primary" onclick="window.print()">Print Document</button>
        <button class="btn" onclick="window.close()">Close Window</button>
    </div>

    <!-- College Header -->
    <div class="print-header">
        <table>
            <tr>
                <td style="width: 90px; text-align: left; vertical-align: middle;">
                    <img src="{{ asset('uploads/logo/logo.png') }}" alt="College Logo" style="width: 80px; height: auto;">
                </td>
                <td style="text-align: center; vertical-align: middle; line-height: 1.5;">
                    <span class="trust-name">Shree Patel Vidhyarthi Ashram Sanchalit</span><br>
                    <strong class="college-name">Shree Patel Vidhya Mandir Science College</strong><br>
                    <span class="college-info">Veraval Road, Keshod - 362 220. Mo. 96874 51774</span>
                </td>
                <td style="width: 90px;"></td> <!-- balancer -->
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
                <td class="label" style="width: 15%;">GR No. :</td>
                <td class="value" style="width: 55%;">{{ $personal['gr_no'] }}</td>
                <td class="student-photo-cell" rowspan="2" style="width: 30%; text-align: right;">
                    <img class="student-photo" src="{{ $personal['photo'] }}" alt="Student Photo">
                </td>
            </tr>
            <tr>
                <td class="label">Name :</td>
                <td class="value">{{ $personal['name'] }}</td>
            </tr>
        </table>

        <div class="section-title" style="margin-top: 10px;">Academic Details</div>
        <table class="details-table">
            <tr>
                <td class="label" style="width: 10%;">Batch :</td>
                <td class="value" style="width: 20%;">{{ $academic['batch'] }}</td>
                <td class="label" style="width: 10%;">Class :</td>
                <td class="value" style="width: 20%;">{{ $academic['class'] }}</td>
                <td class="label" style="width: 15%;">Semester :</td>
                <td class="value" style="width: 25%;">{{ $academic['semester'] }}</td>
            </tr>
        </table>
    </div>

    <!-- 1. Fees Section -->
    <div class="report-section">
        <div class="section-title">Fees Details</div>
        <table class="report-table">
            <thead>
                <tr>
                    <th style="width: 10%;">No.</th>
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
                    <th style="width: 10%;">No.</th>
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
                    <th style="width: 10%;">No.</th>
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
                    <th style="width: 10%;">No.</th>
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
                    <th style="width: 10%;">No.</th>
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
                    <th style="width: 10%;">No.</th>
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
                    <th style="width: 10%;">No.</th>
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
                    <th style="width: 10%;">No.</th>
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

    <script>
        // Trigger print dialog automatically when document loads
        window.addEventListener('DOMContentLoaded', () => {
            // setTimeout to ensure layout rendering
            setTimeout(() => {
                window.print();
            }, 500);
        });
    </script>
</body>
</html>
