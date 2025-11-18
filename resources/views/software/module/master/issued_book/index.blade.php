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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css">

    <link rel="stylesheet" href="{{ asset('admin/assets/cdn.datatables.net/buttons/2.2.2/css/buttons.dataTables.min.css') }}">
@endsection

@section('breadcrumb')
    <div class="d-flex justify-content-lg-between px-1">
        @include('software.includes.breadcrumb', [
            'breadcrumbArray' => [['title' => $page_title, 'url' => '']],
            'page_title' => $page_title,
            'route' => $route,
            // 'show_add_btn' => $modules['addPermission'],
            'show_add_btn' =>
                isset($modules) && isset($modules['permission_add']) ? $modules['permission_add'] : false,
            'show_filter_btn' => false,
            'show_back_btn' => false,
        ])
    </div>
@endsection

@section('content')
    <div class="row my-2">
        <div class="col-md-12 mb-5">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 col-sm-12">
                            <div class="form-group">
                                <label for="search_id" class="form-label">Search Book</label>
                                <input list="search" id="search_id" name="book_id" class="form-control"
                                    placeholder="Search by book name or code" autocomplete="off">
                                <datalist id="search"></datalist>
                            </div>
                        </div>

                        <div class="col-md-4 col-sm-12">
                            <div class="form-group">
                                <label for="admission_search" class="form-label">Search Student</label>
                                <input list="admission_list" id="admission_search" name="student_id" class="form-control"
                                    placeholder="Search by Admission ID or Student Name" autocomplete="off">
                                <datalist id="admission_list"></datalist>
                            </div>
                        </div>

                        <div class="col-md-4 col-sm-12">
                            <label class="form-label">Filter by Date</label>
                            <input type="text" name="selected_date" class="form-control my_daterangepicker  table_filter"
                                value="" placeholder="Filter by date range">
                        </div>

                    </div>
                </div>
            </div>
        </div>
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
@endsection

@section('page_script_file')
    <!--datatable js-->
    <script src="{{ asset('admin/assets/cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('admin/assets/cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js') }}"></script>
    <script src="{{ asset('admin/assets/cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js') }}"></script>
    <!-- Date Range Picker JS --><!-- ✅ Date Range Picker CSS -->
    <!-- ✅ Moment.js (required) -->
    <script src="https://cdn.jsdelivr.net/npm/moment@2.29.4/moment.min.js"></script>

    <!-- ✅ Date Range Picker JS -->
    <script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/moment@2.29.4/moment.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>

    {{-- <script src="{{ asset('admin/assets/cdn.datatables.net/buttons/2.2.2/js/dataTables.buttons.min.js') }}"></script>
<script src="{{ asset('admin/assets/cdn.datatables.net/buttons/2.2.2/js/buttons.print.min.js') }}"></script>
<script src="{{ asset('admin/assets/cdn.datatables.net/buttons/2.2.2/js/buttons.html5.min.js') }}"></script>
<script src="{{ asset('admin/assets/cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js') }}"></script>
<script src="{{ asset('admin/assets/cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js') }}"></script>
<script src="{{ asset('admin/assets/cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js') }}"></script> --}}

    {{-- <script src="{{ asset('admin/assets/js/pages/datatables.init.js') }}"></script> --}}
@endsection

@section('page_leavel_script')
    <script type="text/javascript">
        // 🔹 Initialize Date Range Picker
        // ✅ Destroy any existing daterangepicker (to avoid duplicate initialization)

        $(function() {
            if ($('.my_daterangepicker').length > 0) {
                // ✅ Default range: current month
                var start = moment().startOf('month');
                var end = moment().endOf('month');
                var endOfYear = moment().endOf('year');

                var future_date = $('.my_daterangepicker').attr("data-future-date");
                if (future_date == "yes") {
                    endOfYear = moment().endOf('year');
                }

                // ✅ Initialize Date Range Picker
                $('.my_daterangepicker').daterangepicker({
                    startDate: start,
                    endDate: end,
                    maxDate: endOfYear,
                    autoUpdateInput: true,
                    alwaysShowCalendars: true,
                    locale: {
                        format: 'DD/MM/YYYY',
                        applyLabel: 'Apply',
                        cancelLabel: 'Clear'
                    },
                    ranges: {
                        'Today': [moment(), moment()],
                        'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                        'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                        'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                        'This Month': [moment().startOf('month'), moment().endOf('month')],
                        'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1,
                            'month').endOf('month')],
                        'Custom Range': [] // ✅ Allows user-defined range
                    }
                }, function(start, end, label) {
                    // ✅ Update the input field with selected date range
                    $('.my_daterangepicker').val(start.format('DD/MM/YYYY') + ' to ' + end.format(
                        'DD/MM/YYYY'));
                });

                // ✅ Set default value to current month when page loads
                $('.my_daterangepicker').val(start.format('DD/MM/YYYY') + ' to ' + end.format('DD/MM/YYYY'));
            }
        });

        $('.my_daterangepicker').on('apply.daterangepicker', function(ev, picker) {
            $(this).val(picker.startDate.format('DD/MM/YYYY') + ' to ' + picker.endDate.format('DD/MM/YYYY'));
            dtable.ajax.reload(); // ✅ reload data
        });
        $('.my_daterangepicker').on('cancel.daterangepicker', function(ev, picker) {
            $(this).val('');
            dtable.ajax.reload(); // ✅ reload data after clearing
        });




        // 🔹 Load Book Datalist
        function loadBookDatalist(search = '') {
            $.ajax({
                url: "{{ route('get-books') }}",
                type: 'GET',
                data: {
                    search: search
                },
                success: function(response) {
                    $('#search').html(response);
                },
                error: function(xhr) {
                    console.error('Error fetching books:', xhr.responseText);
                }
            });
        }

        // Load all books initially
        loadBookDatalist();

        // Fetch book list as user types
        $('#search_id').on('input', function() {
            const search = $(this).val().trim();
            loadBookDatalist(search);
        });

        // Reload list on focus if empty
        $('#search_id').on('focus', function() {
            if ($(this).val().trim() === '') {
                loadBookDatalist();
            }
        });

        // 🔹 Load Student Datalist
        function loadAdmissionDatalist(search = '') {
            $.ajax({
                url: "{{ route('get-admission') }}",
                type: 'GET',
                dataType: 'json',
                data: {
                    search: search
                },
                success: function(response) {
                    if (response.data && response.data.length > 0) {
                        let options = '';
                        response.data.forEach(function(item) {
                            options +=
                                `<option value="${item.id} - ${item.name}"></option>`;
                        });
                        $('#admission_list').html(options);
                    } else {
                        $('#admission_list').empty();
                    }
                },
                error: function(xhr) {
                    console.error('Error fetching admissions:', xhr.responseText);
                }
            });
        }

        // Load admission list initially
        loadAdmissionDatalist();

        // Filter as user types
        $('#admission_search').on('input', function() {
            const search = $(this).val().trim();
            loadAdmissionDatalist(search);
        });

        // Reload list on focus if empty
        $('#admission_search').on('focus', function() {
            if ($(this).val().trim() === '') {
                loadAdmissionDatalist();
            }
        });

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
                        data.book_id = $('#search_id').val(); // ✅ SENDS Book filter
                        data.student_id = $('#admission_search').val();
                        let selectedDate = $('input[name="selected_date"]').val();
                        if (selectedDate && selectedDate.includes('to')) {
                            let parts = selectedDate.split('to');
                            data.start_date = parts[0].trim();
                            data.end_date = parts[1].trim();
                        }
                        // data.status = $('select[name="status"] option:selected').val();
                        // data.role = $('select[name="role"] option:selected').val();
                    },
                },
                columns: {!! json_encode($columns) !!},
                language: {
                    searchPlaceholder: 'Search...',
                },
                // dom:'lBfrtip',
                // buttons: ["csv"],
            });
        });

        // When typing or selecting in book search, reload DataTable
        $('#search_id').on('input change', function() {
            dtable.ajax.reload();
        });

        // When typing or selecting in student search, reload DataTable
        $('#admission_search').on('input change', function() {
            dtable.ajax.reload();
        });

        $('input[name="search"]').keyup(function() {
            dtable.draw();
        });
        jQuery(document).on('change', 'select', function(event) {
            event.preventDefault();
            dtable.draw();
        });
    </script>
    <script>
        $(document).on('click', '.open-issue-create', function() {
            let bookId = $(this).data('book-id');
            let bookName = $(this).data('book-name');

            // Store the book details in localStorage
            localStorage.setItem('selected_book_id', bookId);
            localStorage.setItem('selected_book_name', bookName);

            // Redirect to the Issue Book Create page
            window.location.href = "{{ route('issued-book.create') }}";
        });
    </script>


    @include('software.includes.script-delete-record')
    @include('software.includes.script-restore-record')
    @include('software.includes.script-update-status')
@endsection
