@extends('software.layout.app')

@php
    $page_title = 'Other List';
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

        $(document).ready(function() {
            $('.select2').select2({
                width: '100%'
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

                    if (!hasRequiredFilters()) {
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

                    if (!hasRequiredFilters()) {
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

            $('#course_id, #batch_id, #semester_id').on('change', function() {
                dtable.draw();
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

                const params = new URLSearchParams({
                    page_heading: $('#page_heading').val(),
                    course_id: $('#course_id').val(),
                    batch_id: $('#batch_id').val(),
                    semester_id: $('#semester_id').val()
                });

                window.location.href = "{{ route($route . '.export-excel') }}?" + params.toString();
            });

            $('#filterForm').on('submit', function(e) {
                e.preventDefault();

                if (!hasPageHeading()) {
                    showWarning('Please enter Page Heading.');
                    return;
                }

                if (!hasRequiredFilters()) {
                    showWarning('Please select Course, Batch and Semester.');
                    return;
                }
            });
        });
    </script>

    @include('software.utils.getCourse')
    @include('software.utils.getBatchByCourseid')
    @include('software.utils.getSamasterByCourseid')
@endsection
