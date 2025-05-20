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
                        <label class="form-label">Select Batch <span class="text-danger">*</span></label>
                        <select id="course" name="course_id"
                            class="form-control search_by_course select2 @error('course_id') is-invalid @enderror select_filter"
                            data-append="search_by_course"
                            data-selectedCourseId="{{ isset($edit) && $edit?->course_id ? $edit?->course_id : old('course_id') }}"
                            autofocus>
                            <option value="">Select Batch</option>
                        </select>

                        @error('course_id')
                        <span class="invalid-feedback">
                            <strong>{{ $message }}</strong>
                        </span>
                        @enderror
                    </div>
                </div>

                <div class="col-md-4 col-sm-12 mb-3" id="allstudentinbtn">
                    <label class="form-label mb-5"></label>
                    <button type="button" disabled id="allstudentin" data-url="{{ route($route . '.student-in') }}" class="btn btn-success btn-icon student_in"><i class="fa fa-sign-in"></i></button>
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


<div class="row my-4" id="education_details">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table id="simple-table" class="display table table-hover">

                        <thead>
                            <thead>
                                <tr>
                                    <input type="hidden" name="studentld" value="" id="studentld">
                                    <input type="hidden" name="courseld" value="" id="courseld">
                                    <th><input class="form-check-input" type="checkbox" id="checkallstudent" value=""></th>
                                    <th>Admission ID</th>
                                    <th>Student Name</th>
                                    <th>IN</th>
                                    <th>Date And Time</th>
                                    <!-- <th>OUT</th> -->
                                    <th>LEAVE</th>
                                </tr>
                            </thead>
                        <tbody></tbody>
                        </thead>

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

                    $("#checkallstudent").prop("checked", false);
                    let rows = '';
                    let url = "{{ route($route . '.student-in') }}";

                    if (response.data && response.data.length > 0) {
                        $.each(response.data, function(index, item) {
                            rows += '<tr>';
                            rows += '<td><input class="form-check-input studentcheck" name="student[]" type="checkbox" id="studentcheck" data-course_id="' + item.course_id + '" value="' + item.id + '"></td>';
                            rows += '<td>' + item.register_id + '</td>';
                            rows += '<td></td>';
                            rows += '<td>' + item.admission.first_name + ' ' + item.admission.father_name + ' ' + item.admission.last_name + '</td>';
                            rows += '<td><a href="javascript:void(0)" data-id="' + item.id + '" data-course_id="' + item.course_id + '" data-url="' + url + '" class="btn btn-success btn-icon student_in"><i class="fa fa-sign-in"></i></a></td>';
                            rows += '<td></td>';
                            rows += '</tr>';
                        });
                    } else {
                        rows = '<tr><td class="text-center" colspan="4">No data available</td></tr>';
                    }

                    $('#simple-table tbody').html(rows);
                },
                error: function(xhr) {
                    console.error(xhr.responseText);
                    alert("Failed to load data.");
                }
            });
        }

        $('#education_details').hide();
        $('#allstudentinbtn').hide();
        // Reload data when any filter is changed
        $('select[name="admission_id"], select[name="course_id"], select[name="batch_id"]').on('change', function() {
            var select_filter = $(this).val();

            if (select_filter != null && select_filter.trim() !== '') {
                loadTableData(); // Re-fetch filtered data 
                $('#education_details').show();
                $('#allstudentinbtn').show();
            }
        });

        // loadTableData(); // Initial load


        // When #checkallstudent is toggled
        $("#checkallstudent").change(function() {
            let isChecked = $(this).is(':checked');

            if ($(".studentcheck").length == $(".studentcheck:checked").length) {
                $(".studentcheck").prop("checked", isChecked); // Toggle all student checkboxes

                $('#allstudentin').attr("disabled", (isChecked) ? false : true);
            } else {
                $('#allstudentin').attr("disabled", false);
                $(".studentcheck").prop("checked", true); // Toggle all student checkboxes
                $(this).prop("checked", true);

            }
            updateStudentIds(); // Update hidden input
        });

        // When any .studentcheck checkbox is changed
        $(document).on('change', '.studentcheck', function() {
            // Check/uncheck "select all"
            // $("#checkallstudent").prop(
            //     $(".studentcheck").length === $(".studentcheck:checked").length
            // );

            if ($(".studentcheck").length == $(".studentcheck:checked").length) {
                $("#checkallstudent").prop("checked", true);
                const checkbox = document.getElementById('checkallstudent');
                checkbox.indeterminate = false;
                $('#allstudentin').attr("disabled", false);

            } else {
                if ($(".studentcheck:checked").length !== 0) {
                    $("#checkallstudent").prop("checked", true);
                    const checkbox = document.getElementById('checkallstudent');
                    checkbox.indeterminate = true;
                    $('#allstudentin').attr("disabled", false);
                } else {
                    $("#checkallstudent").prop("checked", false);
                    const checkbox = document.getElementById('checkallstudent');
                    checkbox.indeterminate = false;
                    $('#allstudentin').attr("disabled", true);
                }

            }

            updateStudentIds(); // Update hidden input
        });

        // Reusable function to update hidden input
        function updateStudentIds() {
            let selectedIds = $(".studentcheck:checked").map(function() {
                return $(this).val();
            }).get(); // .get() converts it to a plain array

            let courseIds = $(".studentcheck:checked").map(function() {
                return $(this).data('course_id');
            }).get(); // .get() converts it to a plain array

            $("#studentld").val(selectedIds.join(","));
            $('#allstudentin').attr('data-id', selectedIds.join(","));
            $('#allstudentin').attr('data-course_id', courseIds.join(","));
            $("#courseld").val(courseIds.join(","));
        }



        //student-in
        $(document).on('click', '.student_in', function() {
            var _token = '{{ csrf_token() }}';
            var _actionUrl = $(this).attr("data-url");
            var _studentld = $(this).data('id');
            var _courseId = $(this).data('course_id');

            const urlParams = new URLSearchParams(_actionUrl);

            if (!_studentld && urlParams.get('studentld')) {
                _studentld = urlParams.get('studentld');
            }

            if (!_courseId && urlParams.get('courseld')) {
                _courseId = urlParams.get('courseld');
            }

            if (_token && _actionUrl && _studentld && _courseId) {
                console.log("generate-inquiry-no L-31");
                $.ajax({
                    type: "POST",
                    url: _actionUrl,
                    data: {
                        _token: _token,
                        _method: 'POST',
                        studentid: _studentld,
                        courseid: _courseId,
                    },
                    success: function(data) {
                        console.log("data 27", data);

                        if (data?.status == true) {
                            loadTableData(); // reload DataTable
                            toastr.success(data?.message);
                            $("#checkallstudent").prop("checked", false);
                        } else {
                            toastr.error(data?.message);
                        }

                    },
                    error: function() {
                        toastr.error('Something Went wrong Update Status failed.');
                    }
                });
            } else {
                console.log("Update Script", _token, _actionUrl, urlParams, _studentld, _courseId);

            }
        });
    });
</script>


@include('admin.utils.getAdmission')
@include('admin.utils.getCourse')
<!-- @include('admin.utils.getBatchByCourseid') -->
@endsection