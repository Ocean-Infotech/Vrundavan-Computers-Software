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
                    <li class="breadcrumb-item active">{{ isset($edit) && $edit?->id ? 'Edit' : 'Add' }}
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
            @endisset
            <div class="row">
                <div class="col-md-4 col-sm-12">
                    <div class="form-group">
                        <label class="form-label" for="slider_type">Select Slider <span class="text-danger">*</span></label>
                        <select class="form-control select2 @error('slider_type') is-invalid @enderror" name="slider_type" required autofocus>
                            <option value="" selected disabled>Select Slider</option>
                            @foreach($slider_type as $key => $slider)
                            <option value="{{ $key }}"
                                @if(old('slider_type') && old('slider_type')==$key) {{ 'selected' }} @endif
                                @if(isset($edit->slider_type) && $edit->slider_type == $key) {{ 'selected' }} @endif > {{ $slider }} </option>
                            @endforeach
                        </select>
                        @error('name')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                        @enderror
                        {{-- $(this).val().toLowerCase().replace(/ +/g, "_").replace(/[^\w ]+/g, "") --}}
                    </div>
                </div>

                <div class="col-md-4 col-sm-12">
                    <div class="form-group">
                        <label class="form-label" for="image"> Slider Image <span class="text-danger">*</span> </label>
                        <input id="image" type="file" class="form-control @error('image') is-invalid @enderror" name="image" value="{{ (isset($edit->image)) ? $edit->image : old('image') }}" autocomplete="image">

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
                            <a class="fancybox" rel="group" href="{{ $edit->image_url }}" target="_blank">
                                <img class="" width="120px" src="{{ $edit->image_url }}">
                            </a>
                        </div>
                    </div>
                    @endisset
                </div>

                <div class="col-md-4 col-sm-12">
                    <div class="form-group">
                        <label class="form-label" for="redirect_url"> Slider Redirect URL </label>
                        <input id="redirect_url" type="text" class="form-control @error('redirect_url') is-invalid @enderror" name="redirect_url" value="{{ '#' }}" autocomplete="redirect_url" placeholder="Offer Name">
                        @error('redirect_url')
                        <span class="invalid-feedback">
                            <strong>{{ $message }}</strong>
                        </span>
                        @enderror
                    </div>
                </div>

                <div class="col-md-4 col-sm-12 div_video d-none">
                    <div class="form-group">
                        <label class="form-label" for="video_url"> Slider Video URL </label>
                        <input id="video_url" type="text" class="form-control @error('video_url') is-invalid @enderror" name="video_url" value="{{ '#' }}" autocomplete="video_url" placeholder="Video URL">

                        @error('video_url')
                        <span class="invalid-feedback">
                            <strong>{{ $message }}</strong>
                        </span>
                        @enderror
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-group">
                        <label class="form-label">Status</label>
                        <select class="form-control select2 w-100 @error('status') is-invalid @enderror" name="status" required>
                            <option disabled selected>Select Status</option>
                            @foreach(['active', 'inactive'] AS $status)
                            <option value="{{ $status }}" @if(isset($edit)) @if($edit->status == $status) {{ 'selected' }} @endif @else @if(old('status') == $status || $status == 'active') {{ 'selected' }} @endif @endif> {{ ucfirst($status) }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div class="row">
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
@endsection