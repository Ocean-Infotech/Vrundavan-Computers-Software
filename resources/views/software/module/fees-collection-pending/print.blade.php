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

    <table>
        <thead>
            <tr>
                <th>Student ID</th>
                <th>Student Name</th>
                <th>Contact No</th>
                <th>Course</th>
                <th>DOJ</th>
                <th>Total Fee</th>
                <th>Paid Fee</th>
                <th>Pending Fee</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($data as $item)
                @php
                    $reg_id = $item->register_id;
                    $name = trim(
                        ($item->admission->first_name ?? '') .
                            ' ' .
                            ($item->admission->father_name ?? '') .
                            ' ' .
                            ($item->admission->last_name ?? ''),
                    );
                    $contact = $item->admission->mobile_no ?? '-';

                    $courses = $allRegistrations[$reg_id] ?? collect();
                    $course_names = $courses->map(fn($c) => $c->course->course_name ?? '')->implode(', ');
                    $doj = $courses->map(fn($c) => \Carbon\Carbon::parse($c->date)->format('d-m-Y'))->implode(', ');

                    $total_fee = $courses->sum('fee');
                    $paid_fee = $feesCollection[$reg_id] ?? 0;
                    $pending_fee = $total_fee - $paid_fee;
                @endphp
                <tr>
                    <td>{{ $reg_id }}</td>
                    <td>{{ $name }}</td>
                    <td>{{ $contact }}</td>
                    <td>{{ $course_names }}</td>
                    <td>{{ $doj }}</td>
                    <td>{{ number_format($total_fee, 2) }}</td>
                    <td>{{ number_format($paid_fee, 2) }}</td>
                    <td>{{ number_format($pending_fee, 2) }}</td>
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
