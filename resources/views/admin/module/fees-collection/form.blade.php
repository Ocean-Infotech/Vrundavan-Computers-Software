@extends('admin.layout.app')

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
    <!-- start page title -->
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                {{-- <h3 class="page-title">{{ $page_title }}</h3> --}}
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route($route . '.index') }}">View all {{ $page_title }}</a>
                        </li>
                        <li class="breadcrumb-item active">
                            {{ isset($edit) && $edit?->id
                                ? 'Edit'
                                : 'Add
                                                                                ' }}
                            {{ $page_title }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
    <!-- end page title -->
@endsection

@section('content')

    @include('admin.partials.flash_messages')

    <div class="card shadow my-5">
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
                      <div class="col-md-4 mb-2">
                        <div class="form-group">
                            <label for="search">Search <span class="text-danger">*</span></label>
                            <select name="search" id="search"
                                class="form-control select2 @error('search') is-invalid @enderror">
                                <option value="">-- select  --</option>

                            </select>
                            @error('search')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                 </div>
                <div class="row">

                    {{-- student id --}}
                    <div class="col-md-4 mb-2">
                        <div class="form-group">
                            <label class="form-label">Student Id <span class="text-danger">*</span> </label>
                            <input id="student_id" type="text"
                                class="form-control @error('student_id') is-invalid @enderror num_only" name="student_id"
                                value="{{ isset($edit?->student_id) ? $edit?->student_id : old('student_id') }}"
                                placeholder="Enter student id">

                            @error('student_id')
                                <span class="invalid-feedback">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>

                    {{-- Addmission id --}}
                    <div class="col-md-4 mb-2">
                        <div class="form-group">
                            <label class="form-label">Addmission Id <span class="text-danger">*</span> </label>
                            <input id="addmission_id" type="text"
                                class="form-control @error('addmission_id') is-invalid @enderror num_only"
                                name="addmission_id"
                                value="{{ isset($edit?->addmission_id) ? $edit?->addmission_id : old('addmission_id') }}">

                            @error('addmission_id')
                                <span class="invalid-feedback">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>
                    {{-- course name --}}
                    <div class="col-md-4 mb-2">
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

                    {{-- Student name --}}
                    <div class="col-md-4 mb-2">
                        <div class="form-group">
                            <label class="form-label"> Course Name <span class="text-danger">*</span> </label>
                            <input id="student_name" type="text"
                                class="form-control @error('student_name') is-invalid @enderror" name="student_name"
                                value="{{ isset($edit?->student_name) ? $edit?->student_name : old('student_name') }}"
                                autocomplete="student_name" autofocus placeholder="Enter Course Name">

                            @error('student_name')
                                <span class="invalid-feedback">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>

                    {{-- year / semester --}}
                    <div class="col-md-4 mb-2">
                        <div class="form-group">
                            <label for="year_semester">Year / Semester <span class="text-danger">*</span></label>
                            <select name="year_semester" id="year_semester"
                                class="form-control select2 @error('year_semester') is-invalid @enderror">
                                <option value="">-- select Semester --</option>
                                <option value="">1</option>
                                <option value="">2</option>
                                <option value="">3</option>
                                <option value="">4</option>
                                <option value="">5</option>
                                <option value="">6</option>
                            </select>
                            @error('year_semester')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>



                    {{-- Date --}}
                    <div class="col-md-4 mb-2">
                        <div class="form-group">
                            <label for="date">Date <span class="text-danger">*</span></label>
                            <input type="date" name="date" id="date"
                                class="form-control datepicker @error('date') is-invalid @enderror"
                                value="{{ old('date', $edit->date ?? '') }}">
                            @error('date')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>


                    {{-- Fees (auto-filled) --}}
                    <div class="col-md-4 mb-2">
                        <div class="form-group">
                            <label for="fee">Course Fees <span class="text-danger">*</span></label>
                            <input type="number" name="fee" id="fee"
                                class="form-control @error('fee') is-invalid @enderror"
                                value="{{ old('fee', $edit->fee ?? '') }}">
                            @error('fee')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    {{-- Mode --}}
                    <div class="col-md-4 mb-2">
                        <div class="form-group">
                            <label for="mode">Mode <span class="text-danger">*</span></label>
                            <select name="mode" id="mode"
                                class="form-control select2 @error('mode') is-invalid @enderror">
                                <option value="">-- select Mode --</option>
                                <option value="">Cash</option>
                                <option value="">Online</option>
                                <option value="">Cheque</option>
                                <option value="">Discount</option>
                                <option value="">Return</option>
                            </select>
                            @error('mode')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    {{-- password --}}
                    <div class="col-md-4 mb-2">
                        <div class="form-group">
                            <label for="password">Password<span class="text-danger">*</span></label>
                            <input type="password" name="password" id="password"
                                class="form-control @error('password') is-invalid @enderror"
                                value="{{ old('password', $edit->password ?? '') }}">
                            @error('password')
                                <span class="invalid-feedback">{{ $message }}</span>
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
@endsection
