@extends('software.layout.app')

@php
    $page_title = isset($edit) ? 'Edit HSC Marksheet' : 'Add HSC Marksheet';
    $route = $modules['route'] ?? 'hsc-marksheet';
    $marks = old('marks', $edit->marks ?? []);
@endphp
@section('title', $page_title)

@section('page_style_file')
    <style>
        .mark-input:focus {
            border: 2px solid #0d6efd !important;
            box-shadow: 0 0 0 0.15rem rgba(13, 110, 253, 0.2);
        }
        .mark-input.is-invalid,
        .mark-input.is-invalid:focus {
            border: 2px solid #dc3545 !important;
            box-shadow: 0 0 0 0.15rem rgba(220, 53, 69, 0.2);
        }
    </style>
@endsection

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

                <div class="mt-2 mb-2">
                    <h6 class="fw-bold mb-0">Subject Marks Entry</h6>
                </div>
                <p class="text-muted small mb-3">
                    Enter marks in any <strong>7 subjects</strong> (Total 350). Leave <strong>1 subject blank</strong> — it will <strong>auto count as N/A (-)</strong>.
                    Tick <strong>Absent</strong> for AB on that subject.
                </p>

                <div class="table-responsive">
                    <table class="table table-bordered align-middle">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 7%;">Sr.</th>
                                <th>Subject</th>
                                <th style="width: 12%;">Max</th>
                                <th style="width: 18%;">Marks Obtained</th>
                                <th style="width: 12%;" class="text-center">Absent</th>
                                <th style="width: 12%;" class="text-center">N/A</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($subjects as $i => $subject)
                                @php
                                    $key = $subject['key'];
                                    $markVal = $marks[$key] ?? '';
                                    $markUpper = strtoupper(trim((string) $markVal));
                                    $rowAbsent = $markUpper === 'AB' || (bool) old("subject_absent.$key", false);
                                    $rowNa = $markUpper === 'NA' || $markUpper === '-' || (bool) old("subject_na.$key", false);
                                    if ($rowNa) {
                                        $markVal = '-';
                                        $rowAbsent = false;
                                    } elseif ($rowAbsent) {
                                        $markVal = 'AB';
                                    }
                                @endphp
                                <tr class="subject-row" data-key="{{ $key }}">
                                    <td>{{ $i + 1 }}</td>
                                    <td>{{ $subject['name'] }}</td>
                                    <td>{{ $subject['max'] }}</td>
                                    <td>
                                        <input type="text"
                                            name="marks[{{ $key }}]"
                                            class="form-control mark-input"
                                            value="{{ $markVal }}"
                                            placeholder="0"
                                            {{ ($rowAbsent || $rowNa) ? 'readonly' : '' }}>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox"
                                            class="form-check-input subject-absent"
                                            name="subject_absent[{{ $key }}]"
                                            value="1"
                                            {{ $rowAbsent && !$rowNa ? 'checked' : '' }}
                                            {{ $rowNa ? 'disabled' : '' }}>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox"
                                            class="form-check-input subject-na"
                                            name="subject_na[{{ $key }}]"
                                            value="1"
                                            {{ $rowNa ? 'checked' : '' }}>
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

            function applyRowState($row) {
                var $input = $row.find('.mark-input');
                var $absent = $row.find('.subject-absent');
                var $na = $row.find('.subject-na');

                if ($na.is(':checked')) {
                    $absent.prop('checked', false).prop('disabled', true);
                    if ($input.data('prev') === undefined) {
                        $input.data('prev', $input.val());
                    }
                    $input.val('-').prop('readonly', true);
                    return;
                }

                $absent.prop('disabled', false);

                if ($absent.is(':checked')) {
                    if ($input.data('prev') === undefined) {
                        $input.data('prev', $input.val());
                    }
                    $input.val('AB').prop('readonly', true);
                    return;
                }

                var prev = $input.data('prev');
                if (prev !== undefined) {
                    var p = String(prev).toUpperCase();
                    $input.val((p === 'AB' || p === 'NA' || p === '-') ? '' : prev);
                    $input.removeData('prev');
                }
                $input.prop('readonly', false);
            }

            $(document).on('change', '.subject-absent', function() {
                var $row = $(this).closest('.subject-row');
                if ($(this).is(':checked')) {
                    $row.find('.subject-na').prop('checked', false);
                }
                applyRowState($row);
            });

            $(document).on('change', '.subject-na', function() {
                var $row = $(this).closest('.subject-row');
                if ($(this).is(':checked')) {
                    $row.find('.subject-absent').prop('checked', false);
                }
                applyRowState($row);
            });

            function validateMarkInput($input) {
                var val = String($input.val() || '').trim().toUpperCase();
                var invalid = false;

                if (val !== '' && val !== 'AB' && val !== 'NA' && val !== '-' && $input.prop('readonly') !== true) {
                    var num = parseFloat(val);
                    if (isNaN(num) || num < 0 || num > 50) {
                        invalid = true;
                    }
                }

                $input.toggleClass('is-invalid', invalid);
                return !invalid;
            }

            $(document).on('blur', '.mark-input', function() {
                validateMarkInput($(this));
            });

            $(document).on('input', '.mark-input', function() {
                validateMarkInput($(this));
            });

            // Arrow keys move between Marks Obtained inputs
            $(document).on('keydown', '.mark-input', function(e) {
                if (e.key !== 'ArrowDown' && e.key !== 'ArrowUp' && e.key !== 'Enter') {
                    return;
                }
                e.preventDefault();
                validateMarkInput($(this));

                var $inputs = $('.mark-input:not([readonly])');
                var index = $inputs.index(this);
                if (index < 0) {
                    $inputs = $('.mark-input');
                    index = $inputs.index(this);
                }

                var nextIndex = (e.key === 'ArrowUp') ? index - 1 : index + 1;
                if (nextIndex < 0 || nextIndex >= $inputs.length) {
                    return;
                }

                $inputs.eq(nextIndex).focus().select();
            });

            function isApplicableMark($row) {
                if ($row.find('.subject-na').is(':checked')) {
                    return false;
                }
                var val = String($row.find('.mark-input').val() || '').trim().toUpperCase();
                if (val === '' || val === '-' || val === 'NA') {
                    return false;
                }
                return true;
            }

            function countApplicableSubjects() {
                var count = 0;
                $('.subject-row').each(function() {
                    if (isApplicableMark($(this))) {
                        count++;
                    }
                });
                return count;
            }

            // 7 marks filled => remaining blank subject(s) auto N/A
            function autoNaForRemainingSubjects() {
                var applicable = countApplicableSubjects();

                $('.subject-row').each(function() {
                    var $row = $(this);
                    var $na = $row.find('.subject-na');
                    var $input = $row.find('.mark-input');
                    var val = String($input.val() || '').trim().toUpperCase();
                    var isBlank = !isApplicableMark($row) && (
                        val === '' || val === '-' || val === 'NA' || $na.data('auto-na')
                    );

                    if ($na.data('auto-na') && (!isBlank || applicable < 7)) {
                        $na.prop('checked', false).removeData('auto-na');
                        if (val === '-' || val === 'NA') {
                            $input.val('');
                        }
                        applyRowState($row);
                    }
                });

                if (applicable !== 7) {
                    return;
                }

                $('.subject-row').each(function() {
                    var $row = $(this);
                    if (isApplicableMark($row)) {
                        return;
                    }
                    if ($row.find('.subject-absent').is(':checked')) {
                        return;
                    }
                    var $na = $row.find('.subject-na');
                    if ($na.is(':checked') && !$na.data('auto-na')) {
                        return; // manually marked N/A
                    }
                    $na.prop('checked', true).data('auto-na', 1);
                    $row.find('.subject-absent').prop('checked', false);
                    applyRowState($row);
                });
            }

            function showFormError(msg) {
                var $box = $('#marks-client-error');
                if (!$box.length) {
                    $box = $('<div id="marks-client-error" class="alert alert-danger py-2"></div>');
                    $('.table-responsive').before($box);
                }
                $box.html('<ul class="mb-0 ps-3"><li>' + msg + '</li></ul>').show();
            }

            function hideFormError() {
                $('#marks-client-error').hide();
            }

            $(document).on('blur input', '.mark-input', function() {
                autoNaForRemainingSubjects();
            });

            $(document).on('change', '.subject-absent, .subject-na', function() {
                var $na = $(this).closest('.subject-row').find('.subject-na');
                if ($(this).hasClass('subject-na') && $(this).is(':checked')) {
                    $na.removeData('auto-na');
                }
                autoNaForRemainingSubjects();
            });

            $('form').on('submit', function(e) {
                autoNaForRemainingSubjects();
                $('.subject-absent, .subject-na').prop('disabled', false);
                hideFormError();

                var ok = true;
                $('.mark-input').each(function() {
                    if (!validateMarkInput($(this))) {
                        ok = false;
                    }
                });

                var applicable = countApplicableSubjects();
                if (applicable > 7) {
                    ok = false;
                    showFormError('Only 7 subjects allowed (Total 350). Leave 1 subject blank — it will auto count as N/A.');
                } else if (applicable === 0) {
                    ok = false;
                    showFormError('Enter marks in at least 1 subject.');
                }

                if (!ok) {
                    e.preventDefault();
                    var $firstErr = $('.mark-input.is-invalid').first();
                    if ($firstErr.length) {
                        $firstErr.focus().select();
                    }
                }
            });

            $('.subject-row').each(function() {
                applyRowState($(this));
            });
            autoNaForRemainingSubjects();
        });
    </script>
@endsection
