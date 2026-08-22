@extends('software.layout.app')

@php
    $page_title = isset($modules['title']) ? $modules['title'] : null;
    $folder_path = isset($modules['folder_path']) ? $modules['folder_path'] : null;
    $route = isset($modules['route']) ? $modules['route'] : null;
    $permission_prefix = isset($modules['permission_prefix']) ? $modules['permission_prefix'] : null;
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
            'show_add_btn' => isset($modules['permission_add']) ? $modules['permission_add'] : false,
            'show_filter_btn' => false,
            'show_back_btn' => false,
        ])
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card shadow">
                <div class="card-body">
                    <div>
                        <table id="yajra-datatables" class="table table-hover dt-responsive nowrap" style="width: 100%">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>GR No</th>
                                    <th>Student Name</th>
                                    <th>Event</th>
                                    <th class="text-center">Action</th>
                                </tr>
                            </thead>
                        </table>
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
                                    <th>Achievement</th>
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
                                    <th>Student Name</th>
                                    <th>Rank</th>
                                    <th>Remark</th>
                                    <th>Date</th>
                                    <th>By</th>
                                </tr>
                            </thead>
                            <tbody id="event-modal-tbody">
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
                },
                columns: [
                    { data: 'DT_RowIndex', name: 'id', orderable: false, searchable: false },
                    { data: 'gr_no', name: 'gr_no' },
                    { data: 'student_name', name: 'student_name' },
                    { data: 'event_name', name: 'event_name' },
                    { data: 'action', name: 'action', orderable: false, searchable: false }
                ],
                language: {
                    searchPlaceholder: 'Search...',
                },
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
                $('#event-modal-tbody').html('<tr><td colspan="6" class="text-center">Loading...</td></tr>');
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
                                    <td>${row.student_name}</td>
                                    <td>${row.rank}</td>
                                    <td>${row.remark}</td>
                                    <td>${row.date}</td>
                                    <td>${row.by_user}</td>
                                </tr>`;
                            });
                            $('#event-modal-tbody').html(html);
                        } else {
                            $('#event-modal-tbody').html('<tr><td colspan="6" class="text-center text-muted">No achievements found.</td></tr>');
                        }
                    },
                    error: function() {
                        $('#event-modal-tbody').html('<tr><td colspan="6" class="text-center text-danger">Error loading achievements.</td></tr>');
                    }
                });
            });
        });
    </script>
    @include('software.includes.script-delete-record')
@endsection
