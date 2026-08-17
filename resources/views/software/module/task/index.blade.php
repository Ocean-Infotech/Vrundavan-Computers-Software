@extends('software.layout.app')

@php
    $page_title = isset($modules['title']) ? $modules['title'] : 'Task Management';
    $folder_path = isset($modules['folder_path']) ? $modules['folder_path'] : null;
    $route = isset($modules['route']) ? $modules['route'] : null;
@endphp
@section('title', $page_title)

@section('page_style_file')
    <link rel="stylesheet" href="{{ asset('admin/assets/cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css') }}">
    <link rel="stylesheet" href="{{ asset('admin/assets/cdn.datatables.net/responsive/2.2.9/css/responsive.bootstrap.min.css') }}">
    <style>
        .card .table-responsive {
            overflow: visible !important;
        }
        #yajra-datatables td,
        #yajra-datatables th {
            white-space: normal !important;
            word-wrap: break-word;
        }
        #yajra-datatables td:nth-child(2) {
            width: 410px;
        }
    </style>
@endsection

@section('content')
    <div class="d-flex justify-content-lg-between px-1">
        @include('software.includes.breadcrumb', [
            'breadcrumbArray' => [['title' => $page_title, 'url' => '']],
            'route' => $route,
            'show_add_btn' => $modules['permission_add'],
            'show_filter_btn' => false,
            'show_export_btns' => false,
            'show_excal_btn' => false,
            'show_print_btn' => false,
            'show_back_btn' => false,
        ])
    </div>

    <div class="row my-3">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="yajra-datatables" class="table table-hover dt-responsive" style="width: 100%">
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

    <!-- Task Details Modal -->
    <div class="modal fade" id="taskDetailsModal" tabindex="-1" aria-labelledby="taskDetailsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" style="max-width: 1400px; width: 90%;">
            <div class="modal-content">
                <form id="taskReplyForm">
                    @csrf
                    <input type="hidden" id="modal-task-id" name="id">
                    <div class="modal-header">
                        <h5 class="modal-title" id="taskDetailsModalLabel">Task Details</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-0">
                        <div class="table-responsive">
                            <table class="table table-bordered align-middle text-center mb-0" style="width: 100%;">
                                <thead class="table-light text-uppercase small">
                                    <tr>
                                        @if(\App\Helpers\Helper::getLoginUserRole() !== 'Facility')
                                        <th style="width: 18%;">Task Details</th>
                                        <th style="width: 12%;">Assign Date</th>
                                        <th style="width: 12%;">Deadline</th>
                                        <th style="width: 16%;">Status</th>
                                        <th style="width: 14%;">Response</th>
                                        <th style="width: 15%;">Remarks</th>
                                        <th style="width: 13%;">Action</th>
                                        @else
                                        <th style="width: 30%;">Task Details</th>
                                        <th style="width: 18%;">Assign Date</th>
                                        <th style="width: 18%;">Deadline</th>
                                        <th style="width: 22%;">Response</th>
                                        <th style="width: 12%;">Action</th>
                                        @endif
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td class="text-start">
                                            <span id="modal-work" style="white-space: pre-wrap; word-break: break-all;"></span>
                                        </td>
                                        <td id="modal-date" class="text-nowrap small"></td>
                                        <td id="modal-deadline" class="text-nowrap small"></td>
                                        @if(\App\Helpers\Helper::getLoginUserRole() !== 'Facility')
                                        <td>
                                            <select id="modal-status-select" name="status" class="form-select form-select-sm" required>
                                                <option value="0">Pending</option>
                                                <option value="1">In Progress</option>
                                                <option value="2">Completed</option>
                                                <option value="3">Done</option>
                                                <option value="4">Repass</option>
                                                <option value="5">Cancelled</option>
                                            </select>
                                        </td>
                                        <td>
                                            <textarea id="modal-response-textarea" class="form-control form-control-sm auto-expand" rows="1" readonly placeholder="-"></textarea>
                                        </td>
                                        <td>
                                            <textarea id="modal-remarks-textarea" name="remarks" class="form-control form-control-sm auto-expand" rows="1" placeholder="Enter remarks" required></textarea>
                                        </td>
                                        @else
                                        <td>
                                            <textarea id="modal-response-textarea" name="response" class="form-control form-control-sm auto-expand" rows="1" placeholder="Enter response" required></textarea>
                                        </td>
                                        @endif
                                        <td class="text-nowrap">
                                            @if(\App\Helpers\Helper::getLoginUserRole() !== 'Facility')
                                            <button type="button" class="btn btn-sm btn-secondary me-1 btn-modal-repass">Repass</button>
                                            <button type="submit" class="btn btn-sm btn-info text-white me-1">Done</button>
                                            <button type="button" class="btn btn-sm btn-warning text-white" data-bs-dismiss="modal">Cancel</button>
                                            @else
                                            <button type="submit" class="btn btn-sm btn-info text-white me-1">Done</button>
                                            <button type="button" class="btn btn-sm btn-warning text-white" data-bs-dismiss="modal">Cancel</button>
                                            @endif
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </form>
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

        $(document).ready(function() {
            var dtable = $('#yajra-datatables').DataTable({
                searching: true,
                processing: true,
                serverSide: true,
                order: [],
                ajax: {
                    url: "{{ route('task.index') }}",
                    type: "GET"
                },
                columns: {!! json_encode($columns) !!},
                language: {
                    searchPlaceholder: 'Search...',
                    emptyTable: "No tasks found."
                }
            });

            // View Details Modal Handler
            $(document).on('click', '.view-details', function() {
                var id = $(this).attr('data-id');
                var date = $(this).attr('data-date');
                var staff = $(this).attr('data-staff');
                var work = $(this).attr('data-work');
                var deadline = $(this).attr('data-deadline');
                var status = $(this).attr('data-status');
                var remarks = $(this).attr('data-remarks');
                var response = $(this).attr('data-response');

                $('#modal-task-id').val(id);
                $('#modal-date').text(date);
                $('#modal-staff').text(staff);
                $('#modal-work').text(work);
                $('#modal-deadline').text(deadline);
                function autoResize(el) {
                    if (el) {
                        el.style.height = 'auto';
                        el.style.height = Math.max(el.scrollHeight, 38) + 'px';
                    }
                }

                $('#modal-remarks-textarea').val(remarks === 'null' ? '' : remarks);
                $('#modal-response-textarea').val(response === 'null' ? '' : response);

                $('#taskDetailsModal').modal('show');

                setTimeout(function() {
                    $('.auto-expand').each(function() {
                        autoResize(this);
                    });
                }, 200);

                if ($('#modal-status-select').length) {
                    $('#modal-status-select').val(status).trigger('change');
                    $('#modal-status-select').select2({
                        dropdownParent: $('#taskDetailsModal'),
                        width: '100%'
                    });
                }
            });

            $(document).on('input', '.auto-expand', function() {
                this.style.height = 'auto';
                this.style.height = Math.max(this.scrollHeight, 38) + 'px';
            });

            // Repass Button Handler in Modal
            $(document).on('click', '.btn-modal-repass', function() {
                $('#modal-status-select').val('4').trigger('change');
                $('#taskReplyForm').submit();
            });

            // Submit Reply Form Handler
            $('#taskReplyForm').on('submit', function(e) {
                e.preventDefault();
                
                $.ajax({
                    url: "{{ route('task.save-reply') }}",
                    type: "POST",
                    data: $(this).serialize(),
                    success: function(response) {
                        if (response.success) {
                            $('#taskDetailsModal').modal('hide');
                            dtable.ajax.reload(null, false);
                        } else {
                            alert(response.message);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error(xhr.responseText);
                        alert("Error: " + xhr.status + " " + error);
                    }
                });
            });



            // Change Status Handler (inline)
            $(document).on('click', '.change-status', function() {
                var id = $(this).data('id');
                var status = $(this).data('status');
                
                $.ajax({
                    url: "{{ route('task.change-status') }}",
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        id: id,
                        status: status
                    },
                    success: function(response) {
                        if (response.success) {
                            dtable.ajax.reload(null, false);
                        } else {
                            alert(response.message);
                        }
                    }
                });
            });

            // Delete Record Handler
            $(document).on('click', '.delete-record', function() {
                var id = $(this).data('id');
                if (confirm('Are you sure you want to delete this task?')) {
                    $.ajax({
                        url: "{{ route('task.index') }}/" + id,
                        type: "DELETE",
                        data: {
                            _token: "{{ csrf_token() }}"
                        },
                        success: function(response) {
                            if (response.success) {
                                dtable.ajax.reload(null, false);
                            } else {
                                alert(response.message);
                            }
                        }
                    });
                }
            });
        });
    </script>
@endsection
