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
        <form action="{{ route('set-min-withdraw-request-amount') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <input name="key" value="{{ $modules['key'] ?? $route }}" hidden />
            <div class="row">
                <div class="col-md-3 col-sm-12">
                    <div class="form-group">
                        <label class="form-label" for="min_amount"> Min. Amount<span class="text-danger">*</span> </label>
                        <div class="input-group">
                            <input id="min_amount" type="number"
                                class="form-control @error('min_amount') is-invalid @enderror" name="min_amount"
                                value="{{ (isset($edit) && isset($edit?->value?->min_amount)) ? $edit?->value?->min_amount : old('min_amount') }}" placeholder="Min. Amount" min="1">
                            <span class="input-group-text">Min. Amount</span>
                        </div>
                        @error('min_amount')
                        <span class="invalid-feedback">
                            <strong>{{ $message }}</strong>
                        </span>
                        @enderror
                    </div>
                </div>
                <div class="col-md-3 col-sm-12">
                    <div class="form-group">
                        <label class="form-label" for="max_amount"> Max. Amount <span class="text-danger">*</span> </label>
                        <div class="input-group">
                            <input id="max_amount" type="number"
                                class="form-control @error('max_amount') is-invalid @enderror" name="max_amount"
                                value="{{ (isset($edit) && isset($edit?->value?->max_amount)) ? $edit?->value?->max_amount : old('max_amount') }}" placeholder="Max. Amount" min="0">
                            <span class="input-group-text">Max. Amount</span>
                        </div>
                        @error('max_amount')
                        <span class="invalid-feedback">
                            <strong>{{ $message }}</strong>
                        </span>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12 text-center">
                    <button type="submit" class="btn btn-success mt-1 mb-1">
                        {{ isset($edit) ? 'Update' : 'Submit' }}
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

@endsection


@section('page_leavel_script')
@endsection