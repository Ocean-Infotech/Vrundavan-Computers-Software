<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>English Medium Certificate</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 12mm 15mm;
        }

        * {
            box-sizing: border-box;
        }

        html,
        body {
            margin: 0;
            padding: 0;
            font-family: "Times New Roman", Times, serif;
            color: #000;
            background: #fff;
        }

        .certificate {
            width: 100%;
            max-width: 780px;
            margin: 10px auto;
            border: 1px solid #000;
            padding: 18px 24px 26px;
            min-height: 980px;
            break-inside: avoid;
            page-break-inside: avoid;
        }

        /* Header styles (same as bonafide header partial) */
        .report-header {
            margin-bottom: 8px;
        }

        .report-header-top {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .report-logo {
            flex: 0 0 95px;
        }

        .report-logo img {
            width: 95px;
            height: 95px;
            object-fit: contain;
            display: block;
        }

        .report-header-text {
            flex: 1;
            text-align: center;
            font-family: Arial, Helvetica, sans-serif;
            line-height: 1.35;
        }

        .report-header-text .affiliation-line {
            font-size: 12px;
            font-weight: 700;
        }

        .report-header-text .college-name {
            font-size: 24px;
            font-weight: 800;
            margin: 4px 0;
        }

        .report-header-text .managed-line,
        .report-header-text .address-line {
            font-size: 13px;
            font-weight: 700;
        }

        .report-contact-row {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 12px;
            margin-top: 8px;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 13px;
            font-weight: 700;
        }

        .report-contact-row a {
            color: #0645ad;
            text-decoration: underline;
        }

        .header-divider {
            border: none;
            border-top: 3px solid #000;
            margin: 8px 0 0;
        }

        .meta-row {
            position: relative;
            min-height: 46px;
            margin-bottom: 6px;
            font-size: 12px;
        }

        .meta-left {
            padding-top: 8px;
        }

        .header-meta-box {
            position: absolute;
            top: 6px;
            right: 0;
            padding: 5px 14px 6px;
            font-size: 13px;
            line-height: 1.65;
            min-width: 190px;
            text-align: left;
        }

        .body {
            padding-top: 20px;
            text-align: center;
        }

        .body .to-whom {
            font-size: 15px;
            font-weight: 700;
            margin: 34px 0 34px;
        }

        .body .content {
            font-size: 18px;
            line-height: 2;
            text-align: justify;
            margin: 30px auto 80px;
            max-width: 680px;
            text-indent: 2.5em;
        }

        .dynamic-value {
            display: inline;
            border-bottom: none;
            padding: 0 2px;
            line-height: inherit;
            vertical-align: baseline;
            font-weight: 700;
            text-transform: uppercase;
        }

        .dynamic-value.no-underline {
            border-bottom: none;
        }

        .footer {
            margin-top: 120px;
            display: flex;
            justify-content: space-between;
            font-size: 14px;
            padding: 0 30px;
        }

        .page-break {
            page-break-after: always;
            break-after: page;
        }

        @media print {
            html, body {
                height: auto;
            }

            .certificate {
                margin: 0;
            }
        }
    </style>
</head>
<body>
    @php
        $issueDate = $certificateData['issue_date'] ?? '';
        $issueDateDisplay = $issueDate;
        if ($issueDate) {
            try {
                // Revert to slash format (dd/mm/yyyy).
                $issueDateDisplay = \Carbon\Carbon::createFromFormat('j-m-Y', $issueDate)->format('d/m/Y');
            } catch (\Exception $e) {
                $issueDateDisplay = $issueDate;
            }
        }

        $fullName = trim(($admission->first_name ?? '') . ' ' . ($admission->last_name ?? '') . ' ' . ($admission->father_name ?? ''));
        $enrolment = $admission->enrolment_no ?? '';
        $courseName = $courseRegistration?->course?->course_name ?? '';
        $isFemale = strtolower(trim((string) ($admission->gender ?? ''))) === 'female';
        $nameTitle = $isFemale ? 'Ms.' : 'Mr.';

        // Batch year: infer start from registration date, assume 3-year program if unknown.
        $batchStartYear = null;
        $regDateRaw = $courseRegistration?->created_at ?? $courseRegistration?->date ?? null;
        if (!empty($regDateRaw)) {
            try {
                $batchStartYear = \Carbon\Carbon::parse($regDateRaw)->format('Y');
            } catch (\Exception $e) {
                $batchStartYear = null;
            }
        }
        $batchEndYear = $batchStartYear ? ((int) $batchStartYear + 3) : null;
        $batchYearText = $batchStartYear && $batchEndYear ? ($batchStartYear . '-' . $batchEndYear) : '';
        // Always use the report logo under /uploads/logo for consistent printing.
        $logoPath = asset('uploads/logo/report_logo.png');
    @endphp

    <div class="certificate">
        @include('software.module.admission.partials.certificate-report-header')

        <div class="meta-row">
            <div class="meta-left">
                Ref. FCSC/MOI Certificate/2022-23/4
            </div>
            <div class="header-meta-box">
                <div>College Code – 3108010</div>
                <div>Date – {{ $issueDateDisplay ?: '__________' }}</div>
            </div>
        </div>

        <div class="body">
            <div class="to-whom">: To Whom So Ever It May Concern :</div>

            <div class="content">
                This is to certify that {{ $nameTitle }}
                <span class="dynamic-value">{{ $fullName ?: '____________________' }}</span>
                (Enrollment Number:
                <span class="dynamic-value">{{ $enrolment ?: '____________' }}</span>)
                has studied in Bachelor of Computer Application (B.C.A.) programmed with English Medium
                @if ($batchYearText !== '')
                    the batch year <span class="dynamic-value no-underline" style="text-transform:none">{{ $batchYearText }}</span>.
                @else
                    .
                @endif
            </div>
        </div>

        <div class="footer">
            <div>
                Date: {{ $issueDateDisplay ?: '__________' }}<br><br>
                Place: Keshod
            </div>
            <div>
                (Hitesh Vadalia)
            </div>
        </div>
    </div>

    {{-- Keep as view page (no auto-print) --}}
</body>
</html>

