<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Class Master Report</title>
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

    <h1>Class Master Report</h1>
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
            @forelse ($classes as $classItem)
                <tr>
                    @foreach ($columnKeys as $columnKey)
                        <td>
                            @switch($columnKey)
                                @case('class')
                                    {{ $classItem->class ?? '-' }}
                                @break

                                @case('status')
                                    {{ $classItem->status ? ucfirst($classItem->status) : '-' }}
                                @break

                                @default
                                    {{ data_get($classItem, $columnKey, '-') }}
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


