@extends('software.layout.app')

@php
    $page_title = 'Registered Student';

    // $page_title = isset($modules['title']) ? $modules['title'] : null;
    $folder_path = isset($modules['folder_path']) ? $modules['folder_path'] : null;
    $route = isset($modules['route']) ? $modules['route'] : null;
    $permisstion_prefix = isset($modules['permisstion_prefix']) ? $modules['permisstion_prefix'] : null;
    $course_register_add = isset($modules['course-register_add']) && $modules['course-register_add'] ? 'true' : 'false';
    $fees_collection_create =
        isset($modules['fees-collection-create']) && $modules['fees-collection-create'] ? 'true' : 'false';
    $fees_collection_edit =
        isset($modules['fees-collection-edit']) && $modules['fees-collection-edit'] ? 'true' : 'false';
    $i = 0;
@endphp
@section('title', $page_title)

@section('page_style_file')
    {{-- <link href="{{ asset('public/assets/admin/plugins/datatable/dataTables.bootstrap4.min.css') }}" rel="stylesheet" type="text/css"> --}}
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="{{ asset('admin/assets/cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css') }}">
    <link rel="stylesheet"
        href="{{ asset('admin/assets/cdn.datatables.net/responsive/2.2.9/css/responsive.bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('admin/assets/cdn.datatables.net/buttons/2.2.2/css/buttons.dataTables.min.css') }}">
    <!-- <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
                                                                                        <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.dataTables.min.css"> -->

@endsection

@section('content')
    <div class="d-flex justify-content-lg-between px-1">
        @include('software.includes.breadcrumb', [
            'breadcrumbArray' => [['title' => $page_title, 'url' => '']],
            'route' => $route,
            'show_add_btn' => false,
            'show_filter_btn' => false,
            'show_export_btns' => false,
            'show_excal_btn' => false,
            'show_print_btn' => false,
            'show_back_btn' => false,
        ])
    </div>


    <div class="row my-3">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <!-- <table id="yajra-datatables" class="dt-responsive table table-hover"> -->
                        <!-- <table id="yajra-datatables" class="dt-responsive table table-hover" style="width:100%"> -->
                        <table id="yajra-datatables" class="table table-striped table-hover dt-responsive nowrap"
                            style="width: 100%;">
                            @if (isset($columns))
                                <thead>
                                    <tr>
                                        @foreach ($columns as $item)
                                            <th class="{{ $item?->className ?? '' }}">
                                                {{ ucfirst($item?->td_label ?? $item?->name) ?? '' }}
                                            </th>
                                        @endforeach
                                    </tr>
                                </thead>
                            @endif
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <!-- Course/View Modal -->
    <div class="modal fade" id="infoModal" tabindex="-1" aria-labelledby="infoModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="infoModalLabel">Student Info (Id : <span id="modalID"
                            class="text-lowercase"></span>)</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th colspan="2"><b>Aadhaar Card No:</b> <span id="modalAadhar" class="text-lowercase"></span>
                            </th>
                        </tr>
                        <tr>
                            <th><b>Full Name </b> <span id="fullname" class="text-upercase"></span></th>
                            <th><b>Mother Name </b> <span id="MotherName" class="text-upercase"></span></th>
                        </tr>
                        <tr>
                            <th><b>Date of Birth:</b> <span id="modalDOB" class="text-lowercase"></span></th>
                            <th><b>Gender :</b> <span id="modalGender" class="text-lowercase"></span></th>
                        </tr>
                        <tr>
                            <th><b>Cast:</b> <span id="modalcast" class="text-lowercase"></span></th>
                            <th><b>Category:</b> <span id="modalCategory" class="text-lowercase"></span></th>
                        </tr>
                        <tr>
                            <th colspan="2"><b>Father Occupation :</b> <span id="modalfatheroccupation"
                                    class="text-lowercase"></span></th>
                        </tr>
                        <tr>
                            <th><b>Temporary Address :</b> <span id="modaltemporaryAddress" class="text-lowercase"></span>
                            </th>
                            <th><b>Permanent Address :</b> <span id="modalPermAddress" class="text-lowercase"></span></th>
                        </tr>
                        <tr>
                            <th><b>Mobile No:</b> <span id="modalSelfMobile" class="text-lowercase"></span></th>
                            <th><b>Parent Mobile No:</b> <span id="modalParentMobile" class="text-lowercase"></span></th>
                        </tr>
                        <tr>
                            <th><b>Other Mobile No:</b> <span id="modalAddMobile" class="text-lowercase"></span></th>
                            <th><b>Whatsapp No:</b> <span id="modalWpMobile" class="text-lowercase"></span></th>
                        </tr>
                        <tr>
                            <th colspan="2"><b>Email Address:</b> <span id="modalEmail" class="text-lowercase"></span>
                            </th>
                        </tr>
                        <tr>
                            <th colspan="2"><b>APAAR ID/ABC ID:</b> <span id="ModalAPAARID"
                                    class="text-lowercase"></span></th>
                        </tr>
                        <tr>
                            <th><b>Enrollment No:</b> <span id="ModalEnrollment" class="text-lowercase"></span></th>
                            <th><b>SPID:</b> <span id="ModalSPID" class="text-lowercase"></span></th>
                        </tr>
                    </thead>
                </table>



            </div>
        </div>
    </div>



    <!-- Course Modal -->
    <div class="modal fade" id="course" tabindex="-1" aria-labelledby="courseModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header ">
                    <h5 class="modal-title" id="courseModalLabel">Course Details</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <p><strong>Name:</strong> <span class="text-uppercase" id="modalName"></span></p>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Addm ID</th>
                                    <th>Course</th>
                                    <th>DOJ</th>
                                    <th>DOC</th>
                                    @if ($fees_collection_create == 'true')
                                    <th>Fees</th>
                                    @endif
                                    <th>Form</th>
                                    @if ($fees_collection_edit == 'true')
                                        <th>Update</th>
                                        @endif
                                        <th>Status</th>
                                </tr>
                            </thead>

                            <tbody id="courseData">
                                <!-- Rows will be appended dynamically here -->
                            </tbody>

                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="updateModal" tabindex="-1" aria-labelledby="updateModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-md modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="updateModalLabel">Update Course Details</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="updateForm">
                        <input type="hidden" class="form-control" id="updateCourseId" name="course_id">
                        <input type="hidden" class="form-control" id="updateRegisterId" name="register_id">
                        <div class="mb-3">
                            <label for="updateCourseName" class="form-label">Course Name</label>
                            <input type="text" class="form-control" id="updateCourseName" name="courseName" readonly
                                required>
                        </div>
                        <div class="mb-3">
                            <label for="updateFees" class="form-label">Fees</label>
                            <input type="number" class="form-control" id="updateFees" name="fees" required>
                        </div>
                        <button type="submit" id="updateForm" class="btn btn-primary">Save Changes</button>
                    </form>
                </div>
            </div>
        </div>
    </div>




