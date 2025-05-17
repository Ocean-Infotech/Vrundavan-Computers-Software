@extends('admin.layout.app')

@php
    $page_title = $modules['title'] ?? null;
    $folder_path = $modules['folder_path'] ?? null;
    $route = $modules['route'] ?? null;
    $permisstion_prefix = $modules['permisstion_prefix'] ?? null;
@endphp

@section('title', $page_title)

@section('page_style_file')
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
                        @enderror
                    </div>

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
                        @enderror
                    </div>

                    {{-- Date --}}
                    <div class="col-md-4 mb-3">
                        <label for="date">Date <span class="text-danger">*</span></label>
                        <input type="date" name="date" id="date"
                            class="form-control @error('date') is-invalid @enderror"
                            value="{{ old('date', $edit->date ?? '') }}">
                        @error('date')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
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
@endsection

@section('page_leavel_script')
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

            $('#register_id').on('change', function() {
                const registerId = $(this).val();
                const _url = $(this).find('option:selected').attr('data-url');

                if (registerId && _url) {
                    // prevent duplicate fetch if the same id is selected (optional)
                    if (registerId === previousRegisterId) return;

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
