<!DOCTYPE html>
<html>

<head>
    <title>Fee Receipt</title>
    <style>
        /* Base layout */
        body {
            font-family: Arial, sans-serif;
            font-size: 14px;
            margin: 0;
            padding: 0;
            background: #fff;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .receipt-container {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 8px;
            width: 100%;
            padding: 8px;
            box-sizing: border-box;
        }

        .receipt {
            width: 49%;
            border: 2px solid #000;
            padding: 8px;
            box-sizing: border-box;
            position: relative;
            background: white;
            page-break-inside: avoid;
        }

        .watermark {
            position: absolute;
            top: 52%;
            left: 50%;
            transform: translate(-50%, -50%);
            opacity: 0.08;
            z-index: 0;
        }

        .watermark img {
            width: 230px;
            opacity: 0.1;
        }

        .receipt>*:not(.watermark) {
            position: relative;
            z-index: 2;
        }

        /* Tables */
        .info-table,
        .fees-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
            font-size: 12px;
        }

        .info-table td {
            padding: 4px 6px;
            border-bottom: 1px dotted #aaa;
        }

        .fees-table th,
        .fees-table td {
            border: 1px solid #000;
            padding: 2px;
            text-align: left;
            font-size: 15px
        }

        .fees-table th {
            text-align: center;
            background: #f0f0f0;
        }

        .total-row {
            font-weight: bold;
            background: #eaeaea !important;
        }

        .header {
            text-align: center;
            border-bottom: 2px solid #000;
            padding-bottom: 6px;
            margin-bottom: 10px;
        }

        .header img {
            width: 100px;
            height: auto;
        }

        .section-title {
            text-align: center;
            font-weight: bold;
            margin: 10px 0 8px;
            font-size: 13px;
            text-decoration: underline;
        }

        .signature {
            margin-top: 4px;
            font-size: 11px;
        }

        .signature-left {
            float: left;
        }

        .signature-right {
            float: right;
            text-align: right;
        }

        .footer {
            margin-top: 12px;
            font-size: 10px;
            text-align: center;
            border-top: 1px dashed #000;
            padding-top: 4px;
            font-style: italic;
        }

        .clear {
            clear: both;
        }

        .header table {
            width: 100%;
            border-collapse: collapse;
        }

        .header td {
            vertical-align: middle;
        }

        @media screen {
            body {
                margin: 0 !important;
                padding: 0 !important;
            }

            .receipt-container {
                display: flex !important;
                justify-content: space-between !important;
                align-items: flex-start !important;
            }
        }



        @media print {

            html,
            body {
                margin: 10px !important;
                padding: 0 !important;
            }


            .receipt-container {
                padding: 10px !important;
                margin: 0 auto !important;
                gap: 10px !important;
                width: calc(100% - 20px) !important;
            }

            .receipt {
                width: 49% !important;
                padding: 10px !important;
                margin: 0 !important;
                border: 2px solid #000 !important;
            }

        }


    </style>

</head>

<body onload="printAndRedirect();">


    <div class="receipt-container">
        @for ($i = 0; $i < 2; $i++)
            <div class="receipt">
                <!-- Watermark -->
                <div class="watermark">
                    <img src="{{ asset('uploads/logo/logo.png') }}" alt="Logo Watermark">
                </div>

                <!-- Header -->
                <div class="header">
                    <table>
                        <tr>
                            <td style="width: 80px; text-align: right;">
                                <img src="{{ asset('uploads/logo/logo.png') }}" style="width: 120px; height: 100px;"
                                    alt="College Logo">
                            </td>
                            <td style="text-align: center;">
                                Shree Patel Vidhyarthi Ashram Sanchalit<br>
                                <strong>Shree Patel Vidhya Mandir Science College</strong><br>
                                Veraval Road, Keshod - 362 220. Mo. 96874 51774<br>

                            </td>
                        </tr>
                    </table>
                </div>

                <!-- Student Information -->
                <table class="info-table">
               <tr>
    <td colspan="2" style="
        padding: 4px 6px;
        border-bottom: 1px dotted #aaa;
    ">
        <div style="
            display: flex;
            justify-content: space-between;
            width: 100%;
        ">
            <span>Receipt No.: <strong>{{ $receipt->receipt_no }}</strong></span>
            <span>Date: <strong>{{ \Carbon\Carbon::parse($feesData->date)->format('d-m-Y') }}</strong></span>
        </div>
    </td>
</tr>


                    <tr>
                        <td>Student Name (GR NO)</td>
                        <td class="no-wrap">
                            {{ trim(($feesData->admission->first_name ?? '') . ' ' . ($feesData->admission->last_name ?? '') . ' ' . ($feesData->admission->father_name ?? '')) }}
                            ({{ $courseRegistration->register_id ?? 'N/A' }})
                        </td>
                    </tr>

                    <tr>
                        <td>Class - Batch</td>
                        <td>
                            {{ $feesData->course->course_name ?? '' }} -
                            {{ $courseRegistration->batch->batch_name ?? '' }}
                        </td>
                    </tr>

                    <tr>
                        <td>Semester</td>
                        <td>{{ $feesData->year_semester }}</td>
                    </tr>
                </table>


                <!-- Fee Details Section Title -->
                <div class="section-title">
                    Fee Details ({{ $i == 0 ? 'Student Copy' : 'College Copy' }})
                </div>

                <!-- Fee Details Table -->
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
                                <td style="text-align: right;">{{ number_format($fee['amount']) }}/-</td>
                            </tr>
                        @endforeach
                        <tr class="total-row">
                            <td><strong>Total Fees</strong></td>
                            <td style="text-align: right;"><strong>{{ number_format($paidFee) }}/-</strong></td>
                        </tr>
                    </tbody>
                </table>


                <!-- Payment Information -->
                <table class="info-table">
                    <tr>
                        <td>Rs. In Words</td>
                        <td id="amountWords{{ $i }}"></td>

                    </tr>
                    <tr>
                        <td>Payment Mode</td>
                        <td>{{ $feesData->mode ?? '__________' }}</td>
                    </tr>
                </table>

                <!-- Signature Section -->
                <div class="signature">
                    <div class="signature-left" style="margin-top:35px;">
                        Received By: <strong>{{ $feesData->createdByUser->name ?? '__________' }}</strong>
                    </div>
                    <div class="signature-right" style="text-align:right;">
                        <div style="border-top:1px solid #000; width:150px; margin-left:auto; margin-top:44px;"></div>
                        <br> PVM BCA COLLEGE
                    </div>
                    <div class="clear"></div>
                </div>

                <!-- Footer Note -->
                <div class="footer">
                    Note: Submitted Fees For Above Course Is Not Refundable Even If You Cancel Your Admission.
                </div>
            </div>
        @endfor
    </div>

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
            if (amount.includes('.')) amount = amount.split('.')[0];

            var num = parseInt(amount);
            var result = '';

            function numToWords(n, label) {
                let str = '';
                if (n > 19) str += tens[Math.floor(n / 10)] + ' ' + words[n % 10];
                else str += words[n];
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
            if (remainder) result += (result !== '' ? 'and ' : '') + numToWords(remainder, '');

            return result.trim() + ' Rupees Only';
        }

        document.addEventListener("DOMContentLoaded", function() {
            const total = {{ $paidFee }}; // PHP dynamic amount

            for (let i = 0; i < 2; i++) {
                const element = document.getElementById("amountWords" + i);
                if (element) {
                    element.innerText = convertToWords(total);
                }
            }
        });
    </script>


    <script>
            function printAndRedirect() {
                window.print();


            }
    </script>
</body>

</html>
