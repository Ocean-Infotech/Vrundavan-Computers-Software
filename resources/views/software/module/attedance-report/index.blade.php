@extends('software.layout.app')

@php
    $page_title = 'Attendance Report';
    $folder_path = isset($modules['folder_path']) ? $modules['folder_path'] : null;
    $route = isset($modules['route']) ? $modules['route'] : null;
@endphp
@section('title', $page_title)

@section('page_style_file')
    <link rel="stylesheet" href="{{ asset('admin/assets/cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css') }}">
    <link rel="stylesheet" href="{{ asset('admin/assets/cdn.datatables.net/responsive/2.2.9/css/responsive.bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('admin/assets/cdn.datatables.net/buttons/2.2.2/css/buttons.dataTables.min.css') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css">
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
            'show_print_btn' => true,
            'show_back_btn' => false,
        ])
    </div>

    <div class="row my-2">
        <div class="col-md-12 mb-3">
            <div class="card shadow-sm">
                <div class="card-body py-2">
                    <form id="filterForm">
                        <div class="row align-items-end">
                            <div class="col-md-3 mb-2">
                                <label class="form-label fw-semibold mb-1">Course</label>
                                <select id="course_id" class="form-control search_by_course select2 select_filter" data-append="search_by_course">
                                    <option value="">Select Course</option>
                                </select>
                            </div>
                            <div class="col-md-3 mb-2">
                                <label class="form-label fw-semibold mb-1">Batch</label>
                                <select id="batch_id" class="form-control search_by_batch select2 select_filter" data-append="search_by_batch">
                                    <option value="">Select Batch</option>
                                </select>
                            </div>
                            <div class="col-md-3 mb-2">
                                <label class="form-label fw-semibold mb-1">Class</label>
                                <select id="class_id" class="form-control search_by_class select2 select_filter" data-append="search_by_class">
                                    <option value="">Select Class</option>
                                </select>
                            </div>
                            <div class="col-md-3 mb-2">
                                <label class="form-label fw-semibold mb-1">Date Range</label>
                                <input type="text" id="date_range" class="form-control my_daterangepicker" placeholder="Select Date Range">
                                <input type="hidden" id="start_date" value="">
                                <input type="hidden" id="end_date" value="">
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="row my-3">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="yajra-datatables" class="table table-hover dt-responsive nowrap" style="width: 100%">
                            @if (isset($columns))
                                <thead>
                                    <tr>
                                        @foreach ($columns as $item)
                                            <th class="{{ $item?->className ?? '' }}">{{ ucfirst($item?->td_label ?? $item?->name) ?? '' }}</th>
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
    <script src="https://cdn.jsdelivr.net/npm/moment@2.29.4/moment.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
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

            $('.my_daterangepicker').daterangepicker({
                autoUpdateInput: false,
                alwaysShowCalendars: true,
                locale: {
                    format: 'DD/MM/YYYY',
                    applyLabel: 'Apply',
                    cancelLabel: 'Clear'
                }
            });

            $('.my_daterangepicker').on('apply.daterangepicker', function(ev, picker) {
                $('#start_date').val(picker.startDate.format('YYYY-MM-DD'));
                $('#end_date').val(picker.endDate.format('YYYY-MM-DD'));
                $(this).val(picker.startDate.format('DD/MM/YYYY') + ' to ' + picker.endDate.format('DD/MM/YYYY'));
                dtable.draw();
            });

            $('.my_daterangepicker').on('cancel.daterangepicker', function(ev, picker) {
                $('#start_date').val('');
                $('#end_date').val('');
                $(this).val('');
                dtable.draw();
            });

            dtable = $('#yajra-datatables').DataTable({
                searching: true,
                processing: true,
                serverSide: true,
                order: [],
                ajax: function(data, callback) {
                    data.start_date = $('#start_date').val();
                    data.end_date = $('#end_date').val();
                    data.course_id = $('#course_id').val();
                    data.batch_id = $('#batch_id').val();
                    data.class_id = $('#class_id').val();

                    $.ajax({
                        url: "{{ route('attedance-report.index') }}",
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
                    emptyTable: "No attendance records found."
                }
            });

            $('.select_filter').on('change', function() {
                dtable.draw();
            });

            $("#print_btn").click(function(e) {
                e.preventDefault();
                var start = $('#start_date').val();
                if (!start) {
                    alert("Please select a Date Range first!");
                    return;
                }
                const params = new URLSearchParams({
                    start_date: start,
                    end_date: $('#end_date').val(),
                    course_id: $('#course_id').val() || '',
                    batch_id: $('#batch_id').val() || '',
                    class_id: $('#class_id').val() || ''
                });
                window.open("{{ route('attedance-report.print') }}?" + params.toString(), '_blank');
            });

            $("#exportExcelBtn").click(function(e) {
                e.preventDefault();
                var start = $('#start_date').val();
                if (!start) {
                    alert("Please select a Date Range first!");
                    return;
                }
                const params = new URLSearchParams({
                    start_date: start,
                    end_date: $('#end_date').val(),
                    course_id: $('#course_id').val() || '',
                    batch_id: $('#batch_id').val() || '',
                    class_id: $('#class_id').val() || ''
                });
                window.location.href = "{{ route('attedance-report.export-excel') }}?" + params.toString();
            });
        });
    </script>

    @include('software.utils.getCourse')
    @include('software.utils.getBatchByCourseid')
    @include('software.utils.getClassByBatch')
@endsection
