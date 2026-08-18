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
                method="POST" enctype="multipart/form-data" autocomplete="off">
                @csrf
                @isset($edit)
                    @method('PUT')
                @endisset

                @if (!isset($edit))
                    <div class="row mb-3">
                        <div class="col-md-4 mb-2">
                            <div class="form-group">

                                <input list="search" id="search_id" name="search"
                                    class="form-control @error('search') is-invalid @enderror"
                                    value="{{ $id ?? old('search', $admission_id ?? ($edit->admission_id ?? ($student_id ?? ''))) }}"
                                    placeholder="Search">

                                <datalist id="search" class="search_by_courceregistration"
                                    data-append="search_by_courceregistration"
                                    data-selectedCourceRegistrationId="{{ old('search', $edit->admission_id ?? ($student_id ?? '')) }}">
                                </datalist>

                                @error('search')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-1 mb-2">
                            <button type="button" id="searchbtn" class="btn btn-secondary waves-effect waves-light"><i
                                    class="menu-icon ti ti-search"></i>Search</button>
                        </div>
                    </div>

                    <div class="row" id="FeesCollectionDetails">
                    @else
                        <input type="hidden" id="search_id" name="search"
                            class="form-control @error('search') is-invalid @enderror"
                            value="{{ old('search', $edit?->admission_id ?? ($edit?->admission_id ?? '')) }}"
                            placeholder="Search">
                        <div class="row">
                @endif
                <div class="col-md-6">
                    <div class="row">
                        <!-- Student Name (Shown as Text) -->
                        <div class="col-md-12">
                            <label class="form-label"><strong>Student Name :</strong> <span
                                    id="student_name"class="">{{ isset($edit) && $edit?->student_name ? $edit?->student_name : '---' }}</span></label>
                        </div>


                        <div class="row">
                            <!-- Student Id -->
                            <div class="col-md-3">
                                <label class="form-label">
                                    <strong>Student Id:</strong>
                                    <span id="student_id">
                                        {{ isset($edit) && $edit?->student_id ? $edit?->student_id : '---' }}
                                    </span>
                                </label>
                            </div>

                            <!-- Admission Id -->
                            <div class="col-md-6">
                                <label class="form-label">
                                    <strong>Admission Id:</strong>
                                    <span id="admission_id">
                                        {{ isset($edit) && $edit?->admission_id ? $edit?->admission_id : '---' }}
                                    </span>
                                </label>
                            </div>
                        </div>


                        <!-- Course Name (Shown as Text) -->
                        <div class="col-md-12 mb-3">
                            <label class="form-label"><strong>Course Name:</strong> <span id="course_id"
                                    class="">{{ isset($edit) && $edit?->course && $edit?->course?->course_name ? $edit?->course?->course_name : '---' }}</span></label>
                            {{-- <p id="course_id" class="form-control-plaintext mb-0">--</p> --}}
                        </div>

                        <!-- Hidden field for course ID (for submission) -->
                        <input type="hidden" id="courseid" name="course_id">
                        <input type="hidden" id="student_id" name="student_id">
                        <input type="hidden" id="admission_id" name="admission_id">
                        <input type="hidden" id="student_name" name="student_name">

                        {{-- Year / Semester --}}
                        <div class="col-md-12 mb-3">
                            <div class="form-group">
                                <label for="year_semester">Year / Semester <span class="text-danger">*</span></label>
                                <select name="year_semester" id="year_semester"
                                    class="form-control required select2 @error('year_semester') is-invalid @enderror"
                                    data-semester="{{ old('year_semester', $selectedSemester ?? '') }}"
                                    placeholder="Semester">
                                    <option value="">Select Semester</option>
                                    @if (isset($edit))
                                        @for ($semester = 1; $semester <= $edit->course->semester; $semester++)
                                            <option value="{{ $semester }}"
                                                {{ (int) old('year_semester', $selectedSemester ?? ($edit->year_semester ?? '')) === $semester ? 'selected' : '' }}
                                                {{ in_array($semester, $paid_semesters ?? []) ? 'disabled' : '' }}>
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
                        <div class="col-md-12 mb-3">
                            <div class="form-group">
                                <label for="date">Date <span class="text-danger">*</span></label>
                                <input type="date" name="date" id="date"
                                    class="form-control required datepicker @error('date') is-invalid @enderror"
                                    value="{{ isset($edit?->date) ? $edit->date : old('date') ?? \Carbon\Carbon::now()->format('Y-m-d') }}"
                                    placeholder="Date">
                                @error('date')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>


                        {{-- Course Fees (Auto-filled) --}}
                        <div class="col-md-12 mb-3">
                            <label for="fees" class="form-label">
                                Course Fees <span class="text-danger">*</span>
                            </label>
                            <input type="number" name="fees" id="fees"
                                class="form-control required @error('fees') is-invalid @enderror" placeholder="Course Fee"
                                value="{{ old('fees', $edit->fees ?? '') }}"
                                data-editValue="{{ isset($edit) && $edit?->fees ? $edit?->fees : 0.0 }}">
                            @error('fees')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Mode --}}
                        <div class="col-md-12 mb-3">
                            <label for="mode" class="form-label">
                                Mode <span class="text-danger">*</span>
                            </label>
                            <select name="mode" id="mode"
                                class="form-control required select2 @error('mode') is-invalid @enderror"
                                placeholder="Mode">
                                <option value="">Select Mode</option>
                                <option value="Cash" {{ old('mode', $edit->mode ?? '') == 'Cash' ? 'selected' : '' }}>
                                    Cash
                                </option>
                                <option value="Online" {{ old('mode', $edit->mode ?? '') == 'Online' ? 'selected' : '' }}>
                                    Online</option>
                                <option value="Cheque" {{ old('mode', $edit->mode ?? '') == 'Cheque' ? 'selected' : '' }}>
                                    Cheque</option>
                                <option value="Discount"
                                    {{ old('mode', $edit->mode ?? '') == 'Discount' ? 'selected' : '' }}>
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
                        {{-- submit  --}}
                        <div class="col-md-12 text-center mt-4">
                            <button type="button" class="btn btn-success"
                                id="openPasswordModal">{{ isset($edit) ? 'Update' : 'Submit' }}</button>
                            <a href="{{ route($route . '.index') }}" class="btn btn-danger">Cancel</a>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <table class="table table-bordered" id="ReceiptTable">
                        <thead>
                            <tr>
                                <th>Receipt ID</th>
                                <th>Semester Name</th>
                                <th>Fees</th>
                                <th>Pay Fee Date</th>
                                <th>Print Receipt</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if (isset($edit))
                                @foreach ($getfeescollection as $row)
                                    <tr>
                                        <td>{{ $row->id }}</td>
                                        <th>{{ $row->year_semester }}</th>
                                        <td>{{ $row->fees }}</td>
                                        <td>{{ \Carbon\Carbon::parse($row->date)->format('d-m-Y') }}</td>
                                        <td>
                                            <a href="{{ route('fees-collection.create_by_id', $row->id) }}"
                                                class="btn btn-primary btn-sm" target="_blank">Print</a>

                                        </td>
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
                            <h5 style="color: rgb(0, 128, 0);">Deposit Fees : <span id="DepositeFees">00</span></h5>
                        </div>
                        <div class="col-md-4">
                            <h5 style="color: rgb(255, 0, 0); font-weight: bold;">Pending Fees : <span
                                    id="PendingFees">00</span></h5>
                            <input type="hidden" name="pandingfeesinput" id="pandingfeesinput" value="">
                        </div>
                    </div>

                    <div class="mt-4">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th width="70%">Semester</th>
                                    <th width="30%">Action</th>
                                </tr>
                            </thead>
                            <tbody id="semesterPrintRows">
                                <!-- JS will append rows here -->
                            </tbody>
                        </table>

                    </div>
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
                    <button type="button" id="check_master_password" class="btn btn-primary">Confirm</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                </div>
            </div>
        </div>
    </div>
