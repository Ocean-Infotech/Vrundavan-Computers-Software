@extends('software.layout.app')

@php
    $page_title = 'Marksheet Issue';
    $folder_path = 'software.module.marksheet-issue';
    $route = 'marksheet-issue';
@endphp

@section('title', $page_title)

@section('page_style_file')
    <!--datatable css-->
    <link rel="stylesheet" href="{{ asset('admin/assets/cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css') }}">
    <link rel="stylesheet" href="{{ asset('admin/assets/cdn.datatables.net/responsive/2.2.9/css/responsive.bootstrap.min.css') }}">
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

    <div class="row my-3" id="filter_section">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 col-sm-12 mb-3">
                            <div class="form-group">
                                <label class="form-label">Filter by Course</label>
                                <select id="course_id" name="course_id"
                                    class="form-control search_by_course select2 select_filter"
                                    data-append="search_by_course" autofocus>
                                    <option value="">Filter by Course</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-3 col-sm-12 mb-3">
                            <div class="form-group">
                                <label class="form-label">Filter by Batch</label>
                                <select id="batch_id" name="batch_id"
                                    class="form-control search_by_batch select2 select_filter" 
                                    data-append="search_by_batch" autofocus>
                                    <option value="">Filter by Batch</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-3 col-sm-12 mb-3">
                            <div class="form-group">
                                <label class="form-label">Filter by Class</label>
                                <select id="class_id" name="class_id"
                                    class="form-control search_by_class select2 select_filter" 
                                    data-append="search_by_class" autofocus>
                                    <option value="">Filter by Class</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-3 col-sm-12 mb-3">
                            <div class="form-group">
                                <label class="form-label">Filter by Semester</label>
                                <select id="semester_id" name="semester_id"
                                    class="form-control search_by_semester select2 select_filter"
                                    data-append="search_by_semester" autofocus>
                                    <option value="">Filter by Semester</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Placeholder shown before any filter is selected --}}
    <div id="no-filter-placeholder" class="row my-3">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body text-center py-5 text-muted">
                    <i class="bx bx-filter-alt" style="font-size: 3rem;"></i>
                    <p class="mt-2 mb-0 fs-6">Please select a filter (Course / Batch / Class / Semester) to view students.</p>
                </div>
            </div>
        </div>
    </div>

    {{-- DataTable — hidden until filter selected --}}
    <div id="datatable-wrapper" class="row my-3 d-none">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="yajra-datatables" class="table table-striped table-hover dt-responsive" style="width:100%">
                            <thead>
                                <tr>
                                    @foreach ($columns as $item)
                                        <th class="{{ $item->className ?? '' }}">
                                            {!! $item->td_label ?: $item->name !!}
                                        </th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
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
@endsection

