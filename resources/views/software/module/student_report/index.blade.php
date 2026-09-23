@extends('software.layout.app')

@php
    $page_title = isset($modules['title']) ? $modules['title'] : 'Student Report';
    $route = isset($modules['route']) ? $modules['route'] : 'student_report';
@endphp

@section('title', $page_title)

@section('page_style_file')
    <style>
        /* Nav Pills Styling */
        .nav-custom-pills .nav-link {
            border-radius: 8px;
            font-size: 0.95rem;
            color: #555;
            background: #f8f9fa;
            border: 1px solid #e2e8f0;
            margin-right: 10px;
            transition: all 0.2s ease-in-out;
        }
        .nav-custom-pills .nav-link:hover {
            background: #edf2f7;
            color: #333;
        }
        .nav-custom-pills .nav-link.active {
            background: #5c1ac3 !important;
            color: #fff !important;
            border-color: #5c1ac3 !important;
            box-shadow: 0 4px 10px rgba(92, 26, 195, 0.25);
        }

        /* Card & Report Styles */
        .report-section {
            margin-bottom: 2rem;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            padding: 1.5rem;
            border: 1px solid #eef2f6;
        }
        .section-header {
            font-size: 1.1rem;
            font-weight: 700;
            color: #3b3f5c;
            border-bottom: 2px solid #5c1ac3;
            padding-bottom: 0.5rem;
            margin-bottom: 1.2rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .report-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 0.5rem;
        }
        .report-table th {
            background-color: #f8f9fa;
            color: #4b4b4b;
            font-weight: 600;
            text-align: left;
            padding: 10px 15px;
            border-bottom: 2px solid #dee2e6;
            font-size: 0.85rem;
        }
        .report-table td {
            padding: 10px 15px;
            border-bottom: 1px solid #dee2e6;
            color: #5a5a5a;
            font-size: 0.85rem;
        }
        .profile-img-container img {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 8px;
            border: 2px solid #e9ecef;
        }

        /* Printable area styles */
        @media print {
            body * {
                visibility: hidden;
            }
            #printable-area, #printable-area * {
                visibility: visible;
            }
            #printable-area {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
                background: #fff !important;
                color: #000 !important;
            }
            .no-print {
                display: none !important;
            }
            .report-section {
                box-shadow: none !important;
                border: 1px solid #000 !important;
                padding: 0.75rem !important;
                margin-bottom: 0.5rem !important;
                page-break-inside: avoid;
            }
            .section-header {
                border-bottom: 2px solid #000 !important;
                color: #000 !important;
                margin-bottom: 0.5rem !important;
                padding-bottom: 0.25rem !important;
            }
            .report-table th {
                background-color: #f1f1f1 !important;
                border-bottom: 2px solid #000 !important;
                color: #000 !important;
            }
            .report-table td {
                border-bottom: 1px solid #ccc !important;
                color: #000 !important;
            }
        }
    </style>
@endsection

