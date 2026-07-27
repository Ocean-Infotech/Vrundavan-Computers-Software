<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Letter of Recommendation</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 12mm 15mm;
        }

        * { box-sizing: border-box; }

        html,
        body {
            margin: 0;
            padding: 0;
            font-family: "Times New Roman", Times, serif;
            color: #000;
            background: #fff;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
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

        /* Header styles (same as other certificates) */
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

        .contact-separator {
            color: #000;
            font-weight: 400;
        }

        .header-divider {
            border: none;
            border-top: 3px solid #000;
            margin: 8px 0 0;
        }

        .meta-row {
            position: relative;
            min-height: 46px;
            margin-bottom: 10px;
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

        .title {
            text-align: center;
            font-size: 18px;
            font-weight: 800;
            margin: 26px 0 18px;
            text-decoration: underline;
        }

        .body {
            font-size: 16px;
            line-height: 1.6;
            text-align: justify;
            margin: 10px auto 0;
            max-width: 700px;
        }

        .body p {
            margin: 0 0 10px;
        }

        .student-name {
            font-weight: 800;
            text-transform: uppercase;
        }

        .footer {
            margin-top: 46px;
            font-size: 15px;
            padding: 0 30px;
            text-align: right;
        }

        .footer .sign-name {
            margin-top: 70px;
            font-weight: 700;
        }

        .footer .designation {
            margin-top: 2px;
            font-weight: 700;
        }

        @media print {
            .certificate { margin: 0; }
        }
    </style>
</head>
<body>
@php
    $certificateData = $certificateData ?? [];

    $issueDate = $certificateData['issue_date'] ?? '';
    $issueDateDisplay = $issueDate;
    if ($issueDate) {
        try {
            $issueDateDisplay = \Carbon\Carbon::createFromFormat('j-m-Y', $issueDate)->format('d-m-Y');
        } catch (\Exception $e) {
            $issueDateDisplay = $issueDate;
        }
    }

    $fullName = trim(($admission->first_name ?? '') . ' ' . ($admission->last_name ?? '') . ' ' . ($admission->father_name ?? ''));
    $isFemale = strtolower(trim((string) ($admission->gender ?? ''))) === 'female';
    $titleForShortName = $isFemale ? 'Ms.' : 'Mr.';
    $lastNameOnly = trim((string) ($admission->last_name ?? ''));
    $shortName = $lastNameOnly !== '' ? (trim($titleForShortName . ' ' . mb_strtoupper($lastNameOnly))) : '';
    $pronounSubject = $isFemale ? 'she' : 'he';
    $pronounSubjectCap = $isFemale ? 'She' : 'He';
    $pronounPossessive = $isFemale ? 'her' : 'his';
    $pronounObject = $isFemale ? 'her' : 'him';
    $courseName = trim($courseRegistration?->course?->course_name ?? '');

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
@endphp

<div class="certificate">
    @include('software.module.admission.partials.certificate-report-header')

    <div class="meta-row">
        <div class="meta-left">
            Ref. FCSC/2022-23/1
        </div>
        <div class="header-meta-box">
            <div>College Code – 3108010</div>
            <div>Date – {{ $issueDateDisplay ?: '__________' }}</div>
        </div>
    </div>

    <div class="title">: Letter of Recommendation :</div>

    <div class="body">
        <p>
            I am happy that I have been asked to give this testimonial for my student
            <span class="student-name">{{ $fullName ?: '____________________' }}</span>
            of {{ $courseName ?: 'Bachelor of Computer Application (B.C.A)' }} Programmed studied
            @if ($batchYearText !== '')
                in the batch year {{ $batchYearText }}.
            @else
                .
            @endif
        </p>

        <p>
            In this context, I wish to place on record the good academic Performance of
            <span class="student-name">{{ $shortName ?: ($fullName ?: '____________________') }}</span>
            during {{ $pronounPossessive }} three years of study.
        </p>

        <p>
            As per {{ $pronounPossessive }} Performance records, {{ $pronounSubjectCap }} is inquisitive and yearns to gain an in depth Subject
            Knowledge. With {{ $pronounPossessive }} determination and hard work I have no doubt that {{ $pronounSubjectCap }} will succeed in all
            {{ $pronounPossessive }} endeavors. {{ $pronounSubjectCap }} is keen to understand all the concept as clearly as possible.
        </p>

        <p>
            {{ $pronounSubjectCap }} has the right attitude and aptitude to scale further heights and I am sure {{ $pronounSubject }} would do
            justice in meeting the high standards in your university.
        </p>

        <p>
            Therefore, I strongly recommend {{ $pronounObject }} for admission into your prestigious university that would
            enable {{ $pronounObject }} to study and contribute towards the progress of application Oriented research.
        </p>
    </div>

    <div class="footer">
        <div>Sincerely</div>
        <div class="sign-name">({{ $certificateData['recommender_name'] ?? 'Hitesh Vadalia' }})</div>
        <div class="designation">{{ $certificateData['recommender_designation'] ?? 'I/C. Principal' }}</div>
        <div class="designation">Shree Patel Vidhya Mandir <br> Science College - Keshod</div>
    </div>
</div>
</body>
</html>

