@extends('software.layout.app')

@php
    $page_title = isset($modules['title']) ? $modules['title'] : null;
    $folder_path = isset($modules['folder_path']) ? $modules['folder_path'] : null;
    $route = isset($modules['route']) ? $modules['route'] : null;
    $permission_prefix = isset($modules['permission_prefix']) ? $modules['permission_prefix'] : null;
@endphp
@section('title', $page_title)

@section('content')
    <div class="d-flex justify-content-lg-between px-1">
        @include('software.includes.breadcrumb', [
            'breadcrumbArray' => [
                ['title' => $page_title, 'url' => route($route . '.index')],
                [
                    'title' => isset($edit) && $edit?->id ? 'Edit ' . $page_title : 'Create ' . $page_title,
                    'url' => '',
                ],
            ],
            'route' => $route,
            'show_add_btn' => false,
            'show_back_btn' => true,
        ])
    </div>

    <div class="card shadow">
        <div class="card-body">
            <form
                action="{{ isset($edit) && $edit?->id ? route($route . '.update', [$edit?->id]) : route($route . '.store') }}"
                method="POST">
                @csrf
                @isset($edit)
                    @method('PUT')
                    <input type="hidden" name="id" value="{{ $edit?->id ?? '' }}" />
                @endisset

                @if (!isset($edit))
                    <!-- Filters -->
                    <div class="row mb-3">
                        <!-- Course Filter -->
                        <div class="col-md-4 col-sm-12 mb-2">
                            <div class="form-group">
                                <label class="form-label">Course <span class="text-danger">*</span></label>
                                <select id="filter_course_id" class="form-control select2" required>
                                    <option value="">Select Course</option>
                                    @foreach ($courses as $course)
                                        <option value="{{ $course->id }}">{{ $course->course_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <!-- Batch Filter -->
                        <div class="col-md-4 col-sm-12 mb-2">
                            <div class="form-group">
                                <label class="form-label">Batch <span class="text-danger">*</span></label>
                                <select id="filter_batch_id" class="form-control select2" required>
                                    <option value="">Select Batch</option>
                                </select>
                            </div>
                        </div>
                        <!-- Class Filter -->
                        <div class="col-md-4 col-sm-12 mb-2">
                            <div class="form-group">
                                <label class="form-label d-block">Class</label>
                                <div id="class_checkboxes_container" class="d-flex flex-wrap gap-3 mt-2 border p-2 rounded bg-white" style="min-height: 38px; align-items: center;">
                                    <span class="text-muted small">Select Batch first</span>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                <div class="row">
                    <!-- Select Student -->
                    <div class="col-md-4 col-sm-12 mb-3">
                        <div class="form-group">
                            <label class="form-label">Select Student <span class="text-danger">*</span></label>
                            @if (!isset($edit))
                                <div class="card p-2" style="max-height: 250px; overflow-y: auto; border: 1px solid #ccc;">
                                    <input type="text" id="student_search" class="form-control mb-2" placeholder="Search student...">
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" id="select_all_students">
                                        <label class="form-check-label fw-bold" for="select_all_students">Select All</label>
                                    </div>
                                    <div id="student_checkbox_list">
                                        <span class="text-muted small">Select Course, Batch, Class to load students</span>
                                    </div>
                                </div>
                            @else
                                <select name="admission_id"
                                    class="form-control select2 @error('admission_id') is-invalid @enderror" required>
                                    <option value="" disabled>Select Student</option>
                                    @foreach ($students as $student)
                                        <option value="{{ $student->id }}"
                                            @if ($edit->admission_id == $student->id) selected
                                            @elseif (old('admission_id') == $student->id) selected @endif>
                                            {{ $student->id }} -
                                            {{ trim(($student->first_name ?? '') . ' ' . ($student->last_name ?? '') . ' ' . ($student->father_name ?? '')) }}
                                        </option>
                                    @endforeach
                                </select>
                            @endif
                            @error('admission_id')
                                <span class="invalid-feedback d-block"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>

                    <!-- Date -->
                    <div class="col-md-4 col-sm-12 mb-3">
                        <div class="form-group">
                            <label class="form-label">Date <span class="text-danger">*</span></label>
                            <input type="date" name="date" class="form-control @error('date') is-invalid @enderror"
                                value="{{ isset($edit->date) ? \Carbon\Carbon::parse($edit->date)->format('Y-m-d') : old('date', date('Y-m-d')) }}"
                                required>
                            @error('date')
                                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>

                    <!-- Faculty User Dropdown -->
                    <div class="col-md-4 col-sm-12 mb-3">
                        <div class="form-group">
                            <label class="form-label">Faculty User <span class="text-danger">*</span></label>
                            <select name="faculty_id" class="form-control select2 @error('faculty_id') is-invalid @enderror"
                                required>
                                <option value="" disabled {{ !isset($edit) ? 'selected' : '' }}>Select Faculty
                                </option>
                                @foreach ($faculties as $faculty)
                                    <option value="{{ $faculty->id }}"
                                        @if (isset($edit) && $edit->faculty_id == $faculty->id) selected
                                        @elseif(old('faculty_id') == $faculty->id) selected @endif>
                                        {{ $faculty->name }} ({{ $faculty->email ?? '' }})
                                    </option>
                                @endforeach
                            </select>
                            @error('faculty_id')
                                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>

                    <!-- Complaint Textarea -->
                    <div class="col-12 mb-3">
                        <div class="form-group">
                            <label class="form-label">Complaint Details <span class="text-danger">*</span></label>
                            <textarea id="complaint" name="complaint" rows="3" class="form-control @error('complaint') is-invalid @enderror"
                                placeholder="Enter complaint details..." required>{{ isset($edit->complaint) ? $edit->complaint : old('complaint') }}</textarea>
                            @error('complaint')
                                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-12 text-center mt-3">
                        <button type="submit" class="btn btn-success me-2">
                            {{ isset($edit) ? 'Update' : 'Submit' }}
                        </button>
                        <a href="{{ route($route . '.index') }}" class="btn btn-danger">Cancel</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('page_script_file')
    <script src="{{ asset('admin/assets/vendor/libs/autosize/autosize.js') }}"></script>
@endsection

@section('page_leavel_script')
    <script>
        $(document).ready(function() {
            if ($.fn.select2) {
                $('.select2').select2({
                    placeholder: "Select an option",
                    allowClear: true
                });
            }

            @if (!isset($edit))
            function loadFilteredStudents() {
                let courseId = $('#filter_course_id').val();
                let batchId = $('#filter_batch_id').val();
                let classIds = [];
                $('.filter-class-checkbox:checked').each(function() {
                    classIds.push($(this).val());
                });

                if (!courseId || !batchId || classIds.length === 0) {
                    $('#student_checkbox_list').html('<span class="text-muted small">Select Course, Batch, Class to load students</span>');
                    return;
                }

                $('#student_checkbox_list').html('<span class="text-muted small">Loading students...</span>');

                $.ajax({
                    type: 'GET',
                    url: "{{ route('marksheet-issue.get-students') }}",
                    data: {
                        course_id: courseId,
                        batch_id: batchId,
                        class_id: classIds
                    },
                    success: function(response) {
                        if (response.success) {
                            let container = $('#student_checkbox_list');
                            container.empty();

                            if (response.data.length === 0) {
                                container.append('<span class="text-muted small">No students found.</span>');
                                return;
                            }

                            response.data.forEach(function(student) {
                                let studentText = student.id + ' - ' + student.full_name;
                                container.append(`
                                    <div class="form-check student-checkbox-item mb-1">
                                        <input class="form-check-input student-checkbox" type="checkbox" name="admission_id[]" value="${student.id}" id="student_${student.id}">
                                        <label class="form-check-label" for="student_${student.id}">
                                            ${studentText}
                                        </label>
                                    </div>
                                `);
                            });
                            
                            $('#select_all_students').prop('checked', false);
                        }
                    }
                });
            }

            // Select All students
            $(document).on('change', '#select_all_students', function() {
                let isChecked = $(this).is(':checked');
                $('.student-checkbox').prop('checked', isChecked);
            });

            // Dependent dropdown: Course -> Batch
            $('#filter_course_id').on('change', function() {
                let courseId = $(this).val();
                let batchSelect = $('#filter_batch_id');

                batchSelect.empty().append('<option value="">Select Batch</option>');
                $('#class_checkboxes_container').html('<span class="text-muted small">Select Batch first</span>');
                batchSelect.trigger('change.select2');

                if (courseId) {
                    $.ajax({
                        type: 'POST',
                        url: "{{ route('get-batch') }}",
                        data: {
                            _token: '{{ csrf_token() }}',
                            course_id: courseId
                        },
                        success: function(response) {
                            if (response.status && response.data) {
                                response.data.forEach(function(batch) {
                                    batchSelect.append(`<option value="${batch.id}">${batch.name}</option>`);
                                });
                                batchSelect.trigger('change.select2');
                            }
                        }
                    });
                }
                loadFilteredStudents();
            });

            // Dependent dropdown: Batch -> Class Checkboxes
            $('#filter_batch_id').on('change', function() {
                let batchId = $(this).val();
                let container = $('#class_checkboxes_container');

                container.html('<span class="text-muted small">Loading classes...</span>');

                if (batchId) {
                    $.ajax({
                        type: 'POST',
                        url: "{{ route('get-class-bybatch') }}",
                        data: {
                            _token: '{{ csrf_token() }}',
                            batch_id: batchId
                        },
                        success: function(response) {
                            if (response.status && response.data) {
                                let checkboxesHtml = '';
                                response.data.forEach(function(cls) {
                                    checkboxesHtml += `
                                        <div class="form-check me-3">
                                            <input class="form-check-input filter-class-checkbox" type="checkbox" value="${cls.id}" id="class_chk_${cls.id}">
                                            <label class="form-check-label" for="class_chk_${cls.id}">
                                                ${cls.name}
                                            </label>
                                        </div>
                                    `;
                                });
                                container.html(checkboxesHtml || '<span class="text-muted small">No classes found</span>');
                            } else {
                                container.html('<span class="text-muted small">No classes found</span>');
                            }
                            loadFilteredStudents();
                        },
                        error: function() {
                            container.html('<span class="text-muted small">Error loading classes</span>');
                            loadFilteredStudents();
                        }
                    });
                } else {
                    container.html('<span class="text-muted small">Select Batch first</span>');
                    loadFilteredStudents();
                }
            });

            // When Class checkboxes change, load students
            $(document).on('change', '.filter-class-checkbox', loadFilteredStudents);
            @endif

            $('#student_search').on('keyup', function() {
                let value = $(this).val().toLowerCase();
                $('#student_checkbox_list .student-checkbox-item').filter(function() {
                    $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
                });
            });

            $('form').on('submit', function(e) {
                if ($('.student-checkbox').length > 0 && $('.student-checkbox:checked').length === 0) {
                    e.preventDefault();
                    alert('Please select at least one student.');
                }
            });

            const complaintTextarea = document.querySelector('#complaint');
            if (complaintTextarea && typeof autosize !== 'undefined') {
                autosize(complaintTextarea);
            }
        });
    </script>
@endsection
