@extends('software.layout.app')

@section('title', 'Admin Login')

@section('page_style_file')
    <link rel="stylesheet" href="{{ asset('admin/assets/vendor/libs/@form-validation/form-validation.css') }}" />
    <link rel="stylesheet" href="{{ asset('admin/assets/vendor/css/pages/page-auth.css') }}" />

    <!-- AOS Animation CSS -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet" />
@endsection

@section('content')
    <!-- Content -->
    <div class="authentication-wrapper authentication-cover position-relative"
        style="min-height: 100vh; background-image: url('{{ asset('admin/assets/img/illustrations/vector-privacy-protection-and-software-for-development.jpg') }}'); background-size: cover; background-position: center;">

        <!-- Dark Overlay -->
        <div style="position:absolute; top:0; left:0; right:0; bottom:0; background:rgba(0,0,0,0.5); z-index:1;"></div>

        <div class="authentication-inner row" style="position:relative; z-index:2;">
            <!-- Login -->
            <div class="d-flex col-12 col-lg-5 align-items-center justify-content-center p-sm-5 p-4">
                <div class="w-100"
                    style="max-width: 420px; background: rgb(255 255 255); border-radius: 15px; padding: 30px;"
                    data-aos="zoom-in" data-aos-duration="1000" data-aos-delay="200">

                    <!-- Logo -->
                    <div class="app-brand mb-4 text-center">
                        <a href="{{ route('login') }}" class="app-brand-link gap-2">
                            <img class="w-100" src="{{ asset('admin/assets/images/logo-light.png') }}" />
                        </a>
                    </div>

                    <!-- Login Form -->
                    <form id="formAuthentication" class="mb-3" action="{{ route('login') }}" method="post">
                        @csrf
                        <input type="hidden" name="hardware_id" id="hardware_id">
                        <input type="hidden" name="motherboard_serial" id="motherboard_serial">
                        <input type="hidden" name="bios_serial" id="bios_serial">
                        <input type="hidden" name="cpu_id" id="cpu_id">
                        <input type="hidden" name="timestamp" id="timestamp">
                        <input type="hidden" name="nonce" id="nonce">
                        <input type="hidden" name="signature" id="signature">
                        <input type="hidden" name="pc_name" id="pc_name">
                        <input type="hidden" name="latitude" id="latitude">
                        <input type="hidden" name="longitude" id="longitude">

                        @if ($errors->any())
                            <div class="alert alert-danger p-2 mb-3" style="font-size: 0.85rem;">
                                <ul class="mb-0 ps-3">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="text" class="form-control" id="email" name="email" placeholder="Enter your email"
                                autofocus value="{{ old('email') }}" />
                        </div>
                        <div class="mb-3 form-password-toggle">
                            <label class="form-label" for="password">Password</label>
                            <div class="input-group input-group-merge">
                                <input type="password" id="password" class="form-control" name="password"
                                    placeholder="••••••••••" aria-describedby="password" />
                                <span class="input-group-text cursor-pointer"><i class="ti ti-eye-off"></i></span>
                            </div>
                        </div>

                        <!-- Shield Status Indicator -->
                        <div id="shield-status-container" class="mb-3 p-2 rounded text-center"
                            style="font-size: 0.85rem; display: none;">
                            <span id="shield-status-text">Detecting Vrundavan Shield...</span>
                        </div>

                        <button class="btn btn-primary d-grid w-100">Sign in</button>
                    </form>

                    <div class="text-center mt-3">
                        <a href="{{ route('software.privacy-policy') }}" target="_blank" class="">
                            Privacy Policy
                        </a>
                    </div>
                </div>
            </div>
            <!-- /Login -->
        </div>
    </div>
    <!-- / Content -->
@endsection

@section('page_leavel_script')
    <script src="{{ asset('admin/assets/js/pages-auth.js') }}"></script>

    <!-- AOS JS -->
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        AOS.init();
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const statusContainer = document.getElementById('shield-status-container');
            const statusText = document.getElementById('shield-status-text');
            const submitBtn = document.querySelector('#formAuthentication button[type="submit"]') || document.querySelector('#formAuthentication button');

            if (statusContainer) {
                statusContainer.style.display = 'block';
                statusContainer.style.backgroundColor = '#f8f9fa';
                statusContainer.style.color = '#6c757d';
                statusContainer.style.border = '1px solid #dee2e6';
            }

            function checkShieldAgent() {
                fetch('http://127.0.0.1:9988/hardware')
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Agent response not ok');
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data && data.hardware_id && data.signature) {
                            document.getElementById('hardware_id').value = data.hardware_id;
                            document.getElementById('motherboard_serial').value = data.motherboard_serial;
                            document.getElementById('bios_serial').value = data.bios_serial;
                            document.getElementById('cpu_id').value = data.cpu_id;
                            document.getElementById('timestamp').value = data.timestamp;
                            document.getElementById('nonce').value = data.nonce;
                            document.getElementById('signature').value = data.signature;
                            document.getElementById('pc_name').value = data.pc_name || '';
                            // Try to get highly accurate location from the browser first
                            if (navigator.geolocation) {
                                navigator.geolocation.getCurrentPosition(
                                    (position) => {
                                        document.getElementById('latitude').value = position.coords.latitude;
                                        document.getElementById('longitude').value = position.coords.longitude;
                                        console.log('Browser high-accuracy Geolocation captured:', position.coords.latitude, position.coords.longitude);
                                    },
                                    (error) => {
                                        console.warn('Browser Geolocation failed or denied. Falling back to IP-based location:', error.message);
                                        document.getElementById('latitude').value = data.latitude || '';
                                        document.getElementById('longitude').value = data.longitude || '';
                                    },
                                    { enableHighAccuracy: true, timeout: 5000, maximumAge: 0 }
                                );
                            } else {
                                document.getElementById('latitude').value = data.latitude || '';
                                document.getElementById('longitude').value = data.longitude || '';
                            }

                            statusContainer.style.backgroundColor = '#d4edda';
                            statusContainer.style.color = '#155724';
                            statusContainer.style.border = '1px solid #c3e6cb';
                            statusText.innerHTML = '<i class="ti ti-shield-check me-1"></i> Vrundavan Shield Active';
                            if (submitBtn) submitBtn.disabled = false;
                        } else {
                            throw new Error('Invalid agent payload');
                        }
                    })
                    .catch(error => {
                        console.log('Shield Agent is not running on this PC.');
                        statusContainer.style.backgroundColor = '#fff3cd';
                        statusContainer.style.color = '#856404';
                        statusContainer.style.border = '1px solid #ffeeba';
                        statusText.innerHTML = '<i class="ti ti-shield-off me-1"></i> Vrundavan Shield: Agent Offline (Only required for lock-enforced employees)';
                    });
            }

            checkShieldAgent();
        });
    </script>
@endsection