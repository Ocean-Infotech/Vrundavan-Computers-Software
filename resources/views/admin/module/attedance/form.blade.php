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
<link rel="stylesheet" href="{{ asset('admin/assets/cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css') }}">
<!--datatable responsive css-->
<link rel="stylesheet"
    href="{{ asset('admin/assets/cdn.datatables.net/responsive/2.2.9/css/responsive.bootstrap.min.css') }}">

<link rel="stylesheet" href="{{ asset('admin/assets/cdn.datatables.net/buttons/2.2.2/css/buttons.dataTables.min.css') }}">
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
                    <li class="breadcrumb-item active">{{ isset($edit) && $edit?->id ? 'Edit' : 'Add' }}
                        {{ $page_title }}
                    </li>
                </ol>
            </div>
        </div>
    </div>
</div>
<!-- end page title -->
@endsection

@section('content')

@include('admin.partials.flash_messages')

<div class="card mt-5">
    <div class="card-header d-none">
        <h2 class="mb-0">
            {{ $page_title }}
        </h2>
    </div>
    <div class="card-body pt-4">
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
                        <label class="form-label">Select Student<span class="text-danger">*</span></label>
                        <select id="admission" name="admission_id"
                            class="form-control search_by_admission select2 @error('admission_id') is-invalid @enderror select_filter"
                            data-append="search_by_admission"
                            data-selectedAdmissionId="{{ isset($edit) && $edit?->admission_id ? $edit?->admission_id : old('admission_id') }}"
                            autofocus>
                            <option value="">Select Admission</option>
                        </select>

                        @error('admission_id')
                        <span class="invalid-feedback">
                            <strong>{{ $message }}</strong>
                        </span>
                        @enderror
                    </div>
                </div>

                <div class="col-md-4 col-sm-12 mb-3">
                    <div class="form-group">
                        <label class="form-label">Select Course <span class="text-danger">*</span></label>
                        <select id="course" name="course_id"
                            class="form-control search_by_course select2 @error('course_id') is-invalid @enderror select_filter"
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

                {{-- Batch --}}
                <div class="col-md-4 col-sm-12 mb-3">
                    <div class="form-group">
                        <label class="form-label">Select Batch <span class="text-danger">*</span></label>
                        <select name="batch_id" id="batch"
                            class="form-control @error('batch_id') is-invalid @enderror search_by_batch select2 select_filter"
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



            </div>

            <!-- <div class="row mt-4">
                    <div class="col-md-12 text-center">
                        <button type="submit" class="btn btn-success mt-1">
                            {{ isset($edit) ? 'Update' : 'Submit' }}
                        </button>
                        <a href="{{ route($route . '.index') }}" class="btn btn-danger mt-1">Cancel</a>
                    </div>
                </div> -->
        </form>
    </div>
</div>


<div class="row my-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table id="simple-table" class="display table table-hover">
                        <!-- @if (isset($columns)) -->
                        <thead>
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                        </thead>
                        <!-- @endif -->
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('page_script_file')
<!--datatable js-->
<script src="{{ asset('admin/assets/cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('admin/assets/cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js') }}"></script>
<script src="{{ asset('admin/assets/cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js') }}"></script>
@endsection


@section('page_leavel_script')

<script type="text/javascript">
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });


    $(document).ready(function() {
        // Load data into simple table
        function loadTableData() {
            $.ajax({
                url: "{{ route($route . '.filter-student-data') }}", // Route must return JSON
                type: "GET",
                dataType: "json",
                data: {
                    filter_admission: $('select[name="admission_id"]').val(),
                    filter_course: $('select[name="course_id"]').val(),
                    filter_batch: $('select[name="batch_id"]').val()
                },
                beforeSend: function(request) {
                    request.setRequestHeader("X-CSRF-TOKEN", $('meta[name="csrf-token"]').attr('content'));
                },
                success: function(response) {
                    if (response.data && response.data.length > 0) {
                        $.each(response.data, function(index, item) {
                            console.error(item);
                        });
                    }
                    // let rows = '';

                    // if (response.data && response.data.length > 0) {
                    //     $.each(response.data, function(index, item) {
                    //         rows += '<tr>';
                    //         rows += '<td>' + item.id + '</td>';
                    //         rows += '<td>' + item.name + '</td>';
                    //         rows += '<td>' + item.status + '</td>';
                    //         rows += '</tr>';
                    //     });
                    // } else {
                    //     rows = '<tr><td colspan="3">No data available</td></tr>';
                    // }

                    // $('#simple-table tbody').html(rows);
                },
                error: function(xhr) {
                    console.error(xhr.responseText);
                    alert("Failed to load data.");
                }
            });
        }

        // Reload data when any filter is changed
        $('select[name="admission_id"], select[name="course_id"], select[name="batch_id"]').on('change', function() {
            var select_filter = $(this).val();

            if (select_filter != null && select_filter.trim() !== '') {
                    loadTableData(); // Re-fetch filtered data
            }
        });

        // loadTableData(); // Initial load
    });
</script>

@include('admin.utils.getAdmission')
@include('admin.utils.getCourse')
@include('admin.utils.getBatchByCourseid')
@endsection