@extends('software.layout.app')

@php
    $page_title = 'Fee History';
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
@endsection

@section('content')
    <div class="d-flex justify-content-lg-between px-1">
        @include('software.includes.breadcrumb', [
            'breadcrumbArray' => [['title' => $page_title, 'url' => '']],
            'route' => $route,
            'show_add_btn' =>
                isset($modules) && isset($modules['permission_add']) ? $modules['permission_add'] : false,
            'show_filter_btn' => false,
            'show_export_btns' => false,
            'show_excal_btn' => false,
            'show_print_btn' => false,
            'show_back_btn' => false,
        ])
    </div>

    <!-- @include('software.partials.flash_messages') -->
    <div class="row my-3">
        <div class="col-12 mb-4" id="filter_section">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="row align-items-end">
                        <!-- Filter Input -->
                        <div class="col-md-4">
                            <label for="search" class="form-label">Filter by Student</label>
                            <input list="search" id="search_id" name="search"
                                class="form-control search @error('search') is-invalid @enderror"
                                value="{{ old('search') }}" placeholder="Search">
                            <datalist id="search" class="search_by_admission" data-append="search_by_admission"
                                data-selectedAdmissionId="{{ old('search') }}">
                            </datalist>
                        </div>

                        <!-- Clear Filter Button -->
                        <div class="col-md-2">
                            <button type="button" title="Clear Filter" id="cilory_filter"
                                class="btn btn-outline-danger mt-4">
                                <i class="ti ti-x"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-12">
            <div class="card" id="feescollectionlist">
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

            dtable = $('#yajra-datatables').DataTable({
                searching: true,
                processing: true,
                serverSide: true,
                order: [
                    [1, 'desc']
                ],
                ajax: {
                    "url": "{{ route($route . '.index') }}",
                    'beforeSend': function(request) {
                        request.setRequestHeader("X-CSRF-TOKEN", $('meta[name="csrf-token"]').attr(
                            'content'));
                    },
                    type: "GET",
                    data: function(data) {
                        data.search = $('input[name="search"]').val();
                        // data.status = $('select[name="status"] option:selected').val();
                        // data.role = $('select[name="role"] option:selected').val();
                    },
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
                    let search_id = $('#search_id').val();

                    if (data.length === 0 && (!search_id)) {
                        $('#yajra-datatables tbody').html(`
                            <tr class="odd">
                                <td valign="top" colspan="{{ count($columns) }}" class="dataTables_empty text-center text-danger">
                                    Please select <strong>Student</strong> to view records.
                                </td>
                            </tr>
                        `);
                    }
                }
                // dom:'lBfrtip',
                // buttons: ["csv"],
            });
        });

        // $('#feescollectionlist').hide();
        $('input[name="search"]').keyup(function() {
            // $('#feescollectionlist').show();
            dtable.draw();

        });
        jQuery(document).on('change', 'select', function(event) {
            event.preventDefault();
            dtable.draw();
        });

        $("#cilory_filter").click(function() {
            $('.search').val('');
            // $('#feescollectionlist').hide();
            dtable.draw();
        });
    </script>

    @include('software.utils.getAdmission')
    @include('software.includes.script-delete-record')
    @include('software.includes.script-restore-record')
    @include('software.includes.script-update-status')
@endsection
