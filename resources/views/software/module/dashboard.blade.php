@extends('software.layout.app')

@section('content')
    @include('software.partials.flash_messages')

    <div class="row">
        <!-- Card 1: Total Students -->
        <div class="col-md-3 col-sm-6 mb-4">
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
        <div class="col-md-3 col-sm-6 mb-4">
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

        <!-- Card 3: Today's Tests -->
        <div class="col-md-3 col-sm-6 mb-4">
            <div class="card h-100 shadow-sm border-0">
                <div class="card-body d-flex align-items-center">
                    <div class="badge rounded bg-label-danger p-3 me-3" style="background-color: rgba(234, 84, 85, 0.16) !important; width: 60px; height: 60px; display: flex; align-items: center; justify-content: center;">
                        <i class="bx bx-calendar-event fs-3 text-danger" style="font-size: 2rem !important;"></i>
                    </div>
                    <div>
                        <span class="text-muted d-block small">Today's Tests</span>
                        <h4 class="mb-0 fw-bold mt-1">{{ $todayTestsCount }}</h4>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card 4: Timetables (Single Box listing attachments) -->
        <div class="col-md-3 col-sm-6 mb-4">
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
@endsection
