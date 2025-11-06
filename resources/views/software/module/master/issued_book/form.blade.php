@extends('software.layout.app')

@php
    $page_title = isset($modules['title']) ? $modules['title'] : null;
    $folder_path = isset($modules['folder_path']) ? $modules['folder_path'] : null;
    $route = isset($modules['route']) ? $modules['route'] : null;
    $permisstion_prefix = isset($modules['permisstion_prefix']) ? $modules['permisstion_prefix'] : null;
@endphp
@section('title', $page_title)

@section('breadcrumb')
    <div class="d-flex justify-content-lg-between px-1">
        @include('software.includes.breadcrumb', [
            'breadcrumbArray' => [
                ['title' => $page_title, 'url' => route($route . '.index')],
                [
                    'title' => isset($edit) && $edit?->id ? 'Edit ' . $page_title : 'Create ' . $page_title,
                    'url' => '',
                ],
            ],
            'page_title' => $page_title,
            'route' => $route,
            'show_add_btn' => false,
            'show_filter_btn' => false,
            'show_back_btn' => true,
        ])
    </div>
@endsection

@section('content')
    <div class="row my-2">
        <div class="col-md-12">
            <div class="card shadow">
                <div class="card-body">
                    <form
                        action="{{ isset($edit) && $edit?->id ? route($route . '.update', [$edit?->id]) : route($route . '.store') }}"
                        method="POST" enctype="multipart/form-data">
                        @csrf
                        @isset($edit)
                            @method('PUT')
                        @endisset

                        <div class="row">
                            {{-- 🔹 Book Search --}}
                            <div class="col-md-4 col-sm-12">
                                <div class="form-group">
                                    <label for="search_id" class="form-label">Search Book</label>
                                    <input list="search" id="search_id" name="book_id"
                                        class="form-control @error('search') is-invalid @enderror"
                                        value="{{ old('book_id', isset($edit) ? $edit->book->book_name . '  ' . $edit->book->library_book_no : '') }}"
                                        placeholder="Search by book name or code" autocomplete="off">


                                    <datalist id="search"></datalist>

                                    @error('search')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            {{-- 🔹 Student Search --}}
                            <div class="col-md-4 col-sm-12">
                                <div class="form-group">
                                    <label for="admission_search" class="form-label">Search Student</label>
                                    <input list="admission_list" id="admission_search" name="student_id"
                                        class="form-control @error('admission') is-invalid @enderror"
                                        value="{{ old('student_id', isset($edit) ? $edit->student->id . ' - ' . strtoupper($edit->student->first_name . ' ' . $edit->student->last_name) : '') }}"
                                        placeholder="Search by Admission ID or Student Name" autocomplete="off">


                                    <datalist id="admission_list"></datalist>

                                    @error('admission')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-4 col-sm-12">
                                <div class="form-group">
                                    <label for="issued_date" class="form-label">Issued Date</label>
                                    <input type="date" id="issued_date" name="issued_date"
                                        class="form-control @error('issued_date') is-invalid @enderror"
                                        value="{{ old('issued_date', isset($edit) ? $edit->issued_date : date('Y-m-d')) }}">
                                    @error('issued_date')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row mt-5">
                            <div class="col-md-12 text-center">
                                <button type="submit" class="btn btn-success mt-1">
                                    {{ isset($edit) ? 'Update' : 'Submit' }}
                                </button>
                                <a href="{{ route($route . '.index') }}" class="btn btn-danger mt-1">Cancel</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('page_leavel_script')

    <script>
        $(document).ready(function() {

            // ==========================================================
            // 🔹 BOOK SEARCH DATALIST
            // ==========================================================
            function loadBookDatalist(search = '') {
                $.ajax({
                    url: "{{ route('get-books') }}",
                    type: 'GET',
                    data: {
                        search: search
                    },
                    success: function(response) {
                        $('#search').html(response);
                    },
                    error: function(xhr) {
                        console.error('Error fetching books:', xhr.responseText);
                    }
                });
            }

            // Load all books initially
            loadBookDatalist();

            // Fetch book list as user types
            $('#search_id').on('input', function() {
                const search = $(this).val().trim();
                loadBookDatalist(search);
            });

            // Reload list on focus if empty
            $('#search_id').on('focus', function() {
                if ($(this).val().trim() === '') {
                    loadBookDatalist();
                }
            });

            // ==========================================================
            // 🔹 ADMISSION (STUDENT) SEARCH DATALIST
            // ==========================================================
            function loadAdmissionDatalist(search = '') {
                $.ajax({
                    url: "{{ route('get-admission') }}",
                    type: 'GET',
                    dataType: 'json',
                    data: {
                        search: search
                    },
                    success: function(response) {
                        if (response.data && response.data.length > 0) {
                            let options = '';
                            response.data.forEach(function(item) {
                                options +=
                                    `<option value="${item.id} - ${item.name}"></option>`;
                            });
                            $('#admission_list').html(options);
                        } else {
                            $('#admission_list').empty();
                        }
                    },
                    error: function(xhr) {
                        console.error('Error fetching admissions:', xhr.responseText);
                    }
                });
            }

            // Load admission list initially
            loadAdmissionDatalist();

            // Filter as user types
            $('#admission_search').on('input', function() {
                const search = $(this).val().trim();
                loadAdmissionDatalist(search);
            });

            // Reload list on focus if empty
            $('#admission_search').on('focus', function() {
                if ($(this).val().trim() === '') {
                    loadAdmissionDatalist();
                }
            });

        });
    </script>
@endsection


