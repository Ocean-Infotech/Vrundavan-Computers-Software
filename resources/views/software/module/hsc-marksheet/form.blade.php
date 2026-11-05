@extends('software.layout.app')

@php
    $page_title = isset($edit) ? 'Edit HSC Marksheet' : 'Add HSC Marksheet';
    $route = $modules['route'] ?? 'hsc-marksheet';
    $marks = old('marks', $edit->marks ?? []);
    $isAbsent = (bool) old('is_absent', $edit->is_absent ?? false);
@endphp
@section('title', $page_title)

@section('content')
    <div class="d-flex justify-content-lg-between px-1">
        @include('software.includes.breadcrumb', [
            'breadcrumbArray' => [
                ['title' => 'Other', 'url' => ''],
                ['title' => 'HSC Marksheet', 'url' => route('hsc-marksheet.index')],
                ['title' => $page_title, 'url' => ''],
            ],
            'route' => $route,
            'show_add_btn' => false,
            'show_filter_btn' => false,
            'show_back_btn' => true,
        ])
    </div>

    <div class="card my-3">
        <div class="card-body">
            <form method="post"
                action="{{ isset($edit) ? route('hsc-marksheet.update', $edit->id) : route('hsc-marksheet.store') }}">
                @csrf
                @if (isset($edit))
                    @method('PUT')
                @endif

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">HSC Student <span class="text-danger">*</span></label>
                        <select name="student_id" id="student_id" class="form-select select2" required>
                            <option value="">Select Student</option>
                            @foreach ($students as $student)
                                <option value="{{ $student->student_id }}"
                                    {{ (string) old('student_id', $edit->student_id ?? '') === (string) $student->student_id ? 'selected' : '' }}>
                                    {{ $student->name }}
                                    @if ($student->gr_no)
                                        (GR: {{ $student->gr_no }})
                                    @endif
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Roll Number</label>
                        <input type="text" name="roll_number" class="form-control {{ $errors->has('roll_number') ? 'is-invalid' : '' }}"
                            value="{{ old('roll_number', $edit->roll_number ?? '') }}">
                        @error('roll_number')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Exam Title <span class="text-danger">*</span></label>
                        <input type="text" name="exam_title" class="form-control" required
                            value="{{ old('exam_title', $edit->exam_title ?? 'FIRST TERM EXAMINATION 2026 - 27') }}">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Academic Year <span class="text-danger">*</span></label>
                        <input type="text" name="academic_year" class="form-control" required
                            value="{{ old('academic_year', $edit->academic_year ?? '2026 - 27') }}">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Standard <span class="text-danger">*</span></label>
                        <input type="text" name="standard" class="form-control" required
                            value="{{ old('standard', $edit->standard ?? '11th Commerce') }}">
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center mt-2 mb-3">
                    <h6 class="fw-bold mb-0">Subject Marks Entry</h6>
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="is_absent" name="is_absent" value="1"
                            {{ $isAbsent ? 'checked' : '' }}>
                        <label class="form-check-label fw-semibold" for="is_absent">Student Absent (Auto AB)</label>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered align-middle">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 8%;">Sr. No.</th>
                                <th>Subject</th>
                                <th style="width: 15%;">Maximum Marks</th>
                                <th style="width: 20%;">Marks Obtained</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($subjects as $i => $subject)
                                @php
                                    $markVal = $marks[$subject['key']] ?? '';
                                    if ($isAbsent) {
                                        $markVal = 'AB';
                                    }
                                @endphp
                                <tr>
                                    <td>{{ $i + 1 }}</td>
                                    <td>{{ $subject['name'] }}</td>
                                    <td>{{ $subject['max'] }}</td>
                                    <td>
                                        <input type="text"
                                            name="marks[{{ $subject['key'] }}]"
                                            class="form-control mark-input"
                                            value="{{ $markVal }}"
                                            placeholder="0"
                                            {{ $isAbsent ? 'readonly' : '' }}>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="text-end mt-3">
                    <a href="{{ route('hsc-marksheet.index') }}" class="btn btn-outline-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary">{{ isset($edit) ? 'Update' : 'Save' }}</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('page_leavel_script')
    <script>
        $(document).ready(function() {
            if ($.fn.select2) {
                $('#student_id').select2({
                    width: '100%',
                    placeholder: 'Select Student'
                });
            }

            function applyAbsentState(isAbsent) {
                $('.mark-input').each(function() {
                    var $input = $(this);
                    if (isAbsent) {
                        if ($input.data('prev') === undefined) {
                            $input.data('prev', $input.val());
                        }
                        $input.val('AB').prop('readonly', true);
                    } else {
                        var prev = $input.data('prev');
                        $input.val(prev !== undefined ? prev : '').prop('readonly', false);
                        $input.removeData('prev');
                    }
                });
            }

            $('#is_absent').on('change', function() {
                applyAbsentState($(this).is(':checked'));
            });

            if ($('#is_absent').is(':checked')) {
                applyAbsentState(true);
            }
        });
    </script>
@endsection
