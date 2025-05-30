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
    <div class="card-body mt-4">
        <form id="FeesCollection"
            action="{{ isset($edit) && $edit?->id ? route($route . '.update', [$edit?->id]) : route($route . '.store') }}"
            method="POST" enctype="multipart/form-data">
            @csrf
            @isset($edit)
            @method('PUT')
            @endisset

            @if(!isset($edit))
            <div class="row mb-3">
                <div class="col-md-4 mb-2">
                    <div class="form-group">
                        <input list="search" id="search_id" name="search" class="form-control @error('search') is-invalid @enderror" value="{{ isset($edit?->admission_id) ? $edit?->admission_id : old('search') }}" placeholder="Search" autocomplete="off">

                        <datalist id="search" class="search_by_courceregistration" data-append="search_by_courceregistration" data-selectedCourceRegistrationId="{{ isset($edit?->admission_id) ? $edit?->admission_id : old('search') }}">
                        </datalist>
                        @error('search')
                        <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
                <div class="col-md-1 mb-2">
                    <button type="button" id="searchbtn" class="btn btn-secondary waves-effect waves-light"><i class="menu-icon ti ti-search"></i>Search</button>
                </div>
            </div>
            
            <div class="row" id="FeesCollectionDetails">
            @else
            
            <div class="row">
            @endif    
                <div class="col-md-6">
                    <div class="row">
                        {{-- student id --}}
                        <div class="col-md-3 mb-3">
                            <div class="form-group">
                                <label class="form-label">Student Id <span class="text-danger">*</span> </label>
                                <input id="student_id" type="text"
                                    class="form-control required @error('student_id') is-invalid @enderror num_only" name="student_id"
                                    value="{{ isset($edit?->student_id) ? $edit?->student_id : old('student_id') }}"
                                    placeholder="Student Id" readonly>
                                
                                @error('student_id')
                                <span class="invalid-feedback">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>

                        {{-- Admission Id --}}
                        <div class="col-md-3 mb-3">
                            <div class="form-group">
                                <label class="form-label">Admission Id <span class="text-danger">*</span></label>
                                <input id="admission_id" type="text"
                                    class="form-control required @error('admission_id') is-invalid @enderror num_only" name="admission_id"
                                    value="{{ isset($edit?->admission_id) ? $edit?->admission_id : old('admission_id') }}"
                                    placeholder="Admission Id" readonly>

                                @error('admission_id')
                                <span class="invalid-feedback">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>

                        {{-- Course Name --}}
                        <div class="col-md-6 mb-3">
                            <label for="course_id" class="form-label">
                                Course Name <span class="text-danger">*</span>
                            </label>
                            <input type="hidden" id="courseid" name="course_id" value="{{ isset($edit?->course_id) ? $edit?->course_id : old('courseid') }}">
                            <input id="course_id" type="text"
                                class="form-control required @error('course_id') is-invalid @enderror num_only" name="courseid"
                                value="{{ isset($edit?->course_id) ? $edit?->course->course_name : old('courseid') }}"
                                placeholder="Course Name" readonly>

                            @error('course_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Student name --}}
                        <div class="col-md-12 mb-3">
                            <div class="form-group">
                                <label class="form-label"> Student Name <span class="text-danger">*</span> </label>
                                <input id="student_name" type="text"
                                    class="form-control required @error('student_name') is-invalid @enderror num_only" name="student_name"
                                    value="{{ isset($edit?->student_name) ? $edit?->student_name : old('student_name') }}"
                                    placeholder="Student Name" readonly>

                                @error('student_name')
                                <span class="invalid-feedback">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>


                        {{-- Year / Semester --}}
                        <div class="col-md-3 mb-3">
                            <div class="form-group">
                                <label for="year_semester">Year / Semester <span class="text-danger">*</span></label>
                                <select name="year_semester" id="year_semester"
                                    class="form-control required select2 @error('year_semester') is-invalid @enderror" data-semester="{{ isset($edit?->year_semester) ? $edit?->year_semester : old('year_semester') }}" placeholder="Semester">
                                    <option value="">Select Semester</option>
                                    @if(isset($edit))
                                        @for ($semester = 1; $semester <= $edit?->course->semester; $semester++)
                                        <option value="{{ $semester }}"
                                        {{ (int) old('year_semester', $edit->year_semester ?? '') === $semester ? 'selected' : '' }}>
                                        {{ $semester }}
                                    </option>
                                        @endfor
                                    @endif
                                </select>
                                @error('year_semester')
                                <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        {{-- Date --}}
                        <div class="col-md-3 mb-3">
                            <div class="form-group">
                                <label for="date">Date <span class="text-danger">*</span></label>
                                <input type="date" name="date" id="date"
                                    class="form-control required datepicker @error('date') is-invalid @enderror"
                                    value="{{ isset($edit?->date) ? $edit?->date : old('date') }}" placeholder="Date">
                                @error('date')
                                <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        {{-- Course Fees (Auto-filled) --}}
                        <div class="col-md-3 mb-3">
                            <label for="fees" class="form-label">
                                Fees <span class="text-danger">*</span>
                            </label>
                            <input type="number" name="fees" id="fees"
                                class="form-control required @error('fees') is-invalid @enderror" placeholder="Course Fee"
                                value="{{ old('fees', $edit->fees ?? '') }}">
                            @error('fees')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Mode --}}
                        <div class="col-md-3 mb-3">
                            <label for="mode" class="form-label">
                                Mode <span class="text-danger">*</span>
                            </label>
                            <select name="mode" id="mode"
                                class="form-control required select2 @error('mode') is-invalid @enderror" placeholder="Mode">
                                <option value="">Select Mode</option>
                                <option value="Cash" {{ old('mode', $edit->mode ?? '') == 'Cash' ? 'selected' : '' }}>Cash
                                </option>
                                <option value="Online" {{ old('mode', $edit->mode ?? '') == 'Online' ? 'selected' : '' }}>
                                    Online</option>
                                <option value="Cheque" {{ old('mode', $edit->mode ?? '') == 'Cheque' ? 'selected' : '' }}>
                                    Cheque</option>
                                <option value="Discount" {{ old('mode', $edit->mode ?? '') == 'Discount' ? 'selected' : '' }}>
                                    Discount</option>
                                <option value="Return" {{ old('mode', $edit->mode ?? '') == 'Return' ? 'selected' : '' }}>
                                    Return</option>
                            </select>
                            @error('mode')
                            <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- UPI ID --}}
                        <div class="col-md-12 mb-3" id="upi_id_box" style="display: none;">
                            <label for="upi_id">UPI ID <span class="text-danger">*</span></label>
                            <input type="text" name="upi_id" id="upi_id"
                                class="form-control @error('upi_id') is-invalid @enderror"
                                value="{{ old('upi_id', $edit->upi_id ?? '') }}" placeholder="UPI ID">
                            @error('upi_id')
                            <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- Cheque ID --}}
                        <div class="col-md-12 mb-3" id="cheque_id_box" style="display: none;">
                            <label for="cheque_no">Cheque No <span class="text-danger">*</span></label>
                            <input type="text" name="cheque_no" id="cheque_no"
                                class="form-control @error('cheque_no') is-invalid @enderror"
                                value="{{ old('cheque_no', $edit->cheque_no ?? '') }}" placeholder="Cheque No">
                            @error('cheque_no')
                            <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- Return Reason --}}
                        <div class="col-md-12 mb-3" id="return_reason_box" style="display: none;">
                            <label for="return_reason">Return Reason <span class="text-danger">*</span></label>
                            <textarea name="return_reason" id="return_reason" class="form-control @error('return_reason') is-invalid @enderror"
                                placeholder="Return Reason">{{ old('return_reason', $edit->return_reason ?? '') }}</textarea>
                            @error('return_reason')
                            <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <table class="table table-bordered" id="ReceiptTable">
                        <thead>
                            <tr>
                                <th>Receipt ID</th>
                                <th>Fees</th>
                                <th>Pay Fee Date</th>
                                <!-- <th>Print Receipt</th> -->
                            </tr>
                        </thead>
                        <tbody>
                            @if(isset($edit))
                                @foreach($getfeescollection as $row)
                                    <tr>
                                        <td>{{ $row->id }}</td>
                                        <td>{{ $row->fees }}</td>
                                        <td>{{ \Carbon\Carbon::parse($row->date)->format('d-m-Y') }}</td>
                                        <!-- <td></td> -->
                                    </tr>
                                @endforeach
                            @endif
                        </tbody>
                    </table>
                    <div class="row mt-2">
                        <div class="col-md-4">
                            <h5 style="color: rgb(0, 0, 255);">Course Fees : <span id="CourseFees">00</span></h5>
                        </div>
                        <div class="col-md-4">
                            <h5 style="color: rgb(0, 128, 0);">Deposite Fees : <span id="DepositeFees">00</span></h5>
                        </div>
                        <div class="col-md-4">
                            <h4 style="color: rgb(255, 0, 0); font-weight: bold;">Pending Fees : <span id="PendingFees">00</span></h4>
                        </div>
                    </div>
                </div>

                {{-- submit  --}}
                <div class="col-md-12 text-center mt-4">
                    <button type="button" class="btn btn-success" id="openPasswordModal">{{ isset($edit) ? 'Update' : 'Submit' }}</button>
                    <a href="{{ route($route . '.index') }}" class="btn btn-danger">Cancel</a>
                </div>
            </div>

            

        </form>
    </div>
