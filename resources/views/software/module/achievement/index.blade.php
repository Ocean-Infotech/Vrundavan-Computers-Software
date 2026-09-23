@extends('software.layout.app')

@php
    $page_title = isset($modules['title']) ? $modules['title'] : 'Achievement';
    $folder_path = isset($modules['folder_path']) ? $modules['folder_path'] : 'software.module.achievement';
    $route = isset($modules['route']) ? $modules['route'] : 'achievement';
    $permission_prefix = isset($modules['permission_prefix']) ? $modules['permission_prefix'] : 'achievement';
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
            'show_add_btn' => true,
            'show_filter_btn' => false,
            'show_back_btn' => false,
        ])
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row">
        <div class="col-md-12">
            <div class="card shadow">
                <div class="card-body">
                    <!-- Filters -->
                    <div class="row mb-4 align-items-end g-2">
                        <div class="col-md-3 col-sm-6">
                            <label for="filter_month" class="form-label fw-bold">Month</label>
                            <select id="filter_month" class="form-select select2">
                                <option value="">All Months</option>
                                @foreach($months as $mNum => $mName)
                                    <option value="{{ $mNum }}">{{ $mName }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2 col-sm-6">
                            <label for="filter_year" class="form-label fw-bold">Year</label>
                            <select id="filter_year" class="form-select select2">
                                <option value="">All Years</option>
                                @foreach($years as $yr)
                                    <option value="{{ $yr }}">{{ $yr }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 col-sm-6">
                            <label for="filter_gr_no" class="form-label fw-bold">GR No</label>
                            <input list="gr_no_list" id="filter_gr_no" class="form-control" placeholder="Search by GR No">
                            <datalist id="gr_no_list">
                                @foreach($grNos as $student)
                                    @php
                                        $fullName = trim(($student->first_name ?? '') . ' ' . ($student->last_name ?? '') . ' ' . ($student->father_name ?? ''));
                                    @endphp
                                    <option value="{{ $student->gr_no }}">{{ $fullName }}</option>
                                @endforeach
                            </datalist>
                        </div>
                        <div class="col-md-3 col-sm-6">
                            <label for="filter_event_id" class="form-label fw-bold">Event / Award</label>
                            <select id="filter_event_id" class="form-select select2">
                                <option value="">Select an option</option>
                                @foreach($events as $event)
                                    <option value="{{ $event->id }}">{{ $event->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-1 col-sm-6">
                            <button type="button" id="btn_reset" class="btn btn-outline-secondary w-100" title="Reset Filters"><i class="bx bx-reset"></i> Reset</button>
                        </div>
                    </div>

                    <div>
                        <table id="yajra-datatables" class="table table-hover dt-responsive nowrap" style="width: 100%">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>GR No</th>
                                    <th>Student Name</th>
                                    <th>Event / Award</th>
                                    <th>Rank</th>
                                    <th>Date</th>
                                    <th>Remark</th>
                                    <th class="text-center">Action</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Student Achievements Modal -->
    <div class="modal fade" id="studentAchievementsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="studentModalTitle">Student Achievements</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead class="table-light">
                                <tr>
                                    <th>No</th>
                                    <th>Achievement / Event</th>
                                    <th>Rank</th>
                                    <th>Remark</th>
                                    <th>Date</th>
                                    <th>By</th>
                                </tr>
                            </thead>
                            <tbody id="student-modal-tbody">
                                <tr>
                                    <td colspan="6" class="text-center">Loading...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Event Achievements Modal -->
    <div class="modal fade" id="eventAchievementsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="eventModalTitle">Event Achievements</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead class="table-light">
                                <tr>
                                    <th>No</th>
                                    <th>GR No</th>
                                    <th>Student Name</th>
                                    <th>Rank</th>
                                    <th>Remark</th>
                                    <th>Date</th>
                                    <th>By</th>
                                </tr>
                            </thead>
                            <tbody id="event-modal-tbody">
                                <tr>
                                    <td colspan="7" class="text-center">Loading...</td>
                                </tr>
                            </tbody>
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
            if ($.fn.select2) {
                $('#filter_event_id, #filter_month, #filter_year').select2({
                    placeholder: "Select an option",
                    allowClear: true
                });
            }

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
                    data: function(d) {
                        d.gr_no = $('#filter_gr_no').val();
                        d.event_id = $('#filter_event_id').val();
                        d.month = $('#filter_month').val();
                        d.year = $('#filter_year').val();
                    }
                },
                columns: [
                    { data: 'DT_RowIndex', name: 'id', orderable: false, searchable: false },
                    { data: 'gr_no', name: 'gr_no' },
                    { data: 'student_name', name: 'student_name' },
                    { data: 'event_name', name: 'event_name' },
                    { data: 'rank', name: 'rank' },
                    { data: 'date', name: 'date' },
                    { data: 'remark', name: 'remark' },
                    { data: 'action', name: 'action', orderable: false, searchable: false }
                ],
                language: {
                    searchPlaceholder: 'Search...',
                    emptyTable: 'Please apply filter to view achievements.',
                    zeroRecords: 'No achievements found for the selected filter.'
                },
            });

            $('#btn_reset').on('click', function() {
                $('#filter_gr_no').val('');
                $('#filter_event_id').val('').trigger('change.select2');
                $('#filter_month').val('').trigger('change.select2');
                $('#filter_year').val('').trigger('change.select2');
                dtable.draw();
            });

            $('#filter_gr_no').on('keyup change input', function() {
                dtable.draw();
            });

            $('#filter_event_id, #filter_month, #filter_year').on('change', function() {
                dtable.draw();
            });

            // View student achievements modal
            $(document).on('click', '.view-student-achievements', function() {
                let id = $(this).data('id');
                let name = $(this).data('name');
                $('#studentModalTitle').text(name + ' - Achievements');
                $('#student-modal-tbody').html('<tr><td colspan="6" class="text-center">Loading...</td></tr>');
                $('#studentAchievementsModal').modal('show');

                $.ajax({
                    url: "{{ url('software/achievement/student-data') }}/" + id,
                    type: "GET",
                    success: function(response) {
                        if (response.success && response.data.length > 0) {
                            let html = '';
                            response.data.forEach(function(row, idx) {
                                html += `<tr>
                                    <td>${idx + 1}</td>
                                    <td>${row.event}</td>
                                    <td>${row.rank}</td>
                                    <td>${row.remark}</td>
                                    <td>${row.date}</td>
                                    <td>${row.by_user}</td>
                                </tr>`;
                            });
                            $('#student-modal-tbody').html(html);
                        } else {
                            $('#student-modal-tbody').html('<tr><td colspan="6" class="text-center text-muted">No achievements found.</td></tr>');
                        }
                    },
                    error: function() {
                        $('#student-modal-tbody').html('<tr><td colspan="6" class="text-center text-danger">Error loading achievements.</td></tr>');
                    }
                });
            });

            // View event achievements modal
            $(document).on('click', '.view-event-achievements', function() {
                let id = $(this).data('id');
                let name = $(this).data('name');
                $('#eventModalTitle').text(name + ' - Achievements');
                $('#event-modal-tbody').html('<tr><td colspan="7" class="text-center">Loading...</td></tr>');
                $('#eventAchievementsModal').modal('show');

                $.ajax({
                    url: "{{ url('software/achievement/event-data') }}/" + id,
                    type: "GET",
                    success: function(response) {
                        if (response.success && response.data.length > 0) {
                            let html = '';
                            response.data.forEach(function(row, idx) {
                                html += `<tr>
                                    <td>${idx + 1}</td>
                                    <td>${row.gr_no}</td>
                                    <td>${row.student_name}</td>
                                    <td>${row.rank}</td>
                                    <td>${row.remark}</td>
                                    <td>${row.date}</td>
                                    <td>${row.by_user}</td>
                                </tr>`;
                            });
                            $('#event-modal-tbody').html(html);
                        } else {
                            $('#event-modal-tbody').html('<tr><td colspan="7" class="text-center text-muted">No achievements found.</td></tr>');
                        }
                    },
                    error: function() {
                        $('#event-modal-tbody').html('<tr><td colspan="7" class="text-center text-danger">Error loading achievements.</td></tr>');
                    }
                });
            });
        });
    </script>
    @include('software.includes.script-delete-record')
@endsection
