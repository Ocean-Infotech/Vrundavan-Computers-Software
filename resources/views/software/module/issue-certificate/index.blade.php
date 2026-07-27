@extends('software.layout.app')

@php
    $page_title = $modules['title'] ?? 'Issue Certificate';
    $route = $modules['route'] ?? null;
    $certificateRoute = $modules['certificate_route'] ?? null;
    $certificateType = $modules['certificate_type'] ?? 'bonafide';
    $hideExtraColumns = in_array($certificateType, ['english-medium'], true);
@endphp
@section('title', $page_title)

@section('page_style_file')
    <link rel="stylesheet" href="{{ asset('admin/assets/vendor/libs/flatpickr/flatpickr.css') }}">
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
                    <form id="certificateForm">
                        <div class="row align-items-end">
                            <div class="col-md-6 col-sm-12 mb-3">
                                <label class="form-label">Student <span class="text-danger">*</span></label>
                                <select id="student_id" name="student_id" class="form-control select2" required>
                                    <option value="">Select Student</option>
                                    @foreach ($students as $student)
                                        <option value="{{ $student['id'] }}">{{ $student['label'] }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-3 col-sm-12 mb-3">
                                <label class="form-label">Date</label>
                                <input type="date" id="issue_date" name="issue_date" class="form-control"
                                    value="{{ $defaults['issue_date'] ?? now()->format('Y-m-d') }}">
                            </div>

                            @if (!in_array(($modules['certificate_type'] ?? 'bonafide'), ['english-medium', 'letter-recommendation'], true))
                            <div class="col-md-3 col-sm-12 mb-3">
                                <label class="form-label">Academic Year</label>
                                <input type="text" id="academic_year" name="academic_year" class="form-control"
                                    value="{{ $defaults['academic_year'] ?? '' }}" placeholder="Ex. {{ now()->format('Y') }}">
                            </div>

                            <div class="col-md-3 col-sm-12 mb-3">
                                <label class="form-label">Semester Duration Start Date</label>
                                <input type="text" id="semester_start" name="semester_start"
                                    class="form-control semester-date-picker" placeholder="dd-mm-yyyy" autocomplete="off">
                            </div>

                            <div class="col-md-3 col-sm-12 mb-3">
                                <label class="form-label">Semester Duration End Date</label>
                                <input type="text" id="semester_end" name="semester_end"
                                    class="form-control semester-date-picker" placeholder="dd-mm-yyyy" autocomplete="off">
                            </div>
                            @endif

                             <div class="col-md-6 col-sm-12 mb-3">
                                 <button type="button" id="generate_certificate" class="btn btn-primary">
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
                                    @unless($hideExtraColumns)
                                        <th>Academic Year</th>
                                        <th>Semester Start</th>
                                        <th>Semester End</th>
                                        <th>Generated At</th>
                                    @endunless
                                    <th class="text-center" style="width: 90px;">View</th>
                                </tr>
                            </thead>
                            <tbody id="certificate-history-body">
                                @include('software.module.issue-certificate.partials.history-rows', [
                                    'histories' => $histories ?? collect(),
                                    'hideExtraColumns' => $hideExtraColumns,
                                ])
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('page_script_file')
    <script src="{{ asset('admin/assets/vendor/libs/flatpickr/flatpickr.js') }}"></script>
@endsection

@section('page_leavel_script')
    <script type="text/javascript">
        const certificateType = @json($modules['certificate_type'] ?? 'bonafide');
        const hideExtraColumns = @json($hideExtraColumns);

        function prependHistoryRow(row) {
            $('#certificate-history-empty-row').remove();

            const html = `
                <tr>
                    <td class="text-center">1</td>
                    <td>${row.student_name || '-'}</td>
                    <td>${row.issue_date || '-'}</td>
                    ${hideExtraColumns ? '' : `
                        <td>${row.academic_year || '-'}</td>
                        <td>${row.semester_start || '-'}</td>
                        <td>${row.semester_end || '-'}</td>
                        <td>${row.generated_at || '-'}</td>
                    `}
                    <td class="text-center">
                        <a href="${row.certificate_url}" target="_blank" class="btn btn-sm btn-secondary" title="View Certificate">
                            <i class="ti ti-eye"></i>
                        </a>
                    </td>
                </tr>
            `;

            $('#certificate-history-body').prepend(html);
            $('#certificate-history-body tr').each(function(index) {
                $(this).find('td:first').text(index + 1);
            });
        }

        $(document).ready(function() {
            $('.select2').select2({
                placeholder: 'Select Student',
                allowClear: true,
                width: '100%'
            });

            if (typeof flatpickr !== 'undefined') {
                flatpickr('.semester-date-picker', {
                    dateFormat: 'd-m-Y',
                    allowInput: true,
                });
            }

            $('#generate_certificate').on('click', function() {
                const studentId = $('#student_id').val();

                if (!studentId) {
                    const message = 'Please select student.';

                    if (typeof toastr !== 'undefined') {
                        toastr.warning(message);
                    } else {
                        alert(message);
                    }

                    return;
                }

                const params = new URLSearchParams({
                    issue_date: $('#issue_date').val() || '',
                    academic_year: $('#academic_year').val() || '',
                    semester_start: $('#semester_start').val() || '',
                    semester_end: $('#semester_end').val() || ''
                });

                const baseUrl = "{{ route($certificateRoute, ['id' => '__ID__']) }}".replace('__ID__', studentId);
                const certificateUrl = baseUrl + '?' + params.toString();

                $.ajax({
                    url: "{{ route('issue-certificate.store-history') }}",
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        certificate_type: certificateType,
                        student_id: studentId,
                        issue_date: $('#issue_date').val() || '',
                        academic_year: $('#academic_year').val() || '',
                        semester_start: $('#semester_start').val() || '',
                        semester_end: $('#semester_end').val() || ''
                    },
                    success: function(response) {
                        if (response?.data) {
                            prependHistoryRow(response.data);
                        }

                        window.open(certificateUrl, '_blank');
                    },
                    error: function() {
                        window.open(certificateUrl, '_blank');
                    }
                });
            });
        });
    </script>
@endsection
