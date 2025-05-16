<!-- Menu -->
@php
$sidebar_active = Route::current()->getName();
$expand = explode('.', $sidebar_active);
$sidebar_active = count($expand) > 0 ? $expand[0] : '';
$loginUser = Auth::user();
@endphp
<aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
    <div class="app-brand demo">
        <a href="{{ route('software.dashboard') }}" class="app-brand-link">

            <img src="{{ asset('admin/assets/images/logo-light.png') }}" class="main-logo" />
            <img src="{{ asset('admin/assets/images/favicon/favicon.png') }}" class="small-logo" />

            {{-- <span class="app-brand-logo demo">
                <svg width="32" height="22" viewBox="0 0 32 22" fill="none"
                xmlns="http://www.w3.org/2000/svg">
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
                        <div data-i18n="users">User</div>
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

        <li class="menu-item {{ $sidebar_active == 'admission' ? 'active open' : '' }}">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon icon-base ri ri-tools-fill ri-bold"></i>
                <div data-i18n="Student">Student</div>
            </a>
            <ul class="menu-sub">
                
                <li class="menu-item {{ $sidebar_active == 'admission' ? 'active' : '' }}">
                    <a href="{{ route('admission.index') }}" class="menu-link">
                        <div data-i18n="Admission">Admission</div>
                    </a>
                </li>
            </ul>
        </li>

        <li class="menu-item {{ $sidebar_active == 'attedance' ? 'active open' : '' }}">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon icon-base ri ri-tools-fill ri-bold"></i>
                <div data-i18n="Attedance">Attedance</div>
            </a>
            <ul class="menu-sub">
                
                <li class="menu-item {{ $sidebar_active == 'attedance' ? 'active' : '' }}">
                    <a href="{{ route('attedance.index') }}" class="menu-link">
                        <div data-i18n="Attedance">Attedance</div>
                    </a>
                </li>
            </ul>
        </li>

        @directCanAny(['application-user-list', 'application-user-create', 'application-user-edit', 'application-user-delete'])
        <!-- <li class="menu-item {{ $sidebar_active == 'application-user' ? 'active' : '' }}">
            <a href="{{ route('application-user.index') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-user"></i>
                <div data-i18n="Application User">Application User</div>
            </a>
        </li> -->
        @enddirectCanAny

        @directCanAny(['redemption-list', 'redemption-create', 'redemption-edit', 'redemption-delete'])
        <!-- <li class="menu-item {{ $sidebar_active == 'redemption' ? 'active' : '' }}">
            <a href="{{ route('redemption.index') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-qr"></i>
                <div data-i18n="Redemption">Redemption</div>
            </a>
        </li> -->
        @enddirectCanAny
        
        @directCanAny(['withdraw-request-list'])
        <!-- <li class="menu-item {{ $sidebar_active == 'withdraw-request' ? 'active' : '' }}">
            <a href="{{ route('withdraw-request.index') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-money-withdraw"></i>
                <div data-i18n="Withdraw Requests">Withdraw Requests</div>
            </a>
        </li> -->
        @enddirectCanAny

        @directCanAny(['sliders-list', 'sliders-create', 'sliders-edit', 'sliders-delete'])
        <!-- <li class="menu-item {{ $sidebar_active == 'sliders' ? 'active' : '' }}">
            <a href="{{ route('sliders.index') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-grid"></i>
                <div data-i18n="Sliders">Sliders</div>
            </a>
        </li> -->
        @enddirectCanAny
        
        @directCanAny(['reports', 'scan-report'])
        <!-- <li class="menu-item {{ $sidebar_active == 'scan-report' ? 'active open' : '' }}">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon icon-base ri ri-funds-box-line ri-bold"></i>
                <div data-i18n="Report's">Report's</div>
            </a>
            <ul class="menu-sub">
                @directCanAny(['scan-report'])
                <li class="menu-item {{ $sidebar_active == 'scanned-report' ? 'active' : '' }}">
                    <a href="{{ route('scanned-report') }}" class="menu-link">
                        <div data-i18n="Scanned Report">Scanned Report</div>
                    </a>
                </li>
                @enddirectCanAny
            </ul>
        </li> -->
        @enddirectCanAny

        @directCanAny('generate-api-documents')
        <!-- <li class="menu-item {{ $sidebar_active == 'generate-api-documents' || $sidebar_active == 'set-point-to-rupees' || $sidebar_active == 'set-min-withdraw-request-amount' ? 'active open' : '' }}">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon icon-base ri ri-tools-fill ri-bold"></i>
                <div data-i18n="Setting's">Setting's</div>
            </a>
            <ul class="menu-sub">
                @directCanAny(['generate-api-documents'])
                <li class="menu-item {{ $sidebar_active == 'set-point-to-rupees' ? 'active' : '' }}">
                    <a href="{{ route('set-point-to-rupees') }}" class="menu-link">
                        <div data-i18n="Set Point = Rupees">Set Point = Rupees</div>
                    </a>
                </li>
                @enddirectCanAny

                <li class="menu-item {{ $sidebar_active == 'set-min-withdraw-request-amount' ? 'active' : '' }}">
                    <a href="{{ route('set-min-withdraw-request-amount') }}"class="menu-link">
                        <div data-i18n="Set Min. Withdraw Requests Amount"> Set Min. Withdraw Requests Amount</div>
                    </a>
                </li>

                @directCanAny(['generate-api-documents'])
                <li class="menu-item {{ $sidebar_active == 'generate-api-documents' ? 'active' : '' }}">
                    <a href="{{ route('generate-swagger-api') }}"class="menu-link">
                        <div data-i18n="Generate API Documents"> Generate API Documents</div>
                    </a>
                </li>
                @enddirectCanAny

            </ul>
        </li> -->
        @enddirectCanAny

        
        <li class="menu-item {{ $sidebar_active == 'course' || $sidebar_active == 'set-point-to-rupees' || $sidebar_active == 'batch' ? 'active open' : '' }}">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon icon-base ri ri-tools-fill ri-bold"></i>
                <div data-i18n="Course">Course</div>
            </a>
            <ul class="menu-sub">
                
                <li class="menu-item {{ $sidebar_active == 'set-point-to-rupees' ? 'active' : '' }}">
                    <a href="{{ route('set-point-to-rupees') }}" class="menu-link">
                        <div data-i18n="Course Register">Course Register</div>
                    </a>
                </li>
                
                <li class="menu-item {{ $sidebar_active == 'course' ? 'active' : '' }}">
                    <a href="{{ route('course.index') }}"class="menu-link">
                        <div data-i18n="Course Master">Course Master</div>
                    </a>
                </li>

                <li class="menu-item {{ $sidebar_active == 'batch' ? 'active' : '' }}">
                    <a href="{{ route('batch.index') }}"class="menu-link">
                        <div data-i18n="Batch Master">Batch Master</div>
                    </a>
                </li>
            </ul>
        </li>
        
        
        <li class="menu-item {{ $sidebar_active == 'test' ? 'active' : '' }}">
            <a href="{{ route('test.index') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-notepad"></i>
                <div data-i18n="Test">Test</div>
            </a>
        </li>
        @directCan('master-module')
        <li class="menu-item {{ $sidebar_active == 'country' || $sidebar_active == 'state' || $sidebar_active == 'city' || $sidebar_active == 'pincode'  || $sidebar_active == 'document-type' || $sidebar_active == 'documents'  ? 'active open' : '' }}">
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
                <!-- <li class="menu-item {{ $sidebar_active == 'pincode' ? 'active' : '' }}">
                    <a href="{{ route('pincode.index') }}" class="menu-link">
                        <div data-i18n="Pincode">Pincode</div>
                    </a>
                </li> -->
                @enddirectCanAny

                @directCanAny(['document-type-list', 'document-type-create', 'document-type-edit', 'document-type-delete'])
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
        </li>
        @enddirectCanAny

    </ul>
    
</aside>
<!-- / Menu -->
