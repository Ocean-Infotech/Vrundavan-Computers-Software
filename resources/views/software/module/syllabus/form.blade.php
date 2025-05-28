@extends('software.layout.app')

@php
    $page_title = isset($modules['title']) ? $modules['title'] : null;
    $folder_path = isset($modules['folder_path']) ? $modules['folder_path'] : null;
    $route = isset($modules['route']) ? $modules['route'] : null;
    $permisstion_prefix = isset($modules['permisstion_prefix']) ? $modules['permisstion_prefix'] : null;
@endphp

@section('title', $page_title)

@section('breadcrumb')
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('software.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route($route . '.index') }}">View all {{ $page_title }}</a>
                        </li>
                        <li class="breadcrumb-item active">{{ isset($edit) && $edit?->id ? 'Edit' : 'Add' }}
                            {{ $page_title }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('content')
    <!-- @include('software.partials.flash_messages') -->

    <div class="card shadow my-4">
        <div class="card-body my-4">
            <form
                action="{{ isset($edit) && $edit?->id ? route($route . '.update', [$edit?->id]) : route($route . '.store') }}"
                method="POST" enctype="multipart/form-data">
                @csrf
                @isset($edit)
                    @method('PUT')
                @endisset

                <div class="row">
                    <div class="col-md-6 col-sm-12">
                        <div class="form-group">
                            <label class="form-label">Title <span class="text-danger">*</span></label>
                            <input type="text" name="title" class="form-control @error('title') is-invalid @enderror"
                                value="{{ isset($edit?->title) ? $edit?->title : old('title') }}" placeholder="Enter Title"
                                autocomplete="off">

                            @error('title')
                                <span class="invalid-feedback">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-12 col-sm-12 mt-3">
                        <div class="form-group">
                            <label class="form-label">Subject <span class="text-danger">*</span></label>
                            <textarea id="subject_editor" name="subject" class="form-control @error('subject') is-invalid @enderror"
                                placeholder="Enter Subject">{{ isset($edit?->subject) ? $edit?->subject : old('subject') }}</textarea>

                            @error('subject')
                                <span class="invalid-feedback">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>

                </div>

                <div class="row mt-5">
                    <div class="col-md-12 text-center">
                        <button type="submit" class="btn btn-success mt-1">
                            {{ isset($edit) ? 'Update' : 'Submit' }}
                        </button>
                        <a href="{{ route($route . '.index') }}" class="btn btn-danger mt-1">Cancel</a>
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
        .create(document.querySelector('#subject_editor'), {
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


@endsection

