@extends('software.layout.app')

@section('content')
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="mb-0">Fee Details - {{ $student->first_name }} {{ $student->last_name }}</h4>
            <a href="{{ route('fees-collection-pending.index') }}" class="btn btn-secondary btn-sm">Back</a>
        </div>

        <div class="card-body">
            <p><strong>Course:</strong> {{ $courseReg->course->course_name ?? '-' }}</p>
            <p><strong>Student ID:</strong> {{ $student->id }}</p>

            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>SEMESTER</th>
                        <th>TOTAL FEES</th>
                        <th>PAID FEES</th>
                        <th>PENDING FEES</th>
                        <th>ACTION</th>
                        <th>CREATE FEE</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($feesData as $row)
                        @php
                            $semesterNumber = $row['sem_no'] ?? preg_replace('/\D/', '', $row['semester']);
                        @endphp
                        <tr>
                            <td>{{ $row['semester'] }}</td>
                            @if (!empty($row['is_skipped']))
                                <td colspan="5" class="text-center text-muted fw-bold">
                                    Join as semester {{ $courseReg->joining_semester }}
                                </td>
                            @else
                                <td>₹{{ number_format($row['total_fee'], 2) }}</td>
                                <td>₹{{ number_format($row['paid_fee'], 2) }}</td>
                                <td>₹{{ number_format($row['pending_fee'], 2) }}</td>
                                <td>
                                    @if ($row['paid_fee'] > 0)
                                        <a href="{{ url('software/students-fees-report/print') }}?student_id={{ $student->id }}&semester={{ $semesterNumber }}"
                                            class="btn btn-sm btn-primary" target="_blank">
                                            Print
                                        </a>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>
                                    @if ($row['pending_fee'] > 0)
                                        <a href="{{ route('fees-collection.create_by_id', [
                                            'student_id' => $student->id,
                                            'semester' => $semesterNumber,
                                        ]) }}"
                                            class="btn btn-sm btn-success" target="_blank">
                                            Create Fee
                                        </a>
                                    @else
                                        <span class="text-muted">Paid</span>
                                    @endif
                                </td>
                            @endif
                        </tr>
                    @endforeach
                </tbody>

            </table>
        </div>
    </div>
@endsection
