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
    <!--datatable css-->
    <link rel="stylesheet" href="{{ asset('admin/assets/cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css') }}">
    <!--datatable responsive css-->
    <link rel="stylesheet"
        href="{{ asset('admin/assets/cdn.datatables.net/responsive/2.2.9/css/responsive.bootstrap.min.css') }}">

    <link rel="stylesheet" href="{{ asset('admin/assets/cdn.datatables.net/buttons/2.2.2/css/buttons.dataTables.min.css') }}">
@endsection

@section('content')
    <div class="d-flex justify-content-lg-between px-1">
        @include('software.includes.breadcrumb', [
            'breadcrumbArray' => [['title' => $page_title, 'url' => '']],
            'route' => $route,
            'show_add_btn' => true,
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
                        <table id="yajra-datatables" class="display table table-hover">
                            @if (isset($columns))
                                <thead>
                                    <tr>
                                        @foreach ($columns as $item)
                                            <th class="{{ $item?->className ?? '' }}">
                                                {{ ucfirst($item?->td_label ?? $item?->name) ?? '' }}</th>
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
                    <h5 class="modal-title" id="infoModalLabel">Student Info</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th><b>ID:</b> <span id="modalID" class="text-lowercase"></span></th>
                            <th><b>Date of Birth:</b> <span id="modalDOB" class="text-lowercase"></span></th>
                        </tr>
                        <tr>
                            <th><b>Temp Address:</b> <span id="modalTempAddress" class="text-lowercase"></span></th>
                            <th><b>Perm Address:</b> <span id="modalPermAddress" class="text-lowercase"></span></th>
                        </tr>
                        <tr>
                            <th><b>Self Mobile:</b> <span id="modalSelfMobile" class="text-lowercase"></span></th>
                            <th><b>Parent Mobile:</b> <span id="modalParentMobile" class="text-lowercase"></span></th>
                        </tr>
                        <tr>
                            <th><b>Additional Mobile:</b> <span id="modalAddMobile" class="text-lowercase"></span></th>
                            <th><b>WhatsApp Mobile:</b> <span id="modalWhatsappMobile" class="text-lowercase"></span></th>
                        </tr>
                        <tr>
                            <th><b>Email:</b> <span id="modalEmail" class="text-lowercase"></span></th>
                            <th><b>Cast:</b> <span id="modalCast" class="text-lowercase"></span></th>
                        </tr>
                        <tr>
                            <th><b>Occupation:</b> <span id="modalOccupation" class="text-lowercase"></span></th>
                            <th><b>Category:</b> <span id="modalCategory" class="text-lowercase"></span></th>
                        </tr>
                        <tr>
                            <th><b>Gender:</b> <span id="modalGender" class="text-lowercase"></span></th>
                            <th><b>Aadhar Number:</b> <span id="modalAadhar" class="text-lowercase"></span></th>
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
                            <p><strong>Name:</strong> <span id="modalName"></span></p>
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
                                    <th>Fees</th>
                                    <th>Form</th>
                                    <th>Update</th>
                                </tr>
                            </thead>
                            <tbody id="courseData">
                                <th><span id="id"></span></th>
                                <th><span id="course_id"></span></th>
                                <th><span id="date"></span></th>
                                <th><span id="doc"></span></th>
                                <th>
                                    <button type="button" class="btn btn-success btn-sm" id="createBtn">
                                        Create
                                    </button>
                                </th>
                                <th>
                                    <button type="button" class="btn btn-primary btn-sm" id="formBtn">Form</button>
                                </th>
                                <th>
                                    <button type="button" class="btn btn-warning btn-sm" id="updateBtn">Update</button>
                                </th>
                            </tbody>
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
    {{-- <script src="{{ asset('admin/assets/cdn.datatables.net/buttons/2.2.2/js/dataTables.buttons.min.js') }}"></script>
<script src="{{ asset('admin/assets/cdn.datatables.net/buttons/2.2.2/js/buttons.print.min.js') }}"></script>
<script src="{{ asset('admin/assets/cdn.datatables.net/buttons/2.2.2/js/buttons.html5.min.js') }}"></script>
<script src="{{ asset('admin/assets/cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js') }}"></script>
<script src="{{ asset('admin/assets/cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js') }}"></script>
<script src="{{ asset('admin/assets/cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js') }}"></script> --}}

    {{-- <script src="{{ asset('admin/assets/js/pages/datatables.init.js') }}"></script> --}}
@endsection

@section('page_leavel_script')
    <script type="text/javascript">
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
                                let date = new Date(data.date_of_birth);
                                let day = ('0' + date.getDate()).slice(-2);
                                let month = ('0' + (date.getMonth() + 1)).slice(-2);
                                let year = date.getFullYear().toString().slice(-0);
                                $('#modalDOB').text(`${day} ${month} ${year}`);
                            } else {
                                $('#modalDOB').text('-');
                            }
                            $('#modalTempAddress').text(data.temporary_address ?? '-');
                            $('#modalPermAddress').text(data.permanent_address ?? '-');
                            $('#modalSelfMobile').text(data.mobile_no ?? '-');
                            $('#modalParentMobile').text(data.parent_mobile_no ?? '-');
                            $('#modalAddMobile').text(data.other_mobile_no ?? '-');
                            $('#modalWhatsappMobile').text(data.whatsapp_no ?? '-');
                            $('#modalEmail').text(data.email_address ?? '-');
                            $('#modalCast').text(data.cast ?? '-');
                            $('#modalOccupation').text(data.occupation ?? '-');
                            $('#modalCategory').text(data.category ?? '-');
                            $('#modalGender').text(data.gender ?? '-');
                            $('#modalAadhar').text(data.aadhar_card_no ?? '-');
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
                success: function(response) {
                    if (response.success) {
                        let course = response.course;
                        let data = response.student;
                        let master = response.masterCours;
                        if (course) {
                            $('#modalName').text(data.first_name);
                            $('#id').text(course.id);
                            $('#course_id').text(response.course.course.course_name);
                            $('#date').text(course.date);
                            $('#doc').text(course.date);
                            $('#fee').text(course.fee);

                            $('#course').modal('show');
                        } else {
                            toastr.error('Course data not found');
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

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
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
                    "url": "{{ route($route . '.index') }}",
                    'beforeSend': function(request) {
                        request.setRequestHeader("X-CSRF-TOKEN", $('meta[name="csrf-token"]').attr(
                            'content'));
                    },
                    type: "GET",
                    data: function(data) {
                        data.search = $('input[type="search"]').val();
                        // data.status = $('select[name="status"] option:selected').val();
                        // data.role = $('select[name="role"] option:selected').val();
                    },
                },
                columns: {!! json_encode($columns) !!},
                language: {
                    searchPlaceholder: 'Search...',
                },
                // dom:'lBfrtip',
                // buttons: ["csv"],
            });
        });
        $('input[name="search"]').keyup(function() {
            dtable.draw();
        });
        jQuery(document).on('change', 'select', function(event) {
            event.preventDefault();
            dtable.draw();
        });


        $(document).on('click', '.open_google_drive_folder', function() {
            var id = $(this).data('id');
            var folderName = $(this).data('foldername');
            var folderUrl = $(this).data('folderurl');
            var button = $(this);

            if (folderUrl) {
                window.open(folderUrl, '_blank');
            } else {
                $('#yajra-datatables_processing').css('font-weight', 'bold').show();

                $.ajax({
                    url: "{{ route($route . '.create-google-drive-folder') }}",
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        id: id,
                        foldername: folderName,
                    },
                    success: function(response) {
                        console.log(response);
                        if (response.folder_url) {
                            button.attr('data-folderurl', response.folder_url);
                            toastr.success('Google Drive folder created successfully.');
                            window.open(response.folder_url, '_blank');
                        } else {
                            toastr.success('Google Drive folder created successfully.');
                        }
                        $('#yajra-datatables').DataTable().ajax.reload(null, false);
                    },
                    error: function(error) {
                        console.error(error);
                        toastr.error('Error creating Google Drive folder.');
                    },
                    complete: function() {
                        $('#yajra-datatables_processing').hide();
                    }
                });
            }
        });
    </script>


    @include('software.includes.script-delete-record')
    @include('software.includes.script-restore-record')
    @include('software.includes.script-update-status')
@endsection