@section('content')
    <!-- Top Header -->
    <div class="d-flex justify-content-between align-items-center mb-3 no-print">
        <h4 class="mb-0 fw-bold">{{ $page_title }}</h4>
    </div>

    <!-- Navigation Tabs (Navbar for Single vs Bulk) -->
    <div class="card mb-4 no-print shadow-sm border-0">
        <div class="card-body p-2 bg-white rounded">
            <ul class="nav nav-pills nav-custom-pills" id="studentReportTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active fw-bold py-2 px-4" id="single-report-tab" data-bs-toggle="pill" data-bs-target="#single-report-content" type="button" role="tab" aria-controls="single-report-content" aria-selected="true">
                        <i class="bx bx-user me-1"></i> Single Student Report
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link fw-bold py-2 px-4" id="bulk-report-tab" data-bs-toggle="pill" data-bs-target="#bulk-report-content" type="button" role="tab" aria-controls="bulk-report-content" aria-selected="false">
                        <i class="bx bxs-file-archive me-1"></i> Bulk Student Reports (Class / Batch)
                    </button>
                </li>
            </ul>
        </div>
    </div>

    <!-- Tab Contents -->
    <div class="tab-content" id="studentReportTabContent">
        
        <!-- ================= TAB 1: SINGLE STUDENT REPORT ================= -->
        <div class="tab-pane fade show active" id="single-report-content" role="tabpanel" aria-labelledby="single-report-tab">
            
            <!-- Search & Action Bar -->
            <div class="card mb-4 no-print shadow-sm border-0">
                <div class="card-body">
                    <div class="row align-items-end justify-content-between g-3">
                        <div class="col-md-5 col-sm-12">
                            <label class="form-label fw-bold">Search Student</label>
                            <input list="search" id="search_id" name="search"
                                class="form-control"
                                placeholder="Type Student ID or Name to search...">
                            <datalist id="search" class="search_by_courceregistration" data-append="search_by_courceregistration"
                                data-selectedCourceRegistrationId="">
                            </datalist>
                        </div>
                        <div class="col-md-5 col-sm-12 text-md-end">
                            <button id="download-pdf-btn" class="btn btn-danger me-2 shadow-sm">
                                <i class="bx bxs-file-pdf me-1"></i> Download PDF
                            </button>
                            <button id="print-report-btn" class="btn btn-primary shadow-sm">
                                <i class="bx bx-printer me-1"></i> Print Report
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Loading Spinner -->
            <div id="loading-spinner" class="text-center my-5" style="display: none;">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-2 text-muted">Compiling Student Report details...</p>
            </div>

            <!-- Empty State -->
            <div id="empty-state" class="card text-center py-5 no-print shadow-sm border-0">
                <div class="card-body">
                    <i class="bx bx-user-circle text-muted" style="font-size: 4rem;"></i>
                    <h5 class="mt-3 fw-bold">No Student Selected</h5>
                    <p class="text-muted">Please search and select a student above to view their academic and performance report.</p>
                </div>
            </div>

            <!-- Printable Report Container -->
            <div id="printable-area" style="display: none;">

                <div class="text-center mb-4">
                    <h3 class="fw-bold mb-2" id="report-title">Student Report (Current Semester)</h3>
                </div>

                <div class="row">
                    <!-- Personal and Academic Details -->
                    <div class="col-md-12">
                        <div class="report-section">
                            <div class="section-header">Personal Details</div>
                            <div class="row align-items-center">
                                <div class="col-md-2 col-3 text-center profile-img-container">
                                    <img id="student-photo" src="{{ asset('admin/assets/images/user.png') }}" alt="Student Photo">
                                </div>
                                <div class="col-md-10 col-9">
                                    <table class="w-100">
                                        <tr>
                                            <td class="fw-bold w-25 py-2">Gr No. :</td>
                                            <td id="student-gr-no" class="py-2">-</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold w-25 py-2">Name :</td>
                                            <td id="student-name" class="py-2">-</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="report-section">
                            <div class="section-header">Academic Details</div>
                            <div class="row">
                                <div class="col-md-4 mb-2">
                                    <strong>Batch:</strong> <span id="academic-batch">-</span>
                                </div>
                                <div class="col-md-4 mb-2">
                                    <strong>Class:</strong> <span id="academic-class">-</span>
                                </div>
                                <div class="col-md-4 mb-2">
                                    <strong>Semester:</strong> <span id="academic-semester">-</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section: Fees Details -->
                <div class="report-section">
                    <div class="section-header">Fees Details (All Semesters)</div>
                    <div class="table-responsive">
                        <table class="report-table">
                            <thead>
                                <tr>
                                    <th style="width: 10%;">No</th>
                                    <th>Semester</th>
                                    <th>Total Fees</th>
                                    <th>Paid Fees</th>
                                </tr>
                            </thead>
                            <tbody id="fees-tbody">
                                <tr>
                                    <td colspan="4" class="text-center text-muted">No fees records available.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Section: Attendance Details -->
                <div class="report-section">
                    <div class="section-header">Attendance Details (Current Semester)</div>
                    <div class="table-responsive">
                        <table class="report-table">
                            <thead>
                                <tr>
                                    <th style="width: 10%;">No</th>
                                    <th>Month</th>
                                    <th>Total Days</th>
                                    <th>Present Days</th>
                                    <th>Absent Days</th>
                                </tr>
                            </thead>
                            <tbody id="attendance-tbody">
                                <tr>
                                    <td colspan="5" class="text-center text-muted">No attendance records available.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Section: Test Details -->
                <div class="report-section">
                    <div class="section-header">Test Details (Current Semester)</div>
                    <div class="table-responsive">
                        <table class="report-table">
                            <thead>
                                <tr>
                                    <th style="width: 10%;">No</th>
                                    <th>Subject</th>
                                    <th>Date</th>
                                    <th>Total Marks</th>
                                    <th>Obtained Marks</th>
                                </tr>
                            </thead>
                            <tbody id="tests-tbody">
                                <tr>
                                    <td colspan="5" class="text-center text-muted">No tests recorded for this semester.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Section: Complaints -->
                <div class="report-section">
                    <div class="section-header">Complaints</div>
                    <div class="table-responsive">
                        <table class="report-table">
                            <thead>
                                <tr>
                                    <th style="width: 10%;">No</th>
                                    <th>Complaint</th>
                                    <th>Date</th>
                                    <th>Faculty</th>
                                </tr>
                            </thead>
                            <tbody id="complaints-tbody">
                                <tr>
                                    <td colspan="4" class="text-center text-muted">No complaints recorded.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Section: Assignments -->
                <div class="report-section">
                    <div class="section-header">Assignments</div>
                    <div class="table-responsive">
                        <table class="report-table">
                            <thead>
                                <tr>
                                    <th style="width: 10%;">No</th>
                                    <th>Subject</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                    <th>Remarks</th>
                                </tr>
                            </thead>
                            <tbody id="assignments-tbody">
                                <tr>
                                    <td colspan="5" class="text-center text-muted">No assignments assigned.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Section: Assessments -->
                <div class="report-section">
                    <div class="section-header">Assessments</div>
                    <div class="table-responsive">
                        <table class="report-table">
                            <thead>
                                <tr>
                                    <th style="width: 10%;">No</th>
                                    <th>Subject</th>
                                    <th>Performance</th>
                                    <th>Remarks</th>
                                </tr>
                            </thead>
                            <tbody id="assessments-tbody">
                                <tr>
                                    <td colspan="4" class="text-center text-muted">No assessments recorded.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Section: Result Details -->
                <div class="report-section">
                    <div class="section-header">Result Details (All Semesters)</div>
                    <div class="table-responsive">
                        <table class="report-table">
                            <thead>
                                <tr>
                                    <th style="width: 10%;">No</th>
                                    <th>Semester / Exam</th>
                                    <th>Total Marks</th>
                                    <th>Obtained Marks</th>
                                    <th>Percentage</th>
                                </tr>
                            </thead>
                            <tbody id="results-tbody">
                                <tr>
                                    <td colspan="5" class="text-center text-muted">No result records available.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Section: Achievements -->
                <div class="report-section">
                    <div class="section-header">Achievements</div>
                    <div class="table-responsive">
                        <table class="report-table">
                            <thead>
                                <tr>
                                    <th style="width: 10%;">No</th>
                                    <th>Event Name</th>
                                    <th>Rank</th>
                                    <th>Date</th>
                                    <th>Remark</th>
                                </tr>
                            </thead>
                            <tbody id="achievements-tbody">
                                <tr>
                                    <td colspan="5" class="text-center text-muted">No achievements recorded.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>

        <!-- ================= TAB 2: BULK STUDENT REPORTS (CLASS / BATCH) ================= -->
        <div class="tab-pane fade" id="bulk-report-content" role="tabpanel" aria-labelledby="bulk-report-tab">
            
            <!-- Filters Card -->
            <div class="card mb-4 shadow-sm border-0">
                <div class="card-header bg-white py-3 border-bottom">
                    <h5 class="card-title mb-0 fw-bold text-primary">
                        <i class="bx bx-filter-alt me-1"></i> Filter Students by Course, Batch & Class
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row align-items-end g-3">
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Course</label>
                            <select id="bulk_course_id" class="form-control search_by_course select2" data-append="search_by_course">
                                <option value="">Select Course</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Batch</label>
                            <select id="bulk_batch_id" class="form-control search_by_batch select2" data-append="search_by_batch">
                                <option value="">Select Batch</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Class</label>
                            <select id="bulk_class_id" class="form-control search_by_class select2" data-append="search_by_class">
                                <option value="">Select Class</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <button type="button" id="btn-filter-bulk-students" class="btn btn-primary w-100 shadow-sm">
                                <i class="bx bx-search me-1"></i> Search Students
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Student Selection & Download Card -->
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div class="fw-bold">
                        Total Students: <span id="bulk-total-count" class="badge bg-primary fs-6">0</span> | 
                        Selected: <span id="bulk-selected-count" class="badge bg-success fs-6">0</span>
                    </div>
                    <div class="d-flex align-items-center gap-3">
                        <button type="button" id="btn-start-bulk-download" class="btn btn-danger shadow-sm">
                            <i class="bx bxs-file-archive me-1"></i> Download Selected (ZIP)
                        </button>
                        <div class="form-check mb-0">
                            <input class="form-check-input" type="checkbox" id="bulk-select-all" style="cursor: pointer; width: 18px; height: 18px;">
                            <label class="form-check-label fw-bold ms-1" for="bulk-select-all" style="cursor: pointer;">Select All Students</label>
                        </div>
                    </div>
                </div>

                <div class="card-body p-0">
                    <div class="table-responsive" style="max-height: 480px; overflow-y: auto;">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light sticky-top" style="position: sticky; top: 0; z-index: 10;">
                                <tr>
                                    <th style="width: 50px; text-align: center;">#</th>
                                    <th style="width: 120px;">GR No</th>
                                    <th>Student Name</th>
                                    <th>Course</th>
                                    <th>Batch</th>
                                    <th>Class</th>
                                </tr>
                            </thead>
                            <tbody id="bulk-students-tbody">
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-muted">
                                        <i class="bx bx-filter-alt text-primary" style="font-size: 3rem;"></i>
                                        <p class="mt-2 mb-0 fw-semibold">Please select Course and Batch to load students.</p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>

    </div>

    <!-- Attendance Detail Modal -->
    <div class="modal fade no-print" id="attendanceDetailModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title fw-bold text-white" id="attendanceModalTitle">Monthly Attendance Details</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead class="table-light">
                                <tr>
                                    <th>Date</th>
                                    <th>Day</th>
                                    <th>In Time</th>
                                    <th>Out Time</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody id="attendance-modal-tbody">
                                <!-- Dynamic daily records -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('page_script_file')
    @include('software.utils.getCourceRegistration')
    @include('software.utils.getCourse')
    @include('software.utils.getBatchByCourseid')
    @include('software.utils.getClassByBatch')
    
    <script>
        $(document).ready(function() {
            let searchDebounceTimer;
            let currentAttendanceData = [];
            let selectedStudentId = null;

            // ================= 1. SINGLE STUDENT REPORT JS =================
            $('#search_id').on('input change', function() {
                let selectedVal = $(this).val();
                clearTimeout(searchDebounceTimer);

                searchDebounceTimer = setTimeout(function() {
                    if (selectedVal) {
                        let optionExists = false;
                        $('#search option').each(function() {
                            if ($(this).val() == selectedVal) {
                                optionExists = true;
                                return false;
                            }
                        });

                        if (optionExists) {
                            fetchStudentReport(selectedVal);
                        }
                    } else {
                        resetReport();
                    }
                }, 500);
            });

            function fetchStudentReport(studentId) {
                $('#empty-state').hide();
                $('#printable-area').hide();
                $('#loading-spinner').show();

                $.ajax({
                    url: "{{ route('student_report.data') }}",
                    type: "GET",
                    data: { student_id: studentId },
                    success: function(response) {
                        $('#loading-spinner').hide();
                        
                        if (response.success) {
                            currentAttendanceData = response.attendance;
                            selectedStudentId = studentId;

                            $('#student-photo').attr('src', response.personal.photo);
                            $('#student-gr-no').text(response.personal.gr_no);
                            $('#student-name').text(response.personal.name);

                            $('#academic-batch').text(response.academic.batch);
                            $('#academic-class').text(response.academic.class);
                            $('#academic-semester').text(response.academic.semester);
                            $('#report-title').text('Student Report (' + response.academic.semester + ')');

                            // Fees
                            let feesHtml = '';
                            if (response.fees.length > 0) {
                                response.fees.forEach(function(fee, index) {
                                    feesHtml += `<tr>
                                        <td>${index + 1}</td>
                                        <td>${fee.semester}</td>
                                        <td>${fee.total_fees}</td>
                                        <td>${fee.paid_fees}</td>
                                    </tr>`;
                                });
                            } else {
                                feesHtml = '<tr><td colspan="4" class="text-center text-muted">No fees records available.</td></tr>';
                            }
                            $('#fees-tbody').html(feesHtml);

                            // Results
                            let resultsHtml = '';
                            if (response.results && response.results.length > 0) {
                                response.results.forEach(function(res, index) {
                                    resultsHtml += `<tr>
                                        <td>${index + 1}</td>
                                        <td>${res.semester}</td>
                                        <td>${res.total_marks}</td>
                                        <td>${res.obtained_marks}</td>
                                        <td>${res.percentage}</td>
                                    </tr>`;
                                });
                            } else {
                                resultsHtml = '<tr><td colspan="5" class="text-center text-muted">No result records available.</td></tr>';
                            }
                            $('#results-tbody').html(resultsHtml);

                            // Attendance
                            let attendanceHtml = '';
                            if (response.attendance.length > 0) {
                                response.attendance.forEach(function(att, index) {
                                    attendanceHtml += `<tr>
                                        <td>${index + 1}</td>
                                        <td><a href="javascript:void(0)" class="view-daily-attendance text-primary fw-bold" data-index="${index}">${att.month}</a></td>
                                        <td>${att.total_days}</td>
                                        <td>${att.present_days}</td>
                                        <td>${att.absent_days}</td>
                                    </tr>`;
                                });
                            } else {
                                attendanceHtml = '<tr><td colspan="5" class="text-center text-muted">No attendance records available.</td></tr>';
                            }
                            $('#attendance-tbody').html(attendanceHtml);

                            // Tests
                            let testsHtml = '';
                            if (response.tests.length > 0) {
                                response.tests.forEach(function(test, index) {
                                    testsHtml += `<tr>
                                        <td>${index + 1}</td>
                                        <td>${test.subject}</td>
                                        <td>${test.date}</td>
                                        <td>${test.total_marks}</td>
                                        <td>${test.obtained_marks}</td>
                                    </tr>`;
                                });
                            } else {
                                testsHtml = '<tr><td colspan="5" class="text-center text-muted">No tests recorded for this semester.</td></tr>';
                            }
                            $('#tests-tbody').html(testsHtml);

                            // Complaints
                            let complaintsHtml = '';
                            if (response.complaints.length > 0) {
                                response.complaints.forEach(function(comp, index) {
                                    complaintsHtml += `<tr>
                                        <td>${index + 1}</td>
                                        <td>${comp.complaint}</td>
                                        <td>${comp.date}</td>
                                        <td>${comp.faculty}</td>
                                    </tr>`;
                                });
                            } else {
                                complaintsHtml = '<tr><td colspan="4" class="text-center text-muted">No complaints recorded.</td></tr>';
                            }
                            $('#complaints-tbody').html(complaintsHtml);

                            // Assignments
                            let assignmentsHtml = '';
                            if (response.assignments.length > 0) {
                                response.assignments.forEach(function(assign, index) {
                                    let badgeClass = 'bg-warning';
                                    if (assign.status.toLowerCase() === 'complete' || assign.status.toLowerCase() === 'submitted') {
                                        badgeClass = 'bg-success';
                                    } else if (assign.status.toLowerCase() === 'incomplete') {
                                        badgeClass = 'bg-danger';
                                    } else if (assign.status.toLowerCase() === 'pending') {
                                        badgeClass = 'bg-warning';
                                    }
                                    let statusLabel = assign.status.charAt(0).toUpperCase() + assign.status.slice(1);
                                    assignmentsHtml += `<tr>
                                         <td>${index + 1}</td>
                                         <td>${assign.subject}</td>
                                         <td>${assign.date}</td>
                                         <td><span class="badge ${badgeClass}">${statusLabel}</span></td>
                                         <td>${assign.remarks}</td>
                                     </tr>`;
                                });
                            } else {
                                assignmentsHtml = '<tr><td colspan="5" class="text-center text-muted">No assignments assigned.</td></tr>';
                            }
                            $('#assignments-tbody').html(assignmentsHtml);

                            // Assessments
                            let assessmentsHtml = '';
                            if (response.assessments.length > 0) {
                                response.assessments.forEach(function(assess, index) {
                                    assessmentsHtml += `<tr>
                                        <td>${index + 1}</td>
                                        <td>${assess.subject}</td>
                                        <td>${assess.performance}</td>
                                        <td>${assess.remarks}</td>
                                    </tr>`;
                                });
                            } else {
                                assessmentsHtml = '<tr><td colspan="4" class="text-center text-muted">No assessments recorded.</td></tr>';
                            }
                            $('#assessments-tbody').html(assessmentsHtml);

                            // Achievements
                            let achievementsHtml = '';
                            if (response.achievements.length > 0) {
                                response.achievements.forEach(function(ach, index) {
                                    achievementsHtml += `<tr>
                                        <td>${index + 1}</td>
                                        <td>${ach.event}</td>
                                        <td>${ach.rank}</td>
                                        <td>${ach.date}</td>
                                        <td>${ach.remark}</td>
                                    </tr>`;
                                });
                            } else {
                                achievementsHtml = '<tr><td colspan="5" class="text-center text-muted">No achievements recorded.</td></tr>';
                            }
                            $('#achievements-tbody').html(achievementsHtml);

                            $('#printable-area').show();
                        } else {
                            toastr.error(response.message || 'Failed to load report.');
                            resetReport();
                        }
                    },
                    error: function(xhr, status, error) {
                        $('#loading-spinner').hide();
                        toastr.error('An error occurred while compiling the report.');
                        resetReport();
                    }
                });
            }

            $(document).on('click', '.view-daily-attendance', function() {
                let index = $(this).data('index');
                let monthData = currentAttendanceData[index];
                if (monthData) {
                    $('#attendanceModalTitle').text(monthData.month + ' - Monthly Attendance Details');
                    let html = '';
                    if (monthData.daily_records && monthData.daily_records.length > 0) {
                        monthData.daily_records.forEach(function(rec) {
                            let badgeClass = rec.status === 'Present' ? 'bg-success' : 'bg-danger';
                            html += `<tr>
                                <td>${rec.date}</td>
                                <td>${rec.day}</td>
                                <td>${rec.in_time}</td>
                                <td>${rec.out_time}</td>
                                <td><span class="badge ${badgeClass}">${rec.status}</span></td>
                            </tr>`;
                        });
                    } else {
                        html = '<tr><td colspan="5" class="text-center text-muted">No daily records found.</td></tr>';
                    }
                    $('#attendance-modal-tbody').html(html);
                    $('#attendanceDetailModal').modal('show');
                }
            });

            function resetReport() {
                $('#printable-area').hide();
                $('#empty-state').show();
                $('#report-title').text('Student Report (Current Semester)');
                currentAttendanceData = [];
                selectedStudentId = null;
            }

            $('#download-pdf-btn').on('click', function() {
                if (!selectedStudentId) {
                    toastr.warning('Please select a student.');
                    return;
                }
                let $btn = $(this);
                let originalHtml = $btn.html();
                $btn.prop('disabled', true).html('<div class="spinner-border spinner-border-sm me-1"></div>Downloading...');
                toastr.info('Generating PDF report...');

                let url = "{{ route('student_report.pdf') }}?student_id=" + selectedStudentId;
                fetch(url)
                    .then(async response => {
                        if (!response.ok) throw new Error('Download failed');
                        let disposition = response.headers.get('Content-Disposition');
                        let filename = 'Student_Report.pdf';
                        if (disposition && disposition.indexOf('filename=') !== -1) {
                            let matches = /filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/.exec(disposition);
                            if (matches != null && matches[1]) {
                                filename = matches[1].replace(/['"]/g, '');
                            }
                        }
                        return response.blob().then(blob => ({ blob, filename }));
                    })
                    .then(({ blob, filename }) => {
                        let blobUrl = window.URL.createObjectURL(blob);
                        let a = document.createElement('a');
                        a.style.display = 'none';
                        a.href = blobUrl;
                        a.download = filename;
                        document.body.appendChild(a);
                        a.click();
                        setTimeout(() => {
                            window.URL.revokeObjectURL(blobUrl);
                            $(a).remove();
                        }, 1000);

                        $btn.prop('disabled', false).html(originalHtml);
                        toastr.success('PDF report downloaded successfully!');
                    })
                    .catch(() => {
                        $btn.prop('disabled', false).html(originalHtml);
                        toastr.error('Error downloading PDF report.');
                    });
            });

            $('#print-report-btn').on('click', function() {
                if (selectedStudentId) {
                    let url = "{{ route('student_report.print') }}?student_id=" + selectedStudentId;
                    window.open(url, '_blank');
                } else {
                    toastr.warning('Please select a student.');
                }
            });


            // ================= 2. BULK STUDENT REPORTS JS =================
            $(document).on('change', '#bulk_course_id', function() {
                let courseId = $(this).val();
                $('#bulk_class_id').html('<option value="">Select Class</option>');
                $('#bulk-students-tbody').html('<tr><td colspan="6" class="text-center py-5 text-muted"><i class="bx bx-info-circle" style="font-size: 2.5rem;"></i><p class="mt-2 mb-0">Please select a Batch to load students.</p></td></tr>');
                $('#bulk-select-all').prop('checked', false);
                updateBulkCounts();
            });

            $(document).on('change', '#bulk_batch_id', function() {
                let batchId = $(this).val();
                if (batchId) {
                    loadBulkStudents();
                } else {
                    $('#bulk-students-tbody').html('<tr><td colspan="6" class="text-center py-5 text-muted"><i class="bx bx-info-circle" style="font-size: 2.5rem;"></i><p class="mt-2 mb-0">Please select a Batch to load students.</p></td></tr>');
                    $('#bulk-select-all').prop('checked', false);
                    updateBulkCounts();
                }
            });

            $(document).on('change', '#bulk_class_id', function() {
                let batchId = $('#bulk_batch_id').val();
                if (batchId) {
                    loadBulkStudents();
                }
            });

            $('#btn-filter-bulk-students').on('click', function() {
                loadBulkStudents();
            });

            function loadBulkStudents() {
                let courseId = $('#bulk_course_id').val();
                let batchId = $('#bulk_batch_id').val();
                let classId = $('#bulk_class_id').val();

                if (!courseId && !batchId) {
                    $('#bulk-students-tbody').html('<tr><td colspan="6" class="text-center py-5 text-muted"><i class="bx bx-info-circle" style="font-size: 2.5rem;"></i><p class="mt-2 mb-0">Please select Course and Batch first.</p></td></tr>');
                    $('#bulk-select-all').prop('checked', false);
                    updateBulkCounts();
                    return;
                }

                $('#bulk-students-tbody').html('<tr><td colspan="6" class="text-center py-5 text-muted"><div class="spinner-border text-primary me-2"></div><br><span class="mt-2 d-inline-block">Loading students list...</span></td></tr>');

                $.ajax({
                    url: "{{ route('student_report.students_list') }}",
                    type: "GET",
                    data: {
                        course_id: courseId,
                        batch_id: batchId,
                        class_id: classId
                    },
                    success: function(res) {
                        if (res.success && res.data && res.data.length > 0) {
                            let html = '';
                            res.data.forEach(function(stu) {
                                html += `<tr>
                                    <td class="text-center">
                                        <input type="checkbox" class="form-check-input bulk-student-checkbox" value="${stu.id}" style="cursor: pointer; width: 18px; height: 18px;">
                                    </td>
                                    <td><span class="badge bg-light text-dark border">${stu.gr_no}</span></td>
                                    <td class="fw-bold">${stu.name}</td>
                                    <td>${stu.course}</td>
                                    <td>${stu.batch}</td>
                                    <td>${stu.class}</td>
                                </tr>`;
                            });
                            $('#bulk-students-tbody').html(html);
                            $('#bulk-select-all').prop('checked', false);
                            updateBulkCounts();
                        } else {
                            $('#bulk-students-tbody').html('<tr><td colspan="6" class="text-center py-5 text-muted">No students found matching the selected filters.</td></tr>');
                            $('#bulk-select-all').prop('checked', false);
                            updateBulkCounts();
                        }
                    },
                    error: function() {
                        $('#bulk-students-tbody').html('<tr><td colspan="6" class="text-center py-5 text-danger">Error loading students list.</td></tr>');
                    }
                });
            }

            $(document).on('change', '#bulk-select-all', function() {
                let isChecked = $(this).is(':checked');
                $('.bulk-student-checkbox').prop('checked', isChecked);
                updateBulkCounts();
            });

            $(document).on('change', '.bulk-student-checkbox', function() {
                let total = $('.bulk-student-checkbox').length;
                let checked = $('.bulk-student-checkbox:checked').length;
                $('#bulk-select-all').prop('checked', total > 0 && total === checked);
                updateBulkCounts();
            });

            $(document).on('click', '#bulk-students-tbody tr', function(e) {
                if ($(e.target).is('input[type="checkbox"]')) {
                    return;
                }
                let $cb = $(this).find('.bulk-student-checkbox');
                if ($cb.length) {
                    $cb.prop('checked', !$cb.prop('checked')).trigger('change');
                }
            });

            function updateBulkCounts() {
                let total = $('.bulk-student-checkbox').length;
                let checked = $('.bulk-student-checkbox:checked').length;
                $('#bulk-total-count').text(total);
                $('#bulk-selected-count').text(checked);
            }

            $('#btn-start-bulk-download').on('click', function() {
                let selectedIds = [];
                $('.bulk-student-checkbox:checked').each(function() {
                    selectedIds.push($(this).val());
                });

                if (selectedIds.length === 0) {
                    toastr.warning('Please select at least one student.');
                    return;
                }

                let $btn = $(this);
                let originalHtml = $btn.html();
                $btn.prop('disabled', true).html('<div class="spinner-border spinner-border-sm me-2"></div>Creating ZIP...');

                toastr.info('Generating ZIP archive for ' + selectedIds.length + ' student reports. Please wait...');

                let downloadUrl = "{{ route('student_report.bulk_pdf') }}?student_ids=" + selectedIds.join(',') + "&format=zip";

                fetch(downloadUrl)
                    .then(async response => {
                        if (!response.ok) {
                            let errText = await response.text();
                            throw new Error(errText || 'Download failed');
                        }
                        let disposition = response.headers.get('Content-Disposition');
                        let filename = 'Student_Reports_' + new Date().toISOString().slice(0, 10) + '.zip';
                        if (disposition && disposition.indexOf('filename=') !== -1) {
                            let matches = /filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/.exec(disposition);
                            if (matches != null && matches[1]) {
                                filename = matches[1].replace(/['"]/g, '');
                            }
                        }
                        return response.blob().then(blob => ({ blob, filename }));
                    })
                    .then(({ blob, filename }) => {
                        let blobUrl = window.URL.createObjectURL(blob);
                        let a = document.createElement('a');
                        a.style.display = 'none';
                        a.href = blobUrl;
                        a.download = filename;
                        document.body.appendChild(a);
                        a.click();
                        setTimeout(() => {
                            window.URL.revokeObjectURL(blobUrl);
                            $(a).remove();
                        }, 1000);

                        $btn.prop('disabled', false).html(originalHtml);
                        toastr.success('ZIP file downloaded successfully! (' + selectedIds.length + ' Reports)');
                    })
                    .catch(error => {
                        console.error('ZIP download error:', error);
                        $btn.prop('disabled', false).html(originalHtml);
                        toastr.error('Error generating ZIP download. Please try again.');
                    });
            });
        });
    </script>
@endsection
