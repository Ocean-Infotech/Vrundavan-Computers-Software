@extends('software.layout.app')

@php
    $page_title = isset($modules['title']) ? $modules['title'] : 'Result Master';
    $route = isset($modules['route']) ? $modules['route'] : 'result';
@endphp
@section('title', $page_title)

@section('content')
    <div class="d-flex justify-content-lg-between px-1">
        @include('software.includes.breadcrumb', [
            'breadcrumbArray' => [['title' => 'Result Master', 'url' => route('result.index')], ['title' => isset($edit) ? 'Edit' : 'Create', 'url' => '']],
            'route' => $route,
            'show_add_btn' => false,
            'show_filter_btn' => false,
            'show_back_btn' => true,
        ])
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card shadow">
                <div class="card-body">
                    <form action="{{ isset($edit) ? route('result.update', $edit->id) : route('result.store') }}" method="POST">
                        @csrf
                        @if (isset($edit))
                            @method('PUT')
                        @endif

                        <div class="row">
                            <!-- Course selection -->
                            <div class="col-md-3 col-sm-12 mb-3">
                                <div class="form-group">
                                    <label class="form-label">Course <span class="text-danger">*</span></label>
                                    <select class="form-control select2 @error('course_id') is-invalid @enderror" name="course_id" id="courseSelect" required>
                                        <option value="">Select Course</option>
                                        @foreach ($courses as $course)
                                            <option value="{{ $course->id }}" {{ (isset($edit) && $edit->course_id == $course->id) || old('course_id') == $course->id ? 'selected' : '' }}>
                                                {{ $course->course_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('course_id')
                                        <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                                    @enderror
                                </div>
                            </div>

                            <!-- Batch selection -->
                            <div class="col-md-3 col-sm-12 mb-3">
                                <div class="form-group">
                                    <label class="form-label">Batch <span class="text-danger">*</span></label>
                                    <select class="form-control select2 @error('batch_id') is-invalid @enderror" name="batch_id" id="batchSelect" required>
                                        <option value="">Select Batch</option>
                                        @foreach ($batches as $batch)
                                            <option value="{{ $batch->id }}" {{ (isset($edit) && $edit->batch_id == $batch->id) || old('batch_id') == $batch->id ? 'selected' : '' }}>
                                                {{ $batch->batch_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('batch_id')
                                        <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                                    @enderror
                                </div>
                            </div>

                            <!-- Class selection -->
                            <div class="col-md-3 col-sm-12 mb-3">
                                <div class="form-group">
                                    <label class="form-label">Class <span class="text-danger">*</span></label>
                                    <select class="form-control select2 @error('class_ids') is-invalid @enderror" name="class_ids[]" id="classSelect" multiple="multiple" data-placeholder="Select Classes" required>
                                    </select>
                                    @error('class_ids')
                                        <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                                    @enderror
                                </div>
                            </div>

                            <!-- Semester -->
                            <div class="col-md-3 col-sm-12 mb-3">
                                <div class="form-group">
                                    <label class="form-label">Semester <span class="text-danger">*</span></label>
                                    <select class="form-control select2 @error('semester') is-invalid @enderror" name="semester" id="semesterSelect" required>
                                        <option value="">Select Semester</option>
                                    </select>
                                    @error('semester')
                                        <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                                    @enderror
                                </div>
                            </div>

                            <!-- Total Marks -->
                            <div class="col-md-12 col-sm-12 mb-3">
                                <div class="form-group">
                                    <label class="form-label">Total Max Marks <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control num_only @error('total_marks') is-invalid @enderror" name="total_marks" value="{{ isset($edit) ? $edit->total_marks : old('total_marks') }}" min="1" placeholder="e.g. 600" required>
                                    @error('total_marks')
                                        <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-12 mt-3">
                                <button type="submit" class="btn btn-primary me-2">Submit</button>
                                <a href="{{ route('result.index') }}" class="btn btn-label-secondary">Cancel</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('page_leavel_script')
    <script>
        var courseSemesters = {
            @foreach($courses as $course)
                "{{ $course->id }}": {{ $course->semester ?? 8 }},
            @endforeach
        };

        $(document).ready(function() {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            var courseSelect = $('#courseSelect');
            var batchSelect = $('#batchSelect');
            var classSelect = $('#classSelect');
            var semesterSelect = $('#semesterSelect');

            courseSelect.on('change', function() {
                let courseId = $(this).val();
                
                // Keep the current selected batch id if we are triggering on load
                let selectedBatch = batchSelect.val();
                
                batchSelect.empty().append('<option value="">Select Batch</option>').val('').trigger('change.select2');
                classSelect.empty().val([]).trigger('change.select2');

                // Update Semesters based on selected course
                let semCount = courseSemesters[courseId] || 0;
                let selectedSem = "{{ isset($edit) ? $edit->semester : (old('semester') ?? '') }}";

                semesterSelect.empty().append('<option value="">Select Semester</option>');
                for (let i = 1; i <= semCount; i++) {
                    semesterSelect.append(`<option value="${i}">Semester ${i}</option>`);
                }
                semesterSelect.trigger('change.select2');

                if (selectedSem && selectedSem <= semCount) {
                    semesterSelect.val(selectedSem).trigger('change.select2');
                }

                if (courseId) {
                    $.ajax({
                        type: 'POST',
                        url: "{{ route('get-batch') }}",
                        dataType: 'json',
                        data: {
                            course_id: courseId
                        },
                        success: function(response) {
                            if ((response.status === true || response.status === "true") && response.data) {
                                let options = '<option value="">Select Batch</option>';
                                response.data.forEach(function(item) {
                                    options += `<option value="${item.id}">${item.name}</option>`;
                                });
                                batchSelect.html(options).trigger('change.select2');

                                // Re-select edit/old/previously selected value
                                @if(isset($edit))
                                    batchSelect.val("{{ $edit->batch_id }}").trigger('change');
                                @elseif(old('batch_id'))
                                    batchSelect.val("{{ old('batch_id') }}").trigger('change');
                                @else
                                    if (selectedBatch) {
                                        batchSelect.val(selectedBatch).trigger('change');
                                    }
                                @endif
                            }
                        }
                    });
                }
            });

            // Batch change AJAX to load Classes
            batchSelect.on('change', function() {
                let batchId = $(this).val();
                let selectedClass = classSelect.val();
                classSelect.empty().val([]).trigger('change.select2');

                if (batchId) {
                    $.ajax({
                        type: 'POST',
                        url: "{{ route('get-class-bybatch') }}",
                        dataType: 'json',
                        data: {
                            batch_id: batchId
                        },
                        success: function(response) {
                            if ((response.status === true || response.status === "true") && response.data) {
                                let options = '';
                                response.data.forEach(function(item) {
                                    options += `<option value="${item.id}">${item.name}</option>`;
                                });
                                classSelect.html(options).trigger('change.select2');

                                // Re-select edit/old/previously selected value
                                @if(isset($edit))
                                    classSelect.val(["{{ $edit->class_id }}"]).trigger('change.select2');
                                @elseif(old('class_ids'))
                                    classSelect.val({!! json_encode(old('class_ids', [])) !!}).trigger('change.select2');
                                @else
                                    if (selectedClass) {
                                        classSelect.val(selectedClass).trigger('change.select2');
                                    }
                                @endif
                            }
                        }
                    });
                }
            });

            // Trigger change if editing to pre-populate batches, classes and semesters
            @if(isset($edit) || old('course_id'))
                courseSelect.trigger('change');
            @endif
        });
    </script>
@endsection
