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
    </style>
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
                @endisset
                <div class="row">
                    <div class="col-md-4 col-sm-12">
                        <div class="form-group">
                            <label class="form-label" for="country_id">Select country <span
                                    class="text-danger">*</span></label>
                            <select class="form-control select2 search_by_country @error('country_id') is-invalid @enderror"
                                name="country_id" required autofocus
                                data-state_id="{{ isset($edit) && isset($edit->state_id) ? $edit->state_id : '' }}"
                                data-city_id="{{ isset($edit) && isset($edit->city_id) ? $edit->city_id : '' }}">
                                <option value="" selected disabled>Select country</option>
                                @foreach ($countries as $record)
                                    <option value="{{ $record->id }}"
                                        {{ isset($edit) && isset($edit->country_id) && $edit->country_id == $record->id ? 'selected' : (old('country_id') && old('country_id') == $record->id ? 'selected' : '') }}>
                                        {{ $record->name }}</option>
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
                            <label class="form-label" for="pincode"> Pincode <span class="text-danger">*</span> </label>
                            <input id="pincode" type="text" class="form-control @error('pincode') is-invalid @enderror"
                                name="pincode" value="{{ isset($edit?->pincode) ? $edit?->pincode : old('pincode') }}"
                                autocomplete="pincode" placeholder="Enter pincode">

                            @error('pincode')
                                <span class="invalid-feedback">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="form-label">Status</label>
                            <select class="form-control select2 w-100 @error('status') is-invalid @enderror" name="status"
                                required>
                                <option disabled selected>Select Status</option>
                                @foreach (['active', 'inactive'] as $status)
                                    <option value="{{ $status }}"
                                        @if (isset($edit)) @if ($edit->status == $status) {{ 'selected' }} @endif
                                    @else @if (old('status') == $status || $status == 'active') {{ 'selected' }} @endif @endif> {{ ucfirst($status) }}</option>
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
    @include('software.utils.getStateByCountryId')
    @include('software.utils.getCityByStateId')
@endsection
