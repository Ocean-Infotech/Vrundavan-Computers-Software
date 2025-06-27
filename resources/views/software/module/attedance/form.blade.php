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
<link rel="stylesheet" href="{{ asset('admin/assets/cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css') }}">
<!--datatable responsive css-->
<link rel="stylesheet"
    href="{{ asset('admin/assets/cdn.datatables.net/responsive/2.2.9/css/responsive.bootstrap.min.css') }}">

<link rel="stylesheet" href="{{ asset('admin/assets/cdn.datatables.net/buttons/2.2.2/css/buttons.dataTables.min.css') }}">
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
                <div class="col-md-4 mb-3">
                    <label class="form-label">Admission ID</label>
                    <input id="addmission_id" type="text"
                        class="form-control @error('addmission_id') is-invalid @enderror"
                        name="addmission_id"
                        value="{{ isset($edit?->addmission_id) ? $edit?->addmission_id : old('addmission_id') }}"
                        placeholder="Enter Roll No.">
                    @error('addmission_id')
                    <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                    @enderror
                </div>

                <div class="col-md-8 mb-3 text-start">
                    <label class="form-label mb-5"></label>
                    <button type="button" class="btn btn-success" data-url="{{ route($route . '.student-in') }}" id="attendance_in">IN</button>
                    <button type="button" class="btn btn-danger" data-url="{{ route($route . '.student-out') }}" id="attendance_out">OUT</button>
                </div>

                <div class="col-sm-12 col-md-4 col-lg-4 col-xl-3 mb-3">
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
                <div class="col-sm-12 col-md-4 col-lg-4 col-xl-3 mb-3">
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

                <div class="col-md-4 col-sm-12 mb-3" id="allstudentinbtn">
                    <label class="form-label mb-5"></label>
                    <button type="button" disabled id="allstudentin" data-url="{{ route($route . '.student-in') }}" class="btn btn-success btn-icon student_in"><i class="fa fa-sign-in"></i></button>
                    <button type="button" disabled id="allstudentout" data-url="{{ route($route . '.student-out') }}" class="btn btn-danger btn-icon student_out"><i class="fa fa-sign-out"></i></button>
                </div>
            </div>
        </form>
    </div>
</div>


