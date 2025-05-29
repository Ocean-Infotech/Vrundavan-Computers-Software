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
    {{-- <link href="{{ asset('public/assets/admin/plugins/datatable/dataTables.bootstrap4.min.css') }}" rel="stylesheet" type="text/css"> --}}
    <!--datatable css-->
    <link rel="stylesheet" href="{{ asset('admin/assets/cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css') }}">
    <!--datatable responsive css-->
    <link rel="stylesheet"
        href="{{ asset('admin/assets/cdn.datatables.net/responsive/2.2.9/css/responsive.bootstrap.min.css') }}">

    <link rel="stylesheet" href="{{ asset('admin/assets/cdn.datatables.net/buttons/2.2.2/css/buttons.dataTables.min.css') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
@endsection

@section('content')
    <div class="d-flex justify-content-lg-between px-1">
        @include('software.includes.breadcrumb', [
            'breadcrumbArray' => [['title' => $page_title, 'url' => '']],
            'route' => $route,
            'show_add_btn' => false,
            'show_filter_btn' => false,
            'show_export_btns' => false,
            'show_excal_btn' => false,
            'show_print_btn' => false,
            'show_back_btn' => false,
        ])
    </div>

    <!-- @include('software.partials.flash_messages') -->
    <div class="row my-3">
        <div class="row mb-3">
            <div class="col-md-3 col-sm-12 mb-3">
                <div class="form-group">
                    <label class="form-label">Select Course </label>
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
            <div class="col-md-3 col-sm-12 mb-3">
                <div class="form-group">
                    <label class="form-label">Select Batch </label>
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
            <div class="col-md-3 col-sm-12 mb-3">
                <div class="form-group">
                    <label for="date_range" class="form-label">Select Subject </label>
                    <select id="subject" name="subject_name" class="form-control select2">
                        <option value="">Select Subject</option>
                        @foreach ($subjects as $subject)
                            <option value="{{ $subject }}">{{ $subject }}</option>
                        @endforeach
                    </select>


                </div>
            </div>
            <div class="col-md-3 col-sm-12 mb-3">
                <div class="form-group">
                    <label for="date" class="form-label">Select Date</label>
                    <input type="date" id="date" name="date" class="form-control" />
                </div>
            </div>
        </div>

        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="yajra-datatables" class="display table table-hover" style="">
                            @if (isset($columns))
                                <thead>
                                    <tr>
                                        @foreach ($columns as $item)
                                            <th class="{{ $item?->className ?? '' }}">
                                                {{ ucfirst($item?->td_label ?? $item?->name) ?? '' }}</th>
                                        @endforeach
                                    </tr>
                                </thead>
                            @endif
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('page_script_file')
    <!--datatable js-->
    <script src="{{ asset('admin/assets/cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('admin/assets/cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js') }}"></script>
    <script src="{{ asset('admin/assets/cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js') }}"></script>
    {{-- <script src="{{ asset('admin/assets/cdn.datatables.net/buttons/2.2.2/js/dataTables.buttons.min.js') }}"></script>
<script src="{{ asset('admin/assets/cdn.datatables.net/buttons/2.2.2/js/buttons.print.min.js') }}"></script>
<script src="{{ asset('admin/assets/cdn.datatables.net/buttons/2.2.2/js/buttons.html5.min.js') }}"></script>
<script src="{{ asset('admin/assets/cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js') }}"></script>
<script src="{{ asset('admin/assets/cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js') }}"></script>
<script src="{{ asset('admin/assets/cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js') }}"></script> --}}
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    {{-- <script src="{{ asset('admin/assets/js/pages/datatables.init.js') }}"></script> --}}
@endsection

@section('page_leavel_script')
    <script>
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        let dtable;

        $(document).ready(function() {
            dtable = $('#yajra-datatables').DataTable({
                processing: true,
                serverSide: true,
                order: [
                    [1, 'desc']
                ],
                searching: false,
                ajax: {
                    url: "{{ route($route . '.index') }}",
                    type: "GET",
                    data: function(data) {
                        data.date = $('#date').val();
                        data.course_id = $('#course').val(); // ✅ Add this line
                        data.batch_id = $('#batch').val();
                        data.subject_name = $('#subject').val();
                        data.search = $('input[type="search"]').val();
                    }
                },
                columns: {!! json_encode($columns) !!},
                language: {
                    searchPlaceholder: 'Search...'
                }
            });

            $('#filter-btn').on('click', function() {
                if (!$('#date').val() && !$('#batch').val() && !$('#subject').val()) {
                    toastr.warning("Please select at least one filter before searching.");
                    return;
                }
                dtable.draw();
            });

            // Redraw on filter change
            $(document).on('change', '#date, #batch, #subject, #course', function() {
                dtable.draw();
            });

            $(document).on('keyup', 'input[type="search"]', function() {
                dtable.draw();
            });

            // Marks input AJAX save + validation
            $(document).on('change', '.mark-input', function() {
                let input = $(this);
                let testId = input.data('id');
                let marks = input.val();
                let max = input.attr('max');

                if (parseFloat(marks) > parseFloat(max)) {
                    toastr.error("Marks should not exceed maximum (" + max + ")");
                    input.val('');
                    return;
                }

                $.ajax({
                    url: "{{ route('marks.store') }}",
                    type: "POST",
                    data: {
                        _token: '{{ csrf_token() }}',
                        test_id: testId,
                        marks: marks
                    },
                    success: function(response) {
                        toastr.success(response.message);
                    },
                    error: function() {
                        toastr.error('Error while saving marks.');
                    }
                });
            });
        });
    </script>

    @include('software.utils.getCourse')
    @include('software.utils.getBatchByCourseid')
    @include('software.includes.script-delete-record')
    @include('software.includes.script-restore-record')
    @include('software.includes.script-update-status')
@endsection
