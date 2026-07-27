@extends('software.layout.app')

@php
    $page_title = isset($modules['title']) ? $modules['title'] : null;
    $folder_path = isset($modules['folder_path']) ? $modules['folder_path'] : null;
    $route = isset($modules['route']) ? $modules['route'] : null;
    $permission_prefix = isset($modules['permission_prefix']) ? $modules['permission_prefix'] : null;
@endphp
@section('title', $page_title)

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

    <div class="card shadow">
        <div class="card-body">
            <form
                action="{{ isset($edit) && $edit?->id ? route($route . '.update', [$edit?->id]) : route($route . '.store') }}"
                method="POST">
                @csrf
                @isset($edit)
                    @method('PUT')
                    <input type="hidden" name="id" value="{{ $edit?->id ?? '' }}" />
                @endisset

                <div class="row">
                    <!-- Select Student -->
                    <div class="col-md-4 col-sm-12 mb-3">
                        <div class="form-group">
                            <label class="form-label">GR No. <span class="text-danger">*</span></label>
                            <select id="admission_id" name="admission_id"
                                class="form-control select2 @error('admission_id') is-invalid @enderror" required>
                                <option value="" disabled {{ !isset($edit) ? 'selected' : '' }}>GR No.</option>
                                @foreach ($students as $student)
                                    @php
                                        $totalSemesters = 0;
                                        $courseReg = $student->courses->first();
                                        if ($courseReg && $courseReg->course) {
                                            $totalSemesters = (int) $courseReg->course->semester;
                                        }
                                    @endphp
                                    <option value="{{ $student->id }}" data-gr-no="{{ $student->gr_no }}" data-semesters="{{ $totalSemesters }}"
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

                    <!-- Semester -->
                    <div class="col-md-4 col-sm-12 mb-3">
                        <div class="form-group">
                            <label class="form-label">Semester <span class="text-danger">*</span></label>
                            <select name="semester" class="form-control select2 @error('semester') is-invalid @enderror" required>
                                <option value="" disabled {{ !isset($edit) ? 'selected' : '' }}>Select Semester</option>
                                @if (isset($edit))
                                    @php
                                        $totalSemesters = 10;
                                        $courseReg = $edit->admission->courses->first();
                                        if ($courseReg && $courseReg->course) {
                                            $totalSemesters = (int) $courseReg->course->semester;
                                        }
                                    @endphp
                                    @for ($i = 1; $i <= $totalSemesters; $i++)
                                        <option value="{{ $i }}" {{ $edit->semester == $i ? 'selected' : '' }}>
                                            Semester {{ $i }}
                                        </option>
                                    @endfor
                                @endif
                            </select>
                            @error('semester')
                                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>

                    <!-- Date -->
                    <div class="col-md-4 col-sm-12 mb-3">
                        <div class="form-group">
                            <label class="form-label">Date <span class="text-danger">*</span></label>
                            <input type="date" name="date" class="form-control @error('date') is-invalid @enderror"
                                value="{{ isset($edit->date) ? \Carbon\Carbon::parse($edit->date)->format('Y-m-d') : old('date', date('Y-m-d')) }}"
                                required>
                            @error('date')
                                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>

                    <!-- Series -->
                    <div class="col-md-4 col-sm-12 mb-3">
                        <div class="form-group">
                            <label class="form-label">Series</label>
                            <input type="text" name="series" class="form-control @error('series') is-invalid @enderror"
                                value="{{ isset($edit->series) ? $edit->series : old('series') }}" placeholder="Enter Series">
                            @error('series')
                                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>

                    <!-- Note -->
                    <div class="col-12 mb-3">
                        <div class="form-group">
                            <label class="form-label">Note</label>
                            <textarea id="note" name="note" rows="3" class="form-control @error('note') is-invalid @enderror"
                                placeholder="Enter note...">{{ isset($edit->note) ? $edit->note : old('note') }}</textarea>
                            @error('note')
                                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-12 text-center mt-3">
                        <button type="submit" class="btn btn-success me-2">
                            {{ isset($edit) ? 'Update' : 'Submit' }}
                        </button>
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

            // Auto-populate semester dropdown based on selected student's course semesters
            $('#admission_id').on('change', function() {
                let semesters = $(this).find(':selected').data('semesters');
                let semesterSelect = $('select[name="semester"]');
                let selectedVal = semesterSelect.val() || "{{ isset($edit) ? $edit->semester : old('semester') }}";
                
                semesterSelect.empty().append('<option value="" disabled selected>Select Semester</option>');
                
                if (semesters) {
                    for (let i = 1; i <= semesters; i++) {
                        let selected = (selectedVal == i) ? 'selected' : '';
                        semesterSelect.append(`<option value="${i}" ${selected}>Semester ${i}</option>`);
                    }
                }
                
                // Notify Select2 to update the UI
                semesterSelect.trigger('change');
            });

            // Prevent opening semester dropdown if no student/GR No is selected
            $('select[name="semester"]').on('select2:opening', function(e) {
                if (!$('#admission_id').val()) {
                    e.preventDefault();
                    if (typeof toastr !== 'undefined') {
                        toastr.warning('Please select GR No. first.');
                    } else {
                        alert('Please select GR No. first.');
                    }
                }
            });

            // Trigger change on load if student is already selected
            if ($('#admission_id').val()) {
                $('#admission_id').trigger('change');
            }

        });
    </script>
@endsection
