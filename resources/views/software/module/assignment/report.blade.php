@extends('software.layout.app')

@php
    $page_title = isset($modules['title']) ? $modules['title'] : 'Assignment';
    $folder_path = isset($modules['folder_path']) ? $modules['folder_path'] : null;
    $route = isset($modules['route']) ? $modules['route'] : null;
    $i = 0;
@endphp
@section('title', $page_title . ' Report')

@section('page_style_file')
    <!--datatable css-->
    <link rel="stylesheet" href="{{ asset('admin/assets/cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css') }}">
    <!--datatable responsive css-->
    <link rel="stylesheet" href="{{ asset('admin/assets/cdn.datatables.net/responsive/2.2.9/css/responsive.bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('admin/assets/cdn.datatables.net/buttons/2.2.2/css/buttons.dataTables.min.css') }}">
@endsection

@section('content')
    <div class="d-flex justify-content-lg-between px-1">
        @include('software.includes.breadcrumb', [
            'breadcrumbArray' => [
                ['title' => $page_title, 'url' => route($route . '.index')],
                ['title' => 'Report', 'url' => '']
            ],
            'route' => $route,
            'show_add_btn' => false,
            'show_filter_btn' => false,
            'show_export_btns' => false,
            'show_excal_btn' => false,
            'show_print_btn' => false,
            'show_back_btn' => true,
        ])
    </div>

    <div class="row my-3">
        <div class="col-md-12">
            <div class="card mb-3">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-2 col-sm-12 mb-2 mb-md-0">
                            <h4 class="mb-0">{{ $page_title }} Report</h4>
                        </div>
                        <div class="col-md-2 col-sm-6">
                            <strong>Course:</strong> {{ $assignment->course->course_name ?? 'N/A' }}
                        </div>
                        <div class="col-md-2 col-sm-6">
                            <strong>Batch:</strong> {{ $assignment->batch->batch_name ?? 'N/A' }}
                        </div>
                        <div class="col-md-2 col-sm-6">
                            <strong>Subject:</strong> {{ $assignment->subject ?? 'N/A' }}
                        </div>
                        <div class="col-md-2 col-sm-6">
                            <strong>Unit:</strong> {{ $assignment->unit ?? '-' }}
                        </div>
                        <div class="col-md-2 col-sm-6">
                            <strong>Semester:</strong> {{ $assignment->semester ?? '-' }}
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <input list="search" id="search_id" name="search" class="form-control" placeholder="Search">
                            <datalist id="search" class="search_by_courceregistration" data-append="search_by_courceregistration"></datalist>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="report-datatables" class="table table-striped table-hover dt-responsive nowrap" style="width: 100%">
                            <thead>
                                <tr>
                                    <th>Gr.no</th>
                                    <th>Student Name</th>
                                    <th class="d-none">Reg ID</th>
                                    <th class="text-center">Complete</th>
                                    <th class="text-center">Pending</th>
                                    <th class="text-center">Incomplete</th>
                                    <th>Remarks</th>
                                    <th>Faculty Name</th>
                                    <th>Date & Time</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($students as $student)
                                    @php
                                        $status = $student->report->status ?? '';
                                        $remarks = $student->report->remarks ?? '';
                                    @endphp
                                    <tr>
                                        <td>{{ $student->admission ? $student->admission->gr_no : '-' }}</td>
                                        <td>{{ $student->admission ? trim(($student->admission->first_name ?? '') . ' ' . ($student->admission->last_name ?? '') . ' ' . ($student->admission->father_name ?? '')) : '-' }}</td>
                                        <td class="d-none">{{ $student->register_id }}</td>
                                        <td class="text-center">
                                            <input class="form-check-input status-radio" type="radio" 
                                                name="status_{{ $student->admission->gr_no ?? 0 }}" 
                                                data-student-id="{{ $student->admission->gr_no ?? 0 }}" 
                                                data-assignment-id="{{ $assignment->id }}" 
                                                value="complete" {{ $status == 'complete' ? 'checked' : '' }}>
                                        </td>
                                        <td class="text-center">
                                            <input class="form-check-input status-radio" type="radio" 
                                                name="status_{{ $student->admission->gr_no ?? 0 }}" 
                                                data-student-id="{{ $student->admission->gr_no ?? 0 }}" 
                                                data-assignment-id="{{ $assignment->id }}" 
                                                value="pending" {{ $status == 'pending' ? 'checked' : '' }}>
                                        </td>
                                        <td class="text-center">
                                            <input class="form-check-input status-radio" type="radio" 
                                                name="status_{{ $student->admission->gr_no ?? 0 }}" 
                                                data-student-id="{{ $student->admission->gr_no ?? 0 }}" 
                                                data-assignment-id="{{ $assignment->id }}" 
                                                value="incomplete" {{ $status == 'incomplete' ? 'checked' : '' }}>
                                        </td>
                                        <td>
                                            <input type="text" class="form-control remarks-input" 
                                                data-student-id="{{ $student->admission->gr_no ?? 0 }}" 
                                                data-assignment-id="{{ $assignment->id }}" 
                                                value="{{ $remarks }}" placeholder="Enter remarks">
                                        </td>
                                        <td class="faculty-name-box">
                                            @if($student->report)
                                                <span class="badge bg-light text-dark">
                                                    {{ $student->report->updatedByUser?->name ?? $student->report->createdByUser?->name ?? 'N/A' }}
                                                </span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td class="faculty-time-box">
                                            @if($student->report && $student->report->updated_at)
                                                <small class="text-muted">
                                                    {{ $student->report->updated_at->format('d-m-Y h:i A') }}
                                                </small>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
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
$(document).ready(function() {
    let table = $('#report-datatables').DataTable({
        responsive: true,
        searching: true,
        paging: true,
        ordering: true,
        info: true,
        pageLength: 25,
        lengthMenu: [ [25, 50, 100], [25, 50, 100] ],
        dom: 't<"row mt-3"<"col-sm-12 col-md-4"l><"col-sm-12 col-md-4"i><"col-sm-12 col-md-4"p>>',
        language: {
            searchPlaceholder: 'Search student...',
        }
    });

    // Custom student search filter
    $('#search_id').on('keyup change input', function() {
        let val = $(this).val().trim();
        table.columns().search('');
        if (val === '') {
            table.search('').draw();
        } else if (/^\d+$/.test(val)) {
            // Check if this number exists as a Reg ID in Column 2
            let regIdExists = false;
            table.column(2).data().each(function(cellVal) {
                if (String(cellVal).trim() === val) {
                    regIdExists = true;
                }
            });
            
            if (regIdExists) {
                table.column(2).search('^' + val + '$', true, false).draw();
            } else {
                table.search(val).draw();
            }
        } else {
            table.search(val).draw();
        }
    });

    // Helper to send ajax status/remarks updates
    function updateReport(inputEl, studentId, assignmentId, status, remarks) {
        $.ajax({
            type: "POST",
            url: "{{ route('assignment.save-report-ajax') }}",
            headers: {
                "X-CSRF-TOKEN": "{{ csrf_token() }}"
            },
            data: {
                student_id: studentId,
                assignment_id: assignmentId,
                status: status,
                remarks: remarks
            },
            success: function(data) {
                if (data.success) {
                    toastr.success("Report updated successfully!", "Success");
                    // Update the faculty name and time in separate columns
                    let row = inputEl.closest('tr');
                    row.find('.faculty-name-box').html(`
                        <span class="badge bg-light text-dark">${data.faculty_name}</span>
                    `);
                    row.find('.faculty-time-box').html(`
                        <small class="text-muted">${data.time}</small>
                    `);
                } else {
                    toastr.error(data.message || "Something went wrong!", "Error");
                }
            },
            error: function(err) {
                toastr.error("Failed to save report status.", "Error");
            }
        });
    }

    // Save status when radio button is clicked
    $('#report-datatables').on('change', '.status-radio', function() {
        let inputEl = $(this);
        let studentId = $(this).data('student-id');
        let assignmentId = $(this).data('assignment-id');
        let status = $(this).val();
        let remarks = $(this).closest('tr').find('.remarks-input').val();
        updateReport(inputEl, studentId, assignmentId, status, remarks);
    });

    $('#report-datatables').on('blur', '.remarks-input', function() {
        let inputEl = $(this);
        let studentId = $(this).data('student-id');
        let assignmentId = $(this).data('assignment-id');
        let status = $(this).closest('tr').find('.status-radio:checked').val() || '';
        let remarks = $(this).val();
        updateReport(inputEl, studentId, assignmentId, status, remarks);
    });

    $('#report-datatables').on('keypress', '.remarks-input', function(e) {
        if (e.which == 13) { // Enter key
            e.preventDefault();
            $(this).blur();
        }
    });
});
</script>
@include('software.utils.getCourceRegistration')
@endsection
