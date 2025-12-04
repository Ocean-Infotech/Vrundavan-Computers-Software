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
    <link rel="stylesheet" href="{{ asset('admin/assets/cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css') }}">
    <link rel="stylesheet"
        href="{{ asset('admin/assets/cdn.datatables.net/responsive/2.2.9/css/responsive.bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('admin/assets/cdn.datatables.net/buttons/2.2.2/css/buttons.dataTables.min.css') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
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
            'show_back_btn' => false,
        ])
    </div>

    <div class="row my-3">
        <div class="row mb-3">
            <!-- Student Search -->
            <div class="col-md-3 col-sm-12 mb-3">
                <div class="form-group">
                    <label class="form-label">Search Student</label>
                    <input list="search" id="search_id" name="search"
                        class="form-control @error('search') is-invalid @enderror"
                        placeholder="Search by Student ID or Name">

                    <datalist id="search" class="search_by_courceregistration" data-append="search_by_courceregistration"
                        data-selectedCourceRegistrationId="">
                    </datalist>



                    @error('search')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>
            </div>
            <div class="col-md-12 mb-3 d-flex flex-wrap">

                <div class="col-md-3 col-sm-6 mb-2">
                    <label class="fw-bold">Student Name :</label>
                    <span id="selected_student_name" class="fw-bold text-dark"></span>
                </div>

                <div class="col-md-3 col-sm-6 mb-2">
                    <label class="fw-bold">Student Id :</label>
                    <span id="selected_student_id" class="fw-bold text-dark"></span>
                </div>

                <div class="col-md-3 col-sm-6 mb-2">
                    <label class="fw-bold">Admission Id :</label>
                    <span id="selected_admission_id" class="fw-bold text-dark"></span>
                </div>

                <div class="col-md-3 col-sm-6 mb-2">
                    <label class="fw-bold">Course Name :</label>
                    <span id="selected_course_name" class="fw-bold text-dark"></span>
                </div>

            </div>


            {{-- <div class="col-md-3 col-sm-12 mb-3">
                <div class="form-group">
                    <label class="form-label">Select Course <span class="text-danger">*</span></label>
                    <select id="course" name="course_id"
                        class="form-control search_by_course select2 @error('course_id') is-invalid @enderror"
                        data-append="search_by_course"
                        data-selectedCourseId="{{ isset($edit) && $edit?->course_id ? $edit?->course_id : old('course_id') }}"
                        autofocus required>
                        <option value="">Select Course</option>
                    </select>

                    @error('course_id')
                        <span class="invalid-feedback">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
            </div>

            <div class="col-md-3 col-sm-12 mb-3">
                <div class="form-group">
                    <label class="form-label">Select Batch <span class="text-danger">*</span></label>
                    <select id="batch" name="batch_id"
                        class="form-control search_by_batch select2 @error('batch_id') is-invalid @enderror select_filter"
                        data-append="search_by_batch"
                        data-selectedBatchId="{{ isset($edit) && $edit?->batch_id ? $edit?->batch_id : old('batch_id') }}"
                        autofocus>
                        <option value="">Select Batch</option>
                    </select>

                    @error('batch_id')
                        <span class="invalid-feedback">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
            </div>

            <div class="col-md-3 col-sm-12 mb-3">
                <div class="form-group">
                    <label for="date_range" class="form-label">Select Subject </label>
                    <select id="subject" name="subject_name" class="form-control select2">
                        <option value="">Select Subject</option>
                        @foreach ($subjects as $subject)
                            <option value="{{ $subject }}">{{ $subject }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="col-md-3 col-sm-12 mb-3">
                <div class="form-group">
                    <label for="date" class="form-label">Select Date</label>
                    <input type="date" id="date" name="date" class="form-control" />
                </div>
            </div> --}}
        </div>

        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="yajra-datatables" class="table table-hover dt-responsive nowrap" style="width: 100%">
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
    <input type="hidden" id="admission_id" value="">

@endsection

@section('page_script_file')
    <script src="{{ asset('admin/assets/cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('admin/assets/cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js') }}"></script>
    <script src="{{ asset('admin/assets/cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
@endsection

@section('page_leavel_script')
    <script>
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        let dtable;

        $(document).ready(function() {
            dtable = $('#yajra-datatables').DataTable({
                processing: true,
                serverSide: true,
                order: [
                    [1, 'desc']
                ],
                searching: true,
                ajax: {
                    url: "{{ route($route . '.index') }}",
                    type: "GET",
                    data: function(data) {
                        data.admission_id = $('#admission_id').val();
                        data.date = $('#date').val();
                        data.course_id = $('#course').val();
                        data.batch_id = $('#batch').val();
                        data.subject_name = $('#subject').val();
                        data.student_search = $('#search_id').val();
                        data.search = $('input[type="search"]').val();
                    }
                },
                columns: {!! json_encode($columns) !!},
                language: {
                    searchPlaceholder: 'Search...',
                    emptyTable: "No data available in table"
                },
                drawCallback: function(settings) {
                    let api = this.api();
                    let data = api.rows({
                        page: 'current'
                    }).data();

                    let response = settings.json;
                    let student = response.student_details;

                    if (student) {
                        $("#selected_student_name").text(student.student_name ?? '');
                        $("#selected_student_id").text(student.student_id ?? '');
                        $("#selected_admission_id").text(student.admission_id ?? '');
                        $("#selected_course_name").text(student.course_name ?? '');
                    } else {
                        $("#selected_student_name").text('');
                        $("#selected_student_id").text('');
                        $("#selected_admission_id").text('');
                        $("#selected_course_name").text('');
                    }
                    let studentSearch = $('#search_id').val();
                    let admissionId = $('#admission_id').val();

                    // 🟢 Show selected student name below search
                    if (data.length > 0 && studentSearch) {
                        $("#selected_student_name").text(data[0].student_name ?? '');
                    } else {
                        $("#selected_student_name").text('');
                    }

                    // Check if student is searched but no data found
                    if (data.length === 0 && (studentSearch || admissionId)) {
                        $('#yajra-datatables tbody').html(`
            <tr class="odd">
                <td valign="top" colspan="{{ count($columns) }}" class="dataTables_empty text-center text-danger">
                    <strong>No Test Report Found for this Student.</strong>
                </td>
            </tr>
        `);
                    }
                    // Show default message when no student is selected
                    else if (data.length === 0 && !studentSearch && !admissionId) {
                        $('#yajra-datatables tbody').html(`
            <tr class="odd">
                <td valign="top" colspan="{{ count($columns) }}" class="dataTables_empty text-center text-danger">
                    Please select <strong>Student ID</strong> or <strong>Name</strong>.
                </td>
            </tr>
        `);
                    }
                }

            });

            // Check localStorage for admission_id
            var admissionId = localStorage.getItem('admission_id');
            if (admissionId) {
                $('#admission_id').val(admissionId);
                $('#search_id').val(admissionId);
                localStorage.removeItem('admission_id');

                setTimeout(function() {
                    dtable.draw();
                }, 500);
            }

            // Student search functionality
            $("#search_id").on('keyup change', function() {
                dtable.draw();
            });

            $('#filter-btn').on('click', function() {
                if (!$('#date').val() && !$('#batch').val() && !$('#subject').val()) {
                    toastr.warning("Please select at least one filter before searching.");
                    return;
                }
                dtable.draw();
            });

            $(document).on('change', '#date, #batch, #subject, #course', function() {
                dtable.draw();
            });

            $(document).on('keyup', 'input[type="search"]', function() {
                dtable.draw();
            });
        });
    </script>

    @include('software.utils.getCourse')
    @include('software.utils.getBatchByCourseid')
    @include('software.utils.getCourceRegistration')
    @include('software.includes.script-delete-record')
    @include('software.includes.script-restore-record')
    @include('software.includes.script-update-status')
@endsection
