@extends('software.layout.app')

@php
    $page_title = isset($modules['title']) ? $modules['title'] : 'Daily Attendance';
    $folder_path = isset($modules['folder_path']) ? $modules['folder_path'] : null;
    $route = isset($modules['route']) ? $modules['route'] : null;
@endphp
@section('title', $page_title)

@section('page_style_file')
    <link rel="stylesheet" href="{{ asset('admin/assets/cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css') }}">
    <link rel="stylesheet" href="{{ asset('admin/assets/cdn.datatables.net/responsive/2.2.9/css/responsive.bootstrap.min.css') }}">
@endsection

@section('content')
    <div class="d-flex justify-content-lg-between px-1">
        @include('software.includes.breadcrumb', [
            'breadcrumbArray' => [['title' => $page_title, 'url' => '']],
            'route' => $route,
            'show_add_btn' => false,
            'show_filter_btn' => false,
            'show_export_btns' => false,
            'show_excal_btn' => false,
            'show_print_btn' => false,
            'show_back_btn' => false,
        ])
    </div>

    <!-- Filter Card -->
    <div class="row my-2">
        <div class="col-md-12 mb-3">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-light py-2">
                    <h6 class="card-title mb-0 fw-bold text-primary">
                        <i class="fa-solid fa-filter me-2"></i>Filter Student Daily Attendance
                    </h6>
                </div>
                <div class="card-body py-3">
                    <form id="dailyAttendanceFilterForm">
                        <div class="row align-items-end g-3">
                            <div class="col-md-4">
                                <label for="filter_course_id" class="form-label fw-semibold mb-1">Select Course</label>
                                <select id="filter_course_id" class="form-select select2">
                                    <option value="">All Courses</option>
                                    @foreach ($courses as $course)
                                        <option value="{{ $course->id }}">{{ $course->course_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="filter_batch_id" class="form-label fw-semibold mb-1">Select Batch</label>
                                <select id="filter_batch_id" class="form-select select2" disabled>
                                    <option value="">Select Course</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="filter_class_id" class="form-label fw-semibold mb-1">Select Class</label>
                                <select id="filter_class_id" class="form-select select2" disabled>
                                    <option value="">Select Batch</option>
                                </select>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Summary Stats Row -->
    <div class="row mb-3 g-3">
        <div class="col-md-4">
            <div class="p-3 bg-light rounded text-center border">
                <div class="text-muted small fw-semibold">Total Students</div>
                <div class="fs-4 fw-bold text-dark mt-1" id="stat-total-students">0</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="p-3 bg-success-subtle rounded text-center border border-success">
                <div class="text-success small fw-semibold">Present Students</div>
                <div class="fs-4 fw-bold text-success mt-1" id="stat-present-students">0</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="p-3 bg-danger-subtle rounded text-center border border-danger">
                <div class="text-danger small fw-semibold">Absent Students</div>
                <div class="fs-4 fw-bold text-danger mt-1" id="stat-absent-students">0</div>
            </div>
        </div>
    </div>

    <!-- Data Table -->
    <div class="row my-2">
        <div class="col-md-12">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="yajra-datatables" class="table table-hover align-middle dt-responsive w-100">
                            @if (isset($columns))
                                <thead class="table-light">
                                    <tr>
                                        @foreach ($columns as $item)
                                            <th class="{{ $item?->className ?? '' }}">
                                                {{ ucfirst($item?->td_label ?? $item?->name) ?? '' }}
                                            </th>
                                        @endforeach
                                    </tr>
                                </thead>
                            @endif
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Student Monthly Attendance Modal -->
    <div class="modal fade" id="studentDetailModal" tabindex="-1" aria-labelledby="studentDetailModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content shadow-lg border-0">
                <div class="modal-header bg-light">
                    <h5 class="modal-title fw-bold text-dark" id="studentDetailModalLabel">
                        <i class="fa-solid fa-graduation-cap text-primary me-2"></i>
                        Monthly Attendance Details
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 pb-2 border-bottom">
                        <div>
                            <h4 class="mb-1 fw-bold text-primary" id="modal-student-name">-</h4>
                            <span class="badge bg-label-info fs-6">Biometric ID: <span id="modal-biometric-id">-</span></span>
                        </div>
                        <div class="d-flex align-items-center gap-2 mt-2 mt-sm-0">
                            <label for="student-month-select" class="form-label mb-0 fw-semibold text-muted">Select Month:</label>
                            <input type="month" id="student-month-select" class="form-control form-control-sm border-primary" style="width: 170px;" value="{{ date('Y-m') }}">
                        </div>
                    </div>

                    <!-- Modal Stats Cards -->
                    <div class="row g-3 mb-3">
                        <div class="col-md-3 col-6">
                            <div class="p-3 bg-light rounded text-center border">
                                <div class="text-muted small fw-semibold">Total Days</div>
                                <div class="fs-4 fw-bold text-dark mt-1" id="m-stat-total">0</div>
                            </div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="p-3 bg-success-subtle rounded text-center border border-success">
                                <div class="text-success small fw-semibold">Present Days</div>
                                <div class="fs-4 fw-bold text-success mt-1" id="m-stat-present">0</div>
                            </div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="p-3 bg-danger-subtle rounded text-center border border-danger">
                                <div class="text-danger small fw-semibold">Absent Days</div>
                                <div class="fs-4 fw-bold text-danger mt-1" id="m-stat-absent">0</div>
                            </div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="p-3 bg-secondary-subtle rounded text-center border border-secondary">
                                <div class="text-secondary small fw-semibold">Sundays</div>
                                <div class="fs-4 fw-bold text-secondary mt-1" id="m-stat-sundays">0</div>
                            </div>
                        </div>
                    </div>

                    <!-- Modal Loader -->
                    <div id="student-modal-loader" class="text-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2 text-muted fw-semibold">Fetching student attendance...</p>
                    </div>

                    <!-- Modal Table -->
                    <div id="student-modal-table-container" class="table-responsive" style="display: none; max-height: 400px; overflow-y: auto;">
                        <table class="table table-hover table-striped align-middle table-bordered text-center mb-0">
                            <thead class="table-light sticky-top" style="z-index: 1;">
                                <tr>
                                    <th style="width: 50px;">#</th>
                                    <th>Date</th>
                                    <th>Day</th>
                                    <th>Punch In Time</th>
                                    <th>Punch Out Time</th>
                                    <th>Device Name</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody id="student-attendance-tbody">
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('page_script_file')
    <script src="{{ asset('admin/assets/cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('admin/assets/cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js') }}"></script>
    <script src="{{ asset('admin/assets/cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js') }}"></script>
@endsection

@section('page_leavel_script')
    <script type="text/javascript">
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        var dtable = null;
        let activeStudentBioId = null;

        $(document).ready(function() {
            if ($('.select2').length > 0) {
                $('.select2').select2();
            }

            dtable = $('#yajra-datatables').DataTable({
                searching: true,
                processing: true,
                serverSide: true,
                order: [[1, 'asc']],
                ajax: {
                    url: "{{ route('daily-attendance.index') }}",
                    type: "GET",
                    data: function(d) {
                        d.course_id = $('#filter_course_id').val();
                        d.batch_id  = $('#filter_batch_id').val();
                        d.class_id  = $('#filter_class_id').val();
                        d.date      = $('#filter_date').val();
                        d.search    = $('input[type="search"]').val();
                    },
                    dataSrc: function(json) {
                        if (json.total_count !== undefined) {
                            $('#stat-total-students').text(json.total_count);
                            $('#stat-present-students').text(json.present_count);
                            $('#stat-absent-students').text(json.absent_count);
                        }
                        return json.data;
                    }
                },
                drawCallback: function(settings) {
                    var api = this.api();
                    var json = api.ajax.json();
                    if (json && json.total_count !== undefined) {
                        $('#stat-total-students').text(json.total_count);
                        $('#stat-present-students').text(json.present_count);
                        $('#stat-absent-students').text(json.absent_count);
                    }
                },
                columns: {!! json_encode($columns) !!},
                language: {
                    searchPlaceholder: 'Search by student name or biometric ID...',
                    emptyTable: 'No attendance records found for today.',
                    zeroRecords: 'No matching attendance records found.'
                },
                drawCallback: function(settings) {
                    var api = this.api();
                    var json = api.ajax.json();
                    if (json && json.is_sunday) {
                        $('#stat-total-students').text(0);
                        $('#stat-present-students').text(0);
                        $('#stat-absent-students').text(0);
                    } else if (json && json.total_count !== undefined) {
                        $('#stat-total-students').text(json.total_count);
                        $('#stat-present-students').text(json.present_count);
                        $('#stat-absent-students').text(json.absent_count);
                    }
                }
            });

            var allBatches = [
                @foreach ($batches as $batch)
                    { id: "{{ $batch->id }}", name: "{!! addslashes($batch->batch_name) !!}", course_id: "{{ $batch->course_id }}" },
                @endforeach
            ];

            var allClasses = [
                @foreach ($classes as $class)
                    { id: "{{ $class->id }}", name: "{!! addslashes($class->class) !!}" },
                @endforeach
            ];

            // 1. Course Selection -> Loads Batches & Enables/Resets Class
            $(document).on('change', '#filter_course_id', function() {
                var selectedCourseId = $(this).val();
                var $batchSelect = $('#filter_batch_id');
                var $classSelect = $('#filter_class_id');

                $batchSelect.empty();

                if (!selectedCourseId) {
                    $batchSelect.append('<option value="">Select Course</option>');
                    $batchSelect.prop('disabled', true).val('').trigger('change.select2');

                    $classSelect.empty().append('<option value="">Select Batch</option>');
                    $classSelect.prop('disabled', true).val('').trigger('change.select2');

                    dtable.draw();
                    return;
                }

                $batchSelect.prop('disabled', false);
                $batchSelect.append('<option value="">All Batches</option>');

                // Enable Class dropdown with all classes for the selected course
                $classSelect.prop('disabled', false).empty().append('<option value="">All Classes</option>');
                $.each(allClasses, function(i, item) {
                    $classSelect.append('<option value="' + item.id + '">' + item.name + '</option>');
                });
                $classSelect.trigger('change.select2');

                $.ajax({
                    type: 'POST',
                    url: "{{ route('get-batch') }}",
                    data: {
                        course_id: selectedCourseId,
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        if (response.status && response.data && response.data.length > 0) {
                            $.each(response.data, function(i, item) {
                                if (item.id && item.name) {
                                    $batchSelect.append('<option value="' + item.id + '">' + item.name + '</option>');
                                }
                            });
                        } else {
                            var filtered = allBatches.filter(b => b.course_id == selectedCourseId);
                            $.each(filtered, function(i, item) {
                                $batchSelect.append('<option value="' + item.id + '">' + item.name + '</option>');
                            });
                        }
                        $batchSelect.trigger('change.select2');
                        dtable.draw();
                    },
                    error: function() {
                        var filtered = allBatches.filter(b => b.course_id == selectedCourseId);
                        $.each(filtered, function(i, item) {
                            $batchSelect.append('<option value="' + item.id + '">' + item.name + '</option>');
                        });
                        $batchSelect.trigger('change.select2');
                        dtable.draw();
                    }
                });
            });

            // 2. Batch Selection -> Loads/Filters Classes
            $(document).on('change', '#filter_batch_id', function() {
                var selectedBatchId = $(this).val();
                var $classSelect = $('#filter_class_id');

                $classSelect.prop('disabled', false);
                $classSelect.empty().append('<option value="">All Classes</option>');

                $.each(allClasses, function(i, item) {
                    $classSelect.append('<option value="' + item.id + '">' + item.name + '</option>');
                });
                $classSelect.trigger('change.select2');

                if (selectedBatchId) {
                    $.ajax({
                        type: 'POST',
                        url: "{{ route('get-class-bybatch') }}",
                        data: {
                            batch_id: selectedBatchId,
                            _token: $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(response) {
                            if (response.status && response.data && response.data.length > 0) {
                                $classSelect.empty().append('<option value="">All Classes</option>');
                                $.each(response.data, function(i, item) {
                                    if (item.id && item.name) {
                                        $classSelect.append('<option value="' + item.id + '">' + item.name + '</option>');
                                    }
                                });
                                $classSelect.trigger('change.select2');
                            }
                            dtable.draw();
                        },
                        error: function() {
                            dtable.draw();
                        }
                    });
                } else {
                    dtable.draw();
                }
            });

            // 3. Class Change -> Redraw Table
            $(document).on('change', '#filter_class_id', function() {
                dtable.draw();
            });


            $(document).on('click', '.student-name-link', function(e) {
                e.preventDefault();
                activeStudentBioId = $(this).data('biometric-id');
                const studentName = $(this).data('student-name');

                $('#modal-student-name').text(studentName);
                $('#modal-biometric-id').text(activeStudentBioId);
                $('#student-month-select').val("{{ date('Y-m') }}");

                $('#studentDetailModal').modal('show');
                loadStudentMonthlyAttendance(activeStudentBioId, $('#student-month-select').val());
            });

            $('#student-month-select').on('change', function() {
                if (activeStudentBioId) {
                    loadStudentMonthlyAttendance(activeStudentBioId, $(this).val());
                }
            });
        });

        function loadStudentMonthlyAttendance(biometricId, month) {
            $('#student-modal-loader').show();
            $('#student-modal-table-container').hide();

            $.ajax({
                url: "{{ route('daily-attendance.student-detail') }}",
                type: "GET",
                data: {
                    biometric_id: biometricId,
                    month: month
                },
                success: function(response) {
                    $('#student-modal-loader').hide();
                    $('#student-modal-table-container').show();

                    if (response.status) {
                        $('#modal-student-name').text(response.student_name);
                        $('#m-stat-total').text(response.stats.total_days);
                        $('#m-stat-present').text(response.stats.present_days);
                        $('#m-stat-absent').text(response.stats.absent_days);
                        $('#m-stat-sundays').text(response.stats.sundays);

                        let tbodyHtml = '';
                        if (response.rows && response.rows.length > 0) {
                            response.rows.forEach(function(row, index) {
                                tbodyHtml += `<tr>
                                    <td>${index + 1}</td>
                                    <td>${row.date_formatted}</td>
                                    <td>${row.day_name}</td>
                                    <td>${row.in_time}</td>
                                    <td>${row.out_time}</td>
                                    <td>${row.device_name}</td>
                                    <td>${row.status_html}</td>
                                </tr>`;
                            });
                        } else {
                            tbodyHtml = '<tr><td colspan="7" class="text-center text-muted">No attendance logs found.</td></tr>';
                        }
                        $('#student-attendance-tbody').html(tbodyHtml);
                    }
                },
                error: function() {
                    $('#student-modal-loader').hide();
                    $('#student-modal-table-container').show();
                    $('#student-attendance-tbody').html('<tr><td colspan="7" class="text-center text-danger">Error loading student data.</td></tr>');
                }
            });
        }
    </script>
@endsection
