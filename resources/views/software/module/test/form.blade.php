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

    <div class="card shadow my-3">
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
                    <div class="col-md-4 col-sm-12 mb-3">
                        <div class="form-group">
                            <label class="form-label">Select Course <span class="text-danger">*</span></label>
                            <select id="course" name="course_id"
                                class="form-control search_by_course select2 @error('course_id') is-invalid @enderror"
                                data-append="search_by_course"
                                data-selectedCourseId="{{ isset($edit) && $edit?->course_id ? $edit?->course_id : old('course_id') }}"
                                autofocus >
                                <option value="">Select Course</option>
                            </select>

                            @error('course_id')
                                <span class="invalid-feedback">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>

                    {{-- Batch --}}
                    <div class="col-md-4 col-sm-12 mb-3">
                        <div class="form-group">
                            <label class="form-label">Select Batch <span class="text-danger">*</span></label>
                            <select name="batch_id" id="batch"
                                class="form-control @error('batch_id') is-invalid @enderror search_by_batch select2"
                                data-append="search_by_batch"
                                data-selectedBatchId="{{ isset($edit) && $edit?->batch_id ? $edit?->batch_id : old('batch_id') }}">

                                <option value="" disabled
                                    {{ old('batch_id', $edit->batch_id ?? '') ? '' : 'selected' }}>
                                    Select Batch
                                </option>
                            </select>
                            @error('batch_id')
                                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>

                    {{-- Semester --}}
                    <div class="col-md-4 col-sm-12 mb-3">
                        <div class="form-group">
                            <label class="form-label">Select Semester <span class="text-danger">*</span></label>
                            <select name="semester" id="semester"
                                class="form-control @error('semester') is-invalid @enderror search_by_semester select2"
                                data-append="search_by_semester"
                                data-selectedSemesterId="{{ isset($edit) && $edit?->semester ? $edit?->semester : old('semester') }}">
                                <option value="">Select Semester</option>
                            </select>
                            @error('semester')
                                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-4 col-sm-12 mb-3">
                        <div class="form-group">
                            <label class="form-label"> Subject Name <span class="text-danger">*</span> </label>
                            <input id="subject_name" type="text" class="form-control @error('subject_name') is-invalid @enderror"
                                name="subject_name" value="{{ isset($edit?->subject_name) ? $edit?->subject_name : old('subject_name') }}"
                                autocomplete="subject_name" autofocus placeholder="Enter Subject Name">

                            @error('subject_name')
                                <span class="invalid-feedback">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-4 col-sm-12 mb-3">
                        <div class="form-group">
                            <label class="form-label"> Test Type <span class="text-danger">*</span> </label>
                            <select id="test_type" name="test_type" class="form-control select2 @error('test_type') is-invalid @enderror">
                                <option value="">Select Test Type</option>
                                <option value="Weekly" {{ old('test_type', $edit->test_type ?? '') == 'Weekly' ? 'selected' : '' }}>Weekly</option>
                                <option value="Full" {{ old('test_type', $edit->test_type ?? '') == 'Full' ? 'selected' : '' }}>Full</option>
                            </select>

                            @error('test_type')
                                <span class="invalid-feedback">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-4 col-sm-12 mb-3">
                        <div class="form-group">
                            <label class="form-label"> Unit Name <span class="text-danger">*</span> </label>
                            <input id="unit_name" type="text" class="form-control @error('unit_name') is-invalid @enderror"
                                name="unit_name" value="{{ isset($edit?->unit_name) ? $edit?->unit_name : old('unit_name') }}"
                                autocomplete="unit_name" autofocus placeholder="Enter Unit Name">

                            @error('unit_name')
                                <span class="invalid-feedback">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-4 col-sm-12 mb-3">
                        <div class="form-group">
                            <label class="form-label"> Mark <span class="text-danger">*</span> </label>
                            <input id="mark" type="text" class="form-control @error('mark') is-invalid @enderror num_only" name="mark" value="{{ isset($edit?->mark) ? $edit?->mark : old('mark') }}" placeholder="Enter Mark">

                            @error('mark')
                                <span class="invalid-feedback">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-4 col-sm-12 mb-3">
                        <div class="form-group">
                            <label class="form-label"> Date <span class="text-danger">*</span> </label>
                            <input id="date" type="date" class="form-control @error('date') is-invalid @enderror"
                                name="date"
                                value="{{ isset($edit?->date) ? $edit->date : old('date', \Carbon\Carbon::now()->format('Y-m-d')) }}"
                                autocomplete="date" autofocus placeholder="Enter Date">

                            @error('date')
                                <span class="invalid-feedback">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>


                </div>

                <div class="row mt-4">
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
    @include('software.utils.getCourse')
    @include('software.utils.getBatchByCourseid')
    @include('software.utils.getSamasterByCourseid')
@endsection
