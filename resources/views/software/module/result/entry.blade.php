@extends('software.layout.app')

@php
    $page_title = isset($modules['title']) ? $modules['title'] : 'Result Entry';
    $route = isset($modules['route']) ? $modules['route'] : 'result.entry';
@endphp
@section('title', $page_title)

@section('content')
    <div class="d-flex justify-content-lg-between px-1">
        @include('software.includes.breadcrumb', [
            'breadcrumbArray' => [['title' => $page_title, 'url' => '']],
            'route' => $route,
            'show_add_btn' => false,
            'show_filter_btn' => false,
            'show_back_btn' => false,
        ])
    </div>

    <!-- Filter Card -->
    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="row align-items-end">
                <!-- Course -->
                <div class="col-md-3 mb-3">
                    <label class="form-label fw-bold">Course</label>
                    <select class="form-control select2" id="courseSelect">
                        <option value="">Select Course</option>
                        @foreach ($courses as $course)
                            <option value="{{ $course->id }}" data-semesters="{{ $course->semester }}">{{ $course->course_name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Batch -->
                <div class="col-md-3 mb-3">
                    <label class="form-label fw-bold">Batch</label>
                    <select class="form-control select2" id="batchSelect">
                        <option value="">Select Batch</option>
                    </select>
                </div>

                <!-- Class -->
                <div class="col-md-3 mb-3">
                    <label class="form-label fw-bold">Class</label>
                    <select class="form-control select2" id="classSelect" name="class_ids[]" multiple="multiple" data-placeholder="Select Classes">
                    </select>
                </div>

                <!-- Semester -->
                <div class="col-md-3 mb-3">
                    <label class="form-label fw-bold">Semester</label>
                    <select class="form-control select2" id="semesterSelect">
                        <option value="">Select Semester</option>
                        @for ($i = 1; $i <= 8; $i++)
                            <option value="{{ $i }}">Semester {{ $i }}</option>
                        @endfor
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- Student List Card -->
    <div class="card shadow d-none" id="studentListCard">
        <div class="card-header border-bottom d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold">Student Results List</h5>
            <div>
                <span class="badge bg-label-info py-2 px-3 fw-bold" id="totalMarksInfo">Total Max Marks: -</span>
                <input type="hidden" id="totalMaxMarks" value="0">
            </div>
        </div>
        <div class="card-body mt-3">
            <form id="resultEntryForm">
                @csrf
                <input type="hidden" name="semester" id="hiddenSemester">
                
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover align-middle">
                        <thead class="table-light">
                            <tr id="tableHeaderRow">
                                <th style="width: 8%;">No</th>
                                <th style="width: 12%;">GR No</th>
                                <th>Student Name</th>
                                <th style="width: 25%;">Obtained Marks</th>
                                <th style="width: 20%;">Percentage</th>
                            </tr>
                        </thead>
                        <tbody id="student-tbody">
                            <!-- Dynamic Rows -->
                        </tbody>
                    </table>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('page_leavel_script')
    <script>
        $(document).ready(function() {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            let studentCard = $('#studentListCard');
            let studentTbody = $('#student-tbody');
            let totalMarksInfo = $('#totalMarksInfo');
            let totalMaxMarksField = $('#totalMaxMarks');

            function checkAndLoadStudents() {
                let courseId = $('#courseSelect').val();
                let batchId = $('#batchSelect').val();
                let classId = $('#classSelect').val();
                let semester = $('#semesterSelect').val();

                if (courseId && batchId && classId && classId.length > 0 && semester !== '') {
                    studentTbody.html('<tr><td colspan="5" class="text-center"><span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Loading...</td></tr>');
                    studentCard.removeClass('d-none');

                    $.ajax({
                        url: "{{ route('result.entry.students') }}",
                        type: "GET",
                        data: {
                            course_id: courseId,
                            batch_id: batchId,
                            class_id: classId,
                            semester: semester
                        },
                        success: function(response) {
                            if (response.success) {
                                $('#hiddenSemester').val(response.semester);
                                totalMaxMarksField.val(response.total_marks);
                                
                                let isSem1 = parseInt(response.semester) === 1;
                                
                                // Change header row dynamically
                                let headerRow = $('#tableHeaderRow');
                                if (isSem1) {
                                    headerRow.html(`
                                        <th style="width: 5%;">No</th>
                                        <th style="width: 10%;">GR No</th>
                                        <th>Student Name</th>
                                        <th style="width: 20%;">HSC Marks (Out of 700)</th>
                                        <th style="width: 10%;">HSC %</th>
                                        <th style="width: 20%;">Obtained Marks (Sem 1)</th>
                                        <th style="width: 15%;">Percentage (Sem 1)</th>
                                    `);
                                    totalMarksInfo.text("Total Max Marks (Sem 1): " + response.total_marks);
                                } else {
                                    headerRow.html(`
                                        <th style="width: 8%;">No</th>
                                        <th style="width: 12%;">GR No</th>
                                        <th>Student Name</th>
                                        <th style="width: 25%;">Obtained Marks</th>
                                        <th style="width: 20%;">Percentage</th>
                                    `);
                                    totalMarksInfo.text("Total Max Marks: " + response.total_marks);
                                }

                                studentTbody.empty();
                                if (response.students.length > 0) {
                                    response.students.forEach(function(student, idx) {
                                        let rowHtml = '';
                                        if (isSem1) {
                                            rowHtml = `
                                                <tr>
                                                    <td>${idx + 1}</td>
                                                    <td>${student.gr_no}</td>
                                                    <td class="fw-bold">${student.name}</td>
                                                    <td>
                                                        <input type="hidden" name="results[${idx}][admission_id]" value="${student.id}">
                                                        <input type="number" step="0.01" class="form-control hsc-marks-input num_only" data-admission-id="${student.id}" value="${student.hsc_marks || ''}" max="700" placeholder="Enter HSC Marks">
                                                    </td>
                                                    <td>
                                                        <input type="hidden" class="hsc-percentage-input" value="${student.hsc_percentage || ''}">
                                                        <strong class="hsc-percentage-val">${student.hsc_percentage ? student.hsc_percentage + '%' : '-'}</strong>
                                                    </td>
                                                    <td>
                                                        <input type="number" step="0.01" class="form-control obtained-marks-input num_only" name="results[${idx}][obtained_marks]" value="${student.obtained_marks || ''}" max="${response.total_marks}" placeholder="Enter Marks">
                                                    </td>
                                                    <td>
                                                        <input type="hidden" class="percentage-input" name="results[${idx}][percentage]" value="${student.percentage || ''}">
                                                        <strong class="percentage-val">${student.percentage ? student.percentage + '%' : '-'}</strong>
                                                    </td>
                                                </tr>
                                            `;
                                        } else {
                                            rowHtml = `
                                                <tr>
                                                    <td>${idx + 1}</td>
                                                    <td>${student.gr_no}</td>
                                                    <td class="fw-bold">${student.name}</td>
                                                    <td>
                                                        <input type="hidden" name="results[${idx}][admission_id]" value="${student.id}">
                                                        <input type="number" step="0.01" class="form-control obtained-marks-input num_only" name="results[${idx}][obtained_marks]" value="${student.obtained_marks || ''}" max="${response.total_marks}" placeholder="Enter Marks">
                                                    </td>
                                                    <td>
                                                        <input type="hidden" class="percentage-input" name="results[${idx}][percentage]" value="${student.percentage || ''}">
                                                        <strong class="percentage-val">${student.percentage ? student.percentage + '%' : '-'}</strong>
                                                    </td>
                                                </tr>
                                            `;
                                        }
                                        studentTbody.append(rowHtml);
                                    });
                                    studentCard.removeClass('d-none');
                                } else {
                                    let colspan = isSem1 ? 7 : 5;
                                    studentTbody.html(`<tr><td colspan="${colspan}" class="text-center text-muted">No students found in this course, batch, and class.</td></tr>`);
                                    studentCard.removeClass('d-none');
                                }
                            } else {
                                toastr.error(response.message || 'Error occurred.');
                                studentCard.addClass('d-none');
                            }
                        },
                        error: function(xhr) {
                            toastr.error('Error loading students list.');
                            studentCard.addClass('d-none');
                        }
                    });
                } else {
                    studentCard.addClass('d-none');
                }
            }

            // Course change AJAX
            $('#courseSelect').on('change', function() {
                let courseId = $(this).val();
                let semesters = $(this).find(':selected').data('semesters') || 8;
                
                // Update semester dropdown based on course
                let semesterSelect = $('#semesterSelect');
                let currentVal = semesterSelect.val();
                semesterSelect.empty().append('<option value="">Select Semester</option>');
                for (let i = 1; i <= semesters; i++) {
                    semesterSelect.append(`<option value="${i}">Semester ${i}</option>`);
                }
                if (currentVal && parseInt(currentVal) <= semesters) {
                    semesterSelect.val(currentVal).trigger('change.select2');
                } else {
                    semesterSelect.val('').trigger('change.select2');
                }

                $('#batchSelect').empty().append('<option value="">Select Batch</option>').val('').trigger('change.select2');
                $('#classSelect').empty().val([]).trigger('change.select2');
                checkAndLoadStudents();

                if (courseId) {
                    $.ajax({
                        type: 'POST',
                        url: "{{ route('get-batch') }}",
                        dataType: 'json',
                        data: {
                            course_id: courseId
                        },
                        success: function(response) {
                            if ((response.status === true || response.status === "true") && response.data) {
                                let options = '<option value="">Select Batch</option>';
                                response.data.forEach(function(item) {
                                    options += `<option value="${item.id}">${item.name}</option>`;
                                });
                                $('#batchSelect').html(options).trigger('change.select2');
                            }
                        }
                    });
                }
            });

            // Batch change AJAX
            $('#batchSelect').on('change', function() {
                let batchId = $(this).val();
                $('#classSelect').empty().val([]).trigger('change.select2');
                checkAndLoadStudents();

                if (batchId) {
                    $.ajax({
                        type: 'POST',
                        url: "{{ route('get-class-bybatch') }}",
                        dataType: 'json',
                        data: {
                            batch_id: batchId
                        },
                        success: function(response) {
                            if ((response.status === true || response.status === "true") && response.data) {
                                let options = '';
                                response.data.forEach(function(item) {
                                    options += `<option value="${item.id}">${item.name}</option>`;
                                });
                                $('#classSelect').html(options).trigger('change.select2');
                            }
                        }
                    });
                }
            });

            $('#classSelect, #semesterSelect').on('change', function() {
                checkAndLoadStudents();
            });

            // Calculate percentage dynamically
            $(document).on('input', '.obtained-marks-input', function() {
                let obtained = parseFloat($(this).val());
                let total = parseFloat(totalMaxMarksField.val()) || 0;
                let row = $(this).closest('tr');
                let percentageField = row.find('.percentage-input');
                let percentageValField = row.find('.percentage-val');

                if (!isNaN(obtained) && total > 0) {
                    if (obtained > total) {
                        toastr.warning('Obtained marks cannot be greater than Total Max Marks (' + total + ')');
                        $(this).val(total);
                        obtained = total;
                    }
                    let pct = (obtained / total) * 100;
                    percentageField.val(pct.toFixed(2));
                    percentageValField.text(pct.toFixed(2) + '%');
                } else {
                    percentageField.val('');
                    percentageValField.text('-');
                }
            });

            // Auto Save on Marks Input Change
            $(document).on('change', '.obtained-marks-input', function() {
                let inputField = $(this);
                let obtained = inputField.val();
                let total = parseFloat(totalMaxMarksField.val()) || 0;
                let row = inputField.closest('tr');
                let admissionId = row.find('input[name*="[admission_id]"]').val();
                let percentage = row.find('.percentage-input').val();
                let semester = $('#hiddenSemester').val();

                if (obtained !== '') {
                    let obtainedFloat = parseFloat(obtained);
                    if (!isNaN(obtainedFloat) && total > 0 && obtainedFloat > total) {
                        obtained = total;
                        percentage = 100;
                    }
                }

                $.ajax({
                    url: "{{ route('result.entry.store') }}",
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        semester: semester,
                        results: [
                            {
                                admission_id: admissionId,
                                obtained_marks: obtained,
                                percentage: percentage
                            }
                        ]
                    },
                    success: function(response) {
                        if (response.success) {
                            toastr.success(response.message || 'Result saved successfully.');
                        } else {
                            toastr.error(response.message || 'Failed to save result.');
                        }
                    },
                    error: function(xhr) {
                        toastr.error('An error occurred while saving.');
                    }
                });
            });

            // Calculate HSC percentage dynamically
            $(document).on('input', '.hsc-marks-input', function() {
                let obtained = parseFloat($(this).val());
                let total = 700; // HSC total is 700
                let row = $(this).closest('tr');
                let percentageField = row.find('.hsc-percentage-input');
                let percentageValField = row.find('.hsc-percentage-val');

                if (!isNaN(obtained) && total > 0) {
                    if (obtained > total) {
                        toastr.warning('Obtained marks cannot be greater than 700');
                        $(this).val(total);
                        obtained = total;
                    }
                    let pct = (obtained / total) * 100;
                    percentageField.val(pct.toFixed(2));
                    percentageValField.text(pct.toFixed(2) + '%');
                } else {
                    percentageField.val('');
                    percentageValField.text('-');
                }
            });

            // Auto Save on HSC Marks Input Change
            $(document).on('change', '.hsc-marks-input', function() {
                let inputField = $(this);
                let obtained = inputField.val();
                let total = 700;
                let row = inputField.closest('tr');
                let admissionId = inputField.data('admission-id');
                let percentage = row.find('.hsc-percentage-input').val();

                if (obtained !== '') {
                    let obtainedFloat = parseFloat(obtained);
                    if (!isNaN(obtainedFloat) && obtainedFloat > total) {
                        obtained = total;
                        percentage = 100;
                    }
                }

                $.ajax({
                    url: "{{ route('result.entry.store') }}",
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        semester: 0, // HSC is 0
                        results: [
                            {
                                admission_id: admissionId,
                                obtained_marks: obtained,
                                percentage: percentage
                            }
                        ]
                    },
                    success: function(response) {
                        if (response.success) {
                            toastr.success('HSC marks saved successfully.');
                        } else {
                            toastr.error(response.message || 'Failed to save HSC marks.');
                        }
                    },
                    error: function(xhr) {
                        toastr.error('An error occurred while saving.');
                    }
                });
            });

            // Check for pre-filled parameters from Result Master listing page
            let paramCourse = "{{ $courseId ?? '' }}";
            let paramBatch = "{{ $batchId ?? '' }}";
            let paramClasses = {!! json_encode($classId ? [$classId] : []) !!};
            let paramSemester = "{{ $semester ?? '' }}";

            if (paramCourse) {
                $('#courseSelect').val(paramCourse).trigger('change');
                
                $(document).ajaxComplete(function(event, xhr, settings) {
                    if (settings.url.indexOf('get-batch') !== -1 && paramBatch) {
                        $('#batchSelect').val(paramBatch).trigger('change');
                        paramBatch = null; // Prevent infinite trigger loop
                    }
                    if (settings.url.indexOf('get-class-bybatch') !== -1) {
                        if (paramClasses && paramClasses.length > 0) {
                            $('#classSelect').val(paramClasses).trigger('change');
                            paramClasses = null;
                        }
                        if (paramSemester !== '') {
                            $('#semesterSelect').val(paramSemester).trigger('change');
                            paramSemester = null; // Prevent infinite trigger loop
                        }
                    }
                });

            }
        });
    </script>
@endsection
