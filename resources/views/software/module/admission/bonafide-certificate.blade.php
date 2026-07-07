<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Bonafide Certificate</title>
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
            font-family: 'Times New Roman', Times, serif;
            color: #000;
            background: #fff;
            font-size: 15px;
            line-height: 1.6;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .certificate {
            width: 100%;
            max-width: 780px;
            margin: 10px auto;
            padding: 18px 24px 26px;
            border: 1px solid #000;
            min-height: 980px;
        }

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
            color: #1155cc;
            text-decoration: underline;
        }

        .contact-separator {
            color: #000;
            font-weight: 400;
        }

        .header-center {
            text-align: center;
            font-size: 13px;
            line-height: 1.45;
        }

        .header-center .college-name {
            font-size: 21px;
            font-weight: 700;
            margin: 3px 0;
        }

        .header-contact-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            width: 100%;
            font-size: 13px;
            margin-top: 4px;
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
        }

        .header-meta-box {
            position: absolute;
            top: 6px;
            right: 0;
            padding: 5px 14px 6px;
            font-size: 13px;
            line-height: 1.65;
            min-width: 190px;
        }

        .cert-title {
            text-align: center;
            font-size: 24px;
            font-weight: 700;
            margin: 40px 0 50px;
            letter-spacing: 1px;
            text-transform: uppercase;
        }

        .cert-body {
            font-size: 18px;
            line-height: 2;
            text-align: justify;
            margin: 30px 0 80px;
            text-indent: 2.5em;
        }

        .dynamic-value {
            display: inline;
            border-bottom: 1px solid #000;
            padding: 0 2px;
            line-height: inherit;
            vertical-align: baseline;
        }

        .footer-section {
            margin-top: 60px;
            font-size: 17px;
        }

        .footer-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }

        .footer-signature-space {
            min-height: 52px;
            margin-top: 12px;
        }

        .footer-signatory-line {
            display: flex;
            justify-content: space-between;
            align-items: baseline;
            gap: 40px;
            margin-top: 8px;
        }

        .principal-label {
            font-weight: 700;
            font-size: 17px;
            white-space: nowrap;
        }

        .college-stamp-name {
            font-size: 15px;
            font-weight: 600;
            line-height: 1.4;
            text-align: right;
        }

        @media print {
            .no-print {
                display: none;
            }

            .certificate {
                margin: 0;
                max-width: none;
            }
        }
    </style>
</head>

<body>
    @php
        $certificateData = $certificateData ?? [];
        $studentName = trim(
            ($admission->first_name ?? '') . ' ' .
            ($admission->last_name ?? '') . ' ' .
            ($admission->father_name ?? '')
        );
        $studentName = mb_strtoupper($studentName);
        $isFemale = strtolower(trim($admission->gender ?? '')) === 'female';
        $nameTitle = $isFemale ? 'Miss' : 'Mr.';
        $issueDate = $certificateData['issue_date'] ?? now()->format('j-m-Y');
        $courseName = trim($courseRegistration?->course?->course_name ?? '');

        $dojRaw = $courseRegistration?->created_at ?? $courseRegistration?->date ?? null;
        $dojDate = $dojRaw ? \Carbon\Carbon::parse($dojRaw)->startOfDay() : null;
        $todayDate = now()->startOfDay();

        $semester = 1;
        if ($dojDate) {
            $monthsFromDoj = $dojDate->diffInMonths($todayDate);
            $semester = intdiv($monthsFromDoj, 6) + 1;
        }

        $academicYear = $certificateData['academic_year'] ?? ($dojDate ? $dojDate->format('Y') : ($courseRegistration?->date
            ? \Carbon\Carbon::parse($courseRegistration->date)->format('Y')
            : now()->format('Y')));

        $periodFrom = $certificateData['period_from'] ?? '';
        $periodTo = $certificateData['period_to'] ?? '';
    @endphp

    <div class="certificate">
        @include('software.module.admission.partials.certificate-report-header')

        <div class="meta-row">
            <div class="header-meta-box">
                <div>College Code – 3109005</div>
                <div>Date - <span class="dynamic-value">{{ $issueDate }}</span></div>
            </div>
        </div>
        <br>
        <br>
       
      

        <div class="cert-title">BONAFIED CERTIFICATE</div>

        <div class="cert-body">
            This is to certify that {{ $nameTitle }}.
            <span class="dynamic-value">{{ $studentName ?: '____________________' }}</span>
            is the student of our institute. He / She is studying in
            <span class="dynamic-value">{{ $courseName ?: '________' }}</span>
            Semester:
            <span class="dynamic-value">{{ $semester ?: '________' }}</span>
            for the academic year
            <span class="dynamic-value">{{ $academicYear ?: '________' }}</span>
            regarding this
            <span class="dynamic-value">{{ $periodFrom ?: '________' }}</span>
            to
            <span class="dynamic-value">{{ $periodTo ?: '________' }}</span>
            Period and is our bonafied student.
        </div>

        <div class="footer-section">
            <div class="footer-row">
                <div>Thanking you,</div>
                <div>Your faithfully,</div>
            </div>
            <div class="footer-signature-space"></div>
            <div class="footer-signatory-line">
                <div class="principal-label">Principal</div>
                <div class="college-stamp-name">Shree Patel Vidhya Mandir Science College</div>
            </div>
        </div>
    </div>
</body>

</html>
