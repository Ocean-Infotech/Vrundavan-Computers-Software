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
    {{-- <link href="{{ asset('assets/select2/css/select2.min.css') }}" rel="stylesheet"> --}}
@endsection


@section('breadcrumb')
    <!-- start page title -->
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                {{-- <h3 class="page-title">{{ $page_title }}</h3> --}}
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('software.dashboard') }}">Dashboard</a></li>
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

                    <div class="col-md-4 mb-2">
                        <div class="form-group">
                            <label for="search">Search <span class="text-danger">*</span></label>
                            <select name="search" id="search"
                                class="form-control select2 @error('search') is-invalid @enderror">
                                <option value="">-- select --</option>

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

                    {{-- Admission Id --}}
                    <div class="col-md-4 mb-2">
                        <div class="form-group">
                            <label class="form-label">Admission Id <span class="text-danger">*</span></label>
                            <select id="admission_id"
                                class="form-control select2 @error('admission_id') is-invalid @enderror"
                                name="admission_id">
                                <option value="">-- Select Admission --</option>
                                {{-- @foreach ($admissions as $admission)
                                    <option value="{{ $admission->id }}"
                                        data-url="{{ route('get-admission-details', ['id' => $admission->id]) }}"
                                        {{ old('register_id', $edit->register_id ?? '') == $admission->id ? 'selected' : '' }}>
                                        {{ $admission->id }}
                                @endforeach --}}
                            </select>
                            @error('admission_id')
                                <span class="invalid-feedback">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>

                    {{-- Course Name --}}
                    <div class="col-md-4 mb-3">
                        <label for="course_id" class="form-label">
                            Course Name <span class="text-danger">*</span>
                        </label>
                        <select name="course_id" id="course_id"
                            class="form-control select2 @error('course_id') is-invalid @enderror">
                            <option value="">-- Select Course Name --</option>
                            {{-- @foreach ($courses as $course)
                                <option value="{{ $course->id }}" data-fee="{{ $course->course_fees }}"
                                    {{ old('course_id', $edit->course_id ?? '') == $course->id ? 'selected' : '' }}>
                                    {{ $course->course_name }}
                                </option>
                            @endforeach --}}
                        </select>
                        @error('course_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>




                    {{-- Student name --}}
                    <div class="col-md-4 mb-2">
                        <div class="form-group">
                            <label class="form-label"> Student Name <span class="text-danger">*</span> </label>
                            <input id="student_name" type="text"
                                class="form-control @error('student_name') is-invalid @enderror" name="student_name"
                                value="{{ isset($edit?->student_name) ? $edit?->student_name : old('student_name') }}"
                                autocomplete="student_name" autofocus placeholder="Student Name" readonly>

                            @error('student_name')
                                <span class="invalid-feedback">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>


                    {{-- Year / Semester --}}
                    <div class="col-md-4 mb-2">
                        <div class="form-group">
                            <label for="year_semester">Year / Semester <span class="text-danger">*</span></label>
                            <select name="year_semester" id="year_semester"
                                class="form-control select2 @error('year_semester') is-invalid @enderror">
                                <option value="">-- select Semester --</option>
                                <option value="1"
                                    {{ old('year_semester', $edit->year_semester ?? '') == '1' ? 'selected' : '' }}>1
                                </option>
                                <option value="2"
                                    {{ old('year_semester', $edit->year_semester ?? '') == '2' ? 'selected' : '' }}>2
                                </option>
                                <option value="3"
                                    {{ old('year_semester', $edit->year_semester ?? '') == '3' ? 'selected' : '' }}>3
                                </option>
                                <option value="4"
                                    {{ old('year_semester', $edit->year_semester ?? '') == '4' ? 'selected' : '' }}>4
                                </option>
                                <option value="5"
                                    {{ old('year_semester', $edit->year_semester ?? '') == '5' ? 'selected' : '' }}>5
                                </option>
                                <option value="6"
                                    {{ old('year_semester', $edit->year_semester ?? '') == '6' ? 'selected' : '' }}>6
                                </option>
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


                    {{-- Course Fees (Auto-filled) --}}
                    <div class="col-md-4 mb-3">
                        <label for="fees" class="form-label">
                            Course Fees <span class="text-danger">*</span>
                        </label>
                        <input type="number" name="fees" id="fees"
                            class="form-control @error('fees') is-invalid @enderror" placeholder="Course Fee"
                            value="{{ old('fees', $edit->fees ?? '') }}">
                        @error('fees')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Mode --}}
                    <div class="col-md-4 mb-3">
                        <label for="mode" class="form-label">
                            Mode <span class="text-danger">*</span>
                        </label>
                        <select name="mode" id="mode"
                            class="form-control select2 @error('mode') is-invalid @enderror">
                            <option value="">-- Select Mode --</option>
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
                    <div class="col-md-4 mb-3" id="upi_id_box" style="display: none;">
                        <label for="upi_id">UPI ID <span class="text-danger">*</span></label>
                        <input type="text" name="upi_id" id="upi_id"
                            class="form-control @error('upi_id') is-invalid @enderror"
                            value="{{ old('upi_id', $edit->upi_id ?? '') }}" placeholder="Enter UPI ID">
                        @error('upi_id')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- Cheque ID --}}
                    <div class="col-md-4 mb-3" id="cheque_id_box" style="display: none;">
                        <label for="cheque_no">Cheque No <span class="text-danger">*</span></label>
                        <input type="text" name="cheque_no" id="cheque_no"
                            class="form-control @error('cheque_no') is-invalid @enderror"
                            value="{{ old('cheque_no', $edit->cheque_no ?? '') }}" placeholder="Enter Cheque No">
                        @error('cheque_no')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- Return Reason --}}
                    <div class="col-md-4 mb-3" id="return_reason_box" style="display: none;">
                        <label for="return_reason">Return Reason <span class="text-danger">*</span></label>
                        <textarea name="return_reason" id="return_reason" class="form-control @error('return_reason') is-invalid @enderror"
                            placeholder="Enter Return Reason">{{ old('return_reason', $edit->return_reason ?? '') }}</textarea>
                        @error('return_reason')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
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
                    <h5 class="modal-title" id="passwordModalLabel">Enter Password to Confirm</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="password" id="confirm_password" class="form-control" placeholder="Enter Password">
                    <div id="password_error" class="text-danger mt-2 d-none">Password is required.</div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" id="modal_submit" class="btn btn-primary">Confirm</button>
                </div>
            </div>
        </div>
    </div>

