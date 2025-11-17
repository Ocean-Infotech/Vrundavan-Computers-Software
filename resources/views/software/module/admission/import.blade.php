

@extends('software.layout.app')

@php
    $page_title = 'Registered Student';

    // $page_title = isset($modules['title']) ? $modules['title'] : null;
    $folder_path = isset($modules['folder_path']) ? $modules['folder_path'] : null;
    $route = isset($modules['route']) ? $modules['route'] : null;
    $permisstion_prefix = isset($modules['permisstion_prefix']) ? $modules['permisstion_prefix'] : null;
    $course_register_add = isset($modules['course-register_add']) && $modules['course-register_add'] ? 'true' : 'false';
    $fees_collection_create =
        isset($modules['fees-collection-create']) && $modules['fees-collection-create'] ? 'true' : 'false';
    $fees_collection_edit =
        isset($modules['fees-collection-edit']) && $modules['fees-collection-edit'] ? 'true' : 'false';
    $i = 0;
@endphp
@section('title', $page_title)

@section('page_style_file')
    {{-- <link href="{{ asset('public/assets/admin/plugins/datatable/dataTables.bootstrap4.min.css') }}" rel="stylesheet" type="text/css"> --}}
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="{{ asset('admin/assets/cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css') }}">
    <link rel="stylesheet"
        href="{{ asset('admin/assets/cdn.datatables.net/responsive/2.2.9/css/responsive.bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('admin/assets/cdn.datatables.net/buttons/2.2.2/css/buttons.dataTables.min.css') }}">
    <!-- <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">                                                                                                                                        <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.dataTables.min.css"> -->

@endsection

@section('content')
    <div class="d-flex justify-content-lg-between px-1">
        @include('software.includes.breadcrumb', [
            'breadcrumbArray' => [['title' => $page_title, 'url' => '']],
            'route' => $route,
            'show_add_btn' => false,
            'show_filter_btn' => false,
            'show_export_btns' => false,
            'show_excal_btn' => false,
            'show_print_btn' => false,
            'show_back_btn' => false,
        ])
    </div>


    <div class="row my-3">
        <div class="card">
            <div class="card-header">
                <h5>Import Student</h5>
            </div>

            <div class="card-body">
                <form action="{{ route('admission.import-store') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="row">

                        {{-- Import File --}}
                        <div class="col-md-4 col-sm-12">
                            <div class="form-group mb-3">
                                <label class="form-label">Import File <span class="text-danger">*</span></label>

                                <input id="import_file" type="file"
                                    class="form-control @error('import_file') is-invalid @enderror" name="import_file"
                                    required accept=".xlsx,.xls">

                                @error('import_file')
                                    <span class="invalid-feedback">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        {{-- Sample File Download --}}
                        <div class="col-md-4 d-flex align-items-end mb-3 mt-4 bg-custom-light text-center"
                            id="sampleFileDiv" style="background-color:#e9ecef; border-radius:5px;">

                            <a href="{{ asset('uploads\import_student\import_student_sample.xlsx') }}"
                                class="btn text-primary mx-auto" download>
                                <i class="fa fa-download"></i> Download Sample File
                            </a>
                        </div>


                    </div>

                    <div class="divider">
                        <hr />
                    </div>

                    {{-- Submit + Cancel --}}
                    <div class="col-md-12 text-center mt-2">
                        <button type="submit" id="submitBtn" class="btn btn-success mt-1 mb-1">Submit</button>
                        <a href="{{ route('admission.index') }}" class="btn btn-danger mt-1 mb-1">Cancel</a>
                    </div>

                    {{-- Summary --}}
                @php $report = session()->pull('import_report'); @endphp


                    @if ($report)

                        {{-- Summary --}}
                        <div class="alert alert-info mt-3">
                            Import Finished —
                            Inserted: {{ count($report['inserted']) }},
                            Skipped: {{ count($report['skipped']) }},
                            Errors: {{ count($report['errors']) }}
                        </div>

                        {{-- Skipped Rows --}}
                        @if (count($report['skipped']))
                            <div class="mt-3">
                                <h5>Skipped Rows:</h5>
                                @foreach ($report['skipped'] as $r)
                                    <div style="padding:6px 0; border-bottom:1px solid #ddd;">
                                        <strong>Row {{ $r['row'] }} —</strong>
                                        {{ $r['reason'] }}
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        {{-- Error Rows --}}
                        @if (count($report['errors']))
                            <div class="mt-4">
                                <h5>Rows With Errors:</h5>
                                @foreach ($report['errors'] as $e)
                                    <div style="padding:6px 0; border-bottom:1px solid #ddd;">
                                        <strong>Row {{ $e['row'] }} —</strong>
                                        {{ $e['message'] }}
                                    </div>
                                @endforeach
                            </div>
                        @endif

                    @endif




                </form>
            </div>
        </div>
    </div>



@endsection

@section('page_script_file')
    <!-- jQuery (required by DataTables) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>

    <!-- DataTables JS -->
    <script src="{{ asset('admin/assets/cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('admin/assets/cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js') }}"></script>
    <script src="{{ asset('admin/assets/cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js') }}"></script>

    <!-- <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
                                                                                                                                                                                                                                                        <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script> -->

@endsection

@section('page_leavel_script')
    <script type="text/javascript">
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
    </script>



    @include('software.includes.script-delete-record')
    @include('software.includes.script-restore-record')
    {{-- @include('software.includes.script-update-status') --}}
@endsection
