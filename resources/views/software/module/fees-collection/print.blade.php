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
        var words = new Array();
        words[0] = '';
        words[1] = 'One';
        words[2] = 'Two';
        words[3] = 'Three';
        words[4] = 'Four';
        words[5] = 'Five';
        words[6] = 'Six';
        words[7] = 'Seven';
        words[8] = 'Eight';
        words[9] = 'Nine';
        words[10] = 'Ten';
        words[11] = 'Eleven';
        words[12] = 'Twelve';
        words[13] = 'Thirteen';
        words[14] = 'Fourteen';
        words[15] = 'Fifteen';
        words[16] = 'Sixteen';
        words[17] = 'Seventeen';
        words[18] = 'Eighteen';
        words[19] = 'Nineteen';
        words[20] = 'Twenty';
        words[30] = 'Thirty';
        words[40] = 'Forty';
        words[50] = 'Fifty';
        words[60] = 'Sixty';
        words[70] = 'Seventy';
        words[80] = 'Eighty';
        words[90] = 'Ninety';

        amount = amount.toString();
        var atemp = amount.split(".");
        var number = atemp[0].split(",").join("");
        var n_length = number.length;
        var words_string = "";

        if (n_length <= 9) {
            var n_array = new Array(0, 0, 0, 0, 0, 0, 0, 0, 0);
            var received_n_array = number.split("");
            for (var i = 0; i < received_n_array.length; i++) {
                n_array[9 - n_length + i] = parseInt(received_n_array[i]);
            }
            for (var i = 0; i < 9; i++) {
                switch (i) {
                    case 0:
                        if (n_array[i] !== 0) {
                            words_string += words[n_array[i]] + " Crore ";
                        }
                        break;
                    case 1:
                        if (n_array[i] !== 0) {
                            words_string += words[n_array[i]] + " Lakh ";
                        }
                        break;
                    case 2:
                        if (n_array[i] !== 0) {
                            words_string += words[n_array[i]] + " Lakh ";
                        }
                        break;
                    case 3:
                        if (n_array[i] !== 0) {
                            words_string += words[n_array[i]] + " Thousand ";
                        }
                        break;
                    case 4:
                        if (n_array[i] !== 0) {
                            words_string += words[n_array[i]] + " Hundred ";
                        }
                        break;
                    case 5:
                    case 6:
                        if (n_array[i] !== 0) {
                            if (n_array[i] === 1) {
                                words_string += words[10 + n_array[i + 1]] + " ";
                                i++;
                            } else {
                                words_string += words[n_array[i] * 10] + " ";
                                if (n_array[i + 1] !== 0) {
                                    words_string += words[n_array[i + 1]] + " ";
                                }
                                i++;
                            }
                        }
                        break;
                }
            }
        }
        return words_string + "Rupees Only";
    }

    // Example usage
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
    window.onload = function() {
        window.print();
    };
</script>

</html>
