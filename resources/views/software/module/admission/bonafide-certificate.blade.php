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

        .no-print {
            text-align: center;
            padding: 10px;
            background: #f5f5f5;
            border-bottom: 1px solid #ddd;
        }

        .no-print button {
            padding: 8px 18px;
            margin: 0 6px;
            cursor: pointer;
            border: none;
            border-radius: 4px;
            font-size: 14px;
        }

        .btn-print {
            background: #004aad;
            color: #fff;
        }

        .btn-close {
            background: #666;
            color: #fff;
        }

        .certificate {
            width: 100%;
            max-width: 780px;
            margin: 10px auto;
            padding: 18px 24px 26px;
            border: 1px solid #000;
            min-height: 980px;
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

        .footer-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            margin-top: 60px;
            font-size: 17px;
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
        $studentName = trim(
            ($admission->first_name ?? '') . ' ' .
            ($admission->last_name ?? '') . ' ' .
            ($admission->father_name ?? '')
        );
        $studentName = mb_strtoupper($studentName);
        $isFemale = strtolower(trim($admission->gender ?? '')) === 'female';
        $nameTitle = $isFemale ? 'Miss' : 'Mr.';
        $issueDate = now()->format('d-m-Y');
        $courseName = trim($courseRegistration?->course?->course_name ?? '');

        $dojRaw = $courseRegistration?->created_at ?? $courseRegistration?->date ?? null;
        $dojDate = $dojRaw ? \Carbon\Carbon::parse($dojRaw)->startOfDay() : null;
        $todayDate = now()->startOfDay();

        $semester = 1;
        if ($dojDate) {
            $monthsFromDoj = $dojDate->diffInMonths($todayDate);
            $semester = intdiv($monthsFromDoj, 6) + 1;
        }

        $academicYear = $dojDate ? $dojDate->format('Y') : ($courseRegistration?->date
            ? \Carbon\Carbon::parse($courseRegistration->date)->format('Y')
            : now()->format('Y'));

        $periodFrom = $todayDate->format('d-m-Y');
        $periodTo = $todayDate->copy()->addMonths(3)->format('d-m-Y');
    @endphp

    <div class="no-print">
        <button class="btn-print" onclick="window.print()">Print</button>
        <button class="btn-close" onclick="window.close()">Close</button>
    </div>

    <div class="certificate">
        <div class="header-center">
            <div>REG. NO. A-663 JND DATED: 26-10-1964</div>
            <div>(Shree Patel Vidhyarthi Ashram – Keshod Sanchalit)</div>
            <div class="college-name">Shree PVM Computer Science College</div>
            <div>(Affiliated to Bhakta Kavi Narsinh Mehta University - Junagadh)</div>
            <div>Behind Maruti Showroom, Veraval Road, Keshod – 362220</div>
        </div>

        <div class="header-contact-row">
            <span>E-mail : pvmbcacollege@gmail.com</span>
            <span>Mo. 96874 51774</span>
        </div>

        <hr class="header-divider">

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

        <div class="footer-row">
            <div>Thanking you,</div>
            <div>Your faithfully.</div>
        </div>
    </div>
</body>

</html>
