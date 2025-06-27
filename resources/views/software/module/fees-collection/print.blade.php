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

<body onload="printAndRedirect();">

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
                    <td>{{ $feesData->course->course_name ?? '-' }}</td>
                </tr>
                <tr>
                    <td>Semester</td>
                    <td>{{ $feesData->year_semester ?? '-' }}</td>
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
                    <td>{{ $totalFee }}</td>
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
<script>
    function convertToWords(amount) {
        var words = ['', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine', 'Ten',
            'Eleven', 'Twelve', 'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen', 'Seventeen',
            'Eighteen', 'Nineteen'
        ];
        var tens = ['', '', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety'];

        if (isNaN(amount) || amount === '') return 'Invalid amount';
        if (parseInt(amount) === 0) return 'Zero Rupees Only';

        amount = amount.toString().replace(/[, ]/g, '');
        if (amount.includes('.')) {
            amount = amount.split('.')[0]; // Only take integer part
        }

        var num = parseInt(amount);
        var result = '';

        function numToWords(n, label) {
            let str = '';
            if (n > 19) {
                str += tens[Math.floor(n / 10)] + ' ' + words[n % 10];
            } else {
                str += words[n];
            }
            return str ? str + ' ' + label + ' ' : '';
        }

        var crore = Math.floor(num / 10000000);
        num %= 10000000;
        var lakh = Math.floor(num / 100000);
        num %= 100000;
        var thousand = Math.floor(num / 1000);
        num %= 1000;
        var hundred = Math.floor(num / 100);
        var remainder = num % 100;

        if (crore) result += numToWords(crore, 'Crore');
        if (lakh) result += numToWords(lakh, 'Lakh');
        if (thousand) result += numToWords(thousand, 'Thousand');
        if (hundred) result += words[hundred] + ' Hundred ';
        if (remainder) {
            result += (result !== '' ? 'and ' : '') + numToWords(remainder, '');
        }

        return result.trim() + ' Rupees Only';
    }

    document.addEventListener("DOMContentLoaded", function() {
        const rows = document.querySelectorAll("table.info-table td");
        rows.forEach(row => {
            if (row.textContent.includes("Amount In Words") || row.textContent.includes(
                "Rs. In Words")) {
                const totalCell = document.querySelector(
                    "table.fees-table tbody tr:last-child th:last-child");
                if (totalCell) {
                    const num = parseFloat(totalCell.textContent.replace(/[^\d.]/g, ''));
                    row.nextElementSibling.innerText = convertToWords(num);
                }
            }
        });
    });
</script>

<script>
    function printAndRedirect() {
        window.print();

        window.onafterprint = function() {
            setTimeout(function() {
                window.location.href = "{{ route($modules['route'] . '.create') }}";
            }, 1);
        };
    }
</script>

</html>
