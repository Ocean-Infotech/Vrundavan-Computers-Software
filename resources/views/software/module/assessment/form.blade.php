@extends('software.layout.app')

@php
    $page_title = isset($modules['title']) ? $modules['title'] : null;
    $folder_path = isset($modules['folder_path']) ? $modules['folder_path'] : null;
    $route = isset($modules['route']) ? $modules['route'] : null;
    $permission_prefix = isset($modules['permission_prefix']) ? $modules['permission_prefix'] : null;
@endphp

@section('title', isset($edit) && $edit?->id ? 'Edit ' . $page_title : 'Create ' . $page_title)

@section('page_style_file')
    <!-- select2 css -->
    <link href="{{ asset('public/assets/admin/plugins/select2/css/select2.min.css') }}" rel="stylesheet" />
@endsection

@section('breadcrumb')
    <div class="d-flex justify-content-lg-between px-1">
        @include('software.includes.breadcrumb', [
            'breadcrumbArray' => [
                ['title' => $page_title, 'url' => route($route . '.index')],
                [
                    'title' => isset($edit) && $edit?->id ? 'Edit ' . $page_title : 'Create ' . $page_title,
                    'url' => '',
                ],
            ],
            'page_title' => $page_title,
            'route' => $route,
            'show_add_btn' => false,
            'show_filter_btn' => false,
            'show_back_btn' => true,
        ])
    </div>
@endsection

