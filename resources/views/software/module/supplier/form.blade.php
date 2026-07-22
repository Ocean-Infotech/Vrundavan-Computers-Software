@extends('software.layout.app')

@php
    $page_title = isset($modules['title']) ? $modules['title'] : null;
    $folder_path = isset($modules['folder_path']) ? $modules['folder_path'] : null;
    $route = isset($modules['route']) ? $modules['route'] : null;
    $permission_prefix = isset($modules['permission_prefix']) ? $modules['permission_prefix'] : null;
@endphp
@section('title', $page_title)

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

    <div class="card shadow">
        <div class="card-body">
            <form
                action="{{ isset($edit) && $edit?->id ? route($route . '.update', [$edit?->id]) : route($route . '.store') }}"
                method="POST">
                @csrf
                @isset($edit)
                    @method('PUT')
                    <input type="hidden" name="id" value="{{ $edit?->id ?? '' }}" />
                @endisset

                <div class="row">
                    <!-- Name -->
                    <div class="col-md-6 col-sm-12 mb-3">
                        <div class="form-group">
                            <label class="form-label">Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                value="{{ isset($edit->name) ? $edit->name : old('name') }}" placeholder="Enter Name" required autofocus>
                            @error('name')
                                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>

                    <!-- Category -->
                    <div class="col-md-6 col-sm-12 mb-3">
                        <div class="form-group">
                            <label class="form-label">Category</label>
                            <input type="text" name="category" class="form-control @error('category') is-invalid @enderror"
                                value="{{ isset($edit->category) ? $edit->category : old('category') }}" placeholder="Enter Category">
                            @error('category')
                                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>

                    <!-- City -->
                    <div class="col-md-6 col-sm-12 mb-3">
                        <div class="form-group">
                            <label class="form-label">City</label>
                            <input type="text" name="city" class="form-control @error('city') is-invalid @enderror"
                                value="{{ isset($edit->city) ? $edit->city : old('city') }}" placeholder="Enter City">
                            @error('city')
                                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>

                    <!-- Mobile -->
                    <div class="col-md-6 col-sm-12 mb-3">
                        <div class="form-group">
                            <label class="form-label">Mobile</label>
                            <input type="text" name="mobile" minlength="10" maxlength="10" oninput="this.value = this.value.replace(/[^0-9]/g, '');"
                                class="form-control @error('mobile') is-invalid @enderror num_only"
                                value="{{ isset($edit->mobile) ? $edit->mobile : old('mobile') }}" placeholder="Enter 10 Digit Mobile">
                            @error('mobile')
                                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-12 text-center mt-3">
                        <button type="submit" class="btn btn-success me-2">
                            {{ isset($edit) ? 'Update' : 'Submit' }}
                        </button>
                        <a href="{{ route($route . '.index') }}" class="btn btn-danger">Cancel</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection
