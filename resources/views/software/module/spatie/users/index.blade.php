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
    <link rel="stylesheet" href="{{ asset('admin/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css') }}">
    <link rel="stylesheet" href="{{ asset('admin/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css') }}">
@endsection

@section('breadcrumb')
<!-- start page title -->
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            {{-- <h3 class="page-title">{{ $page_title }}</h3> --}}
            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">{{ $page_title }}</li>
                </ol>
            </div>
            @directCan($permisstion_prefix . '-create')
            <a href="{{ route($route . '.create') }}" class="btn btn-primary ml-auto"><i class="fa fa-plus"></i> Add
                {{ $page_title }}</a>
            @enddirectCan
        </div>
    </div>
</div>
<!-- end page title -->
@endsection

@section('content')

@include('software.partials.flash_messages')
<div class="row my-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <div class="row">
                    <div class="col-md-3">
                        <input type="search" class="form-control" name="search" placeholder="search...">
                    </div>
                    <div class="col-md-3">
                        <select class="form-control" name="status">
                            <option value="">Select Status</option>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select class="form-control" name="role">
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
                    <table id="yajra-datatables" class="display table table-hover">
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
<script src="{{ asset('admin/assets/cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('admin/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js') }}"></script>

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

        if ( $.fn.DataTable.isDataTable('#yajra-datatables') ) {
            $('#yajra-datatables').DataTable().clear().destroy();
        }

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
            /*
            columns: [{
                    data: 'name',
                    name: 'name'
                },
                {
                    data: 'email',
                    name: 'email'
                },
                {
                    data: 'phone',
                    name: 'phone'
                },
                {
                    data: 'sp',
                    name: 'sp',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'role_name',
                    name: 'role_name',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'status',
                    name: 'status'
                },
                {
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    searchable: false,
                    className: 'text-center'
                },
            ],
            */
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
</script>

@include('software.includes.script-delete-record')
@endsection
