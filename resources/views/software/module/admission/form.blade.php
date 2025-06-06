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
    <style>
        .swal2-popup.swal-wide {
            width: 550px !important;
        }
    </style>

    {{-- <link href="{{ asset('public/assets/admin/plugins/datatable/dataTables.bootstrap4.min.css') }}" rel="stylesheet" type="text/css"> --}}
@endsection


@section('content')
    <div class="d-flex justify-content-lg-between px-1">
        @include('software.includes.breadcrumb', [
            'breadcrumbArray' => [
                ['title' => $page_title, 'url' => route($route . '.index')],
                [
                    'title' => isset($edit) && $edit?->id ? 'Edit ' . $page_title : 'Create ' . $page_title,
                    'url' => '',
                ],
            ],
            'route' => $route,
            'show_add_btn' => false,
            'show_back_btn' => true,
        ])
    </div>



    <div class="card shadow my-3">
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
                    <!-- Hidden fields to pass to controller -->
                    <input type="hidden" name="aadhar_card_no" id="aadhar_hidden">
                    <input type="hidden" name="admission_id" id="admission_hidden">

                    <div class="collapse show" id="aadharSection">
                        <div class="col-md-12 col-sm-12 mb-3">
                            <div class="form-group">
                                <label class="form-label"> Aadhaar Card No. <span class="text-danger">*</span> </label>
                                <input id="aadhar_card_no" type="text"
                                    class="form-control @error('aadhar_card_no') is-invalid @enderror num_only"
                                    minlength="12" maxlength="12" name="aadhar_card_no"
                                    value="{{ isset($edit?->aadhar_card_no) ? $edit?->aadhar_card_no : old('aadhar_card_no') }}"
                                    autocomplete="aadhar_card_no" autofocus placeholder="Enter Aadhaar Card No."
                                    autocapitalize="off">

                                @error('aadhar_card_no')
                                    <span class="invalid-feedback">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <hr>

                    <div class="text-left">
                        <h6 class="menu-link menu-toggle d-flex align-items-center justify-content-between"
                            data-bs-toggle="collapse" href="#personalDetails" role="button" aria-expanded="true"
                            aria-controls="personalDetails">
                            <span class="ms-3">Personal Details</span>
                        </h6>
                    </div>



                    <div class="collapse show mt-2" id="personalDetails">
                        <div class="row">
                            {{-- First Name --}}
                            <div class="col-md-6 col-sm-12 mb-3">
                                <div class="form-group">
                                    <label class="form-label"> First Name <span class="text-danger">*</span> </label>
                                    <input id="first_name" type="text"
                                        class="form-control text-uppercase @error('first_name') is-invalid @enderror"
                                        name="first_name" autocapitalize="off"
                                        value="{{ isset($edit?->first_name) ? $edit?->first_name : old('first_name') }}"
                                        placeholder="Enter First Name">
                                    @error('first_name')
                                        <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                                    @enderror
                                </div>
                            </div>

                            {{-- Last Name --}}
                            <div class="col-md-6 col-sm-12 mb-3">
                                <div class="form-group">
                                    <label class="form-label"> Last Name <span class="text-danger">*</span> </label>
                                    <input id="last_name" type="text"
                                        class="form-control text-uppercase @error('last_name') is-invalid @enderror "
                                        name="last_name" autocapitalize="off"
                                        value="{{ isset($edit?->last_name) ? $edit?->last_name : old('last_name') }}"
                                        placeholder="Enter Last Name">
                                    @error('last_name')
                                        <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                                    @enderror
                                </div>
                            </div>

                            {{-- Father Name --}}
                            <div class="col-md-6 col-sm-12 mb-3">
                                <div class="form-group">
                                    <label class="form-label"> Father's Name <span class="text-danger">*</span> </label>
                                    <input id="father_name" type="text"
                                        class="form-control text-uppercase @error('father_name') is-invalid @enderror "
                                        name="father_name" autocapitalize="off"
                                        value="{{ isset($edit?->father_name) ? $edit?->father_name : old('father_name') }}"
                                        placeholder="Enter Father Name">
                                    @error('father_name')
                                        <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                                    @enderror
                                </div>
                            </div>

                            {{-- Mother Name --}}
                            <div class="col-md-6 col-sm-12 mb-3">
                                <div class="form-group">
                                    <label class="form-label">Mother's Name<span class="text-danger">*</span> </label>
                                    <input id="mother_name" type="text"
                                        class="form-control text-uppercase @error('mother_name') is-invalid @enderror "
                                        name="mother_name" autocapitalize="off"
                                        value="{{ isset($edit?->mother_name) ? $edit?->mother_name : old('mother_name') }}"
                                        placeholder="Enter Mother Name">
                                    @error('mother_name')
                                        <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                                    @enderror
                                </div>
                            </div>

                            {{-- DOB --}}
                            <div class="col-md-6 col-sm-12 mb-3">
                                <div class="form-group">
                                    <label class="form-label"> Date of Birth <span class="text-danger">*</span> </label>
                                    <input id="date_of_birth" type="date"
                                        class="form-control @error('date_of_birth') is-invalid @enderror "
                                        name="date_of_birth" max="{{ date('Y-m-d') }}"
                                        value="{{ isset($edit?->date_of_birth) ? $edit?->date_of_birth : old('date_of_birth') }}">
                                    @error('date_of_birth')
                                        <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                                    @enderror
                                </div>
                            </div>

                            {{-- Gender --}}
                            <div class="col-md-6 col-sm-12 mb-3">
                                <div class="form-group">
                                    <small class="fw-medium d-block">Gender <span class="text-danger">*</span></small>
                                    @foreach (['Male', 'Female', 'Other'] as $gender)
                                        <div class="form-check form-check-inline mt-1">
                                            <input type="radio" name="gender" class="form-check-input"
                                                value="{{ $gender }}" id="gender_{{ $gender }}"
                                                {{ old('gender', $edit->gender ?? '') === $gender ? 'checked' : '' }}>
                                            <label class="form-check-label" for="gender_{{ $gender }}">
                                                {{ $gender }} </label>
                                        </div>
                                    @endforeach
                                    @error('gender')
                                        <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                                    @enderror
                                </div>
                            </div>

                            {{-- Cast --}}
                            <div class="col-md-6 col-sm-12 mb-3">
                                <div class="form-group">
                                    <label class="form-label"> Cast </label>
                                    <input id="cast" type="text"
                                        class="form-control @error('cast') is-invalid @enderror " name="cast"
                                        autocapitalize="off"
                                        value="{{ isset($edit?->cast) ? $edit?->cast : old('cast') }}"
                                        placeholder="Enter Cast">
                                    @error('cast')
                                        <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                                    @enderror
                                </div>
                            </div>

                            {{-- Category --}}
                            <div class="col-md-6 col-sm-12 mb-3">
                                <div class="form-group">
                                    <small class="fw-medium d-block">Category <span class="text-danger">*</span></small>
                                    @foreach (['SC', 'ST', 'OBC', 'EWS', 'General'] as $cat)
                                        <div class="form-check form-check-inline mt-1">
                                            <input type="radio" name="category" class="form-check-input"
                                                value="{{ $cat }}" id="category_{{ $cat }}"
                                                {{ old('category', $edit->category ?? '') === $cat ? 'checked' : '' }}>
                                            <label class="form-check-label" for="category_{{ $cat }}">
                                                {{ $cat }} </label>
                                        </div>
                                    @endforeach
                                    @error('category')
                                        <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                                    @enderror
                                </div>
                            </div>
                            <div class="collapse show" id="FatherOccupation">
                                <div class="col-md-12 col-sm-12  mb-3">
                                    <div class="form-group">
                                        <label class="form-label"> Father Occupation </label>
                                        <input id="occupation" type="text"
                                            class="form-control @error('occupation') is-invalid @enderror "
                                            name="occupation" autocapitalize="off"
                                            value="{{ isset($edit?->occupation) ? $edit?->occupation : old('occupation') }}"
                                            placeholder="Enter Father Occupation">

                                        @error('occupation')
                                            <span class="invalid-feedback">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            @if (isset($edit) && $edit?->gdrivefolderurl)
                                <div class="col-md-6 col-sm-12 mb-3" id="google_drive_rename_folder_col">
                                    <div class="form-group">
                                        <label class="form-label"> </label>
                                        <a href="javascript:void(0)"
                                            class="btn rounded-pill btn-success waves-effect waves-light  google_drive_rename_folder">Google
                                            Drive Rename Folder </a>
                                    </div>
                                </div>

                                <div class="col-md-6 col-sm-12 mb-3" id="google_drive_rename_folder_url_col">
                                    <div class="form-group">
                                        <label class="form-label">Google Drive Folder URL </label>
                                        <div class="input-group">
                                            <input type="text" class="form-control" name="gdrivefolderurl"
                                                id="GoogleDriveFolderUrl"
                                                value="{{ isset($edit?->gdrivefolderurl) ? $edit?->gdrivefolderurl : old('gdrivefolderurl') }}"
                                                aria-describedby="GoogleDriveFolderUrlCopy" readonly />
                                            <input type="hidden" name="gdrivefoldername" id="GoogleDriveFolderName"
                                                value="{{ isset($edit?->gdrivefoldername) ? $edit?->gdrivefoldername : old('gdrivefoldername') }}" />
                                            <input type="hidden" name="gdrivefolderid" id="GoogleDriveFolderId"
                                                value="{{ isset($edit?->gdrivefolderid) ? $edit?->gdrivefolderid : old('gdrivefolderid') }}" />
                                            <span id="GoogleDriveFolderUrlCopy" class="input-group-text cursor-pointer"><i
                                                    class="icon-base bx bx-copy-alt"></i></span>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                    <hr>
                    <div class="text-left">
                        <h6 class="text-decoration-none  menu-link menu-toggle d-flex align-items-center justify-content-between"
                            data-bs-toggle="collapse" href="#ContactDetails" role="button" aria-expanded="true"
                            aria-controls="ContactDetails">
                            <span class="ms-3">Contact Details</span>

                        </h6>
                    </div>
                    <div class="collapse show mt-2" id="ContactDetails">
                        <div class="row">
                            <div class="col-md-6 col-sm-12 mb-3">
                                <div class="form-group">
                                    <label class="form-label">Temporary Address <span class="text-danger">*</span></label>
                                    <textarea rows="3" id="temporary_address" type="text"
                                        class="form-control @error('temporary_address') is-invalid @enderror" name="temporary_address"
                                        placeholder="Enter Temporary Address">{{ isset($edit?->temporary_address) ? $edit?->temporary_address : old('temporary_address') }}</textarea>
                                    @error('temporary_address')
                                        <span class="invalid-feedback">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6 col-sm-12 mb-3">
                                <div class="form-group">
                                    <label class="form-label d-flex justify-content-between">
                                        <span>Permanent Address <span class="text-danger">*</span></span>
                                        <span>
                                            <input type="checkbox" id="copy_address" onclick="copyTemporaryAddress()" />
                                            <label for="copy_address" class="mb-0 ms-1" style="font-weight: normal;">Same as Temporary</label>
                                        </span>
                                    </label>
                                    <textarea rows="3" id="permanent_address" type="text"
                                        class="form-control @error('permanent_address') is-invalid @enderror" name="permanent_address"
                                        placeholder="Enter Permanent Address">{{ isset($edit?->permanent_address) ? $edit?->permanent_address : old('permanent_address') }}</textarea>
                                    @error('permanent_address')
                                        <span class="invalid-feedback">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>



                            <div class="col-md-6 col-sm-12  mb-3">
                                <div class="form-group">
                                    <label class="form-label"> Mobile No. <span class="text-danger">*</span> </label>
                                    <input id="mobile_no" type="text" minlength="10" maxlength="10"
                                        class="form-control @error('mobile_no') is-invalid @enderror num_only"
                                        name="mobile_no" autocapitalize="off"
                                        value="{{ isset($edit?->mobile_no) ? $edit?->mobile_no : old('mobile_no') }}"
                                        placeholder="Enter Mobile No.">

                                    @error('mobile_no')
                                        <span class="invalid-feedback">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6 col-sm-12  mb-3">
                                <div class="form-group">
                                    <label class="form-label"> Parent's Mobile No. </label>
                                    <input id="parent_mobile_no" type="text" minlength="10" maxlength="10"
                                        class="form-control @error('parent_mobile_no') is-invalid @enderror num_only"
                                        name="parent_mobile_no" autocapitalize="off"
                                        value="{{ isset($edit?->parent_mobile_no) ? $edit?->parent_mobile_no : old('parent_mobile_no') }}"
                                        placeholder="Enter Parent Mobile No.">

                                    @error('parent_mobile_no')
                                        <span class="invalid-feedback">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6 col-sm-12  mb-3">
                                <div class="form-group">
                                    <label class="form-label"> Other Mobile No. </label>
                                    <input id="other_mobile_no" type="text" minlength="10" maxlength="10"
                                        class="form-control @error('other_mobile_no') is-invalid @enderror num_only"
                                        name="other_mobile_no" autocapitalize="off"
                                        value="{{ isset($edit?->other_mobile_no) ? $edit?->other_mobile_no : old('other_mobile_no') }}"
                                        placeholder="Enter Other Mobile No.">

                                    @error('other_mobile_no')
                                        <span class="invalid-feedback">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6 col-sm-12  mb-3">
                                <div class="form-group">
                                    <label class="form-label"> Whatsapp No. </label>
                                    <input id="whatsapp_no" type="text" minlength="10" maxlength="10"
                                        class="form-control @error('whatsapp_no') is-invalid @enderror num_only"
                                        name="whatsapp_no" autocapitalize="off"
                                        value="{{ isset($edit?->whatsapp_no) ? $edit?->whatsapp_no : old('whatsapp_no') }}"
                                        placeholder="Enter Whatsapp No.">

                                    @error('whatsapp_no')
                                        <span class="invalid-feedback">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-12 col-sm-12  mb-3">
                                <div class="form-group">
                                    <label class="form-label"> Email Address <span class="text-danger">*</span> </label>
                                    <input id="email_address" type="email"
                                        class="form-control @error('email_address') is-invalid @enderror "
                                        name="email_address" autocapitalize="off"
                                        value="{{ isset($edit?->email_address) ? $edit?->email_address : old('email_address') }}"
                                        placeholder="Enter Email Address">

                                    @error('email_address')
                                        <span class="invalid-feedback">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                    <hr>
                    <div class="text-left">
                        <h6 class="text-decoration-none  menu-link menu-toggle d-flex align-items-center justify-content-between"
                            data-bs-toggle="collapse" href="#Academy" role="button" aria-expanded="true"
                            aria-controls="Academy">
                            <span class="ms-3"> Academic Details </span>
                        </h6>
                    </div>

                    <div class="collapse show mt-2" id="Academy">
                        <div class="row">
                            <div class="col-md-12 col-sm-12  mb-3">
                                <div class="form-group">
                                    <label class="form-label"> APAAR ID/ABC ID </label>
                                    <input id="apaar_id_abc_id" type="text"
                                        class="form-control @error('apaar_id_abc_id') is-invalid @enderror "
                                        autocapitalize="off" name="apaar_id_abc_id"
                                        value="{{ isset($edit?->apaar_id_abc_id) ? $edit?->apaar_id_abc_id : old('apaar_id_abc_id') }}"
                                        placeholder="Enter APAAR ID/ABC ID">

                                    @error('apaar_id_abc_id')
                                        <span class="invalid-feedback">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6 col-sm-12  mb-3">
                                <div class="form-group">
                                    <label class="form-label"> Enrollment No.</label>
                                    <input id="enrolment_no" type="text"
                                        class="form-control @error('enrolment_no') is-invalid @enderror "
                                        name="enrolment_no" autocapitalize="off"
                                        value="{{ isset($edit?->enrolment_no) ? $edit?->enrolment_no : old('enrolment_no') }}"
                                        placeholder="Enter Enrollment No.">

                                    @error('enrolment_no')
                                        <span class="invalid-feedback">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6 col-sm-12  mb-3">
                                <div class="form-group">
                                    <label class="form-label"> SPID </label>
                                    <input id="spid" type="text"
                                        class="form-control @error('spid') is-invalid @enderror " name="spid"
                                        autocapitalize="off"
                                        value="{{ isset($edit?->spid) ? $edit?->spid : old('spid') }}"
                                        placeholder="Enter SPID">

                                    @error('spid')
                                        <span class="invalid-feedback">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>




                        </div>
                    </div>
