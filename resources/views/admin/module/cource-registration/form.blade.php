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


<link rel="stylesheet" href="{{ asset('admin/assets/vendor/libs/flatpickr/flatpickr.css') }}" />

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

<div class="card shadow my-5">
    <div class="card-header d-none">
        <h2 class="mb-0">
            {{ $page_title }}
        </h2>
    </div>
    <div class="card-body my-4">
        <form method="POST"
            action="{{ isset($edit) ? route($route . '.update', $edit->id) : route($route . '.store') }}">
            @csrf
            @isset($edit)
            @method('PUT')
            @endisset

            <div class="row">

                {{-- Register No --}}
                <div class="col-md-4 mb-2">
                    <div class="form-group">
                        <label for="register_no">Register No. <span class="text-danger">*</span></label>

                        <input list="registers_id" id="register_id" name="register_id" class="form-control @error('register_id') is-invalid @enderror" placeholder="Register No.">

                        <datalist id="registers_id">
                            <!-- <option value="">-- Select Register No. --</option> -->
                            @foreach ($admissions as $admission)
                            <option value="{{ $admission->id }}"
                                {{ (int) old('register_id', $edit->register_id ?? '') === $admission->id ? 'selected' : '' }}>
                                {{ $admission->first_name." ".$admission->father_name." ".$admission->last_name }}
                            </option>
                            @endforeach
                        </datalist>

                        @error('register_no')
                        <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                </div>



                {{-- Course Name --}}
                <div class="col-md-4 col-sm-12">
                    <div class="form-group">
                        <label class="form-label">Select Course <span class="text-danger">*</span></label>
                        <select id="course" name="course_id"
                            class="form-control search_by_course select2 @error('course_id') is-invalid @enderror"
                            data-append="search_by_course"
                            data-selectedCourseId="{{ isset($edit) && $edit?->course_id ? $edit?->course_id : old('course_id') }}"
                            autofocus required>
                            <option value="">Select Course</option>
                        </select>

                        @error('course_id')
                        <span class="invalid-feedback">
                            <strong>{{ $message }}</strong>
                        </span>
                        @enderror
                    </div>
                </div>

                {{-- Fees (auto-filled) --}}
                <div class="col-md-4 mb-2">
                    <div class="form-group">
                        <label for="fee">Course Fees <span class="text-danger">*</span></label>
                        <input type="text" name="fee" id="fee"
                            class="form-control num_only @error('fee') is-invalid @enderror"
                            value="{{ old('fee', $edit->fee ?? '') }}" placeholder="Course Fees">
                        @error('fee')
                        <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                    {{-- Date --}}
                    <div class="col-md-4 mb-3">
                        <label for="date">Date <span class="text-danger">*</span></label>
                        <input type="date" name="date" id="flatpickr-date"
                            class="form-control datepicker @error('date') is-invalid @enderror"
                            value="{{ old('date', $edit->date ?? '') }}" placeholder="DD-MM-YYYY">
                        @error('date')
                        <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                {{-- Note --}}
                <div class="col-md-4 mb-2">
                    <div class="form-group">
                        <label for="note">Note</label>
                        <textarea name="note" id="note" rows="3" class="form-control @error('note') is-invalid @enderror"
                            placeholder="Enter Note">{{ old('note', $edit->note ?? '') }}</textarea>
                        @error('note')
                        <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
                {{-- Status --}}
                {{-- <div class="col-md-4 mb-2">
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
    </div> --}}

    {{-- Submit --}}
    <div class="col-md-12 text-center mt-4">
        <button type="submit" class="btn btn-success">{{ isset($edit) ? 'Update' : 'Submit' }}</button>
        <a href="{{ route($route . '.index') }}" class="btn btn-danger">Cancel</a>
    </div>
</div>
</form>
</div>
</div>

@endsection

@section('page_leavel_script')
@include('admin.utils.getCourse')


<!-- <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js"></script> -->



<script src="{{ asset('admin/assets/vendor/libs/flatpickr/flatpickr.js') }}"></script>

<script src="{{ asset('admin/assets/js/forms-pickers.js') }}"></script>
<script>
    $(document).ready(function() {
        $('.select2').select2();
        $('.datepicker').datepicker({
            format: 'yyyy-mm-dd',
            autoclose: true,
            todayHighlight: true
        });

        // $('#course_name').on('change', function() {
        //     var fee = $(this).find(':selected').data('fee');
        //     $('#fee').val(fee);
        // });
    });
    // let $i = 0;
    $('#course').change(function() {
        let fees = $(this).find(':selected').data('fees') || '';
        // if($i !== 0){
            $('#fee').val(fees);
        // }else{
        //     ++$i
        // }

    });
    //      document.addEventListener('DOMContentLoaded', function () {
    //     const courseSelect = document.getElementById('course_id');
    //     const feesInput = document.getElementById('fee');

            $('#register_id').on('change', function() {
                const registerId = $(this).val();
                const _url = $(this).find('option:selected').attr('data-url');

                if (registerId && _url) {
                    // prevent duplicate fetch if the same id is selected (optional)
                    if (registerId === previousRegisterId) return;
                }
            });
    //     // Trigger on load (for edit page)
    //     updateFees();
    // });
</script>
@endsection
