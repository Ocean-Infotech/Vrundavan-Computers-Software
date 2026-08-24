@extends('software.layout.app')

@php
    $page_title = isset($modules['title']) ? $modules['title'] : 'Student Results Report';
    $route = isset($modules['route']) ? $modules['route'] : 'result.report';
@endphp
@section('title', $page_title)

@section('content')
    <div class="d-flex justify-content-lg-between px-1 no-print">
        @include('software.includes.breadcrumb', [
            'breadcrumbArray' => [['title' => $page_title, 'url' => '']],
            'route' => $route,
            'show_add_btn' => false,
            'show_filter_btn' => false,
            'show_export_btns' => true,
            'show_excal_btn' => true,
            'show_print_btn' => false,
            'show_back_btn' => false,
        ])
    </div>

    <!-- Filter Card -->
    <div class="card shadow mb-4 no-print">
        <div class="card-body">
            <div class="row align-items-end">
                <!-- Course -->
                <div class="col-md-3 mb-3">
                    <label class="form-label fw-bold">Course</label>
                    <select class="form-control select2" id="courseSelect">
                        <option value="">Select Course</option>
                        @foreach ($courses as $course)
                            <option value="{{ $course->id }}">{{ $course->course_name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Batch -->
                <div class="col-md-3 mb-3">
                    <label class="form-label fw-bold">Batch</label>
                    <select class="form-control select2" id="batchSelect">
                        <option value="">Select Batch</option>
                    </select>
                </div>

                <!-- Class -->
                <div class="col-md-3 mb-3">
                    <label class="form-label fw-bold">Class</label>
                    <select class="form-control select2" id="classSelect" name="class_ids[]" multiple="multiple" data-placeholder="Select Classes">
                    </select>
                </div>

                <div class="col-md-3 mb-3">
                    <button id="loadReportBtn" class="btn btn-primary w-100"><i class="bx bx-search-alt me-1"></i> Generate Report</button>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow d-none" id="reportCard">
        <div class="card-header border-bottom d-flex justify-content-between align-items-center no-print">
            <h5 class="mb-0 fw-bold" id="reportTitle">Student Results Report</h5>
        </div>
        <div class="card-body mt-3">
            <div class="text-center print-header-banner d-none d-print-block mb-4">
                <h3 class="fw-bold mb-0">Student Semester Results Report</h3>
                <p class="text-muted" id="printSubtitle"></p>
            </div>
            
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover align-middle text-nowrap" id="reportTable" style="width:100%; min-width: 1000px;">
                    <thead class="table-light" id="report-thead">
                        <!-- Dynamic Header -->
                    </thead>
                    <tbody id="report-tbody">
                        <!-- Dynamic Rows -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@section('page_leavel_script')
    <script>
        $(document).ready(function() {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            // Course change AJAX
            $('#courseSelect').on('change', function() {
                let courseId = $(this).val();
                $('#batchSelect').empty().append('<option value="">Select Batch</option>').val('').trigger('change.select2');
                $('#classSelect').empty().val([]).trigger('change.select2');

                if (courseId) {
                    $.ajax({
                        type: 'POST',
                        url: "{{ route('get-batch') }}",
                        dataType: 'json',
                        data: {
                            course_id: courseId
                        },
                        success: function(response) {
                            if ((response.status === true || response.status === "true") && response.data) {
                                let options = '<option value="">Select Batch</option>';
                                response.data.forEach(function(item) {
                                    options += `<option value="${item.id}">${item.name}</option>`;
                                });
                                $('#batchSelect').html(options).trigger('change.select2');
                            }
                        }
                    });
                }
            });

            // Batch change AJAX
            $('#batchSelect').on('change', function() {
                let batchId = $(this).val();
                $('#classSelect').empty().val([]).trigger('change.select2');

                if (batchId) {
                    $.ajax({
                        type: 'POST',
                        url: "{{ route('get-class-bybatch') }}",
                        dataType: 'json',
                        data: {
                            batch_id: batchId
                        },
                        success: function(response) {
                            if ((response.status === true || response.status === "true") && response.data) {
                                let options = '';
                                response.data.forEach(function(item) {
                                    options += `<option value="${item.id}">${item.name}</option>`;
                                });
                                $('#classSelect').html(options).trigger('change.select2');
                            }
                        }
                    });
                }
            });

            let loadBtn = $('#loadReportBtn');
            let reportCard = $('#reportCard');
            let reportThead = $('#report-thead');
            let reportTbody = $('#report-tbody');

            loadBtn.on('click', function() {
                let courseId = $('#courseSelect').val();
                let batchId = $('#batchSelect').val();
                let classId = $('#classSelect').val();

                let courseName = $('#courseSelect option:selected').text();
                let batchName = $('#batchSelect option:selected').text();
                
                let selectedClasses = [];
                $('#classSelect option:selected').each(function() {
                    selectedClasses.push($(this).text());
                });
                let className = selectedClasses.join(', ');

                if (!courseId || !batchId || !classId || classId.length === 0) {
                    toastr.warning('Please select all filters first.');
                    return;
                }

                loadBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Compiling...');

                $.ajax({
                    url: "{{ route('result.report.data') }}",
                    type: "GET",
                    data: {
                        course_id: courseId,
                        batch_id: batchId,
                        class_id: classId
                    },
                    success: function(response) {
                        loadBtn.prop('disabled', false).html('<i class="bx bx-search-alt me-1"></i> Generate Report');
                        if (response.success) {
                            $('#reportTitle').text(`${courseName} - ${batchName} - Class ${className} - Student Results`);
                            $('#printSubtitle').text(`Course: ${courseName} | Batch: ${batchName} | Class: ${className}`);

                            // Build table header dynamically
                            let headerHtml = `
                                <tr>
                                    <th rowspan="2" class="text-center align-middle" style="width: 5%;">No</th>
                                    <th rowspan="2" class="text-center align-middle" style="width: 8%;">GR No</th>
                                    <th rowspan="2" class="align-middle">Student Name</th>
                            `;
                            
                            response.semesters.forEach(function(sem) {
                                if (parseInt(sem) === 0) {
                                    headerHtml += `<th colspan="2" class="text-center">HSC / Entry</th>`;
                                } else {
                                    headerHtml += `<th colspan="2" class="text-center">Semester ${sem}</th>`;
                                }
                            });

                            headerHtml += `</tr><tr>`;
                            response.semesters.forEach(function(sem) {
                                headerHtml += `<th class="text-center">Marks</th><th class="text-center">%</th>`;
                            });
                            headerHtml += `</tr>`;

                            reportThead.html(headerHtml);

                            // Build table body
                            reportTbody.empty();
                            if (response.students.length > 0) {
                                response.students.forEach(function(student, idx) {
                                    let rowHtml = `
                                        <tr>
                                            <td class="text-center">${idx + 1}</td>
                                            <td class="text-center">${student.gr_no}</td>
                                            <td class="fw-bold">${student.name}</td>
                                    `;

                                    student.semesters.forEach(function(sem) {
                                        rowHtml += `
                                            <td class="text-center">${sem.marks}</td>
                                            <td class="text-center fw-bold ${parseInt(sem.semester) === 0 ? 'text-primary' : 'text-success'}">${sem.percentage}</td>
                                        `;
                                    });

                                    rowHtml += `</tr>`;
                                    reportTbody.append(rowHtml);
                                });
                                reportCard.removeClass('d-none');
                            } else {
                                let colspan = 5 + (response.semesters.length * 2);
                                reportTbody.html(`<tr><td colspan="${colspan}" class="text-center text-muted">No student records found.</td></tr>`);
                                reportCard.removeClass('d-none');
                            }
                        }
                    },
                    error: function(xhr) {
                        loadBtn.prop('disabled', false).html('<i class="bx bx-search-alt me-1"></i> Generate Report');
                        toastr.error('Error compiling results report.');
                    }
                });
            });

            $(document).on('click', '#exportExcelBtn', function(e) {
                e.preventDefault();
                let courseId = $('#courseSelect').val();
                let batchId = $('#batchSelect').val();
                let classIds = $('#classSelect').val();

                if (!courseId || !batchId || !classIds || classIds.length === 0) {
                    alert('Please select course, batch, and class.');
                    return;
                }

                $('#excel_course_id').val(courseId);
                $('#excel_batch_id').val(batchId);
                
                let container = $('#excel_class_ids_container').empty();
                classIds.forEach(function(val) {
                    container.append(`<input type="hidden" name="class_id[]" value="${val}">`);
                });

                $('#exportExcelForm').submit();
            });
        });
    </script>

    <form id="exportExcelForm" method="POST" action="{{ route('result.report.export-excel') }}" class="d-none">
        @csrf
        <input type="hidden" name="course_id" id="excel_course_id">
        <input type="hidden" name="batch_id" id="excel_batch_id">
        <div id="excel_class_ids_container"></div>
    </form>
@endsection
