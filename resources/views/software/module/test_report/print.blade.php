<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Test Report</title>
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
    <div class="no-print">
        <button onclick="window.print()">Print</button>
        <button onclick="window.close()">Close</button>
    </div>

    <h1>Test Report</h1>
    <div class="meta">
        @if (!empty($studentDetails))
            <div><strong>Student:</strong> {{ $studentDetails->student_name ?? '-' }}</div>
            <div><strong>Course:</strong> {{ $studentDetails->course_name ?? '-' }}</div>
        @endif
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
            @forelse ($tests as $test)
                <tr>
                    @foreach ($columnKeys as $columnKey)
                        <td>
                            @switch($columnKey)
                                @case('subject_name')
                                    {{ $test->subject_name ?? '-' }}
                                @break

                                @case('unit_name')
                                    {{ $test->unit_name ?? '-' }}
                                @break

                                @case('cr_date')
                                    {{ $test->cr_date ? \Carbon\Carbon::parse($test->cr_date)->format('d-m-Y') : '-' }}
                                @break

                                @case('mark')
                                    {{ $test->mark ?? '-' }}
                                @break

                                @case('student_mark')
                                    {{ $test->student_mark ?? 0 }}
                                @break

                                @default
                                    {{ data_get($test, $columnKey, '-') }}
                            @endswitch
                        </td>
                    @endforeach
                </tr>
            @empty
                <tr>
                    <td colspan="{{ max(1, count($columnLabels ?? [])) }}" style="text-align: center;">
                        No data available for the selected criteria.
                    </td>
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


