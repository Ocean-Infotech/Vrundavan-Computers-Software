@extends('software.layout.app')

@php
    $page_title = isset($modules['title']) ? $modules['title'] : null;
    $folder_path = isset($modules['folder_path']) ? $modules['folder_path'] : null;
    $route = isset($modules['route']) ? $modules['route'] : null;
    $permission_prefix = isset($modules['permission_prefix']) ? $modules['permission_prefix'] : null;
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

    <div class="card shadow">
        <div class="card-header d-none">
            <h2 class="mb-0">
                {{ $page_title }}
            </h2>
        </div>
        <div class="card-body">
            <form
                action="{{ isset($edit) && $edit?->id ? route($route . '.update', [$edit?->id]) : route($route . '.store') }}"
                method="POST" enctype="multipart/form-data">
                @csrf
                @isset($edit)
                    @method('PUT')
                    <input type="hidden" name="id" value="{{ $edit?->id ?? '' }}" />
                @endisset
                <div class="row">
                    <div class="col-md-4 col-sm-12 mb-2">
                        <div class="form-group">
                            <label class="form-label"> Document Type <span class="text-danger">*</span> </label>
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
                            <label class="form-label">Status</label>
                            <select class="form-control select2 w-100 @error('status') is-invalid @enderror" name="status"
                                required>
                                <option disabled selected>Select Status</option>
                                @foreach (['active', 'inactive'] as $status)
                                    <option value="{{ $status }}"
                                        @if (isset($edit)) @if ($edit->status == $status) {{ 'selected' }} @endif
                                    @else @if (old('status', 'active') == $status) {{ 'selected' }} @endif @endif> {{ ucfirst($status) }}</option>
                                @endforeach
                            </select>
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
