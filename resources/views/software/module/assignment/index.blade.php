@extends('software.layout.app')

@php
    $page_title = isset($modules['title']) ? $modules['title'] : 'Assignment';
    $folder_path = isset($modules['folder_path']) ? $modules['folder_path'] : null;
    $route = isset($modules['route']) ? $modules['route'] : null;
@endphp
@section('title', $page_title)

@section('page_style_file')
    <link rel="stylesheet" href="{{ asset('admin/assets/cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css') }}">
    <link rel="stylesheet" href="{{ asset('admin/assets/cdn.datatables.net/responsive/2.2.9/css/responsive.bootstrap.min.css') }}">
@endsection

@section('content')
    <div class="d-flex justify-content-lg-between px-1">
        @include('software.includes.breadcrumb', [
            'breadcrumbArray' => [['title' => $page_title, 'url' => '']],
            'route' => $route,
            'show_add_btn' => $modules['permission_add'] ?? false,
            'show_filter_btn' => false,
            'show_export_btns' => false,
            'show_excal_btn' => false,
            'show_print_btn' => false,
            'show_back_btn' => false,
        ])
    </div>

    <div class="row my-3">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="yajra-datatables" class="table table-hover dt-responsive nowrap" style="width: 100%">
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

        $(document).ready(function() {
            var dtable = $('#yajra-datatables').DataTable({
                searching: true,
                processing: true,
                serverSide: true,
                order: [],
                ajax: {
                    url: "{{ route('assignment.index') }}",
                    type: "GET"
                },
                columns: {!! json_encode($columns) !!},
                language: {
                    searchPlaceholder: 'Search...',
                    emptyTable: "No assignments found."
                }
            });

            // Change Status Handler
            $(document).on('click', '.change-status', function() {
                var id = $(this).data('id');
                var status = $(this).data('status');
                
                $.ajax({
                    url: "{{ route('assignment.change-status') }}",
                    type: "POST",
                    data: {
                        id: id,
                        status: status
                    },
                    success: function(response) {
                        if (response.success) {
                            dtable.ajax.reload(null, false);
                            if (typeof toastr !== 'undefined') {
                                toastr.success(response.message);
                            }
                        } else {
                            if (typeof toastr !== 'undefined') {
                                toastr.error(response.message);
                            } else {
                                alert(response.message);
                            }
                        }
                    }
                });
            });
        });
    </script>

    @include('software.includes.script-delete-record')
@endsection
