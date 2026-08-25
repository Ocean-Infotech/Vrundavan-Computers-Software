@extends('software.layout.app')

@php
    $page_title = isset($modules['title']) ? $modules['title'] : null;
    $folder_path = isset($modules['folder_path']) ? $modules['folder_path'] : null;
    $route = isset($modules['route']) ? $modules['route'] : null;
    $permisstion_prefix = isset($modules['permisstion_prefix']) ? $modules['permisstion_prefix'] : null;
    $i = 0;
@endphp
@section('title', $page_title)

@section('page_style_file')
    {{-- <link href="{{ asset('public/assets/admin/plugins/datatable/dataTables.bootstrap4.min.css') }}" rel="stylesheet" type="text/css"> --}}
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

    <!-- @include('software.partials.flash_messages') -->

    <div class="card shadow my-3">
        <div class="card-header d-none">
            <h2 class="mb-0">
                {{ $page_title }}
            </h2>
        </div>
        <div class="card-body my-4">
            <form
                action="{{ isset($edit) && $edit?->id ? route($route . '.update', [$edit?->id]) : route($route . '.store') }}"
                method="POST" enctype="multipart/form-data">
                @csrf
                @isset($edit)
                    @method('PUT')
                @endisset
                <div class="row">
                    <div class="col-md-4 col-sm-12 mb-2">
                        <div class="form-group">
                            <label class="form-label"> Name <span class="text-danger">*</span> </label>
                            <input id="name" type="text" class="form-control @error('name') is-invalid @enderror"
                                name="name" value="{{ isset($edit?->name) ? $edit?->name : old('name') }}"
                                autocomplete="name" autofocus placeholder="Enter name">

                            @error('name')
                                <span class="invalid-feedback">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-4 col-sm-12 mb-2">
                        <div class="form-group">
                            <label class="form-label"> Email <span class="text-danger">*</span> </label>
                            <input id="email" type="email" class="form-control @error('email') is-invalid @enderror"
                                name="email" value="{{ isset($edit->email) ? $edit->email : old('email') }}" required
                                autocomplete="email" placeholder="Enter valid email..">

                            @error('email')
                                <span class="invalid-feedback">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-4 col-sm-12 mb-2">
                        <div class="form-group">
                            <label class="form-label"> Phone <span class="text-danger">*</span></label>
                            <input id="phone" type="text" class="num_only form-control @error('phone') is-invalid @enderror"
                                name="phone" value="{{ isset($edit->phone) ? $edit->phone : old('phone') }}" required
                                placeholder="Enter phone" maxlength="10" minlength="10">

                            @error('phone')
                                <span class="invalid-feedback">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-4 col-sm-12 mb-2">
                        <div class="form-group">
                            <label class="form-label">Password</label>
                            <input type="password" class="form-control @error('password') is-invalid @enderror"
                                name="password" value="{{ old('password') }}"
                                {{ isset($edit->password) ? '' : 'required' }} placeholder="Enter password">

                            @error('password')
                                <span class="invalid-feedback">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-4 col-sm-12 mb-2">
                        <div class="form-group">
                            <label class="form-label">Role</label>
                            <select class="form-control select2 w-100 @error('role') is-invalid @enderror selectRole"
                                name="role" required>
                                <option disabled selected>Select role</option>
                                @foreach ($roles as $role)
                                    @if (isset($edit) && count($edit->roles) > 0)
                                        @foreach ($edit->roles as $user_role)
                                            <option value="{{ $role->name }}"
                                                @if (isset($user_role)) @if ($user_role->id == $role->id) {{ 'selected' }} @endif
                                            @else @if (old('role') == $role->id) {{ 'selected' }} @endif
                                                @endif> {{ ucfirst($role->name) }}</option>
                                        @endforeach
                                    @else
                                        <option value="{{ $role->name }}"
                                            @if (isset($user_role)) @if ($user_role->id == $role->id) {{ 'selected' }} @endif
                                        @else @if (old('role') == $role->id) {{ 'selected' }} @endif
                                            @endif> {{ ucfirst($role->name) }}</option>
                                    @endif
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4 col-sm-12 mb-2">
                        <div class="form-group">
                            <label class="form-label">Status</label>
                            <select class="form-control select2 w-100 @error('status') is-invalid @enderror" name="status"
                                required>
                                <option disabled selected>Select Status</option>
                                @foreach (['active', 'inactive'] as $status)
                                    <option value="{{ $status }}"
                                        @if (isset($edit)) @if ($edit->status == $status) {{ 'selected' }} @endif
                                    @else @if (old('status') == $status) {{ 'selected' }} @endif @endif> {{ ucfirst($status) }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4 col-sm-12 mb-2">
                        <div class="form-group">
                            <label class="form-label"> Biometric ID </label>
                            <input id="biometric_id" type="text" class="form-control @error('biometric_id') is-invalid @enderror"
                                name="biometric_id" value="{{ isset($edit->biometric_id) ? $edit->biometric_id : old('biometric_id') }}"
                                placeholder="Enter biometric ID">

                            @error('biometric_id')
                                <span class="invalid-feedback">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-4 col-sm-12 mb-2">
                        <div class="form-group">
                            <label class="form-label"> Date of Birth </label>
                            <input id="date_of_birth" type="date" class="form-control @error('date_of_birth') is-invalid @enderror"
                                name="date_of_birth" value="{{ old('date_of_birth', $edit->date_of_birth ?? '') }}">

                            @error('date_of_birth')
                                <span class="invalid-feedback">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>

                    <div class="col-12 mb-2 showRoleWisePermission"></div>
                    <div class="col-md-12 text-center">
                        <button type="submit" class="btn btn-success mt-1 mb-1">
                            {{ isset($edit) ? 'Update' : 'Submit' }}
                        </button>
                        <a href="{{ route($route . '.index') }}" class="btn btn-danger mt-1 mb-1">Cancel</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

@endsection

@section('page_script_file') @endsection

@section('page_leavel_script')
    <script>
        let isEditID = "{{ isset($edit) && $edit?->id ? $edit?->id : '' }}";
        if (isEditID) {
            // console.log("L-171", isEditID);
            setTimeout(function() {
                $(".selectRole").trigger("change")
            }, 1000);
        }

        $(".selectRole").on("change", function() {
            console.log("L-149", $(this).val());
            let _url = "{{ route('search-permission') }}";
            $.ajax({
                type: "POST",
                url: _url,
                data: {
                    _token: '{{ csrf_token() }}',
                    user_id: isEditID,
                    selectedRole: $(this).val(),
                    getResponse: "html",
                },
                success: function(response) {
                    console.log("data 158", response);
                    let responseData = response?.data;
                    let extraData = response?.extraData;
                    if (extraData?.response && extraData?.response == "html") {
                        // $(".showRoleWisePermission").append(responseData);
                        $(".showRoleWisePermission").empty().append(responseData);
                    } else {
                        console.log("Permission response generated", response);
                    }
                },
                error: function() {
                    console.error("Got the error");
                }
            });
        });
    </script>
@endsection
