<!DOCTYPE html>
<html>

<head>
    <title>Pending Fees Report</title>
    <style>
        body {
            font-family: sans-serif;
            font-size: 14px;
        }

        table {
            border-collapse: collapse;
            width: 100%;
            margin-top: 20px;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 8px;
            text-align: center;
        }

        th {
            background-color: #4F81BD;
            color: white;
        }

        h2 {
            text-align: center;
        }

        .no-print {
            margin: 10px 0;
            text-align: right;
        }

        @media print {
            .no-print {
                display: none;
            }
        }
    </style>
</head>

<body>

    <div class="no-print">
        <button onclick="window.print()">🖨️ Print Report</button>
    </div>

    <h2>Pending Fees Report</h2>
    <div class="meta" style="text-align:center; font-size:13px; margin-bottom:10px;">
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
            @foreach ($data as $item)
                @php
                    $reg_id = $item->register_id;
                    $courses = $allRegistrations[$reg_id] ?? collect();
                    $course_names = $courses->map(fn($c) => $c->course->course_name ?? '')->implode(', ');
                    $total_fee = $courses->sum(fn($reg) => \App\Models\CourceRegistration::calculateTotalFee($reg));
                    $paid_fee = $feesCollection->where('student_id', $reg_id)->sum('total_paid');
                    $pending_fee = $total_fee - $paid_fee;
                    $firstCourse = $courses->first();
                    $className = $firstCourse && $firstCourse->class ? $firstCourse->class->class : '-';
                    $semesterVal = optional($feesCollection->firstWhere('student_id', $reg_id))->year_semester ?? '-';
                    $studentName = trim(
                        ($item->admission->last_name ?? '') . ' ' .
                        ($item->admission->first_name ?? '') . ' ' .
                        ($item->admission->father_name ?? '')
                    );
                    $contactNo = $item->admission->mobile_no ?? '-';
                @endphp
                <tr>
                    @foreach ($columnKeys as $columnKey)
                        <td>
                            @switch($columnKey)
                                @case('register_id')
                                    {{ $reg_id }}
                                @break

                                @case('student_name')
                                    {{ $studentName }}
                                @break

                                @case('contact_no')
                                    {{ $contactNo }}
                                @break

                                @case('course_name')
                                    {{ $course_names ?: '-' }}
                                @break

                                @case('class_name')
                                    {{ $className }}
                                @break

                                @case('semester')
                                    {{ $semesterVal }}
                                @break

                                @case('total_fee')
                                    {{ number_format($total_fee, 2) }}
                                @break

                                @case('paid_fee')
                                    {{ number_format($paid_fee, 2) }}
                                @break

                                @case('pending_fee')
                                    {{ number_format($pending_fee, 2) }}
                                @break

                                @default
                                    -
                            @endswitch
                        </td>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
<script>
    window.onload = function() {
        window.print();
    };
</script>

</html>
