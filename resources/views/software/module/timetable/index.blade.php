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
    {{-- <link href="{{ asset('public/assets/admin/plugins/datatable/dataTables.bootstrap4.min.css') }}" rel="stylesheet" type="text/css"> --}}
    <!--datatable css-->
    <link rel="stylesheet" href="{{ asset('admin/assets/cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css') }}">
    <!--datatable responsive css-->
    <link rel="stylesheet"
        href="{{ asset('admin/assets/cdn.datatables.net/responsive/2.2.9/css/responsive.bootstrap.min.css') }}">

    <link rel="stylesheet" href="{{ asset('admin/assets/cdn.datatables.net/buttons/2.2.2/css/buttons.dataTables.min.css') }}">
@endsection

@section('content')
    <div class="d-flex justify-content-lg-between px-1">
        @include('software.includes.breadcrumb', [
            'breadcrumbArray' => [['title' => $page_title, 'url' => '']],
            'route' => $route,
            'show_add_btn' => true,
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
            <div class="card shadow-sm">
                <div class="card-body py-3">
                    <form id="timetableFilterForm">
                        <div class="row align-items-end">
                            <div class="col-md-3 col-sm-12 mb-2">
                                <label class="form-label fw-semibold mb-1">Course</label>
                                <select id="course" name="course_id" class="form-control search_by_course select2 select_filter" data-append="search_by_course">
                                    <option value="">Select Course</option>
                                </select>
                            </div>
                            <div class="col-md-3 col-sm-12 mb-2">
                                <label class="form-label fw-semibold mb-1">Batch</label>
                                <select id="batch" name="batch_id" class="form-control search_by_batch select2 select_filter" data-append="search_by_batch">
                                    <option value="">Select Batch</option>
                                </select>
                            </div>
                            <div class="col-md-3 col-sm-12 mb-2">
                                <label class="form-label fw-semibold mb-1">Class</label>
                                <select id="class" name="class_id" class="form-control search_by_class select2 select_filter" data-append="search_by_class">
                                    <option value="">Select Class</option>
                                </select>
                            </div>
                            <div class="col-md-3 col-sm-12 mb-2">
                                <button type="button" id="reset_filter_btn" class="btn btn-secondary w-100">
                                    <i class="fa-solid fa-rotate-right me-1"></i> Reset Filter
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- @include('software.partials.flash_messages') -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="yajra-datatables" class="table table-hover dt-responsive nowrap" style="width: 100%">
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

    <!-- Student List Modal -->
    <div class="modal fade" id="studentModal" tabindex="-1" aria-labelledby="studentModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="studentModalLabel"><i class="fa-solid fa-users me-2"></i>Enrolled Students List</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="student-modal-loader" class="text-center py-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                    <div id="student-modal-content" class="table-responsive" style="display: none;">
                        <table class="table table-bordered table-striped" id="students-table">
                            <thead class="table-dark">
                                <tr>
                                    <th>#</th>
                                    <th>GR No</th>
                                    <th>Student Name</th>
                                    <th>Mobile No</th>
                                </tr>
                            </thead>
                            <tbody id="student-table-body">
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
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
    @include('software.utils.getClass')

    <script type="text/javascript">
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        var dtable = null;
        $(document).ready(function() {
            dtable = $('#yajra-datatables').DataTable({
                searching: true,
                processing: true,
                serverSide: true,
                order: [
                    [0, 'desc']
                ],
                ajax: {
                    "url": "{{ route($route . '.index') }}",
                    'beforeSend': function(request) {
                        request.setRequestHeader("X-CSRF-TOKEN", $('meta[name="csrf-token"]').attr('content'));
                    },
                    type: "GET",
                    data: function(data) {
                        data.course_id = $('.search_by_course').val();
                        data.batch_id = $('.search_by_batch').val();
                        data.class_id = $('.search_by_class').val();
                    },
                },
                columns: {!! json_encode($columns) !!},
                language: {
                    searchPlaceholder: 'Search...',
                },
            });

            $(document).on('change', '.select_filter', function() {
                dtable.draw();
            });

            $('#reset_filter_btn').on('click', function() {
                $('.search_by_course').val('').trigger('change');
                $('.search_by_batch').val('').trigger('change');
                $('.search_by_class').val('').trigger('change');
                dtable.draw();
            });

            $(document).on('click', '.view-students-btn', function() {
                var timetableId = $(this).data('id');
                $('#studentModal').modal('show');
                $('#student-modal-loader').show();
                $('#student-modal-content').hide();

                $.ajax({
                    url: "{{ route('timetable.students', ':id') }}".replace(':id', timetableId),
                    type: "GET",
                    success: function(response) {
                        $('#student-modal-loader').hide();
                        $('#student-modal-content').show();
                        var tbody = $('#student-table-body');
                        tbody.empty();

                        if (response.students && response.students.length > 0) {
                            $.each(response.students, function(index, student) {
                                tbody.append(
                                    '<tr>' +
                                    '<td>' + (index + 1) + '</td>' +
                                    '<td>' + (student.gr_no || '-') + '</td>' +
                                    '<td>' + (student.name || '-') + '</td>' +
                                    '<td>' + (student.mobile || '-') + '</td>' +
                                    '</tr>'
                                );
                            });
                        } else {
                            tbody.append('<tr><td colspan="4" class="text-center">No active students found for this Course, Batch & Class.</td></tr>');
                        }
                    },
                    error: function() {
                        $('#student-modal-loader').hide();
                        $('#student-modal-content').show();
                        $('#student-table-body').html('<tr><td colspan="4" class="text-center text-danger">Failed to fetch students.</td></tr>');
                    }
                });
            });
        });
    </script>

    @include('software.includes.script-delete-record')
@endsection
