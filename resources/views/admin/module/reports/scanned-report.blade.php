@extends('admin.layout.app')

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
    <link rel="stylesheet"
        href="{{ asset('admin/assets/cdn.datatables.net/responsive/2.2.9/css/responsive.bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('admin/assets/cdn.datatables.net/buttons/2.2.2/css/buttons.dataTables.min.css') }}">
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
            </div>
        </div>
    </div>
    <!-- end page title -->
@endsection

@section('content')

    @include('admin.partials.flash_messages')

    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col-md-3 col-sm-6">
                    <input type="search" class="form-control" name="search" placeholder="Search by scanned name, redemption code,">
                </div>
                @isset($redeption_list)
                    <div class="col-md-2 col-sm-4">
                        <select class="form-control select2 filter_by_redeption_id">
                            <option value="" selected>Filter by redeption</option>
                            @foreach ($redeption_list as $key => $record)
                                <option value="{{ $record?->id ?? '0' }}">
                                    {{ $record?->label ?? '' }}</option>
                            @endforeach
                        </select>
                    </div>
                @endisset
                @isset($scanned_by)
                    <div class="col-md-2 col-sm-4">
                        <select class="form-control select2 filter_by_scanned_user">
                            <option value="" selected>Filter by redeption</option>
                            @foreach ($scanned_by as $key => $record)
                                <option value="{{ $record?->id }}">
                                    {{ $record }}</option>
                            @endforeach
                        </select>
                    </div>
                @endisset
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="yajra-datatables" class="display table table-hover">
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
                    [0, 'desc']
                ],
                ajax: {
                    "url": "{{ route($route) }}",
                    'beforeSend': function(request) {
                        request.setRequestHeader("X-CSRF-TOKEN", $('meta[name="csrf-token"]').attr(
                            'content'));
                    },
                    type: "GET",
                    data: function(data) {
                        data.search = $('input[type="search"]').val();
                        data.filter_by_redeption_id = $('.filter_by_redeption_id').val();
                        // data.status = $('select[name="status"] option:selected').val();
                        // data.role = $('select[name="role"] option:selected').val();
                    },
                },
                columns: {!! json_encode($columns) !!},
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
    @include('admin.includes.script-delete-record')
@endsection