</div>
<!-- Password Confirmation Modal -->
<div class="modal fade" id="passwordModal" tabindex="-1" aria-labelledby="passwordModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="passwordModalLabel">Enter Password to Login</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="password" id="confirm_password" class="form-control" placeholder="Enter Password">
                <div id="password_error" class="text-danger mt-2 d-none">Password is required.</div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" id="check_master_password" class="btn btn-primary">Confirm</button>
            </div>
        </div>
    </div>
</div>
@endsection


@section('page_leavel_script')

<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>
<script>
    $(document).ready(function() {

        $('#openPasswordModal').on('click', function() {

            let isValid = true;

            $("#FeesCollection").find(".required").each(function() {

                if (!$(this).val()) {
                    $(this).addClass('is-invalid');
                    isValid = false;
                    showMessage($(this).attr("placeholder") + " is required", "error");

                } else {
                    $(this).removeClass("is-invalid");
                }
            });

            if (isValid) {
                $('#passwordModal').modal('show');
            }
        });

        $('#check_master_password').on('click', function() {
            let password = $('#confirm_password').val().trim();
            if (password === '') {
                $('#password_error').removeClass('d-none');
            } else {
                $('#password_error').addClass('d-none');
                
                $.ajax({
                    type: 'POST',
                    url: "{{ route($route . '.check-master-password') }}",
                    data: {
                        _token: '{{ csrf_token() }}',
                        password: password,
                    },
                    success: function(response) {

                        if(response.status==='true'){
                            $('#openPasswordModal').attr('type', 'submit');
                            $('#openPasswordModal').removeAttr('id');
                            showMessage("Password Match", "success");
                            $('#passwordModal').modal('hide');
                            $('#FeesCollection').submit();
                        }else{
                            $('#password_error').removeClass('d-none');
                            $('#password_error').text('Password do not match!');
                            showMessage("Password do not match!", "error");
                        }
                        
                    }
                });
            }
        });



        $('#FeesCollectionDetails').hide();
        $("#search_id").keyup(function abc() {
            searchdata();
        });
        
        $('#searchbtn').on('click', function() {
            searchdata();
        });
        // $('#searchbtn').trigger('click');

        function searchdata() {
            var search_id = $('#search_id').val();
            if (search_id === '') {
                $('#search_id').addClass('is-invalid');
                showMessage("search is required", "error");
            } else {
                $("#search_id").removeClass("is-invalid");
                if (search_id) {
                    $.ajax({
                        type: 'POST',
                        url: "{{ route($route . '.search-fees-collection-details') }}",

                        data: {
                            _token: '{{ csrf_token() }}',
                            id: search_id,
                        },
                        success: function(response) {
                            $('#FeesCollectionDetails').show();

                            if (response.data && response.data.length > 0) {

                                var studentdetails = response.data[0];

                                $('#student_id').val(studentdetails.admission.id);

                                $('#admission_id').val(studentdetails.id);

                                $('#course_id').val(studentdetails.course.course_name);
                                $('#courseid').val(studentdetails.course.id);
                                
                                //$('#fees').val(studentdetails.fee);
                                
                                var studentname = studentdetails.admission.first_name + ' ' + studentdetails.admission.father_name + ' ' + studentdetails.admission.last_name;
                                $('#student_name').val(studentname);

                                var semester_total = studentdetails.course.semester;
                                var semester = 1;
                                // var SemesterVal = $('#year_semester').data('semester');
                                var semester_rows = '<option value="">Select Semester</option>';
                                for (semester; semester <= semester_total; semester++) 
                                {
                                    // var selectedAttr = (SemesterVal == semester) ? ' selected' : '';
                                    semester_rows += '<option value="' + semester + '">' + semester + '</option>';
                                }
                                $('#year_semester').html(semester_rows);

                                var rows = '';
                                var depositefees = 0;
                                if (response.fees_collection && response.fees_collection.length > 0) {
                                    $.each(response.fees_collection, function(index, item) {
                                        rows += '<tr>';
                                        rows += '<td>' + item.id + '</td>';
                                        rows += '<td>' + item.fees + '</td>';
                                        rows += '<td>' + moment(item.date).format('DD-MM-YYYY') + '</td>';
                                        // rows += '<td></td>';
                                        rows += '</tr>';
                                        depositefees += +item.fees;
                                    });
                                    $('#CourseFees').text(studentdetails.fee);
                                    $('#DepositeFees').text(depositefees);
                                    $('#PendingFees').text(studentdetails.fee-depositefees);
                                } else {
                                    $('#CourseFees').text(studentdetails.fee);
                                    $('#DepositeFees').text('00');
                                    $('#PendingFees').text(studentdetails.fee);
                                    
                                    rows = '<tr><td class="text-center" colspan="4">No data available</td></tr>';
                                }
                                $('#ReceiptTable tbody').html(rows);

                            } else {
                                console.log("student id " + studentdetails.register_id);
                            }

                        }
                    });
                }
            }
        }

        function showMessage(message, type) {
            toastr[type](message);
        }
    });
