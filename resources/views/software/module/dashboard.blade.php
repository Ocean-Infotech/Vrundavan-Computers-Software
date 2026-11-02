@extends('software.layout.app')

@section('content')
    @include('software.partials.flash_messages')

    <div class="row mb-4">
        <!-- Student Birthdays -->
        <div class="col-md-6 mb-3">
            <div class="card shadow-sm border-0 h-100" style="background-color: rgba(115, 103, 240, 0.08) !important; border-left: 5px solid #7367f0 !important;">
                <div class="card-body d-flex align-items-center justify-content-between flex-wrap gap-3">
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-md bg-primary text-white rounded me-3 p-2 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px; font-size: 1.5rem; flex-shrink: 0;">
                            🎉
                        </div>
                        <div>
                            @if($studentBirthdaysCount > 0)
                                <h5 class="mb-1 fw-bold text-primary">Today's Student Birthdays ({{ $studentBirthdaysCount }})</h5>
                                <p class="mb-0 text-muted">
                                    @foreach($studentBirthdays as $birthday)
                                        <span class="fw-bold text-dark">{{ $birthday->first_name }} {{ $birthday->last_name }}</span>{{ !$loop->last ? ', ' : '' }}
                                    @endforeach
                                </p>
                            @else
                                <h5 class="mb-1 fw-bold text-primary">Today's Student Birthdays</h5>
                                <p class="mb-0 text-muted">No student birthdays today 🎂</p>
                            @endif
                        </div>
                    </div>
                    @if($studentBirthdaysCount > 0)
                        <div>
                            <span class="badge bg-primary py-2 px-3 fw-bold">Happy Birthday! 🎂</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Faculty Birthdays -->
        <div class="col-md-6 mb-3">
            <div class="card shadow-sm border-0 h-100" style="background-color: rgba(0, 207, 232, 0.08) !important; border-left: 5px solid #00cfe8 !important;">
                <div class="card-body d-flex align-items-center justify-content-between flex-wrap gap-3">
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-md bg-info text-white rounded me-3 p-2 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px; font-size: 1.5rem; flex-shrink: 0;">
                            🏫
                        </div>
                        <div>
                            @if($facilityBirthdaysCount > 0)
                                <h5 class="mb-1 fw-bold text-info">Today's Faculty Birthdays ({{ $facilityBirthdaysCount }})</h5>
                                <p class="mb-0 text-muted">
                                    @foreach($facilityBirthdays as $birthday)
                                        <span class="fw-bold text-dark">{{ $birthday->name }}</span>{{ !$loop->last ? ', ' : '' }}
                                    @endforeach
                                </p>
                            @else
                                <h5 class="mb-1 fw-bold text-info">Today's Faculty Birthdays</h5>
                                <p class="mb-0 text-muted">No faculty birthdays today 🎂</p>
                            @endif
                        </div>
                    </div>
                    @if($facilityBirthdaysCount > 0)
                        <div>
                            <span class="badge bg-info py-2 px-3 fw-bold text-white">Happy Birthday! 🎂</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Upcoming Birthdays with Month Filter -->
    <div class="row mb-3">
        <div class="col-12">
            <form method="GET" action="{{ url()->current() }}" class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                <h5 class="mb-0 fw-bold">
                    <i class="bx bx-cake me-1 text-primary"></i>
                    {{ !empty($isCurrentBirthdayMonth) ? 'Upcoming' : '' }} Birthdays
                    <span class="text-muted fw-normal">({{ $currentMonthName }})</span>
                </h5>
                <div class="d-flex align-items-center gap-2">
                    <label for="birthday_month" class="mb-0 small text-muted">Month</label>
                    <select name="birthday_month" id="birthday_month" class="form-select form-select-sm" style="min-width: 140px;" onchange="this.form.submit()">
                        @foreach($birthdayMonths as $monthNumber => $monthLabel)
                            <option value="{{ $monthNumber }}" {{ (int) $selectedBirthdayMonth === (int) $monthNumber ? 'selected' : '' }}>
                                {{ $monthLabel }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </form>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-6 mb-3">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-transparent border-bottom d-flex align-items-center justify-content-between">
                    <h5 class="card-title mb-0 fw-bold d-flex align-items-center">
                        <i class="bx bx-cake me-2 text-primary fs-4"></i>
                        Student Birthdays ({{ $currentMonthName }})
                    </h5>
                    <span class="badge bg-label-primary">{{ $upcomingStudentBirthdaysCount }}</span>
                </div>
                <div class="card-body p-0" style="max-height: 280px; overflow-y: auto;">
                    @forelse($upcomingStudentBirthdays as $birthday)
                        <div class="d-flex align-items-center justify-content-between px-3 py-2 {{ !$loop->last ? 'border-bottom' : '' }}">
                            <div class="overflow-hidden">
                                <span class="fw-semibold d-block text-truncate">{{ $birthday->display_name }}</span>
                                <small class="text-muted">{{ $birthday->birthday_date }}</small>
                            </div>
                            <span class="badge {{ $birthday->days_remaining === 0 ? 'bg-primary' : ($birthday->days_remaining === 1 ? 'bg-warning' : ($birthday->days_remaining > 1 ? 'bg-label-secondary' : 'bg-label-secondary')) }} flex-shrink-0 ms-2">
                                {{ $birthday->status_label }}
                            </span>
                        </div>
                    @empty
                        <div class="text-center text-muted py-4 px-3">
                            No student birthdays in {{ $currentMonthName }}
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="col-md-6 mb-3">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-transparent border-bottom d-flex align-items-center justify-content-between">
                    <h5 class="card-title mb-0 fw-bold d-flex align-items-center">
                        <i class="bx bx-cake me-2 text-info fs-4"></i>
                        Faculty Birthdays ({{ $currentMonthName }})
                    </h5>
                    <span class="badge bg-label-info">{{ $upcomingFacilityBirthdaysCount }}</span>
                </div>
                <div class="card-body p-0" style="max-height: 280px; overflow-y: auto;">
                    @forelse($upcomingFacilityBirthdays as $birthday)
                        <div class="d-flex align-items-center justify-content-between px-3 py-2 {{ !$loop->last ? 'border-bottom' : '' }}">
                            <div class="overflow-hidden">
                                <span class="fw-semibold d-block text-truncate">{{ $birthday->name }}</span>
                                <small class="text-muted">{{ $birthday->birthday_date }}</small>
                            </div>
                            <span class="badge {{ $birthday->days_remaining === 0 ? 'bg-info' : ($birthday->days_remaining === 1 ? 'bg-warning' : 'bg-label-secondary') }} flex-shrink-0 ms-2">
                                {{ $birthday->status_label }}
                            </span>
                        </div>
                    @empty
                        <div class="text-center text-muted py-4 px-3">
                            No faculty birthdays in {{ $currentMonthName }}
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Card 1: Total Students -->
        <div class="col-xl-2 col-md-4 col-sm-6 mb-4">
            <div class="card h-100 shadow-sm border-0">
                <div class="card-body d-flex align-items-center">
                    <div class="badge rounded bg-label-primary p-3 me-3" style="background-color: rgba(115, 103, 240, 0.16) !important; width: 60px; height: 60px; display: flex; align-items: center; justify-content: center;">
                        <i class="bx bx-group fs-3 text-primary" style="font-size: 2rem !important;"></i>
                    </div>
                    <div>
                        <span class="text-muted d-block small">Total Students</span>
                        <h4 class="mb-0 fw-bold mt-1">{{ $totalStudents }}</h4>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card 2: Total Courses -->
        <div class="col-xl-2 col-md-4 col-sm-6 mb-4">
            <div class="card h-100 shadow-sm border-0">
                <div class="card-body d-flex align-items-center">
                    <div class="badge rounded bg-label-warning p-3 me-3" style="background-color: rgba(255, 159, 67, 0.16) !important; width: 60px; height: 60px; display: flex; align-items: center; justify-content: center;">
                        <i class="bx bx-book-open fs-3 text-warning" style="font-size: 2rem !important;"></i>
                    </div>
                    <div>
                        <span class="text-muted d-block small">Total Courses</span>
                        <h4 class="mb-0 fw-bold mt-1">{{ $totalCourses }}</h4>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card 3: Student Requests -->
        <div class="col-xl-2 col-md-4 col-sm-6 mb-4">
            <a href="{{ route('student-requests.index') }}" class="text-decoration-none text-dark">
                <div class="card h-100 shadow-sm border-0 card-hover-effect" style="transition: transform 0.2s ease, box-shadow 0.2s ease;">
                    <div class="card-body d-flex align-items-center">
                        <div class="badge rounded bg-label-success p-3 me-3" style="background-color: rgba(40, 199, 111, 0.16) !important; width: 60px; height: 60px; display: flex; align-items: center; justify-content: center;">
                            <i class="bx bx-envelope fs-3 text-success" style="font-size: 2rem !important;"></i>
                        </div>
                        <div>
                            <span class="text-muted d-block small">Student Requests</span>
                            <h4 class="mb-0 fw-bold mt-1">{{ $studentRequestsCount }}</h4>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <!-- Card 4: Today's Tests -->
        <div class="col-xl-3 col-md-6 col-sm-6 mb-4">
            <div class="card h-100 shadow-sm border-0">
                <div class="card-body d-flex align-items-start">
                    <div class="badge rounded bg-label-danger p-3 me-3" style="background-color: rgba(234, 84, 85, 0.16) !important; width: 60px; height: 60px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                        <i class="bx bx-calendar-event fs-3 text-danger" style="font-size: 2rem !important;"></i>
                    </div>
                    <div class="overflow-hidden">
                        <span class="text-muted d-block small mb-1">Today's Tests</span>
                        @forelse($todayTests as $test)
                            <span class="d-block text-truncate fw-bold mb-1 small" style="font-size: 0.85rem;" title="{{ $test->course->course_name ?? '-' }} ({{ $test->subject_name ?? '-' }})">
                                {{ $loop->iteration }} {{ $test->course->course_name ?? '-' }} ({{ $test->subject_name ?? '-' }})
                            </span>
                        @empty
                            <span class="text-muted small">No tests today</span>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <!-- Card 5: Timetables (Single Box listing attachments) -->
        <div class="col-xl-3 col-md-6 col-sm-6 mb-4">
            <div class="card h-100 shadow-sm border-0">
                <div class="card-body d-flex align-items-start">
                    <div class="badge rounded bg-label-info p-3 me-3" style="background-color: rgba(0, 192, 239, 0.16) !important; width: 60px; height: 60px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                        <i class="bx bx-table fs-3 text-info" style="font-size: 2rem !important;"></i>
                    </div>
                    <div class="overflow-hidden">
                        <span class="text-muted d-block small mb-1">Timetables</span>
                        @forelse($timetables as $timetable)
                            <a href="{{ asset($timetable->attachment) }}" target="_blank" class="text-decoration-none text-dark d-block text-truncate fw-bold mb-1 small" style="font-size: 0.85rem;" title="{{ $timetable->course->course_name ?? '-' }}({{ $timetable->batch->batch_name ?? '-' }}) {{ $timetable->class->class ?? '-' }}">
                                <i class="bx bx-link-alt me-1 text-info"></i>{{ $timetable->course->course_name ?? '-' }}({{ $timetable->batch->batch_name ?? '-' }}) {{ $timetable->class->class ?? '-' }}
                            </a>
                        @empty
                            <span class="text-muted small">No active timetables</span>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if ($isFaculty)
        <!-- Tasks Section for Faculty -->
        <div class="row mt-3">
            <div class="col-12">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-transparent border-bottom">
                        <h5 class="card-title mb-0 fw-bold d-flex align-items-center">
                            <i class="bx bx-list-check me-2 text-primary fs-4"></i> My Assigned Tasks
                        </h5>
                    </div>
                    <div class="table-responsive text-nowrap">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>No</th>
                                    <th>Task</th>
                                    <th>Assign Date</th>
                                    <th>Due Date</th>
                                    <th class="text-center">Status</th>
                                    <th>Remarks</th>
                                    <th>Response</th>
                                </tr>
                            </thead>
                            <tbody class="table-border-bottom-0">
                                @forelse($tasks as $task)
                                    @php
                                        $statusText = 'Pending';
                                        $class = 'danger';
                                        if ($task->status == 1) {
                                            $statusText = 'In Progress';
                                            $class = 'warning';
                                        } elseif ($task->status == 2) {
                                            $statusText = 'Completed';
                                            $class = 'success';
                                        } elseif ($task->status == 3) {
                                            $statusText = 'Done';
                                            $class = 'info';
                                        } elseif ($task->status == 4) {
                                            $statusText = 'Repass';
                                            $class = 'secondary';
                                        } elseif ($task->status == 5) {
                                            $statusText = 'Cancelled';
                                            $class = 'dark';
                                        }
                                    @endphp
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td><strong>{{ $task->title }}</strong></td>
                                        <td>{{ $task->date ? \Carbon\Carbon::parse($task->date)->format('d-m-Y h:i A') : '-' }}</td>
                                        <td>{{ $task->due_date ? \Carbon\Carbon::parse($task->due_date)->format('d-m-Y h:i A') : '-' }}</td>
                                        <td class="text-center">
                                            <span class="badge bg-{{ $class }}">{{ $statusText }}</span>
                                        </td>
                                        <td>{{ $task->remarks ?? '-' }}</td>
                                        <td>{{ $task->response ?? '-' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center text-muted py-4">No tasks assigned to you.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endsection
