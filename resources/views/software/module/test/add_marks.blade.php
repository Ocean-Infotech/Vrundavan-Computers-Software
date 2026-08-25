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
            'show_filter_btn' => false,
            'show_export_btns' => false,
            'show_excal_btn' => false,
            'show_print_btn' => false,
            'show_back_btn' => true,
        ])
    </div>

    <div class="row my-3">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="row align-items-center">
                        <div class="col-md-3 d-flex align-items-center justify-content-between">
                            <h4 class="mb-0">{{ $modules['title'] }} Mark Entry Form</h4>
                            <a href="{{ route('test.print-marks', [$test->id]) }}" target="_blank" class="btn btn-primary btn-sm ms-2">
                                <i class="fa-solid fa-print me-1"></i> Print Report
                            </a>
                        </div>
                        <div class="col-md-9">
                            <div class="row">
                                <div class="col-md-4 mb-2">
                                    <strong>Batch:</strong> {{ $test->batch->batch_name ?? 'N/A' }}
                                </div>
                                <div class="col-md-4 mb-2">
                                    <strong>Subject:</strong> {{ $test->subject_name ?? 'N/A' }}
                                </div>
                                <div class="col-md-4 mb-2">
                                    <strong>Unit:</strong> {{ $test->unit_name ?? 'N/A' }}
                                </div>
                                <div class="col-md-4">
                                    <strong>Faculty Name:</strong> {{ $test->createdByUser?->name ?? 'N/A' }}
                                </div>
                                <div class="col-md-4">
                                    <strong>Date & Time:</strong> {{ $test->created_at ? $test->created_at->format('d-m-Y h:i A') : 'N/A' }}
                                </div>
                                <div class="col-md-4">
                                    <strong>Max Marks:</strong> {{ $test->mark ?? 'N/A' }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <form action="{{ route('test.save-marks') }}" method="POST">
                        @csrf
                        <input type="hidden" name="test_id" value="{{ $test->id }}">

                        <div class="table-responsive">
                            <table id="yajra-datatables" class="table table-striped table-hover dt-responsive nowrap" style="width: 100%">
                                <thead>
                                    <tr>
                                        <th>Student ID</th>
                                        <th>Name</th>
                                        <th>Mark</th>
                                        <th>Faculty Name</th>
                                        <th>Date & Time</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($students as $student)
                                        <tr>
                                            <td>{{ $student->id }}</td>
                                            <td>{{ $student->admission->first_name }} {{ $student->admission->last_name }}
                                            </td>
                                            <td>
                                                <input type="number" class="form-control mark-input"
                                                    data-student-id="{{ $student->id }}"
                                                    data-test-id="{{ $test->id }}"
                                                    value="{{ $student->admission->testMarks->isNotEmpty() ? number_format($student->admission->testMarks->first()->marks, 2) : 0 }}"
                                                    min="0" max="100" placeholder="Enter marks">
                                            </td>
                                            @php
                                                $markInfo = $student->admission->testMarks->first();
                                            @endphp
                                            <td class="faculty-name-box">
                                                @if($markInfo)
                                                    <span class="badge bg-light text-dark">
                                                        {{ $markInfo->updatedByUser?->name ?? $markInfo->createdByUser?->name ?? 'N/A' }}
                                                    </span>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td class="faculty-time-box">
                                                @if($markInfo)
                                                    <small class="text-muted">
                                                        {{ $markInfo->updated_at ? $markInfo->updated_at->format('d-m-Y h:i A') : '' }}
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
                    </form>
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

    $.fn.dataTable.ext.order['dom-text-numeric'] = function(settings, col) {
        return this.api()
            .column(col, { order: 'index' })
            .nodes()
            .map(function(td) {
                let val = $('input', td).val();
                return val ? parseFloat(val) : 0;
            });
    };

    $('#yajra-datatables').DataTable({
        responsive: true,
        searching: true,
        paging: true,
        ordering: true,
        info: true,
        pageLength: 25,
        lengthMenu: [ [25, 50, 100], [25, 50, 100] ],
        language: {
            searchPlaceholder: 'Search student...',
        },
        columnDefs: [
            {
                targets: 2,
                orderDataType: 'dom-text-numeric'
            }
        ]
    });

    // ✅ Event Delegation - table પર event listener લગાવો
    $('#yajra-datatables').on('change', '.mark-input', function() {
        let inputEl = $(this);
        let mark = parseFloat(this.value);
        let studentId = this.dataset.studentId;
        let testId = this.dataset.testId;
        let maxMark = parseFloat("{{ $test->mark }}");

        if (mark > maxMark) {
            toastr.error("Marks cannot exceed maximum allowed.", "Invalid Marks");
            this.value = '';
            return;
        }

        fetch("{{ route('test.save-marks-ajax') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                },
                body: JSON.stringify({
                    student_id: studentId,
                    test_id: testId,
                    mark: mark
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    toastr.success("Marks saved successfully!", "Success");
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
            })
            .catch(err => {
                toastr.error("Failed to save mark. Please try again.", "Error");
            });
    });
});
</script>

@endsection
