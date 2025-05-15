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
                            {{ $page_title }}</li>
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
                            <select name="register_no" id="register_no"
                                class="form-control select2 @error('register_no') is-invalid @enderror">
                                <option value="">-- Select Register No. --</option>

                            </select>
                            @error('register_no')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    {{-- Course Name --}}
                    <div class="col-md-4 mb-2">
                        <div class="form-group">
                            <label for="course_id">Course Name <span class="text-danger">*</span></label>
                            <select name="course_id" id="course_id"
                                class="form-control select2 @error('course_id') is-invalid @enderror">
                                <option value="">-- Select Course --</option>
                                @foreach ($courses as $course)
                                    <option value="{{ $course->id }}" data-fee="{{ $course->course_fees }}"
                                        {{ (int) old('course_id', $edit->course_id ?? '') === $course->id ? 'selected' : '' }}>
                                        {{ $course->course_name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('course_id')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    {{-- Fees (auto-filled) --}}
                    <div class="col-md-4 mb-2">
                        <div class="form-group">
                            <label for="fee">Course Fees <span class="text-danger">*</span></label>
                            <input type="text" name="fees" id="fee"
                                class="form-control @error('fee') is-invalid @enderror"
                                value="{{ old('fee', $edit->feess ?? '') }}">
                            @error('fee')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>


                {{-- Date --}}
                <div class="col-md-4 mb-2">
                    <div class="form-group">
                        <label for="date">Date <span class="text-danger">*</span></label>
                        <input type="date" name="date" id="date"
                            class="form-control datepicker @error('date') is-invalid @enderror"
                            value="{{ old('date', isset($edit->date) ? $edit->date->format('Y-m-d') : \Carbon\Carbon::now()->format('Y-m-d')) }}">
                        @error('date')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
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
                <div class="col-md-4 mb-2">
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
@endsection


@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js"></script>

    <script>
        $(document).ready(function() {
            $('.select2').select2();
            $('.datepicker').datepicker({
                format: 'yyyy-mm-dd',
                autoclose: true,
                todayHighlight: true
            });

            $('#course_name').on('change', function() {
                var fee = $(this).find(':selected').data('fee');
                $('#fees').val(fee);
            });
        });

        // $('#course_id').on('change', function() {
        //     let fee = $(this).find(':selected').data('fee') || '';
        //     $('#fees').val(fee);
        // });
         document.addEventListener('DOMContentLoaded', function () {
        const courseSelect = document.getElementById('course_id');
        const feesInput = document.getElementById('fees');

        function updateFees() {
            const selected = courseSelect.options[courseSelect.selectedIndex];
            const fee = selected.getAttribute('data-fee');
            if (feesInput && fee !== null) {
                feesInput.value = fee;
            }
        }

        // Change event
        courseSelect.addEventListener('change', updateFees);

        // Trigger on load (for edit page)
        updateFees();
    });
    </script>
@endpush