@endsection


@section('page_leavel_script')

    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>

    <script>
        let editId = null;
        $(document).ready(function() {
            $('#date').on('click', function() {
                this.showPicker?.(); // Opens the datepicker if browser supports `showPicker()`
            });
        });

        window.paidSemesters = @json($paid_semesters ?? []);
        window.currentStudentDetails = @json($studentdetails ?? []);
        window.paidSemestersByAdmission = @json($paidSemestersGroupedByAdmission ?? []);
    </script>

    <script>
        $(document).ready(function() {
            $('#year_semester').on('change', function() {
                $('#semErrorMsg').remove(); // clear old error

                let selectedSemester = parseInt($(this).val());
                if (isNaN(selectedSemester)) return;

                // Find the minimum numeric semester option value in the dropdown
                let options = $('#year_semester option').map(function() {
                    let val = parseInt($(this).val());
                    return isNaN(val) ? null : val;
                }).get();
                let minSemester = Math.min.apply(Math, options);

                if (selectedSemester === minSemester) return; // always allow the first starting semester

                // Find previous option
                let previousSemester = selectedSemester - 1;
                let previousOption = $('#year_semester option[value="' + previousSemester + '"]');

                if (!previousOption.length || !previousOption.prop('disabled')) {
                    $(this).val('').trigger('change'); // reset selection
                    $('<div id="semErrorMsg" class="text-danger mt-1">You must complete payment for Semester ' +
                            previousSemester + ' before selecting Semester ' + selectedSemester + '.</div>')
                        .insertAfter($(this).parent());
                }
            });

            $('#search_id').on('change', function() {
                $('#year_semester').val('');
                $('#semErrorMsg').remove();
            });
        });
    </script>


    <script>
        $(document).ready(function() {
            editId = "{{ isset($edit) && $edit?->id ? $edit?->id : '' }}";
            const clearCreateFormKey = 'feesCollectionCreateSubmitted';

            function clearFeesCollectionCreateForm() {
                if (editId) {
                    return;
                }

                const form = document.getElementById('FeesCollection');
                if (form) {
                    form.reset();
                }

                $('#FeesCollectionDetails').hide();
                $('#search_id').val('');
                $('span#student_name, span#student_id, span#admission_id, span#course_id').text('---');
                $('input[name="student_name"], input[name="student_id"], input[name="admission_id"], input[name="course_id"]').val('');
                $('#year_semester').html('<option value="">Select Semester</option>').val('').removeClass('is-invalid');
                $('#date').val('{{ \Carbon\Carbon::now()->format('Y-m-d') }}');
                $('#fees').val('').prop('disabled', false).removeClass('is-invalid');
                $('#mode').val('').removeClass('is-invalid');
                $('#upi_id, #cheque_no, #confirm_password, #pandingfeesinput').val('');
                $('#password_error').addClass('d-none').text('Password is required.');
                $('#CourseFees, #DepositeFees, #PendingFees').text('00');
                $('#ReceiptTable tbody').html('<tr><td class="text-center" colspan="5">No data available</td></tr>');
                $('#semesterPrintRows').html('<tr><td class="text-center" colspan="2">No paid semesters</td></tr>');
                $('#semErrorMsg, #feeError, #feeErrorZero, #feeErrorCourse').remove();
                window.currentStudentDetails = null;
                window.paidSemesters = [];
                window.unpaidSemesters = [];

                if ($.fn.select2) {
                    $('#year_semester, #mode').trigger('change.select2');
                }
                $('#mode').trigger('change');
            }

            window.addEventListener('pageshow', function(event) {
                if (!editId && (event.persisted || sessionStorage.getItem(clearCreateFormKey) === '1')) {
                    sessionStorage.removeItem(clearCreateFormKey);
                    clearFeesCollectionCreateForm();
                }
            });

            if (!editId && sessionStorage.getItem(clearCreateFormKey) === '1') {
                sessionStorage.removeItem(clearCreateFormKey);
                clearFeesCollectionCreateForm();
            }

            // Open Password Modal button click - Validate required fields before opening modal
            $('#openPasswordModal').on('click', function(e) {
                e.preventDefault();

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

            // Check Master Password AJAX call
            $('#check_master_password').on('click', function(e) {
                e.preventDefault();
                let password = $('#confirm_password').val().trim();

                if (password === '') {
                    $('#password_error').removeClass('d-none').text('Password is required');
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
                            if (response.status === 'true') {
                                // Change button type to submit to allow form submit
                                $('#openPasswordModal').attr('type', 'submit');
                                // Remove id to prevent duplicate submission logic
                                $('#openPasswordModal').removeAttr('id');

                                showMessage("Password Match", "success");
                                $('#passwordModal').modal('hide');
                                if (!editId) {
                                    sessionStorage.setItem(clearCreateFormKey, '1');
                                }
                                $('#FeesCollection').submit();
                            } else {
                                $('#password_error').removeClass('d-none').text(
                                    'Password does not match!');
                                showMessage("Password does not match!", "error");
                            }
                        },
                        error: function() {
                            showMessage("Server error occurred", "error");
                        }
                    });
                }
            });

            // Hide FeesCollectionDetails initially
            $('#FeesCollectionDetails').hide();

            // Trigger search on Enter keyup in search_id input
            $("#search_id").keyup(function() {

                searchData();

            });
            let pendingStudent = sessionStorage.getItem('pendingFeeStudent');
            let pendingSemester = sessionStorage.getItem('pendingFeeSemester');
            if (pendingStudent && pendingSemester) {
                $('#search_id').val(pendingStudent);
                $('#year_semester').attr('data-semester', pendingSemester);
                sessionStorage.removeItem('pendingFeeStudent');
                sessionStorage.removeItem('pendingFeeSemester');
            }

            var search_id = $('#search_id').val()?.trim();
            if (search_id !== '') {
                searchData();
            }

            // Trigger search on search button click
            $('#searchbtn').on('click', function(e) {
                e.preventDefault();
                searchData();
            });


            window.currentStudentDetails = null;
            $('#FeesCollectionDetails').hide();


            function searchData() {
                var search_id = $('#search_id').val()?.trim();

                // if (search_id === '') {
                //     $('#search_id').addClass('is-invalid');
                //     showMessage("Search is required", "warning");
                //     return;
                // }
                // $("#search_id").removeClass("is-invalid");

                $.ajax({
                    type: 'POST',
                    url: "{{ route($route . '.search-fees-collection-details') }}",
                    data: {
                        _token: '{{ csrf_token() }}',
                        id: search_id,
                    },
                    success: function(response) {
                        if (response.data && response.data.length > 0) {
                            var studentdetails = response.data[0];
                            window.currentStudentDetails = studentdetails; // Save for global use

                            $('#FeesCollectionDetails').show();

                             let fullCourseFee = parseFloat(studentdetails.fee);
                             let totalSemesters = studentdetails.course.semester;

                             let startSem = (parseInt(studentdetails.is_lateral_entry) === 1 && studentdetails.joining_semester)
                                  ? parseInt(studentdetails.joining_semester)
                                  : 1;

                             // Fill semester dropdown
                             let semester_rows = '<option value="">Select Semester</option>';
                             for (let semester = startSem; semester <= totalSemesters; semester++) {
                                 semester_rows += '<option value="' + semester + '"';
                                 if ($("#year_semester").attr("data-semester") == semester) {
                                     semester_rows += " selected ";
                                 }
                                 semester_rows += '>' + semester +
                                     '</option>';
                             }
                             $('#year_semester').html(semester_rows);
                            // Fill other student info
                            let studentname = studentdetails.admission.first_name + ' ' +
                                studentdetails.admission.last_name + ' ' +
                                studentdetails.admission.father_name;


                            $('#student_id').text(studentdetails.admission.id);
                            $('#admission_id').text(studentdetails.id);
                            let batchname = studentdetails.batch ? studentdetails.batch.batch_name : "";
                            let coursename = studentdetails.course.course_name;

                            $('#course_id').text(coursename + (batchname ? ' - ' + batchname : ''));

                            $('#student_name').text(studentname);

                            // Hidden inputs
                            $('#student_name[name="student_name"]').val(studentname);
                            $('#student_id[name="student_id"]').val(studentdetails.admission.id);
                            $('#admission_id[name="admission_id"]').val(studentdetails.id);
                            $('#courseid[name="course_id"]').val(studentdetails.course.id);




                            window.paidSemesters = response.paid_semesters.map(Number);
                            window.unpaidSemesters = response.unpaid_semesters;
                            buildSemesterTable();

                            // Disable fully paid semesters
                            $('#year_semester option').each(function() {
                                let semVal = parseInt($(this).val());

                                // Disable if in paidSemesters
                                if (window.paidSemesters.includes(semVal)) {
                                    $(this).prop('disabled', true).text(
                                        `${semVal} (Completed)`); // Optional: add label
                                } else {
                                    // Check if it's in unpaid list and show pending fee
                                    let unpaid = window.unpaidSemesters.find(item => parseInt(
                                        item.semester) === semVal);
                                    if (unpaid) {
                                        $(this).prop('disabled', false).text(
                                            `${semVal} (Pending ₹${unpaid.pending_fee.toFixed(2)})`
                                        );
                                    }
                                }
                            });

                            // Fees collection
                            var depositedFees = 0;
                            var rows = '';

                            if (response.fees_collection && response.fees_collection.length > 0) {
                                $.each(response.fees_collection, function(index, item) {
                                    if (parseFloat(item.fees) <= 0) return; // Skip placeholder/unpaid records
                                    rows += '<tr>';
                                    rows += '<td>' + item.id + '</td>';
                                    rows += '<td>' + item.year_semester + '</td>';
                                    rows += '<td>' + item.fees + '</td>';
                                    rows += '<td>' + moment(item.date).format('DD-MM-YYYY') +
                                        '</td>';
                                    rows += '<td><a href="/software/fees-collection/print/' +
                                        item.id +
                                        '" target="_blank" class="btn btn-primary btn-sm">Print</a></td>';
                                    rows += '</tr>';

                                    depositedFees += parseFloat(item.fees);
                                });
                            } else {
                                rows =
                                    '<tr><td class="text-center" colspan="5">No data available</td></tr>';
                            }

                            $('#ReceiptTable tbody').html(rows);

                            // Set fee values initially
                            $('#CourseFees').text(formatCurrency(fullCourseFee));
                            $('#DepositeFees').text(formatCurrency(depositedFees));
                            $('#PendingFees').text(formatCurrency(fullCourseFee - depositedFees));
                            $('#fees').val(fullCourseFee -
                                depositedFees); // Auto-fill Course Fees = Pending Fees


                            $('#year_semester').trigger("change");
                        } else {
                            $('#FeesCollectionDetails').hide();
                            // showMessage("No data found for the given ID", "error");
                        }
                    },
                    error: function() {
                        // showMessage("Error retrieving data", "error");
                    }
                });
            }

            // // Semester change handler

            $('#year_semester').on('change', function() {
                var selectedSemester = parseInt($(this).val());
                var studentdetails = window.currentStudentDetails;

                if (!selectedSemester || !studentdetails) return;

                // Check if total pending is already 0.00
                var totalPending = parseFloat($('#PendingFees').text().replace(/[^0-9.-]+/g, "")) || 0;

                if (totalPending <= 0) {
                    // All fees paid — block further actions
                    $('#fees').val('').prop('disabled', true);
                    $('#CourseFees').text("0.00");
                    $('#PendingFees').text("0.00");
                    showMessage("All semester fees have been paid.", "info");
                    return;
                }

                $('#fees').val('').prop('disabled', false).removeClass('is-invalid');
                $('#feeError, #feeErrorZero, #feeErrorCourse').remove();

                 // Calculate semester fee
                 let studentFee = (parseFloat(studentdetails.fee) > 0) ? parseFloat(studentdetails.fee) : (parseFloat(studentdetails.course.course_fees) || 0);
                 let activeSemesters = (parseInt(studentdetails.is_lateral_entry) === 1 && studentdetails.joining_semester)
                     ? (parseInt(studentdetails.course.semester) - parseInt(studentdetails.joining_semester) + 1)
                     : parseInt(studentdetails.course.semester);
                 var semesterFee = (activeSemesters > 0) ? (studentFee / activeSemesters) : 0;

                // Get already paid semesters
                var paidSemesters = window.paidSemesters || [];

                if (paidSemesters.includes(selectedSemester)) {
                    $('#fees').val('').prop('disabled', true);
                    $('#PendingFees').text("0.00");
                    showMessage("Fees already paid for Semester " + selectedSemester, "info");
                } else {
                    // ✅ MAIN FIX: Auto-fill Course Fees with Pending Fee
                    $('#CourseFees').text(formatCurrency(semesterFee));

                    let unpaid = window.unpaidSemesters.find(item => parseInt(item.semester) ===
                        selectedSemester);
                    if (unpaid) {
                        var depositedFees = semesterFee - unpaid.pending_fee;
                        var pendingFee = unpaid.pending_fee;

                        $('#PendingFees').text(formatCurrency(pendingFee));
                        $('#pandingfeesinput').val(formatCurrency(pendingFee));
                        $('#DepositeFees').text(formatCurrency(depositedFees.toFixed(2)));

                        // ✅ AUTO FILL COURSE FEES INPUT WITH PENDING FEE
                        $('#fees').val(pendingFee.toFixed(2));
                    } else {
                        // If no unpaid record found, assume full semester fee is pending
                        var pendingFee = semesterFee;
                        $('#PendingFees').text(formatCurrency(pendingFee));
                        $('#pandingfeesinput').val(formatCurrency(pendingFee));
                        $('#DepositeFees').text("0.00");

                        // ✅ AUTO FILL COURSE FEES INPUT WITH FULL SEMESTER FEE
                        $('#fees').val(pendingFee.toFixed(2));
                    }
                }

                /** Edit time show table */
                if ($("#fees").attr('data-editValue') && editId) {
                    $('#fees').val($("#fees").attr('data-editValue'));
                }
            });


            // Toastr message helper function
            function showMessage(message, type) {
                toastr[type](message);
            }

            // Initialize select2 if needed (you might already have this elsewhere)
            if ($.fn.select2) {
                $('.select2').select2({
                    width: '100%'
                });
            }

            // Optional: To hide/show UPI and Cheque input boxes based on Mode selected
            $('#mode').on('change', function() {
                let mode = $(this).val();
                if (mode === 'Online') {
                    $('#upi_id_box').show();
                    $('#cheque_id_box').hide();
                } else if (mode === 'Cheque') {
                    $('#upi_id_box').hide();
                    $('#cheque_id_box').show();
                } else {
                    $('#upi_id_box').hide();
                    $('#cheque_id_box').hide();
                }
            }).trigger('change'); // trigger on page load

        });
    </script>

    <script>
        $(document).ready(function() {
            let storedData = sessionStorage.getItem('admissionDetails');
            if (storedData) {
                let data = JSON.parse(storedData);

                // Example: assuming your form fields have IDs matching your data keys
                $('#student_name').val(data.id || '');
                // $('#student_email').val(data.email_address || '');
                // $('#student_mobile').val(data.mobile_no || '');

                // Clear sessionStorage after use if you want
                sessionStorage.removeItem('admissionDetails');
            }

            function getSemesterFee() {
                return parseFloat($('#CourseFees').text().replace(/[^0-9.-]+/g, "")) || 0;
            }

            function getPendingFees() {
                return parseFloat($('#PendingFees').text().replace(/[^0-9.-]+/g, "")) || 0;
            }

            function getPendingFeesinut() {
                return parseFloat($('#pandingfeesinput').val().replace(/[^0-9.-]+/g, "")) || 0;
            }

            function getDepositedFees() {
                return parseFloat($('#DepositeFees').text().replace(/[^0-9.-]+/g, "")) || 0;
            }

            $('#fees').on('input', function() {
                let perSemesterFee = getSemesterFee();
                let depositedFee = getDepositedFees();
                let currentPending = getPendingFees();

                let enteredFee = formatCurrency($(this).val()) || 0;

                // Clear any previous messages
                $('#feeError, #feeErrorZero, #feeErrorCourse').remove();
                $(this).removeClass('is-invalid');

                if (isNaN(enteredFee) || enteredFee <= 0) {
                    $(this).val('');
                    $('<div id="feeErrorZero" class="text-danger mt-1">Enter valid amount greater than 0.</div>')
                        .insertAfter($(this));
                    $(this).addClass('is-invalid');

                    let newPendingFees = formatCurrency(perSemesterFee - depositedFee);
                    $('#PendingFees').text(newPendingFees);
                    $('#pandingfeesinput').val(newPendingFees);

                    console.log("enteredFee 707", getSemesterFee(), getDepositedFees(),
                        getPendingFeesinut(), $(this).val());
                    return;
                }

                if (currentPending <= 0) {
                    $(this).val(0);
                    // $(this).prop('disabled', true);
                    $(this).addClass('is-invalid');
                    $('<div id="feeErrorZero" class="text-danger mt-1">All semester fees are already paid.</div>')
                        .insertAfter($(this));
                    let newValue = formatCurrency(getSemesterFee() - (getDepositedFees() + enteredFee));

                    $('#PendingFees').text(newValue);
                    if ($(this).val() == 0) {
                        $('#PendingFees').text(parseFloat(getSemesterFee()) - parseFloat(
                            getDepositedFees()));
                    }
                    // $('#pandingfeesinput').val(newValue);
                    console.log("enteredFee 731", getSemesterFee(), getDepositedFees(), getPendingFees(),
                        getPendingFeesinut(), $(this).val(), newValue);
                    return;
                }

                if (editId) {
                    let editTimePending = parseFloat(getSemesterFee()) - parseFloat(getDepositedFees());
                    editTimePending = editTimePending + parseFloat($('#fees').attr('data-editValue'));
                    console.log("enteredFee 748", editTimePending);
                    let newValue = 0;
                    newValue = editTimePending - parseFloat(enteredFee);
                    newValue = formatCurrency(newValue);
                    $('#PendingFees').text(newValue);
                } else {
                    if (enteredFee > parseFloat(getPendingFeesinut())) {
                        $(this).val('');
                        $('<div id="feeError" class="text-danger mt-1">Entered amount exceeds pending fee (₹' +
                            parseFloat(getPendingFeesinut()) + ').</div>').insertAfter($(this));
                        $(this).addClass('is-invalid');
                        let newValuePending = parseFloat(getSemesterFee()) - parseFloat(getDepositedFees());
                        newValuePending = formatCurrency(newValuePending);

                        $('#PendingFees').text(newValuePending);
                        // $('#pandingfeesinput').val(newValuePending);
                        console.log("enteredFee 743", getSemesterFee(), getDepositedFees(),
                            getPendingFeesinut(), newValuePending);
                        return;
                    }

                    let newValue = 0;
                    newValue = parseFloat(getSemesterFee()) - (parseFloat(getDepositedFees()) + parseFloat(
                        enteredFee));
                    // console.log("enteredFee 766", getSemesterFee(), getDepositedFees(), getPendingFees(), getPendingFeesinut(), enteredFee, newValue);
                    newValue = formatCurrency(newValue);
                    $('#PendingFees').text(newValue);
                }


                /*
                // var currentCurseFees = $("#CourseFees").text();
                var currentCurseFees = $("#PendingFees").text();
                //  console.log("currentCurseFees"+$('#CourseFees').text())
                let newPending = currentCurseFees - enteredFee;
                // console.log("newPending"+newPending)
                if (newPending < 0) newPending = 0;

                $('#PendingFees').text(newPending.toFixed(2));
                */
                // $('#pandingfeesinput').val(newValue);

            });

        });
    </script>



    <script>
        // $(document).ready(function() {

        //     function getSemesterFee() {
        //         // Assuming fixed fee per semester (like 25000 total / 6 semesters)
        //         return parseFloat($('#TotalCourseFees').text()) || 0;
        //     }

        //     function getTotalDepositedFees() {
        //         return parseFloat($('#TotalDepositedFees').text()) || 0;
        //     }

        //     function getPendingFees() {
        //         return parseFloat($('#PendingFees').text()) || 0;
        //     }

        //     $('#fees').on('input', function() {
        //         var perSemesterFee = getSemesterFee();
        //         var depositedFee = getTotalDepositedFees();
        //         var pendingFee = getPendingFees();

        //         var newFee = parseFloat($(this).val()) || 0;
        //         console.log("L-787 ", perSemesterFee, depositedFee, pendingFee, newFee);
        //         return ;

        //         // Clear previous error
        //         $('#feeError, #feeErrorZero, #feeErrorCourse').remove();
        //         $(this).removeClass('is-invalid');

        //         if (pendingFee <= 0) {
        //             $(this).val('');
        //             $('<div id="feeErrorZero" class="text-danger mt-1">All course fees are paid.</div>')
        //                 .insertAfter($(this));
        //             $(this).addClass('is-invalid');
        //             $(this).prop('disabled', true);
        //             // $('#PendingFees').text("0.00");
        //             return;
        //         }

        //         if (isNaN(newFee) || newFee <= 0) {
        //             $(this).val('');
        //             $('<div id="feeErrorZero" class="text-danger mt-1">Enter valid amount greater than 0.</div>')
        //                 .insertAfter($(this));
        //             $(this).addClass('is-invalid');
        //             $('#PendingFees').text(pendingFee.toFixed(2));
        //             return;
        //         }

        //         if (newFee > pendingFee) {
        //             $(this).val('');
        //             $('<div id="feeError" class="text-danger mt-1">Entered amount exceeds pending fees (' +
        //                     pendingFee.toFixed(2) + ').</div>')
        //                 .insertAfter($(this));
        //             $(this).addClass('is-invalid');
        //             $('#PendingFees').text(pendingFee.toFixed(2));
        //             return;
        //         }

        //         // Valid case
        //         var newPending = pendingFee - newFee;
        //         $('#PendingFees').text(newPending.toFixed(2));
        //     });

        //     $('#semester').on('change', function() {
        //         $('#fees').val('').prop('disabled', false).removeClass('is-invalid');
        //         $('#PendingFees').text(calculatePendingFees().toFixed(2));
        //     });

        //     function calculatePendingFees() {
        //         var total = parseFloat($('#TotalCourseFees').text()) || 0;
        //         var deposited = parseFloat($('#TotalDepositedFees').text()) || 0;
        //         return total - deposited;
        //     }
        // });
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
    <script>
        $(document).ready(function() {

            // After searchData success – build semester print rows
            function renderSemesterPrintRows() {

                let student = window.currentStudentDetails;
                if (!student) return;

                let totalSem = student.course.semester;

                // ✅ Use Student Id instead of Admission Id
                let studentId = student.admission.id;

                let paidSemesters = window.paidSemesters || [];

                let rows = "";

                paidSemesters.forEach(function(sem) {

                    rows += `
                    <tr>
                        <td>Semester ${sem}</td>
                        <td>
                            <a
                                href="/software/students-fees-report/print?student_id=${studentId}&semester=${sem}"
                                class="btn btn-primary btn-sm"
                                target="_blank"
                            >
                                Print
                            </a>
                        </td>
                    </tr>
                `;
                });

                // If no paid semesters → show message
                if (paidSemesters.length === 0) {
                    rows = `
                    <tr>
                        <td colspan="2" class="text-center text-danger">No paid semesters</td>
                    </tr>
                `;
                }

                $("#semesterPrintRows").html(rows);
            }

            // Register the function globally
            window.buildSemesterTable = renderSemesterPrintRows;

        });
    </script>


    @include('software.utils.getCourceRegistration')
@endsection
