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
@endsection

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
            // 'show_add_btn' => $modules['addPermission'],
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
                <div class="card-header d-none">
                    <h2 class="mb-0">
                        {{ $page_title }}
                    </h2>
                </div>
                <div class="card-body my-4">
                    <form
                        action="{{ isset($edit) && $edit?->id ? route($route . '.update', [$edit?->id]) : route($route . '.store') }}"
                        method="POST" enctype="multipart/form-data">
                        @csrf
                        @isset($edit)
                            @method('PUT')
                        @endisset
                        <div class="row">

                            <div class="col-md-4 col-sm-12">
                                <div class="form-group">
                                    <label class="form-label"> Book Name <span class="text-danger">*</span> </label>
                                    <input id="book_name" type="text"
                                        class="form-control @error('book_name') is-invalid @enderror" name="book_name"
                                        value="{{ isset($edit?->book_name) ? $edit?->book_name : old('book_name') }}"
                                        autocomplete="book_name" autofocus placeholder="Enter Book Name">

                                    @error('book_name')
                                        <span class="invalid-feedback">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4 col-sm-12">
                                <div class="form-group">
                                    <label class="form-label"> Library Book No. <span class="text-danger">*</span> </label>
                                    <input id="library_book_no" type="text"
                                        class="form-control @error('library_book_no') is-invalid @enderror"
                                        name="library_book_no"
                                        value="{{ isset($edit?->library_book_no) ? $edit?->library_book_no : old('library_book_no') }}"
                                        autocomplete="library_book_no" autofocus placeholder="Enter Library Book No.">

                                    @error('library_book_no')
                                        <span class="invalid-feedback">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-4 col-sm-12">
                                <div class="form-group">
                                    <label class="form-label"> Author Name <span class="text-danger">*</span> </label>
                                    <input id="author_name" type="text"
                                        class="form-control @error('author_name') is-invalid @enderror" name="author_name"
                                        value="{{ isset($edit?->author_name) ? $edit?->author_name : old('author_name') }}"
                                        autocomplete="author_name" autofocus placeholder="Enter Author Name">

                                    @error('author_name')
                                        <span class="invalid-feedback">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-4 col-sm-12">
                                <div class="form-group">
                                    <label class="form-label"> Publisher Name <span class="text-danger">*</span> </label>
                                    <input id="publisher_name" type="text"
                                        class="form-control @error('publisher_name') is-invalid @enderror"
                                        name="publisher_name"
                                        value="{{ isset($edit?->publisher_name) ? $edit?->publisher_name : old('publisher_name') }}"
                                        autocomplete="publisher_name" placeholder="Enter Publisher Name">

                                    @error('publisher_name')
                                        <span class="invalid-feedback">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-4 col-sm-12">
                                <div class="form-group">
                                    <label class="form-label"> Total Number of Pages <span class="text-danger">*</span>
                                    </label>
                                    <input id="total_number_of_page" type="number" min="1"
                                        class="form-control @error('total_number_of_page') is-invalid @enderror" name="total_number_of_page"
                                        value="{{ isset($edit?->total_number_of_page) ? $edit?->total_number_of_page : old('total_number_of_page') }}"
                                        autocomplete="total_number_of_page" placeholder="Enter Total Number of Pages">

                                    @error('total_number_of_page')
                                        <span class="invalid-feedback">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>

                            {{-- Date --}}
                             <div class="col-md-4 col-sm-12">
                                <div class="form-group">
                                    <label for="purchase_date">Purchase Date <span class="text-danger">*</span></label>
                                    <input type="purchase_date" name="purchase_date" id="purchase_date"
                                        class="form-control required datepicker @error('date') is-invalid @enderror"
                                        value="{{ isset($edit?->date) ? $edit->date : old('date') ?? \Carbon\Carbon::now()->format('d-m-Y') }}"
                                        placeholder="Date">
                                    @error('date')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-4 col-sm-12">
                                <div class="form-group">
                                    <label class="form-label"> Price <span class="text-danger">*</span> </label>
                                    <input id="price" type="number" step="0.01" min="0"
                                        class="form-control @error('price') is-invalid @enderror" name="price"
                                        value="{{ isset($edit?->price) ? $edit?->price : old('price') }}"
                                        autocomplete="price" placeholder="Enter Price">

                                    @error('price')
                                        <span class="invalid-feedback">
                                            <strong>{{ $message }}</strong>
                                        </span>
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
    @include('software.utils.getCourse')
     @include('software.includes.script-delete-record')
    @include('software.includes.script-restore-record')
    @include('software.includes.script-update-status')
    <script>
         $(document).ready(function() {
            $('#purchase_date').on('click', function() {
                this.showPicker?.(); // Opens the datepicker if browser supports `showPicker()`
            });
        });
    </script>
@endsection
