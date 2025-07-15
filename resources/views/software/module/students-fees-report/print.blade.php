<!DOCTYPE html>
<html>

<head>
    <title>Students Fees Report</title>
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
                <th>Semester</th>
                <th>Total Fees</th>
                <th>Paid Fees</th>
                <th>Pending Fees</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($data as $item)
                <tr>
                    <td>{{ $item['year_semester'] }}</td>
                    <td>{{ number_format($item['total_fee'], 2) }}</td>
                    <td>{{ number_format($item['paid_fee'], 2) }}</td>
                    <td>{{ number_format($item['pending_fee'], 2) }}</td>
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
