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
{{-- <link href="{{ asset('public/assets/admin/plugins/datatable/dataTables.bootstrap4.min.css') }}" rel="stylesheet" type="text/css"> --}}
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
                    <li class="breadcrumb-item"><a href="{{ route($route . '.index') }}">View all {{ $page_title }}</a>
                    </li>
                    <li class="breadcrumb-item active">{{ isset($edit) && $edit?->id ? 'Add' : 'Edit' }}
                        {{ $page_title }}
                    </li>
                </ol>
            </div>
        </div>
    </div>
</div>
<!-- end page title -->
@endsection

@section('content')

<!-- @include('admin.partials.flash_messages') -->

<div class="card shadow my-5">
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
                <div class="col-md-4 col-sm-12  mb-3">
                    <div class="form-group">
                        <label class="form-label"> Aadhar Card No. <span class="text-danger">*</span> </label>
                        <input id="aadhar_card_no" type="text" class="form-control @error('aadhar_card_no') is-invalid @enderror"
                            name="aadhar_card_no" value="{{ isset($edit?->aadhar_card_no) ? $edit?->aadhar_card_no : old('aadhar_card_no') }}"
                            autocomplete="aadhar_card_no" autofocus placeholder="Enter Aadhar Card No.">

                        @error('aadhar_card_no')
                        <span class="invalid-feedback">
                            <strong>{{ $message }}</strong>
                        </span>
                        @enderror
                    </div>
                </div>
                <div class="col-md-4 col-sm-12  mb-3">
                    <div class="form-group">
                        <label class="form-label"> First Name <span class="text-danger">*</span> </label>
                        <input id="first_name" type="text" class="form-control @error('first_name') is-invalid @enderror" name="first_name" value="{{ isset($edit?->first_name) ? $edit?->first_name : old('first_name') }}" placeholder="Enter First Name">

                        @error('first_name')
                        <span class="invalid-feedback">
                            <strong>{{ $message }}</strong>
                        </span>
                        @enderror
                    </div>
                </div>


                <div class="col-md-4 col-sm-12  mb-3">
                    <div class="form-group">
                        <label class="form-label"> Last Name <span class="text-danger">*</span> </label>
                        <input id="last_name" type="text" class="form-control @error('last_name') is-invalid @enderror " name="last_name" value="{{ isset($edit?->last_name) ? $edit?->last_name : old('last_name') }}" placeholder="Enter Last Name">

                        @error('last_name')
                        <span class="invalid-feedback">
                            <strong>{{ $message }}</strong>
                        </span>
                        @enderror
                    </div>
                </div>

                <div class="col-md-4 col-sm-12  mb-3">
                    <div class="form-group">
                        <label class="form-label"> Father Name <span class="text-danger">*</span> </label>
                        <input id="father_name" type="text" class="form-control @error('father_name') is-invalid @enderror " name="father_name" value="{{ isset($edit?->father_name) ? $edit?->father_name : old('father_name') }}" placeholder="Enter Father Name">

                        @error('father_name')
                        <span class="invalid-feedback">
                            <strong>{{ $message }}</strong>
                        </span>
                        @enderror
                    </div>
                </div>

                <div class="col-md-4 col-sm-12  mb-3">
                    <div class="form-group">
                        <label class="form-label"> Mother Name <span class="text-danger">*</span> </label>
                        <input id="mother_name" type="text" class="form-control @error('mother_name') is-invalid @enderror " name="mother_name" value="{{ isset($edit?->mother_name) ? $edit?->mother_name : old('mother_name') }}" placeholder="Enter Mother Name">

                        @error('mother_name')
                        <span class="invalid-feedback">
                            <strong>{{ $message }}</strong>
                        </span>
                        @enderror
                    </div>
                </div>


                <div class="col-md-4 col-sm-12  mb-3">
                    <div class="form-group">
                        <label class="form-label"> Temporary Address <span class="text-danger">*</span> </label>
                        <input id="temporary_address" type="text" class="form-control @error('temporary_address') is-invalid @enderror " name="temporary_address" value="{{ isset($edit?->temporary_address) ? $edit?->temporary_address : old('temporary_address') }}" placeholder="Enter Temporary Address">

                        @error('temporary_address')
                        <span class="invalid-feedback">
                            <strong>{{ $message }}</strong>
                        </span>
                        @enderror
                    </div>
                </div>

                <div class="col-md-4 col-sm-12  mb-3">
                    <div class="form-group">
                        <label class="form-label"> Permanent Address <span class="text-danger">*</span> </label>
                        <input id="permanent_address" type="text" class="form-control @error('permanent_address') is-invalid @enderror " name="permanent_address" value="{{ isset($edit?->permanent_address) ? $edit?->permanent_address : old('permanent_address') }}" placeholder="Enter Permanent Address">

                        @error('permanent_address')
                        <span class="invalid-feedback">
                            <strong>{{ $message }}</strong>
                        </span>
                        @enderror
                    </div>
                </div>


                <div class="col-md-4 col-sm-12  mb-3">
                    <div class="form-group">
                        <label class="form-label"> Mobile No. <span class="text-danger">*</span> </label>
                        <input id="mobile_no" type="text" class="form-control @error('mobile_no') is-invalid @enderror num_only" name="mobile_no" value="{{ isset($edit?->mobile_no) ? $edit?->mobile_no : old('mobile_no') }}" placeholder="Enter Mobile No.">

                        @error('mobile_no')
                        <span class="invalid-feedback">
                            <strong>{{ $message }}</strong>
                        </span>
                        @enderror
                    </div>
                </div>

                <div class="col-md-4 col-sm-12  mb-3">
                    <div class="form-group">
                        <label class="form-label"> Parent Mobile No. </label>
                        <input id="parent_mobile_no" type="text" class="form-control @error('parent_mobile_no') is-invalid @enderror num_only" name="parent_mobile_no" value="{{ isset($edit?->parent_mobile_no) ? $edit?->parent_mobile_no : old('parent_mobile_no') }}" placeholder="Enter Parent Mobile No.">

                        @error('parent_mobile_no')
                        <span class="invalid-feedback">
                            <strong>{{ $message }}</strong>
                        </span>
                        @enderror
                    </div>
                </div>

                <div class="col-md-4 col-sm-12  mb-3">
                    <div class="form-group">
                        <label class="form-label"> Other Mobile No. </label>
                        <input id="other_mobile_no" type="text" class="form-control @error('other_mobile_no') is-invalid @enderror num_only" name="other_mobile_no" value="{{ isset($edit?->other_mobile_no) ? $edit?->other_mobile_no : old('other_mobile_no') }}" placeholder="Enter Other Mobile No.">

                        @error('other_mobile_no')
                        <span class="invalid-feedback">
                            <strong>{{ $message }}</strong>
                        </span>
                        @enderror
                    </div>
                </div>

                <div class="col-md-4 col-sm-12  mb-3">
                    <div class="form-group">
                        <label class="form-label"> Whatsapp No. </label>
                        <input id="whatsapp_no" type="text" class="form-control @error('whatsapp_no') is-invalid @enderror num_only" name="whatsapp_no" value="{{ isset($edit?->whatsapp_no) ? $edit?->whatsapp_no : old('whatsapp_no') }}" placeholder="Enter Whatsapp No.">

                        @error('whatsapp_no')
                        <span class="invalid-feedback">
                            <strong>{{ $message }}</strong>
                        </span>
                        @enderror
                    </div>
                </div>

                <div class="col-md-4 col-sm-12  mb-3">
                    <div class="form-group">
                        <label class="form-label"> Cast </label>
                        <input id="cast" type="text" class="form-control @error('cast') is-invalid @enderror " name="cast" value="{{ isset($edit?->cast) ? $edit?->cast : old('cast') }}" placeholder="Enter Cast">

                        @error('cast')
                        <span class="invalid-feedback">
                            <strong>{{ $message }}</strong>
                        </span>
                        @enderror
                    </div>
                </div>

                <div class="col-md-4 col-sm-12  mb-3">
                    <div class="form-group">
                        <label class="form-label"> Occupation </label>
                        <input id="occupation" type="text" class="form-control @error('occupation') is-invalid @enderror " name="occupation" value="{{ isset($edit?->occupation) ? $edit?->occupation : old('occupation') }}" placeholder="Enter Occupation">

                        @error('occupation')
                        <span class="invalid-feedback">
                            <strong>{{ $message }}</strong>
                        </span>
                        @enderror
                    </div>
                </div>

                <div class="col-md-4 col-sm-12  mb-3">
                    <div class="form-group">
                        <label class="form-label"> Date of Birth <span class="text-danger">*</span> </label>
                        <input id="date_of_birth" type="date" class="form-control @error('date_of_birth') is-invalid @enderror " name="date_of_birth" value="{{ isset($edit?->date_of_birth) ? $edit?->date_of_birth : old('date_of_birth') }}" placeholder="Enter Date of Birth">

                        @error('date_of_birth')
                        <span class="invalid-feedback">
                            <strong>{{ $message }}</strong>
                        </span>
                        @enderror
                    </div>
                </div>

                <div class="col-md-4 col-sm-12  mb-3">
                    <div class="form-group">
                        <label class="form-label"> Email Address <span class="text-danger">*</span> </label>
                        <input id="email_address" type="email" class="form-control @error('email_address') is-invalid @enderror " name="email_address" value="{{ isset($edit?->email_address) ? $edit?->email_address : old('email_address') }}" placeholder="Enter Email Address">

                        @error('email_address')
                        <span class="invalid-feedback">
                            <strong>{{ $message }}</strong>
                        </span>
                        @enderror
                    </div>
                </div>

                <div class="col-md-4 col-sm-12  mb-3">
                    <div class="form-group">
                        <small class="fw-medium d-block">Gender <span class="text-danger">*</span></small>
                        <div class="form-check form-check-inline mt-1">
                            <input type="radio" name="gender" class="form-check-input" value="Male" id="gender1" {{ old('gender', $edit->gender ?? '') === 'Male' ? 'checked' : '' }}>
                            <label class="form-check-label" for="gender1"> Male</label>
                        </div>
                        <div class="form-check form-check-inline mt-1">
                            <input type="radio" name="gender" class="form-check-input" value="Female" id="gender2" {{ old('gender', $edit->gender ?? '') === 'Female' ? 'checked' : '' }}>
                            <label class="form-check-label" for="gender2"> Female</label>
                        </div>
                        <div class="form-check form-check-inline mt-1">
                            <input type="radio" name="gender" class="form-check-input" value="Other" id="gender3" {{ old('gender', $edit->gender ?? '') === 'Other' ? 'checked' : '' }}>
                            <label class="form-check-label" for="gender3"> Other</label>
                        </div>

                        @error('gender')
                        <span class="invalid-feedback">
                            <strong>{{ $message }}</strong>
                        </span>
                        @enderror
                    </div>
                </div>

                <div class="col-md-4 col-sm-12  mb-3">
                    <div class="form-group">
                        <small class="fw-medium d-block">Category <span class="text-danger">*</span></small>
                        <div class="form-check form-check-inline mt-1">
                            <input type="radio" name="category" class="form-check-input" value="SC" id="category1">
                            <label class="form-check-label" for="category1"> SC</label>
                        </div>
                        <div class="form-check form-check-inline mt-1">
                            <input type="radio" name="category" class="form-check-input" value="ST" id="category2">
                            <label class="form-check-label" for="category2"> ST</label>
                        </div>
                        <div class="form-check form-check-inline mt-1">
                            <input type="radio" name="category" class="form-check-input" value="OBC" id="category3">
                            <label class="form-check-label" for="category3"> OBC</label>
                        </div>
                        <div class="form-check form-check-inline mt-1">
                            <input type="radio" name="category" class="form-check-input" value="EWS" id="category4">
                            <label class="form-check-label" for="category4"> EWS</label>
                        </div>
                        <div class="form-check form-check-inline mt-1">
                            <input type="radio" name="category" class="form-check-input" value="General" id="category5">
                            <label class="form-check-label" for="category5"> General</label>
                        </div>

                        @error('gender')
                        <span class="invalid-feedback">
                            <strong>{{ $message }}</strong>
                        </span>
                        @enderror
                    </div>
                </div>

                <div class="col-md-4 col-sm-12  mb-3">
                    <div class="form-group">
                        <label class="form-label"> Enrolment no </label>
                        <input id="enrolment_no" type="text" class="form-control @error('enrolment_no') is-invalid @enderror " name="enrolment_no" value="{{ isset($edit?->enrolment_no) ? $edit?->enrolment_no : old('enrolment_no') }}" placeholder="Enter Enrolment no">

                        @error('enrolment_no')
                        <span class="invalid-feedback">
                            <strong>{{ $message }}</strong>
                        </span>
                        @enderror
                    </div>
                </div>

                <div class="col-md-4 col-sm-12  mb-3">
                    <div class="form-group">
                        <label class="form-label"> Spid </label>
                        <input id="spid" type="text" class="form-control @error('spid') is-invalid @enderror " name="spid" value="{{ isset($edit?->spid) ? $edit?->spid : old('spid') }}" placeholder="Enter Spid">

                        @error('spid')
                        <span class="invalid-feedback">
                            <strong>{{ $message }}</strong>
                        </span>
                        @enderror
                    </div>
                </div>


                <div class="col-md-4 col-sm-12  mb-3">
                    <div class="form-group">
                        <label class="form-label"> APAAR ID/ABC ID </label>
                        <input id="apaar_id_abc_id" type="text" class="form-control @error('apaar_id_abc_id') is-invalid @enderror " name="apaar_id_abc_id" value="{{ isset($edit?->apaar_id_abc_id) ? $edit?->apaar_id_abc_id : old('apaar_id_abc_id') }}" placeholder="Enter APAAR ID/ABC ID">

                        @error('apaar_id_abc_id')
                        <span class="invalid-feedback">
                            <strong>{{ $message }}</strong>
                        </span>
                        @enderror
                    </div>
                </div>

                <div class="col-md-4 col-sm-12 mb-3">
                    <div class="form-group">
                        <label class="form-label">Education <span class="text-danger">*</span></label>
                        <select name="education_details" id="education"
                            class="form-control select2" autofocus>
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
                <div class="col-sm-12">
                    <hr>
                </div>

                <div class="col-sm-12" id="education_details">
                    <div class="content-header mt-1 mb-4 mb-3">
                        <h6 class="mb-0">Education Details</h6>
                        <!-- <small>Enter Your Company Information.</small> -->
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


