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
                            <label class="form-label">Select Course <span class="text-danger">*</span></label>
                            <select id="course" name="course_id"
                                class="form-control search_by_course select2 @error('course_id') is-invalid @enderror"
                                data-append="search_by_course"
                                data-selectedCourseId="{{ isset($edit) && $edit?->course_id ? $edit?->course_id : old('course_id') }}"
                                autofocus>
                                <option value="">Select Course</option>
                            </select>

                            @error('course_id')
                                <span class="invalid-feedback">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-4 col-sm-12 mb-3">
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

                    <div class="col-md-4 col-sm-12 mb-3">
                        <div class="form-group">
                            <label class="form-label">Select Class <span class="text-danger">*</span></label>
                            <select id="class " name="class_id"
                                class="form-control search_by_class select2 @error('class_id') is-invalid @enderror select_filter"
                                data-append="search_by_class"
                                data-selectedClassId="{{ isset($edit) && $edit?->class_id ? $edit?->class_id : old('class_id') }}"
                                autofocus>
                                <option value="">Select Class</option>
                            </select>

                            @error('class_id')
                                <span class="invalid-feedback">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>





                    <div class="col-md-4 col-sm-12 mb-3">
                        <div class="form-group">
                            <label class="form-label">Attachment</label>
                            <input type="file" class="form-control @error('attachment') is-invalid @enderror"
                                name="attachment" accept=".pdf,.doc,.docx,.txt,.text,.jpg,.png,.jpeg,.webp">
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

    @include('software.utils.getCourse')
    @include('software.utils.getBatchByCourseid')
    @include('software.utils.getClass')
@endsection
