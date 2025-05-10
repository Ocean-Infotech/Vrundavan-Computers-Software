@extends('admin.layout.app')

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


@section('breadcrumb')
<!-- start page title -->
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            {{-- <h3 class="page-title">{{ $page_title }}</h3> --}}
            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route($route . '.index') }}">View all {{ $page_title }}</a>
                    </li>
                    <li class="breadcrumb-item active">{{ isset($edit) && $edit?->id ? 'Add' : 'Edit' }}
                        {{ $page_title }}
                    </li>
                </ol>
            </div>
        </div>
    </div>
</div>
<!-- end page title -->
@endsection

@section('content')

@include('admin.partials.flash_messages')

<div class="card shadow my-5">
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

                <div class="col-md-4">
                    <div class="form-group">
                        <label class="form-label">Withdraw Request Status <span class="text-danger">*</span></label>
                        <select class="form-control select2 w-100 @error('withdraw_request_status') is-invalid @enderror" required id="withdraw_request_status" name="withdraw_request_status">
                            <option disabled selected>Select Withdraw Request Status</option>
                            @foreach(['Approve','Reject'] AS $withdraw_request_status)
                            <option value="{{ $withdraw_request_status }}"
                                @if(isset($edit))
                                @if(old('withdraw_request_status' , 'Approve' )==$withdraw_request_status) {{ 'selected' }} @endif @endif> {{ ucfirst($withdraw_request_status) }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div id="reason_div" class="col-md-4">
                    <div class="form-group">
                        <label class="form-label">Reason <span class="text-danger">*</span></label>
                        <textarea id="reason" type="text" class="form-control @error('reason') is-invalid @enderror" required name="reason" value="" autocomplete="reason" placeholder="Enter Reason">{{ (isset($edit->image_url)) ? $edit->image_url : old('reason') }}</textarea>
                        @error('reason')
                        <span class="invalid-feedback">
                            <strong>{{ $message }}</strong>
                        </span>
                        @enderror
                    </div>
                </div>

                <div id="image_div" class="col-md-4 col-sm-12">
                    <div class="form-group">
                        <label class="form-label" for="image"> Image <span class="text-danger">*</span> </label>
                        <input id="image" type="file" class="form-control @error('image') is-invalid @enderror" required name="image" value="{{ (isset($edit->image)) ? $edit->image : old('image') }}" autocomplete="image">

                        @error('image')
                        <span class="invalid-feedback">
                            <strong>{{ $message }}</strong>
                        </span>
                        @enderror
                    </div>
                    @isset($edit->image_url)
                    <div class="div_image">
                        <label class="form-label">Image</label>
                        <div class="">
                            <a class="fancybox" rel="group" href="{{ $edit->image_url }}" target="_blank"><img class="wd-100px" src="{{ $edit->image_url }}"></a>
                        </div>
                    </div>
                    @endisset
                </div>
            </div>

            <div class="row my-3">
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


@section('page_leavel_script')
<script>
    $(document).ready(function() {
        function toggleFields() {
            const status = $('#withdraw_request_status').val();
            if (status === 'Approve') {
                $('#image_div').show();
                $('#reason_div').hide();
                $('#reason').removeAttr('required');
            } else if (status === 'Reject') {
                $('#reason_div').show();
                $('#image_div').hide();
                $('#image').removeAttr('required');
            }
        }

        // Initial toggle on page load
        toggleFields();

        // Toggle on change
        $('#withdraw_request_status').change(function() {
            toggleFields();
        });
    });
</script>
@endsection