@section('page_leavel_script')

<script>
        $(document).ready(function() {
            $('.remove_row').on('click', function() {
                $(this).closest('tr').remove();
            });

            $('#filter_section').hide();
            $('#education').on('change', function () {


                let selectededucationId = $(this).val();
                let selectededucationText = $(this).find("option:selected").text();

                if (!selectededucationId) return;
                let tableBody = $('#education_details_table tbody');

                let isVariantAlreadyAdded = tableBody.find('input[name="education[]"]').filter(function () {
                    return $(this).val() === selectededucationText;
                }).length > 0;

                if (isVariantAlreadyAdded) {
                    toastr.error('This '+selectededucationText+' is already added.');
                    $('#education').val('').trigger('change');
                    return;
                }

                
                let index = $('table tbody tr').length;
                let newRow = $('<tr>');
                newRow.html(`
                    <td>
                        <input type="text" name="education[]" class="form-control" placeholder="Education" value="`+selectededucationText+`" readonly>
                    </td>
                    <td>
                        <input type="text" name="percentage_cgpa[]" class="form-control num_only" placeholder="Percentage or CGPA" value="" >
                    </td>
                    <td>
                        <input type="text" name="seat_no_nrollment_no[]" class="form-control num_only" placeholder="Enter Seat Number or Enrollment Number" value="">
                    </td>
                    <td><input type="text" name="board_university[]" class="form-control" placeholder="Enter Board or University" value=""></td>
                    <td><input type="text" name="passing_year[]" class="form-control" placeholder="Enter Passing Year" value=""></td>
                    <td><input type="text" name="school_name_college_name[]" class="form-control" placeholder="Enter School Name or College Name" value=""></td>

                    <td><button type="button" class="btn btn-danger remove_row"><i class="fa-solid fa-trash"></i></button></td>
                `);

                tableBody.append(newRow);
                newRow.find('.remove_row').on('click', function () {
                    newRow.remove();
                });

                $('#education').val('').trigger('change');
            });
        });
    </script>

@endsection