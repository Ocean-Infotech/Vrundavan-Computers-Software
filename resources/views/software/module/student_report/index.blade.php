@extends('software.layout.app')

@php
    $page_title = isset($modules['title']) ? $modules['title'] : 'Student Report';
    $route = isset($modules['route']) ? $modules['route'] : 'student_report';
@endphp

@section('title', $page_title)

@section('page_style_file')
    <style>
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
                padding: 1rem !important;
                margin-bottom: 1.5rem !important;
                page-break-inside: avoid;
            }
            .section-header {
                border-bottom: 2px solid #000 !important;
                color: #000 !important;
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
    <div class="d-flex justify-content-between align-items-center mb-4 no-print">
        <h4 class="mb-0 fw-bold">{{ $page_title }}</h4>
    </div>

    <!-- Search Section -->
    <div class="card mb-4 no-print">
        <div class="card-body">
            <div class="row align-items-end">
                <div class="col-md-6 col-sm-12">
                    <div class="form-group mb-0">
                        <label class="form-label fw-bold">Search Student</label>
                        <input list="search" id="search_id" name="search"
                            class="form-control"
                            placeholder="Type Student ID or Name to search...">
                        <datalist id="search" class="search_by_courceregistration" data-append="search_by_courceregistration"
                            data-selectedCourceRegistrationId="">
                        </datalist>
                    </div>
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
    <div id="empty-state" class="card text-center py-5 no-print">
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
            <!-- Left Column: Personal and Academic Details -->
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
    </div>

    <!-- Attendance Detail Modal -->
    <div class="modal fade no-print" id="attendanceDetailModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="attendanceModalTitle">Monthly Attendance Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
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
    
    <script>
        $(document).ready(function() {
            let searchDebounceTimer;
            let currentAttendanceData = [];

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

                            // Populate Personal Details
                            $('#student-photo').attr('src', response.personal.photo);
                            $('#student-gr-no').text(response.personal.gr_no);
                            $('#student-name').text(response.personal.name);

                            // Populate Academic Details
                            $('#academic-batch').text(response.academic.batch);
                            $('#academic-class').text(response.academic.class);
                            $('#academic-semester').text(response.academic.semester);
                            $('#report-title').text('Student Report (' + response.academic.semester + ')');

                            // Populate Fees
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

                            // Populate Attendance
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

                            // Populate Tests
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

                            // Populate Complaints
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

                            // Populate Assignments
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

                            // Populate Assessments
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

                            $('#printable-area').show();
                        } else {
                            toastr.error(response.message || 'Failed to load report.');
                            resetReport();
                        }
                    },
                    error: function(xhr, status, error) {
                        $('#loading-spinner').hide();
                        let errorMsg = 'An error occurred while compiling the report.';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMsg += ' Details: ' + xhr.responseJSON.message;
                        } else if (xhr.responseText) {
                            // Extract title or body if it is HTML error page
                            let tempDiv = document.createElement('div');
                            tempDiv.innerHTML = xhr.responseText;
                            let title = tempDiv.querySelector('title');
                            if (title && title.innerText) {
                                errorMsg += ' Details: ' + title.innerText;
                            } else {
                                errorMsg += ' Details: ' + xhr.statusText;
                            }
                        }
                        toastr.error(errorMsg);
                        console.error('AJAX Error:', status, error, xhr.responseText);
                        resetReport();
                    }
                });
            }

            // Click listener for showing daily details modal
            $(document).on('click', '.view-daily-attendance', function() {
                let index = $(this).data('index');
                let monthData = currentAttendanceData[index];
                
                if (monthData) {
                    $('#attendanceModalTitle').text(monthData.month + ' - Daily Attendance Details');
                    
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
            }
        });
    </script>
@endsection
