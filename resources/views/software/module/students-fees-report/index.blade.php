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
            'show_add_btn' => false,
            'show_filter_btn' => true,
            'show_export_btns' => true,
            'show_excal_btn' => true,
            'show_print_btn' => true,
            'show_back_btn' => false,
        ])
    </div>

    <!-- @include('software.partials.flash_messages') -->
    <div class="row my-3">
        <div class="col-md-12 mb-5" id="filter_section">
            <div class="card">
                <div class="card-body">
                    <div class="row" id="filter_section">

                        <div class="col-md-4 mb-2">
                            <div class="form-group">
                                <label class="form-label">Search Student Name</label>
                                <input list="search" id="search_id" name="search_id"
                                    class="form-control select_filter"
                                    value="{{ old('search', $edit->admission_id ?? ($id ?? '')) }}" placeholder="Search">
                                <datalist id="search" class="search_by_courceregistration"
                                    data-append="search_by_courceregistration"
                                    data-selectedCourceRegistrationId="{{ old('search', $edit->admission_id ?? ($id ?? '')) }}">
                                </datalist>
                                @error('search')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>


                        <div class="col-md-2 col-sm-12 mb-3">
                            <div class="form-group">
                                <label class="form-label">Filter by Course </label>
                                <select id="course_id" name="course_id"
                                    class="form-control search_by_course select2 select_filter"
                                    data-append="search_by_course" autofocus>
                                    <option value="">Filter by Course</option>
                                </select>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
        <form id="exportExcelForm" method="POST" action="{{ route('students-fees-report.export-excel') }}">
            @csrf
            <input type="hidden" name="search_id" id="export_search_id">
            <input type="hidden" name="course_id" id="export_course_id">
        </form>
        <div class="col-md-12">
            <div class="card">
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
            $('.select2').select2();

            if ($.fn.DataTable.isDataTable('#yajra-datatables')) {
                $('#yajra-datatables').DataTable().clear().destroy();
            }

            dtable = $('#yajra-datatables').DataTable({
                processing: true,
                serverSide: true,
                searching: false,

                order: [
                    [1, 'desc']
                ],
                ajax: {
                    url: "{{ route($route . '.index') }}",
                    type: "GET",
                    data: function(data) {
                        data.search_id = $('#search_id').val();
                        data.course_id = $('#course_id').val();
                        data.search = $('input[type="search"]').val();
                    }
                },
                columns: {!! json_encode($columns) !!},
                language: {
                    searchPlaceholder: 'Search...',
                },
                drawCallback: function(settings) {
                let api = this.api();
                let data = api.rows({
                    page: 'current'
                }).data();
                let search_id = $('#search_id').val();
                let course_id = $('#course_id').val();


                if (data.length === 0 && (!search_id) && (!course_id)) {
                    $('#yajra-datatables tbody').html(`
                            <tr class="odd">
                                <td valign="top" colspan="{{ count($columns) }}" class="dataTables_empty text-center text-danger">
                                    Please select <strong>Student</strong> And <strong>Course</strong> to View Records.
                                </td>
                            </tr>
                        `);
                }
            }
            });
            $(document).ready(function() {
                $('#filter_section').hide();
                $("#show_filter").click(function() {

                    if ($('#filter_section').is(':hidden')) {
                        $('#filter_section').show();
                    } else {
                        $('#filter_section').hide();
                    }
                });

                $("#cilory_filter").click(function() {
                    $('.select_filter').val(null).trigger('change');
                    $('.search').val('');
                    dtable.draw();
                });
            });

            $('.select_filter').on('change keyup', function() {
                dtable.draw();
            });

        });
    </script>
    <script>
        $(document).ready(function () {
            $('#exportExcelBtn').on('click', function (e) {
                e.preventDefault();

                let search_id = $('#search_id').val();
                let course_id = $('#course_id').val();

                if (!search_id || !course_id) {
                    alert('Please select student and course.');
                    return;
                }

                $('#export_search_id').val(search_id);
                $('#export_course_id').val(course_id);

                $('#exportExcelForm').submit();
            });
        });

        // $('#print_btn').on('click', function(e) {
        //     e.preventDefault();

        //     var search_id = $('#search_id').val();
        //     var course_id = $('#course_id').val();

        //     var queryString = '?search_id=' + encodeURIComponent(search_id) +
        //         '&course_id=' + encodeURIComponent(course_id);

        //     var url = "{{ route($route . '.print') }}" + queryString;

        //     // Redirect to new tab
        //     window.open(url, '_blank');
        // });
    </script>

<script>
    $('#print_btn').on('click', function(e) {
        e.preventDefault();

        var search_id = $('#search_id').val();
        var course_id = $('#course_id').val();

        if (!search_id || !course_id) {
            alert("Please select student and course.");
            return;
        }

        var queryString = '?search_id=' + encodeURIComponent(search_id) +
                          '&course_id=' + encodeURIComponent(course_id);



        var url = "{{ route('students-fees-report.print') }}" + queryString;

        window.open(url, '_blank');
    });
</script>


    @include('software.utils.getAdmission')
    @include('software.utils.getCourse')
    @include('software.utils.getCourceRegistration')
    @include('software.includes.script-delete-record')
    @include('software.includes.script-restore-record')
    @include('software.includes.script-update-status')
@endsection
