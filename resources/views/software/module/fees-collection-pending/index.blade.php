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
            'show_filter_btn' => true,
            'show_export_btns' => true,
            'show_excal_btn' => true,
            'show_print_btn' => true,
            'show_back_btn' => false,
        ])
    </div>

    <!-- @include('software.partials.flash_messages') -->
    <div class="row my-3">
        <div class="col-md-12 mb-5" id="filter_section">
            <div class="card">
                <div class="card-body">
                    <div class="row" id="filter_section">
                        <div class="col-md-3 mb-2 col-sm-12">
                            <div class="form-group">
                                <label for="student_filter">Filter by Student (ID, Name, Contact)</label>
                                <input type="text" id="student_filter" name="student_filter"
                                    class="form-control select_filter" placeholder="Enter student ID, name or contact">
                            </div>
                        </div>

                        <div class="col-md-2 col-sm-12 mb-3">
                            <div class="form-group">
                                <label class="form-label">Filter by Course </label>
                                <select id="course_id" name="course_id"
                                    class="form-control search_by_course select2 select_filter"
                                    data-append="search_by_course" autofocus>
                                    <option value="">Filter by Course</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-2 col-sm-12 mb-3">
                            <div class="form-group">
                                <label class="form-label d-flex justify-content-between align-items-center">Filter by
                                    Batch</label>
                                <select id="batch_id" name="batch_id"
                                    class="form-control search_by_batch select2 select_filter" data-append="search_by_batch"
                                    autofocus>
                                    <option value="">Filter by Batch</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-2 col-sm-12 mb-3">
                            <div class="form-group">
                                <label class="form-label d-flex justify-content-between align-items-center">Filter by
                                    Class</label>
                                <select id="class_id" name="class_id"
                                    class="form-control search_by_class select2 select_filter" data-append="search_by_class"
                                    autofocus>
                                    <option value="">Filter by Class</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-2 col-sm-12 mb-3">
                            <div class="form-group">
                                <label class="form-label d-flex justify-content-between align-items-center">Filter by
                                    Semester</label>
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
                                    Use current filters
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="export_scope" id="scopeAll"
                                       value="all">
                                <label class="form-check-label" for="scopeAll">
                                    Export complete Fees Pending list
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

            if ($.fn.DataTable.isDataTable('#yajra-datatables')) {
                $('#yajra-datatables').DataTable().clear().destroy();
            }

            dtable = $('#yajra-datatables').DataTable({
                processing: true,
                serverSide: true,
                searching: false,

                order: [
                    [1, 'desc']
                ],
                ajax: {
                    url: "{{ route($route . '.index') }}",
                    type: "GET",
                    data: function(data) {
                        data.student_filter = $('#student_filter').val();
                        data.course_id = $('#course_id').val();
                        data.batch_id = $('#batch_id').val();
                        data.class_id = $('#class_id').val();
                        data.semester_id = $('#semester_id').val();
                    }
                },
                columns: {!! json_encode($columns) !!},
                language: {
                    searchPlaceholder: 'Search...',
                },
            });
            $(document).ready(function() {
                $('#filter_section').hide();
                $("#show_filter").click(function() {

                    if ($('#filter_section').is(':hidden')) {
                        $('#filter_section').show();
                    } else {
                        $('#filter_section').hide();
                    }
                });

                $("#cilory_filter").click(function() {
                    $('.select_filter').val(null).trigger('change');
                    $('.search').val('');
                    dtable.draw();
                });
            });

            $('.select_filter').on('change keyup', function() {
                dtable.draw();
            });
        });
    </script>
    <script>
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

                if (scope === 'filtered') {
                    const studentFilter = $('#student_filter').val() || '';
                    const courseId = $('#course_id').val() || '';
                    const batchId = $('#batch_id').val() || '';
                    const classId = $('#class_id').val() || '';
                    const semesterId = $('#semester_id').val() || '';

                    if (studentFilter) params.append('student_filter', studentFilter);
                    if (courseId) params.append('course_id', courseId);
                    if (batchId) params.append('batch_id', batchId);
                    if (classId) params.append('class_id', classId);
                    if (semesterId) params.append('semester_id', semesterId);
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
    @include('software.utils.getClassByBatch')
    @include('software.utils.getSamasterByCourseid')
    @include('software.utils.getAdmission')
    @include('software.includes.script-delete-record')
    @include('software.includes.script-restore-record')
    @include('software.includes.script-update-status')
@endsection
