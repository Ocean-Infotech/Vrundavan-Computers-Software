@extends('software.layout.app')

@php
    $page_title = isset($modules['title']) ? $modules['title'] : 'Result Master';
    $route = isset($modules['route']) ? $modules['route'] : 'result';
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
            'show_add_btn' => true,
            'show_filter_btn' => false,
            'show_back_btn' => false,
        ])
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card shadow">
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="yajra-datatables" class="table table-hover dt-responsive nowrap" style="width: 100%">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Course</th>
                                    <th>Batch</th>
                                    <th>Class</th>
                                    <th>Semester</th>
                                    <th>Total Marks</th>
                                    <th class="text-center">Action</th>
                                </tr>
                            </thead>
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
        $(document).ready(function() {
            dtable = $('#yajra-datatables').DataTable({
                searching: true,
                processing: true,
                serverSide: true,
                order: [[0, 'desc']],
                ajax: {
                    "url": "{{ route($route . '.index') }}",
                    'beforeSend': function(request) {
                        request.setRequestHeader("X-CSRF-TOKEN", $('meta[name="csrf-token"]').attr('content'));
                    },
                    type: "GET",
                },
                columns: [
                    { data: 'DT_RowIndex', name: 'id', orderable: false, searchable: false },
                    { data: 'course_name', name: 'course_name' },
                    { data: 'batch_name', name: 'batch_name' },
                    { data: 'class_name', name: 'class_name' },
                    { data: 'semester', name: 'semester' },
                    { data: 'total_marks', name: 'total_marks' },
                    { data: 'action', name: 'action', orderable: false, searchable: false }
                ],
                language: {
                    searchPlaceholder: 'Search...',
                },
            });
        });
    </script>
    @include('software.includes.script-delete-record')
@endsection
