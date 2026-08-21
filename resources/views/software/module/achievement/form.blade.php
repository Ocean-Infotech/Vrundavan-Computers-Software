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

                    <!-- Students Grid Table -->
                    <div class="table-responsive my-3">
                        <table class="table table-bordered table-striped align-middle" id="studentGridTable">
                            <thead class="table-light">
                                <tr>
                                    <th width="5%" class="text-center">
                                        <input type="checkbox" id="select_all_students" class="form-check-input" checked>
                                    </th>
                                    <th width="25%">Student Name</th>
                                    <th width="20%">Event <span class="text-danger">*</span></th>
                                    <th width="15%">Rank <span class="text-danger">*</span></th>
                                    <th width="15%">Date <span class="text-danger">*</span></th>
                                    <th width="20%">Remark</th>
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
                        <!-- Student Name Select -->
                        <div class="col-md-6 col-sm-12 mb-3">
                            <div class="form-group">
                                <label class="form-label">Student Name <span class="text-danger">*</span></label>
                                <select name="admission_id" class="form-control select2" required>
                                    <option value="">Select Student</option>
                                    @foreach ($students as $student)
                                        <option value="{{ $student->id }}" {{ $edit->admission_id == $student->id ? 'selected' : '' }}>
                                            {{ trim(($student->first_name ?? '') . ' ' . ($student->last_name ?? '') . ' ' . ($student->father_name ?? '')) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- Event Dropdown -->
                        <div class="col-md-6 col-sm-12 mb-3">
                            <div class="form-group">
                                <label class="form-label">Event <span class="text-danger">*</span></label>
                                <select name="event_id" class="form-control select2" required>
                                    <option value="">Select Event</option>
                                    @foreach ($events as $event)
                                        <option value="{{ $event->id }}" {{ $edit->event_id == $event->id ? 'selected' : '' }}>{{ $event->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- Rank -->
                        <div class="col-md-4 col-sm-12 mb-3">
                            <div class="form-group">
                                <label class="form-label">Rank <span class="text-danger">*</span></label>
                                <input type="text" name="rank" class="form-control" value="{{ $edit->rank }}" placeholder="e.g. 1st, 2nd, Participant" required>
                            </div>
                        </div>

                        <!-- Date -->
                        <div class="col-md-4 col-sm-12 mb-3">
                            <div class="form-group">
                                <label class="form-label">Date <span class="text-danger">*</span></label>
                                <input type="date" name="date" class="form-control" value="{{ \Carbon\Carbon::parse($edit->date)->format('Y-m-d') }}" required>
                            </div>
                        </div>

                        <!-- Remark -->
                        <div class="col-md-4 col-sm-12 mb-3">
                            <div class="form-group">
                                <label class="form-label">Remark</label>
                                <input type="text" name="remark" class="form-control" value="{{ $edit->remark }}" placeholder="Enter Remark">
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
            let eventsData = @json($events);

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
                    url: "{{ route('achievement.get-students') }}",
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

                            let currentDate = '{{ date('Y-m-d') }}';

                            response.data.forEach(function(student, index) {
                                let eventOptions = `<option value="">Select Event</option>`;
                                eventsData.forEach(function(evt) {
                                    eventOptions += `<option value="${evt.id}">${evt.name}</option>`;
                                });

                                tbody.append(`
                                    <tr>
                                        <td class="text-center">
                                            <input type="checkbox" class="form-check-input student-select" checked>
                                        </td>
                                        <td>
                                            <strong>${student.first_name} ${student.last_name} ${student.father_name}</strong>
                                            <input type="hidden" name="achievements[${index}][admission_id]" value="${student.id}" class="student-admission-id">
                                        </td>
                                        <td>
                                            <select name="achievements[${index}][event_id]" class="form-control student-event-id" required>
                                                ${eventOptions}
                                            </select>
                                        </td>
                                        <td>
                                            <input type="text" name="achievements[${index}][rank]" class="form-control student-rank" value="" placeholder="e.g. 1st, Participant" required>
                                        </td>
                                        <td>
                                            <input type="date" name="achievements[${index}][date]" class="form-control student-date" value="${currentDate}" required>
                                        </td>
                                        <td>
                                            <input type="text" name="achievements[${index}][remark]" class="form-control student-remark" value="" placeholder="Remark">
                                        </td>
                                    </tr>
                                `);
                            });

                            if ($.fn.select2) {
                                $('.student-event-id').select2({
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
            @endif
        });
    </script>
@endsection