@endsection
@section('page_leavel_script')
    <script src="{{ asset('assets/jquery/jquery.min.js') }}"></script>
    <script>
    $(document).ready(function () {
        $('#openPasswordModal').on('click', function () {
            $('#passwordModal').modal('show');
        });

        $('#modal_submit').on('click', function () {
            let password = $('#confirm_password').val().trim();
            if (password === '') {
                $('#password_error').removeClass('d-none');
            } else {
                $('#password_error').addClass('d-none');


                $('<input>').attr({
                    type: 'hidden',
                    name: 'password',
                    value: password
                }).appendTo('form');

                $('#passwordModal').modal('hide');

                $('form').submit();
            }
        });
    });
</script>

    <script>

        $(document).ready(function() {
            $('.select2').select2({ width: '100%', placeholder: 'Choose an option', allowClear: true });

            function updateFee() {
                const fee = $('#course_id option:selected').data('fee') || '';
                $('#fees').val(fee);
            }
            $('#course_id').on('change', updateFee);
            updateFee();

            function togglePaymentFields() {
                const mode = $('#mode').val().toLowerCase();
                $('#upi_id_box, #cheque_id_box, #return_reason_box').hide();
                if (mode === 'online') $('#upi_id_box').show();
                else if (mode === 'cheque') $('#cheque_id_box').show();
                else if (mode === 'return') $('#return_reason_box').show();
            }
            $('#mode').on('change', togglePaymentFields);
            togglePaymentFields();

            $('#admission_id').on('change', function() {
                let admissionId = $(this).val();
                if (admissionId) {
                    $.ajax({
                        url: '/get-admission-details/' + admissionId,
                        type: 'GET',
                        dataType: 'json',
                        success: function(data) {
                            if (data.first_name && data.last_name) {
                                $('#student_name').val(data.first_name + ' ' + data.last_name);
                            } else {
                                $('#student_name').val('');
                            }
                        },
                        error: function() {
                            $('#student_name').val('');
                        }
                    });
                } else {
                    $('#student_name').val('');
                }
            });
            $('#admission_id').trigger('change');

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
@endsection
