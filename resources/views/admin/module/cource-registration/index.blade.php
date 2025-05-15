@extends('admin.layout.app')

@php
    $page_title = $modules['title'] ?? 'Course Registrations';
    $route = $modules['route'] ?? 'cource-registration';
@endphp

@section('title', $page_title)

@section('page_style_file')
    <link rel="stylesheet" href="{{ asset('admin/assets/cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css') }}">
    <link rel="stylesheet" href="{{ asset('admin/assets/cdn.datatables.net/responsive/2.2.9/css/responsive.bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('admin/assets/cdn.datatables.net/buttons/2.2.2/css/buttons.dataTables.min.css') }}">
@endsection

@section('breadcrumb')
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">{{ $page_title }}</li>
                    </ol>
                </div>
                <div>
                    <a href="{{ route($route . '.create') }}" class="btn btn-primary ml-auto">
                        <i class="fa fa-plus"></i> Add {{ $page_title }}
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('content')

    @include('admin.partials.flash_messages')

    <div class="row my-5">
        <div class="col-md-12">
            <div class="card shadow">
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="yajra-datatables" class="display table table-hover">
                            @if (isset($columns))
                            <thead>
                                <tr>
                                    @foreach ($columns as $item)
                                        <th class="{{ $item?->className ?? '' }}">{{ ucfirst($item?->td_label ?? $item?->name) ?? '' }}</th>
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

        let dtable = null;

        $(document).ready(function() {
            if ($.fn.DataTable.isDataTable('#yajra-datatables')) {
                $('#yajra-datatables').DataTable().clear().destroy();
            }

            dtable = $('#yajra-datatables').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                searching: true,
                order: [[0, 'desc']],
                ajax: {
                    url: "{{ route($route . '.index') }}",
                    type: "GET",
                    data: function(data) {
                        data.search = $('input[type="search"]').val();
                    },
                },
                columns: {!! json_encode($columns) !!},
                language: {
                    searchPlaceholder: 'Search...',
                }
            });
        });

        $('input[name="search"]').on('keyup', function() {
            dtable.draw();
        });

        $(document).on('change', 'select', function() {
            dtable.draw();
        });
    </script>

    @include('admin.includes.script-delete-record')
    @include('admin.includes.script-restore-record')
    @include('admin.includes.script-update-status')
@endsection