<hr>

                    <div class="col-sm-12" id="education_details">
                        <div class="content-header mt-1 mb-4 mb-3">
                            <h6 class="mb-0">Educational Details</h6>
                            <!-- <small>Enter Your Company Information.</small> -->
                            <div class="col-md-6 col-sm-12 mb-3">
                                <div class="form-group">
                                    <label class="form-label">Education <span class="text-danger">*</span></label>
                                    <select name="education_details" id="education" class="form-control select2"
                                        autofocus>
                                        <option value="" disabled selected> Select Education </option>
                                        <option value="10"> 10<sup>th</sup> </option>
                                        <option value="12"> 12<sup>th</sup> </option>
                                        <option value="Graduation"> Graduation </option>
                                    </select>
                                    @error('product_variant_id')
                                        <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <table id="education_details_table" class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Education</th>
                                    <th>Percentage or CGPA</th>
                                    <th>Seat Number or Enrollment Number</th>
                                    <th>Board or University </th>
                                    <th>Passing Year</th>
                                    <th>School Name or College Name</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                {{-- @if (isset($education))
                                    @foreach ($education as $educationdetail)
                                        <tr>
                                            <td>
                                                <input type="text" name="education[]" class="form-control"
                                                    placeholder="Education" value="{{ $educationdetail->education }}"
                                                    readonly>
                                                <input type="hidden" name="education_id[]"
                                                    value="{{ $educationdetail->id }}">
                                            </td>
                                            <td>
                                                <input type="text" name="percentage_cgpa[]"
                                                    class="form-control num_only" placeholder="Percentage or CGPA"
                                                    value="{{ $educationdetail->percentage_cgpa }}" autocapitalize="off">
                                            </td>
                                            <td>
                                                <input type="text" name="seat_no_nrollment_no[]"
                                                    class="form-control num_only"
                                                    placeholder="Enter Seat Number or Enrollment Number"
                                                    value="{{ $educationdetail->seat_no_nrollment_no }}"
                                                    autocapitalize="off">
                                            </td>
                                            <td><input type="text" name="board_university[]" class="form-control"
                                                    placeholder="Enter Board or University"
                                                    value="{{ $educationdetail->board_university }}"
                                                    autocapitalize="off"></td>
                                            <td><input type="text" name="passing_year[]" class="form-control"
                                                    placeholder="Enter Passing Year"
                                                    value="{{ $educationdetail->passing_year }}" autocapitalize="off">
                                            </td>
                                            <td><input type="text" name="school_name_college_name[]"
                                                    class="form-control" placeholder="Enter School Name or College Name"
                                                    value="{{ $educationdetail->school_name_college_name }}"
                                                    autocapitalize="off"></td>

                                            <td><button data-education_id="{{ $educationdetail->id }}" type="button"
                                                    class="btn btn-danger remove_row"><i
                                                        class="fa-solid fa-trash"></i></button></td>
                                        </tr>
                                    @endforeach
                                @endif --}}
                                @if (old('education'))
                                    @foreach (old('education') as $index => $edu)
                                        <tr>
                                            <td>
                                                <input type="text" name="education[]" class="form-control"
                                                    value="{{ $edu }}" readonly>
                                                <input type="hidden" name="education_id[]"
                                                    value="{{ old('education_id')[$index] ?? '' }}">
                                            </td>
                                            <td>
                                                <input type="text" name="percentage_cgpa[]" class="form-control"
                                                    value="{{ old('percentage_cgpa')[$index] ?? '' }}">
                                            </td>
                                            <td>
                                                <input type="text" name="seat_no_nrollment_no[]" class="form-control"
                                                    value="{{ old('seat_no_nrollment_no')[$index] ?? '' }}">
                                            </td>
                                            <td>
                                                <input type="text" name="board_university[]" class="form-control"
                                                    value="{{ old('board_university')[$index] ?? '' }}">
                                            </td>
                                            <td>
                                                <input type="text" name="passing_year[]" class="form-control"
                                                    value="{{ old('passing_year')[$index] ?? '' }}">
                                            </td>
                                            <td>
                                                <input type="text" name="school_name_college_name[]"
                                                    class="form-control"
                                                    value="{{ old('school_name_college_name')[$index] ?? '' }}">
                                            </td>
                                            <td>
                                                <button type="button" class="btn btn-danger remove_row"><i
                                                        class="fa-solid fa-trash"></i></button>
                                            </td>
                                        </tr>
                                    @endforeach
                                @elseif(isset($education))
                                    @foreach ($education as $educationdetail)
                                        <tr>
                                            <td>
                                                <input type="text" name="education[]" class="form-control"
                                                    value="{{ $educationdetail->education }}" readonly>
                                                <input type="hidden" name="education_id[]"
                                                    value="{{ $educationdetail->id }}">
                                            </td>
                                            <td>
                                                <input type="text" name="percentage_cgpa[]" class="form-control"
                                                    value="{{ $educationdetail->percentage_cgpa }}">
                                            </td>
                                            <td>
                                                <input type="text" name="seat_no_nrollment_no[]" class="form-control"
                                                    value="{{ $educationdetail->seat_no_nrollment_no }}">
                                            </td>
                                            <td>
                                                <input type="text" name="board_university[]" class="form-control"
                                                    value="{{ $educationdetail->board_university }}">
                                            </td>
                                            <td>
                                                <input type="text" name="passing_year[]" class="form-control"
                                                    value="{{ $educationdetail->passing_year }}">
                                            </td>
                                            <td>
                                                <input type="text" name="school_name_college_name[]"
                                                    class="form-control"
                                                    value="{{ $educationdetail->school_name_college_name }}">
                                            </td>
                                            <td>
                                                <button data-education_id="{{ $educationdetail->id }}" type="button"
                                                    class="btn btn-danger remove_row"><i
                                                        class="fa-solid fa-trash"></i></button>
                                            </td>
                                        </tr>
                                    @endforeach
                                @endif


                            </tbody>
                        </table>
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

