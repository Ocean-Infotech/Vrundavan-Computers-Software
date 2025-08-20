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
                    <div class="col-md-4 col-sm-12">
                        <div class="form-group">
                            <label class="form-label">Class <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('class') is-invalid @enderror" name="class"
                                value="{{ isset($edit?->class) ? $edit->class : old('class') }}" placeholder="Enter class">
                            @error('class')
                                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-4 col-sm-12">
                        <div class="form-group">
                            <label class="form-label">Semester <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('semester') is-invalid @enderror"
                                name="semester" value="{{ isset($edit?->semester) ? $edit->semester : old('semester') }}"
                                placeholder="Enter semester">
                            @error('semester')
                                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-4 col-sm-12">
                        <div class="form-group">
                            <label class="form-label">Batch <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('batch') is-invalid @enderror" name="batch"
                                value="{{ isset($edit?->batch) ? $edit->batch : old('batch') }}" placeholder="Enter batch">
                            @error('batch')
                                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-4 col-sm-12">
                        <div class="form-group">
                            <label class="form-label">Attachment</label>
                            <input type="file" class="form-control @error('attachment') is-invalid @enderror"
                                name="attachment" accept=".pdf,.doc,.docx,.jpg,.png,.jpeg">
                            @if (isset($edit?->attachment) && $edit->attachment)
                                <p class="mt-2">
                                    <a href="{{ asset($edit->attachment) }}" target="_blank">
                                        View Attachment
                                    </a>
                                </p>
                            @endif
                            @error('attachment')
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

            // Course change AJAX
            $('select[name="course_id"]').on('change', function() {
                let courseId = $(this).val();
                studentDropdown.empty().append('<option value="">Loading...</option>');

                if (courseId) {
                    $.ajax({
                        url: "{{ url('software/get-students-by-course') }}/" + courseId,
                        type: "GET",
                        success: function(data) {
                            studentDropdown.empty().append(
                                '<option value="all">Select All</option>');
                            $.each(data, function(key, student) {
                                studentDropdown.append('<option value="' + student.id +
                                    '">' + student.name + '</option>');
                            });
                        },
                        error: function(xhr) {
                            studentDropdown.empty().append(
                                '<option value="">Error loading students</option>');
                        }
                    });
                } else {
                    studentDropdown.empty().append('<option value="">Select Student</option>');
                }
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
