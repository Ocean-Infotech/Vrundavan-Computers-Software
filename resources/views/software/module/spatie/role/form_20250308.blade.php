@extends('software.layout.app')

@php
$page_title = (isset($modules['title'])) ? $modules['title'] : null;
$folder_path = (isset($modules['folder_path'])) ? $modules['folder_path'] : null;
$route = (isset($modules['route'])) ? $modules['route'] : null;
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

<div class="card shadow">
    <div class="card-header d-none">
        <h2 class="mb-0">
            {{ $page_title }}
        </h2>
    </div>
    <div class="card-body">
        <form action="{{ (isset($edit) && $edit?->id) ? route($route.'.update', [$edit?->id]) : route($route.'.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            @isset($edit)
			@method('PUT')
			@endisset
            <div class="row">
                <div class="col-md-4 col-sm-12">
                    <div class="form-group">
                        <label class="form-label"> Name <span class="text-danger">*</span> </label>
                        <input id="name" type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ (isset($edit?->name)) ? $edit?->name : old('name') }}" autocomplete="name" autofocus placeholder="Enter name">

                        @error('name')
                        <span class="invalid-feedback">
                            <strong>{{ $message }}</strong>
                        </span>
                        @enderror
                    </div>
                </div>
                <div class="form-group">
                    <label for="formGroupExampleInput" class="d-block">Gived Permission</label>
                    <div class="row">
                        @foreach($permission as $value)
                        <div class="col-md-3">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" id="{{ $value->name }}" name="permission[]" value="{{ $value->id }}" {{ @(isset($rolePermissions) && in_array($value->id, $rolePermissions)) ? 'checked' : '' }}>
                                <label class="custom-control-label" for="{{ $value->name }}">{{ $value->name }}</label>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @error('permission')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                    @enderror
                    {{-- $(this).val().toLowerCase().replace(/ +/g, "_").replace(/[^\w ]+/g, "") --}}
                </div>
                <div class="col-md-12 text-center">
                    <button type="submit" class="btn btn-success mt-1 mb-1">
                        {{ (isset($edit)) ? 'Update' : 'Submit' }}
                    </button>
                    <a href="{{ route($route.'.index') }}" class="btn btn-danger mt-1 mb-1">Cancel</a>
                </div>
            </div>
        </form>
    </div>
</div>

@endsection

