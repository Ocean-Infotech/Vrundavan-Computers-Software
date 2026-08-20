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
                            <div class="col-md-2 mb-2">
                                <label class="form-label fw-semibold mb-1">Course</label>
                                <select id="course_id" class="form-control search_by_course select2 select_filter" data-append="search_by_course">
                                    <option value="">Select Course</option>
                                </select>
                            </div>
                            <div class="col-md-2 mb-2">
                                <label class="form-label fw-semibold mb-1">Batch</label>
                                <select id="batch_id" class="form-control search_by_batch select2 select_filter" data-append="search_by_batch">
                                    <option value="">Select Batch</option>
                                </select>
                            </div>
                            <div class="col-md-5 mb-2">
                                <label class="form-label fw-semibold mb-1">Class</label>
                                <div id="class_id" class="form-control search_by_class d-flex flex-wrap align-items-center" data-append="search_by_class" style="min-height: 38px; border: 1px solid #ced4da; border-radius: 4px; padding: 6px 12px; background: #fff; gap: 15px;">
                                    <span class="text-muted">Select Class</span>
                                </div>
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

    {{-- Student Attendance Detail Modal --}}
    <div class="modal fade" id="studentDetailModal" tabindex="-1" aria-labelledby="studentDetailModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="studentDetailModalLabel">
                        <span id="modalStudentName"></span> - Attendance Details
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="studentDetailLoading" class="text-center py-4 d-none">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2 text-muted mb-0">Fetching attendance…</p>
                    </div>
                    <div id="studentDetailError" class="alert alert-danger m-3 d-none"></div>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="studentDetailTable">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Day</th>
                                    <th>In Time</th>
                                    <th>Out Time</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody id="studentDetailTableBody">
                                <tr><td colspan="5" class="text-center text-muted py-3">No data</td></tr>
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
                ranges: {
                   'Today': [moment(), moment()],
                   'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                   'This Month': [moment().startOf('month'), moment().endOf('month')],
                   'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')],
                   'Last 2 Months': [moment().subtract(2, 'month').startOf('month'), moment().endOf('month')]
                },
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
                    
                    var classIds = [];
                    $('.class-checkbox-filter:checked').each(function() {
                        classIds.push($(this).val());
                    });
                    data.class_id = classIds;
 
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

             $(document).on('change', '.class-checkbox-filter', function() {
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
                     batch_id: $('#batch_id').val() || ''
                 });
                 $('.class-checkbox-filter:checked').each(function() {
                     params.append('class_id[]', $(this).val());
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
                     batch_id: $('#batch_id').val() || ''
                 });
                 $('.class-checkbox-filter:checked').each(function() {
                     params.append('class_id[]', $(this).val());
                 });
                 window.location.href = "{{ route('attedance-report.export-excel') }}?" + params.toString();
             });
             // Custom handler for loading class checkboxes in this page
             $(document).on('change', '.search_by_batch', function() {
                 let batch_id = $(this).val();
                 let container = $('#class_id');
                 if (batch_id) {
                     $.ajax({
                         type: 'POST',
                         url: "{{ route('get-class-bybatch') }}",
                         beforeSend: function(request) {
                             request.setRequestHeader("X-CSRF-TOKEN", $('meta[name="csrf-token"]').attr('content'));
                         },
                         data: {
                             batch_id: batch_id
                         },
                         success: function(response) {
                             if (response.status) {
                                 let checkboxes = "";
                                 if (response.data && response.data.length > 0) {
                                     $.each(response.data, function(i, item) {
                                         checkboxes += `
                                             <div class="form-check form-check-inline mb-0 me-3">
                                                 <input class="form-check-input class-checkbox-filter" type="checkbox" name="class_id[]" value="${item.id}" id="class_chk_${item.id}">
                                                 <label class="form-check-label fw-semibold" for="class_chk_${item.id}">${item.name}</label>
                                             </div>
                                         `;
                                     });
                                 } else {
                                     checkboxes = '<span class="text-muted">No classes found</span>';
                                 }
                                 container.empty().html(checkboxes);
                                 dtable.draw();
                             }
                         }
                     });
                 } else {
                     container.empty().html('<span class="text-muted">Select Batch first</span>');
                     dtable.draw();
                 }
             });
        });
    </script>

    @include('software.utils.getCourse')
    @include('software.utils.getBatchByCourseid')

    <script type="text/javascript">
        // Student Name Click → Detail Modal
        var studentDetailModal = new bootstrap.Modal(document.getElementById('studentDetailModal'));

        $(document).on('click', '.student-name-link', function (e) {
            e.preventDefault();

            var biometricId  = $(this).data('biometric-id');
            var studentName  = $(this).data('student-name');
            var startDate    = $('#start_date').val();
            var endDate      = $('#end_date').val();

            if (!startDate || !endDate) {
                alert('Please select a Date Range first to view student detail.');
                return;
            }

            // Reset modal state
            $('#modalStudentName').text(studentName);
            $('#studentDetailLoading').removeClass('d-none');
            $('#studentDetailError').addClass('d-none').text('');
            $('#studentDetailTableBody').html('<tr><td colspan="5" class="text-center text-muted py-3">Loading…</td></tr>');
            $('#studentDetailSummary').text('');

            studentDetailModal.show();

            $.ajax({
                type: 'GET',
                url: "{{ route('attedance-report.student-detail') }}",
                data: {
                    biometric_id : biometricId,
                    start_date   : startDate,
                    end_date     : endDate,
                },
                beforeSend: function (request) {
                    request.setRequestHeader('X-CSRF-TOKEN', $('meta[name="csrf-token"]').attr('content'));
                },
                success: function (response) {
                    $('#studentDetailLoading').addClass('d-none');

                    if (!response.status || !response.data || !response.data.length) {
                        $('#studentDetailTableBody').html('<tr><td colspan="5" class="text-center text-muted py-3">No attendance records found.</td></tr>');
                        return;
                    }

                    var rows    = response.data;
                    var html    = '';

                    $.each(rows, function (i, row) {
                        var isPresent = (row.status === 'Present');
                        var badgeClass = isPresent ? 'bg-success' : 'bg-danger';
                        var statusBadge = '<span class="badge ' + badgeClass + '">' + row.status + '</span>';

                        html += '<tr>'
                             +  '<td>' + row.date + '</td>'
                             +  '<td>' + row.day + '</td>'
                             +  '<td>' + row.in_time + '</td>'
                             +  '<td>' + row.out_time + '</td>'
                             +  '<td>' + statusBadge + '</td>'
                             +  '</tr>';
                    });

                    $('#studentDetailTableBody').html(html);
                },
                error: function () {
                    $('#studentDetailLoading').addClass('d-none');
                    $('#studentDetailError').removeClass('d-none').text('Failed to load attendance data. Please try again.');
                    $('#studentDetailTableBody').html('<tr><td colspan="5" class="text-center text-muted py-3">Error loading data.</td></tr>');
                }
            });
        });
    </script>
@endsection

