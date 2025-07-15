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
            'show_add_btn' => false,
            'show_filter_btn' => true,
            'show_export_btns' => true,
            'show_excal_btn' => true,
            'show_print_btn' => true,
            'show_back_btn' => false,
        ])
    </div>

    <!-- @include('software.partials.flash_messages') -->
    <div class="row my-3">
        <div class="col-md-12 mb-5" id="filter_section">
            <div class="card">
                <div class="card-body">
                    <div class="row" id="filter_section">
                        <div class="col-md-3 mb-2 col-sm-12">
                            <div class="form-group">
                                <label for="student_filter">Filter by Student (ID, Name, Contact)</label>
                                <input type="text" id="student_filter" name="student_filter"
                                    class="form-control select_filter" placeholder="Enter student ID, name or contact">
                            </div>
                        </div>

                        <div class="col-md-2 col-sm-12 mb-3">
                            <div class="form-group">
                                <label class="form-label">Filter by Course </label>
                                <select id="course_id" name="course_id"
                                    class="form-control search_by_course select2 select_filter"
                                    data-append="search_by_course" autofocus>
                                    <option value="">Filter by Course</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-2 col-sm-12 mb-3">
                            <div class="form-group">
                                <label class="form-label d-flex justify-content-between align-items-center">Filter by
                                    Batch</label>
                                <select id="batch_id" name="batch_id"
                                    class="form-control search_by_batch select2 select_filter" data-append="search_by_batch"
                                    autofocus>
                                    <option value="">Filter by Batch</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-2 col-sm-12 mb-3">
                            <div class="form-group">
                                <label class="form-label d-flex justify-content-between align-items-center">Filter by
                                    Class</label>
                                <select id="class_id" name="class_id"
                                    class="form-control search_by_class select2 select_filter" data-append="search_by_class"
                                    autofocus>
                                    <option value="">Filter by Class</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-2 col-sm-12 mb-3">
                            <div class="form-group">
                                <label class="form-label d-flex justify-content-between align-items-center">Filter by
                                    Semester</label>
                                <select id="semester_id" name="semester_id"
                                    class="form-control search_by_semester select2 select_filter"
                                    data-append="search_by_semester" autofocus>
                                    <option value="">Filter by Semester</option>
                                </select>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="yajra-datatables" class="display table table-hover">
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

        $(document).ready(function() {
            $('.select2').select2();

            if ($.fn.DataTable.isDataTable('#yajra-datatables')) {
                $('#yajra-datatables').DataTable().clear().destroy();
            }

            dtable = $('#yajra-datatables').DataTable({
                processing: true,
                serverSide: true,
                searching: false,

                order: [
                    [1, 'desc']
                ],
                ajax: {
                    url: "{{ route($route . '.index') }}",
                    type: "GET",
                    data: function(data) {
                        data.student_filter = $('#student_filter').val();
                        data.course_id = $('#course_id').val();
                        data.batch_id = $('#batch_id').val();
                        data.class_id = $('#class_id').val();
                        data.semester_id = $('#semester_id').val();
                    }
                },
                columns: {!! json_encode($columns) !!},
                language: {
                    searchPlaceholder: 'Search...',
                },
            });
            $(document).ready(function() {
                $('#filter_section').hide();
                $("#show_filter").click(function() {

                    if ($('#filter_section').is(':hidden')) {
                        $('#filter_section').show();
                    } else {
                        $('#filter_section').hide();
                    }
                });

                $("#cilory_filter").click(function() {
                    $('.select_filter').val(null).trigger('change');
                    $('.search').val('');
                    dtable.draw();
                });
            });

            $('.select_filter').on('change keyup', function() {
                dtable.draw();
            });
        });
    </script>
    <script>
        $('#exportExcelBtn').on('click', function() {

            var studentFilter = $('#student_filter').val();
            var courseId = $('#course_id').val();
            var batch_id = $('#batch_id').val();
            var class_id = $('#class_id').val();
            var semester_id = $('#semester_id').val();

            var queryString = '?student_filter=' + encodeURIComponent(studentFilter) +
                              '&course_id=' + encodeURIComponent(courseId) +
                              '&batch_id=' + encodeURIComponent(courseId) + 
                              '&class_id=' + encodeURIComponent(class_id) +
                              '&semester_id=' + encodeURIComponent(semester_id);

            // console.log("L-180 Excal Rwdirect","Export URL: ", "{{ route($route . '.export-excel') }}" + queryString);
            // return;
            window.location.href = "{{ route($route . '.export-excel') }}" + queryString;
        });

        $('#print_btn').on('click', function(e) {
            e.preventDefault();

            var studentFilter = $('#student_filter').val();
            var courseId = $('#course_id').val();
            var batch_id = $('#batch_id').val();
            var class_id = $('#class_id').val();
            var semester_id = $('#semester_id').val();

            var queryString = '?student_filter=' + encodeURIComponent(studentFilter) +
                              '&course_id=' + encodeURIComponent(courseId) +
                              '&batch_id=' + encodeURIComponent(courseId) + 
                              '&class_id=' + encodeURIComponent(class_id) +
                              '&semester_id=' + encodeURIComponent(semester_id);

            var url = "{{ route($route . '.print') }}" + queryString;

            // Redirect to new tab
            window.open(url, '_blank');
        });
    </script>

    @include('software.utils.getCourse')
    @include('software.utils.getBatchByCourseid')
    @include('software.utils.getClassByBatch')
    @include('software.utils.getSamasterByCourseid')
    @include('software.utils.getAdmission')
    @include('software.includes.script-delete-record')
    @include('software.includes.script-restore-record')
    @include('software.includes.script-update-status')
@endsection
