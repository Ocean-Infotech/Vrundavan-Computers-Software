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
                            <label class="form-label">Select Student <span class="text-danger">*</span></label>
                            @if (!isset($edit))
                                <div class="card p-2" style="max-height: 250px; overflow-y: auto; border: 1px solid #ccc;">
                                    <input type="text" id="student_search" class="form-control mb-2" placeholder="Search student...">
                                    <div id="student_checkbox_list">
                                        @foreach ($students as $student)
                                            @php
                                                $studentText = $student->id . ' - ' . trim(($student->first_name ?? '') . ' ' . ($student->last_name ?? '') . ' ' . ($student->father_name ?? ''));
                                            @endphp
                                            <div class="form-check student-checkbox-item mb-1">
                                                <input class="form-check-input student-checkbox" type="checkbox" name="admission_id[]" value="{{ $student->id }}" id="student_{{ $student->id }}"
                                                    @if (is_array(old('admission_id')) && in_array($student->id, old('admission_id'))) checked @endif>
                                                <label class="form-check-label" for="student_{{ $student->id }}">
                                                    {{ $studentText }}
                                                </label>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @else
                                <select name="admission_id"
                                    class="form-control select2 @error('admission_id') is-invalid @enderror" required>
                                    <option value="" disabled>Select Student</option>
                                    @foreach ($students as $student)
                                        <option value="{{ $student->id }}"
                                            @if ($edit->admission_id == $student->id) selected
                                            @elseif (old('admission_id') == $student->id) selected @endif>
                                            {{ $student->id }} -
                                            {{ trim(($student->first_name ?? '') . ' ' . ($student->last_name ?? '') . ' ' . ($student->father_name ?? '')) }}
                                        </option>
                                    @endforeach
                                </select>
                            @endif
                            @error('admission_id')
                                <span class="invalid-feedback d-block"><strong>{{ $message }}</strong></span>
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

                    <!-- Faculty User Dropdown -->
                    <div class="col-md-4 col-sm-12 mb-3">
                        <div class="form-group">
                            <label class="form-label">Faculty User <span class="text-danger">*</span></label>
                            <select name="faculty_id" class="form-control select2 @error('faculty_id') is-invalid @enderror"
                                required>
                                <option value="" disabled {{ !isset($edit) ? 'selected' : '' }}>Select Faculty
                                </option>
                                @foreach ($faculties as $faculty)
                                    <option value="{{ $faculty->id }}"
                                        @if (isset($edit) && $edit->faculty_id == $faculty->id) selected
                                        @elseif(old('faculty_id') == $faculty->id) selected @endif>
                                        {{ $faculty->name }} ({{ $faculty->email ?? '' }})
                                    </option>
                                @endforeach
                            </select>
                            @error('faculty_id')
                                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>

                    <!-- Complaint Textarea -->
                    <div class="col-12 mb-3">
                        <div class="form-group">
                            <label class="form-label">Complaint Details <span class="text-danger">*</span></label>
                            <textarea id="complaint" name="complaint" rows="3" class="form-control @error('complaint') is-invalid @enderror"
                                placeholder="Enter complaint details..." required>{{ isset($edit->complaint) ? $edit->complaint : old('complaint') }}</textarea>
                            @error('complaint')
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

@section('page_script_file')
    <script src="{{ asset('admin/assets/vendor/libs/autosize/autosize.js') }}"></script>
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

            $('#student_search').on('keyup', function() {
                let value = $(this).val().toLowerCase();
                $('#student_checkbox_list .student-checkbox-item').filter(function() {
                    $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
                });
            });

            $('form').on('submit', function(e) {
                if ($('.student-checkbox').length > 0 && $('.student-checkbox:checked').length === 0) {
                    e.preventDefault();
                    alert('Please select at least one student.');
                }
            });

            const complaintTextarea = document.querySelector('#complaint');
            if (complaintTextarea && typeof autosize !== 'undefined') {
                autosize(complaintTextarea);
            }
        });
    </script>
@endsection
