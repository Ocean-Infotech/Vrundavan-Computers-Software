<!DOCTYPE html>
<html>

<head>
    <title>Fee Receipt</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            margin: 0;
            padding: 10px;
        }

        .receipt {
            width: 48%;
            display: inline-block;
            vertical-align: top;
            padding: 10px;
            border: 1px solid #000;
            box-sizing: border-box;
            margin-bottom: 10px;
        }

        .header {
            text-align: center;
            font-size: 13px;
            font-weight: bold;
            line-height: 1.4;
            border-bottom: 1px solid #000;
            padding-bottom: 5px;
        }

        .info-table,
        .fees-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            font-size: 12px;
        }

        .info-table td {
            padding: 4px 6px;
            vertical-align: top;
        }

        .info-table td:first-child {
            width: 35%;
            font-weight: bold;
        }

        .fees-table th,
        .fees-table td {
            border: 1px solid #000;
            padding: 6px;
            text-align: left;
        }

        .section-title {
            text-align: center;
            font-weight: bold;
            margin: 10px 0 5px;
            font-size: 13px;
            text-decoration: underline;
        }

        .signature {
            margin-top: 20px;
            font-size: 12px;
        }

        .footer {
            margin-top: 15px;
            font-size: 11px;
            font-style: italic;
            text-align: center;
            border-top: 1px dashed #000;
            padding-top: 5px;
        }
    </style>
</head>

<body>
    @for ($i = 0; $i < 2; $i++)
        <div class="receipt">
            <div class="header">
                Shree Patel Vidhyarthi Ashram Sanchalit<br>
                <strong>Shree Patel Vidhya Mandir Computer Science College</strong><br>
                Veraval Road, Keshod - 362 220<br>
                Mo. 96874 51774<br>
                Date: {{ \Carbon\Carbon::parse($feesData->date)->format('d-m-Y') }}
            </div>

            <table class="info-table">
                <tr>
                    <td>Receipt No.</td>
                    <td>{{ $feesData->id }}</td>
                </tr>
                <tr>
                    <td>Student Name</td>
                    <td>{{ $feesData->student_name }}</td>
                </tr>
                <tr>
                    <td>Class</td>
                    <td>{{ $feesData->course_id }}</td>
                </tr>
                <tr>
                    <td>Semester</td>
                    <td>{{ $feesData->semester }}</td>
                </tr>
            </table>

            <div class="section-title">
                Fee Details ({{ $i == 0 ? 'Student Copy' : 'College Copy' }})
            </div>

            <table class="fees-table">
                <thead>
                    <tr>
                        <th>Fee Name</th>
                        <th>Amount (₹)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($feesDetails as $fee)
                        <tr>
                            <td>{{ $fee['name'] }}</td>
                            <td>{{ number_format($fee['amount'], 2) }}/-</td>
                        </tr>
                    @endforeach
                    <tr>
                        <th>Total Fee</th>
                        <th>{{ number_format($totalFee, 2) }}/-</th>
                    </tr>
                </tbody>
            </table>

            <table class="info-table">
                <tr>
                    <td>Rs. In Words</td>
                    <td>{{ $totalInWords }}</td>
                </tr>
                <tr>
                    <td>Payment Mode</td>
                    <td>{{ $feesData->mode ?? '__________' }}</td>
                </tr>
            </table>

            <div class="signature">
                <div style="float: left;">Received By: <strong>{{ $feesData->received_by ?? '__________' }}</strong>
                </div>
                <div style="float: right;"><strong>PVM BCA COLLEGE</strong></div>
                <div style="clear: both;"></div>
            </div>

            <div class="footer">
                Note: Submitted Fees For Above Course Is Not Refundable Even If You Cancel Your Admission.
            </div>
        </div>
    @endfor
</body>

</html>
