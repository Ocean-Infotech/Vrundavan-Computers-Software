@extends('software.layout.app')

@php
    $page_title = isset($modules['title']) ? $modules['title'] : 'Combined Report';
    $folder_path = isset($modules['folder_path']) ? $modules['folder_path'] : null;
    $route = isset($modules['route']) ? $modules['route'] : null;
@endphp

@section('title', $page_title)

@section('page_style_file')
    <link rel="stylesheet" href="{{ asset('admin/assets/cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css') }}">
    <link rel="stylesheet" href="{{ asset('admin/assets/cdn.datatables.net/responsive/2.2.9/css/responsive.bootstrap.min.css') }}">
    <style>
        .table-responsive {
            overflow-x: auto !important;
            scrollbar-width: auto !important;
            -ms-overflow-style: auto !important;
        }
        .table-responsive::-webkit-scrollbar {
            height: 10px !important;
            display: block !important;
        }
        .table-responsive::-webkit-scrollbar-track {
            background: #f1f1f1 !important;
        }
        .table-responsive::-webkit-scrollbar-thumb {
            background: #a8a8a8 !important;
            border-radius: 6px !important;
            border: 2px solid #f1f1f1 !important;
        }
        .table-responsive::-webkit-scrollbar-thumb:hover {
            background: #787878 !important;
        }
    </style>
@endsection

@section('content')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <div class="d-flex justify-content-lg-between px-1">
        @include('software.includes.breadcrumb', [
            'breadcrumbArray' => [['title' => $page_title, 'url' => '']],
            'route' => $route,
            'show_add_btn' => false,
            'show_back_btn' => false,
        ])
    </div>

    <div class="row my-3">
        <div class="col-md-12">
            <!-- Filter Card -->
            <div class="card mb-3">
                <div class="card-body">
                    <div class="row align-items-end">
                        <!-- Course -->
                        <div class="col-md-4 col-sm-12">
                            <div class="form-group">
                                <label class="form-label">Course</label>
                                <select id="course" name="course_id"
                                    class="form-control search_by_course select2"
                                    data-append="search_by_course">
                                    <option value="">Select Course</option>
                                </select>
                            </div>
                        </div>

                        <!-- Batch -->
                        <div class="col-md-4 col-sm-12">
                            <div class="form-group">
                                <label class="form-label">Batch</label>
                                <select id="batch" name="batch_id"
                                    class="form-control search_by_batch select2"
                                    data-append="search_by_batch">
                                    <option value="">Select Batch</option>
                                </select>
                            </div>
                        </div>

                        <!-- Semester -->
                        <div class="col-md-2 col-sm-12">
                            <div class="form-group">
                                <label class="form-label">Semester</label>
                                <select id="semester" name="semester"
                                    class="form-control search_by_semester select2"
                                    data-append="search_by_semester">
                                    <option value="">Select Semester</option>
                                </select>
                            </div>
                        </div>

                        <!-- Export Button -->
                        <div class="col-md-2 col-sm-12 mt-3 mt-md-0">
                            <button type="button" id="export_btn" class="btn btn-success w-100">
                                <i class="ti ti-file-spreadsheet me-1"></i> Export
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Table Card -->
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive" style="overflow-x: auto !important; width: 100% !important; display: block !important;">
                        <table id="yajra-datatables" class="table table-bordered table-striped table-hover text-nowrap" style="width: max-content !important; min-width: 100%; white-space: nowrap; border-collapse: collapse;">
                            <thead>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('page_script_file')
    <!--datatable js-->
    <script src="{{ asset('admin/assets/cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('admin/assets/cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js') }}"></script>
    <script src="{{ asset('admin/assets/cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js') }}"></script>
@endsection

