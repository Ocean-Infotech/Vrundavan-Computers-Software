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
            width: 100%;
            /* Full width instead of 48% */
            display: block;
            /* Block instead of inline-block */
            margin-bottom: 20px;
            /* Space between two copies */
            padding: 10px;
            border: 1px solid #000;
            box-sizing: border-box;
            position: relative;
            overflow: hidden;
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
            margin-top: 72px;
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

        .header {
            font-size: 13px;
            font-weight: bold;
            line-height: 1.4;
            border-bottom: 1px solid #000;
            padding-bottom: 5px;
        }

        .header img {
            display: block;
            margin: 0 auto;
        }



        .watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            opacity: 0.08;
            /* transparency level */
            z-index: 0;
            /* keep behind text */
            pointer-events: none;
            /* ignore clicks */
        }

        .watermark img {
            width: 300px;
            /* adjust as needed */
            height: auto;
        }

        .receipt>*:not(.watermark) {
            position: relative;
            z-index: 1;
            /* text always above logo */
        }
    </style>
</head>

<body onload="printAndRedirect();">

    @for ($i = 0; $i < 2; $i++)
        <div class="receipt">
            <div class="watermark">
                <img src="{{ asset('uploads/logo/logo.png') }}" alt="Logo Watermark">
            </div>
            <div class="header">
                <table style="width: 100%;">
                    <tr>
                        <td style="width: 80px; text-align: left;">
                            <img src="{{ asset('uploads/logo/logo.png') }}" alt="College Logo"
                                style="width:100px; height:auto;">
                        </td>
                        <td style="text-align: center;">
                            Shree Patel Vidhyarthi Ashram Sanchalit<br>
                            <strong>Shree Patel Vidhya Mandir Science College</strong><br>
                            Veraval Road, Keshod - 362 220. Mo. 96874 51774<br>

                        </td>
                    </tr>
                </table>
            </div>


            <table class="info-table">
                <tr>
                    <td style="width: 50%;">
                        Receipt No.: <strong>{{ $feesData->id }}</strong>
                    </td>

                    <td style="width: 50%; text-align: right;">
                        Date: <strong>{{ \Carbon\Carbon::parse($feesData->date)->format('d-m-Y') }}</strong>
                    </td>
                </tr>

                <tr>
                    <td>Student Name (GR NO)</td>
                    <td>
                        {{ $feesData->student_name ?? '-' }}
                        ({{ $feesData->student_id ?? '-' }})
                    </td>
                </tr>

                <tr>
                    <td>Class - Batch</td>
                    <td>
                        {{ $feesData->course->course_name ?? '-' }}
                        -
                        {{ $feesData->registration->batch->batch_name ?? '-' }}
                    </td>
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
                        <th style="text-align: center;">Fee Name</th>
                        <th style="text-align: center;">Amount (₹)</th>
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
                    <td>
                        @php
                            $mode = strtolower($feesData->mode ?? '');
                        @endphp

                        @if ($mode === 'online' && !empty($feesData->upi_id))
                            Online ({{ $feesData->upi_id }})
                        @elseif($mode === 'cheque' && !empty($feesData->cheque_no))
                            Cheque ({{ $feesData->cheque_no }})
                        @elseif($mode === 'return' && !empty($feesData->return_reason))
                            Return ({{ $feesData->return_reason }})
                        @else
                            {{ $feesData->mode ?? '__________' }}
                        @endif
                    </td>
                </tr>

            </table>

            <div class="signature">
                
                <div style="float: left;">Received By: <strong>{{ $feesData->createdByUser->name ?? '__________' }}</strong>
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
    }
</script>

</html>
