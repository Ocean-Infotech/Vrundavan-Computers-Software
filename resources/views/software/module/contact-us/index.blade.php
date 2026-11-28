@extends('software.layout.app')

@php
    $page_title = isset($modules['title']) ? $modules['title'] : 'Contact Us';
    $route = isset($modules['route']) ? $modules['route'] : 'contact-us';
@endphp
@section('title', $page_title)

@section('page_style_file')
    <link rel="stylesheet" href="{{ asset('admin/assets/cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css') }}">
    <link rel="stylesheet"
        href="{{ asset('admin/assets/cdn.datatables.net/responsive/2.2.9/css/responsive.bootstrap.min.css') }}">
@endsection

@section('breadcrumb')
    <div class="d-flex justify-content-lg-between px-1">
        @include('software.includes.breadcrumb', [
            'breadcrumbArray' => [
                ['title' => 'Other', 'url' => ''],
                ['title' => $page_title, 'url' => ''],
            ],
            'page_title' => $page_title,
            'route' => $route,
            'show_add_btn' => isset($modules['permission_add']) ? $modules['permission_add'] : false,
            'show_filter_btn' => false,
            'show_back_btn' => false,
        ])
    </div>
@endsection

@section('content')
    <div class="card my-3">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Numbers — Active only show in App</h5>
            @if (!empty($modules['permission_add']))
                <a href="{{ route('contact-us.numbers.create') }}" class="btn btn-sm btn-primary">Add Number</a>
            @endif
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Label</th>
                            <th>Mobile</th>
                            <th>Status</th>
                            <th>Order</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($numbers as $number)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $number->label }}</td>
                                <td>{{ $number->mobile }}</td>
                                <td>
                                    @if ($number->status === 'active')
                                        <span class="badge bg-success">Active</span>
                                    @else
                                        <span class="badge bg-secondary">Inactive</span>
                                    @endif
                                </td>
                                <td>{{ $number->sort_order }}</td>
                                <td class="text-center">
                                    @if (!empty($modules['permission_edit']))
                                        <a href="{{ route('contact-us.numbers.edit', $number->id) }}"
                                            class="btn btn-light btn-icon mx-1" title="Edit">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </a>
                                    @endif
                                    @if (!empty($modules['permission_delete']))
                                        <a href="javascript:void(0)" data-id="{{ $number->id }}"
                                            data-did="{{ route('contact-us.numbers.destroy', $number->id) }}"
                                            class="btn btn-danger btn-icon deletebutton mx-1" title="Delete">
                                            <i class="fa-solid fa-trash"></i>
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted">No numbers added.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card my-3">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Branches — Active only show in App</h5>
            @if (!empty($modules['permission_add']))
                <a href="{{ route('contact-us.create') }}" class="btn btn-sm btn-primary">Add Branch</a>
            @endif
        </div>
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

        var dtable = null;
        $(document).ready(function() {
            dtable = $('#yajra-datatables').DataTable({
                searching: true,
                processing: true,
                serverSide: true,
                order: [
                    [4, 'asc']
                ],
                ajax: {
                    "url": "{{ route($route . '.index') }}",
                    'beforeSend': function(request) {
                        request.setRequestHeader("X-CSRF-TOKEN", $('meta[name="csrf-token"]').attr(
                            'content'));
                    },
                    type: "GET",
                },
                columns: {!! json_encode($columns) !!},
                language: {
                    searchPlaceholder: 'Search...',
                    emptyTable: "No data available in table"
                },
            });
        });
    </script>
@endsection
