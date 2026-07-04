@extends('software.layout.app')

@php
    $page_title = 'Other List Selection';
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
            'show_export_btns' => true,
            'show_excal_btn' => true,
            'show_print_btn' => false,
            'show_back_btn' => false,
        ])
    </div>

    <div class="row my-3">
        <div class="col-md-12 mb-4">
            <form id="filterForm" action="{{ route($route . '.print-list') }}" method="GET" target="_blank">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <div class="row align-items-end">
                            <div class="col-md-3 col-sm-12 mb-3">
                                <label class="form-label">Heading</label>
                                <input type="text" id="page_heading" name="page_heading" class="form-control"
                                    placeholder="Ex. Exam Form, Tour List">
                            </div>
                            <div class="col-md-3 col-sm-12 mb-3">
                                <label class="form-label">Course</label>
                                <select id="course_id" name="course_id" class="form-control search_by_course select2"
                                    data-append="search_by_course">
                                    <option value="">Select Course</option>
                                </select>
                            </div>
                            <div class="col-md-3 col-sm-12 mb-3">
                                <label class="form-label">Batch</label>
                                <select id="batch_id" name="batch_id" class="form-control search_by_batch select2"
                                    data-append="search_by_batch">
                                    <option value="">Select Batch</option>
                                </select>
                            </div>
                            <div class="col-md-3 col-sm-12 mb-3">
                                <label class="form-label">Semester</label>
                                <select id="semester_id" name="semester_id" class="form-control search_by_semester select2"
                                    data-append="search_by_semester">
                                    <option value="">Select Semester</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label class="form-label">Student Selection</label>
                            <select id="student_ids" name="student_ids[]" class="form-control select2" multiple form="filterForm">
                                <option value="all">All Students</option>
                            </select>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table id="yajra-datatables" class="table table-hover dt-responsive" style="width: 100%">
                            @if (isset($columns))
                                <thead>
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

        <div class="col-md-12 mt-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0">List History</h5>
                        <small class="text-muted">Shows when each heading was exported.</small>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered table-striped align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Heading</th>
                                    <th>Course</th>
                                    <th>Batch</th>
                                    <th>Semester</th>
                                    <th>Type</th>
                                    <th>Students</th>
                                    <th>Date Time</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse(($histories ?? collect()) as $index => $history)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $history->page_heading ?: 'Other List Selection' }}</td>
                                        <td>{{ $history->course->course_name ?? '-' }}</td>
                                        <td>{{ $history->batch->batch_name ?? '-' }}</td>
                                        <td>{{ $history->semester_id ?? '-' }}</td>
                                        <td class="text-uppercase">{{ $history->action_type ?? '-' }}</td>
                                        <td>{{ $history->student_count ?? 0 }}</td>
                                        <td>{{ optional($history->created_at)->format('d-m-Y h:i A') ?? '-' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center text-danger">
                                            No history found yet. Export a list to create history.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
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

        function hasRequiredFilters() {
            return Boolean($('#course_id').val() && $('#batch_id').val() && $('#semester_id').val());
        }

        function hasStudentSelection() {
            return ($('#student_ids').val() || []).length > 0;
        }

        function hasPageHeading() {
            return Boolean($.trim($('#page_heading').val()));
        }

        function showWarning(message) {
            if (typeof toastr !== 'undefined') {
                toastr.warning(message);
                return;
            }

            alert(message);
        }

        function refreshHistorySoon() {
            window.setTimeout(function() {
                window.location.reload();
            }, 1200);
        }

        function resetStudentOptions() {
            $('#student_ids').html('<option value="all">All Students</option>').val(null).trigger('change');
        }

        function loadStudentOptions() {
            const courseId = $('#course_id').val();
            const batchId = $('#batch_id').val();

            resetStudentOptions();

            if (!courseId || !batchId) {
                return;
            }

            $.ajax({
                url: "{{ route($route . '.student-options') }}",
                type: "GET",
                data: {
                    course_id: courseId,
                    batch_id: batchId
                },
                success: function(response) {
                    let options = "<option value='all'>All Students</option>";

                    if (response.status && response.data && response.data.length > 0) {
                        $.each(response.data, function(index, student) {
                            options += "<option value='" + student.id + "'>" + student.name + "</option>";
                        });
                    }

                    $('#student_ids').html(options).val(null).trigger('change');
                }
            });
        }

        $(document).ready(function() {
            $('.select2').select2({
                width: '100%',
                placeholder: 'All Students or Select Student',
                allowClear: true,
                closeOnSelect: false
            });

            dtable = $('#yajra-datatables').DataTable({
                searching: false,
                processing: true,
                serverSide: true,
                paging: false,
                info: false,
                lengthChange: false,
                order: [],
                ajax: function(data, callback) {
                    data.course_id = $('#course_id').val();
                    data.batch_id = $('#batch_id').val();
                    data.semester_id = $('#semester_id').val();
                    data.student_ids = $('#student_ids').val();

                    if (!hasRequiredFilters() || !hasStudentSelection()) {
                        callback({
                            draw: data.draw,
                            recordsTotal: 0,
                            recordsFiltered: 0,
                            data: []
                        });
                        return;
                    }

                    $.ajax({
                        url: "{{ route($route . '.index') }}",
                        type: "GET",
                        data: data,
                        beforeSend: function(request) {
                            request.setRequestHeader("X-CSRF-TOKEN", $('meta[name="csrf-token"]').attr('content'));
                        },
                        success: function(response) {
                            callback(response);
                        }
                    });
                },
                columns: {!! json_encode($columns) !!},
                language: {
                    searchPlaceholder: 'Search...',
                    emptyTable: ""
                },
                drawCallback: function() {
                    let api = this.api();
                    let data = api.rows({ page: 'current' }).data();

                    if (!hasRequiredFilters() || !hasStudentSelection()) {
                        $('#yajra-datatables tbody').empty();
                        return;
                    }

                    if (data.length === 0) {
                        $('#yajra-datatables tbody').html(`
                            <tr class="odd">
                                <td valign="top" colspan="{{ count($columns) }}" class="dataTables_empty text-center text-danger">
                                    No students found for selected filters.
                                </td>
                            </tr>
                        `);
                    }
                }
            });

            $('#course_id, #batch_id').on('change', function() {
                loadStudentOptions();
                dtable.draw();
            });

            $('#semester_id, #student_ids').on('change', function() {
                dtable.draw();
            });

            $('#student_ids').on('select2:select', function(e) {
                let selectedId = e.params.data.id;
                let values = $(this).val() || [];

                if (selectedId === 'all') {
                    $(this).val(['all']).trigger('change');
                    return;
                }

                if (values.includes('all')) {
                    values = values.filter(function(value) {
                        return value !== 'all';
                    });
                    $(this).val(values).trigger('change');
                }
            });

            $('#student_ids').on('select2:unselect', function(e) {
                let unselectedId = e.params.data.id;
                let values = $(this).val() || [];

                if (unselectedId === 'all') {
                    $(this).val(null).trigger('change');
                    return;
                }

                if (values.length === 0) {
                    $(this).val(null).trigger('change');
                }
            });

            $("#exportExcelBtn").click(function(e) {
                e.preventDefault();

                if (!hasPageHeading()) {
                    showWarning('Please enter Page Heading.');
                    return;
                }

                if (!hasRequiredFilters()) {
                    showWarning('Please select Course, Batch and Semester.');
                    return;
                }

                if (!hasStudentSelection()) {
                    showWarning('Please select All Students or particular student.');
                    return;
                }

                const params = new URLSearchParams({
                    page_heading: $('#page_heading').val(),
                    course_id: $('#course_id').val(),
                    batch_id: $('#batch_id').val(),
                    semester_id: $('#semester_id').val()
                });

                const studentIds = $('#student_ids').val() || [];
                studentIds.forEach(function(studentId) {
                    params.append('student_ids[]', studentId);
                });

                window.location.href = "{{ route($route . '.export-excel') }}?" + params.toString();
                refreshHistorySoon();
            });

            $('#filterForm').on('submit', function(e) {
                e.preventDefault();
            });
        });
    </script>

    @include('software.utils.getCourse')
    @include('software.utils.getBatchByCourseid')
    @include('software.utils.getSamasterByCourseid')
@endsection
