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
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="text" class="form-control" id="email" name="email"
                                placeholder="Enter your email" autofocus value="{{ old('email') }}" />
                        </div>
                        <div class="mb-3 form-password-toggle">
                            <label class="form-label" for="password">Password</label>
                            <div class="input-group input-group-merge">
                                <input type="password" id="password" class="form-control" name="password"
                                    placeholder="••••••••••" aria-describedby="password" />
                                <span class="input-group-text cursor-pointer"><i class="ti ti-eye-off"></i></span>
                            </div>
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
@endsection
