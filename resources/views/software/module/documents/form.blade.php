@extends('software.layout.app')

@php
    $page_title = isset($modules['title']) ? $modules['title'] : null;
    $folder_path = isset($modules['folder_path']) ? $modules['folder_path'] : null;
    $route = isset($modules['route']) ? $modules['route'] : null;
    $permission_prefix = isset($modules['permission_prefix']) ? $modules['permission_prefix'] : null;
    $i = 0;
@endphp
@section('title', $page_title)

@section('page_style_file')
    {{-- <link href="{{ asset('public/assets/admin/plugins/datatable/dataTables.bootstrap4.min.css') }}" rel="stylesheet" type="text/css"> --}}
    <style>
    </style>
@endsection


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

    <!-- @include('software.partials.flash_messages') -->

    <div class="card shadow">
        <div class="card-header d-none">
            <h2 class="mb-0">
                {{ $page_title }}
            </h2>
        </div>
        <div class="card-body">
            <form
                action="{{ isset($edit) && $edit?->id ? route($route . '.update', [$edit?->id]) : route($route . '.store') }}"
                method="POST" enctype="multipart/form-data">
                @csrf
                @isset($edit)
                    @method('PUT')
                    <input type="hidden" name="id" value="{{ $edit?->id ?? '' }}" />
                @endisset
                <div class="row">
                    <div class="col-md-4 col-sm-12 mb-2">
                        <div class="form-group">
                            <label class="form-label">Select Document Type</label>
                            <select class="form-control select2 w-100 @error('document_type_id') is-invalid @enderror"
                                name="document_type_id" required>
                                <option disabled selected value="">Select document type</option>
                                @if (isset($document_types))
                                    @foreach ($document_types as $record)
                                        <option value="{{ $record?->id }}"
                                            @if (isset($edit)) @if ($edit->document_type_id == $record?->id) {{ 'selected' }} @endif
                                        @else @if (old('document_type_id') == $record?->id) {{ 'selected' }} @endif
                                            @endif> {{ ucfirst($record?->name) }}</option>
                                    @endforeach
                                @endif
                            </select>

                            @error('document_type_id')
                                <span class="invalid-feedback">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-4 col-sm-12 mb-2">
                        <div class="form-group">
                            <label class="form-label"> Document Name <span class="text-danger">*</span> </label>
                            <input id="name" type="text" class="form-control @error('name') is-invalid @enderror"
                                name="name" value="{{ isset($edit?->name) ? $edit?->name : old('name') }}"
                                placeholder="Enter document name">

                            @error('name')
                                <span class="invalid-feedback">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-4 col-sm-12  mb-2">
                        <div class="form-group">
                            <label class="form-label" for="document_file"> Select Document <span
                                    class="text-danger">*</span> </label>
                            <div class="input-group">
                                <input id="document_file" type="file"
                                    value="{{ isset($edit->document_file) ? $edit->document_file : old('document_file') }}"
                                    class="form-control @error('document_file') is-invalid  @enderror" name="document_file"
                                    autocomplete="document_file">
                                @isset($edit->document_file_url)
                                    <a class="input-group-text bg-info text-white" rel="group" href="{{ $edit->document_file_url }}"
                                        target="_blank">View</a>
                                @endisset
                            </div>
                            @error('document_file')
                                <span class="invalid-feedback">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                    </div>

                    <div class="col-md-4 col-sm-12 mb-2">
                        <div class="form-group">
                            <label class="form-label">Status</label>
                            <select class="form-control select2 w-100 @error('status') is-invalid @enderror" name="status"
                                required>
                                <option disabled selected>Select Status</option>
                                @foreach (['active', 'inactive'] as $status)
                                    <option value="{{ $status }}"
                                        @if (isset($edit)) @if ($edit->status == $status) {{ 'selected' }} @endif
                                    @else @if (old('status', 'active') == $status) {{ 'selected' }} @endif @endif> {{ ucfirst($status) }}</option>
                                @endforeach
                            </select>

                            @error('status')
                                <span class="invalid-feedback">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>

                    <div class="col-12 mb-2 showRoleWisePermission"></div>
                    <div class="col-md-12 text-center">
                        <button type="submit" class="btn btn-success mt-1 mb-1">
                            {{ isset($edit) ? 'Update' : 'Submit' }}
                        </button>
                        <a href="{{ route($route . '.index') }}" class="btn btn-danger mt-1 mb-1">Cancel</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

@endsection

@section('page_script_file') @endsection

@section('page_leavel_script')
    <script>
        document.querySelector('input[name="document_file"]').addEventListener('change', function() {
            const allowedExtensions = ['jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx'];
            const file = this.files[0];

            if (file) {
                const fileSize = file.size / 1024 / 1024; // Convert bytes to MB
                const fileExtension = file.name.split('.').pop().toLowerCase();

                if (!allowedExtensions.includes(fileExtension)) {
                    alert('Invalid file type! Please upload JPG, PNG, PDF, or DOC files.');
                    this.value = ''; // Clear the input
                } else if (fileSize > 5) {
                    alert('File size exceeds 5MB limit!');
                    this.value = ''; // Clear the input
                }
            }
        });
    </script>
@endsection
