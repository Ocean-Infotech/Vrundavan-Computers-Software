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
                    </li>
                    <li class="breadcrumb-item active"> {{ $page_title }}</li>
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
        <form action="{{ route('set-point-to-rupees') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <input name="key" value="{{ $modules['key'] ?? $route }}" hidden />
            <div class="row">
                {{-- <div class="col-md-6 col-sm-12">
                        <label class="form-label">Point = Rupees</label>
                        <div class="input-group">
                            <input type="text" class="form-control" placeholder="Point" aria-label="Point">
                            <span class="input-group-text">=</span>
                            <input type="text" class="form-control" placeholder="Rupees" aria-label="Rupees">
                        </div>
                    </div> --}}
                <div class="col-md-3 col-sm-12">
                    <div class="form-group">
                        <label class="form-label" for="point"> Point <span class="text-danger">*</span> </label>
                        <div class="input-group">
                            <span class="input-group-text">Per</span>
                            <input id="point" type="number"
                                class="form-control @error('point') is-invalid @enderror" name="point"
                                value="{{ (isset($edit) && isset($edit?->value?->point)) ? $edit?->value?->point : old('point') }}" placeholder="Point" min="1">
                            <span class="input-group-text">Point</span>
                        </div>
                        @error('point')
                        <span class="invalid-feedback">
                            <strong>{{ $message }}</strong>
                        </span>
                        @enderror
                    </div>
                </div>
                <div class="col-md-3 col-sm-12">
                    <div class="form-group">
                        <label class="form-label" for="rupees"> Rupees <span class="text-danger">*</span> </label>
                        <div class="input-group">
                            <input id="rupees" type="number"
                                class="form-control @error('rupees') is-invalid @enderror" name="rupees"
                                value="{{ (isset($edit) && isset($edit?->value?->rupees)) ? $edit?->value?->rupees : old('rupees') }}" placeholder="Rupees" min="0">
                            <span class="input-group-text">Repees</span>
                        </div>
                        @error('rupees')
                        <span class="invalid-feedback">
                            <strong>{{ $message }}</strong>
                        </span>
                        @enderror
                    </div>
                </div>

                {{-- <div class="col-md-4">
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
    </div> --}}
</div>

<div class="row">
    <div class="col-md-12 text-center">
        <button type="submit" class="btn btn-success mt-1 mb-1">
            {{ isset($edit) ? 'Update' : 'Submit' }}
        </button>
        {{-- <a href="{{ route($route . '.index') }}" class="btn btn-danger mt-1 mb-1">Cancel</a> --}}
    </div>
</div>
</form>
</div>
</div>

@endsection


@section('page_leavel_script')
@endsection