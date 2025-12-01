<!DOCTYPE html>
<html>

<head>
    <title>Fee Receipt</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 0;
            padding: 10px;
            background: white;
            -webkit-print-color-adjust: exact;
            color-adjust: exact;
        }

        /* Print specific styles */
        @media print {
            body {
                padding: 5px;
                margin: 0;
            }

            .no-print {
                display: none !important;
            }

            .receipt-container {
                display: flex !important;
                justify-content: space-between !important;
                width: 100% !important;
                gap: 5px !important;
            }

            .receipt {
                width: 48% !important;
                margin: 0 !important;
                border: 2px solid #000 !important;
                break-inside: avoid !important;
                page-break-inside: avoid !important;
            }
        }

        .receipt-container {
            display: flex;
            justify-content: center;
            align-items: flex-start;
            gap: 10px;
            margin: 0 auto;
            max-width: 100%;
        }

        .receipt {
            width: 48%;
            border: 2px solid #000;
            padding: 15px;
            box-sizing: border-box;
            position: relative;
            background: white;
            page-break-inside: avoid;
        }

        .watermark {
            position: absolute;
            top: 56%;
            left: 50%;
            transform: translate(-50%, -50%);
            opacity: 0.08;
            z-index: 0;
            pointer-events: none;
        }

        .watermark img {
            width: 250px;
            height: auto;
        }

        .receipt>*:not(.watermark) {
            position: relative;
            z-index: 1;
        }

        .header {
            text-align: center;
            font-size: 13px;
            font-weight: bold;
            line-height: 1.3;
            border-bottom: 2px solid #000;
            padding-bottom: 8px;
            margin-bottom: 10px;
        }

        .header table {
            width: 100%;
        }

        .header img {
            width: 70px;
            height: auto;
        }

        .info-table,
        .fees-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
            font-size: 11px;
        }

        .info-table td {
            padding: 4px 6px;
            vertical-align: top;
            border-bottom: 1px dotted #ccc;
        }

        .info-table td:first-child {
            width: 35%;
            font-weight: bold;
        }

        .fees-table th,
        .fees-table td {
            border: 1px solid #000;
            padding: 5px;
            text-align: left;
        }

        .fees-table th {
            background-color: #f0f0f0;
            font-weight: bold;
        }

        .section-title {
            text-align: center;
            font-weight: bold;
            margin: 12px 0 8px;
            font-size: 12px;
            text-decoration: underline;
        }

        .signature {
            margin-top: 15px;
            font-size: 11px;
        }

        .signature-left {
            float: left;
        }

        .signature-right {
            float: right;
        }

        .clear {
            clear: both;
        }

        .footer {
            margin-top: 12px;
            font-size: 10px;
            font-style: italic;
            text-align: center;
            border-top: 1px dashed #000;
            padding-top: 5px;
        }

        /* Total row highlighting */
        .total-row {
            font-weight: bold;
            background-color: #f0f0f0 !important;
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
                            <td style="width: 80px; text-align: left;">
                                <img src="{{ asset('uploads/logo/logo.png') }}" style="width: 120px; height: 100px;"
                                    alt="College Logo">
                            </td>
                            <td style="text-align: center;">
                                Shree Patel Vidhyarthi Ashram Sanchalit<br>
                                <strong>Shree Patel Vidhya Mandir Computer Science College</strong><br>
                                Veraval Road, Keshod - 362 220. Mo. 96874 51774<br>
                                Date: {{ \Carbon\Carbon::parse($feesData->date)->format('d-m-Y') }}
                            </td>
                        </tr>
                    </table>
                </div>

                <!-- Student Information -->
                <table class="info-table">
                    <tr>
                        <td>Receipt No.</td>
                        <td>{{ $receipt->receipt_no }}</td>
                    </tr>
                    {{-- <tr>
                        <td>Student Name</td>
                        <td>
                            {{ trim(
                                ($feesData->admission->first_name ?? '') .
                                    ' ' .
                                    ($feesData->admission->father_name ?? '') .
                                    ' ' .
                                    ($feesData->admission->last_name ?? ''),
                            ) ?:
                                '____________' }}
                        </td>
                    </tr> --}}
                    <tr>
                        <td>Student Name</td>
                        <td>
                            {{ trim(
                                ($feesData->admission->first_name ?? '') .
                                    ' ' .
                                    ($feesData->admission->father_name ?? '') .
                                    ' ' .
                                    ($feesData->admission->last_name ?? ''),
                            ) ?:
                                '____________' }}

                            ({{ $courseRegistration->register_id ?? 'N/A' }})
                        </td>
                    </tr>

                    <tr>
                        <td>Class</td>
                        <td>
                            {{ $feesData->course->course_name ?? '____________' }}
                            -
                            {{ $courseRegistration->batch->batch_name ?? '____________' }}
                        </td>
                    </tr>



                    <tr>
                        <td>Semester</td>
                        <td>{{ $feesData->year_semester ?? '____________' }}</td>
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
                            <th>Fee Name</th>
                            <th>Amount (₹)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($feesDetails as $fee)
                            <tr>
                                <td>{{ $fee['name'] }}</td>
                                <td>{{ number_format($fee['amount']) }}/-</td>
                            </tr>
                        @endforeach
                        <tr class="total-row">
                            <td><strong>Total Fees</strong></td>
                            <td><strong>{{ number_format($paidFee) }}/-</strong></td>
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
                    <div class="signature-left" style="margin-top:15px;">
                        Received By: <strong>{{ $feesData->received_by ?? '__________' }}</strong>
                    </div>
                    <div class="signature-right" style="text-align:right;">
                        <div style="border-top:1px solid #000; width:150px; margin-left:auto; margin-top:25px;"></div>
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

        // Set amount in words for both receipts
        document.addEventListener("DOMContentLoaded", function() {
            const total = {{ $paidFee }}; // Dynamic amount from PHP
            for (let i = 0; i < 2; i++) {
                const element = document.getElementById("amountWords" + i);
                if (element) {
                    element.innerText = convertToWords(total);
                }
            }
        });

        function printAndRedirect() {
            // Small delay to ensure page is fully loaded
            setTimeout(function() {
                window.print();

                // Handle after print
                window.onafterprint = function() {
                    setTimeout(function() {
                        // Redirect back to the index page
                        // window.location.href = "{{ route($modules['route'] . '.index') }}";
                    }, 100);
                };

                // Fallback for browsers that don't support onafterprint
                setTimeout(function() {
                    if (!window.matchMedia('print').matches) {
                        // window.location.href = "{{ route($modules['route'] . '.index') }}";
                    }
                }, 2000);
            }, 500);
        }
    </script>

</body>

</html>
