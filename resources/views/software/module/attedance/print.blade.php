<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Attendance Report</title>
    <style>
        body {
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            margin: 20px;
            color: #111;
        }

        h1 {
            text-align: center;
            margin-bottom: 5px;
        }

        .meta {
            text-align: center;
            font-size: 13px;
            margin-bottom: 15px;
        }

        .no-print {
            text-align: right;
            margin-bottom: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            font-size: 13px;
        }

        th,
        td {
            border: 1px solid #555;
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #f2f4f7;
        }

        @media print {
            .no-print {
                display: none;
            }
        }
    </style>
</head>

<body>
    @php
        $columnCount = max(1, count($columnLabels ?? []));
    @endphp

    <div class="no-print">
        <button onclick="window.print()">Print</button>
        <button onclick="window.close()">Close</button>
    </div>

    <h1>Attendance Report</h1>
    <div class="meta">
        {{-- <div><strong>Data Scope:</strong> {{ $scope === 'all' ? 'All records' : 'Filtered records' }}</div>
        @if (!empty($searchTerm))
            <div><strong>Search Filter:</strong> "{{ $searchTerm }}"</div>
        @endif --}}
        <div><strong>Generated On:</strong> {{ optional($generatedAt)->format('d-m-Y h:i A') }}</div>
    </div>

    <table>
        <thead>
            <tr>
                @foreach ($columnLabels as $label)
                    <th>{{ $label }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @forelse ($attendances as $attendance)
                <tr>
                    @foreach ($columnKeys as $columnKey)
                        <td>
                            @switch($columnKey)
                                @case('biometric_id')
                                    @php
                                        $biometricId = $attendance->biometric_id;
                                        if (!$biometricId && $attendance->admission_id) {
                                            $biometricId = $attendance->admission?->biometric_id ?? $attendance->admission_id;
                                        }
                                    @endphp
                                    {{ $biometricId ?? '-' }}
                                @break

                                @case('student_name')
                                    @php
                                        $displayName = $attendance->UserName;
                                        if (empty($displayName)) {
                                            $admission = $attendance->admission;
                                            if (!$admission && $attendance->biometric_id) {
                                                $admission = \App\Models\Admission::where('biometric_id', $attendance->biometric_id)->first();
                                            }
                                            $last = $admission?->first_name ?? '';
                                            $first = $admission?->last_name ?? '';
                                            $father = $admission?->father_name ?? '';
                                            $fullName = trim($last . ' ' . $first . ' ' . $father);
                                            $displayName = $fullName !== '' ? $fullName : '-';
                                        }
                                    @endphp
                                    {{ $displayName }}
                                @break

                                @case('course_name')
                                    {{ $attendance->course->course_name ?? '-' }}
                                @break

                                @case('batch_name')
                                    {{ $attendance->batch->batch_name ?? '-' }}
                                @break

                                @case('date')
                                    {{ $attendance->date ? \Carbon\Carbon::parse($attendance->date)->format('d-m-Y') : '-' }}
                                @break

                                @case('in_time')
                                    {{ $attendance->first_punch ? \Carbon\Carbon::parse($attendance->first_punch)->format('h:i A') : '-' }}
                                @break

                                @case('out_time')
                                    {{ ($attendance->last_punch && $attendance->last_punch !== $attendance->first_punch) ? \Carbon\Carbon::parse($attendance->last_punch)->format('h:i A') : '-' }}
                                @break

                                @case('leave_status')
                                    @php
                                        $inTime = $attendance->in_time ?? '';
                                        $outTime = $attendance->out_time ?? '';
                                        $leave = $attendance->leave ?? '';
                                        $statusText = ($inTime === '' && $outTime === '' && $leave !== '') ? 'Leave' : '-';
                                    @endphp
                                    {{ $statusText }}
                                @break

                                @default
                                    {{ data_get($attendance, $columnKey, '-') }}
                            @endswitch
                        </td>
                    @endforeach
                </tr>
            @empty
                <tr>
                    <td colspan="{{ $columnCount }}" style="text-align: center;">No data available for the selected
                        criteria.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <script>
        window.onload = function() {
            window.print();
        };
        window.onafterprint = function() {
            window.close();
        };
    </script>
</body>

</html>