@section('page_leavel_script')
    @include('software.utils.getCourse')
    @include('software.utils.getBatchByCourseid')
    @include('software.utils.getSamasterByCourseid')

    <script type="text/javascript">
        $(document).ready(function() {
            let table = null;

            function loadCombinedReport() {
                let courseId = $('#course').val();
                let batchId = $('#batch').val();
                let semester = $('#semester').val();

                if (!courseId || !batchId) {
                    return;
                }

                $.ajax({
                    type: "POST",
                    url: "{{ route('combined-report.get-data') }}",
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    data: {
                        course_id: courseId,
                        batch_id: batchId,
                        semester: semester
                    },
                    success: function(response) {
                        if (!response.success) return;

                        $('#yajra-datatables thead').empty();
                        $('#yajra-datatables tbody').empty();

                        let weeklyTests = response.weekly_tests;
                        let fullTests = response.full_tests;
                        let assignments = response.assignments;
                        let students = response.students;

                        let weeklyCount = Object.keys(weeklyTests).length;
                        let fullCount = Object.keys(fullTests).length;
                        let assignmentCount = Object.keys(assignments).length;

                        if (weeklyCount === 0 && fullCount === 0 && assignmentCount === 0) {
                            toastr.warning("No tests or assignments found for this semester.", "No Data Found");
                        }

                        // 1. Calculate Colspans for Row 1
                        let weeklyColspan = 0;
                        $.each(weeklyTests, function(sub, tests) {
                            weeklyColspan += tests.length + 1; // unit columns + 1 total column
                        });

                        let fullColspan = 0;
                        $.each(fullTests, function(sub, tests) {
                            fullColspan += tests.length + 1; // unit columns + 1 total column
                        });

                        let assignmentColspan = Object.keys(assignments).length;

                        // 2. Build Header Row 1 (Report Types)
                        let headRow1 = '<tr>';
                        headRow1 += '<th rowspan="3" class="align-middle text-center fw-bold" style="min-width: 80px; white-space: nowrap; vertical-align: middle;">Gr.no</th>';
                        headRow1 += '<th rowspan="3" class="align-middle text-center fw-bold" style="min-width: 220px; white-space: nowrap; vertical-align: middle;">Student Name</th>';
                        if (weeklyColspan > 0) {
                            headRow1 += '<th colspan="' + weeklyColspan + '" class="text-center fw-bold text-uppercase bg-light" style="white-space: nowrap; font-size: 13px; letter-spacing: 0.5px;">Weekly TEST Report</th>';
                        }
                        if (fullColspan > 0) {
                            headRow1 += '<th colspan="' + fullColspan + '" class="text-center fw-bold text-uppercase bg-light" style="white-space: nowrap; font-size: 13px; letter-spacing: 0.5px;">Full Test Report</th>';
                        }
                        if (assignmentColspan > 0) {
                            headRow1 += '<th colspan="' + assignmentColspan + '" class="text-center fw-bold text-uppercase bg-light" style="white-space: nowrap; font-size: 13px; letter-spacing: 0.5px;">Assignment Report</th>';
                        }
                        headRow1 += '</tr>';

                        // 3. Build Header Row 2 (Subjects)
                        let headRow2 = '<tr>';
                        $.each(weeklyTests, function(subject, tests) {
                            headRow2 += '<th colspan="' + (tests.length + 1) + '" class="text-center fw-semibold text-primary" style="min-width: 150px !important; white-space: nowrap !important; font-size: 12px;">' + subject + '</th>';
                        });
                        $.each(fullTests, function(subject, tests) {
                            headRow2 += '<th colspan="' + (tests.length + 1) + '" class="text-center fw-semibold text-success" style="min-width: 150px !important; white-space: nowrap !important; font-size: 12px;">' + subject + '</th>';
                        });
                        $.each(assignments, function(subject, count) {
                            headRow2 += '<th class="text-center fw-semibold text-info" style="min-width: 150px !important; white-space: nowrap !important; font-size: 12px;">' + subject + '</th>';
                        });
                        headRow2 += '</tr>';

                        // Helper to format unit names
                        function formatUnitName(unitName) {
                            let name = String(unitName).trim();
                            if (/^\d+(,\d+)*$/.test(name)) {
                                return "Unit " + name;
                            }
                            return name.charAt(0).toUpperCase() + name.slice(1).toLowerCase();
                        }

                        // 4. Build Header Row 3 (Units & Max Marks / Assignment Totals)
                        let headRow3 = '<tr>';
                        $.each(weeklyTests, function(subject, tests) {
                            let totalMax = 0;
                            $.each(tests, function(i, test) {
                                headRow3 += '<th class="text-center fw-medium text-muted" style="min-width: 150px !important; white-space: nowrap !important; font-size: 11px;">' + subject + ' ' + formatUnitName(test.unit_name) + ' (' + test.mark + ')</th>';
                                totalMax += parseFloat(test.mark);
                            });
                            headRow3 += '<th class="text-center fw-bold bg-light" style="min-width: 120px !important; white-space: nowrap !important; font-size: 11px;">' + subject + ' Total (' + totalMax + ')</th>';
                        });
                        $.each(fullTests, function(subject, tests) {
                            let totalMax = 0;
                            $.each(tests, function(i, test) {
                                headRow3 += '<th class="text-center fw-medium text-muted" style="min-width: 150px !important; white-space: nowrap !important; font-size: 11px;">' + subject + ' ' + formatUnitName(test.unit_name) + ' (' + test.mark + ')</th>';
                                totalMax += parseFloat(test.mark);
                            });
                            headRow3 += '<th class="text-center fw-bold bg-light" style="min-width: 120px !important; white-space: nowrap !important; font-size: 11px;">' + subject + ' Total (' + totalMax + ')</th>';
                        });
                        $.each(assignments, function(subject, count) {
                            headRow3 += '<th class="text-center fw-bold text-dark" style="min-width: 150px !important; white-space: nowrap !important; font-size: 11px;">' + subject + ' TOTAL (' + count + ')</th>';
                        });
                        headRow3 += '</tr>';

                        $('#yajra-datatables thead').append(headRow1 + headRow2 + headRow3);

                        // 5. Build Table Rows
                        let rowsHtml = '';
                        $.each(students, function(i, student) {
                            let row = '<tr>';
                            row += '<td class="text-center">' + student.gr_no + '</td>';
                            row += '<td class="fw-semibold">' + student.student_name + '</td>';

                            // Weekly test marks
                            $.each(weeklyTests, function(subject, tests) {
                                $.each(tests, function(j, test) {
                                    let val = (student.weekly_marks[subject] && student.weekly_marks[subject].tests && student.weekly_marks[subject].tests[test.id] !== undefined) 
                                        ? student.weekly_marks[subject].tests[test.id] 
                                        : '-';
                                    row += '<td class="text-center">' + val + '</td>';
                                });
                                let totalVal = (student.weekly_marks[subject] && student.weekly_marks[subject].total !== undefined)
                                    ? student.weekly_marks[subject].total
                                    : '0';
                                row += '<td class="text-center fw-bold bg-light">' + totalVal + '</td>';
                            });

                            // Full test marks
                            $.each(fullTests, function(subject, tests) {
                                $.each(tests, function(j, test) {
                                    let val = (student.full_marks[subject] && student.full_marks[subject].tests && student.full_marks[subject].tests[test.id] !== undefined) 
                                        ? student.full_marks[subject].tests[test.id] 
                                        : '-';
                                    row += '<td class="text-center">' + val + '</td>';
                                });
                                let totalVal = (student.full_marks[subject] && student.full_marks[subject].total !== undefined)
                                    ? student.full_marks[subject].total
                                    : '0';
                                row += '<td class="text-center fw-bold bg-light">' + totalVal + '</td>';
                            });

                            // Assignment counts
                            $.each(assignments, function(subject, totalCount) {
                                let countVal = (student.assignment_counts[subject] !== undefined)
                                    ? student.assignment_counts[subject]
                                    : '0';
                                row += '<td class="text-center fw-semibold">' + countVal + '</td>';
                            });

                            row += '</tr>';
                            rowsHtml += row;
                        });

                        $('#yajra-datatables tbody').append(rowsHtml);

                        // No DataTable initialization to keep it clean and simple like Excel
                    }
                });
            }



            // Export Excel
            $('#export_btn').on('click', function() {
                let courseId = $('#course').val();
                let batchId = $('#batch').val();
                let semester = $('#semester').val();
                if (!courseId || !batchId) {
                    alert('Please select Course and Batch first.');
                    return;
                }
                window.location.href = "{{ route('combined-report.export-excel') }}?course_id=" + courseId + "&batch_id=" + batchId + "&semester=" + (semester || '');
            });

            // Reload table on dropdown changes
            $('#course, #batch, #semester').on('change', function() {
                loadCombinedReport();
            });

            // Auto load checking loop for initial population
            let loadTimer = setInterval(function() {
                let c = $('#course').val();
                let b = $('#batch').val();
                if (c && b) {
                    loadCombinedReport();
                    clearInterval(loadTimer);
                }
            }, 500);

            setTimeout(function() {
                clearInterval(loadTimer);
            }, 5000);
        });
    </script>
@endsection
