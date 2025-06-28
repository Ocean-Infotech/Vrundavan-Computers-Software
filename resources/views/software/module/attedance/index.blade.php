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
    'show_api_attendance' => true,
    'show_filter_btn' => true,
    'show_export_btns' => false,
    'show_excal_btn' => false,
    'show_print_btn' => false,
    'show_back_btn' => false,
    ])
</div>

<div class="row my-3">
    <div class="col-md-12 mb-5" id="filter_section" style="display: none;">
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 mb-2 col-sm-12">
                        <div class="form-group">
                            <label class="form-label">Filter by Type</label>
                            <select id="type" name="type"
                                class="form-control search_by_type select2"
                                data-append="search_by_type">
                                <option value="">Filter by Type</option>
                                <option value="1">Student</option>
                                <option value="2">Class</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3 mb-2 col-sm-12">
                        <div class="form-group">
                            <label for="register_no">Filter by Student</label>

                            <!-- <input list="registers_id" id="register_id" name="register_id" class="form-control select_filter
                            @error('register_id') is-invalid @enderror" placeholder="Enter Student Name Or ID"> -->
                            <select id="registers_id" name="registers_id" class="form-control select2 select_filter">
                                <option value="">Filter by Student</option>
                                @foreach ($admissions as $admission)
                                <option value="{{ $admission->id }}">
                                    {{ $admission->first_name." ".$admission->father_name." ".$admission->last_name }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3 mb-2 col-sm-12">
                        <div class="form-group">
                            <label for="register_no">Filter by Batch</label>

                            <!-- <input list="batch_id" id="register_id" name="batch_id" class="form-control select_filter
                            @error('class_id') is-invalid @enderror" placeholder="Enter Batch Name"> -->
                            <select id="batch_id" name="batch_id" class="form-control select2 select_filter">
                                <option value="">Filter by Batch</option>
                                @foreach ($classes as $classrow)
                                <option value="{{ $classrow->id }}">
                                    {{ $classrow->batch_name }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3 mb-2">
                        <label class="form-label">Filter by Inquiry Date</label>
                        <input type="text" id="selected_date" name="selected_date"
                            class="form-control" placeholder="Select inquiry date range"
                            autocomplete="off" />
                        <input type="hidden" id="selectedStartDate" />
                        <input type="hidden" id="selectedEndDate" />
                    </div>
                    <div class="col-md-1 text-center">
                        <button type="button" title="Cilory Filter" id="cilory_filter"
                            class="btn btn-outline-danger btn-icon ms-75 me-75 mt-4"><i class="ti ti-x"></i></button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <!-- <table id="yajra-datatables" class="display table table-hover"> -->
                    <table id="yajra-datatables" class="table table-striped table-hover dt-responsive nowrap" style="width: 100%;">
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
<!--datatable js-->
<script src="{{ asset('admin/assets/cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('admin/assets/cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js') }}"></script>
<script src="{{ asset('admin/assets/cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/moment@2.29.4/moment.min.js"></script>
<!-- Daterangepicker JS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css">

<script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>

<!-- <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script> -->
{{-- <script src="{{ asset('admin/assets/cdn.datatables.net/buttons/2.2.2/js/dataTables.buttons.min.js') }}"></script>
<script src="{{ asset('admin/assets/cdn.datatables.net/buttons/2.2.2/js/buttons.print.min.js') }}"></script>
<script src="{{ asset('admin/assets/cdn.datatables.net/buttons/2.2.2/js/buttons.html5.min.js') }}"></script>
<script src="{{ asset('admin/assets/cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js') }}"></script>
<script src="{{ asset('admin/assets/cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js') }}"></script>
<script src="{{ asset('admin/assets/cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js') }}"></script> --}}

{{-- <script src="{{ asset('admin/assets/js/pages/datatables.init.js') }}"></script> --}}
@endsection

@section('page_leavel_script')
<script>
    // let filterInquiryDateRange = [];

    // const filterByInquiryDate = flatpickr("#filter_by_inquiry_date", {
    //     mode: "range",
    //     dateFormat: "d-m-Y",
    //     nge: function(selectedDates) {
    //         filterInquiryDateRange = selectedDates;
    //         dtable.draw();
    //     }
    // });
    $(function() {
        var start = moment(); // Today
        var end = moment(); // Today

        $('#selected_date').daterangepicker({
            startDate: start,
            endDate: end,
            autoUpdateInput: true, // Automatically fills the input field
            locale: {
                format: 'DD/MM/YYYY'
            },
            ranges: {
                'Today': [moment()],
                'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                'This Month': [moment().startOf('month'), moment().endOf('month')],
                'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1,
                    'month').endOf('month')]
            }
        }, function(start, end) {
            $('#selected_date').val(start.format('DD/MM/YYYY') + ' to ' + end.format('DD/MM/YYYY'));
            $('#selectedStartDate').val(start.format('YYYY-MM-DD'));
            $('#selectedEndDate').val(end.format('YYYY-MM-DD'));

            var selectedStartDate = start.format('YYYY-MM-DD');
            var selectedEndDate = end.format('YYYY-MM-DD');

            dtable.search(selectedStartDate).draw();
            dtable.search(selectedEndDate).draw();

        });

        // Set default value in input
        $('#selected_date').val(start.format('DD/MM/YYYY') + ' to ' + end.format('DD/MM/YYYY'));
        $('#selectedStartDate').val(start.format('YYYY-MM-DD'));
        $('#selectedEndDate').val(end.format('YYYY-MM-DD'));
    });
</script>
<script>

</script>
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
                [1, 'desc']
            ],
            ajax: {
                "url": "{{ route($route . '.index') }}",
                'beforeSend': function(request) {
                    request.setRequestHeader("X-CSRF-TOKEN", $('meta[name="csrf-token"]').attr(
                        'content'));
                },
                type: "GET",
                data: function(data) {
                    data.search = $('input[type="search"]').val();
                    data.filter_registers_id = $('select[name="registers_id"] option:selected').val();
                    data.filter_batch_id = $('select[name="batch_id"] option:selected').val();
                    data.selectedStartDate = $('#selectedStartDate').val();
                    data.selectedEndDate = $('#selectedEndDate').val();

                },
            },
            columns: {!!json_encode($columns) !!},
            language: {
                searchPlaceholder: 'Search...',
            },
            // dom:'lBfrtip',
            // buttons: ["csv"],
        });

        $('#status_filter').on('change', function() {
            dtable.ajax.reload();
        });
        jQuery(document).on('change', 'select', function(event) {
            event.preventDefault();
            dtable.draw();
        });
        $('#filter_section').hide();
        $("#show_filter").click(function() {
            $('#filter_section').toggle();
        });
    });
</script>
<script>
    $(document).ready(function () {
        $('#add-attendance-btn').on('click', function () {
            $.ajax({
                url: "{{ route('attedance.fetch-logs') }}",
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                beforeSend: function () {
                    $('#add-attendance-btn').prop('disabled', true).text('Syncing...');
                },
                success: function (res) {

                    console.log(res);

                    // alert(res.message);
                    // location.reload(); // Reload table afster uccess
                },
                error: function (xhr) {
                    alert(xhr.responseJSON?.message || 'Something went wrong.');
                },
                complete: function () {
                    $('#add-attendance-btn').prop('disabled', false).text('Fetch Attendance Logs');
                }
            });
        });
    });
</script>



@include('software.includes.script-delete-record')
@include('software.includes.script-restore-record')
@include('software.includes.script-update-status')
@endsection
