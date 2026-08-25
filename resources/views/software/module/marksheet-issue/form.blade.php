@extends('software.layout.app')

@php
    $page_title = isset($modules['title']) ? $modules['title'] : null;
    $folder_path = isset($modules['folder_path']) ? $modules['folder_path'] : null;
    $route = isset($modules['route']) ? $modules['route'] : null;
    $permission_prefix = isset($modules['permission_prefix']) ? $modules['permission_prefix'] : null;
@endphp
@section('title', $page_title)

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

    <div class="card shadow">
        <div class="card-body">
            <form
                action="{{ isset($edit) && $edit?->id ? route($route . '.update', [$edit?->id]) : route($route . '.store') }}"
                method="POST">
                @csrf
                @isset($edit)
                    @method('PUT')
                    <input type="hidden" name="id" value="{{ $edit?->id ?? '' }}" />
                @endisset

                @if (!isset($edit))
                    <!-- Filters -->
                    <div class="row mb-3">
                        <!-- Course Filter -->
                        <div class="col-md-4 col-sm-12 mb-2">
                            <div class="form-group">
                                <label class="form-label">Course <span class="text-danger">*</span></label>
                                <select id="filter_course_id" class="form-control select2" required>
                                    <option value="">Select Course</option>
                                    @foreach ($courses as $course)
                                        <option value="{{ $course->id }}">{{ $course->course_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <!-- Batch Filter -->
                        <div class="col-md-4 col-sm-12 mb-2">
                            <div class="form-group">
                                <label class="form-label">Batch <span class="text-danger">*</span></label>
                                <select id="filter_batch_id" class="form-control select2" required>
                                    <option value="">Select Batch</option>
                                </select>
                            </div>
                        </div>
                        <!-- Class Filter -->
                        <div class="col-md-4 col-sm-12 mb-2">
                            <div class="form-group">
                                <label class="form-label d-block">Class</label>
                                <div id="class_checkboxes_container" class="d-flex flex-wrap gap-3 mt-2 border p-2 rounded bg-white" style="min-height: 38px; align-items: center;">
                                    <span class="text-muted small">Select Batch first</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Common Inputs for Auto Fill -->
                    <div class="card bg-light border-0 mb-4">
                        <div class="card-body">
                            <h6 class="fw-bold mb-3"><i class="bx bx-cog me-1"></i> Common Inputs (Auto Fill)</h6>
                            <div class="row">
                                <!-- Common Semester -->
                                <div class="col-md-4 col-sm-6 mb-2">
                                    <div class="form-group">
                                        <label class="form-label">Semester</label>
                                        <select id="common_semester" class="form-control select2">
                                            <option value="">Select Semester</option>
                                            @for ($s = 1; $s <= 10; $s++)
                                                <option value="{{ $s }}">Semester {{ $s }}</option>
                                            @endfor
                                        </select>
                                    </div>
                                </div>
                                <!-- Common Series -->
                                <div class="col-md-4 col-sm-6 mb-2">
                                    <div class="form-group">
                                        <label class="form-label">Series</label>
                                        <input type="text" id="common_series" class="form-control" placeholder="Enter Series">
                                    </div>
                                </div>
                                <!-- Common Date -->
                                <div class="col-md-4 col-sm-6 mb-2">
                                    <div class="form-group">
                                        <label class="form-label">Date</label>
                                        <input type="date" id="common_date" class="form-control" value="{{ date('Y-m-d') }}">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Students Grid Table -->
                    <div class="table-responsive my-3">
                        <table class="table table-bordered table-striped align-middle" id="studentGridTable">
                            <thead class="table-light">
                                <tr>
                                    <th width="5%" class="text-center">
                                        <input type="checkbox" id="select_all_students" class="form-check-input" checked>
                                    </th>
                                    <th width="25%">Student Name</th>
                                    <th width="15%">Semester <span class="text-danger">*</span></th>
                                    <th width="15%">Series</th>
                                    <th width="15%">Date <span class="text-danger">*</span></th>
                                    <th width="25%">Note</th>
                                </tr>
                            </thead>
                            <tbody id="studentTableBody">
                                <tr>
                                    <td colspan="6" class="text-center text-muted">Please select Course, Batch, and Class to load student list.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                @else
                    <!-- Edit Mode (Single Student) -->
                    <div class="row">
                        <!-- Select Student -->
                        <div class="col-md-4 col-sm-12 mb-3">
                            <div class="form-group">
                                <label class="form-label">Student <span class="text-danger">*</span></label>
                                <select id="admission_id" name="admission_id" class="form-control select2" required>
                                    @foreach ($students as $student)
                                        <option value="{{ $student->id }}" @if ($edit->admission_id == $student->id) selected @endif>
                                            {{ trim(($student->first_name ?? '') . ' ' . ($student->last_name ?? '') . ' ' . ($student->father_name ?? '')) }} (GR: {{ $student->gr_no ?? '-' }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- Semester -->
                        <div class="col-md-4 col-sm-12 mb-3">
                            <div class="form-group">
                                <label class="form-label">Semester <span class="text-danger">*</span></label>
                                <select name="semester" class="form-control select2" required>
                                    @php
                                        $totalSemesters = 10;
                                        $courseReg = $edit->admission->courses->first();
                                        if ($courseReg && $courseReg->course) {
                                            $totalSemesters = (int) $courseReg->course->semester;
                                        }
                                    @endphp
                                    @for ($i = 1; $i <= $totalSemesters; $i++)
                                        <option value="{{ $i }}" {{ $edit->semester == $i ? 'selected' : '' }}>
                                            Semester {{ $i }}
                                        </option>
                                    @endfor
                                </select>
                            </div>
                        </div>

                        <!-- Date -->
                        <div class="col-md-4 col-sm-12 mb-3">
                            <div class="form-group">
                                <label class="form-label">Date <span class="text-danger">*</span></label>
                                <input type="date" name="date" class="form-control"
                                    value="{{ \Carbon\Carbon::parse($edit->date)->format('Y-m-d') }}" required>
                            </div>
                        </div>

                        <!-- Series -->
                        <div class="col-md-4 col-sm-12 mb-3">
                            <div class="form-group">
                                <label class="form-label">Series</label>
                                <input type="text" name="series" class="form-control" value="{{ $edit->series }}" placeholder="Enter Series">
                            </div>
                        </div>

                        <!-- Note -->
                        <div class="col-12 mb-3">
                            <div class="form-group">
                                <label class="form-label">Note</label>
                                <textarea name="note" rows="3" class="form-control" placeholder="Enter note...">{{ $edit->note }}</textarea>
                            </div>
                        </div>
                    </div>
                @endif

                <div class="row">
                    <div class="col-md-12 text-center mt-3">
                        <button type="submit" class="btn btn-success me-2">
                            {{ isset($edit) ? 'Update' : 'Submit' }}
                        </button>
                        <a href="{{ route($route . '.index') }}" class="btn btn-danger">Cancel</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('page_leavel_script')
    <script>
        $(document).ready(function() {
            if ($.fn.select2) {
                $('.select2').select2({
                    placeholder: "Select an option",
                    allowClear: true
                });
            }

            @if (!isset($edit))
             function loadFilteredStudents() {
                let courseId = $('#filter_course_id').val();
                let batchId = $('#filter_batch_id').val();
                let classIds = [];
                $('.filter-class-checkbox:checked').each(function() {
                    classIds.push($(this).val());
                });

                if (!courseId || !batchId || classIds.length === 0) {
                    $('#studentTableBody').html('<tr><td colspan="6" class="text-center text-muted">Please select Course, Batch, and Class to load student list.</td></tr>');
                    return;
                }

                $('#studentTableBody').html('<tr><td colspan="6" class="text-center">Loading student list...</td></tr>');

                $.ajax({
                    type: 'GET',
                    url: "{{ route('marksheet-issue.get-students') }}",
                    data: {
                        course_id: courseId,
                        batch_id: batchId,
                        class_id: classIds
                    },
                    success: function(response) {
                        if (response.success) {
                            let tbody = $('#studentTableBody');
                            tbody.empty();

                            if (response.data.length === 0) {
                                tbody.append('<tr><td colspan="6" class="text-center text-muted">No students found.</td></tr>');
                                return;
                            }

                            let commonSemester = $('#common_semester').val() || '';
                            let commonSeries = $('#common_series').val() || '';
                            let commonDate = $('#common_date').val() || '{{ date('Y-m-d') }}';

                            response.data.forEach(function(student, index) {
                                let totalSems = student.semester_count || 10;
                                let semOptions = `<option value="">Semester</option>`;
                                for (let s = 1; s <= totalSems; s++) {
                                    let selected = (commonSemester == s) ? 'selected' : '';
                                    semOptions += `<option value="${s}" ${selected}>Semester ${s}</option>`;
                                }

                                tbody.append(`
                                    <tr>
                                        <td class="text-center">
                                            <input type="checkbox" class="form-check-input student-select" checked>
                                        </td>
                                        <td>
                                            <strong>${student.first_name} ${student.last_name} ${student.father_name}</strong>
                                            <input type="hidden" name="issues[${index}][admission_id]" value="${student.id}" class="student-admission-id">
                                        </td>
                                        <td>
                                            <select name="issues[${index}][semester]" class="form-control student-semester" required>
                                                ${semOptions}
                                            </select>
                                        </td>
                                        <td>
                                            <input type="text" name="issues[${index}][series]" class="form-control student-series" value="${commonSeries}" placeholder="Series">
                                        </td>
                                        <td>
                                            <input type="date" name="issues[${index}][date]" class="form-control student-date" value="${commonDate}" required>
                                        </td>
                                        <td>
                                            <input type="text" name="issues[${index}][note]" class="form-control student-note" value="" placeholder="Note">
                                        </td>
                                    </tr>
                                `);
                            });

                            if ($.fn.select2) {
                                $('.student-semester').select2({
                                    width: '100%'
                                });
                            }

                            toggleRowInputs();
                        }
                    }
                });
            }

            function toggleRowInputs() {
                $('.student-select').each(function() {
                    let isChecked = $(this).is(':checked');
                    let row = $(this).closest('tr');
                    row.find('input:not(.student-select), select').prop('disabled', !isChecked).trigger('change.select2');
                });
            }

            // Sync rows inputs status when select status is checked/unchecked
            $(document).on('change', '.student-select', toggleRowInputs);

            // Master Select All checkbox
            $(document).on('change', '#select_all_students', function() {
                let isChecked = $(this).is(':checked');
                $('.student-select').prop('checked', isChecked);
                toggleRowInputs();
            });

            // Auto-fill common inputs on all rows
            $('#common_semester').on('change', function() {
                let val = $(this).val();
                if (val) {
                    $('.student-semester').val(val).trigger('change');
                }
            });

            $('#common_series').on('input', function() {
                let val = $(this).val();
                $('.student-series').val(val);
            });

            $('#common_date').on('change input', function() {
                let val = $(this).val();
                $('.student-date').val(val);
            });

            // Dependent dropdown: Course -> Batch
            $('#filter_course_id').on('change', function() {
                let courseId = $(this).val();
                let batchSelect = $('#filter_batch_id');

                batchSelect.empty().append('<option value="">Select Batch</option>');
                $('#class_checkboxes_container').html('<span class="text-muted small">Select Batch first</span>');

                batchSelect.trigger('change.select2');

                if (courseId) {
                    $.ajax({
                        type: 'POST',
                        url: "{{ route('get-batch') }}",
                        data: {
                            _token: '{{ csrf_token() }}',
                            course_id: courseId
                        },
                        success: function(response) {
                            if (response.status && response.data) {
                                response.data.forEach(function(batch) {
                                    batchSelect.append(`<option value="${batch.id}">${batch.name}</option>`);
                                });
                                batchSelect.trigger('change.select2');
                            }
                        }
                    });
                }
                loadFilteredStudents();
            });

            // Dependent dropdown: Batch -> Class Checkboxes
            $('#filter_batch_id').on('change', function() {
                let batchId = $(this).val();
                let container = $('#class_checkboxes_container');

                container.html('<span class="text-muted small">Loading classes...</span>');

                if (batchId) {
                    $.ajax({
                        type: 'POST',
                        url: "{{ route('get-class-bybatch') }}",
                        data: {
                            _token: '{{ csrf_token() }}',
                            batch_id: batchId
                        },
                        success: function(response) {
                            if (response.status && response.data) {
                                let checkboxesHtml = '';
                                response.data.forEach(function(cls) {
                                    checkboxesHtml += `
                                        <div class="form-check me-3">
                                            <input class="form-check-input filter-class-checkbox" type="checkbox" value="${cls.id}" id="class_chk_${cls.id}">
                                            <label class="form-check-label" for="class_chk_${cls.id}">
                                                ${cls.name}
                                            </label>
                                        </div>
                                    `;
                                });
                                container.html(checkboxesHtml || '<span class="text-muted small">No classes found</span>');
                            } else {
                                container.html('<span class="text-muted small">No classes found</span>');
                            }
                            loadFilteredStudents();
                        },
                        error: function() {
                            container.html('<span class="text-muted small">Error loading classes</span>');
                            loadFilteredStudents();
                        }
                    });
                } else {
                    container.html('<span class="text-muted small">Select Batch first</span>');
                    loadFilteredStudents();
                }
            });

            // When Class checkboxes change, load students
            $(document).on('change', '.filter-class-checkbox', loadFilteredStudents);

            // Column-wise vertical tab and arrow key navigation
            $(document).on('keydown', '.student-series, .student-date, .student-note', function(e) {
                // Tab = 9, ArrowUp = 38, ArrowDown = 40
                if (e.which === 9 || e.which === 38 || e.which === 40) {
                    let currentInput = $(this);
                    let currentClass = '';
                    
                    if (currentInput.hasClass('student-series')) {
                        currentClass = '.student-series';
                    } else if (currentInput.hasClass('student-date')) {
                        currentClass = '.student-date';
                    } else if (currentInput.hasClass('student-note')) {
                        currentClass = '.student-note';
                    }
                    
                    if (currentClass) {
                        let currentRow = currentInput.closest('tr');
                        let goUp = (e.which === 9 && e.shiftKey) || e.which === 38;
                        let goDown = (e.which === 9 && !e.shiftKey) || e.which === 40;
                        
                        let targetRow = null;
                        if (goUp) {
                            targetRow = currentRow.prev('tr');
                        } else if (goDown) {
                            targetRow = currentRow.next('tr');
                        }
                        
                        if (targetRow && targetRow.length > 0) {
                            let targetInput = targetRow.find(currentClass);
                            if (targetInput.length > 0 && !targetInput.prop('disabled')) {
                                e.preventDefault();
                                targetInput.focus().select();
                            }
                        }
                    }
                }
            });
            @endif
        });
    </script>
@endsection
