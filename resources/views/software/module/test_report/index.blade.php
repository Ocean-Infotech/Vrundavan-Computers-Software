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
    <link rel="stylesheet" href="{{ asset('admin/assets/cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css') }}">
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
            'show_export_btns' => true,
            'show_excal_btn' => true,
            'show_print_btn' => true,
            'show_student_test_report_btn' => true,
            'show_back_btn' => false,
        ])
    </div>

    <div class="row my-3">
        <div class="row mb-3">
            <!-- Student Search -->
            <div class="col-md-3 col-sm-12 mb-3">
                <div class="form-group">
                    <label class="form-label">Search Student</label>
                    <input list="search" id="search_id" name="search"
                        class="form-control @error('search') is-invalid @enderror"
                        placeholder="Search by Student ID or Name">

                    <datalist id="search" class="search_by_courceregistration" data-append="search_by_courceregistration"
                        data-selectedCourceRegistrationId="">
                    </datalist>



                    @error('search')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>
            </div>
            <div class="col-md-12 mb-3 d-flex flex-wrap">

                <div class="col-md-3 col-sm-6 mb-2">
                    <label class="fw-bold">Student Name :</label>
                    <span id="selected_student_name" class="fw-bold text-dark"></span>
                </div>

                <div class="col-md-3 col-sm-6 mb-2">
                    <label class="fw-bold">Student Id :</label>
                    <span id="selected_student_id" class="fw-bold text-dark"></span>
                </div>

                <div class="col-md-3 col-sm-6 mb-2">
                    <label class="fw-bold">Admission Id :</label>
                    <span id="selected_admission_id" class="fw-bold text-dark"></span>
                </div>

                <div class="col-md-3 col-sm-6 mb-2">
                    <label class="fw-bold">Course Name :</label>
                    <span id="selected_course_name" class="fw-bold text-dark"></span>
                </div>

            </div>


            {{-- <div class="col-md-3 col-sm-12 mb-3">
                <div class="form-group">
                    <label class="form-label">Select Course <span class="text-danger">*</span></label>
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
                    <label class="form-label">Select Batch <span class="text-danger">*</span></label>
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
            </div> --}}
        </div>

        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="yajra-datatables" class="table table-hover dt-responsive nowrap" style="width: 100%">
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
    <input type="hidden" id="admission_id" value="">

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
                                    Export complete Test Report list
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
    <script src="{{ asset('admin/assets/cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('admin/assets/cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js') }}"></script>
    <script src="{{ asset('admin/assets/cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
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
                        data.admission_id = $('#admission_id').val();
                        data.date = $('#date').val();
                        data.course_id = $('#course').val();
                        data.batch_id = $('#batch').val();
                        data.subject_name = $('#subject').val();
                        data.student_search = $('#search_id').val();
                        data.search = $('input[type="search"]').val();
                    }
                },
                columns: {!! json_encode($columns) !!},
                language: {
                    searchPlaceholder: 'Search...',
                    emptyTable: "No data available in table"
                },
                drawCallback: function(settings) {
                    let api = this.api();
                    let data = api.rows({
                        page: 'current'
                    }).data();

                    let response = settings.json;
                    let student = response.student_details;
                    let studentSearch = $('#search_id').val();
                    let admissionId = $('#admission_id').val();

                    // Update student details from server response
                    if (student) {
                        $("#selected_student_name").text(student.student_name ?? '');
                        $("#selected_student_id").text(student.student_id ?? '');
                        $("#selected_admission_id").text(student.admission_id ?? '');
                        $("#selected_course_name").text(student.course_name ?? '');
                    }
                    // Alternative: Get from first row if server doesn't send student_details
                    else if (data.length > 0 && studentSearch) {
                        $("#selected_student_name").text(data[0].student_name ?? '');
                        $("#selected_student_id").text(data[0].student_id ?? '');
                        $("#selected_admission_id").text(data[0].admission_id ?? '');
                        $("#selected_course_name").text(data[0].course_name ?? '');
                    }
                    // Clear if no student selected
                    else if (!studentSearch && !admissionId) {
                        $("#selected_student_name").text('');
                        $("#selected_student_id").text('');
                        $("#selected_admission_id").text('');
                        $("#selected_course_name").text('');
                    }

                    // Check if student is searched but no data found
                    if (data.length === 0 && (studentSearch || admissionId)) {
                        $('#yajra-datatables tbody').html(`
                            <tr class="odd">
                                <td valign="top" colspan="{{ count($columns) }}" class="dataTables_empty text-center text-danger">
                                    <strong>No Test Report Found for this Student.</strong>
                                </td>
                            </tr>
                        `);
                    }
                    // Show default message when no student is selected
                    else if (data.length === 0 && !studentSearch && !admissionId) {
                        $('#yajra-datatables tbody').html(`
                            <tr class="odd">
                                <td valign="top" colspan="{{ count($columns) }}" class="dataTables_empty text-center text-danger">
                                    Please select <strong>Student ID</strong> or <strong>Name</strong>.
                                </td>
                            </tr>
                        `);
                    }
                }
            });

            // Check localStorage for admission_id
            var admissionId = localStorage.getItem('admission_id');
            if (admissionId) {
                $('#admission_id').val(admissionId);
                $('#search_id').val(admissionId);
                localStorage.removeItem('admission_id');

                setTimeout(function() {
                    dtable.draw();
                }, 500);
            }

            // Student search functionality - debounced on input to avoid flooding requests
            let searchIdDebounceTimer;
            $("#search_id").on('input', function() {
                let selectedValue = $(this).val();

                // Update admission_id when student is selected
                if (selectedValue) {
                    $('#admission_id').val(selectedValue);
                } else {
                    $('#admission_id').val('');
                }

                clearTimeout(searchIdDebounceTimer);
                searchIdDebounceTimer = setTimeout(function() {
                    dtable.draw();
                }, 500); // 500ms debounce
            });

            $("#search_id").on('change', function() {
                // Trigger immediately on change (e.g. selection from datalist)
                clearTimeout(searchIdDebounceTimer);
                dtable.draw();
            });

            $('#filter-btn').on('click', function() {
                if (!$('#date').val() && !$('#batch').val() && !$('#subject').val()) {
                    toastr.warning("Please select at least one filter before searching.");
                    return;
                }
                dtable.draw();
            });

            $(document).on('change', '#date, #batch, #subject, #course', function() {
                dtable.draw();
            });

            // Debounced general search
            let generalSearchDebounceTimer;
            $(document).on('keyup', 'input[type="search"]', function() {
                clearTimeout(generalSearchDebounceTimer);
                generalSearchDebounceTimer = setTimeout(function() {
                    dtable.draw();
                }, 500);
            });

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

            $('#print_student_test_report_btn').on('click', function(e) {
                e.preventDefault();
                
                const studentSearch = $('#search_id').val() || '';
                if (!studentSearch) {
                    alert('Please search and select a student first.');
                    return;
                }
                
                const params = new URLSearchParams();
                params.append('scope', 'filtered');
                params.append('student_search', studentSearch);
                
                // Add default columns
                const defaultCols = ['subject_name', 'unit_name', 'cr_date', 'mark', 'student_mark'];
                defaultCols.forEach(column => params.append('columns[]', column));
                
                const baseUrl = "{{ route($route . '.print') }}";
                const finalUrl = `${baseUrl}?${params.toString()}`;
                
                window.open(finalUrl, '_blank');
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

                if (scope === 'filtered') {
                    const studentSearch = $('#search_id').val() || '';
                    const searchValue = $('#yajra-datatables_filter input[type="search"]').val() || '';

                    if (studentSearch) params.append('student_search', studentSearch);
                    if (searchValue) params.append('search', searchValue);
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
        });
    </script>

    @include('software.utils.getCourse')
    @include('software.utils.getBatchByCourseid')
    @include('software.utils.getCourceRegistration')
    @include('software.includes.script-delete-record')
    @include('software.includes.script-restore-record')
    @include('software.includes.script-update-status')
@endsection
