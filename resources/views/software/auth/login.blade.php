@extends('software.layout.app')

@section('title', 'Admin Login')

@section('page_style_file')
    <link rel="stylesheet" href="{{ asset('admin/assets/vendor/libs/@form-validation/form-validation.css') }}" />
    <link rel="stylesheet" href="{{ asset('admin/assets/vendor/css/pages/page-auth.css') }}" />
@endsection


@section('content')
    <!-- Content -->
    <div class="authentication-wrapper authentication-cover"
        style="min-height: 100vh; background-image: url('{{ asset('admin/assets/img/illustrations/bg-image.avif') }}'); background-size: cover; background-position: center;">
        <div class="authentication-inner row">
            <!-- /Left Text -->
            {{-- <div class="d-none d-lg-flex col-lg-7 p-0"> --}}
                {{-- <div class="auth-cover-bg auth-cover-bg-color d-flex justify-content-center align-items-center">
                    <img src="{{ asset('admin/assets/img/illustrations/authimage.png') }}" alt="auth-login-cover"
                        class="img-fluid my-5 auth-illustration" />
                    <img src="{{ asset('admin/assets/img/illustrations/bg-shape-image-light.png') }}" alt="auth-login-cover"
                        class="platform-bg" />
                </div> --}}
            {{-- </div> --}}
            <!-- /Left Text -->

            <!-- Login -->
            <!-- Login -->
            <div class="d-flex col-12 col-lg-5 align-items-center justify-content-center p-sm-5 p-4">
                <div class="w-100"
                    style="max-width: 420px; background: rgb(255, 255, 255); border-radius: 15px; padding: 30px;">
                    <!-- Logo -->
                    <div class="app-brand mb-4 text-center">
                        <a href="{{ route('login') }}" class="app-brand-link gap-2">
                            <img class="w-100" src="{{ asset('admin/assets/images/logo-light.png') }}" />
                        </a>
                    </div>

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
                </div>
            </div>
            <!-- /Login -->

            <!-- /Login -->
        </div>
    </div>
    <!-- / Content -->
@endsection


<!-- / Content -->

@section('page_leavel_script')
    <script src="{{ asset('admin/assets/js/pages-auth.js') }}"></script>
@endsection
