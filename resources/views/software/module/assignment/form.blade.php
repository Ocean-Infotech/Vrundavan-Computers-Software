@extends('software.layout.app')

@php
    $page_title = isset($modules['title']) ? $modules['title'] : 'Assignment';
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

    <div class="card shadow my-3">
        <div class="card-body my-4">
            <form action="{{ isset($edit) && $edit?->id ? route($route . '.update', [$edit?->id]) : route($route . '.store') }}"
                method="POST" enctype="multipart/form-data">
                @csrf
                @isset($edit)
                    @method('PUT')
                @endisset
                
                <div class="row">
                    <!-- Course -->
                    <div class="col-md-4 col-sm-12 mb-3">
                        <div class="form-group">
                            <label class="form-label">Course <span class="text-danger">*</span></label>
                            <select id="course" name="course_id"
                                class="form-control search_by_course select2 @error('course_id') is-invalid @enderror"
                                data-append="search_by_course"
                                data-selectedCourseId="{{ isset($edit) && $edit?->course_id ? $edit?->course_id : old('course_id') }}"
                                required autofocus>
                                <option value="">Select Course</option>
                            </select>
                            @error('course_id')
                                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>

                    <!-- Batch -->
                    <div class="col-md-4 col-sm-12 mb-3">
                        <div class="form-group">
                            <label class="form-label">Batch <span class="text-danger">*</span></label>
                            <select id="batch" name="batch_id"
                                class="form-control search_by_batch select2 @error('batch_id') is-invalid @enderror"
                                data-append="search_by_batch"
                                data-selectedBatchId="{{ isset($edit) && $edit?->batch_id ? $edit?->batch_id : old('batch_id') }}"
                                required>
                                <option value="">Select Batch</option>
                            </select>
                            @error('batch_id')
                                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>

                    <!-- Semester -->
                    <div class="col-md-4 col-sm-12 mb-3">
                        <div class="form-group">
                            <label class="form-label">Semester <span class="text-danger">*</span></label>
                            <select id="semester" name="semester"
                                class="form-control search_by_semester select2 @error('semester') is-invalid @enderror"
                                data-append="search_by_semester"
                                data-selectedSemesterId="{{ isset($edit) && $edit?->semester ? $edit?->semester : old('semester') }}"
                                required>
                                <option value="">Select Semester</option>
                            </select>
                            @error('semester')
                                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>

                    <!-- Subject -->
                    <div class="col-md-4 col-sm-12 mb-3">
                        <div class="form-group">
                            <label class="form-label">Subject <span class="text-danger">*</span></label>
                            <select id="subject" name="subject" class="form-control select2 @error('subject') is-invalid @enderror"
                                data-selectedSubject="{{ old('subject', $edit->subject ?? '') }}" required>
                                <option value="">Select Subject</option>
                            </select>
                            @error('subject')
                                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>

                    <!-- Unit -->
                    <div class="col-md-4 col-sm-12 mb-3">
                        <div class="form-group">
                            <label class="form-label">Unit</label>
                            <input type="text" name="unit" class="form-control @error('unit') is-invalid @enderror"
                                value="{{ old('unit', $edit->unit ?? '') }}" placeholder="Enter Unit (e.g. Unit 1)">
                            @error('unit')
                                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>

                    <!-- Date -->
                    <div class="col-md-4 col-sm-12 mb-3">
                        <div class="form-group">
                            <label class="form-label">Date <span class="text-danger">*</span></label>
                            <input type="date" name="date" class="form-control @error('date') is-invalid @enderror"
                                value="{{ old('date', isset($edit->date) ? \Carbon\Carbon::parse($edit->date)->format('Y-m-d') : \Carbon\Carbon::now()->format('Y-m-d')) }}" required>
                            @error('date')
                                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
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

@section('page_leavel_script')
    @include('software.utils.getCourse')
    @include('software.utils.getBatchByCourseid')
    @include('software.utils.getSamasterByCourseid')
    <script>
        $(document).ready(function() {
            // Function to load subjects from test table
            function loadSubjects() {
                let course_id = $('#course').val();
                let batch_id = $('#batch').val();
                let semester = $('#semester').val();
                let subject_select = $('#subject');
                let selected_subject = subject_select.attr('data-selectedSubject');

                if (course_id && batch_id && semester) {
                    $.ajax({
                        type: 'POST',
                        url: "{{ route('get-test-subjects') }}",
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        data: {
                            course_id: course_id,
                            batch_id: batch_id,
                            semester: semester
                        },
                        success: function(response) {
                            if (response.status) {
                                let options = "<option value=''>Select Subject</option>";
                                if (response.data && response.data.length > 0) {
                                    $.each(response.data, function(i, item) {
                                        if (selected_subject == item.name) {
                                            options += "<option value='" + item.name + "' selected>" + item.name + "</option>";
                                        } else {
                                            options += "<option value='" + item.name + "'>" + item.name + "</option>";
                                        }
                                    });
                                }
                                subject_select.empty().append(options).trigger('change');
                            }
                        }
                    });
                } else {
                    subject_select.empty().append("<option value=''>Select Subject</option>").trigger('change');
                }
            }

            // Listen to batch and semester dropdown changes
            $(document).on('change', '#batch, #semester', function() {
                loadSubjects();
            });

            // Periodically check if batch select has options and trigger change
            // because options are appended asynchronously via getBatchByCourseid.blade.php
            let checkBatchInterval = setInterval(function() {
                let batchVal = $('#batch').val();
                let semesterVal = $('#semester').val();
                if (batchVal && semesterVal) {
                    loadSubjects();
                    clearInterval(checkBatchInterval);
                }
            }, 300);
            
            // Safety timeout for interval
            setTimeout(function() {
                clearInterval(checkBatchInterval);
            }, 5000);
        });
    </script>
@endsection
