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


    <link rel="stylesheet" href="{{ asset('admin/assets/vendor/libs/flatpickr/flatpickr.css') }}" />

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
            'show_back_btn' => false,
        ])
    </div>

    <!-- @include('software.partials.flash_messages') -->

    <div class="card shadow my-3">
        <div class="card-header d-none">
            <h2 class="mb-0">
                {{ $page_title }}
            </h2>
        </div>
        <div class="card-body my-4">
            <form method="POST"
                action="{{ isset($edit) ? route($route . '.update', $edit->id) : route($route . '.store') }}">
                @csrf
                @isset($edit)
                    @method('PUT')
                @endisset

                <div class="row">

                    <div class="col-md-6 col-sm-12">
                        <h2 style="color: #101010; font-weight: bold; text-align: center; margin-bottom: 20px;">
                            Student Name :
                            <label id="StudentName" style="font-weight: 400; text-transform: uppercase;"></label>
                        </h2>
                        <div class="row">
                            {{-- University --}}

                            <div class="col-md-12 mb-3">
                                <div class="form-group">
                                    <label for="university">University <span class="text-danger">*</span></label>
                                    <select name="university" id="university"
                                        class="form-control search_by_university select2 @error('university') is-invalid @enderror"
                                        data-selected-university-id="{{ old('university', $edit->university ?? '') }}"
                                        required>
                                        <option value="">Select University</option>
                                    </select>

                                    @error('university')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>


                            {{-- Department --}}
                            <div class="col-md-12 mb-3">
                                <div class="form-group">
                                    <label for="department">Department <span class="text-danger">*</span></label>
                                    <select name="department" id="department"
                                        class="form-control search_by_department @error('department') is-invalid @enderror select2"
                                        data-selected-department-id="{{ old('department', $edit->department ?? '') }}"
                                        required>
                                        <option value="">Select Department</option>
                                    </select>


                                    @error('department')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>


                            {{-- Register No --}}
                            <div class="col-md-12 mb-3">
                                <div class="form-group">
                                    <label for="register_no">GR No<span class="text-danger">*</span></label>

                                    <input list="registers_id" id="register_id" name="register_id"
                                        class="form-control bg-light readonly @error('register_id') is-invalid @enderror"
                                        value="{{ old('register_id', '' . ($edit->register_id ?? ($student->id ?? ($admission_id ?? '')))) }}"
                                        placeholder="Register No. ">




                                    <datalist id="registers_id">
                                        @foreach ($admissions as $admission)
                                            <option value="{{ $admission->id }}"
                                                {{ (int) old('register_id', $edit->register_id ?? '') === $admission->id ? 'selected' : '' }}>
                                                {{ $admission->first_name . ' ' . $admission->father_name . ' ' . $admission->last_name }}
                                            </option>
                                        @endforeach
                                    </datalist>


                                    @error('register_no')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            {{-- Course Name --}}
                            <div class="col-md-12 col-sm-12 mb-3">
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

                            {{-- Batch --}}
                            <div class="col-md-12 col-sm-12 mb-3">
                                <div class="form-group">
                                    <label class="form-label d-flex justify-content-between align-items-center">
                                        <span>Select Batch <span class="text-danger">*</span></span>
                                        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal"
                                            data-bs-target="#createBatchModal">
                                            + Create
                                        </button>
                                    </label>

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






                            <div class="col-md-12 col-sm-12 mb-3">
                                <div class="form-group">
                                    <label class="form-label d-flex justify-content-between align-items-center">
                                        <span>Select Class <span class="text-danger">*</span></span>
                                        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal"
                                            data-bs-target="#createClassModal">
                                            + Create
                                        </button>
                                    </label>

                                    <select id="class" name="class_id"
                                        class="form-control search_by_class select2 @error('class_id') is-invalid @enderror select_filter"
                                        data-append="search_by_class"
                                        data-selectedClassId="{{ isset($edit) && $edit?->class_id ? $edit?->class_id : old('class_id') }}"
                                        autofocus>
                                        <option value="">Select Class</option>
                                    </select>

                                    @error('class_id')
                                        <span class="invalid-feedback">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>




                            {{-- Shift --}}
                            <div class="col-md-12 col-sm-12 mb-3">
                                <div class="form-group">
                                    <label class="form-label d-flex justify-content-between align-items-center">
                                        <span>Select Shift <span class="text-danger">*</span></span>
                                        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal"
                                            data-bs-target="#createShiftModal">
                                            + Create
                                        </button>
                                    </label>

                                    <select id="shift" name="shift_id"
                                        class="form-control search_by_shift select2 @error('shift_id') is-invalid @enderror select_filter"
                                        data-append="search_by_shift"
                                        data-selectedShiftId="{{ isset($edit) && $edit?->shift_id ? $edit?->shift_id : old('shift_id') }}"
                                        autofocus>
                                        <option value="">Select Shift</option>
                                    </select>

                                    @error('shift_id')
                                        <span class="invalid-feedback">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>



                            {{-- Fees (auto-filled) --}}
                            <div class="col-md-12 mb-3">
                                <div class="form-group">
                                    <label for="fee">Course Fees <span class="text-danger">*</span></label>
                                    <input type="text" name="fee" id="fee"
                                        class="form-control num_only @error('fee') is-invalid @enderror"
                                        value="{{ old('fee', $edit->fee ?? '') }}" placeholder="Course Fees">
                                    @error('fee')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            {{-- Date --}}
                            <div class="col-md-12 mb-3">
                                <label for="date">Joining Date <span class="text-danger">*</span></label>
                                <input type="date" name="date" id="flatpickr-date"
                                    class="form-control datepicker @error('date') is-invalid @enderror"
                                    value="{{ old('date', $edit->date ?? '') }}" placeholder="DD-MM-YYYY">

                                @error('date')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            @if (!isset($edit))
                            {{-- Lateral Entry Checkbox --}}
                            <div class="col-md-12 mb-3">
                                <div class="form-check form-switch">
                                    <input type="hidden" name="is_lateral_entry" value="0">
                                    <input class="form-check-input @error('is_lateral_entry') is-invalid @enderror" type="checkbox" name="is_lateral_entry" id="is_lateral_entry" value="1" {{ old('is_lateral_entry', $edit->is_lateral_entry ?? 0) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_lateral_entry">Lateral Admission</label>
                                    @error('is_lateral_entry')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            {{-- Starting Semester Dropdown --}}
                            <div class="col-md-12 mb-3" id="joining_semester_container">
                                <div class="form-group">
                                    <label for="joining_semester">Starting Semester <span class="text-danger">*</span></label>
                                    <select name="joining_semester" id="joining_semester" class="form-control select2 @error('joining_semester') is-invalid @enderror">
                                        <option value="">Select Starting Semester</option>
                                        @for ($sem = 1; $sem <= 6; $sem++)
                                            <option value="{{ $sem }}" {{ old('joining_semester', $edit->joining_semester ?? '') == $sem ? 'selected' : '' }}>
                                                Semester {{ $sem }}
                                            </option>
                                        @endfor
                                    </select>
                                    @error('joining_semester')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            @endif

                            {{-- Note --}}
                            <div class="col-md-12 mb-3">
                                <div class="form-group">
                                    <label for="note">Note</label>
                                    <textarea name="note" id="note" rows="3" class="form-control @error('note') is-invalid @enderror"
                                        placeholder="Enter Note">{{ old('note', $edit->note ?? '') }}</textarea>
                                    @error('note')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-sm-12" id="{{ isset($edit) ? '' : 'RegisterCource' }}">


                        <table class="table table-bordered" id="RegisterCourceTable">
                            <thead>
                                <tr>
                                    <th>Register No.</th>
                                    <th>Course Name</th>
                                    <th>Joining Date</th>
                                </tr>
                            </thead>
                            <tbody>

                            </tbody>
                        </table>
                    </div>

                    {{-- Submit --}}
                    <div class="col-md-12 text-center mt-4">

                        <button type="submit" class="btn btn-success">{{ isset($edit) ? 'Update' : 'Submit' }}</button>
                        <a href="{{ route('admission.index') }}" class="btn btn-danger">Cancel</a>
                    </div>
                </div>
            </form>

            <!-- Create Shift Modal -->
            <div class="modal fade" id="createShiftModal" tabindex="-1" aria-labelledby="createShiftLabel"
                aria-hidden="true">
                <div class="modal-dialog">
                    <form id="createShiftForm">
                        @csrf
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Create Shift</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="form-group mb-3">
                                    <label for="from_time">From Time</label>
                                    <input type="text" name="from_time" id="shift_from_time"
                                        class="form-control flatpickr-time-24" placeholder="HH:MM" required
                                        autocomplete="off">
                                </div>
                                <div class="form-group mb-3">
                                    <label for="to_time">To Time</label>
                                    <input type="text" name="to_time" id="shift_to_time"
                                        class="form-control flatpickr-time-24" placeholder="HH:MM" required
                                        autocomplete="off">
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-success" id="saveShiftBtn">Save
                                    Shift</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <!-- Create Class Modal -->
            <div class="modal fade" id="createClassModal" tabindex="-1" aria-labelledby="createClassModalLabel"
                aria-hidden="true">
                <div class="modal-dialog">
                    <form id="createClassForm">
                        @csrf
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="createClassModalLabel">Create New Class</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label for="class" class="form-label">Class Name</label>
                                    <input type="text" class="form-control" id="new_class_name" name="class">

                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" id="submitCreateClass" class="btn btn-success">Save</button>
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <!-- Create Batch Modal -->
            <div class="modal fade" id="createBatchModal" tabindex="-1" aria-labelledby="createBatchModalLabel"
                aria-hidden="true">
                <div class="modal-dialog">
                    <form id="createBatchForm">
                        @csrf
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="createBatchModalLabel">Create New Batch</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label for="new_batch_name" class="form-label">Batch Name</label>
                                    <input type="text" class="form-control" id="new_batch_name" name="batch_name"
                                        required>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="submit" class="btn btn-success" id="submitCreateBatch">Save</button>
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('page_leavel_script')
    @include('software.utils.getCourse')
    @include('software.utils.getDepartment')
    @include('software.utils.getUniversity')
    @include('software.utils.getBatchByCourseid')
    @include('software.utils.getClass')
    @include('software.utils.getShiftByBatchByCourseidByClass')


    <!-- <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
                                                                                                                                                                                                                                                <script src="https://cdn.jsdelivr.net/npm/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js"></script> -->



    <script src="{{ asset('admin/assets/vendor/libs/flatpickr/flatpickr.js') }}"></script>

    <script src="{{ asset('admin/assets/js/forms-pickers.js') }}"></script>
    <script>
        $(document).ready(function() {
            $('.select2').select2();

            if (typeof flatpickr !== 'undefined') {
                flatpickr('.flatpickr-time-24', {
                    enableTime: true,
                    noCalendar: true,
                    dateFormat: 'H:i',
                    time_24hr: true,
                    allowInput: true,
                });
            }

            setTimeout(function() {
                const dateInput = document.getElementById('flatpickr-date');
                if (dateInput && !dateInput.value) {
                    const today = new Date();
                    const yyyy = today.getFullYear();
                    const mm = String(today.getMonth() + 1).padStart(2, '0');
                    const dd = String(today.getDate()).padStart(2, '0');

                    dateInput.value = `${dd}-${mm}-${yyyy}`; // browser required format
                }
            }, 1000);

        });

        let firstOldFee = null; // store first old value only once

        $('#course').change(function() {
            let oldFee = $('#fee').val();

            if (oldFee === "") {
                // First time: fee input is empty
                let fees = $(this).find(':selected').data('fees') || '';
                $('#fee').val(fees);
                firstOldFee = fees;
            } else {
                if (firstOldFee === null) {
                    firstOldFee = oldFee; // store only once
                } else {
                    let fees = $(this).find(':selected').data('fees') || '';
                    $('#fee').val(fees);
                }
            }
        });

        $('#RegisterCource').hide();
        $(document).ready(function() {
            var register_id = $("#register_id").val();
            if (register_id) {
                $.ajax({
                    type: 'POST',
                    url: "{{ route($route . '.get-register-cource') }}",

                    data: {
                        _token: '{{ csrf_token() }}',
                        id: register_id,
                    },
                    success: function(response) {
                        $('#RegisterCource').show();

                        let rows = '';

                        if (response.data && response.data.length > 0) {
                            $.each(response.data, function(index, item) {
                                rows += '<tr>';
                                rows += '<td>' + item.id + '</td>';
                                rows += '<td>' + item.course.course_name + '</td>';
                                let formattedDate = "";
                                if (item.date) {
                                    let d = new Date(item.date);
                                    let day = ("0" + d.getDate()).slice(-2);
                                    let month = ("0" + (d.getMonth() + 1)).slice(-2);
                                    let year = d.getFullYear();
                                    formattedDate = day + "-" + month + "-" + year;
                                }
                                rows += '<td>' + formattedDate + '</td>';

                                rows += '</tr>';
                            });
                        } else {
                            rows =
                                '<tr><td class="text-center" colspan="3">No data available</td></tr>';
                        }

                        $('#RegisterCourceTable tbody').html(rows);
                      var studentname = response.admission.first_name + ' ' +
                  response.admission.last_name + ' ' +
                  response.admission.father_name;
                        $('#StudentName').text(studentname);
                    }
                });
            }

            function toggleLateralFields() {
                if ($('#is_lateral_entry').length > 0) {
                    if ($('#is_lateral_entry').is(':checked')) {
                        $('#joining_semester_container').show();
                        $('#joining_semester').attr('required', true);
                    } else {
                        $('#joining_semester_container').hide();
                        $('#joining_semester').removeAttr('required').val('').trigger('change');
                    }
                }
            }
            if ($('#is_lateral_entry').length > 0) {
                $('#is_lateral_entry').change(toggleLateralFields);
                toggleLateralFields();
            }
        });
    </script>
    <script>
        $(document).ready(function() {
            $('#submitCreateBatch').click(function(e) {
                e.preventDefault();

                let batchName = $('#new_batch_name').val().trim();
                let courseId = $('#course').val();

                // Validate required fields
                if (!courseId) {
                    alert('Please select a course first');
                    return;
                }
                if (!batchName) {
                    alert('Please enter a batch name');
                    return;
                }

                $.ajax({
                    url: '{{ route('batch.store') }}', // Your batch store route
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        batch_name: batchName,
                        course_id: courseId
                    },
                    success: function(response) {
                        // Check if the response contains the new batch ID and name
                        if (response.id && response.name) {
                            // Add new batch option to the batch dropdown and select it
                            let newOption = new Option(response.name, response.id, true, true);
                            $('#batch').append(newOption).trigger('change');

                            // Hide the modal only after successful insert
                            $('#createBatchModal').modal('hide');

                            // Reset the form fields
                            $('#createBatchForm')[0].reset();
                        } else {
                            alert('Batch created but response format is incorrect.');
                        }
                    },
                    error: function(xhr) {
                        console.error("Error:", xhr.responseText);
                        let msg = xhr.responseJSON?.message ?? 'Something went wrong';
                        alert('Error: ' + msg);
                    }
                });
            });
        });

        //  bach code End  ===========================      //

        $(document).on('click', '#submitCreateClass', function(e) {
            e.preventDefault();

            let className = $('#new_class_name').val().trim();
            console.log("Class Name entered:", className); // Check this in console

            if (!className) {
                alert('Please enter a class name');
                return;
            }

            $.ajax({
                url: '{{ route('class.store') }}',
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    class: className
                },
                success: function(response) {
                    if (response.id && response.name) {
                        let newOption = new Option(response.name, response.id, true, true);
                        $('#class').append(newOption).trigger('change');
                        $('#createClassModal').modal('hide');
                        $('#createClassForm')[0].reset();

                    }
                },
                error: function(xhr) {
                    let errorMsg = xhr.responseJSON?.message || 'Something went wrong';
                    alert('Error: ' + errorMsg);
                }
            });
        });



        //  clss code End  ===========================      //

        $(document).ready(function() {
            $('#saveShiftBtn').on('click', function(e) {
                e.preventDefault();

                $('#saveShiftBtn').prop('disabled', true).text('Saving...');

                let formData = {
                    _token: $('input[name="_token"]').val(),
                    from_time: $('input[name="from_time"]').val(),
                    to_time: $('input[name="to_time"]').val(),
                    course_id: $('#course').val(),
                    batch_id: $('#batch').val(),
                    class_id: $('#class').val()
                };

                $.ajax({
                    type: 'POST',
                    url: '{{ route('shift.store') }}',
                    data: formData,
                    success: function(response) {
                        if (response.status === 'success') {
                            let newOption = new Option(response.data.label, response.data.id,
                                true, true);
                            $('#shift').append(newOption).trigger('change');

                            $('#createShiftModal').modal('hide');
                            $('#createShiftForm')[0].reset();
                        }
                        $('#saveShiftBtn').prop('disabled', false).text('Save Shift');
                    },
                    error: function(xhr) {
                        let errorMessage = "";

                        if (xhr.status === 422 || xhr.status === 409) {
                            if (xhr.responseJSON.errors) {
                                $.each(xhr.responseJSON.errors, function(key, value) {
                                    errorMessage += value[0] + "\n";
                                });
                            } else if (xhr.responseJSON.message) {
                                errorMessage = xhr.responseJSON.message;
                            }
                        } else {
                            errorMessage = "Something went wrong. Please try again.";
                        }

                        alert(errorMessage);
                        $('#saveShiftBtn').prop('disabled', false).text('Save Shift');
                    }
                });
            });
        });




        //  shift code End  ===========================      //
    </script>


    @if (isset($edit))
        <script>
            $(document).ready(function() {
                var registerid = $("#register_id").val();
                if (registerid) {
                    $.ajax({
                        type: 'POST',
                        url: "{{ route($route . '.get-register-cource') }}",

                        data: {
                            _token: '{{ csrf_token() }}',
                            id: registerid,
                        },
                        success: function(response) {
                            $('#RegisterCource').show();

                            let rows = '';

                            if (response.data && response.data.length > 0) {
                                $.each(response.data, function(index, item) {
                                    rows += '<tr>';
                                    rows += '<td>' + item.id + '</td>';
                                    rows += '<td>' + item.course.course_name + '</td>';
                                    let formattedDate = "";
                                    if (item.date) {
                                        let d = new Date(item.date);
                                        let day = ("0" + d.getDate()).slice(-2);
                                        let month = ("0" + (d.getMonth() + 1)).slice(-2);
                                        let year = d.getFullYear();
                                        formattedDate = day + "-" + month + "-" + year;
                                    }
                                    rows += '<td>' + formattedDate + '</td>';

                                    rows += '</tr>';
                                });
                            } else {
                                rows =
                                    '<tr><td class="text-center" colspan="3">No data available</td></tr>';
                            }

                            $('#RegisterCourceTable tbody').html(rows);
                            var studentname = response.admission.first_name + ' ' +
                  response.admission.last_name + ' ' +
                  response.admission.father_name;
                            $('#StudentName').text(studentname);
                        }
                    });
                }
            });
        </script>
    @endif

@endsection