@section('content')
    <div class="card shadow my-4">
        <div class="card-body mt-4">
            <form id="AssessmentForm"
                action="{{ isset($edit) && $edit?->id ? route($route . '.update', [$edit?->id]) : route($route . '.store') }}"
                method="POST" autocomplete="off">
                @csrf
                @isset($edit)
                    @method('PUT')
                @endisset

                @if (isset($edit))
                    <!-- Single Student Edit Form -->
                    <div class="row">
                        <!-- Student Selection -->
                        <div class="col-md-6 col-sm-12 mb-3">
                            <div class="form-group">
                                <label class="form-label">Student <span class="text-danger">*</span></label>
                                <select id="admission_id" name="admission_id"
                                    class="form-control select2 @error('admission_id') is-invalid @enderror" required>
                                    @foreach ($students as $student)
                                        <option value="{{ $student->id }}"
                                            @if ($edit->admission_id == $student->id) selected @endif>
                                            {{ trim(($student->first_name ?? '') . ' ' . ($student->last_name ?? '') . ' ' . ($student->father_name ?? '')) }} (GR: {{ $student->gr_no ?? '-' }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('admission_id')
                                    <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                                @enderror
                            </div>
                        </div>

                        <!-- Subject -->
                        <div class="col-md-6 col-sm-12 mb-3">
                            <div class="form-group">
                                <label class="form-label">Subject <span class="text-danger">*</span></label>
                                <input type="text" name="subject" class="form-control @error('subject') is-invalid @enderror"
                                    value="{{ $edit->subject }}" placeholder="Enter Subject" required>
                                @error('subject')
                                    <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <!-- Performance -->
                        <div class="col-md-6 col-sm-12 mb-3">
                            <div class="form-group">
                                <label class="form-label d-block">Performance <span class="text-danger">*</span></label>
                                <div class="d-flex gap-3 mt-2">
                                    <div class="form-check">
                                        <input class="form-check-input @error('performance') is-invalid @enderror" type="radio" name="performance" id="performance_best" value="best"
                                            {{ $edit->performance == 'best' ? 'checked' : '' }} required>
                                        <label class="form-check-label" for="performance_best">Best</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input @error('performance') is-invalid @enderror" type="radio" name="performance" id="performance_good" value="good"
                                            {{ $edit->performance == 'good' ? 'checked' : '' }} required>
                                        <label class="form-check-label" for="performance_good">Good</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input @error('performance') is-invalid @enderror" type="radio" name="performance" id="performance_average" value="average"
                                            {{ $edit->performance == 'average' ? 'checked' : '' }} required>
                                        <label class="form-check-label" for="performance_average">Average</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input @error('performance') is-invalid @enderror" type="radio" name="performance" id="performance_poor" value="poor"
                                            {{ $edit->performance == 'poor' ? 'checked' : '' }} required>
                                        <label class="form-check-label" for="performance_poor">Poor</label>
                                    </div>
                                </div>
                                @error('performance')
                                    <span class="invalid-feedback d-block"><strong>{{ $message }}</strong></span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <!-- Remarks -->
                        <div class="col-md-12 mb-3">
                            <div class="form-group">
                                <label class="form-label">Remarks</label>
                                <textarea name="remarks" class="form-control @error('remarks') is-invalid @enderror" rows="4"
                                    placeholder="Enter remarks...">{{ $edit->remarks }}</textarea>
                                @error('remarks')
                                    <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                                @enderror
                            </div>
                        </div>
                    </div>
                @else
                    <!-- Bulk Student Assessment Entry Form -->
                    <div class="row">
                        <!-- Course Select -->
                        <div class="col-md-4 col-sm-12 mb-3">
                            <div class="form-group">
                                <label class="form-label">Course <span class="text-danger">*</span></label>
                                <select class="form-control select2" id="courseSelect" required>
                                    <option value="">Select Course</option>
                                    @foreach ($courses as $id => $course_name)
                                        <option value="{{ $id }}">{{ $course_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- Batch Select -->
                        <div class="col-md-4 col-sm-12 mb-3">
                            <div class="form-group">
                                <label class="form-label">Batch</label>
                                <select class="form-control select2" id="batchSelect">
                                    <option value="">Select Batch</option>
                                </select>
                            </div>
                        </div>

                        <!-- Class Select -->
                        <div class="col-md-4 col-sm-12 mb-3">
                            <div class="form-group">
                                <label class="form-label">Class</label>
                                <select class="form-control select2" id="classSelect">
                                    <option value="">Select Class</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Common Subject Auto Fill -->
                    <div class="row mb-3">
                        <div class="col-md-6 col-sm-12">
                            <div class="form-group">
                                <label class="form-label"><strong>Common Subject</strong> (Type to fill for all students)</label>
                                <input type="text" id="common_subject" class="form-control" placeholder="Type common subject name...">
                            </div>
                        </div>
                    </div>

                    <!-- Students Grid -->
                    <div class="row mt-4">
                        <div class="col-md-12 table-responsive">
                            <table class="table table-bordered table-striped" id="StudentGridTable">
                                <thead>
                                    <tr>
                                        <th width="5%" class="text-center">
                                            <input type="checkbox" id="select_all_students" class="form-check-input" checked>
                                        </th>
                                        <th width="25%">Student Name</th>
                                        <th width="20%">Subject <span class="text-danger">*</span></th>
                                        <th width="30%">Performance <span class="text-danger">*</span></th>
                                        <th width="20%">Remarks</th>
                                    </tr>
                                </thead>
                                <tbody id="studentTableBody">
                                    <tr>
                                        <td colspan="5" class="text-center text-muted">Please select Course, Batch, and Class to load student list.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif

                <div class="row">
                    <div class="col-md-12 text-center mt-3">
                        <button type="submit" class="btn btn-success me-2">{{ isset($edit) ? 'Update' : 'Submit' }}</button>
                        <a href="{{ route($route . '.index') }}" class="btn btn-danger">Cancel</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('page_leavel_script')
    <script>
        $(document).ready(function() {
            if ($.fn.select2) {
                $('.select2').select2({
                    width: '100%'
                });
            }

            @if(!isset($edit))
            var courseSelect = $('#courseSelect');
            var batchSelect = $('#batchSelect');
            var classSelect = $('#classSelect');

            function fetchStudents() {
                let courseId = courseSelect.val();
                let batchId = batchSelect.val();
                let classId = classSelect.val();

                if (!courseId || !batchId || !classId) {
                    $('#studentTableBody').html('<tr><td colspan="5" class="text-center text-muted">Please select Course, Batch, and Class to load student list.</td></tr>');
                    return;
                }

                $('#studentTableBody').html('<tr><td colspan="5" class="text-center">Loading students...</td></tr>');

                $.ajax({
                    url: "{{ url('software/get-students-by-course') }}/" + courseId,
                    type: "GET",
                    data: {
                        batch_id: batchId,
                        class_id: classId
                    },
                    success: function(data) {
                        if (data.length === 0) {
                            $('#studentTableBody').html('<tr><td colspan="5" class="text-center">No students found for this combination.</td></tr>');
                            return;
                        }
                        let rows = '';
                        let commonSubject = $('#common_subject').val() || '';
                        $.each(data, function(index, student) {
                            rows += `
                            <tr>
                                <td class="text-center">
                                    <input type="checkbox" class="form-check-input student-select" checked>
                                </td>
                                <td>
                                    <strong>${student.name}</strong>
                                    <input type="hidden" name="assessments[${index}][admission_id]" value="${student.id}" class="student-admission-id">
                                </td>
                                <td>
                                    <input type="text" name="assessments[${index}][subject]" class="form-control student-subject" value="${commonSubject}" placeholder="Enter Subject" required>
                                </td>
                                <td>
                                    <div class="d-flex gap-3">
                                        <div class="form-check">
                                            <input class="form-check-input student-performance" type="radio" name="assessments[${index}][performance]" id="perf_best_${student.id}" value="best" required>
                                            <label class="form-check-label" for="perf_best_${student.id}">Best</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input student-performance" type="radio" name="assessments[${index}][performance]" id="perf_good_${student.id}" value="good" required>
                                            <label class="form-check-label" for="perf_good_${student.id}">Good</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input student-performance" type="radio" name="assessments[${index}][performance]" id="perf_avg_${student.id}" value="average" required>
                                            <label class="form-check-label" for="perf_avg_${student.id}">Average</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input student-performance" type="radio" name="assessments[${index}][performance]" id="perf_poor_${student.id}" value="poor" required>
                                            <label class="form-check-label" for="perf_poor_${student.id}">Poor</label>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <input type="text" name="assessments[${index}][remarks]" class="form-control student-remarks" placeholder="Enter remarks">
                                </td>
                            </tr>
                            `;
                        });
                        $('#studentTableBody').html(rows);
                        toggleRowInputs();
                    },
                    error: function(xhr) {
                        $('#studentTableBody').html('<tr><td colspan="5" class="text-center text-danger">Error loading students.</td></tr>');
                    }
                });
            }

            function toggleRowInputs() {
                $('.student-select').each(function() {
                    let isChecked = $(this).is(':checked');
                    let row = $(this).closest('tr');
                    // Enable/disable inputs on this row
                    row.find('input:not(.student-select)').prop('disabled', !isChecked);
                });
            }

            // Sync inputs with Checkbox
            $(document).on('change', '.student-select', function() {
                toggleRowInputs();
            });

            // Master Select All checkbox
            $(document).on('change', '#select_all_students', function() {
                let isChecked = $(this).is(':checked');
                $('.student-select').prop('checked', isChecked);
                toggleRowInputs();
            });

            // Auto-fill common subject in all rows
            $('#common_subject').on('input', function() {
                let val = $(this).val();
                $('.student-subject').val(val);
            });

            // Course selection AJAX for Batch
            courseSelect.on('change', function() {
                let courseId = $(this).val();
                
                batchSelect.empty().append('<option value="">Select Batch</option>').val('').trigger('change.select2');
                classSelect.empty().append('<option value="">Select Class</option>').val('').trigger('change.select2');

                if (courseId) {
                    $.ajax({
                        type: 'POST',
                        url: "{{ route('get-batch') }}",
                        dataType: 'json',
                        data: {
                            _token: '{{ csrf_token() }}',
                            course_id: courseId
                        },
                        success: function(response) {
                            if ((response.status === true || response.status === "true") && response.data) {
                                let options = '<option value="">Select Batch</option>';
                                response.data.forEach(function(item) {
                                    options += `<option value="${item.id}">${item.name}</option>`;
                                });
                                batchSelect.html(options).trigger('change.select2');
                            }
                        }
                    });
                }
                fetchStudents();
            });

            // Batch selection AJAX for Class
            batchSelect.on('change', function() {
                let batchId = $(this).val();

                classSelect.empty().append('<option value="">Select Class</option>').val('').trigger('change.select2');

                if (batchId) {
                    $.ajax({
                        type: 'POST',
                        url: "{{ route('get-class-bybatch') }}",
                        dataType: 'json',
                        data: {
                            _token: '{{ csrf_token() }}',
                            batch_id: batchId
                        },
                        success: function(response) {
                            if ((response.status === true || response.status === "true") && response.data) {
                                let options = '<option value="">Select Class</option>';
                                response.data.forEach(function(item) {
                                    options += `<option value="${item.id}">${item.name}</option>`;
                                });
                                classSelect.html(options).trigger('change.select2');
                            }
                        }
                    });
                }
                fetchStudents();
            });

            // Class selection
            classSelect.on('change', function() {
                fetchStudents();
            });
            @endif
        });
    </script>
@endsection
