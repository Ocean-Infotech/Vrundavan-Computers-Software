@extends('software.layout.app')

@php
    $page_title = isset($modules['title']) ? $modules['title'] : 'Achievement';
    $folder_path = isset($modules['folder_path']) ? $modules['folder_path'] : 'software.module.achievement';
    $route = isset($modules['route']) ? $modules['route'] : 'achievement';
    $permission_prefix = isset($modules['permission_prefix']) ? $modules['permission_prefix'] : 'achievement';
@endphp
@section('title', $page_title)

@section('page_style_file')
    <style>
        .course-badge-checkbox {
            cursor: pointer;
            transition: all 0.2s ease-in-out;
            user-select: none;
        }
        .course-badge-checkbox input[type="checkbox"] {
            cursor: pointer;
        }
        .course-badge-checkbox:hover {
            border-color: #5c1ac3 !important;
            background-color: #f8f5ff !important;
        }
        .section-box {
            background: #ffffff;
            border: 1px solid #e9ecef;
            border-radius: 10px;
            padding: 1.25rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.02);
        }
        .section-title {
            font-size: 1rem;
            font-weight: 700;
            color: #3b3f5c;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .count-badge {
            font-size: 0.8rem;
            padding: 0.25rem 0.6rem;
            border-radius: 20px;
        }
        .search-results-box {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            z-index: 1050;
            background: #ffffff;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
            max-height: 250px;
            overflow-y: auto;
            display: none;
        }
        .search-result-item {
            padding: 10px 15px;
            cursor: pointer;
            border-bottom: 1px solid #f1f3f5;
            transition: background 0.15s ease;
        }
        .search-result-item:hover, .search-result-item.active {
            background: #f4f0ff;
        }
        .selected-student-item {
            background: #f8f9fc;
            border: 1px solid #e3e6f0;
            border-left: 4px solid #5c1ac3;
            border-radius: 6px;
            padding: 10px 14px;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .event-table-container {
            max-height: 400px;
            overflow-y: auto;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
        }
    </style>
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

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card shadow">
        <div class="card-body p-4">
            <form id="achievementForm"
                action="{{ isset($edit) && $edit?->id ? route($route . '.update', [$edit?->id]) : route($route . '.store') }}"
                method="POST">
                @csrf
                @isset($edit)
                    @method('PUT')
                    <input type="hidden" name="id" value="{{ $edit?->id ?? '' }}" />
                @endisset

                <!-- 1. Month, Year Section -->
                <div class="section-box border-primary">
                    <div class="section-title">
                        <span><i class="bx bx-calendar me-1 text-primary"></i> 1. Select Month & Year</span>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6 col-sm-12">
                            <label class="form-label fw-bold">Month <span class="text-danger">*</span></label>
                            <select id="filter_month" name="month" class="form-select select2" required>
                                <option value="">Select Month</option>
                                @foreach ($months as $mNum => $mName)
                                    <option value="{{ $mNum }}" {{ (isset($selectedMonth) && $selectedMonth == $mNum) ? 'selected' : '' }}>
                                        {{ $mName }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6 col-sm-12">
                            <label class="form-label fw-bold">Year <span class="text-danger">*</span></label>
                            <select id="filter_year" name="year" class="form-select select2" required>
                                <option value="">Select Year</option>
                                @foreach ($years as $yr)
                                    <option value="{{ $yr }}" {{ (isset($selectedYear) && $selectedYear == $yr) ? 'selected' : '' }}>
                                        {{ $yr }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Hidden input for Achievement Date -->
                        <input type="hidden" id="achievement_date" name="date" value="{{ isset($edit) && $edit?->date ? \Carbon\Carbon::parse($edit->date)->format('Y-m-d') : date('Y-m-d') }}">
                    </div>
                </div>

                <!-- Notice when Month and Year are not selected -->
                <div id="select_month_year_notice" class="alert alert-primary text-center py-4 my-3 border-dashed" style="{{ (isset($selectedMonth) && isset($selectedYear)) ? 'display: none;' : '' }}">
                    <i class="bx bx-calendar-check fs-2 text-primary d-block mb-2"></i>
                    <h6 class="fw-bold mb-1">Please Select Month and Year First</h6>
                    <p class="text-muted small mb-0">Select Month and Year in Step 1 to proceed with Course selection, Student search, and Awards.</p>
                </div>

                <!-- Main Content (Shows when Month & Year are selected) -->
                <div id="main_content_section" style="{{ (isset($selectedMonth) && isset($selectedYear)) ? '' : 'display: none;' }}">
                    <!-- 2. Course Checkboxes Section -->
                    <div class="section-box">
                        <div class="section-title">
                            <span><i class="bx bx-book-open me-1 text-primary"></i> 2. Select Courses</span>
                            <div>
                                <div class="form-check form-check-inline mb-0">
                                    <input class="form-check-input" type="checkbox" id="select_all_courses">
                                    <label class="form-check-label fw-bold small text-primary" for="select_all_courses">Select All Courses</label>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex flex-wrap gap-2 p-2 border rounded bg-light">
                            @foreach ($courses as $course)
                                <label class="course-badge-checkbox border rounded px-3 py-2 bg-white d-flex align-items-center mb-0" for="course_chk_{{ $course->id }}">
                                    <input class="form-check-input course-checkbox me-2" type="checkbox" name="course_ids[]" value="{{ $course->id }}" id="course_chk_{{ $course->id }}">
                                    <span class="fw-semibold">{{ $course->course_name }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <!-- 3. Search Student Section -->
                    <div class="section-box">
                        <div class="section-title">
                            <span><i class="bx bx-user me-1 text-primary"></i> 3. Search Student</span>
                            <span class="badge bg-primary count-badge" id="student_count_badge">0 Selected</span>
                        </div>

                        <div class="position-relative mb-3">
                            <label class="form-label fw-bold small text-muted">Type Student Name or GR No to Search & Select:</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white"><i class="bx bx-search fs-5 text-primary"></i></span>
                                <input type="text" id="student_search_input" class="form-control form-control-lg" placeholder="Search by GR No or Student Name..." autocomplete="off">
                                <button class="btn btn-outline-secondary" type="button" id="btn_clear_search"><i class="bx bx-x"></i></button>
                            </div>

                            <!-- Dynamic Search Suggestions Dropdown -->
                            <div id="search_results_box" class="search-results-box">
                                <!-- Dynamic items -->
                            </div>
                        </div>

                        <!-- Selected Student(s) Display -->
                        <div id="selected_students_wrapper">
                            <label class="form-label fw-bold small text-dark mb-2">Selected Student(s):</label>
                            <div id="selected_students_list">
                                <div class="alert alert-secondary py-3 text-center mb-0 text-muted" id="no_student_placeholder">
                                    <i class="bx bx-user-x fs-4 d-block mb-1"></i>
                                    No student selected. Please search and select a student above.
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 4. Events / Awards Selection Section -->
                    <div class="section-box" id="awards_section" style="display: none;">
                        <div class="section-title">
                            <span><i class="bx bx-trophy me-1 text-primary"></i> 4. Select Events / Awards</span>
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge bg-success count-badge" id="event_count_badge">0 Selected</span>
                                <div class="form-check mb-0 ms-2">
                                    <input class="form-check-input" type="checkbox" id="select_all_events">
                                    <label class="form-check-label fw-bold small text-success" for="select_all_events">Select All</label>
                                </div>
                            </div>
                        </div>

                        <div class="event-table-container">
                            <table class="table table-hover align-middle mb-0" id="eventTable">
                                <thead class="table-light sticky-top">
                                    <tr>
                                        <th width="5%" class="text-center">#</th>
                                        <th width="45%">Event / Award Name</th>
                                        <th width="20%">Rank</th>
                                        <th width="30%">Remark</th>
                                    </tr>
                                </thead>
                                <tbody id="eventTableBody">
                                    @foreach ($events as $index => $event)
                                        @php
                                            $isEventSelected = false;
                                            $eventRank = '';
                                            $eventRemark = '';

                                            if (isset($existingEventsMap) && isset($existingEventsMap[$event->id])) {
                                                $isEventSelected = true;
                                                $eventRank = $existingEventsMap[$event->id]['rank'] ?? '';
                                                $eventRemark = $existingEventsMap[$event->id]['remark'] ?? '';
                                            } elseif (isset($edit) && ($edit->event_id == $event->id)) {
                                                $isEventSelected = true;
                                                $eventRank = $edit->rank ?? '';
                                                $eventRemark = $edit->remark ?? '';
                                            }
                                        @endphp
                                        <tr class="event-row">
                                            <td class="text-center">
                                                <input type="checkbox" class="form-check-input event-checkbox" 
                                                    name="events[{{ $event->id }}][selected]" value="1" 
                                                    data-event-id="{{ $event->id }}" id="event_chk_{{ $event->id }}"
                                                    {{ $isEventSelected ? 'checked' : '' }}>
                                                <input type="hidden" name="events[{{ $event->id }}][event_id]" value="{{ $event->id }}">
                                            </td>
                                            <td>
                                                <label class="form-check-label fw-semibold cursor-pointer mb-0 fs-6" for="event_chk_{{ $event->id }}">
                                                    {{ $event->name }}
                                                </label>
                                            </td>
                                            <td>
                                                <input type="text" name="events[{{ $event->id }}][rank]" 
                                                    class="form-control form-control-sm event-rank-input" 
                                                    value="{{ $eventRank }}" placeholder="Enter Rank" {{ $isEventSelected ? '' : 'disabled' }}>
                                            </td>
                                            <td>
                                                <input type="text" name="events[{{ $event->id }}][remark]" 
                                                    class="form-control form-control-sm event-remark-input" 
                                                    value="{{ $eventRemark }}" placeholder="Enter Remark" {{ $isEventSelected ? '' : 'disabled' }}>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Summary & Submit Actions -->
                    <div class="p-3 border rounded bg-light text-center mt-2" id="summary_submit_section" style="display: none;">
                        <div class="mb-2">
                            <span class="fw-bold fs-6" id="summary_preview_text">
                                Please check Award(s) to continue.
                            </span>
                        </div>
                        <div class="d-flex justify-content-center gap-2">
                            <button type="submit" class="btn btn-success px-4" id="btnSubmitForm" disabled>
                                <i class="bx bx-check-circle me-1"></i> {{ isset($edit) ? 'Update Achievement' : 'Save Achievements' }}
                            </button>
                            <a href="{{ route($route . '.index') }}" class="btn btn-outline-danger px-4">Cancel</a>
                        </div>
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
                    allowClear: true,
                    width: '100%'
                });
            }

            let allLoadedStudents = [];
            let selectedStudentsMap = {}; // id -> student object
            let isEditMode = {{ isset($edit) ? 'true' : 'false' }};

            @if(isset($preselectedStudent) && !empty($preselectedStudent))
                selectedStudentsMap[{{ $preselectedStudent['id'] }}] = @json($preselectedStudent);
            @endif

            function getSelectedMonthName() {
                let m = $('#filter_month option:selected').text();
                let mVal = $('#filter_month').val();
                return mVal ? m.trim() : '';
            }

            function getSelectedYear() {
                let y = $('#filter_year').val();
                return y ? y.trim() : '';
            }

            function checkMonthYearSelection() {
                let month = $('#filter_month').val();
                let year = $('#filter_year').val();

                if (month && year) {
                    $('#select_month_year_notice').slideUp(200);
                    $('#main_content_section').slideDown(300);
                    
                    // Set achievement_date
                    let paddedMonth = String(month).padStart(2, '0');
                    let currentHiddenDate = $('#achievement_date').val();
                    let day = '01';
                    if (currentHiddenDate) {
                        let parts = currentHiddenDate.split('-');
                        if (parts.length === 3) {
                            day = parts[2];
                        }
                    }
                    let maxDays = new Date(year, month, 0).getDate();
                    if (parseInt(day, 10) > maxDays) day = String(maxDays).padStart(2, '0');
                    $('#achievement_date').val(`${year}-${paddedMonth}-${day}`);
                } else {
                    $('#main_content_section').slideUp(200);
                    $('#select_month_year_notice').slideDown(300);
                }
                updateSummary();
            }

            $('#filter_month, #filter_year').on('change', function() {
                checkMonthYearSelection();
            });

            // Course Checkboxes change -> Load Students
            function getSelectedCourses() {
                let selected = [];
                $('.course-checkbox:checked').each(function() {
                    selected.push($(this).val());
                });
                return selected;
            }

            function loadStudents() {
                let courseIds = getSelectedCourses();

                $.ajax({
                    url: "{{ route('achievement.get-students') }}",
                    type: "GET",
                    data: {
                        course_id: courseIds
                    },
                    success: function(response) {
                        if (response.success) {
                            allLoadedStudents = response.data || [];
                        } else {
                            allLoadedStudents = [];
                        }
                    },
                    error: function() {
                        allLoadedStudents = [];
                    }
                });
            }

            // Initial load of students
            loadStudents();

            // Select All Courses
            $('#select_all_courses').on('change', function() {
                let isChecked = $(this).is(':checked');
                $('.course-checkbox').prop('checked', isChecked);
                loadStudents();
            });

            $('.course-checkbox').on('change', function() {
                let total = $('.course-checkbox').length;
                let checked = $('.course-checkbox:checked').length;
                $('#select_all_courses').prop('checked', total > 0 && total === checked);
                loadStudents();
            });

            // Student Search & Suggestions logic
            $('#student_search_input').on('input', function() {
                let search = $(this).val().toLowerCase().trim();
                let resultsBox = $('#search_results_box');

                if (!search || search.length < 1) {
                    resultsBox.hide().empty();
                    return;
                }

                if (allLoadedStudents.length === 0) {
                    loadStudents();
                }

                let filtered = allLoadedStudents.filter(function(stu) {
                    return (stu.full_name && stu.full_name.toLowerCase().includes(search)) ||
                           (stu.gr_no && stu.gr_no.toString().toLowerCase().includes(search)) ||
                           (stu.course_name && stu.course_name.toLowerCase().includes(search));
                });

                if (filtered.length === 0) {
                    resultsBox.html('<div class="p-3 text-center text-muted small">No students found matching your search.</div>').show();
                    return;
                }

                let slice = filtered.slice(0, 25);
                let html = '';
                slice.forEach(function(stu) {
                    let isAlreadySelected = selectedStudentsMap[stu.id] ? 'style="opacity: 0.5;"' : '';
                    let checkBadge = selectedStudentsMap[stu.id] ? '<span class="badge bg-success ms-2"><i class="bx bx-check"></i> Added</span>' : '';
                    html += `
                        <div class="search-result-item" data-id="${stu.id}" ${isAlreadySelected}>
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <strong class="text-dark">${stu.full_name}</strong>
                                    ${checkBadge}
                                </div>
                            </div>
                        </div>
                    `;
                });

                resultsBox.html(html).show();
            });

            // Click outside search box to hide suggestions
            $(document).on('click', function(e) {
                if (!$(e.target).closest('#student_search_input, #search_results_box').length) {
                    $('#search_results_box').hide();
                }
            });

            // Click on a search suggestion item
            $(document).on('click', '.search-result-item', function() {
                let studentId = $(this).data('id');
                let student = allLoadedStudents.find(s => s.id == studentId);

                if (student) {
                    selectedStudentsMap[student.id] = student;
                    renderSelectedStudents();
                    $('#student_search_input').val('');
                    $('#search_results_box').hide();
                }
            });

            $('#btn_clear_search').on('click', function() {
                $('#student_search_input').val('').focus();
                $('#search_results_box').hide();
            });

            // Remove selected student
            $(document).on('click', '.btn-remove-student', function() {
                let id = $(this).data('id');
                delete selectedStudentsMap[id];
                renderSelectedStudents();
            });

            function renderSelectedStudents() {
                let container = $('#selected_students_list');
                let keys = Object.keys(selectedStudentsMap);

                if (keys.length === 0) {
                    container.html(`
                        <div class="alert alert-secondary py-3 text-center mb-0 text-muted" id="no_student_placeholder">
                            <i class="bx bx-user-x fs-4 d-block mb-1"></i>
                            No student selected. Please search and select a student above.
                        </div>
                    `);
                    $('#student_count_badge').text('0 Selected');
                    $('#awards_section').slideUp(200);
                    $('#summary_submit_section').slideUp(200);
                } else {
                    let html = '';
                    keys.forEach(function(id) {
                        let stu = selectedStudentsMap[id];
                        html += `
                            <div class="selected-student-item">
                                <div class="d-flex align-items-center">
                                    <i class="bx bxs-user-check text-success fs-4 me-2"></i>
                                    <div>
                                        <div class="fw-bold fs-6 text-dark">${stu.full_name}</div>
                                    </div>
                                    <input type="hidden" name="student_ids[]" value="${stu.id}">
                                </div>
                                <div>
                                    <button type="button" class="btn btn-sm btn-outline-danger btn-remove-student" data-id="${stu.id}" title="Remove">
                                        <i class="bx bx-trash me-1"></i> Remove
                                    </button>
                                </div>
                            </div>
                        `;
                    });
                    container.html(html);
                    $('#student_count_badge').text(keys.length + ' Selected');
                    $('#awards_section').slideDown(300);
                    $('#summary_submit_section').slideDown(300);
                }

                updateSummary();
            }

            // Events Checkboxes
            $('#select_all_events').on('change', function() {
                let isChecked = $(this).is(':checked');
                $('.event-checkbox').prop('checked', isChecked).trigger('change');
            });

            $(document).on('change', '.event-checkbox', function() {
                let row = $(this).closest('tr');
                let isChecked = $(this).is(':checked');
                row.find('.event-rank-input, .event-remark-input').prop('disabled', !isChecked);

                let total = $('.event-checkbox').length;
                let checked = $('.event-checkbox:checked').length;
                $('#select_all_events').prop('checked', total > 0 && total === checked);

                $('#event_count_badge').text(checked + ' Selected');
                updateSummary();
            });

            function updateSummary() {
                let stuCount = Object.keys(selectedStudentsMap).length;
                let evtCount = $('.event-checkbox:checked').length;
                let monthName = getSelectedMonthName();
                let year = getSelectedYear();

                if (stuCount > 0 && evtCount > 0 && monthName && year) {
                    let actionText = isEditMode ? 'Updating' : 'Assigning';
                    $('#summary_preview_text').html(
                        `${actionText} <span class="badge bg-success fs-6">${evtCount}</span> Award(s) to <span class="badge bg-primary fs-6">${stuCount}</span> Student(s) for <strong class="text-dark">${monthName} ${year}</strong>`
                    );
                    $('#btnSubmitForm').prop('disabled', false);
                } else {
                    $('#summary_preview_text').text('Please select Month, Year, search & select at least 1 Student, and check at least 1 Award.');
                    $('#btnSubmitForm').prop('disabled', true);
                }
            }

            // Render preselected student & initial state
            renderSelectedStudents();
            checkMonthYearSelection();

            // Trigger initial event count badge
            let initialCheckedEvents = $('.event-checkbox:checked').length;
            $('#event_count_badge').text(initialCheckedEvents + ' Selected');
            let totalEvents = $('.event-checkbox').length;
            if (totalEvents > 0 && totalEvents === initialCheckedEvents) {
                $('#select_all_events').prop('checked', true);
            }
        });
    </script>
@endsection
