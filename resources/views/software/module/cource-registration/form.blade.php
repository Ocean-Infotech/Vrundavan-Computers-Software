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


<link rel="stylesheet" href="{{ asset('admin/assets/vendor/libs/flatpickr/flatpickr.css') }}" />

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

<div class="card shadow my-3">
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
                <div class="col-md-6 col-sm-12">
                    <div class="row">
                        {{-- Register No --}}
                        <div class="col-md-12 mb-3">
                            <div class="form-group">
                                <label for="register_no">Select Student<span class="text-danger">*</span></label>

                                <input list="registers_id" id="register_id" name="register_id" class="form-control @error('register_id') is-invalid @enderror" value="{{ old('register_id', $edit->register_id ?? '') }}" placeholder="Register No.">

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
                        <div class="col-md-12 col-sm-12 mb-3">
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

                        {{-- Batch --}}
                        <div class="col-md-12 col-sm-12 mb-3">
                            <div class="form-group">
                                <label class="form-label">Select Batch <span class="text-danger">*</span></label>
                                <select id="batch" name="batch_id"
                                    class="form-control search_by_batch select2 @error('batch_id') is-invalid @enderror select_filter"
                                    data-append="search_by_batch"
                                    data-selectedBatchId="{{ isset($edit) && $edit?->batch_id ? $edit?->batch_id : old('batch_id') }}"
                                    autofocus>
                                    <option value="">Select Batch</option>
                                </select>

                                @error('batch_id')
                                <span class="invalid-feedback">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-12 col-sm-12 mb-3">
                            <div class="form-group">
                                <label class="form-label">Select Class <span class="text-danger">*</span></label>
                                <select id="class" name="class_id"
                                    class="form-control search_by_class select2 @error('class_id') is-invalid @enderror select_filter"
                                    data-append="search_by_class"
                                    data-selectedClassId="{{ isset($edit) && $edit?->class_id ? $edit?->class_id : old('class_id') }}"
                                    autofocus>
                                    <option value="">Select Class</option>
                                </select>

                                @error('class_id')
                                <span class="invalid-feedback">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>

                        {{-- Shift --}}
                        <div class="col-md-12 col-sm-12 mb-3">
                            <div class="form-group">
                                <label class="form-label">Select Shift <span class="text-danger">*</span></label>
                                <select id="shift" name="shift_id"
                                    class="form-control search_by_shift select2 @error('shift_id') is-invalid @enderror select_filter"
                                    data-append="search_by_shift"
                                    data-selectedShiftId="{{ isset($edit) && $edit?->shift_id ? $edit?->shift_id : old('shift_id') }}"
                                    autofocus>
                                    <option value="">Select Shift</option>
                                </select>

                                @error('shift_id')
                                <span class="invalid-feedback">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>

                        {{-- Fees (auto-filled) --}}
                        <div class="col-md-12 mb-3">
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
                        <div class="col-md-12 mb-3">
                            <label for="date">Date <span class="text-danger">*</span></label>
                            <input type="date" name="date" id="flatpickr-date"
                                class="form-control datepicker @error('date') is-invalid @enderror"
                                value="{{ old('date', $edit->date ?? '') }}" placeholder="DD-MM-YYYY">

                            @error('date')
                            <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- Note --}}
                        <div class="col-md-12 mb-3">
                            <div class="form-group">
                                <label for="note">Note</label>
                                <textarea name="note" id="note" rows="3" class="form-control @error('note') is-invalid @enderror"
                                    placeholder="Enter Note">{{ old('note', $edit->note ?? '') }}</textarea>
                                @error('note')
                                <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-sm-12" id="{{ isset($edit) ? '' : 'RegisterCource' }}">
                    <p style="color: #101010;font-weight: bold;">Student Name : <label id="StudentName" style="font-weight: 400;"></label></p>
                    <table class="table table-bordered" id="RegisterCourceTable">
                        <thead>
                            <tr>
                                <th>Register No.</th>
                                <th>Course Name</th>
                                <th>Joining Date</th>
                            </tr>
                        </thead>
                        <tbody>

                        </tbody>
                    </table>
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
    @include('software.utils.getCourse')
    @include('software.utils.getBatchByCourseid')
    @include('software.utils.getClassByBatch')
    @include('software.utils.getShiftByBatchByCourseidByClass')


