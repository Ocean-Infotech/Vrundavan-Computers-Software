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
    {{-- <link href="{{ asset('public/assets/admin/plugins/datatable/dataTables.bootstrap4.min.css') }}" rel="stylesheet" type="text/css"> --}}
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
            // 'show_add_btn' => $modules['addPermission'],
            'show_add_btn' => false,
            'show_filter_btn' => false,
            'show_back_btn' => true,
        ])
    </div>
@endsection

@section('content')
    <div class="row my-2">
        <div class="col-md-12">
            <div class="card shadow">
                <div class="card-header d-none">
                    <h2 class="mb-0">
                        {{ $page_title }}
                    </h2>
                </div>
                <div class="card-body my-4">
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
                            <div class="col-md-4 col-sm-12">
                                <div class="form-group">
                                    <label class="form-label"> Name <span class="text-danger">*</span> </label>
                                    <input id="batch_name" type="text"
                                        class="form-control @error('batch_name') is-invalid @enderror" name="batch_name"
                                        value="{{ isset($edit?->batch_name) ? $edit?->batch_name : old('batch_name') }}" autocomplete="batch_name"
                                        autofocus placeholder="Enter Batch Name">

                                    @error('batch_name')
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
                                @if (!isset($edit))
                                    <button type="submit" name="action" value="save_next" class="btn btn-primary mt-1">
                                        Save & Next
                                    </button>
                                @endif
                                <a href="{{ route($route . '.index') }}" class="btn btn-danger mt-1">Cancel</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection


@section('page_leavel_script')
    @include('software.utils.getCourse')

@endsection
