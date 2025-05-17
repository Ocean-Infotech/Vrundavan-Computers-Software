@extends('admin.layout.app')

@php
<<<<<<< HEAD
    $page_title = $modules['title'] ?? null;
    $folder_path = $modules['folder_path'] ?? null;
    $route = $modules['route'] ?? null;
    $permisstion_prefix = $modules['permisstion_prefix'] ?? null;
=======
$page_title = isset($modules['title']) ? $modules['title'] : null;
$folder_path = isset($modules['folder_path']) ? $modules['folder_path'] : null;
$route = isset($modules['route']) ? $modules['route'] : null;
$permisstion_prefix = isset($modules['permisstion_prefix']) ? $modules['permisstion_prefix'] : null;
$i = 0;
>>>>>>> c3185a777309af4835b742caed5ccc0aec4a2db9
@endphp

@section('title', $page_title)

@section('page_style_file')
<<<<<<< HEAD
    <link href="{{ asset('assets/select2/css/select2.min.css') }}" rel="stylesheet">
@endsection

@section('content')
    <div class="card shadow my-5">
        <div class="card-body my-4">
            <h4 class="mb-4">
                @isset($edit)
                    Edit {{ $page_title }}
                @else
                    Add {{ $page_title }}
                @endisset
            </h4>

            <form method="POST"
                action="@isset($edit) {{ route($route . '.update', $edit->id) }} @else {{ route($route . '.store') }} @endisset">
                @csrf
                @isset($edit)
                    @method('PUT')
                @endisset

                <div class="row">
                    {{-- Register No --}}
                    <div class="col-md-4 mb-3">
                        <label for="register_id" class="form-label">
                            Register No. <span class="text-danger">*</span>
                        </label>
                        <select name="register_id" id="register_id"
                            class="form-control select2 @error('register_id') is-invalid @enderror">
                            <option value="">-- Select Register No. --</option>
                            @foreach ($education as $edu)
                                <option value="{{ $edu->id }}"
                                    data-url="{{ route('get-admission-details', ['id' => $edu->id]) }}"
                                    {{ old('register_id', $edit->register_id ?? '') == $edu->id ? 'selected' : '' }}>
                                    {{ $edu->id }}
                                </option>
                                {{-- <option value="{{ $edu->id }}"
                                    data-url="{{ url('/admission/details/' . $edu->id) }}">
                                    {{ $edu->first_name }} {{ $edu->last_name }}
                                </option> --}}
                            @endforeach
                        </select>

                        @error('register_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div id="admission-info" style="display: none; margin-top: 10px;">
                            <p><strong>Name:</strong> <span id="admission-name"></span></p>
                            <p><strong>Course:</strong> <span id="admission-course"></span></p>
                            <p><strong>Joining Date:</strong> <span id="admission-joining-date"></span></p>
                        </div>
                    </div>
                </div>
                <div class="row">

                    {{-- Course Name --}}
                    <div class="col-md-4 mb-3">
                        <label for="course_id" class="form-label">
                            Course Name <span class="text-danger">*</span>
                        </label>
                        <select name="course_id" id="course_id"
                            class="form-control select2 @error('course_id') is-invalid @enderror">
                            <option value="">-- Select Course Name--</option>
                            @foreach ($courses as $course)
                                <option value="{{ $course->id }}" data-fee="{{ $course->course_fees }}"
                                    {{ old('course_id', $edit->course_id ?? '') == $course->id ? 'selected' : '' }}>
                                    {{ $course->course_name }}
                                </option>
                            @endforeach
                        </select>
                        @error('course_id')
                            <div class="invalid-feedback">{{ $message }}</div>
=======


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
>>>>>>> c3185a777309af4835b742caed5ccc0aec4a2db9
                        @enderror
                    </div>
                </div>

<<<<<<< HEAD
                    {{-- Fee --}}
                    <div class="col-md-4 mb-3">
                        <label for="fee" class="form-label">
                            Fee <span class="text-danger">*</span>
                        </label>
                        <input type="number" name="fee" id="fee"
                            class="form-control @error('fee') is-invalid @enderror" placeholder="Course Fee"
                            value="{{ old('fee', $edit->fee ?? '') }}">
                        @error('fee')
                            <div class="invalid-feedback">{{ $message }}</div>
=======


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
>>>>>>> c3185a777309af4835b742caed5ccc0aec4a2db9
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
<<<<<<< HEAD
                        <input type="date" name="date" id="date"
                            class="form-control @error('date') is-invalid @enderror"
                            value="{{ old('date', $edit->date ?? '') }}">
=======
                        <input type="date" name="date" id="flatpickr-date"
                            class="form-control datepicker @error('date') is-invalid @enderror"
                            value="{{ old('date', $edit->date ?? '') }}" placeholder="DD-MM-YYYY">
>>>>>>> c3185a777309af4835b742caed5ccc0aec4a2db9
                        @error('date')
                        <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

<<<<<<< HEAD
                    {{-- Note --}}
                    <div class="col-md-4 mb-3">
                        <label for="note" class="form-label">Note</label>
=======
                {{-- Note --}}
                <div class="col-md-4 mb-2">
                    <div class="form-group">
                        <label for="note">Note</label>
>>>>>>> c3185a777309af4835b742caed5ccc0aec4a2db9
                        <textarea name="note" id="note" rows="3" class="form-control @error('note') is-invalid @enderror"
                            placeholder="Enter Note">{{ old('note', $edit->note ?? '') }}</textarea>
                        @error('note')
<<<<<<< HEAD
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Status --}}
                    <div class="col-md-4 mb-3">
                        <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                        <select name="status" id="status"
                            class="form-control select2 @error('status') is-invalid @enderror">
                            <option value="" disabled>Select Status</option>
                            @foreach (['active', 'inactive'] as $status)
                                <option value="{{ $status }}"
                                    {{ old('status', $edit->status ?? 'active') == $status ? 'selected' : '' }}>
                                    {{ ucfirst($status) }}
                                </option>
                            @endforeach
                        </select>
                        @error('status')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="mt-4 text-center">
                    <button type="submit" class="btn btn-success">
                        @isset($edit)
                            Update
                        @else
                            Submit
                        @endisset
                    </button>
                    <a href="{{ route($route . '.index') }}" class="btn btn-danger">Cancel</a>
                </div>
            </form>
        </div>
    </div>
