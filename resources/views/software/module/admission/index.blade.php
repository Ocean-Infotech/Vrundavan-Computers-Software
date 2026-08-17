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
    $exportColumns = $availableExportColumns ?? [];
    $defaultColumns = $defaultExportColumns ?? array_keys($exportColumns);
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
    <!-- <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">                                                                                                                                        <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.dataTables.min.css"> -->

@endsection

@section('content')
    <div class="d-flex justify-content-lg-between px-1">
        @include('software.includes.breadcrumb', [
            'breadcrumbArray' => [['title' => $page_title, 'url' => '']],
            'route' => $route,
            'show_add_btn' => false,
            'show_filter_btn' => true,
            'show_export_btns' => true,
            'show_excal_btn' => true,
            'show_print_btn' => false,
            'show_back_btn' => false,
        ])
    </div>


    <div class="row my-3">
        <div class="col-12 mb-4" id="filter_section" style="display: none;">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="row align-items-end">
                        <!-- Filter Input -->
                        <div class="col-md-4">
                            <label for="search" class="form-label">Filter by Student</label>
                            <input list="search" id="search_id" name="search"
                                class="form-control search @error('search') is-invalid @enderror"
                                value="{{ old('search') }}" placeholder="Search">
                            <datalist id="search" class="search_by_admission" data-append="search_by_admission"
                                data-selectedAdmissionId="{{ old('search') }}">
                            </datalist>
                        </div>

                        <!-- Clear Filter Button -->
                        <div class="col-md-2">
                            <button type="button" title="Clear Filter" id="cilory_filter"
                                class="btn btn-outline-danger mt-4">
                                <i class="ti ti-x"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <!-- <table id="yajra-datatables" class="dt-responsive table table-hover"> -->
                        <!-- <table id="yajra-datatables" class="dt-responsive table table-hover" style="width:100%"> -->
                        <table id="yajra-datatables" class="table table-striped table-hover dt-responsive">
                            @if (isset($columns))
                                <thead>
                                    <tr>
                                        @foreach ($columns as $item)
                                            <th class="{{ $item?->className ?? '' }}">
                                                {!! $item?->td_label ?: $item?->name !!}
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
 
    {{-- Export / Print Options Modal --}}
    <div class="modal fade" id="exportModal" aria-labelledby="exportModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exportModalLabel">Export Options</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="exportOptionsForm">
                    <div class="modal-body">
                        <input type="hidden" id="export_action" value="excel">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Data Scope</label>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="export_scope" id="scopeFiltered"
                                       value="filtered" checked>
                                <label class="form-check-label" for="scopeFiltered">
                                    Use current filters / search
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="export_scope" id="scopeAll"
                                       value="all">
                                <label class="form-check-label" for="scopeAll">
                                    Export complete Admission list
                                </label>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="export_course_filter" class="form-label fw-bold">Filter By Course</label>
                            <select id="export_course_filter" class="form-select select2">
                                <option value="">All Courses</option>
                                @foreach ($courses as $course)
                                    <option value="{{ $course->id }}">{{ $course->course_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="export_batch_filter" class="form-label fw-bold">Filter By Batch</label>
                            <select id="export_batch_filter" class="form-select select2">
                                <option value="">Filter By Batch</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="export_class_filter" class="form-label fw-bold">Filter By Class</label>
                            <select id="export_class_filter" class="form-select select2">
                                <option value="">Filter By Class</option>
                            </select>
                        </div>
                        <div>
                            <div class="d-flex flex-wrap gap-2 align-items-center mb-2">
                                <label class="form-label fw-bold mb-0">Columns</label>
                                <div class="ms-auto d-flex flex-wrap gap-2">
                                    <button type="button" class="btn btn-sm btn-light border" id="selectAllColumns">
                                        Select all
                                    </button>
                                    <button type="button" class="btn btn-sm btn-light border" id="clearAllColumns">
                                        Clear all
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-primary" id="resetDefaultColumns">
                                        Reset to default
                                    </button>
                                </div>
                            </div>
                            <p class="text-muted mb-2">Choose the columns that should appear in the export/print output.</p>
                            <div class="row" style="max-height: 260px; overflow-y: auto;">
                                @forelse ($exportColumns as $columnKey => $columnLabel)
                                    <div class="col-md-4 col-sm-6 mb-2">
                                        <div class="form-check">
                                            <input class="form-check-input export-column" type="checkbox"
                                                   value="{{ $columnKey }}" id="export_column_{{ $columnKey }}"
                                                   {{ in_array($columnKey, $defaultColumns) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="export_column_{{ $columnKey }}">
                                                {{ $columnLabel }}
                                            </label>
                                        </div>
                                    </div>
                                @empty
                                    <div class="col-12">
                                        <p class="text-muted mb-0">No exportable columns configured.</p>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Continue</button>
                    </div>
                </form>
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
                <div class="text-center my-3">
                    <img id="profilePic" src="" alt="Student Image" class="rounded-circle border border-2"
                        style="width: 120px; height: 120px; object-fit: cover;">
                </div>

                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th colspan="3"><b>Aadhaar Card No:</b> <span id="modalAadhar" class="text-lowercase"></span>
                            </th>
                        </tr>
                        <tr>
                            <th><b>Surname:</b> <span id="modalSurname" class="text-uppercase"></span></th>
                            <th><b>Name:</b> <span id="modalName" class="text-uppercase"></span></th>
                            {{-- <th><b>Mother Name:</b> <span id="MotherName" class="text-uppercase"></span></th> --}}
                        </tr>

                        <tr>
                            <th><b>Father Name:</b> <span id="FatherName" class="text-uppercase"></span></th>
                            <th colspan="2"><b>Mother Name:</b> <span id="MotherName" class="text-upercase"></span></th>
                        </tr>
                        <tr>
                            <th><b>Date of Birth:</b> <span id="modalDOB" class="text-lowercase"></span></th>
                            <th><b>Gender:</b> <span id="modalGender" class="text-lowercase"></span></th>
                        </tr>
                        <tr>
                            <th><b>Cast:</b> <span id="modalcast" class="text-lowercase"></span></th>
                            <th><b>Category:</b> <span id="modalCategory" class="text-lowercase"></span></th>
                        </tr>
                        <tr>
                            <th colspan="2"><b>Father Occupation:</b> <span id="modalfatheroccupation"
                                    class="text-lowercase"></span></th>
                        </tr>
                        <tr>
                            <th><b>Temporary Address:</b> <span id="modaltemporaryAddress" class="text-lowercase"></span>
                            </th>
                            <th><b>Permanent Address:</b> <span id="modalPermAddress" class="text-lowercase"></span></th>
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
                            <th><b>APAAR ID/ABC ID:</b> <span id="ModalAPAARID" class="text-lowercase"></span></th>
                            <th><b>UDISE No:</b> <span id="ModalUDISE" class="text-lowercase"></span></th>

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
                <div class="modal-header">
                    <h5 class="modal-title" id="courseModalLabel">Course Details</h5>
                    <div class="d-flex align-items-center">
                        <!-- Edit Button -->
                        {{-- <a href="#" id="courseEditBtn" class="btn btn-primary btn-sm me-2">
                            <i class="fas fa-edit"></i> Update Course
                        </a> --}}
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>
                </div>
                <div class="modal-body">
                    <div class="row mb-3">
                        <div id="courseDetailsHeader" class="card mb-4 shadow-sm">
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-md-8">
                                        <div class="container">
                                            <div class="row mb-2">
                                                <div class="col-md-6">
                                                    <p class="mb-1"><strong>GR No:</strong></p>
                                                </div>
                                                <div class="col-md-6">
                                                    <p class="mb-1" id="modalGRNo">-</p>
                                                </div>
                                            </div>

                                            <div class="row mb-2">
                                                <div class="col-md-6">
                                                    <p class="mb-1"><strong>Name:</strong></p>
                                                </div>
                                                <div class="col-md-6">
                                                    <p class="mb-1" id="modalFullName">-</p>
                                                </div>
                                            </div>

                                            <div class="row mb-2">
                                                <div class="col-md-6">
                                                    <p class="mb-1"><strong>Course:</strong></p>
                                                </div>
                                                <div class="col-md-6">
                                                    <p class="mb-1" id="modalCourse">-</p>
                                                </div>
                                            </div>

                                            <div class="row mb-2">
                                                <div class="col-md-6">
                                                    <p class="mb-1"><strong>Class:</strong></p>
                                                </div>
                                                <div class="col-md-6">
                                                    <p class="mb-1" id="modalClass">-</p>
                                                </div>
                                            </div>

                                            <div class="row mb-2">
                                                <div class="col-md-6">
                                                    <p class="mb-1"><strong>University:</strong></p>
                                                </div>
                                                <div class="col-md-6">
                                                    <p class="mb-1" id="modalUniversity">-</p>
                                                </div>
                                            </div>

                                            <div class="row mb-2">
                                                <div class="col-md-6">
                                                    <p class="mb-1"><strong>Enrollment No:</strong></p>
                                                </div>
                                                <div class="col-md-6">
                                                    <p class="mb-1" id="modalEnrollNo">-</p>
                                                </div>
                                            </div>

                                            <div class="row mb-2">
                                                <div class="col-md-6">
                                                    <p class="mb-1"><strong>ABC ID:</strong></p>
                                                </div>
                                                <div class="col-md-6">
                                                    <p class="mb-1" id="modalUdiseABC">-</p>
                                                </div>
                                            </div>
                                            <div class="row mb-2">
                                                <div class="col-md-6">
                                                    <p class="mb-1"><strong>DOJ:</strong></p>
                                                </div>
                                                <div class="col-md-6">
                                                    <p class="mb-1" id="modalDOJ">-</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="row mb-2">
                                            <div class="col-6">
                                                <p class="mb-1"><strong>&nbsp;</strong></p>
                                            </div>
                                            <div class="col-6">
                                                <p class="mb-1">&nbsp;</p>
                                            </div>
                                        </div>
                                        <div class="row mb-2">
                                            <div class="col-6">
                                                <p class="mb-1"><strong>&nbsp;</strong></p>
                                            </div>
                                            <div class="col-6">
                                                <p class="mb-1">&nbsp;</p>
                                            </div>
                                        </div>
                                        <div class="row mb-2">
                                            <div class="col-md-6">
                                                <p class="mb-1"><strong>Department:</strong></p>
                                            </div>
                                            <div class="col-md-6">
                                                <p class="mb-1" id="modalDepartment2">-</p>
                                            </div>
                                        </div>

                                        <div class="row mb-2">
                                            <div class="col-md-6">
                                                <p class="mb-1"><strong>Shift:</strong></p>
                                            </div>
                                            <div class="col-md-6">
                                                <p class="mb-1" id="modalShift">-</p>
                                            </div>
                                        </div>
                                        <div class="row mb-2">
                                            <div class="col-6">
                                                <p class="mb-1"><strong>&nbsp;</strong></p>
                                            </div>
                                            <div class="col-6">
                                                <p class="mb-1">&nbsp;</p>
                                            </div>
                                        </div>
                                        <div class="row mb-2">
                                            <div class="col-md-6">
                                                <p class="mb-1"><strong>SPID:</strong></p>
                                            </div>
                                            <div class="col-md-6">
                                                <p class="mb-1" id="modalSPID2">-</p>
                                            </div>
                                        </div>
                                        <div class="row mb-2">
                                            <div class="col-md-6">
                                                <p class="mb-1"><strong>UDISE No:</strong></p>
                                            </div>
                                            <div class="col-md-6">
                                                <p class="mb-1" id="modaludiseno">-</p>
                                            </div>
                                        </div>
                                        <div class="row mb-2">
                                            <div class="col-md-6">
                                                <p class="mb-1"><strong>DOC:</strong></p>
                                            </div>
                                            <div class="col-md-6">
                                                <p class="mb-1" id="modalDOC">-</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr></tr>
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






@endsection

@section('page_script_file')
    <!-- jQuery (required by DataTables) - removed duplicate to prevent select2 override -->
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
        const canUpdateStatus = @json(auth()->check() && (auth()->user()->hasRole('super-admin') || auth()->user()->hasRole('developer')));
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
                            // Profile picture handling
                            let profileUrl = data.profile_pic ?
                                "{{ asset('') }}" + data.profile_pic :
                                '';
                            $('#profilePic').attr('src', profileUrl);


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
                            $('#modalSurname').text(data.first_name?.toUpperCase() ?? '-');
                            $('#modalName').text(data.last_name?.toUpperCase() ?? '-');

                            $('#FatherName').text(data.father_name ?? '-');
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
                            $('#ModalUDISE').text(data.udise ?? '-');



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



        // Update the course modal display logic
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
                    if (response.success) {
                        let courses = response.courses;
                        let student = response.student;

                        if (courses.length > 0) {
                            // ✅ Set Edit Button URL
                            let editUrl = 'cource-registration/' + student.id + '/edit';
                            $('#courseEditBtn').attr('href', editUrl);

                            // ===== Populate Header =====
                            $('#modalGRNo').text(student.id ?? '-');
                            $('#modalFullName').text(
                                `${student.first_name} ${student.last_name} ${student.father_name}`
                                .toUpperCase()
                            );

                            let courseNames = courses.map(c => {
                                let batchName = c?.batch?.batch_name ?
                                    ` (${c.batch.batch_name})` : '';
                                return `${c.course.course_name}${batchName}`;
                            }).join(', ');

                            $('#modalCourse').text(courseNames);

                            $('#modalClass').text(courses[0]?.class?.class ?? '-');

                            let shiftTime = courses[0]?.shift ?
                                `${courses[0].shift.from_time} - ${courses[0].shift.to_time}` : '-';
                            $('#modalShift').text(shiftTime);

                            $('#modalUniversity').text(courses[0]?.university ?? '-');
                            $('#modalDepartment2').text(courses[0]?.department ?? '-');
                            $('#modalEnrollNo').text(student.enrolment_no ?? '-');
                            $('#modalSPID2').text(student.spid ?? '-');
                            $('#modalUdiseABC').text(student.apaar_id_abc_id ?? '-');
                            $('#modaludiseno').text(student.udise ?? '-');


                            let firstCourse = courses[0];

                            let doj = '-';
                            if (firstCourse.created_at) {
                                doj = moment(firstCourse.created_at).format('DD-MM-YYYY');
                            }

                            let doc = '-';
                            if (firstCourse.status === 'Completed' && firstCourse.updated_at) {
                                doc = moment(firstCourse.updated_at).format('DD-MM-YYYY');
                            }

                            $('#modalDOJ').text(doj);
                            $('#modalDOC').text(doc);

                            $('#modalName').text(
                                `${student.first_name} ${student.father_name} ${student.last_name}`
                            );

                            $('#courseData').empty();

                            courses.forEach(function(course) {
                                let row = `<div class="row mb-3">`;

                                if ("{{ $fees_collection_create ?? 'false' }}" == "true") {
                                    row += `<div class="col-md-3 mb-2">
                                <button type="button" class="btn btn-success w-100 collectFees" data-id="${course.id}">
                                    Fees
                                </button>
                            </div>`;
                                }

                                row += `<div class="col-md-3 mb-2">
                            <button type="button" class="btn btn-primary w-100 formBtn"
                                data-register_id="${course.register_id}"
                                data-course_id="${course.course_id}">
                                Form
                            </button>
                        </div>`;

                                row += `<div class="col-md-3 mb-2">
                                    <button type="button" class="btn btn-warning w-100 updateBtn"
                                 data-id="${course.id}"

                                        data-register_id="${course.register_id}"
                                        data-course_id="${course.course_id}">
                                        Update
                                    </button>
                                </div>`;



                                let statusOptions = ['Running', 'Completed', 'Cancel'];
                                let currentStatus = course.status ?? '';
                                let statusClass =
                                    currentStatus === 'Running' ? 'btn-primary' :
                                    currentStatus === 'Completed' ? 'btn-success' :
                                    currentStatus === 'Cancel' ? 'btn-danger' : 'btn-secondary';

                                let statusDropdown = '';
                                if (canUpdateStatus) {
                                    statusDropdown = `<div class="btn-group w-100">
                                <button type="button" class="btn ${statusClass} btn-sm dropdown-toggle w-100"
                                    data-bs-toggle="dropdown" aria-expanded="false" style="height: 37px">
                                    ${currentStatus || 'Select Status'}
                                </button>
                                <ul class="dropdown-menu w-100">`;

                                    statusOptions.forEach(function(option) {
                                        if (option !== currentStatus) {
                                            statusDropdown += `<li>
                                        <a href="javascript:void(0)" class="dropdown-item update-status"
                                            data-register_id="${course.register_id}"
                                            data-course_id="${course.course_id}"
                                            data-update_status="${option}">
                                            ${option}
                                        </a>
                                    </li>`;
                                        }
                                    });

                                    statusDropdown += `</ul></div>`;
                                } else {
                                    statusDropdown = `<button type="button" class="btn ${statusClass} btn-sm w-100" disabled style="height: 37px">
                                    ${currentStatus || 'Select Status'}
                                </button>`;
                                }
                                row +=
                                    `<div class="col-md-3 mb-2">${statusDropdown}</div>`;
                                row +=
                                    `</div>`;

                                $('#courseData').append(row);
                            });

                            $('#course').modal('show');
                        } else {
                            if ("{{ $course_register_add ?? 'false' }}" == 'true') {
                                toastr.warning("User does not have the right permissions.");
                                return;
                            }
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
                searching: false,
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
                        data.search = {
                            value: $('input[name="search"]').val(),
                            regex: false
                        };
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

        // Redraw DataTable on search input keyup / input / change with debounce
        let searchDebounceTimer;
        $('input[name="search"]').on('input change keyup', function() {
            clearTimeout(searchDebounceTimer);
            searchDebounceTimer = setTimeout(function() {
                dtable.draw();
            }, 500);
        });

        // Toggle filter section on filter button click
        $(document).on('click', '#show_filter', function() {
            $('#filter_section').slideToggle();
        });

        $("#cilory_filter").click(function() {
            $('.search').val('');
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
            let url = '/software/fees-collection/get-details/' + admId;
            window.open(url, '_blank'); // 🔹 Opens in new tab
        });
        $(document).on('click', '.updateBtn', function() {

            let id = $(this).data('id');
            window.location.href = "/software/cource-registration/" + id + "/edit";


            window.location.href = url;
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
        // Status button dropdown click event
        $(document).on('click', '.update-status', function() {
            let register_id = $(this).data('register_id');
            let course_id = $(this).data('course_id');
            let newStatus = $(this).data('update_status');

            $.ajax({
                url: "{{ route('admission.update-course-status') }}",
                type: "POST",
                data: {
                    _token: '{{ csrf_token() }}',
                    register_id: register_id,
                    course_id: course_id,
                    status: newStatus
                },
                success: function(response) {
                    if (response.status) {
                        toastr.success(response.message || 'Status updated successfully');

                        // Re-fetch courses for this student and refresh modal
                        $.ajax({
                            url: "{{ route($route . '.show') }}",
                            type: "GET",
                            data: {
                                id: register_id
                            },
                            dataType: "json",
                            success: function(resp) {
                                if (resp.success && resp.courses.length > 0) {
                                    let courses = resp.courses;
                                    let student = resp.student;

                                    // ✅ HEADER UPDATE - DOJ/DOC (AA ADD KARO)
                                    let firstCourse = courses[0];

                                    // Date of Joining
                                    let doj = firstCourse.created_at ?
                                        moment(firstCourse.created_at).format(
                                            'DD-MM-YYYY') : '-';

                                    // Date of Completion - Status Completed hoy tyare j show thase
                                    let doc = (firstCourse.status === 'Completed' &&
                                            firstCourse.updated_at) ?
                                        moment(firstCourse.updated_at).format(
                                            'DD-MM-YYYY') : '-';

                                    // Header ma DOJ ane DOC update karo
                                    $('#modalDOJ').text(doj);
                                    $('#modalDOC').text(doc);

                                    // Clear previous data
                                    $('#courseData').empty();

                                    courses.forEach(function(course) {
                                        // Start row
                                        let row =
                                            `<div class="row mb-3 align-items-center">`;

                                        // ===== Fees Button =====
                                        if ("{{ $fees_collection_create ?? 'false' }}" ==
                                            "true") {
                                            row += `
                                    <div class="col-md-3 mb-2">
                                        <button type="button" class="btn btn-success w-100 collectFees" data-id="${course.id}">
                                            Fees
                                        </button>
                                    </div>`;
                                        }

                                        // ===== Form Button =====
                                        row += `
                                <div class="col-md-3 mb-2">
                                    <button type="button" class="btn btn-primary w-100 formBtn"
                                        data-register_id="${course.register_id}"
                                        data-course_id="${course.course_id}">
                                        Form
                                    </button>
                                </div>`;

                                        // ===== Update Button =====
                                        if ("{{ $fees_collection_edit ?? 'false' }}" ==
                                            "true") {
                                            row += `
                                    <div class="col-md-3 mb-2">
                                        <button type="button" class="btn btn-warning w-100 updateBtn"
                                            data-register_id="${course.register_id}"
                                            data-course_id="${course.course_id}">
                                            Update
                                        </button>
                                    </div>`;
                                        }

                                        // ===== Status Dropdown =====
                                        let statusOptions = ['Running', 'Completed',
                                            'Cancel'
                                        ];
                                        let currentStatus = course.status ?? '';
                                        let statusClass =
                                            currentStatus === 'Running' ?
                                            'btn-primary' :
                                            currentStatus === 'Completed' ?
                                            'btn-success' :
                                            currentStatus === 'Cancel' ?
                                            'btn-danger' :
                                            'btn-secondary';

                                         let statusDropdown = '';
                                         if (canUpdateStatus) {
                                             statusDropdown = `<div class="btn-group w-100">
                                    <button type="button" class="btn ${statusClass} btn-sm dropdown-toggle w-100" style="height: 37px"
                                        data-bs-toggle="dropdown" aria-expanded="false">
                                        ${currentStatus || 'Select Status'}
                                    </button>
                                    <ul class="dropdown-menu w-100">`;

                                             statusOptions.forEach(function(option) {
                                                 if (option !== currentStatus) {
                                                     statusDropdown += `
                                             <li>
                                                 <a href="javascript:void(0)" class="dropdown-item update-status"
                                                     data-register_id="${course.register_id}"
                                                     data-course_id="${course.course_id}"
                                                     data-update_status="${option}">
                                                     ${option}
                                                 </a>
                                             </li>`;
                                                 }
                                             });

                                             statusDropdown += `</ul></div>`;
                                         } else {
                                             statusDropdown = `<button type="button" class="btn ${statusClass} btn-sm w-100" disabled style="height: 37px">
                                             ${currentStatus || 'Select Status'}
                                         </button>`;
                                         }

                                        row +=
                                            `<div class="col-md-3 mb-2">${statusDropdown}</div>`;
                                        row += `</div>`; // close row

                                        // Append the constructed row to container
                                        $('#courseData').append(row);
                                    });
                                } else {
                                    toastr.warning('No courses found for this student.');
                                }
                            },
                            error: function(err) {
                                console.error(err);
                                toastr.error('Error refreshing course modal');
                            }
                        });
                    } else {
                        toastr.error(response.message || 'Failed to update status');
                    }
                },
                error: function(xhr) {
                    console.error(xhr);
                    toastr.error('Error updating status');
                }
            });
        });

        // Form button click → open new view
        $(document).on('click', '.formBtn', function() {
            let register_id = $(this).data('register_id');
            let course_id = $(this).data('course_id');

            // redirect to view page
            window.open("/software/admission/view/" + register_id + "/" + course_id, "_blank");
        });
        $(document).on('click', '.open-attendance', function(e) {
            e.preventDefault();
            var admissionId = $(this).data('admission-id');
            localStorage.setItem('admission_id', admissionId);
            window.open("{{ url('software/attedance') }}", "_blank");
        });
        $(document).on('click', '.open-test', function(e) {
            e.preventDefault();
            var admissionId = $(this).data('admission-id');
            localStorage.setItem('admission_id', admissionId);
            window.open($(this).attr('href'), '_blank');
        });
    </script>
    <script type="text/javascript">
        $(function() {
            const defaultColumns = @json(array_values($defaultColumns));
            const exportModalElement = document.getElementById('exportModal');
            let exportModalInstance = null;

            if (exportModalElement && typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                exportModalInstance = new bootstrap.Modal(exportModalElement);
            }

            const showExportModal = () => {
                if (exportModalInstance) {
                    exportModalInstance.show();
                } else {
                    $('#exportModal').modal('show');
                }
            };

            const hideExportModal = () => {
                if (exportModalInstance) {
                    exportModalInstance.hide();
                } else {
                    $('#exportModal').modal('hide');
                }
            };

            function openExportModal(actionType) {
                $('#export_action').val(actionType);
                $('#exportModalLabel').text(actionType === 'print' ? 'Print Preview' : 'Export to Excel');
                showExportModal();
                
                // Initialize select2 inside the modal with the proper dropdown parent
                $('#export_course_filter').select2({ dropdownParent: $('#exportModal') });
                $('#export_batch_filter').select2({ dropdownParent: $('#exportModal') });
                $('#export_class_filter').select2({ dropdownParent: $('#exportModal') });
            }

            $('#exportExcelBtn').on('click', function(e) {
                e.preventDefault();
                openExportModal('excel');
            });

            $('#print_btn').on('click', function(e) {
                e.preventDefault();
                openExportModal('print');
            });

            $('#selectAllColumns').on('click', function() {
                $('.export-column').prop('checked', true);
            });

            $('#clearAllColumns').on('click', function() {
                $('.export-column').prop('checked', false);
            });

            $('#resetDefaultColumns').on('click', function() {
                $('.export-column').each(function() {
                    $(this).prop('checked', defaultColumns.includes($(this).val()));
                });
            });

            $('#exportOptionsForm').on('submit', function(e) {
                e.preventDefault();

                const selectedColumns = $('.export-column:checked').map(function() {
                    return $(this).val();
                }).get();

                if (!selectedColumns.length) {
                    alert('Please select at least one column to continue.');
                    return;
                }

                const scope = $('input[name="export_scope"]:checked').val() || 'filtered';
                const params = new URLSearchParams();
                params.append('scope', scope);

                const courseId = $('#export_course_filter').val();
                if (courseId) {
                    params.append('course_id', courseId);
                }

                const batchId = $('#export_batch_filter').val();
                if (batchId) {
                    params.append('batch_id', batchId);
                }

                const classId = $('#export_class_filter').val();
                if (classId) {
                    params.append('class_id', classId);
                }

                if (scope === 'filtered') {
                    const searchValue = $('#yajra-datatables_filter input[type="search"]').val();
                    if (searchValue) {
                        params.append('search', searchValue);
                    }
                }

                selectedColumns.forEach(column => params.append('columns[]', column));

                const actionType = $('#export_action').val();
                const baseUrl = actionType === 'print'
                    ? "{{ route($route . '.print') }}"
                    : "{{ route($route . '.export-excel') }}";

                const finalUrl = params.toString() ? `${baseUrl}?${params.toString()}` : baseUrl;

                if (actionType === 'print') {
                    window.open(finalUrl, '_blank');
                } else {
                    window.location.href = finalUrl;
                }

                hideExportModal();
            });

            // Store all batches for dynamic filtering
            const allBatches = @json($batches);

            $('#export_course_filter').on('change', function() {
                const courseId = $(this).val();
                let optionsHtml = '';
                
                if (courseId) {
                    optionsHtml += '<option value="">All Batches</option>';
                    allBatches.forEach(function(batch) {
                        if (batch.course_id == courseId) {
                            optionsHtml += `<option value="${batch.id}">${batch.batch_name}</option>`;
                        }
                    });
                } else {
                    optionsHtml += '<option value="">Filter By Batch</option>';
                }
                
                $('#export_batch_filter').html(optionsHtml).val('').trigger('change').select2({ dropdownParent: $('#exportModal') });
            });

            $('#export_batch_filter').on('change', function() {
                const batchId = $(this).val();
                let optionsHtml = '';
                
                if (batchId) {
                    $.ajax({
                        type: 'POST',
                        url: "{{ route('get-class-bybatch') }}",
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        data: {
                            batch_id: batchId
                        },
                        success: function(response) {
                            if (response.status) {
                                optionsHtml += '<option value="">All Classes</option>';
                                $.each(response.data, function(i, item) {
                                    optionsHtml += `<option value="${item.id}">${item.name}</option>`;
                                });
                            } else {
                                optionsHtml += '<option value="">Filter By Class</option>';
                            }
                            $('#export_class_filter').html(optionsHtml).val('').trigger('change').select2({ dropdownParent: $('#exportModal') });
                        },
                        error: function() {
                            optionsHtml += '<option value="">Filter By Class</option>';
                            $('#export_class_filter').html(optionsHtml).val('').trigger('change').select2({ dropdownParent: $('#exportModal') });
                        }
                    });
                } else {
                    optionsHtml += '<option value="">Filter By Class</option>';
                    $('#export_class_filter').html(optionsHtml).val('').trigger('change').select2({ dropdownParent: $('#exportModal') });
                }
            });
        });
    </script>


    @include('software.includes.script-delete-record')
    @include('software.includes.script-restore-record')
    {{-- @include('software.includes.script-update-status') --}}
    <script>
        $(document).ready(function() {
            let instance = $('.search_by_admission');
            let admission_id = instance.attr("data-selectedAdmissionId");

            $.ajax({
                type: 'POST',
                url: "{{ route('get-all-admission') }}",
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.status) {
                        let options = "<option value=''>Select Admission</option>";
                        if (response.data && response.data.length > 0) {
                            $.each(response.data, function(i, item) {
                                if (admission_id == item.id) {
                                    options += "<option value='" + item.id + "' selected>" + item.name + "</option>";
                                } else {
                                    options += "<option value='" + item.id + "'>" + item.name + "</option>";
                                }
                            });
                        }
                        if (instance.attr('data-append')) {
                            $("." + instance.attr('data-append')).empty().append(options);
                        }
                    }
                }
            });
        });
    </script>
@endsection