</script>

<script>
    $(document).ready(function() {
        $('.select2').select2({
            width: '100%',
            placeholder: 'Choose an option',
            allowClear: true
        });

        function togglePaymentFields() {
            const mode = $('#mode').val().toLowerCase();
            $('#upi_id_box, #cheque_id_box, #return_reason_box').hide();
            if (mode === 'online') {
                $('#upi_id_box').show();
                $('#upi_id').addClass('required');
                $("#cheque_no").removeClass("required");
                $("#return_reason").removeClass("required");
            } else if (mode === 'cheque') {
                $('#cheque_id_box').show();
                $('#upi_id').removeClass('required');
                $("#cheque_no").addClass("required");
                $("#return_reason").removeClass("required");
            } else if (mode === 'return') {
                $('#return_reason_box').show();
                $('#upi_id').removeClass('required');
                $("#cheque_no").removeClass("required");
                $("#return_reason").addClass("required");
            } else {
                $('#upi_id').removeClass('required');
                $("#cheque_no").removeClass("required");
                $("#return_reason").removeClass("required");
            }
        }
        $('#mode').on('change', togglePaymentFields);
        togglePaymentFields();

        // $('#admission_id').on('change', function() {
        //     let admissionId = $(this).val();
        //     if (admissionId) {
        //         $.ajax({
        //             url: '/get-admission-details/' + admissionId,
        //             type: 'GET',
        //             dataType: 'json',
        //             success: function(data) {
        //                 if (data.first_name && data.last_name) {
        //                     $('#student_name').val(data.first_name + ' ' + data.last_name);
        //                 } else {
        //                     $('#student_name').val('');
        //                 }
        //             },
        //             error: function() {
        //                 $('#student_name').val('');
        //             }
        //         });
        //     } else {
        //         $('#student_name').val('');
        //     }
        // });
        // $('#admission_id').trigger('change');

        // Password Modal Example Hook
        $('#modal_submit').on('click', function() {
            let password = $('#confirm_password').val();
            if (!password) {
                $('#password_error').removeClass('d-none');
            } else {
                $('#password_error').addClass('d-none');
                // Submit form or perform secured action
                $('#passwordModal').modal('hide');
            }
        });


    });
</script>
@include('software.utils.getCourceRegistration')
@endsection