<div class="row my-3" id="education_details">
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
                                    <input type="hidden" name="attendanceid" value="" id="attendanceid">
                                    <th><input class="form-check-input" type="checkbox" id="checkallstudent" value=""></th>
                                    <th>Admission ID</th>
                                    <th>Student Name</th>
                                    <th>IN</th>
                                    <!-- <th>Date And Time</th> -->
                                    <th>OUT</th>
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
                url: "{{ route($route . '.filter-student-data') }}",
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

                    // console.log(response);

                    $("#checkallstudent").prop("checked", false);

                    let rows = '';
                    let url = "{{ route($route . '.student-in') }}";

                    let out_url = "{{ route($route . '.student-out') }}";

                    let leave_url = "{{ route($route . '.student-leave') }}";

                    if (response.data && response.data.length > 0) {

                        $.each(response.data, function(index, item) {
                            let in_time = '';
                            let date = '';
                            let out_time = '';
                            let attendance_id = "";
                            let leave = '';

                            // Find attendance by admission_id
                            if (response.attedances && response.attedances.length > 0) {
                                const attendanceRecord = response.attedances.find(att => att.admission_id == item.register_id);
                                if (attendanceRecord) {
                                    in_time = attendanceRecord.in_time || '';
                                    date = attendanceRecord.date || '';
                                    out_time = attendanceRecord.out_time || '';
                                    leave = attendanceRecord.leave || '';
                                    attendance_id = attendanceRecord.id || '';
                                }
                            }

                            if (!item.admission) {
                                console.warn("Admission data missing for register_id:", item.register_id);
                            }

                            rows += '<tr>';
                            rows += '<td><input class="form-check-input studentcheck" name="student[]" type="checkbox" id="studentcheck" data-attendance_id="' + attendance_id + '"  data-course_id="' + item.course_id + '" value="' + item.id + '"></td>';
                            rows += '<td>' + item.register_id + '</td>';
                            rows += '<td>' + item.admission.first_name + ' ' + item.admission.father_name + ' ' + item.admission.last_name + '</td>';
                            if (in_time == "") {
                                rows += '<td><button type="button" data-id="' + item.id + '" data-course_id="' + item.course_id + '" data-url="' + url + '" class="btn btn-success btn-icon student_in"  ' + (leave != "" ? ' disabled' : '') + '><i class="fa fa-sign-in"></i></button></td>';
                            } else {

                                let newin_time = date + ' ' + in_time;
                                let in_dateTime = new Date(newin_time.replace(' ', 'T'));

                                let day = String(in_dateTime.getDate()).padStart(2, '0');
                                let month = String(in_dateTime.getMonth() + 1).padStart(2, '0');
                                let year = in_dateTime.getFullYear();
                                let hours = String(in_dateTime.getHours()).padStart(2, '0');
                                let minutes = String(in_dateTime.getMinutes()).padStart(2, '0');
                                let ampm = in_dateTime.getHours() >= 12 ? 'pm' : 'am';

                                let formatted = `${day}-${month}-${year} ${hours}:${minutes} ${ampm}`;

                                rows += '<td>' + formatted + '</td>';
                            }
                            if (out_time == "") {
                                rows += '<td><button type="button" data-id="' + attendance_id + '"  data-url="' + out_url + '" class="btn btn-danger btn-icon student_out"' + (in_time == "" ? ' disabled' : '') + '><i class="fa fa-sign-out"></i></button></td>';
                            } else {


                                let newout_time = date + ' ' + out_time;
                                let out_dateTime = new Date(newout_time.replace(' ', 'T'));

                                let day = String(out_dateTime.getDate()).padStart(2, '0');
                                let month = String(out_dateTime.getMonth() + 1).padStart(2, '0');
                                let year = out_dateTime.getFullYear();
                                let hours = String(out_dateTime.getHours()).padStart(2, '0');
                                let minutes = String(out_dateTime.getMinutes()).padStart(2, '0');
                                let ampm = out_dateTime.getHours() >= 12 ? 'pm' : 'am';

                                let formatted = `${day}-${month}-${year} ${hours}:${minutes} ${ampm}`;

                                rows += '<td>' + formatted + '</td>';
                            }
                            if (leave == "") {
                                rows += '<td><button type="button" data-id="' + item.id + '" data-course_id="' + item.course_id + '" data-url="' + leave_url + '"  class="btn btn-primary student_leave" ' + (in_time != "" ? ' disabled' : '') + '>Leave </button></td>';
                            } else {

                                let date = new Date(leave.replace(' ', 'T'));

                                let day = String(date.getDate()).padStart(2, '0');
                                let month = String(date.getMonth() + 1).padStart(2, '0');
                                let year = date.getFullYear();
                                let hours = String(date.getHours()).padStart(2, '0');
                                let minutes = String(date.getMinutes()).padStart(2, '0');
                                let ampm = date.getHours() >= 12 ? 'pm' : 'am';

                                let formatted = `${day}-${month}-${year} ${hours}:${minutes} ${ampm}`;

                                rows += '<td>' + formatted + '</td>';
                            }
                            rows += '</tr>';
                        });

                    } else {
                        rows = '<tr><td class="text-center" colspan="7">No data available</td></tr>';
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

        $('select[name="batch_id"]').on('change', function() {
            var select_filter = $(this).val();

            if (select_filter && select_filter.trim() !== '') {
                loadTableData();
                $('#education_details').show();
                $('#allstudentinbtn').show();
            } else {
                $('#simple-table tbody').html('<tr><td class="text-center" colspan="7">Please select a batch to view students.</td></tr>');
                $('#education_details').hide();
                $('#allstudentinbtn').hide();
            }
        });


        // When #checkallstudent is toggled
        $("#checkallstudent").change(function() {
            let isChecked = $(this).is(':checked');

            if ($(".studentcheck").length == $(".studentcheck:checked").length) {
                $(".studentcheck").prop("checked", isChecked); // Toggle all student checkboxes

                $('#allstudentin').attr("disabled", (isChecked) ? false : true);
                $('#allstudentout').attr("disabled", (isChecked) ? false : true);
            } else {
                $('#allstudentin').attr("disabled", false);
                $('#allstudentout').attr("disabled", false);
                $(".studentcheck").prop("checked", true); // Toggle all student checkboxes
                $(this).prop("checked", true);

            }
            updateStudentIds(); // Update hidden input
        });

        // When any .studentcheck checkbox is changed
        $(document).on('change', '.studentcheck', function() {
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
                    $('#allstudentout').attr("disabled", false);
                } else {
                    $("#checkallstudent").prop("checked", false);
                    const checkbox = document.getElementById('checkallstudent');
                    checkbox.indeterminate = false;
                    $('#allstudentin').attr("disabled", true);
                    $('#allstudentout').attr("disabled", true);
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

            let attendanceIds = $(".studentcheck:checked").map(function() {
                return $(this).data('attendance_id');
            }).get();

            $("#studentld").val(selectedIds.join(","));
            $('#allstudentin').attr('data-id', selectedIds.join(","));
            $('#allstudentin').attr('data-course_id', courseIds.join(","));
            $('#allstudentout').attr('data-id', selectedIds.join(","));
            $('#allstudentout').attr('data-id', attendanceIds.join(","));
            $("#courseld").val(courseIds.join(","));
            $("#attendanceid").val(courseIds.join(","));

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
                // console.log("generate-inquiry-no L-31");
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
                        // console.log("data 27", data);

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


        $(document).on('click', '.student_out', function() {
            var _token = '{{ csrf_token() }}';
            var _actionUrl = $(this).attr("data-url");
            var _studentld = $(this).data('id');
            var _attendanceid = $(this).data('id');

            const urlParams = new URLSearchParams(_actionUrl);

            if (!_studentld && urlParams.get('studentld')) {
                _studentld = urlParams.get('studentld');
            }

            if (!_attendanceid && urlParams.get('attendanceid')) {
                _attendanceid = urlParams.get('attendanceid');
            }

            if (_token && _actionUrl && _attendanceid && _studentld) {
                $.ajax({
                    type: "POST",
                    url: _actionUrl,
                    data: {
                        _token: _token,
                        _method: 'POST',
                        studentid: _studentld,
                        attendanceid: _attendanceid,
                    },
                    success: function(data) {
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
                console.log("Update Script", _token, _actionUrl, urlParams, _attendanceid, _studentld);
            }
        });

        $(document).on('click', '.student_leave', function() {
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

            if (_token && _actionUrl && _courseId && _studentld) {
                $.ajax({
                    type: "POST",
                    url: _actionUrl,
                    data: {
                        _token: _token,
                        _method: 'POST',
                        studentid: _studentld,
                        course_id: _courseId,
                    },
                    success: function(data) {
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
                console.log("Update Script", _token, _actionUrl, urlParams, _attendanceid, _studentld);
            }
        });




        $(document).on('click', '#attendance_in', function() {
            $.ajax({
                url: "{{ route($route . '.filter-student-data') }}",
                type: "GET",
                dataType: "json",
                data: {
                    filter_admission: $('#addmission_id').val(),
                },
                beforeSend: function(request) {
                    request.setRequestHeader("X-CSRF-TOKEN", $('meta[name="csrf-token"]').attr('content'));
                },
                success: function(response) {
                    console.log(response);
                    if (response.data && response.data.length > 0) {

                        if (response.attedances == "") {

                            var _token = '{{ csrf_token() }}';
                            var _actionUrl = $('#attendance_in').attr("data-url");
                            var id = response.data[0].id;
                            var register_id = response.data[0].register_id;
                            var course_id = response.data[0].course_id;
                            var batch_id = response.data[0].batch_id;
                            var stdName = response.data[0].admission.first_name + ' ' + response.data[0].admission.last_name;
                            // console.log(stdName);

                            if (_token && _actionUrl && id && course_id) {
                                // console.log("generate-inquiry-no L-31");
                                $.ajax({
                                    type: "POST",
                                    url: _actionUrl,
                                    data: {
                                        _token: _token,
                                        _method: 'POST',
                                        studentid: id,
                                        courseid: course_id,
                                    },
                                    success: function(data) {
                                        if (data?.status == true) {
                                            toastr.success(data?.message + " for " + stdName);
                                            $('#addmission_id').val("");
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
                        } else {
                            toastr.error('This Student Attendance Already Taken.');
                            $('#addmission_id').val("");
                        }

                    } else {
                        toastr.error("Admission ID is wrong please add correct ID");
                    }
                },
                error: function(xhr) {
                    console.error(xhr.responseText);
                    alert("Failed to load data.");
                }
            });
        });

        $(document).on('click', '#attendance_out', function() {
            $.ajax({
                url: "{{ route($route . '.filter-student-data') }}",
                type: "GET",
                dataType: "json",
                data: {
                    filter_admission: $('#addmission_id').val(),
                },
                beforeSend: function(request) {
                    request.setRequestHeader("X-CSRF-TOKEN", $('meta[name="csrf-token"]').attr('content'));
                },
                success: function(response) {
                    // console.log(response);
                    if (response.data && response.data.length > 0) {

                        const attendances = response.attedances || [];
                        const admissionInput = $('#addmission_id').val();
                        const matchedAttendance = attendances.find(item => item.admission_id == admissionInput);
                        if (matchedAttendance) {
                            const attendance_id = matchedAttendance.id;
                            const out_time = matchedAttendance.out_time;

                            if (out_time == null) {

                                var _token = '{{ csrf_token() }}';
                                var _actionUrl = $('#attendance_out').attr("data-url");
                                var id = response.data[0].id;
                                var register_id = response.data[0].register_id;
                                var course_id = response.data[0].course_id;
                                var batch_id = response.data[0].batch_id;
                                var stdName = response.data[0].admission.first_name + ' ' + response.data[0].admission.last_name;

                                if (_token && _actionUrl && attendance_id && id) {
                                    $.ajax({
                                        type: "POST",
                                        url: _actionUrl,
                                        data: {
                                            _token: _token,
                                            _method: 'POST',
                                            studentid: id,
                                            attendanceid: attendance_id,
                                        },
                                        success: function(data) {
                                            if (data?.status == true) {
                                                toastr.success(data?.message + " for " + stdName);
                                                $('#addmission_id').val("");
                                            } else {
                                                toastr.error(data?.message);
                                            }
                                        },
                                        error: function() {
                                            toastr.error('Something Went wrong Update Status failed.');
                                        }
                                    });
                                } else {
                                    console.log("Update Script", _token, _actionUrl, urlParams, _attendanceid, _studentld);
                                }

                            } else {
                                toastr.error('This Student Attendance Already Taken.');
                                $('#addmission_id').val("");
                            }
                        }
                    } else {
                        toastr.error("Admission ID is wrong please add correct ID");
                    }
                },
                error: function(xhr) {
                    console.error(xhr.responseText);
                    alert("Failed to load data.");
                }
            });
        });
    });
</script>


@include('software.utils.getCourse')
@include('software.utils.getBatchByCourseid')
<!-- @include('software.utils.getAdmission') -->
<!-- @include('software.utils.getCourse') -->
<!-- @include('software.utils.getBatchByCourseid') -->
@endsection
