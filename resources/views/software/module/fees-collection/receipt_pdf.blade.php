<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Fee Receipt</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 13px;
            color: #000;
            margin: 0;
            padding: 10px;
        }

        .receipt {
            width: 100%;
            border: 1px solid #000;
            padding: 12px 16px;
            box-sizing: border-box;
            position: relative;
            background: #fff;
        }

        .content-wrap {
            position: relative;
            z-index: 1;
        }

        .header {
            border-bottom: 1px solid #000;
            padding-bottom: 6px;
            margin-bottom: 8px;
        }

        .header-table {
            width: 100%;
            border-collapse: collapse;
        }

        .header-table td {
            vertical-align: middle;
        }

        .college-header {
            text-align: center;
            line-height: 1.4;
            font-size: 13px;
        }

        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 4px;
            font-size: 13px;
        }

        .info-table td {
            padding: 4px 0px;
            vertical-align: top;
        }

        .section-title {
            text-align: center;
            font-weight: bold;
            font-size: 13px;
            margin: 14px 0 8px 0;
            text-decoration: underline;
        }

        .fees-table {
            width: 100%;
            border-collapse: collapse;
            margin: 6px 0 10px 0;
            font-size: 13px;
        }

        .fees-table th,
        .fees-table td {
            border: 1px solid #000;
            padding: 6px 10px;
        }

        .signature {
            margin-top: 55px;
            margin-bottom: 15px;
            font-size: 13px;
        }

        .signature-table {
            width: 100%;
            border-collapse: collapse;
        }

        .signature-table td {
            vertical-align: bottom;
        }

        .footer {
            margin-top: 10px;
            font-size: 11px;
            font-style: italic;
            text-align: center;
            border-top: 1px dashed #000;
            padding-top: 5px;
        }
    </style>
</head>
<body>

    <div class="receipt">
        <div class="content-wrap">
            <!-- Header -->
            <div class="header">
                <table class="header-table">
                    <tr>
                        <td style="width: 90px; text-align: left;">
                            @if (!empty($logo_src))
                                <img src="{{ $logo_src }}" alt="College Logo" style="width: 85px; height: auto;">
                            @endif
                        </td>
                        <td class="college-header">
                            Shree Patel Vidhyarthi Ashram Sanchalit<br>
                            <strong style="font-size: 14px;">Shree Patel Vidhya Mandir Science College</strong><br>
                            <strong>Veraval Road, Keshod - 362 220. Mo. 96874 51774</strong>
                        </td>
                    </tr>
                </table>
            </div>

            <!-- Student & Receipt Details -->
            <table class="info-table">
                <tr>
                    <td style="width: 50%;">
                        Receipt No.: <strong>{{ $feesData->id }}</strong>
                    </td>
                    <td style="width: 50%; text-align: right;">
                        Date: <strong>{{ $feesData->date ? \Carbon\Carbon::parse($feesData->date)->format('d-m-Y') : date('d-m-Y') }}</strong>
                    </td>
                </tr>
                <tr>
                    <td style="width: 35%; font-weight: bold;">Student Name (GR NO)</td>
                    <td style="width: 65%; text-align: center;">
                        <strong>{{ strtoupper($feesData->student_name ?? ($feesData->admission ? $feesData->admission->first_name . ' ' . $feesData->admission->last_name . ' ' . $feesData->admission->father_name : '-')) }} ({{ $feesData->student_id ?? '-' }})</strong>
                    </td>
                </tr>
                <tr>
                    <td style="font-weight: bold;">Class - Batch</td>
                    <td style="text-align: center;">
                        {{ $feesData->course->course_name ?? '-' }}
                        -
                        {{ $feesData->registration->batch->batch_name ?? ($feesData->batch->batch_name ?? '-') }}
                    </td>
                </tr>
                <tr>
                    <td style="font-weight: bold;">Semester</td>
                    <td style="text-align: center;">{{ $feesData->year_semester ?? '-' }}</td>
                </tr>
            </table>

            <!-- Section Title -->
            <div class="section-title">
                Fee Details
            </div>

            <!-- Fees Table -->
            <table class="fees-table">
                <thead>
                    <tr>
                        <th style="text-align: center; width: 50%; font-weight: bold;">Fee Name</th>
                        <th style="text-align: center; width: 50%; font-weight: bold;">Amount (₹)</th>
                    </tr>
                </thead>
                <tbody>
                    @if (!empty($feesDetails) && is_array($feesDetails) && count($feesDetails) > 0)
                        @foreach ($feesDetails as $fee)
                            <tr>
                                <td>{{ $fee['name'] }}</td>
                                <td style="text-align: center;">{{ number_format($fee['amount'] ?? 0, 2) }}/-</td>
                            </tr>
                        @endforeach
                    @endif
                    <tr>
                        <td style="font-weight: bold;">Total Fee</td>
                        <td style="text-align: center; font-weight: bold;">{{ number_format($totalFee, 2) }}/-</td>
                    </tr>
                </tbody>
            </table>

            <!-- Additional Info -->
            <table class="info-table">
                <tr>
                    <td style="width: 30%; font-weight: bold;">Rs. In Words</td>
                    <td style="width: 70%; font-weight: normal;">{{ $amountInWords }}</td>
                </tr>
                <tr>
                    <td style="font-weight: bold;">Payment Mode</td>
                    <td style="font-weight: normal;">
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
                            {{ ucfirst($feesData->mode ?? 'Cash') }}
                        @endif
                    </td>
                </tr>
            </table>

            <!-- Signature -->
            <div class="signature">
                <table class="signature-table">
                    <tr>
                        <td style="width: 50%; text-align: left;">
                            Received By: ___________________
                        </td>
                        <td style="width: 50%; text-align: right;">
                            <strong>PVM BCA COLLEGE</strong>
                        </td>
                    </tr>
                </table>
            </div>

            <!-- Footer -->
            <div class="footer">
                Note: Submitted Fees For Above Course Is Not Refundable Even If You Cancel Your Admission.
            </div>
        </div>
    </div>

</body>
</html>
