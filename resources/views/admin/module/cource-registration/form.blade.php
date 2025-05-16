@extends('admin.layout.app')

@php
    $page_title = isset($modules['title']) ? $modules['title'] : null;
    $folder_path = isset($modules['folder_path']) ? $modules['folder_path'] : null;
    $route = isset($modules['route']) ? $modules['route'] : null;
    $permisstion_prefix = isset($modules['permisstion_prefix']) ? $modules['permisstion_prefix'] : null;
    $i = 0;
@endphp
@section('title', $page_title)

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
                                    {{ old('register_id', $edit->register_id ?? '') == $edu->id ? 'selected' : '' }}>
                                    {{ $edu->id }}
                                </option>
                            @endforeach
                        </select>
                        @error('register_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Course Name --}}
                    <div class="col-md-4 mb-3">
                        <label for="course_id" class="form-label">
                            Course <span class="text-danger">*</span>
                        </label>
                        <select name="course_id" id="course_id"
                            class="form-control select2 @error('course_id') is-invalid @enderror">
                            <option value="">-- Select Course --</option>
                            @foreach ($courses as $course)
                                <option value="{{ $course->id }}" data-fee="{{ $course->course_fees }}"
                                    {{ old('course_id', $edit->course_id ?? '') == $course->id ? 'selected' : '' }}>
                                    {{ $course->course_name }}
                                </option>
                            @endforeach
                        </select>
                        @error('course_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Fee (auto-filled) --}}
                    <div class="col-md-4 mb-3">
                        <label for="fee" class="form-label">
                            Fee <span class="text-danger">*</span>
                        </label>
                        <input type="number" name="fee" id="fee"
                            class="form-control @error('fee') is-invalid @enderror" placeholder="Auto-filled from course"
                            value="{{ old('fee', $edit->fee ?? '') }}">
                        @error('fee')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Date --}}
                    <div class="col-md-4 mb-2">
                        <div class="form-group">
                            <label for="date">Date <span class="text-danger">*</span></label>
                            <input type="date" name="date" id="date"
                                class="form-control  @error('date') is-invalid @enderror"
                                value="{{ old('date', $edit->date ?? '') }}">
                            @error('date')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    {{-- Note --}}
                    <div class="col-md-4 mb-3">
                        <label for="note" class="form-label">Note</label>
                        <textarea name="note" id="note" rows="3" class="form-control @error('note') is-invalid @enderror"
                            placeholder="Enter Note">{{ old('note', $edit->note ?? '') }}</textarea>
                        @error('note')
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
@endsection

@section('page_leavel_script')
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


        });

        $(function() {
            // Initialize Select2 + Datepicker globally
            $('.select2').select2({
                width: '100%',
                placeholder: 'Choose an option',
                allowClear: true
            });

            // Cache selectors
            var $courseSelect = $('#course_id');
            var $feeInput = $('#fee');

            // On change, read data-fee and set the fee input
            function updateFee() {
                var fee = $courseSelect.find('option:selected').data('fee') || '';
                console.log('Auto-fill fee:', fee);
                $feeInput.val(fee);
            }

            $courseSelect
                .on('change', updateFee)
                .on('select2:select select2:clear', updateFee);

            // Run once on load (for edit)
            updateFee();
        });
    </script>
@endsection
