@extends('software.layout.app')

@php
    $page_title = $modules['title'] ?? 'Letter of Recommendation';
    $route = $modules['route'] ?? null;
@endphp

@section('title', $page_title)

@section('page_style_file')
    <link rel="stylesheet" href="{{ asset('admin/assets/vendor/libs/select2/select2.css') }}">
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
        <div class="col-md-12">
            <div class="card shadow-sm">
                <div class="card-body">
                    <form id="letterRecommendationForm" method="POST" action="{{ route('issue-certificate.store-history') }}">
                        @csrf
                        <input type="hidden" name="certificate_type" value="letter-recommendation">

                        <!-- Row 1: Student, Date, Recommender Type -->
                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-4">
                                    <label class="form-label">Student <span class="text-danger">*</span></label>
                                    <select class="form-select select2" id="student" name="student_id" required>
                                        <option value="">-- Select Student --</option>
                                        @foreach ($students as $student)
                                            <option value="{{ $student['id'] }}">{{ $student['label'] }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="mb-4">
                                    <label class="form-label">Date</label>
                                    <input type="date" class="form-control" id="date" name="issue_date"
                                        value="{{ $defaults['issue_date'] ?? now()->format('Y-m-d') }}">
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="mb-4">
                                    <label class="form-label">Recommender Type</label>

                                    <div class="d-flex gap-3 mt-2">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio"
                                                name="recommender_type" value="principal"
                                                id="principal" checked>

                                            <label class="form-check-label" for="principal">
                                                Principal
                                            </label>
                                        </div>

                                        <div class="form-check">
                                            <input class="form-check-input" type="radio"
                                                name="recommender_type" value="faculty"
                                                id="faculty">

                                            <label class="form-check-label" for="faculty">
                                                Faculty
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Faculty Section -->
                        <div class="row d-none" id="facultySection">
                        {{-- <div class="row" id="facultySection" style="display:none;"> --}}
                            <div class="col-md-6">
                                <div class="mb-4">
                                    <label class="form-label">Recommender Designation</label>
                                    <input type="text"
                                           class="form-control"
                                           id="recommenderDesignation"
                                           name="recommender_designation"
                                           value="Faculty"
                                           readonly>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-4">
                                    <label class="form-label">Select Faculty</label>
                                    <select class="form-select select2" id="selectFaculty" name="faculty_id">
                                        <option value="">Select Faculty</option>
                                        @foreach ($faculties as $faculty)
                                            <option value="{{ $faculty->id }}">{{ $faculty->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Button -->
                        <div class="row mt-4">
                            <div class="col-12">
                                <button type="submit" id="generate_certificate" class="btn btn-primary">
                                    <i class="ti ti-certificate"></i> Generate
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-12">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0">Generate History</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered mb-0" id="certificate-history-table">
                            <thead>
                                <tr>
                                    <th class="text-center" style="width: 60px;">No</th>
                                    <th>Student Name</th>
                                    <th>Date</th>
                                    <th>Recommender</th>
                                    <th class="text-center" style="width: 90px;">View</th>
                                </tr>
                            </thead>
                            <tbody id="certificate-history-body">
                                @forelse ($histories as $index => $history)
                                    <tr>
                                        <td class="text-center">{{ $index + 1 }}</td>
                                        <td>{{ $history['student_name'] ?? '-' }}</td>
                                        <td>{{ $history['issue_date'] ?? '-' }}</td>
                                        <td>{{ $history['recommender_name'] ?? '-' }}</td>
                                        <td class="text-center">
                                            <a href="{{ $history['certificate_url'] }}" target="_blank" class="btn btn-sm btn-secondary" title="View Certificate">
                                                <i class="ti ti-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr id="certificate-history-empty-row">
                                        <td colspan="5" class="text-center text-muted py-3">No history found</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="{{ asset('admin/assets/vendor/libs/select2/select2.js') }}"></script>
    <script>
    document.addEventListener("DOMContentLoaded", function () {
        const principalRadio = document.getElementById("principal");
        const facultyRadio = document.getElementById("faculty");

        const facultySection = document.getElementById("facultySection");
        const designation = document.getElementById("recommenderDesignation");

        // function toggleSection() {
        //     if (principalRadio.checked) {
        //         facultySection.style.display = "none";
        //     } else {
        //         facultySection.style.display = "block";
        //         designation.value = "Faculty";
        //     }
        // }
        function toggleSection() {

    if (principalRadio.checked) {

        facultySection.classList.add('d-none');

        $('#selectFaculty').val('').trigger('change');

    } else {

        facultySection.classList.remove('d-none');

        designation.value = "Faculty";
    }
}


        principalRadio.addEventListener("change", toggleSection);
        facultyRadio.addEventListener("change", toggleSection);

        toggleSection();

        // Initialize Select2
        $('.select2').select2({
            placeholder: 'Select an option',
            allowClear: true,
            width: '100%'
        });

        // Form submission
        $('#letterRecommendationForm').on('submit', function(e) {
            e.preventDefault();

            const studentId = $('#student').val();
            if (!studentId) {
                alert('Please select a student');
                return;
            }

            const recommenderType = $('input[name="recommender_type"]:checked').val();
            const facultyId = $('#selectFaculty').val();

            if (recommenderType === 'faculty' && !facultyId) {
                alert('Please select a faculty member');
                return;
            }

            const formData = {
                _token: '{{ csrf_token() }}',
                certificate_type: 'letter-recommendation',
                student_id: studentId,
                issue_date: $('#date').val(),
                recommender_type: recommenderType,
                faculty_id: facultyId || null,
                recommender_name: recommenderType === 'principal' ? 'Hitesh Vadalia' : $('#selectFaculty option:selected').text(),
                recommender_designation: recommenderType === 'principal' ? 'I/C. Principal' : $('#recommenderDesignation').val(),
            };

            $.ajax({
                url: $(this).attr('action'),
                type: 'POST',
                data: formData,
                success: function(response) {
                    if (response.status) {
                        // Add new row to history table
                        const rowNum = $('#certificate-history-body tr:not(#certificate-history-empty-row)').length + 1;
                        const row = `
                            <tr>
                                <td class="text-center">${rowNum}</td>
                                <td>${response.data.student_name || '-'}</td>
                                <td>${response.data.issue_date || '-'}</td>
                                <td>${response.data.recommender_name || '-'}</td>
                                <td class="text-center">
                                    <a href="${response.data.certificate_url}" target="_blank" class="btn btn-sm btn-secondary" title="View Certificate">
                                        <i class="ti ti-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        `;

                        $('#certificate-history-empty-row').remove();
                        $('#certificate-history-body').prepend(row);

                        // Reindex rows
                        $('#certificate-history-body tr').each(function(index) {
                            $(this).find('td:first').text(index + 1);
                        });

                        // Open certificate in new tab
                        window.open(response.data.certificate_url, '_blank');

                        // Reset form
                        $('#letterRecommendationForm')[0].reset();
                        $('#student').val('').trigger('change');
                        $('#selectFaculty').val('').trigger('change');
                        $('#principal').prop('checked', true).trigger('change');

                        if (typeof toastr !== 'undefined') {
                            toastr.success(response.message || 'Certificate generated successfully');
                        }
                    } else {
                        if (typeof toastr !== 'undefined') {
                            toastr.error(response.message);
                        } else {
                            alert(response.message);
                        }
                    }
                },
                error: function(xhr) {
                    const response = xhr.responseJSON;
                    const message = response?.message || 'An error occurred';
                    if (typeof toastr !== 'undefined') {
                        toastr.error(message);
                    } else {
                        alert(message);
                    }
                }
            });
        });
    });
    </script>
@endsection