@section('page_leavel_script')
    <script type="text/javascript">
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        var dtable = null;

        $(document).ready(function() {
            $('.select2').select2();

            var isFilterActive = false;
            var dtableInitialized = false;

            function initOrReloadTable() {
                isFilterActive = !!($('#course_id').val() || $('#batch_id').val() || $('#class_id').val() || $('#semester_id').val());

                if (!isFilterActive) {
                    // No filter — show placeholder, hide table
                    $('#no-filter-placeholder').removeClass('d-none');
                    $('#datatable-wrapper').addClass('d-none');
                    return;
                }

                // Filter active — hide placeholder, show table
                $('#no-filter-placeholder').addClass('d-none');
                $('#datatable-wrapper').removeClass('d-none');

                if (!dtableInitialized) {
                    // First time: initialize DataTable
                    dtableInitialized = true;
                    dtable = $('#yajra-datatables').DataTable({
                        processing: true,
                        serverSide: true,
                        searching: true,
                        ordering: false,
                        ajax: {
                            url: "{{ route('marksheet-issue.index') }}",
                            type: "GET",
                            data: function(d) {
                                d.course_id   = $('#course_id').val();
                                d.batch_id    = $('#batch_id').val();
                                d.class_id    = $('#class_id').val();
                                d.semester_id = $('#semester_id').val();
                            }
                        },
                        columns: {!! json_encode($columns) !!},
                        language: {
                            searchPlaceholder: 'Search...',
                            emptyTable: 'No students found for the selected filter.',
                            zeroRecords: 'No students found for the selected filter.',
                        },
                    });
                } else {
                    // Already initialized — just redraw with new filters
                    dtable.draw();
                }
            }

            // Redraw table on filter change
            $('.select_filter').on('change', function() {
                initOrReloadTable();
            });

            // Handle checkbox toggles for marksheets
            $(document).on('change', '.marksheet-toggle-checkbox', function() {
                let checkbox = $(this);
                let admission_id = checkbox.data('admission-id');
                let semester = checkbox.data('semester');
                let is_issued = checkbox.is(':checked') ? 1 : 0;

                $.ajax({
                    url: "{{ route('marksheet-issue.toggle') }}",
                    type: "POST",
                    dataType: 'json',
                    data: {
                        _token: "{{ csrf_token() }}",
                        admission_id: admission_id,
                        semester: semester,
                        is_issued: is_issued
                    },
                    success: function(response) {
                        if (response.success) {
                            toastr.success('Marksheet status updated successfully.');
                        } else {
                            toastr.error('Failed to update marksheet status.');
                            checkbox.prop('checked', !is_issued);
                        }
                    },
                    error: function(xhr) {
                        console.error(xhr);
                        toastr.error('Error updating marksheet status.');
                        checkbox.prop('checked', !is_issued);
                    }
                });
            });

            // Dynamic Chaining: Course -> Batch -> Class & Semester
            $(document).on('change', '#course_id', function() {
                let courseId = $(this).val();

                // Reset Batch, Class, Semester selects
                $('#batch_id').empty().append('<option value="">Filter by Batch</option>').val('').trigger('change');
                $('#class_id').empty().append('<option value="">Filter by Class</option>').val('').trigger('change');
                $('#semester_id').empty().append('<option value="">Filter by Semester</option>').val('').trigger('change');

                if (courseId) {
                    // Fetch Batch list
                    $.ajax({
                        type: 'POST',
                        url: "{{ route('get-batch') }}",
                        dataType: 'json',
                        data: {
                            course_id: courseId
                        },
                        success: function(response) {
                            if ((response.status === true || response.status === "true") && response.data) {
                                let options = '<option value="">Filter by Batch</option>';
                                response.data.forEach(function(item) {
                                    options += `<option value="${item.id}">${item.name}</option>`;
                                });
                                $('#batch_id').html(options).trigger('change');
                            }
                        }
                    });

                    // Fetch Semester list
                    $.ajax({
                        type: 'POST',
                        url: "{{ route('get-semester') }}",
                        dataType: 'json',
                        data: {
                            course_id: courseId
                        },
                        success: function(response) {
                            if ((response.status === true || response.status === "true") && response.data) {
                                let options = '<option value="">Filter by Semester</option>';
                                response.data.forEach(function(item) {
                                    options += `<option value="${item.semester}">Sem ${item.semester}</option>`;
                                });
                                $('#semester_id').html(options).trigger('change');
                            }
                        }
                    });
                }
            });

            $(document).on('change', '#batch_id', function() {
                let batchId = $(this).val();
                
                // Reset Class select
                $('#class_id').empty().append('<option value="">Filter by Class</option>').val('').trigger('change');

                if (batchId) {
                    // Fetch Class list
                    $.ajax({
                        type: 'POST',
                        url: "{{ route('get-class-bybatch') }}",
                        dataType: 'json',
                        data: {
                            batch_id: batchId
                        },
                        success: function(response) {
                            if ((response.status === true || response.status === "true") && response.data) {
                                let options = '<option value="">Filter by Class</option>';
                                response.data.forEach(function(item) {
                                    options += `<option value="${item.id}">${item.name}</option>`;
                                });
                                $('#class_id').html(options).trigger('change');
                            }
                        }
                    });
                }
            });
        });
    </script>

    @include('software.utils.getCourse')
@endsection
