@extends('software.layout.app')

@php
    $page_title = isset($modules['title']) ? $modules['title'] : 'Summary Report';
    $route = isset($modules['route']) ? $modules['route'] : 'summary_report';
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
            margin-bottom: 1.5rem;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 3px 6px rgba(0, 0, 0, 0.04);
            padding: 1.25rem;
            border: 1px solid #eef2f6;
            height: calc(100% - 1.5rem);
            display: flex;
            flex-direction: column;
        }
        .section-header {
            font-size: 1rem;
            font-weight: 700;
            color: #3b3f5c;
            border-bottom: 2px solid #5c1ac3;
            padding-bottom: 0.4rem;
            margin-bottom: 1rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .section-header i {
            margin-right: 6px;
            color: #5c1ac3;
        }
        .report-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 0.25rem;
        }
        .report-table th {
            background-color: #f8f9fa;
            color: #4b4b4b;
            font-weight: 600;
            text-align: left;
            padding: 8px 12px;
            border-bottom: 2px solid #dee2e6;
            font-size: 0.82rem;
            white-space: nowrap;
        }
        .report-table td {
            padding: 8px 12px;
            border-bottom: 1px solid #dee2e6;
            color: #5a5a5a;
            font-size: 0.82rem;
            vertical-align: middle;
        }
        .report-table tbody tr:hover {
            background-color: #f9fbfd;
        }
        .profile-img-container img {
            width: 90px;
            height: 90px;
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
                padding: 0.6rem !important;
                margin-bottom: 0.5rem !important;
                page-break-inside: avoid;
            }
            .section-header {
                border-bottom: 2px solid #000 !important;
                color: #000 !important;
                margin-bottom: 0.4rem !important;
                padding-bottom: 0.2rem !important;
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
            <ul class="nav nav-pills nav-custom-pills" id="summaryReportTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active fw-bold py-2 px-4" id="single-summary-tab" data-bs-toggle="pill" data-bs-target="#single-summary-content" type="button" role="tab" aria-controls="single-summary-content" aria-selected="true">
                        <i class="bx bx-user me-1"></i> Single Student Summary Report
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link fw-bold py-2 px-4" id="bulk-summary-tab" data-bs-toggle="pill" data-bs-target="#bulk-summary-content" type="button" role="tab" aria-controls="bulk-summary-content" aria-selected="false">
                        <i class="bx bxs-file-archive me-1"></i> Bulk Summary Reports (Class / Batch)
                    </button>
                </li>
            </ul>
        </div>
    </div>

    <!-- Tab Contents -->
    <div class="tab-content" id="summaryReportTabContent">
        
        <!-- ================= TAB 1: SINGLE STUDENT SUMMARY REPORT ================= -->
        <div class="tab-pane fade show active" id="single-summary-content" role="tabpanel" aria-labelledby="single-summary-tab">
            
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
                <p class="mt-2 text-muted">Compiling Student Summary Report...</p>
            </div>

            <!-- Empty State -->
            <div id="empty-state" class="card text-center py-5 no-print shadow-sm border-0">
                <div class="card-body">
                    <i class="bx bx-user-circle text-muted" style="font-size: 4rem;"></i>
                    <h5 class="mt-3 fw-bold">No Student Selected</h5>
                    <p class="text-muted">Please search and select a student above to view their summary report.</p>
                </div>
            </div>

            <!-- Printable Summary Report Container -->
            <div id="printable-area" style="display: none;">

                <!-- Student Personal & Academic Details Header Card -->
                <div class="card report-section mb-4 shadow-sm border-0">
                    <div class="section-header">
                        <span><i class="bx bx-id-card"></i> Student Summary</span>
                        <span class="badge bg-label-primary px-3 py-1" id="academic-semester">Semester 1</span>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <table class="w-100">
                                <tr>
                                    <td class="fw-bold text-muted py-1" style="width: 25%;">Gr No :</td>
                                    <td id="student-gr-no" class="fw-bold py-1 fs-6">-</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold text-muted py-1">Student Name :</td>
                                    <td id="student-name" class="fw-bold text-primary py-1 fs-6">-</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="w-100">
                                <tr>
                                    <td class="fw-bold text-muted py-1" style="width: 25%;">Course :</td>
                                    <td id="academic-course" class="py-1">-</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold text-muted py-1">Batch / Class :</td>
                                    <td class="py-1"><span id="academic-batch">-</span> / <span id="academic-class">-</span></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- ROW 1: Assignment (col-md-6) | Assessment (col-md-6) -->
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <div class="report-section h-100">
                            <div class="section-header">
                                <span><i class="bx bx-task"></i> Assignment</span>
                                <span class="badge bg-label-info" id="assignment-count">0</span>
                            </div>
                            <div class="table-responsive flex-grow-1">
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
                                    <tbody id="assignments-tbody">
                                        <tr>
                                            <td colspan="5" class="text-center text-muted py-3">No assignments recorded.</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="report-section h-100">
                            <div class="section-header">
                                <span><i class="bx bx-check-double"></i> Assessment</span>
                                <span class="badge bg-label-success" id="assessment-count">0</span>
                            </div>
                            <div class="table-responsive flex-grow-1">
                                <table class="report-table">
                                    <thead>
                                        <tr>
                                            <th style="width: 8%;">No</th>
                                            <th>Subject</th>
                                            <th>Performance</th>
                                            <th>Remarks</th>
                                        </tr>
                                    </thead>
                                    <tbody id="assessments-tbody">
                                        <tr>
                                            <td colspan="4" class="text-center text-muted py-3">No assessments recorded.</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ROW 2: Test Detail (col-md-6) | Complaints (col-md-6) -->
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <div class="report-section h-100">
                            <div class="section-header">
                                <span><i class="bx bx-edit-alt"></i> Test Detail</span>
                                <span class="badge bg-label-primary" id="test-count">0</span>
                            </div>
                            <div class="table-responsive flex-grow-1">
                                <table class="report-table">
                                    <thead>
                                        <tr>
                                            <th style="width: 8%;">No</th>
                                            <th>Subject</th>
                                            <th>Date</th>
                                            <th>Total Marks</th>
                                            <th>Obtained Marks</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tests-tbody">
                                        <tr>
                                            <td colspan="5" class="text-center text-muted py-3">No test records available.</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="report-section h-100">
                            <div class="section-header">
                                <span><i class="bx bx-error-circle"></i> Complaints</span>
                                <span class="badge bg-label-danger" id="complaint-count">0</span>
                            </div>
                            <div class="table-responsive flex-grow-1">
                                <table class="report-table">
                                    <thead>
                                        <tr>
                                            <th style="width: 8%;">No</th>
                                            <th>Complaint</th>
                                            <th>Date</th>
                                            <th>Faculty</th>
                                        </tr>
                                    </thead>
                                    <tbody id="complaints-tbody">
                                        <tr>
                                            <td colspan="4" class="text-center text-muted py-3">No complaints recorded.</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ROW 3: Result Details (col-md-6) | Achievements (col-md-6) -->
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <div class="report-section h-100">
                            <div class="section-header">
                                <span><i class="bx bx-award"></i> Result Details</span>
                                <span class="badge bg-label-warning" id="result-count">0</span>
                            </div>
                            <div class="table-responsive flex-grow-1">
                                <table class="report-table">
                                    <thead>
                                        <tr>
                                            <th style="width: 8%;">No</th>
                                            <th>Semester / Exam</th>
                                            <th>Total Marks</th>
                                            <th>Obtained Marks</th>
                                            <th>Percentage</th>
                                        </tr>
                                    </thead>
                                    <tbody id="results-tbody">
                                        <tr>
                                            <td colspan="5" class="text-center text-muted py-3">No result records available.</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="report-section h-100">
                            <div class="section-header">
                                <span><i class="bx bx-trophy"></i> Achievements</span>
                                <span class="badge bg-label-info" id="achievement-count">0</span>
                            </div>
                            <div class="table-responsive flex-grow-1">
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
                                    <tbody id="achievements-tbody">
                                        <tr>
                                            <td colspan="5" class="text-center text-muted py-3">No achievements recorded.</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

        </div>

        <!-- ================= TAB 2: BULK STUDENT SUMMARY REPORTS (CLASS / BATCH) ================= -->
        <div class="tab-pane fade" id="bulk-summary-content" role="tabpanel" aria-labelledby="bulk-summary-tab">
            
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
                            <button id="filter-students-btn" class="btn btn-primary w-100 shadow-sm">
                                <i class="bx bx-search-alt me-1"></i> Filter Students
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Students List Card -->
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <h5 class="card-title mb-0 fw-bold">
                        <i class="bx bx-list-check me-1"></i> Students List
                    </h5>
                    <div>
                        <button id="bulk-download-zip-btn" class="btn btn-danger btn-sm me-2 shadow-sm" disabled>
                            <i class="bx bxs-file-archive me-1"></i> Download Selected (ZIP)
                        </button>
                        <button id="bulk-download-pdf-btn" class="btn btn-primary btn-sm shadow-sm" disabled>
                            <i class="bx bxs-file-pdf me-1"></i> Download Selected (Single PDF)
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Bulk Loading Spinner -->
                    <div id="bulk-loading-spinner" class="text-center my-4" style="display: none;">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2 text-muted">Fetching students...</p>
                    </div>

                    <!-- Students Table -->
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" id="bulk-students-table">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 5%;">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="select-all-students">
                                        </div>
                                    </th>
                                    <th style="width: 15%;">Gr No.</th>
                                    <th style="width: 30%;">Student Name</th>
                                    <th style="width: 25%;">Course</th>
                                    <th style="width: 15%;">Batch</th>
                                    <th style="width: 10%;">Class</th>
                                </tr>
                            </thead>
                            <tbody id="bulk-students-tbody">
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">
                                        Please apply filters above to load students.
                                    </td>
                                </tr>
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
            let selectedStudentId = null;

            // ================= 1. SINGLE STUDENT SUMMARY REPORT JS =================
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
                    url: "{{ route('summary_report.data') }}",
                    type: "GET",
                    data: { student_id: studentId },
                    success: function(response) {
                        $('#loading-spinner').hide();
                        
                        if (response.success) {
                            selectedStudentId = studentId;

                            $('#student-gr-no').text(response.personal.gr_no);
                            $('#student-name').text(response.personal.name);

                            $('#academic-course').text(response.academic.course);
                            $('#academic-batch').text(response.academic.batch);
                            $('#academic-class').text(response.academic.class);
                            $('#academic-semester').text(response.academic.semester);

                            // 1. Assignments
                            let assignmentsHtml = '';
                            if (response.assignments && response.assignments.length > 0) {
                                $('#assignment-count').text(response.assignments.length);
                                response.assignments.forEach(function(item, index) {
                                    let statusBadge = item.status === 'Submitted' 
                                        ? '<span class="badge bg-label-success">Submitted</span>' 
                                        : '<span class="badge bg-label-warning">Pending</span>';
                                    assignmentsHtml += `<tr>
                                        <td>${index + 1}</td>
                                        <td class="fw-semibold">${item.subject}</td>
                                        <td>${item.date}</td>
                                        <td>${statusBadge}</td>
                                        <td>${item.remarks}</td>
                                    </tr>`;
                                });
                            } else {
                                $('#assignment-count').text('0');
                                assignmentsHtml = '<tr><td colspan="5" class="text-center text-muted py-3">No assignments recorded.</td></tr>';
                            }
                            $('#assignments-tbody').html(assignmentsHtml);

                            // 2. Assessments
                            let assessmentsHtml = '';
                            if (response.assessments && response.assessments.length > 0) {
                                $('#assessment-count').text(response.assessments.length);
                                response.assessments.forEach(function(item, index) {
                                    assessmentsHtml += `<tr>
                                        <td>${index + 1}</td>
                                        <td class="fw-semibold">${item.subject}</td>
                                        <td><span class="badge bg-label-primary">${item.performance}</span></td>
                                        <td>${item.remarks}</td>
                                    </tr>`;
                                });
                            } else {
                                $('#assessment-count').text('0');
                                assessmentsHtml = '<tr><td colspan="4" class="text-center text-muted py-3">No assessments recorded.</td></tr>';
                            }
                            $('#assessments-tbody').html(assessmentsHtml);

                            // 3. Tests
                            let testsHtml = '';
                            if (response.tests && response.tests.length > 0) {
                                $('#test-count').text(response.tests.length);
                                response.tests.forEach(function(test, index) {
                                    testsHtml += `<tr>
                                        <td>${index + 1}</td>
                                        <td class="fw-semibold">${test.subject}</td>
                                        <td>${test.date}</td>
                                        <td>${test.total_marks}</td>
                                        <td><span class="badge bg-label-success fw-bold">${test.obtained_marks}</span></td>
                                    </tr>`;
                                });
                            } else {
                                $('#test-count').text('0');
                                testsHtml = '<tr><td colspan="5" class="text-center text-muted py-3">No tests recorded.</td></tr>';
                            }
                            $('#tests-tbody').html(testsHtml);

                            // 4. Complaints
                            let complaintsHtml = '';
                            if (response.complaints && response.complaints.length > 0) {
                                $('#complaint-count').text(response.complaints.length);
                                response.complaints.forEach(function(item, index) {
                                    complaintsHtml += `<tr>
                                        <td>${index + 1}</td>
                                        <td class="text-danger fw-semibold">${item.complaint}</td>
                                        <td>${item.date}</td>
                                        <td>${item.faculty}</td>
                                    </tr>`;
                                });
                            } else {
                                $('#complaint-count').text('0');
                                complaintsHtml = '<tr><td colspan="4" class="text-center text-muted py-3">No complaints recorded.</td></tr>';
                            }
                            $('#complaints-tbody').html(complaintsHtml);

                            // 5. Results
                            let resultsHtml = '';
                            if (response.results && response.results.length > 0) {
                                $('#result-count').text(response.results.length);
                                response.results.forEach(function(item, index) {
                                    resultsHtml += `<tr>
                                        <td>${index + 1}</td>
                                        <td class="fw-semibold">${item.semester}</td>
                                        <td>${item.total_marks}</td>
                                        <td>${item.obtained_marks}</td>
                                        <td><span class="badge bg-label-info fw-bold">${item.percentage}</span></td>
                                    </tr>`;
                                });
                            } else {
                                $('#result-count').text('0');
                                resultsHtml = '<tr><td colspan="5" class="text-center text-muted py-3">No result records available.</td></tr>';
                            }
                            $('#results-tbody').html(resultsHtml);

                            // 6. Achievements
                            let achievementsHtml = '';
                            if (response.achievements && response.achievements.length > 0) {
                                $('#achievement-count').text(response.achievements.length);
                                response.achievements.forEach(function(item, index) {
                                    achievementsHtml += `<tr>
                                        <td>${index + 1}</td>
                                        <td class="fw-semibold">${item.event}</td>
                                        <td><span class="badge bg-label-warning text-dark fw-bold">${item.rank}</span></td>
                                        <td>${item.date}</td>
                                        <td>${item.remark}</td>
                                    </tr>`;
                                });
                            } else {
                                $('#achievement-count').text('0');
                                achievementsHtml = '<tr><td colspan="5" class="text-center text-muted py-3">No achievements recorded.</td></tr>';
                            }
                            $('#achievements-tbody').html(achievementsHtml);

                            $('#printable-area').fadeIn();
                        } else {
                            alert(response.message || 'Could not fetch report data.');
                            resetReport();
                        }
                    },
                    error: function() {
                        $('#loading-spinner').hide();
                        alert('An error occurred while fetching the student summary report.');
                        resetReport();
                    }
                });
            }

            function resetReport() {
                selectedStudentId = null;
                $('#printable-area').hide();
                $('#empty-state').show();
            }

            // Download PDF
            $('#download-pdf-btn').on('click', function() {
                if (!selectedStudentId) {
                    alert('Please select a student first.');
                    return;
                }
                let url = "{{ route('summary_report.pdf') }}?student_id=" + selectedStudentId;
                window.open(url, '_blank');
            });

            // Print Report
            $('#print-report-btn').on('click', function() {
                if (!selectedStudentId) {
                    alert('Please select a student first.');
                    return;
                }
                let url = "{{ route('summary_report.print') }}?student_id=" + selectedStudentId;
                window.open(url, '_blank');
            });

            // ================= 2. BULK STUDENT SUMMARY REPORTS JS =================
            $('#filter-students-btn').on('click', function() {
                let courseId = $('#bulk_course_id').val();
                let batchId = $('#bulk_batch_id').val();
                let classId = $('#bulk_class_id').val();

                if (!courseId && !batchId && !classId) {
                    alert('Please select at least one filter criterion (Course, Batch, or Class).');
                    return;
                }

                $('#bulk-loading-spinner').show();
                $('#bulk-students-tbody').html('');

                $.ajax({
                    url: "{{ route('summary_report.students_list') }}",
                    type: "GET",
                    data: {
                        course_id: courseId,
                        batch_id: batchId,
                        class_id: classId
                    },
                    success: function(response) {
                        $('#bulk-loading-spinner').hide();
                        if (response.success && response.data.length > 0) {
                            let rowsHtml = '';
                            response.data.forEach(function(student) {
                                rowsHtml += `<tr>
                                    <td>
                                        <div class="form-check">
                                            <input class="form-check-input student-checkbox" type="checkbox" value="${student.id}">
                                        </div>
                                    </td>
                                    <td class="fw-bold">${student.gr_no}</td>
                                    <td>${student.name}</td>
                                    <td>${student.course}</td>
                                    <td>${student.batch}</td>
                                    <td>${student.class}</td>
                                </tr>`;
                            });
                            $('#bulk-students-tbody').html(rowsHtml);
                            $('#select-all-students').prop('checked', false);
                            updateBulkButtons();
                        } else {
                            $('#bulk-students-tbody').html('<tr><td colspan="6" class="text-center text-muted py-4">No students found matching the selected criteria.</td></tr>');
                            updateBulkButtons();
                        }
                    },
                    error: function() {
                        $('#bulk-loading-spinner').hide();
                        alert('Failed to load students list.');
                    }
                });
            });

            // Select All Checkbox
            $('#select-all-students').on('change', function() {
                let isChecked = $(this).is(':checked');
                $('.student-checkbox').prop('checked', isChecked);
                updateBulkButtons();
            });

            // Single Checkbox toggle
            $(document).on('change', '.student-checkbox', function() {
                let allChecked = $('.student-checkbox').length === $('.student-checkbox:checked').length;
                $('#select-all-students').prop('checked', allChecked);
                updateBulkButtons();
            });

            function updateBulkButtons() {
                let checkedCount = $('.student-checkbox:checked').length;
                if (checkedCount > 0) {
                    $('#bulk-download-zip-btn').removeAttr('disabled').text(`Download Selected (${checkedCount}) ZIP`);
                    $('#bulk-download-pdf-btn').removeAttr('disabled').text(`Download Selected (${checkedCount}) Single PDF`);
                } else {
                    $('#bulk-download-zip-btn').attr('disabled', 'disabled').text('Download Selected (ZIP)');
                    $('#bulk-download-pdf-btn').attr('disabled', 'disabled').text('Download Selected (Single PDF)');
                }
            }

            // Bulk Download ZIP
            $('#bulk-download-zip-btn').on('click', function() {
                let selectedIds = [];
                $('.student-checkbox:checked').each(function() {
                    selectedIds.push($(this).val());
                });

                if (selectedIds.length === 0) {
                    alert('Please select at least one student.');
                    return;
                }

                let downloadUrl = "{{ route('summary_report.bulk_pdf') }}?student_ids=" + selectedIds.join(',') + "&format=zip";
                window.location.href = downloadUrl;
            });

            // Bulk Download Combined PDF
            $('#bulk-download-pdf-btn').on('click', function() {
                let selectedIds = [];
                $('.student-checkbox:checked').each(function() {
                    selectedIds.push($(this).val());
                });

                if (selectedIds.length === 0) {
                    alert('Please select at least one student.');
                    return;
                }

                let downloadUrl = "{{ route('summary_report.bulk_pdf') }}?student_ids=" + selectedIds.join(',') + "&format=combined";
                window.open(downloadUrl, '_blank');
            });
        });
    </script>
@endsection
