@extends('software.layout.app')

@php
    $page_title = isset($modules['title']) ? $modules['title'] : null;
    $folder_path = isset($modules['folder_path']) ? $modules['folder_path'] : null;
    $route = isset($modules['route']) ? $modules['route'] : null;
    $permission_prefix = isset($modules['permission_prefix']) ? $modules['permission_prefix'] : null;
    $exportColumns = $availableExportColumns ?? [];
    $defaultColumns = $defaultExportColumns ?? array_keys($exportColumns);
@endphp
@section('title', $page_title)

@section('page_style_file')
    <!--datatable css-->
    <link rel="stylesheet" href="{{ asset('admin/assets/cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css') }}">
    <link rel="stylesheet" href="{{ asset('admin/assets/cdn.datatables.net/responsive/2.2.9/css/responsive.bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('admin/assets/cdn.datatables.net/buttons/2.2.2/css/buttons.dataTables.min.css') }}">
@endsection

@section('content')
    <div class="d-flex justify-content-lg-between px-1">
        @include('software.includes.breadcrumb', [
            'breadcrumbArray' => [['title' => $page_title, 'url' => '']],
            'route' => $route,
            'show_add_btn' => true,
            'show_filter_btn' => false,
            'show_export_btns' => true,
            'show_excal_btn' => true,
            'show_print_btn' => true,
            'show_back_btn' => false,
        ])
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <input list="search" id="search_id" name="search" class="form-control" placeholder="Search Name, GR No">
                    <datalist id="search" class="search_by_courceregistration" data-append="search_by_courceregistration"></datalist>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="yajra-datatables" class="table table-hover dt-responsive nowrap" style="width: 100%">
                            @if (isset($columns))
                                <thead>
                                    <tr>
                                        @foreach ($columns as $item)
                                            <th class="{{ $item?->className ?? '' }}">{{ ucfirst($item?->td_label) ?? '' }}</th>
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
                                    Use current filters / search
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="export_scope" id="scopeAll"
                                    value="all">
                                <label class="form-check-label" for="scopeAll">
                                    Export complete Faculty Complaint Report list
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
            dtable = $('#yajra-datatables').DataTable({
                searching: false,
                processing: true,
                serverSide: true,
                order: [],
                ajax: {
                    "url": "{{ route($route . '.index') }}",
                    'beforeSend': function(request) {
                        request.setRequestHeader("X-CSRF-TOKEN", $('meta[name="csrf-token"]').attr('content'));
                    },
                    type: "GET",
                    data: function(data) {
                        data.search = { value: $('input[name="search"]').val() };
                    },
                },
                columns: {!! json_encode($columns) !!},
                language: {
                    searchPlaceholder: 'Search...',
                },
            });
        });

        $('input[name="search"]').on('keyup change', function() {
            dtable.draw();
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

                if (scope === 'filtered') {
                    const searchValue = $('input[name="search"]').val();
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
        });
    </script>

    @include('software.utils.getCourceRegistration')
    @include('software.includes.script-delete-record')
@endsection