@endsection

@section('page_script_file')
    <!-- jQuery (required by DataTables) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>

    <!-- DataTables JS -->
    <script src="{{ asset('admin/assets/cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('admin/assets/cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js') }}"></script>
    <script src="{{ asset('admin/assets/cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js') }}"></script>

    <!-- <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
                                                                                        <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script> -->

@endsection

@section('page_leavel_script')
    <script type="text/javascript">
        // Show student details modal
        $(document).on('click', '.view-btn', function() {
            let id = $(this).data('id');

            $.ajax({
                url: "{{ route($route . '.show') }}",
                type: "GET",
                data: {
                    id: id
                },
                success: function(response) {
                    if (response.success) {
                        let data = response.student;
                        if (data) {
                            $('#modalID').text(data.id);
                            if (data.date_of_birth) {
                                /*
                                let date = new Date(data.date_of_birth);
                                let day = ('0' + date.getDate()).slice(-2);
                                let month = ('0' + (date.getMonth() + 1)).slice(-2);
                                let year = date.getFullYear();
                                $('#modalDOB').text(`${day} ${month} ${year}`);
                                */

                                $('#modalDOB').text(moment(data?.date_of_birth).format('DD-MM-YYYY'));
                            } else {
                                $('#modalDOB').text('-');
                            }
                            $('#modalAadhar').text(data.aadhar_card_no ?? '-');
                            $('#fullname').text((data.first_name + ' ' + data.father_name + ' ' + data
                                .last_name).toUpperCase() ?? '-');
                            $('#MotherName').text(data.mother_name ?? '-');
                            $('#modalGender').text(data.gender ?? '-');
                            $('#modalcast').text(data.cast ?? '-');
                            $('#modalCategory').text(data.category ?? '-');
                            $('#modalfatheroccupation').text(data.occupation ?? '-');
                            $('#modaltemporaryAddress').text(data.temporary_address ?? '-');
                            $('#modalPermAddress').text(data.permanent_address ?? '-');
                            $('#modalSelfMobile').text(data.mobile_no ?? '-');
                            $('#modalParentMobile').text(data.parent_mobile_no ?? '-');
                            $('#modalAddMobile').text(data.other_mobile_no ?? '-');
                            $('#modalWpMobile').text(data.whatsapp_no ?? '-');
                            $('#modalEmail').text(data.email_address ?? '-');
                            $('#ModalAPAARID').text(data.apaar_id_abc_id ?? '-');
                            $('#ModalEnrollment').text(data.enrolment_no ?? '-');
                            $('#ModalSPID').text(data.spid ?? '-');


                            $('#infoModal').modal('show');
                        } else {
                            toastr.error('Student data not found');
                        }
                    } else {
                        toastr.error(response.message || 'Student not found');
                    }
                },
                error: function(err) {
                    console.error(err);
                    toastr.error('Error fetching student info');
                }
            });
        });



        $(document).on('click', '.course-btn', function() {
            let id = $(this).data('id');

            $.ajax({
                url: "{{ route($route . '.show') }}",
                type: "GET",
                data: {
                    id: id
                },
                dataType: "json",
                success: function(response) {
                    console.log(response);

                    if (response.success) {
                        let courses = response.courses;
                        let student = response.student;

                        if (courses.length > 0) {
                            // Set student name
                            $('#modalName').text(
                                `${student.first_name} ${student.father_name} ${student.last_name}`);

                            // Clear previous table rows
                            $('#courseData').empty();

                            // Loop and append course rows
                            courses.forEach(function(course) {
                                let row = `
                            <tr>
                                <td>${course.id}</td>
                                <td>${course.course.course_name}</td>
                                <td>${moment(course.date).format('DD-MM-YYYY') ?? '-'}</td>
                                <td>${moment(course.date).format('DD-MM-YYYY') ?? '-'}</td>`;
                                if ("{{ $fees_collection_create ?? 'false' }}" == "true") {
                                    row += `
                                    <td>
                                        <button type="button" class="btn btn-success btn-sm collectFees" data-id="${course.id}">Fees</button>
                                    </td>`;
                                }

                                row += `
                            <td>
                                <button type="button" class="btn btn-primary btn-sm formBtn">Form</button>
                            </td>`;

                                if ("{{ $fees_collection_edit ?? 'false' }}" == "true") {
                                    row += `
                                <td>
                                    <button type="button" class="btn btn-warning btn-sm updateBtn" data-register_id="${course.register_id}" data-course_id="${course.course_id}">Update</button>
                                </td>`;
                                }
                                row += `<td>
            ${course.status == 'Completed'
                ? '<span class="badge bg-success">Completed</span>'
                : course.status == 'Cancel'
                    ? '<span class="badge bg-danger">Cancel</span>'
                    : '<span class="badge bg-primary">Running</span>'}
        </td>
                            </tr>`;

                                $('#courseData').append(row);
                            });

                            $('#course').modal('show');
                        } else {
                            if ("{{ $course_register_add ?? 'false' }}" == 'true') {
                                toastr.warning("User does not have the right permissions.");
                                return;
                            }

                            // No course found, redirect
                            window.location.href =
                                "{{ url('software/cource-registration/create') }}/" + student.id;
                        }
                    } else {
                        toastr.error(response.message || 'Student not found');
                    }
                },
                error: function(err) {
                    console.error(err);
                    toastr.error('Error fetching course info');
                }
            });
        });



        // Setup CSRF token for AJAX
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        $(document).on("click", ".create_google_drive_folder", function() {
            let studentId = $(this).data("id");

            $.ajax({
                url: "{{ route('admission.create-folder') }}",
                type: "POST",
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },

                data: {
                    id: studentId
                }, // only data, CSRF auto headers ma gayo che
                success: function(response) {
                    if (response.success) {
                        alert("Folder created successfully!");
                        $('#datatable').DataTable().ajax.reload();
                    } else {
                        alert("Failed: " + response.message);
                    }
                },
                error: function(xhr) {
                    alert("Something went wrong: " + xhr.responseJSON.message);
                }
            });
        });
        var dtable = null;
        $(document).ready(function() {
            dtable = $('#yajra-datatables').DataTable({
                searching: true,
                processing: true,
                serverSide: true,
                order: [
                    [1, 'desc']
                ],
                ajax: {
                    url: "{{ route($route . '.index') }}",
                    beforeSend: function(request) {
                        request.setRequestHeader("X-CSRF-TOKEN", $('meta[name="csrf-token"]').attr(
                            'content'));
                    },
                    type: "GET",
                    data: function(data) {
                        data.search = $('input[type="search"]').val();
                    },
                },
                columns: {!! json_encode($columns) !!},
                language: {
                    searchPlaceholder: 'Search...'
                },
                // dom:'lBfrtip',
                // buttons: ["csv"],
            });
        });

        // Redraw DataTable on search input keyup
        $('input[name="search"]').keyup(function() {
            dtable.draw();
        });

        // Redraw DataTable on select change
        $(document).on('change', 'select', function(event) {
            event.preventDefault();
            dtable.draw();
        });

        // Handle Google Drive folder open/create button click
        // $(document).on('click', '.open_google_drive_folder', function() {
        //     var id = $(this).data('id');
        //     var folderName = $(this).data('foldername');
        //     var folderUrl = $(this).data('folderurl');
        //     var button = $(this);

        //     if (folderUrl) {
        //         window.open(folderUrl, '_blank');
        //     } else {
        //         $('#yajra-datatables_processing').css('font-weight', 'bold').show();

        //         $.ajax({
        //             url: "{{ route($route . '.create-google-drive-folder') }}",
        //             type: 'POST',
        //             data: {
        //                 _token: '{{ csrf_token() }}',
        //                 id: id,
        //                 foldername: folderName,
        //             },
        //             success: function(response) {
        //                 console.log("response 459 | success", response);
        //                 if (response.folder_url) {
        //                     button.attr('data-folderurl', response.folder_url);
        //                     toastr.success('Google Drive folder created successfully.');
        //                     window.open(response.folder_url, '_blank');
        //                 } else {
        //                     toastr.success('Google Drive folder created successfully.');
        //                 }
        //                 $('#yajra-datatables').DataTable().ajax.reload(null, false);
        //             },
        //             error: function(error) {
        //                 console.error("response 470 | Error", error);
        //                 toastr.error('Error creating Google Drive folder.');
        //             },
        //             complete: function() {
        //                 $('#yajra-datatables_processing').hide();
        //             }
        //         });
        //     }
        // });
        // Create folder button click




        $(document).on('click', '.collectFees', function() {
            let admId = $(this).data('id');
            window.location.href = '/software/fees-collection/get-details/' + admId;
        });
        $(document).on('click', '.updateBtn', function() {
            $('#course').modal('hide');
            $('#updateModal').modal('show');

            let register_id = $(this).data('register_id');
            let course_id = $(this).data('course_id');

            $.ajax({
                url: "{{ route($route . '.course-fees-get') }}",
                method: 'GET',
                data: {
                    register_id: register_id,
                    course_id: course_id
                },
                success: function(response) {
                    $('#updateCourseName').val(response.course_name);
                    $('#updateFees').val(response.fee);
                    $('#updateCourseId').val(course_id);
                    $('#updateRegisterId').val(register_id);
                },
                error: function() {
                    alert('Error fetching course details.');
                }
            });
        });

        // Submit Update Form
        $(document).ready(function() {
            $('#updateForm').on('submit', function(e) {
                e.preventDefault();

                var registerId = $('#updateRegisterId').val();
                var courseId = $('#updateCourseId').val();
                var fees = $('#updateFees').val();

                $.ajax({
                    url: "{{ route($route . '.course-fees-update') }}",
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        register_id: registerId,
                        course_id: courseId,
                        fee: fees
                    },
                    success: function(response) {
                        if (response.success) {
                            $('#updateForm')[0].reset();
                            $('#updateModal').modal('hide');
                            $('.modal-backdrop').remove();
                            toastr.success(response.message);
                        } else {
                            toastr.error(response.message || 'Update failed');
                        }
                    },
                    error: function(xhr) {
                        console.error(xhr.responseText);
                        alert('Error updating fee.');
                    }
                });
            });
        });
        // Update Status on Change
$(document).on("change", ".updateStatus", function () {
    let registerId = $(this).data("register_id");
    let courseId = $(this).data("course_id");
    let status = $(this).val();

    $.ajax({
        url: "{{ route($route . '.course-status-update') }}", // 👈 New route
        type: "POST",
        data: {
            _token: "{{ csrf_token() }}",
            register_id: registerId,
            course_id: courseId,
            status: status
        },
        success: function (response) {
            if (response.success) {
                toastr.success(response.message || "Status updated successfully.");
            } else {
                toastr.error(response.message || "Failed to update status.");
            }
        },
        error: function (xhr) {
            console.error(xhr.responseText);
            toastr.error("Error updating status.");
        }
    });
});

    </script>



    @include('software.includes.script-delete-record')
    @include('software.includes.script-restore-record')
    @include('software.includes.script-update-status')
@endsection