@endsection

@section('page_script_file')
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endsection

@section('page_leavel_script')



    <script>
        $(function() {
            $("#aadhar_card_no").autocomplete({
                source: function(request, response) {
                    $.ajax({
                        url: "{{ route($route . '.aadhar-card-no-suggestion') }}",
                        data: {
                            term: request.term
                        },
                        dataType: 'json',
                        success: function(data) {
                            response(data);
                        },
                        error: function(xhr, status, error) {
                            console.error('Suggestion AJAX error:', error);
                        }
                    });
                },
                minLength: 3,
                select: function(event, ui) {
                    event.preventDefault(); // prevent default behavior

                    $.ajax({
                        url: "{{ route($route . '.aadhar-card-no-to-data') }}",
                        data: {
                            aadhar: ui.item.value
                        },
                        dataType: 'json',
                        success: function(data) {
                            console.log('Server response:', data);

                            // If redirect is needed
                            if (data.redirect === true && data.redirect_url) {
                                // Determine the title dynamically based on message content
                                let alertTitle = 'Info';

                                if (data.message.includes(
                                        'already have a course registration')) {
                                    alertTitle = 'Course Already Registered';
                                } else if (data.message.includes(
                                        'Admission form complete but course registration not done yet'
                                    )) {
                                    alertTitle = 'Course Registration Pending';
                                }

                                Swal.fire({
                                    title: alertTitle,
                                    html: data.message,
                                    icon: 'info',
                                    confirmButtonText: 'Proceed',
                                    customClass: {
                                        popup: 'swal-wide'
                                    }
                                }).then(() => {
                                    window.location.href = data.redirect_url;
                                });
                            }

                            // If no redirect, fill the form
                            if (data.user) {
                                $('#first_name').val(data.user.first_name || '');
                                $('#last_name').val(data.user.last_name || '');
                                $('#father_name').val(data.user.father_name || '');
                                $('#mother_name').val(data.user.mother_name || '');
                                $('#mobile_no').val(data.user.mobile_no || '');
                                $('#email_address').val(data.user.email || '');
                                $('#date_of_birth').val(data.user.date_of_birth || '');

                                $("input[name='gender'][value='" + (data.user.gender ||
                                    '') + "']").prop("checked", true);
                                $("input[name='category'][value='" + (data.user.category ||
                                    '') + "']").prop("checked", true);

                                $('#temporary_address').val(data.user.temporary_address ||
                                    '');
                                $('#permanent_address').val(data.user.permanent_address ||
                                    '');
                                $('#parent_mobile_no').val(data.user.parent_mobile_no ||
                                    '');
                                $('#other_mobile_no').val(data.user.other_mobile_no || '');
                                $('#whatsapp_no').val(data.user.whatsapp_no || '');
                                $('#cast').val(data.user.cast || '');
                                $('#occupation').val(data.user.occupation || '');
                                $('#enrolment_no').val(data.user.enrolment_no || '');
                                $('#spid').val(data.user.spid || '');
                                $('#apaar_id_abc_id').val(data.user.apaar_id_abc_id || '');

                                // Populate education details
                                $('#education_details_table tbody').empty();
                                if (Array.isArray(data.education_details)) {
                                    data.education_details.forEach(function(item) {
                                        $('#education_details_table tbody').append(`
                                                <tr>
                                                    <td>
                                                        <input type="text" name="education[]" class="form-control" placeholder="Education" value="${item.education || ''}" readonly>
                                                        <input type="hidden" name="education_id[]" value="${item.id || ''}">
                                                    </td>
                                                    <td><input type="text" name="percentage_cgpa[]" class="form-control num_only" value="${item.percentage_cgpa || ''}"></td>
                                                    <td><input type="text" name="seat_no_nrollment_no[]" class="form-control num_only" value="${item.seat_no_nrollment_no || ''}"></td>
                                                    <td><input type="text" name="board_university[]" class="form-control" value="${item.board_university || ''}"></td>
                                                    <td><input type="text" name="passing_year[]" class="form-control" value="${item.passing_year || ''}"></td>
                                                    <td><input type="text" name="school_name_college_name[]" class="form-control" value="${item.school_name_college_name || ''}"></td>
                                                    <td>
                                                        <button type="button" class="btn btn-danger remove_row">
                                                            <i class="fa-solid fa-trash"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                            `);
                                    });
                                }
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error('Data fetch AJAX error:', error);
                        }
                    });
                }
            });
        });
    </script>




    <script>
        $(document).ready(function() {



            $('#mobile_no').on('keyup', function() {
                whatsapp_no
                var mobile_no = $(this).val();
                var whatsapp_no = $('#whatsapp_no').val(mobile_no);
            });

            $('.remove_row').on('click', function() {
                $(this).closest('tr').remove();
                var education_id = $(this).data('education_id');

                $.ajax({
                    url: "{{ route($route . '.education-detail-delete') }}",
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        id: education_id,
                    },
                    success: function(response) {
                        console.log(response);
                    },
                    error: function(error) {
                        console.log(error);
                    }
                });
            });


            $('#education').on('change', function() {


                let selectededucationId = $(this).val();
                let selectededucationText = $(this).find("option:selected").text();

                if (!selectededucationId) return;
                let tableBody = $('#education_details_table tbody');

                let isVariantAlreadyAdded = tableBody.find('input[name="education[]"]').filter(function() {
                    return $(this).val() === selectededucationText;
                }).length > 0;

                if (isVariantAlreadyAdded) {
                    toastr.error('This ' + selectededucationText + ' is already added.');
                    $('#education').val('').trigger('change');
                    return;
                }


                let index = $('table tbody tr').length;
                let newRow = $('<tr>');
                newRow.html(`
                    <td>
                        <input type="text" name="education[]" class="form-control" placeholder="Education" value="` +
                    selectededucationText + `" readonly>
                        <input type="hidden" name="education_id[]" value="">
                    </td>
                    <td>
                        <input type="text" name="percentage_cgpa[]" class="form-control num_only" placeholder="Percentage or CGPA" value="" autocapitalize="off">
                    </td>
                    <td>
                        <input type="text" name="seat_no_nrollment_no[]" class="form-control num_only" placeholder="Enter Seat Number or Enrollment Number" value="" autocapitalize="off">
                    </td>
                    <td><input type="text" name="board_university[]" class="form-control" placeholder="Enter Board or University" value="" autocapitalize="off"></td>
                    <td><input type="text" name="passing_year[]" class="form-control" placeholder="Enter Passing Year" value=""></td>
                    <td><input type="text" name="school_name_college_name[]" class="form-control" placeholder="Enter School Name or College Name" value="" autocapitalize="off"></td>

                    <td><button type="button" class="btn btn-danger remove_row"><i class="fa-solid fa-trash"></i></button></td>
                `);

                tableBody.append(newRow);
                newRow.find('.remove_row').on('click', function() {
                    newRow.remove();
                });

                $('#education').val('').trigger('change');
            });
        });

        function showMessage(message, type) {
            toastr[type](message);
        }
    </script>
    <script>
        function copyTemporaryAddress() {
            const tempAddress = document.getElementById('temporary_address');
            const permAddress = document.getElementById('permanent_address');
            const checkbox = document.getElementById('copy_address');

            if (checkbox.checked) {
                permAddress.value = tempAddress.value;
                permAddress.setAttribute('readonly', true);
            } else {
                permAddress.removeAttribute('readonly');
            }

            // Optional: live update if temp address changes while checkbox is checked
            tempAddress.addEventListener('input', function () {
                if (checkbox.checked) {
                    permAddress.value = this.value;
                }
            });
        }
    </script>

    @if (isset($edit))
        <script>
            // $('#google_drive_rename_folder_url_col').hide();
            $('.google_drive_rename_folder').on('click', function() {
                var first_name = $('#first_name').val();
                var father_name = $('#father_name').val();
                var last_name = $('#last_name').val();

                let isValid = true;

                if (!first_name) {
                    isValid = false;
                    showMessage("First Name is required", "error");
                }

                if (!father_name) {
                    isValid = false;
                    showMessage("Father Name is required", "error");
                }

                if (!last_name) {
                    isValid = false;
                    showMessage("Last Name is required", "error");
                }

                var id = '{{ $edit?->id }}';
                var folderName = id + '-' + first_name + '-' + father_name + '-' + last_name;
                var OldfolderName = '{{ $edit?->gdrivefoldername }}';
                var OldfolderId = '{{ $edit?->gdrivefolderid }}';
                if (isValid) {
                    $.ajax({
                        url: "{{ route($route . '.google-drive-rename-folder') }}",
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            id: id,
                            foldername: folderName,
                            OldfolderName: OldfolderName,
                            OldfolderId: OldfolderId,
                        },
                        success: function(response) {
                            console.log(response.folder_id);
                            console.log(response.folder_url);
                            $('#GoogleDriveFolderUrl').val(response.folder_url);
                            $('#GoogleDriveFolderId').val(response.folder_id);
                            $('#GoogleDriveFolderName').val(response.folder_name);
                            $('#google_drive_rename_folder_col').hide();
                            $('#google_drive_rename_folder_url_col').show();
                        },
                        error: function(error) {
                            console.log(error);
                        }
                    });
                }
            });

            $('#GoogleDriveFolderUrlCopy').on('click', function() {
                var url = $('#GoogleDriveFolderUrl').val();

                if (url) {
                    navigator.clipboard.writeText(url).then(function() {
                        console.log('URL copied to clipboard:', url);

                        // Optional: Feedback (e.g., temporary text/icon change)
                        $('#GoogleDriveFolderUrlCopy i').removeClass('bx-copy-alt').addClass('bx-check');
                        setTimeout(function() {
                            $('#GoogleDriveFolderUrlCopy i').removeClass('bx-check').addClass(
                                'bx-copy-alt');
                        }, 1500);
                    }).catch(function(err) {
                        console.error('Failed to copy URL:', err);
                    });
                }
            });
        </script>
    @endif
@endsection