<!-- <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js"></script> -->



<script src="{{ asset('admin/assets/vendor/libs/flatpickr/flatpickr.js') }}"></script>

<script src="{{ asset('admin/assets/js/forms-pickers.js') }}"></script>
<script>
    $(document).ready(function() {
        $('.select2').select2();
        // $('.datepicker').datepicker({
        //     format: 'yyyy-mm-dd',
        //     autoclose: true,
        //     todayHighlight: true
        // });

        // $('#course_name').on('change', function() {
        //     var fee = $(this).find(':selected').data('fee');
        //     $('#fee').val(fee);
        // });

        setTimeout(function () {
            const dateInput = document.getElementById('flatpickr-date');
            if (dateInput && !dateInput.value) {
                const today = new Date();
                const yyyy = today.getFullYear();
                const mm = String(today.getMonth() + 1).padStart(2, '0');
                const dd = String(today.getDate()).padStart(2, '0');

                dateInput.value = `${dd}-${mm}-${yyyy}`; // browser required format
            }
        }, 1000);

    });

    let firstOldFee = null; // store first old value only once

    $('#course').change(function() {
        let oldFee = $('#fee').val();

        if (oldFee === "") {
            // First time: fee input is empty
            let fees = $(this).find(':selected').data('fees') || '';
            $('#fee').val(fees);
            firstOldFee = fees;
        } else {
            if (firstOldFee === null) {
                firstOldFee = oldFee; // store only once
            } else {
                let fees = $(this).find(':selected').data('fees') || '';
                $('#fee').val(fees);
            }
        }
    });

    $('#RegisterCource').hide();
    $("#register_id").keyup(function abc(){
		var register_id = $("#register_id").val();
        if(register_id){
            $.ajax({
                type:'POST',
                url:"{{ route($route . '.get-register-cource') }}",

                data: {
                    _token: '{{ csrf_token() }}',
                    id:register_id,
                },
                success : function(response)
                {
                    $('#RegisterCource').show();

                    let rows = '';

                    if (response.data && response.data.length > 0) {
                        $.each(response.data, function(index, item) {
                            rows += '<tr>';
                                rows += '<td>' + item.id + '</td>';
                                rows += '<td>' + item.course.course_name + '</td>';
                                rows += '<td>' + item.date + '</td>';
                            rows += '</tr>';
                        });
                    } else {
                        rows = '<tr><td class="text-center" colspan="3">No data available</td></tr>';
                    }

                    $('#RegisterCourceTable tbody').html(rows);
                    var studentname = response.admission.first_name+' '+response.admission.father_name+' '+response.admission.last_name;
                    $('#StudentName').text(studentname);
                }
            });
        }
	});

</script>
@if(isset($edit))
    <script>
        $(document).ready(function() {
            var registerid = $("#register_id").val();
            if(registerid){
                $.ajax({
                    type:'POST',
                    url:"{{ route($route . '.get-register-cource') }}",

                    data: {
                        _token: '{{ csrf_token() }}',
                        id:registerid,
                    },
                    success : function(response)
                    {
                        $('#RegisterCource').show();

                        let rows = '';

                        if (response.data && response.data.length > 0) {
                            $.each(response.data, function(index, item) {
                                rows += '<tr>';
                                    rows += '<td>' + item.id + '</td>';
                                    rows += '<td>' + item.course.course_name + '</td>';
                                    rows += '<td>' + item.date + '</td>';
                                rows += '</tr>';
                            });
                        } else {
                            rows = '<tr><td class="text-center" colspan="3">No data available</td></tr>';
                        }

                        $('#RegisterCourceTable tbody').html(rows);
                        var studentname = response.admission.first_name+' '+response.admission.father_name+' '+response.admission.last_name;
                        $('#StudentName').text(studentname);
                    }
                });
            }
        });

</script>
@endif

@endsection
