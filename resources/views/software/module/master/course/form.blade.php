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

    <!-- @include('software.partials.flash_messages') -->

    <div class="card shadow my-4">
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
                    <div class="col-md-3 col-sm-12">
                        <div class="form-group">
                            <label class="form-label"> Course Name <span class="text-danger">*</span> </label>
                            <input id="course_name" type="text"
                                class="form-control @error('course_name') is-invalid @enderror" name="course_name"
                                value="{{ isset($edit?->course_name) ? $edit?->course_name : old('course_name') }}"
                                autocomplete="course_name" autofocus placeholder="Enter Course Name">

                            @error('course_name')
                                <span class="invalid-feedback">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-12">
                        <div class="form-group">
                            <label class="form-label"> Course Fees <span class="text-danger">*</span> </label>
                            <div class="input-group">
                                <input id="course_fees" type="text"
                                    class="form-control @error('course_fees') is-invalid @enderror w-auto num_only"
                                    name="course_fees"
                                    value="{{ isset($edit?->course_fees) ? $edit?->course_fees : old('course_fees') }}"
                                    placeholder="Enter Course Fees">
                                <input type="text" class="form-control @error('course_fees') is-invalid @enderror"
                                    name="course_fees_rupees" disabled value="Rs.">
                            </div>

                            @error('course_fees')
                                <span class="invalid-feedback">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-3 col-sm-12">
                        <div class="form-group">
                            <label class="form-label">Course Year <span class="text-danger">*</span> </label>
                            <div class="input-group">
                                <input id="course_year" type="text"
                                    class="form-control @error('course_year') is-invalid @enderror w-auto num_only"
                                    name="course_year" maxlength="4"
                                    value="{{ isset($edit?->course_year) ? $edit?->course_year : old('course_year') }}"
                                    placeholder="Enter Course Year">
                                @if (isset($course_year_type))
                                    <!-- <button class="btn btn-outline-primary dropdown-toggle waves-effect" type="button"
                                                data-bs-toggle="dropdown" aria-expanded="false">Type</button>
                                            <ul class="dropdown-menu dropdown-menu-end" style="" name="course_year_type">
                                                @foreach ($course_year_type as $key => $item)
    <li value="{{ $key ?? '' }}" class="dropdown-item">{{ $item ?? '' }}</li>
    @endforeach
                                            </ul> -->
                                @endif
                            </div>

                            @error('course_year')
                                <span class="invalid-feedback">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-3 col-sm-12">
                        <div class="form-group">
                            <label class="form-label">Total Semester <span class="text-danger">*</span> </label>
                            <div class="input-group">
                                <input id="semester" type="text"
                                    class="form-control @error('semester') is-invalid @enderror num_only" name="semester"
                                    maxlength="4"
                                    value="{{ isset($edit?->semester) ? $edit?->semester : old('semester') }}"
                                    placeholder="Enter Course Semester" readonly>
                            </div>

                            @error('semester')
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

@endsection


@section('page_leavel_script')
    <script>
        $(document).ready(function() {
            $('#course_year').on('input', function() {
                let year = parseInt($(this).val());
                if (!isNaN(year) && year > 0) {
                    $('#semester').val(year * 2);
                } else {
                    $('#semester').val('');
                }
            });
        });
    </script>

@endsection
