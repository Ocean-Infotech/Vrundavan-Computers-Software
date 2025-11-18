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
<link rel="stylesheet" href="{{ asset('admin/assets/cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css') }}">
<meta name="csrf-token" content="{{ csrf_token() }}">
<link rel="stylesheet"
    href="{{ asset('admin/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css') }}">
@endsection

@section('content')
<div class="d-flex justify-content-lg-between px-1">
    @include('software.includes.breadcrumb', [
    'breadcrumbArray' => [['title' => $page_title, 'url' => '']],
    'route' => $route,
    'show_add_btn' => $modules['permission_add'] ?? 'false',
    'show_filter_btn' => false,
    'show_export_btns' => false,
    'show_excal_btn' => false,
    'show_print_btn' => false,
    'show_back_btn' => false,
    ])
</div>

@include('software.partials.flash_messages')
<div class="row my-3">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <div class="row">
                    <div class="col-md-3">
                        <input type="search" class="form-control" name="search" placeholder="search...">
                    </div>
                    <div class="col-md-3">
                        <select class="form-control select2" name="status">
                            <option value="">Select Status</option>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select class="form-control select2" name="role">
                            <option value="">Select role</option>
                            @foreach ($roles as $item)
                            <option value="{{ $item->id }}">{{ $item->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                                            <table id="yajra-datatables" class="table table-hover dt-responsive">

                        @if (isset($columns))
                        <thead>
                            <tr>
                                @foreach ($columns as $item)
                                <th class="{{ $item?->className ?? '' }}">{{ ucfirst($item?->name) ?? '' }}
                                </th>
                                @endforeach
                                {{-- <th>Name</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>SP</th>
                                    <th>Roles</th>
                                    <th>Status</th>
                                    <th width="15%" class="no-sort text-center">Actions</th> --}}
                                {{-- <th class="text-center wd-15p" >Status</th> --}}
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
            searching: false,
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
                    data.search = $('input[type="search"]').val();
                    data.status = $('select[name="status"] option:selected').val();
                    data.role = $('select[name="role"] option:selected').val();
                },
            },
            columns: {!!json_encode($columns) !!},
            language: {
                searchPlaceholder: 'Search...',
            },
            // dom:'lBfrtip',
            // buttons: ["csv"],
        });
    });
    $('input[name="search"]').keyup(function() {
        dtable.draw();
    });
    jQuery(document).on('change', 'select', function(event) {
        event.preventDefault();
        dtable.draw();
    });
    console.log(dtable);
</script>

@include('software.includes.script-delete-record')
@endsection
