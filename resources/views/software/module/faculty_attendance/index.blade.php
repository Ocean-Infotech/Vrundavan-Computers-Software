@extends('software.layout.app')

@php
    $page_title = isset($modules['title']) ? $modules['title'] : null;
    $folder_path = isset($modules['folder_path']) ? $modules['folder_path'] : null;
    $route = isset($modules['route']) ? $modules['route'] : null;
    $permisstion_prefix = isset($modules['permisstion_prefix']) ? $modules['permisstion_prefix'] : null;
    $i = 0;
@endphp
@section('title', $page_title)

@section('page_style_file')
    <!--datatable css-->
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

    <div class="row my-3">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="yajra-datatables" class="table table-hover dt-responsive">
                            @if (isset($columns))
                                <thead>
                                    <tr>
                                        @foreach ($columns as $item)
                                            <th class="{{ $item?->className ?? '' }}">
                                                {{ ucfirst($item?->td_label ?? $item?->name) ?? '' }}</th>
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

    <!-- Faculty Monthly Attendance Modal -->
    <div class="modal fade" id="facultyAttendanceModal" tabindex="-1" aria-labelledby="facultyAttendanceModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content shadow-lg border-0">
                <div class="modal-header bg-light">
                    <h5 class="modal-title fw-bold text-dark" id="facultyAttendanceModalLabel">
                        <i class="fa-solid fa-user-clock text-primary me-2"></i>
                        Monthly Attendance Details
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 pb-2 border-bottom">
                        <div>
                            <h4 class="mb-1 fw-bold text-primary" id="modal-faculty-name">-</h4>
                            <span class="badge bg-label-info fs-6">Biometric ID: <span id="modal-biometric-id">-</span></span>
                        </div>
                        <div class="d-flex align-items-center gap-2 mt-2 mt-sm-0">
                            <label for="faculty-month-select" class="form-label mb-0 fw-semibold text-muted">Select Month:</label>
                            <input type="month" id="faculty-month-select" class="form-control form-control-sm border-primary" style="width: 170px;" value="{{ date('Y-m') }}">
                        </div>
                    </div>

                    <!-- Stats Cards -->
                    <div class="row g-3 mb-3" id="faculty-modal-stats">
                        <div class="col-md-3 col-6">
                            <div class="p-3 bg-light rounded text-center border">
                                <div class="text-muted small fw-semibold">Total Days in Month</div>
                                <div class="fs-4 fw-bold text-dark mt-1" id="stat-total-days">0</div>
                            </div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="p-3 bg-success-subtle rounded text-center border border-success">
                                <div class="text-success small fw-semibold">Present Days</div>
                                <div class="fs-4 fw-bold text-success mt-1" id="stat-present-days">0</div>
                            </div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="p-3 bg-danger-subtle rounded text-center border border-danger">
                                <div class="text-danger small fw-semibold">Absent Days</div>
                                <div class="fs-4 fw-bold text-danger mt-1" id="stat-absent-days">0</div>
                            </div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="p-3 bg-secondary-subtle rounded text-center border border-secondary">
                                <div class="text-secondary small fw-semibold">Sundays</div>
                                <div class="fs-4 fw-bold text-secondary mt-1" id="stat-sundays">0</div>
                            </div>
                        </div>
                    </div>

                    <!-- Loading Spinner -->
                    <div id="faculty-modal-loader" class="text-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2 text-muted fw-semibold">Fetching attendance details...</p>
                    </div>

                    <!-- Table Container -->
                    <div id="faculty-modal-table-container" class="table-responsive" style="display: none; max-height: 420px; overflow-y: auto;">
                        <table class="table table-hover table-striped align-middle table-bordered text-center mb-0">
                            <thead class="table-light sticky-top" style="z-index: 1;">
                                <tr>
                                    <th style="width: 50px;">#</th>
                                    <th>Date</th>
                                    <th>Day</th>
                                    <th>Punch In Time</th>
                                    <th>Punch Out Time</th>
                                    <th>Total Duration</th>
                                    <th>Device Name</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody id="faculty-attendance-tbody">
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
    <!--datatable js-->
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
        let currentBiometricId = null;

        $(document).ready(function() {
            dtable = $('#yajra-datatables').DataTable({
                searching: true,
                processing: true,
                serverSide: true,
                order: [
                    [2, 'desc']
                ],
                ajax: {
                    "url": "{{ route($route . '.index') }}",
                    'beforeSend': function(request) {
                        request.setRequestHeader("X-CSRF-TOKEN", $('meta[name="csrf-token"]').attr('content'));
                    },
                    type: "GET",
                    data: function(data) {
                        data.search = $('input[type="search"]').val();
                    },
                },
                columns: {!! json_encode($columns) !!},
                language: {
                    searchPlaceholder: 'Search...',
                },
            });

            $(document).on('click', '.faculty-name-link', function(e) {
                e.preventDefault();
                currentBiometricId = $(this).data('biometric-id');
                const facultyName = $(this).data('faculty-name');
                
                $('#modal-faculty-name').text(facultyName);
                $('#modal-biometric-id').text(currentBiometricId);
                $('#faculty-month-select').val("{{ date('Y-m') }}");

                $('#facultyAttendanceModal').modal('show');
                loadFacultyMonthlyAttendance(currentBiometricId, $('#faculty-month-select').val());
            });

            $('#faculty-month-select').on('change', function() {
                if (currentBiometricId) {
                    loadFacultyMonthlyAttendance(currentBiometricId, $(this).val());
                }
            });
        });

        $('input[name="search"]').keyup(function() {
            dtable.draw();
        });

        function loadFacultyMonthlyAttendance(biometricId, month) {
            $('#faculty-modal-loader').show();
            $('#faculty-modal-table-container').hide();

            $.ajax({
                url: "{{ route('faculty-attendance.monthly-details') }}",
                type: "GET",
                data: {
                    biometric_id: biometricId,
                    month: month
                },
                success: function(response) {
                    $('#faculty-modal-loader').hide();
                    $('#faculty-modal-table-container').show();

                    if (response.status) {
                        $('#modal-faculty-name').text(response.faculty_name);
                        $('#stat-total-days').text(response.stats.total_days);
                        $('#stat-present-days').text(response.stats.present_days);
                        $('#stat-absent-days').text(response.stats.absent_days);
                        $('#stat-sundays').text(response.stats.sundays);

                        let tbodyHtml = '';
                        if (response.rows && response.rows.length > 0) {
                            response.rows.forEach(function(row, index) {
                                const rowClass = row.is_today ? 'table-warning fw-bold' : (row.is_sunday ? 'table-light' : '');
                                tbodyHtml += `<tr class="${rowClass}">
                                    <td>${index + 1}</td>
                                    <td>${row.date_formatted}</td>
                                    <td>${row.day_name}</td>
                                    <td>${row.in_time}</td>
                                    <td>${row.out_time}</td>
                                    <td>${row.duration}</td>
                                    <td>${row.device_name}</td>
                                    <td>${row.status_html}</td>
                                </tr>`;
                            });
                        } else {
                            tbodyHtml = '<tr><td colspan="8" class="text-center text-muted">No attendance records found for this month.</td></tr>';
                        }
                        $('#faculty-attendance-tbody').html(tbodyHtml);
                    }
                },
                error: function(xhr) {
                    $('#faculty-modal-loader').hide();
                    $('#faculty-modal-table-container').show();
                    $('#faculty-attendance-tbody').html('<tr><td colspan="8" class="text-center text-danger">Failed to load data. Please try again.</td></tr>');
                }
            });
        }
    </script>
@endsection

