@extends('software.layout.app')

@php
    $page_title = isset($modules['title']) ? $modules['title'] : null;
    $folder_path = isset($modules['folder_path']) ? $modules['folder_path'] : null;
    $route = isset($modules['route']) ? $modules['route'] : null;
    $permisstion_prefix = isset($modules['permisstion_prefix']) ? $modules['permisstion_prefix'] : null;
    $i = 0;
    $exportColumns = $availableExportColumns ?? [];
    $defaultColumns = $defaultExportColumns ?? array_keys($exportColumns);
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
@endsection

@section('content')
    <div class="d-flex justify-content-lg-between px-1">
        @include('software.includes.breadcrumb', [
            'breadcrumbArray' => [['title' => $page_title, 'url' => '']],
            'route' => $route,
            'show_add_btn' => false,
            'show_api_attendance' =>
                isset($modules) && isset($modules['permission_show_api_attendance'])
                    ? $modules['permission_show_api_attendance']
                    : false,
            'show_filter_btn' => false,
            'show_export_btns' => true,
            'show_excal_btn' => true,
            'show_print_btn' => true,
            'show_classwise_attendance_btn' => true,
            'show_back_btn' => false,
        ])
    </div>

    <!-- @include('software.partials.flash_messages') -->
    <div class="row my-3">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="yajra-datatables" class="table table-hover dt-responsive">

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
    <input type="hidden" id="admission_id" value="{{ $admission_id ?? '' }}">

    <div class="modal fade" id="classwiseAttendanceModal" tabindex="-1" aria-labelledby="classwiseAttendanceModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="classwiseAttendanceModalLabel">Classwise Attendance</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="classwiseAttendanceForm">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Select Course <span class="text-danger">*</span></label>
                            <select id="classwise_course_id" name="filter_course"
                                class="form-control select2" required>
                                <option value="">Select Course</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Select Batch <span class="text-danger">*</span></label>
                            <select id="classwise_batch_id" name="filter_batch"
                                class="form-control select2" required disabled>
                                <option value="">Select Batch</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Select Class <span class="text-danger">*</span></label>
                            <select id="classwise_class_id" name="filter_class"
                                class="form-control select2" required disabled>
                                <option value="">Select Class</option>
                            </select>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Month <span class="text-danger">*</span></label>
                                <select id="classwise_month" name="month" class="form-control select2" required>
                                    @for ($m = 1; $m <= 12; $m++)
                                        <option value="{{ $m }}" {{ now()->month == $m ? 'selected' : '' }}>
                                            {{ \Carbon\Carbon::createFromDate(now()->year, $m, 1)->format('F') }}
                                        </option>
                                    @endfor
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Year <span class="text-danger">*</span></label>
                                <select id="classwise_year" name="year" class="form-control select2" required>
                                    @for ($y = now()->year - 2; $y <= now()->year + 1; $y++)
                                        <option value="{{ $y }}" {{ now()->year == $y ? 'selected' : '' }}>{{ $y }}</option>
                                    @endfor
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" id="classwiseAttendanceReset">Reset</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-success" id="classwiseExportExcelBtn">
                            <i class="fa fa-file-excel me-1"></i> Excel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="exportModal" tabindex="-1" aria-labelledby="exportModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exportModalLabel">Export Options</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="exportOptionsForm">
                    <div class="modal-body">
                        <input type="hidden" id="export_action" value="excel">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Data Scope</label>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="export_scope" id="scopeFiltered"
                                       value="filtered" checked>
                                <label class="form-check-label" for="scopeFiltered">
                                    Use current filters / search
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="export_scope" id="scopeAll"
                                       value="all">
                                <label class="form-check-label" for="scopeAll">
                                    Export complete Attendance list
                                </label>
                            </div>
                        </div>
                        <div>
                            <div class="d-flex flex-wrap gap-2 align-items-center mb-2">
                                <label class="form-label fw-bold mb-0">Columns</label>
                                <div class="ms-auto d-flex flex-wrap gap-2">
                                    <button type="button" class="btn btn-sm btn-light border" id="selectAllColumns">
                                        Select all
                                    </button>
                                    <button type="button" class="btn btn-sm btn-light border" id="clearAllColumns">
                                        Clear all
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-primary" id="resetDefaultColumns">
                                        Reset to default
                                    </button>
                                </div>
                            </div>
                            <p class="text-muted mb-2">Choose the columns that should appear in the export/print output.</p>
                            <div class="row" style="max-height: 260px; overflow-y: auto;">
                                @forelse ($exportColumns as $columnKey => $columnLabel)
                                    <div class="col-md-4 col-sm-6 mb-2">
                                        <div class="form-check">
                                            <input class="form-check-input export-column" type="checkbox"
                                                   value="{{ $columnKey }}" id="export_column_{{ $columnKey }}"
                                                   {{ in_array($columnKey, $defaultColumns) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="export_column_{{ $columnKey }}">
                                                {{ $columnLabel }}
                                            </label>
                                        </div>
                                    </div>
                                @empty
                                    <div class="col-12">
                                        <p class="text-muted mb-0">No exportable columns configured.</p>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Continue</button>
                    </div>
                </form>
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

    {{-- <script src="{{ asset('admin/assets/js/pages/datatables.init.js') }}"></script> --}}
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
            var admissionId = localStorage.getItem('admission_id');
            if (admissionId) {
                $('#admission_id').val(admissionId);
                localStorage.removeItem('admission_id');
            }

            dtable = $('#yajra-datatables').DataTable({
                searching: false,
                processing: true,
                serverSide: true,
                order: [
                    [2, 'desc']
                ],
                ajax: {
                    url: "{{ route($route . '.index') }}",
                    type: "GET",
                    data: function(data) {
                        data.search = $('input[type="search"]').val();
                        data.admission_id = $('#admission_id').val();
                        data.filter_course = $('#classwise_course_id').val();
                        data.filter_batch = $('#classwise_batch_id').val();
                        data.filter_class = $('#classwise_class_id').val();
                    },
                    beforeSend: function(request) {
                        request.setRequestHeader("X-CSRF-TOKEN", $('meta[name="csrf-token"]').attr(
                            'content'));
                    }
                },
                columns: {!! json_encode($columns) !!},
                language: {
                    searchPlaceholder: 'Search...',
                }
            });
        });

        $('input[name="search"]').keyup(function() {
            dtable.draw();
        });

        $('#filter_section').hide();
        $("#show_filter").click(function() {
            $('#filter_section').toggle();
        });
    </script>
    <script type="text/javascript">
        $(function() {
            const defaultColumns = @json(array_values($defaultColumns));
            const exportModalElement = document.getElementById('exportModal');
            let exportModalInstance = null;

            if (exportModalElement && typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                exportModalInstance = new bootstrap.Modal(exportModalElement);
            }

            const showExportModal = () => {
                if (exportModalInstance) {
                    exportModalInstance.show();
                } else {
                    $('#exportModal').modal('show');
                }
            };

            const hideExportModal = () => {
                if (exportModalInstance) {
                    exportModalInstance.hide();
                } else {
                    $('#exportModal').modal('hide');
                }
            };

            function openExportModal(actionType) {
                $('#export_action').val(actionType);
                $('#exportModalLabel').text(actionType === 'print' ? 'Print Preview' : 'Export to Excel');
                showExportModal();
            }

            $('#exportExcelBtn').on('click', function(e) {
                e.preventDefault();
                openExportModal('excel');
            });

            $('#print_btn').on('click', function(e) {
                e.preventDefault();
                openExportModal('print');
            });

            $('#selectAllColumns').on('click', function() {
                $('.export-column').prop('checked', true);
            });

            $('#clearAllColumns').on('click', function() {
                $('.export-column').prop('checked', false);
            });

            $('#resetDefaultColumns').on('click', function() {
                $('.export-column').each(function() {
                    $(this).prop('checked', defaultColumns.includes($(this).val()));
                });
            });

            $('#exportOptionsForm').on('submit', function(e) {
                e.preventDefault();

                const selectedColumns = $('.export-column:checked').map(function() {
                    return $(this).val();
                }).get();

                if (!selectedColumns.length) {
                    alert('Please select at least one column to continue.');
                    return;
                }

                const scope = $('input[name="export_scope"]:checked').val() || 'filtered';
                const params = new URLSearchParams();
                params.append('scope', scope);

                const admissionIdCurrent = $('#admission_id').val();
                if (admissionIdCurrent) {
                    params.append('admission_id', admissionIdCurrent);
                }

                if (scope === 'filtered') {
                    const searchValue = $('#yajra-datatables_filter input[type="search"]').val();
                    if (searchValue) {
                        params.append('search', searchValue);
                    }
                }

                selectedColumns.forEach(column => params.append('columns[]', column));

                const actionType = $('#export_action').val();
                const baseUrl = actionType === 'print'
                    ? "{{ route($route . '.print') }}"
                    : "{{ route($route . '.export-excel') }}";

                const finalUrl = params.toString() ? `${baseUrl}?${params.toString()}` : baseUrl;

                if (actionType === 'print') {
                    window.open(finalUrl, '_blank');
                } else {
                    window.location.href = finalUrl;
                }

                hideExportModal();
            });

            const classwiseModalElement = document.getElementById('classwiseAttendanceModal');
            let classwiseModalInstance = null;

            if (classwiseModalElement && typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                classwiseModalInstance = new bootstrap.Modal(classwiseModalElement);
            }

            const showClasswiseModal = () => {
                if (classwiseModalInstance) {
                    classwiseModalInstance.show();
                } else {
                    $('#classwiseAttendanceModal').modal('show');
                }
            };

            const hideClasswiseModal = () => {
                if (classwiseModalInstance) {
                    classwiseModalInstance.hide();
                } else {
                    $('#classwiseAttendanceModal').modal('hide');
                }
            };

            $('#classwiseAttendanceBtn').on('click', function(e) {
                e.preventDefault();
                showClasswiseModal();
            });

            function initClasswiseSelect2() {
                if (!$.fn.select2) {
                    return;
                }

                $('#classwise_course_id, #classwise_batch_id, #classwise_class_id, #classwise_month, #classwise_year').each(function() {
                    if ($(this).hasClass('select2-hidden-accessible')) {
                        $(this).select2('destroy');
                    }
                    $(this).select2({
                        dropdownParent: $('#classwiseAttendanceModal'),
                        width: '100%'
                    });
                });
            }

            function resetClasswiseClass() {
                $('#classwise_class_id')
                    .html('<option value="">Select Class</option>')
                    .prop('disabled', true)
                    .val('')
                    .trigger('change');
            }

            function resetClasswiseBatch() {
                resetClasswiseClass();
                $('#classwise_batch_id')
                    .html('<option value="">Select Batch</option>')
                    .prop('disabled', true)
                    .val('')
                    .trigger('change');
            }

            function refreshClasswiseSelect2(selector) {
                if (!$.fn.select2) {
                    return;
                }

                const $select = $(selector);
                if ($select.hasClass('select2-hidden-accessible')) {
                    $select.select2('destroy');
                }
                $select.select2({
                    dropdownParent: $('#classwiseAttendanceModal'),
                    width: '100%'
                });
            }

            function loadClasswiseClasses(batchId) {
                resetClasswiseClass();

                if (!batchId) {
                    return;
                }

                $.ajax({
                    type: 'POST',
                    url: "{{ route('get-class-bybatch') }}",
                    beforeSend: function(request) {
                        request.setRequestHeader('X-CSRF-TOKEN', $('meta[name="csrf-token"]').attr('content'));
                    },
                    data: {
                        batch_id: batchId
                    },
                    success: function(response) {
                        if (!response.status) {
                            return;
                        }

                        let options = '<option value="">Select Class</option>';
                        if (response.data && response.data.length > 0) {
                            $.each(response.data, function(i, item) {
                                options += '<option value="' + item.id + '">' + item.name + '</option>';
                            });
                        }

                        $('#classwise_class_id')
                            .html(options)
                            .prop('disabled', false)
                            .trigger('change');

                        refreshClasswiseSelect2('#classwise_class_id');
                    }
                });
            }

            function loadClasswiseBatches(courseId) {
                resetClasswiseBatch();

                if (!courseId) {
                    return;
                }

                $.ajax({
                    type: 'POST',
                    url: "{{ route('get-batch') }}",
                    beforeSend: function(request) {
                        request.setRequestHeader('X-CSRF-TOKEN', $('meta[name="csrf-token"]').attr('content'));
                    },
                    data: {
                        course_id: courseId
                    },
                    success: function(response) {
                        if (!response.status) {
                            return;
                        }

                        let options = '<option value="">Select Batch</option>';
                        if (response.data && response.data.length > 0) {
                            $.each(response.data, function(i, item) {
                                options += '<option value="' + item.id + '">' + item.name + '</option>';
                            });
                        }

                        $('#classwise_batch_id')
                            .html(options)
                            .prop('disabled', false)
                            .trigger('change');

                        refreshClasswiseSelect2('#classwise_batch_id');
                    }
                });
            }

            function loadClasswiseCourses() {
                $.ajax({
                    type: 'POST',
                    url: "{{ route('get-course') }}",
                    beforeSend: function(request) {
                        request.setRequestHeader('X-CSRF-TOKEN', $('meta[name="csrf-token"]').attr('content'));
                    },
                    success: function(response) {
                        if (!response.status) {
                            return;
                        }

                        let options = '<option value="">Select Course</option>';
                        if (response.data && response.data.length > 0) {
                            $.each(response.data, function(i, item) {
                                options += '<option value="' + item.id + '">' + item.name + '</option>';
                            });
                        }

                        $('#classwise_course_id').html(options).trigger('change');
                    }
                });
            }

            $('#classwiseAttendanceModal').on('shown.bs.modal', function() {
                resetClasswiseBatch();
                loadClasswiseCourses();
                initClasswiseSelect2();
            });

            $('#classwise_course_id').on('change', function() {
                loadClasswiseBatches($(this).val());
            });

            $('#classwise_batch_id').on('change', function() {
                loadClasswiseClasses($(this).val());
            });

            function checkClasswiseStudents(showSuccessToast = false) {
                const courseId = $('#classwise_course_id').val();
                const batchId = $('#classwise_batch_id').val();
                const classId = $('#classwise_class_id').val();

                if (!courseId || !batchId || !classId) {
                    return $.Deferred().resolve({ has_students: true }).promise();
                }

                return $.ajax({
                    type: 'GET',
                    url: "{{ route($route . '.classwise-check-students') }}",
                    data: {
                        filter_course: courseId,
                        filter_batch: batchId,
                        filter_class: classId,
                    },
                    success: function(response) {
                        if (!response.has_students) {
                            toastr.warning(response.message || 'No students found for selected Course, Batch and Class.');
                        } else if (showSuccessToast) {
                            toastr.success(response.message || 'Students found successfully.');
                        }
                    },
                    error: function() {
                        toastr.error('Unable to check students. Please try again.');
                    }
                });
            }

            $('#classwise_class_id').on('change', function() {
                if ($(this).val()) {
                    checkClasswiseStudents(false);
                }
            });

            $('#classwiseExportExcelBtn').on('click', function() {
                if (!$('#classwise_course_id').val()) {
                    toastr.warning('Please select Course.');
                    return;
                }

                if (!$('#classwise_batch_id').val()) {
                    toastr.warning('Please select Batch.');
                    return;
                }

                if (!$('#classwise_class_id').val()) {
                    toastr.warning('Please select Class.');
                    return;
                }

                if (!$('#classwise_month').val()) {
                    toastr.warning('Please select Month.');
                    return;
                }

                if (!$('#classwise_year').val()) {
                    toastr.warning('Please select Year.');
                    return;
                }

                checkClasswiseStudents(false).done(function(response) {
                    if (response && !response.has_students) {
                        return;
                    }

                    const params = new URLSearchParams({
                        filter_course: $('#classwise_course_id').val(),
                        filter_batch: $('#classwise_batch_id').val(),
                        filter_class: $('#classwise_class_id').val(),
                        month: $('#classwise_month').val(),
                        year: $('#classwise_year').val(),
                    });

                    window.location.href = "{{ route($route . '.classwise-export-excel') }}?" + params.toString();
                });
            });

            $('#classwiseAttendanceReset').on('click', function() {
                $('#classwise_course_id').val('').trigger('change');
                resetClasswiseBatch();
                $('#classwise_month').val('{{ now()->month }}');
                $('#classwise_year').val('{{ now()->year }}');
                if ($.fn.select2) {
                    $('#classwise_course_id, #classwise_batch_id, #classwise_class_id, #classwise_month, #classwise_year').trigger('change.select2');
                }
                dtable.draw();
            });
        });
    </script>

    @include('software.includes.script-delete-record')
    @include('software.includes.script-restore-record')
    @include('software.includes.script-update-status')
@endsection
