@extends('software.layout.app')

@php
    $page_title = isset($modules['title']) ? $modules['title'] : null;
    $folder_path = isset($modules['folder_path']) ? $modules['folder_path'] : null;
    $route = isset($modules['route']) ? $modules['route'] : null;
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
                method="POST" enctype="multipart/form-data">
                @csrf
                @isset($edit)
                    @method('PUT')
                @endisset

                <div class="row">
                    <div class="col-md-4 col-sm-12 mb-3">
                        <div class="form-group">
                            <label class="form-label">Course <span class="text-danger">*</span></label>
                            <select class="form-control select2 @error('course_id') is-invalid @enderror" name="course_id" id="courseSelect">
                                <option value="">Select Course</option>
                                @foreach ($courses as $id => $course_name)
                                    <option value="{{ $id }}"
                                        {{ (isset($edit) && $edit?->course_id == $id) || old('course_id') == $id ? 'selected' : '' }}>
                                        {{ $course_name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('course_id')
                                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-4 col-sm-12 mb-3">
                        <div class="form-group">
                            <label class="form-label">Batch</label>
                            <select class="form-control select2" name="batch_id" id="batchSelect">
                                <option value="">Select Batch</option>
                                @foreach ($batches as $id => $batch_name)
                                    <option value="{{ $id }}"
                                        {{ (isset($edit) && $edit->batch_id == $id) || old('batch_id') == $id ? 'selected' : '' }}>
                                        {{ $batch_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="col-md-4 col-sm-12 mb-3">
                        <div class="form-group">
                            <label class="form-label d-block">Class</label>
                            <div id="classCheckboxContainer" class="form-control d-flex flex-wrap align-items-center" style="min-height: 38px; border: 1px solid #ced4da; border-radius: 4px; padding: 6px 12px; background: #fff; gap: 15px;">
                                @if (isset($classes) && count($classes) > 0)
                                    @foreach ($classes as $id => $class_name)
                                        <div class="form-check form-check-inline mb-0 me-3">
                                            <input class="form-check-input class-checkbox" type="checkbox" name="class_id[]" value="{{ $id }}" id="class_chk_{{ $id }}"
                                                {{ (isset($edit) && strpos($edit->class_id, (string)$id) !== false) || (is_array(old('class_id')) && in_array($id, old('class_id'))) ? 'checked' : '' }}>
                                            <label class="form-check-label fw-semibold" for="class_chk_{{ $id }}">{{ $class_name }}</label>
                                        </div>
                                    @endforeach
                                @else
                                    <span class="text-muted">Select Class</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="col-md-12 col-sm-12 mb-3">
                        <div class="form-group">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="form-label mb-0">Student <span class="text-danger">*</span></label>
                                <button type="button" class="btn btn-sm btn-link p-0 text-danger" id="clearStudentsBtn" style="text-decoration: none;"><i class="fa-solid fa-xmark me-1"></i>Clear Selection</button>
                            </div>
                            <select class="form-control select2 @error('student_id') is-invalid @enderror"
                                name="student_id[]" id="studentDropdown" multiple>
                                <option value="all">Select All</option>
                                @if (isset($students) && count($students) > 0)
                                    @foreach ($students as $student)
                                        <option value="{{ $student['id'] }}"
                                            {{ in_array($student['id'], $selectedStudents ?? []) ? 'selected' : '' }}>
                                            {{ $student['name'] }}
                                        </option>
                                    @endforeach
                                @endif
                            </select>

                            @error('student_id')
                                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>


                    <div class="col-md-12 col-sm-12 mb-3">
                        <div class="form-group">
                            <label class="form-label">Title <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('title') is-invalid @enderror" name="title"
                                value="{{ isset($edit?->title) ? $edit->title : old('title') }}" placeholder="Enter title">
                            @error('title')
                                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-12 col-sm-12 mt-3">
                        <div class="form-group">
                            <label class="form-label">Body <span class="text-danger">*</span></label>
                            <textarea id="body" name="body" rows="5" class="form-control @error('body') is-invalid @enderror"
                                placeholder="Enter message">{{ isset($edit?->body) ? $edit->body : old('body') }}</textarea>

                            @error('body')
                                <span class="invalid-feedback">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6 col-sm-12 mt-3">
                        <div class="form-group">
                            <label class="form-label">Notification Image</label>
                            <input type="file" name="image" class="form-control @error('image') is-invalid @enderror" accept="image/*">
                            @if(isset($edit) && $edit->image)
                                <div class="mt-2">
                                    <img src="{{ asset($edit->image) }}" class="img-thumbnail" style="max-height: 100px;">
                                </div>
                            @endif
                            @error('image')
                                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6 col-sm-12 mt-3">
                        <div class="form-group">
                            <label class="form-label">Notification PDF Document</label>
                            <input type="file" name="pdf" class="form-control @error('pdf') is-invalid @enderror" accept="application/pdf">
                            @if(isset($edit) && $edit->pdf)
                                <div class="mt-2">
                                    <a href="{{ asset($edit->pdf) }}" target="_blank" class="btn btn-sm btn-info">View Uploaded PDF</a>
                                </div>
                            @endif
                            @error('pdf')
                                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>

                </div>

                <div class="row">
                    <div class="col-md-12 text-center">
                        <button type="submit"
                            class="btn btn-success mt-1 mb-1">{{ isset($edit) ? 'Update' : 'Submit' }}</button>
                        <a href="{{ route($route . '.index') }}" class="btn btn-danger mt-1 mb-1">Cancel</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('page_leavel_script')
    <script>
        $(document).ready(function() {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            var studentDropdown = $('#studentDropdown');
            var courseSelect = $('#courseSelect');
            var batchSelect = $('#batchSelect');
            var classCheckboxContainer = $('#classCheckboxContainer');

            function fetchStudents() {
                let courseId = courseSelect.val();
                let batchId = batchSelect.val();
                
                let classIds = [];
                $('.class-checkbox:checked').each(function() {
                    classIds.push($(this).val());
                });

                if (classIds.length === 0) {
                    studentDropdown.empty().append('<option value="">Select Student</option>').val('').trigger('change.select2');
                    return;
                }

                if (!courseId) {
                    studentDropdown.empty().append('<option value="">Select Student</option>');
                    return;
                }

                studentDropdown.empty().append('<option value="">Loading...</option>');

                $.ajax({
                    url: "{{ url('software/get-students-by-course') }}/" + courseId,
                    type: "GET",
                    data: {
                        batch_id: batchId,
                        class_id: classIds
                    },
                    success: function(data) {
                        studentDropdown.empty().append('<option value="all">Select All</option>');
                        var allIds = [];
                        $.each(data, function(key, student) {
                            studentDropdown.append('<option value="' + student.id + '">' + student.name + '</option>');
                            allIds.push(student.id);
                        });
                        studentDropdown.val(allIds).trigger('change.select2');
                    },
                    error: function(xhr) {
                        studentDropdown.empty().append('<option value="">Error loading students</option>');
                    }
                });
            }

            // Course change AJAX
            courseSelect.on('change', function() {
                let courseId = $(this).val();
                
                // Reset batch, class and student dropdowns
                batchSelect.empty().append('<option value="">Select Batch</option>').val('').trigger('change.select2');
                classCheckboxContainer.empty().html('<span class="text-muted">Select Class</span>');
                studentDropdown.empty().append('<option value="">Select Student</option>').val('').trigger('change.select2');

                if (courseId) {
                    // Fetch Batch list
                    $.ajax({
                        type: 'POST',
                        url: "{{ route('get-batch') }}",
                        dataType: 'json',
                        data: {
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
            });

            // Batch change AJAX
            batchSelect.on('change', function() {
                let batchId = $(this).val();

                // Reset class container and student dropdown
                classCheckboxContainer.empty().html('<span class="text-muted">Select Class</span>');
                studentDropdown.empty().append('<option value="">Select Student</option>').val('').trigger('change.select2');

                if (batchId) {
                    // Fetch Class list
                    $.ajax({
                        type: 'POST',
                        url: "{{ route('get-class-bybatch') }}",
                        dataType: 'json',
                        data: {
                            batch_id: batchId
                        },
                        success: function(response) {
                            if ((response.status === true || response.status === "true") && response.data) {
                                let checkboxes = '';
                                response.data.forEach(function(item) {
                                    checkboxes += `
                                        <div class="form-check form-check-inline mb-0 me-3">
                                            <input class="form-check-input class-checkbox" type="checkbox" name="class_id[]" value="${item.id}" id="class_chk_${item.id}">
                                            <label class="form-check-label fw-semibold" for="class_chk_${item.id}">${item.name}</label>
                                        </div>
                                    `;
                                });
                                classCheckboxContainer.html(checkboxes || '<span class="text-muted">No classes found</span>');
                            }
                        }
                    });
                }
            });

            // Class checkbox change
            $(document).on('change', '.class-checkbox', function() {
                fetchStudents();
            });

            // Clear selection functionality
            $('#clearStudentsBtn').on('click', function() {
                studentDropdown.val(null).trigger('change.select2');
            });

            // Select All functionality
            studentDropdown.on('select2:select', function(e) {
                if (e.params.data.id === 'all') {
                    var allIds = [];
                    studentDropdown.find('option').each(function() {
                        if ($(this).val() !== 'all') allIds.push($(this).val());
                    });
                    studentDropdown.val(allIds).trigger('change.select2'); // Select all
                }
            });

            // Deselect “Select All” if any other option is deselected
            studentDropdown.on('select2:unselect', function(e) {
                if (e.params.data.id !== 'all') {
                    studentDropdown.find('option[value="all"]').prop('selected', false);
                }
            });

        });
    </script>

@endsection
