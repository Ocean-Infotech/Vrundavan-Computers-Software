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
                    <div class="col-md-3 col-sm-12 mb-3">
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

                    <div class="col-md-3 col-sm-12 mb-3">
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

                    <div class="col-md-3 col-sm-12 mb-3">
                        <div class="form-group">
                            <label class="form-label">Class</label>
                            <select class="form-control select2" name="class_id" id="classSelect">
                                <option value="">Select Class</option>
                                @foreach ($classes as $id => $class_name)
                                    <option value="{{ $id }}"
                                        {{ (isset($edit) && $edit->class_id == $id) || old('class_id') == $id ? 'selected' : '' }}>
                                        {{ $class_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="col-md-3 col-sm-12 mb-3">
                        <div class="form-group">
                            <label class="form-label">Student <span class="text-danger">*</span></label>
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
                            <textarea id="body_editor" name="body" class="form-control @error('body') is-invalid @enderror"
                                placeholder="Enter message">{{ isset($edit?->body) ? $edit->body : old('body') }}</textarea>

                            @error('body')
                                <span class="invalid-feedback">
                                    <strong>{{ $message }}</strong>
                                </span>
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
    <script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script>
    <script>
        ClassicEditor
            .create(document.querySelector('#body_editor'), {
                toolbar: [
                    'heading', '|',
                    'bold', 'italic', 'underline', 'link', '|',
                    'bulletedList', 'numberedList', '|',
                    'blockQuote', 'undo', 'redo'
                ]
            })
    </script>
    <style>
        .ck-editor__editable {
            min-height: 300px !important;
            max-height: 300px !important;
            overflow-y: auto;
        }
    </style>
    <script>
        $(document).ready(function() {

            var studentDropdown = $('#studentDropdown');
            var courseSelect = $('#courseSelect');
            var batchSelect = $('#batchSelect');
            var classSelect = $('#classSelect');

            function fetchStudents() {
                let courseId = courseSelect.val();
                let batchId = batchSelect.val();
                let classId = classSelect.val();

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
                        class_id: classId
                    },
                    success: function(data) {
                        studentDropdown.empty().append('<option value="all">Select All</option>');
                        $.each(data, function(key, student) {
                            studentDropdown.append('<option value="' + student.id + '">' + student.name + '</option>');
                        });
                        studentDropdown.trigger('change.select2');
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
                classSelect.empty().append('<option value="">Select Class</option>').val('').trigger('change.select2');

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
                
                fetchStudents();
            });

            // Batch change AJAX
            batchSelect.on('change', function() {
                let batchId = $(this).val();

                // Reset class dropdown
                classSelect.empty().append('<option value="">Select Class</option>').val('').trigger('change.select2');

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

            // Class change
            classSelect.on('change', function() {
                fetchStudents();
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
