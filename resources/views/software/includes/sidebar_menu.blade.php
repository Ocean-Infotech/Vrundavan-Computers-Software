<!-- Menu -->
@php
    $sidebar_active = Route::current()->getName();
    // dd($sidebar_active);
    $expand = explode('.', $sidebar_active);
    $sidebar_active = count($expand) > 0 ? $expand[0] : '';
    $loginUser = Auth::user();
@endphp


<aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme  ">


    <div class="app-brand demo">
        <a href="{{ route('software.dashboard') }}" class="app-brand-link">

            <img src="{{ asset('admin/assets/images/logo-light.png') }}" class="main-logo" />
            <img src="{{ asset('admin/assets/images/favicon/favicon.png') }}" class="small-logo" />

            {{-- <span class="app-brand-logo demo">
                <svg width="32" height="22" viewBox="0 0 32 22" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path fill-rule="evenodd" clip-rule="evenodd"
                        d="M0.00172773 0V6.85398C0.00172773 6.85398 -0.133178 9.01207 1.98092 10.8388L13.6912 21.9964L19.7809 21.9181L18.8042 9.88248L16.4951 7.17289L9.23799 0H0.00172773Z"
                        fill="#7367F0" />
                    <path opacity="0.06" fill-rule="evenodd" clip-rule="evenodd"
                        d="M7.69824 16.4364L12.5199 3.23696L16.5541 7.25596L7.69824 16.4364Z" fill="#161616" />
                    <path opacity="0.06" fill-rule="evenodd" clip-rule="evenodd"
                        d="M8.07751 15.9175L13.9419 4.63989L16.5849 7.28475L8.07751 15.9175Z" fill="#161616" />
                    <path fill-rule="evenodd" clip-rule="evenodd"
                        d="M7.77295 16.3566L23.6563 0H32V6.88383C32 6.88383 31.8262 9.17836 30.6591 10.4057L19.7824 22H13.6938L7.77295 16.3566Z"
                        fill="#7367F0" />
                </svg>
            </span>
            <span class="app-brand-text demo menu-text fw-bold">Vuexy</span> --}}
        </a>

        <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto">
            <i class="ti menu-toggle-icon d-none d-xl-block ti-sm align-middle"></i>
            <i class="ti ti-x d-block d-xl-none ti-sm align-middle"></i>
        </a>
    </div>

    <div class="menu-inner-shadow"></div>




    <ul class="menu-inner py-1">
        <!-- Dashboards -->

        <li class="menu-item {{ $sidebar_active == 'dashboard' ? 'active' : '' }}">
            <a href="{{ route('software.dashboard') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-home"></i>
                <div data-i18n="Dashboard">Dashboard</div>
            </a>
        </li>


        @directCanAny(['slider-list', 'slider-create', 'slider-edit', 'slider-delete'])
        <li class="menu-item {{ $sidebar_active == 'slider' ? 'active' : '' }}">
            <a href="{{ route('slider.index') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-image"></i>
                <div data-i18n="Slider" class="ms-1">Slider</div>
            </a>
        </li>
        @enddirectCanAny



        @directCan('panel-user-managment')
        <li
            class="menu-item {{ $sidebar_active == 'users' || $sidebar_active == 'roles' || $sidebar_active == 'permissions' ? 'active open' : '' }}">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons bx bx-cog"></i>
                <div data-i18n="User Managment">User Managment</div>
            </a>
            <ul class="menu-sub">
                @directCanAny(['users-list', 'users-create', 'users-edit', 'users-delete'])
                <li class="menu-item {{ $sidebar_active == 'users' ? 'active' : '' }}">
                    <a href="{{ route('users.index') }}" class="menu-link">
                        <div data-i18n="Users">User</div>
                    </a>
                </li>
                @enddirectCanAny

                @directCanAny(['roles-list', 'roles-create', 'roles-edit', 'roles-delete'])
                <li class="menu-item {{ $sidebar_active == 'roles' ? 'active' : '' }}">
                    <a href="{{ route('roles.index') }}" class="menu-link">
                        <div data-i18n="Roles">Roles</div>
                    </a>
                </li>
                @enddirectCanAny

                @directCanAny(['permissions-list', 'permissions-create', 'permissions-edit', 'permissions-delete'])
                <li class="menu-item {{ $sidebar_active == 'permissions' ? 'active' : '' }}">
                    <a href="{{ route('permissions.index') }}" class="menu-link">
                        <div data-i18n="Permissions">Permissions</div>
                    </a>
                </li>
                @enddirectCanAny

            </ul>
        </li>
        @enddirectCan

        @directCanAny(['system-user-list', 'system-user-create', 'system-user-edit', 'system-user-delete'])
        <li class="menu-item {{ $sidebar_active == 'system-user' ? 'active' : '' }}">
            <a href="{{ route('system-user.index') }}" class="menu-link">
                <i class="menu-icon bx bx-user me-2"></i>
                <div data-i18n="System User">System User</div>
            </a>
        </li>
        @enddirectCanAny


        @directCanAny(['admission-list', 'admission-create', 'admission-edit', 'admission-delete', 'admission-import', 'student_report-list'])
        <li
            class="menu-item {{ $sidebar_active == 'admission' || $sidebar_active == 'cource-registration' || $sidebar_active == 'student_report' ? 'active open' : '' }}">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="ti ti-school me-2"></i>
                <div data-i18n="Student">Student</div>
            </a>

            <ul class="menu-sub">

                {{-- New Admission --}}
                @directCan('admission-create')
                <li class="menu-item {{ Route::current()->getName() == 'admission.create' ? 'active' : '' }}">
                    <a href="{{ route('admission.create') }}" class="menu-link">
                        <div data-i18n="New Admission">New Admission</div>
                    </a>
                </li>
                @enddirectCan

                {{-- Registered Students --}}
                @directCan('admission-list')
                <li class="menu-item {{ Route::current()->getName() == 'admission.index' && request()->get('view') !== 'marksheet' ? 'active' : '' }}">
                    <a href="{{ route('admission.index') }}" class="menu-link">
                        <div data-i18n="Registered Student">Registered Student</div>
                    </a>
                </li>
                @enddirectCan

                {{-- Import Student --}}
                @directCan('admission-import')
                <li class="menu-item {{ Route::current()->getName() == 'admission.import' ? 'active' : '' }}">
                    <a href="{{ route('admission.import') }}" class="menu-link">
                        <div data-i18n="Import Student">Import Student</div>
                    </a>
                </li>
                @enddirectCan

                {{-- Student Report --}}
                @directCan('student_report-list')
                <li class="menu-item {{ Route::current()->getName() == 'student_report.index' ? 'active' : '' }}">
                    <a href="{{ route('student_report.index') }}" class="menu-link">
                        <div data-i18n="Student Report">Student Report</div>
                    </a>
                </li>
                @enddirectCan

            </ul>
        </li>
        @enddirectCanAny


        @directCanAny(['attedance-list', 'attedance-create', 'attedance-edit', 'attedance-delete', 'daily-attendance-list', 'daily-attendance-create', 'daily-attendance-edit', 'daily-attendance-delete', 'faculty-attendance-list'])
        <li class="menu-item {{ $sidebar_active == 'attedance' || $sidebar_active == 'attedance-report' || $sidebar_active == 'faculty-attendance' || $sidebar_active == 'daily-attendance' ? 'active open' : '' }}">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="ti ti-calendar me-2"></i>
                <div data-i18n="Attendance">Attendance</div>
            </a>
            <ul class="menu-sub">

                {{-- Create Attendance --}}
                @directCan('attedance-create')
                <li class="menu-item {{ Route::current()->getName() == 'attedance.create' ? 'active' : '' }}">
                    <a href="{{ route('attedance.create') }}" class="menu-link">
                        <div data-i18n="Attendance"> Attendance</div>
                    </a>
                </li>
                @enddirectCan

                {{-- View Attendance --}}
                @directCan('attedance-list')
                <li class="menu-item {{ Route::current()->getName() == 'attedance.index' ? 'active' : '' }}">
                    <a href="{{ route('attedance.index') }}" class="menu-link">
                        <div data-i18n="View Attendance">View Attendance</div>
                    </a>
                </li>
                @enddirectCan

                {{-- Daily Attendance (Students Only) --}}
                @directCan('daily-attendance-list')
                <li class="menu-item {{ Route::current()->getName() == 'daily-attendance.index' ? 'active' : '' }}">
                    <a href="{{ route('daily-attendance.index') }}" class="menu-link">
                        <div data-i18n="Daily Attendance">Daily Attendance</div>
                    </a>
                </li>
                @enddirectCan

                {{-- Attendance Report --}}
                @directCan('attedance-list')
                <li class="menu-item {{ Route::current()->getName() == 'attedance-report.index' ? 'active' : '' }}">
                    <a href="{{ route('attedance-report.index') }}" class="menu-link">
                        <div data-i18n="Attendance Report">Attendance Report</div>
                    </a>
                </li>
                @enddirectCan

                {{-- Faculty Attendance --}}
                @directCan('faculty-attendance-list')
                <li class="menu-item {{ Route::current()->getName() == 'faculty-attendance.index' ? 'active' : '' }}">
                    <a href="{{ route('faculty-attendance.index') }}" class="menu-link">
                        <div data-i18n="Faculty Attendance">Faculty Attendance</div>
                    </a>
                </li>
                @enddirectCan

            </ul>
        </li>

        @enddirectCanAny


        @directCanAny([
            'fees-collection-list',
            'fees-collection-create',
            'fees-collection-edit',
            'fees-collection-delete',
            'fees-collection-pending',
            'fees-collection-report',
            'fee-history-list'
        ])
        <li
            class="menu-item {{ $sidebar_active == 'fees-collection' || $sidebar_active == 'fees-collection-list' || $sidebar_active == 'fees-collection-create' || $sidebar_active == 'fees-collection-pending' || $sidebar_active == 'fee-history' || $sidebar_active == 'fees-pending-report' || $sidebar_active == 'fees-collection-report' ? 'active open' : '' }}">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="ti ti-receipt me-2"></i>
                <div data-i18n="Fees Collection">Fees Collection</div>
            </a>
            <ul class="menu-sub">

                @directCan('fees-collection-create')
                <li class="menu-item {{ Route::current()->getName() == 'fees-collection.create' ? 'active' : '' }}">
                    <a href="{{ route('fees-collection.create') }}" class="menu-link">
                        <div data-i18n="Fees Collection">Fees Collection</div>
                    </a>
                </li>
                @enddirectCan

                @directCan('fees-collection-list')
                <li class="menu-item {{ Route::current()->getName() == 'fees-collection.index' ? 'active' : '' }}">
                    <a href="{{ route('fees-collection.index') }}" class="menu-link">
                        <div data-i18n="Fees Collection Report">Fees Collection Report</div>
                    </a>
                </li>
                @enddirectCan

                @directCan('fees-collection-pending')
                <li
                    class="menu-item {{ Route::current()->getName() == 'fees-collection-pending.index' ? 'active' : '' }}">
                    <a href="{{ route('fees-collection-pending.index') }}" class="menu-link">
                        <div data-i18n="Fees Pending">Fees Pending</div>
                    </a>
                </li>
                @enddirectCan

                @directCan('fee-history-list')
                <li class="menu-item {{ Route::current()->getName() == 'fee-history.index' ? 'active' : '' }}">
                    <a href="{{ route('fee-history.index') }}" class="menu-link">
                        <div data-i18n="Fee History">Fee History</div>
                    </a>
                </li>
                @enddirectCan

                <li class="menu-item {{ Route::current()->getName() == 'fees-pending-report.index' ? 'active' : '' }}">
                    <a href="{{ route('fees-pending-report.index') }}" class="menu-link">
                        <div data-i18n="Fees Pending Report">Fees Pending Report</div>
                    </a>
                </li>

                <li class="menu-item {{ Route::current()->getName() == 'fees-collection-report.index' ? 'active' : '' }}">
                    <a href="{{ route('fees-collection-report.index') }}" class="menu-link">
                        <div data-i18n="Fees Collection Report">Fees Collection Report</div>
                    </a>
                </li>

            </ul>
        </li>
        @enddirectCanAny

        @directCanAny(['issue-certificate-bonafide-list', 'issue-certificate-english-medium-list', 'issue-certificate-letter-recommendation-list', 'issue-certificate-tc-list'])
        <li class="menu-item {{ $sidebar_active == 'issue-certificate' ? 'active open' : '' }}">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="ti ti-certificate me-2"></i>
                <div data-i18n="Issue Certificate">Issue Certificate</div>
            </a>
            <ul class="menu-sub">
                @directCan('issue-certificate-bonafide-list')
                <li class="menu-item {{ Route::is('issue-certificate.bonafide') ? 'active' : '' }}">
                    <a href="{{ route('issue-certificate.bonafide') }}" class="menu-link">
                        <div data-i18n="Bonafide">Bonafide</div>
                    </a>
                </li>
                @enddirectCan

                @directCan('issue-certificate-tc-list')
                <li class="menu-item {{ Route::is('issue-certificate.tc') ? 'active' : '' }}">
                    <a href="{{ route('issue-certificate.tc') }}" class="menu-link">
                        <div data-i18n="Transfer Certificate">Transfer Certificate</div>
                    </a>
                </li>
                @enddirectCan

                @directCan('issue-certificate-english-medium-list')
                <li class="menu-item {{ Route::is('issue-certificate.english-medium') ? 'active' : '' }}">
                    <a href="{{ route('issue-certificate.english-medium') }}" class="menu-link">
                        <div data-i18n="Medium of Instruction">Medium of Instruction</div>
                    </a>
                </li>
                @enddirectCan

                @directCan('issue-certificate-letter-recommendation-list')
                <li class="menu-item {{ Route::is('issue-certificate.letter-recommendation') ? 'active' : '' }}">
                    <a href="{{ route('issue-certificate.letter-recommendation') }}" class="menu-link">
                        <div data-i18n="Letter of Recommendation">Letter of Recommendation</div>
                    </a>
                </li>
                @enddirectCan
            </ul>
        </li>
        @enddirectCanAny

        @directCanAny([
            'faculty-complaint-report-list',
            'faculty-complaint-report-create',
            'marksheet-issue-list',
            'notification-setting-list',
            'notification-setting-create',
            'notification-setting-edit',
            'notification-setting-delete',
            'assessment-list',
            'assignment-list',
            'assignment-create',
            'task-list',
            'achievement-list'
        ])
        <li class="menu-item {{ in_array($sidebar_active, ['faculty-complaint-report', 'marksheet-issue', 'notification_setting', 'assessment', 'assignment', 'combined-report', 'task', 'achievement']) ? 'active open' : '' }}">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="ti ti-messages me-2"></i>
                <div data-i18n="Communication">Communication</div>
            </a>
            <ul class="menu-sub">
                @directCan('faculty-complaint-report-list')
                <li class="menu-item {{ $sidebar_active == 'faculty-complaint-report' ? 'active' : '' }}">
                    <a href="{{ route('faculty-complaint-report.index') }}" class="menu-link">
                        <div data-i18n="Faculty Complaint Report">Faculty Complaint Report</div>
                    </a>
                </li>
                @enddirectCan
 
                {{-- Marksheet Issue --}}
                @directCan('marksheet-issue-list')
                <li class="menu-item {{ $sidebar_active == 'marksheet-issue' ? 'active' : '' }}">
                    <a href="{{ route('marksheet-issue.index') }}" class="menu-link">
                        <div data-i18n="Marksheet Issue">Marksheet Issue</div>
                    </a>
                </li>
                @enddirectCan
 
                {{-- Notification Settings --}}
                @directCanAny([
                    'notification-setting-list',
                    'notification-setting-create',
                    'notification-setting-edit',
                    'notification-setting-delete'
                ])
                <li class="menu-item {{ $sidebar_active == 'notification_setting' ? 'active' : '' }}">
                    <a href="{{ route('notification_setting.index') }}" class="menu-link">
                        <div data-i18n="Announcement">Announcement</div>
                    </a>
                </li>
                @enddirectCanAny

                
                {{-- Assessment --}}
                @directCan('assessment-list')
                <li class="menu-item {{ $sidebar_active == 'assessment' ? 'active' : '' }}">
                    <a href="{{ route('assessment.index') }}" class="menu-link">
                        <div data-i18n="Assessment">Assessment</div>
                    </a>
                </li>
                @enddirectCan
                @directCan('assignment-list')
                <li class="menu-item {{ $sidebar_active == 'assignment' ? 'active' : '' }}">
                    <a href="{{ route('assignment.index') }}" class="menu-link">
                        <div data-i18n="Assignment">Assignment</div>
                    </a>
                </li>
                @enddirectCan
                @directCan('task-list')
                <li class="menu-item {{ $sidebar_active == 'task' ? 'active' : '' }}">
                    <a href="{{ route('task.index') }}" class="menu-link">
                        <div data-i18n="Task Management">Task Management</div>
                    </a>
                </li>
                @enddirectCan

                {{-- Achievement --}}
                @directCan('achievement-create')
                <li class="menu-item {{ Route::current()->getName() == 'achievement.create' ? 'active' : '' }}">
                    <a href="{{ route('achievement.create') }}" class="menu-link">
                        <div data-i18n="Add Achievement">Add Achievement</div>
                    </a>
                </li>
                @enddirectCan
                @directCan('achievement-list')
                <li class="menu-item {{ in_array(Route::current()->getName(), ['achievement.index', 'achievement.edit']) ? 'active' : '' }}">
                    <a href="{{ route('achievement.index') }}" class="menu-link">
                        <div data-i18n="View Achievement">View Achievement</div>
                    </a>
                </li>
                @enddirectCan
            </ul>
        </li>
        @enddirectCanAny

        @directCanAny(['test-list', 'test_report-list'])
        <li class="menu-item {{ $sidebar_active == 'test' || $sidebar_active == 'test_report' ? 'active open' : '' }}">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="bx bx-notepad me-2"></i>
                <div data-i18n="Test">Test</div>
            </a>
            <ul class="menu-sub">
                @directCanAny(['test-list', 'test-create', 'test-edit', 'test-delete'])
                <li class="menu-item {{ $sidebar_active == 'test' ? 'active' : '' }}">
                    <a href="{{ route('test.index') }}" class="menu-link">
                        <div data-i18n="Test">Test</div>
                    </a>
                </li>
                @enddirectCanAny

                @directCanAny(['test_report-list', 'test_report-create', 'test_report-edit', 'test_report-delete'])
                <li class="menu-item {{ $sidebar_active == 'test_report' ? 'active' : '' }}">
                    <a href="{{ route('test_report.index') }}" class="menu-link">
                        <div data-i18n="Test Report">Test Report</div>
                    </a>
                </li>
                @enddirectCanAny
            </ul>
        </li>
        @enddirectCanAny



        <li class="menu-item {{ in_array(Route::current()->getName(), ['result.index', 'result.create', 'result.edit', 'result.entry.index', 'result.entry.direct', 'result.report.index']) ? 'active open' : '' }}">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons bx bx-bar-chart-alt-2 me-2"></i>
                <div>Result</div>
            </a>
            <ul class="menu-sub">
                <li class="menu-item {{ in_array(Route::current()->getName(), ['result.index', 'result.create', 'result.edit', 'result.entry.index', 'result.entry.direct']) ? 'active' : '' }}">
                    <a href="{{ route('result.index') }}" class="menu-link">
                        <div>Result</div>
                    </a>
                </li>
                <li class="menu-item {{ Route::current()->getName() == 'result.report.index' ? 'active' : '' }}">
                    <a href="{{ route('result.report.index') }}" class="menu-link">
                        <div>Student Results</div>
                    </a>
                </li>
            </ul>
        </li>



        @directCanAny([
            'course-list',
            'batch-list',
            'class-list',
            'shift-list',
            'syllabus-list',
            'subject-list',
            'event-list',
            'holiday-list',
            'notification-setting-list',
            'timetable-list',
            'student-requests-list',
            'fees-list',
            'department-list'
        ])
        <li
            class="menu-item {{ in_array($sidebar_active, ['course', 'shift', 'class', 'syllabus', 'batch', 'subject', 'event', 'holiday', 'timetable', 'student-requests', 'fees', 'department', 'university']) ? 'active open' : '' }}">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="ti ti-certificate me-2"></i>
                <div data-i18n="Course Masters">Course Masters</div>
            </a>
            <ul class="menu-sub">
                @directCanAny(['course-list', 'course-create', 'course-edit', 'course-delete'])
                <li class="menu-item {{ $sidebar_active == 'course' ? 'active' : '' }}">
                    <a href="{{ route('course.index') }}" class="menu-link">
                        <div data-i18n="Course Master">Course Master</div>
                    </a>
                </li>
                @enddirectCanAny



                {{-- Batch --}}
                @directCanAny(['batch-list', 'batch-create', 'batch-edit', 'batch-delete'])
                <li class="menu-item {{ $sidebar_active == 'batch' ? 'active' : '' }}">
                    <a href="{{ route('batch.index') }}" class="menu-link">
                        <div data-i18n="Batch Master">Batch Master</div>
                    </a>
                </li>
                @enddirectCanAny

                {{-- Class --}}
                @directCanAny(['class-list', 'class-create', 'class-edit', 'class-delete'])
                <li class="menu-item {{ $sidebar_active == 'class' ? 'active' : '' }}">
                    <a href="{{ route('class.index') }}" class="menu-link">
                        <div data-i18n="Class Master">Class Master</div>
                    </a>
                </li>
                @enddirectCanAny

                {{-- Shift --}}
                @directCanAny(['shift-list', 'shift-create', 'shift-edit', 'shift-delete'])
                <li class="menu-item {{ $sidebar_active == 'shift' ? 'active' : '' }}">
                    <a href="{{ route('shift.index') }}" class="menu-link">
                        <div data-i18n="Shift Master">Shift Master</div>
                    </a>
                </li>
                @enddirectCanAny

                @directCanAny(['department-list', 'department-create', 'department-edit', 'department-delete'])
                <li class="menu-item {{ $sidebar_active == 'department' ? 'active' : '' }}">
                    <a href="{{ route('department.index') }}" class="menu-link">
                        <i class="menu-icon tf-icons bx bx-buildings me-2"></i>
                        <div data-i18n="Department Master">Department Master</div>
                    </a>
                </li>
                @enddirectCanAny

                @directCanAny(['university-list', 'university-create', 'university-edit', 'university-delete'])
                <li class="menu-item {{ $sidebar_active == 'university' ? 'active' : '' }}">
                    <a href="{{ route('university.index') }}" class="menu-link">
                        <i class="menu-icon tf-icons bx bxs-school me-2"></i>
                        <div data-i18n="University Master">University Master</div>
                    </a>
                </li>
                @enddirectCanAny


                {{-- Syllabus --}}
                @directCanAny(['syllabus-list', 'syllabus-create', 'syllabus-edit', 'syllabus-delete'])
                <li class="menu-item {{ $sidebar_active == 'syllabus' ? 'active' : '' }}">
                    <a href="{{ route('syllabus.index') }}" class="menu-link">
                        <div data-i18n="Syllabus">Syllabus</div>
                    </a>
                </li>
                @enddirectCanAny

                {{-- Subject Master (NEW) --}}
                @directCanAny(['subject-list', 'subject-create', 'subject-edit', 'subject-delete'])
                <li class="menu-item {{ $sidebar_active == 'subject' ? 'active' : '' }}">
                    <a href="{{ route('subject.index') }}" class="menu-link">
                        <div data-i18n="Request Subject">Request Subject</div>
                    </a>
                </li>
                @enddirectCanAny

                {{-- Event Master --}}
                @directCanAny(['event-list', 'event-create', 'event-edit', 'event-delete'])
                <li class="menu-item {{ $sidebar_active == 'event' ? 'active' : '' }}">
                    <a href="{{ route('event.index') }}" class="menu-link">
                        <div data-i18n="Event Master">Event Master</div>
                    </a>
                </li>
                @enddirectCanAny

                {{-- Holiday Master --}}
                @directCanAny(['holiday-list', 'holiday-create', 'holiday-edit', 'holiday-delete'])
                <li class="menu-item {{ $sidebar_active == 'holiday' ? 'active' : '' }}">
                    <a href="{{ route('holiday.index') }}" class="menu-link">
                        <div data-i18n="Holiday Master">Holiday Master</div>
                    </a>
                </li>
                @enddirectCanAny

                {{-- Student Requests --}}
                @directCanAny(['student-requests-list'])
                <li class="menu-item {{ $sidebar_active == 'student-requests' ? 'active' : '' }}">
                    <a href="{{ route('student-requests.index') }}" class="menu-link">
                        <div data-i18n="Student Requests List">Student Requests List</div>
                    </a>
                </li>
                @enddirectCanAny


                @directCanAny(['timetable-list', 'timetable-create', 'timetable-edit', 'timetable-delete'])
                <li class="menu-item {{ $sidebar_active == 'timetable' ? 'active' : '' }}">
                    <a href="{{ route('timetable.index') }}" class="menu-link">
                        <div data-i18n="Timetable">Timetable</div>
                    </a>
                </li>
                @enddirectCanAny

                @directCanAny(['fees-list', 'fees-create', 'fees-edit', 'fees-delete'])
                <li class="menu-item {{ $sidebar_active == 'fees' ? 'active' : '' }}">
                    <a href="{{ route('fees.index') }}" class="menu-link">
                        <div data-i18n="Fee Details">Fee Details</div>
                    </a>
                </li>
                @enddirectCanAny
            </ul>
        </li>
        @enddirectCanAny






        @directCanAny([
            'library-book-master-list',
            'library-book-master-create',
            'library-book-master-edit',
            'library-book-master-delete',
            'issued-book-list',
            'issued-book-create',
            'issued-book-edit',
            'issued-book-delete'
        ])
        <li
            class="menu-item {{ in_array($sidebar_active, ['library-book-master', 'issued-book']) ? 'active open' : '' }}">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons bx bx-library me-2"></i>
                <div data-i18n="Library">Library</div>
            </a>
            <ul class="menu-sub">

                {{-- Library Book Master --}}
                @directCanAny([
                    'library-book-master-list',
                    'library-book-master-create',
                    'library-book-master-edit',
                    'library-book-master-delete'
                ])
                <li class="menu-item {{ $sidebar_active == 'library-book-master' ? 'active' : '' }}">
                    <a href="{{ route('library-book-master.index') }}" class="menu-link">
                        <i class="menu-icon tf-icons bx bx-book-open me-2"></i>
                        <div data-i18n="Book Master">Book Master</div>
                    </a>
                </li>
                @enddirectCanAny

                {{-- Issue Book (Create Page) --}}
                @directCan('issued-book-create')
                <li class="menu-item {{ Route::is('issued-book.create') ? 'active' : '' }}">
                    <a href="{{ route('issued-book.create') }}" class="menu-link">
                        <i class="menu-icon tf-icons bx bx-book-add me-2"></i>
                        <div data-i18n="Issue Book">Issue Book</div>
                    </a>
                </li>
                @enddirectCan

                {{-- Issued Book List --}}
                @directCanAny(['issued-book-list', 'issued-book-edit', 'issued-book-delete'])
                <li class="menu-item {{ Route::is('issued-book.index') ? 'active' : '' }}">
                    <a href="{{ route('issued-book.index') }}" class="menu-link">
                        <i class="menu-icon tf-icons bx bx-book-bookmark me-2"></i>
                        <div data-i18n="Issued Book">Issued Book</div>
                    </a>
                </li>
                @enddirectCanAny

            </ul>
        </li>
        @enddirectCanAny

        @directCanAny(['other-list-selection-list', 'supplier-list'])
        <li class="menu-item {{ in_array($sidebar_active, ['other-list', 'other-list-selection', 'supplier', 'combined-report']) ? 'active open' : '' }}">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons bx bx-list-ul me-2"></i>
                <div data-i18n="Other">Other</div>
            </a>
            <ul class="menu-sub">
                {{-- Other List menu hidden --}}
                {{-- @directCan('other-list-list')
                <li class="menu-item {{ Route::is('other-list.index') ? 'active' : '' }}">
                    <a href="{{ route('other-list.index') }}" class="menu-link">
                        <div data-i18n="Other List">Other List</div>
                    </a>
                </li>
                @enddirectCan --}}

                @directCan('other-list-selection-list')
                <li class="menu-item {{ Route::is('other-list-selection.index') ? 'active' : '' }}">
                    <a href="{{ route('other-list-selection.index') }}" class="menu-link">
                        <div data-i18n="Other List Selection">Other List Selection</div>
                    </a>
                </li>
                @enddirectCan

                @directCan('supplier-list')
                <li class="menu-item {{ $sidebar_active == 'supplier' ? 'active' : '' }}">
                    <a href="{{ route('supplier.index') }}" class="menu-link">
                        <div data-i18n="Supplier Contact">Supplier Contact</div>
                    </a>
                </li>
                @enddirectCan

                <li class="menu-item {{ $sidebar_active == 'combined-report' ? 'active' : '' }}">
                    <a href="{{ route('combined-report.index') }}" class="menu-link">
                        <div data-i18n="Combined Report">Combined Report</div>
                    </a>
                </li>
            </ul>
        </li>
        @enddirectCanAny


        @if(method_exists($loginUser, 'hasRole') && ($loginUser->hasRole('super-admin') || $loginUser->hasRole('developer')))
            <li class="menu-item {{ $sidebar_active == 'device-shield' ? 'active open' : '' }}">
                <a href="javascript:void(0);" class="menu-link menu-toggle">
                    <i class="menu-icon tf-icons bx bx-shield-quarter me-2"></i>
                    <div data-i18n="Device Shield">Device Shield</div>
                </a>
                <ul class="menu-sub">
                    <li class="menu-item {{ Route::is('device-shield.dashboard') ? 'active' : '' }}">
                        <a href="{{ route('device-shield.dashboard') }}" class="menu-link">
                            <div data-i18n="Dashboard">Dashboard</div>
                        </a>
                    </li>
                    <li class="menu-item {{ Route::is('device-shield.index') ? 'active' : '' }}">
                        <a href="{{ route('device-shield.index') }}" class="menu-link">
                            <div data-i18n="Devices & Enforcement">Devices & Enforcement</div>
                        </a>
                    </li>
                    <li class="menu-item {{ Route::is('device-shield.logs') ? 'active' : '' }}">
                        <a href="{{ route('device-shield.logs') }}" class="menu-link">
                            <div data-i18n="Security Logs">Security Logs</div>
                        </a>
                    </li>
                </ul>
            </li>
        @endif


        @directCanAny(['biomax-list'])
        <li class="menu-item {{ $sidebar_active == 'Biomax' ? 'active' : '' }}">
            <a href="{{ route('biomax.status') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-home me-2"></i>
                <div data-i18n="Biomax">Biomax</div>
            </a>
        </li>
        @enddirectCanAny

        @directCan('master-module')
        {{-- <li
            class="menu-item {{ $sidebar_active == 'country' || $sidebar_active == 'state' || $sidebar_active == 'city' || $sidebar_active == 'pincode' || $sidebar_active == 'document-type' || $sidebar_active == 'documents' ? 'active open' : '' }}">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon icon-base bx bx-cog"></i>
                <div data-i18n="Master Modules">Master Modules</div>
            </a>
            <ul class="menu-sub">

                @directCanAny(['country-list', 'country-create', 'country-edit', 'country-delete'])
                <li class="menu-item {{ $sidebar_active == 'country' ? 'active' : '' }}">
                    <a href="{{ route('country.index') }}" class="menu-link">
                        <div data-i18n="Country">Country</div>
                    </a>
                </li>
                @enddirectCanAny

                @directCanAny(['state-list', 'state-create', 'state-edit', 'state-delete'])
                <li class="menu-item {{ $sidebar_active == 'state' ? 'active' : '' }}">
                    <a href="{{ route('state.index') }}" class="menu-link">
                        <div data-i18n="State">State</div>
                    </a>
                </li>
                @enddirectCanAny

                @directCanAny(['city-list', 'city-create', 'city-edit', 'city-delete'])
                <li class="menu-item {{ $sidebar_active == 'city' ? 'active' : '' }}">
                    <a href="{{ route('city.index') }}" class="menu-link">
                        <div data-i18n="City">City</div>
                    </a>
                </li>
                @enddirectCanAny

                @directCanAny(['pincode-list', 'pincode-create', 'pincode-edit', 'pincode-delete'])
                <li class="menu-item {{ $sidebar_active == 'pincode' ? 'active' : '' }}">
                    <a href="{{ route('pincode.index') }}" class="menu-link">
                        <div data-i18n="Pincode">Pincode</div>
                    </a>
                </li>
                @enddirectCanAny

                @directCanAny(['document-type-list', 'document-type-create', 'document-type-edit',
                'document-type-delete'])
                <li class="menu-item {{ $sidebar_active == 'document-type' ? 'active' : '' }}">
                    <a href="{{ route('document-type.index') }}" class="menu-link">
                        <div data-i18n="Document Type">Document Type</div>
                    </a>
                </li>
                @enddirectCanAny

                @directCanAny(['documents-list', 'documents-create', 'documents-edit', 'documents-delete'])
                <li class="menu-item {{ $sidebar_active == 'documents' ? 'active' : '' }}">
                    <a href="{{ route('documents.index') }}" class="menu-link">
                        <div data-i18n="Document's">Document's</div>
                    </a>
                </li>
                @enddirectCanAny
            </ul>
        </li> --}}
        @enddirectCanAny



</aside>
<!-- / Menu -->