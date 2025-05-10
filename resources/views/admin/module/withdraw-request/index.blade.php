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
<!--datatable css-->
<link rel="stylesheet" href="{{ asset('admin/assets/cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css') }}">
<!--datatable responsive css-->
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

@include('admin.partials.flash_messages')
<div class="row my-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <!-- <div class="row">
                    <div class="col-md-3">
                        <input type="search" class="form-control" name="search" placeholder="search...">
                    </div>
                </div> -->
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="yajra-datatables" class="display table table-hover">
                        @if (isset($columns))
                        <thead>
                            <tr>
                                @foreach ($columns as $item)
                                <th class="{{ $item?->className ?? '' }}">{{ ucfirst($item?->td_label) ?? '' }}
                                </th>
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
                    // data.filter_by_slider_type = $('.filter_by_slider_type').val();
                    // data.status = $('select[name="status"] option:selected').val();
                    // data.role = $('select[name="role"] option:selected').val();
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
</script>
@include('admin.includes.script-delete-record')
@endsection