=======
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

>>>>>>> c3185a777309af4835b742caed5ccc0aec4a2db9
@endsection

@section('page_leavel_script')
<<<<<<< HEAD
    <script src="{{ asset('assets/jquery/jquery.min.js') }}"></script>
    <script>
        $(document).ready(function() {
            $('.select2').select2({
                width: '100%',
                placeholder: 'Choose an option',
                allowClear: true
            });

            function updateFee() {
                var fee = $('#course_id option:selected').data('fee') || '';
                $('#fee').val(fee);
            }

            $('#course_id').on('change', updateFee);
            updateFee();



        });

        $(document).ready(function() {
            let previousRegisterId = null;
=======
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
    let $i = 0;
    $('#course').change(function() {
        let fees = $(this).find(':selected').data('fees') || '';
        if($i !== 0){
            $('#fee').val(fees);
        }else{
            ++$i
        }

    });
    //      document.addEventListener('DOMContentLoaded', function () {
    //     const courseSelect = document.getElementById('course_id');
    //     const feesInput = document.getElementById('fee');
>>>>>>> c3185a777309af4835b742caed5ccc0aec4a2db9

            $('#register_id').on('change', function() {
                const registerId = $(this).val();
                const _url = $(this).find('option:selected').attr('data-url');

                if (registerId && _url) {
                    // prevent duplicate fetch if the same id is selected (optional)
                    if (registerId === previousRegisterId) return;

<<<<<<< HEAD
                    previousRegisterId = registerId;

                    $.ajax({
                        url: _url + '?t=' + new Date().getTime(), // prevent caching
                        type: 'GET',
                        dataType: 'json',
                        success: function(data) {
                            if (data.first_name && data.last_name) {
                                $('#admission-name').text(data.first_name + ' ' + data
                                    .last_name);
                            } else {
                                $('#admission-name').text('No name found');
                            }

                            $('#admission-course').text(data.course_id || 'N/A');
                            $('#admission-joining-date').text(data.date || 'N/A');
                            $('#admission-info').show();
                        },
                        error: function(xhr) {
                            console.log(xhr.responseText);
                            $('#admission-name').text('Not found');
                            $('#admission-course').text('');
                            $('#admission-joining-date').text('');
                            $('#admission-info').show();
                        }
                    });
                } else {
                    $('#admission-info').hide();
                }
            });

            // Optional: re-trigger change if same value selected again
            $('#register_id').on('click', function() {
                const currentVal = $(this).val();
                if (currentVal === previousRegisterId) {
                    $(this).trigger('change'); // force re-fetch
                }
            });
        });
    </script>
@endsection
=======
    //     // Trigger on load (for edit page)
    //     updateFees();
    // });
</script>
@endsection
>>>>>>> c3185a777309af4835b742caed5ccc0aec4a2db9
