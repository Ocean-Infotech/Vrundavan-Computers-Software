@extends('software.layout.app')

@php
    $page_title = isset($modules['title']) ? $modules['title'] : null;
    $folder_path = isset($modules['folder_path']) ? $modules['folder_path'] : null;
    $route = isset($modules['route']) ? $modules['route'] : null;
    $permission_prefix = isset($modules['permission_prefix']) ? $modules['permission_prefix'] : null;
@endphp

@section('title', isset($edit) && $edit?->id ? 'Edit ' . $page_title : 'Create ' . $page_title)

@section('page_style_file')
    <!-- select2 css -->
    <link href="{{ asset('public/assets/admin/plugins/select2/css/select2.min.css') }}" rel="stylesheet" />
@endsection

@section('breadcrumb')
    <div class="d-flex justify-content-lg-between px-1">
        @include('software.includes.breadcrumb', [
            'breadcrumbArray' => [
                ['title' => $page_title, 'url' => route($route . '.index')],
                [
                    'title' => isset($edit) && $edit?->id ? 'Edit ' . $page_title : 'Create ' . $page_title,
                    'url' => '',
                ],
            ],
            'page_title' => $page_title,
            'route' => $route,
            'show_add_btn' => false,
            'show_filter_btn' => false,
            'show_back_btn' => true,
        ])
    </div>
@endsection

@section('content')
    <div class="card shadow my-4">
        <div class="card-body mt-4">
            <form id="AssessmentForm"
                action="{{ isset($edit) && $edit?->id ? route($route . '.update', [$edit?->id]) : route($route . '.store') }}"
                method="POST" autocomplete="off">
                @csrf
                @isset($edit)
                    @method('PUT')
                @endisset

                <div class="row">
                    <!-- Student Selection -->
                    <div class="col-md-6 col-sm-12 mb-3">
                        <div class="form-group">
                            <label class="form-label">GR No. <span class="text-danger">*</span></label>
                            <select id="admission_id" name="admission_id"
                                class="form-control select2 @error('admission_id') is-invalid @enderror" required>
                                <option value="" disabled {{ !isset($edit) ? 'selected' : '' }}>Select Student (GR No.)</option>
                                @foreach ($students as $student)
                                    <option value="{{ $student->id }}"
                                        @if (isset($edit) && $edit->admission_id == $student->id) selected
                                        @elseif(old('admission_id') == $student->id) selected @endif>
                                        {{ trim(($student->first_name ?? '') . ' ' . ($student->last_name ?? '') . ' ' . ($student->father_name ?? '')) }} (GR: {{ $student->gr_no ?? '-' }})
                                    </option>
                                @endforeach
                            </select>
                            @error('admission_id')
                                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>

                    <!-- Subject -->
                    <div class="col-md-6 col-sm-12 mb-3">
                        <div class="form-group">
                            <label class="form-label">Subject <span class="text-danger">*</span></label>
                            <input type="text" name="subject" class="form-control @error('subject') is-invalid @enderror"
                                value="{{ isset($edit->subject) ? $edit->subject : old('subject') }}" placeholder="Enter Subject" required>
                            @error('subject')
                                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <!-- Performance -->
                    <div class="col-md-6 col-sm-12 mb-3">
                        <div class="form-group">
                            <label class="form-label d-block">Performance <span class="text-danger">*</span></label>
                            <div class="d-flex gap-3 mt-2">
                                <div class="form-check">
                                    <input class="form-check-input @error('performance') is-invalid @enderror" type="radio" name="performance" id="performance_best" value="best"
                                        {{ (isset($edit) && $edit->performance == 'best') || old('performance') == 'best' ? 'checked' : '' }} required>
                                    <label class="form-check-label" for="performance_best">Best</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input @error('performance') is-invalid @enderror" type="radio" name="performance" id="performance_good" value="good"
                                        {{ (isset($edit) && $edit->performance == 'good') || old('performance') == 'good' ? 'checked' : '' }} required>
                                    <label class="form-check-label" for="performance_good">Good</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input @error('performance') is-invalid @enderror" type="radio" name="performance" id="performance_average" value="average"
                                        {{ (isset($edit) && $edit->performance == 'average') || old('performance') == 'average' ? 'checked' : '' }} required>
                                    <label class="form-check-label" for="performance_average">Average</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input @error('performance') is-invalid @enderror" type="radio" name="performance" id="performance_poor" value="poor"
                                        {{ (isset($edit) && $edit->performance == 'poor') || old('performance') == 'poor' ? 'checked' : '' }} required>
                                    <label class="form-check-label" for="performance_poor">Poor</label>
                                </div>
                            </div>
                            @error('performance')
                                <span class="invalid-feedback d-block"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <!-- Remarks -->
                    <div class="col-md-12 mb-3">
                        <div class="form-group">
                            <label class="form-label">Remarks</label>
                            <textarea name="remarks" class="form-control @error('remarks') is-invalid @enderror" rows="4"
                                placeholder="Enter remarks...">{{ isset($edit->remarks) ? $edit->remarks : old('remarks') }}</textarea>
                            @error('remarks')
                                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12 text-center mt-3">
                        <button type="submit" class="btn btn-success me-2">{{ isset($edit) ? 'Update' : 'Submit' }}</button>
                        <a href="{{ route($route . '.index') }}" class="btn btn-danger">Cancel</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('page_leavel_script')
    <script>
        $(document).ready(function() {
            if ($.fn.select2) {
                $('.select2').select2({
                    placeholder: "Select an option",
                    allowClear: true
                });
            }
        });
    </script>
@endsection
