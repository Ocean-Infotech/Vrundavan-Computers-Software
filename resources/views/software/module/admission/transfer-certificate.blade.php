<!DOCTYPE html>
<html lang="gu">

<head>
    <meta charset="UTF-8">
    <title>Transfer Certificate</title>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Gujarati:wght@400;600;700&display=swap" rel="stylesheet">
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
            font-family: 'Noto Sans Gujarati', 'Shruti', 'Nirmala UI', sans-serif;
            color: #000;
            background: #fff;
            font-size: 15px;
            line-height: 1.55;
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

        .header-contact-row span:first-child {
            text-align: left;
        }

        .header-contact-row span:last-child {
            text-align: right;
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

        .header-meta-box .date-row {
            display: flex;
            align-items: flex-end;
            gap: 4px;
            white-space: nowrap;
        }

        .cert-title {
            text-align: center;
            font-size: 22px;
            font-weight: 700;
            margin: 8px 0 12px;
            text-decoration: underline;
        }

        .intro {
            font-size: 16px;
            margin-bottom: 10px;
            text-align: justify;
        }

        .student-name,
        .dynamic-value {
            display: inline-block;
            font-weight: 600;
            border-bottom: 1px solid #000;
            padding: 0 2px 1px;
            line-height: 1.2;
            vertical-align: bottom;
        }

        .student-name {
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            min-width: 260px;
        }

        .dynamic-value {
            min-width: 80px;
        }

        .points {
            font-size: 15px;
        }

        .point {
            margin-bottom: 9px;
            text-align: justify;
            padding-left: 2px;
            line-height: 1.6;
        }

        .sub-point {
            margin: 3px 0 3px 22px;
            text-align: justify;
            line-height: 1.6;
        }

        .blank {
            display: inline-block;
            border-bottom: 1px solid #000;
            min-width: 70px;
            height: 15px;
            vertical-align: bottom;
            margin: 0 1px;
        }

        .blank-sm {
            min-width: 45px;
        }

        .blank-md {
            min-width: 110px;
        }

        .blank-lg {
            min-width: 180px;
        }

        .blank-xl {
            min-width: 260px;
        }

        .blank-date {
            min-width: 90px;
            flex: 1;
        }

        .footer-section {
            margin-top: 25px;
            font-size: 15px;
        }

        .footer-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            gap: 20px;
            margin-top: 28px;
        }

        .footer-left {
            flex: 1;
            line-height: 1.85;
        }

        .footer-right {
            flex-shrink: 0;
            text-align: right;
            font-weight: 700;
            font-size: 17px;
            min-width: 100px;
            padding-bottom: 2px;
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
    <div class="no-print">
        <button class="btn-print" onclick="window.print()">Print</button>
        <button class="btn-close" onclick="window.close()">Close</button>
    </div>

    @php
        $studentName = trim(
            ($admission->first_name ?? '') . ' ' .
            ($admission->last_name ?? '') . ' ' .
            ($admission->father_name ?? '')
        );
        $studentName = mb_strtoupper($studentName);
        $isFemale = strtolower(trim($admission->gender ?? '')) === 'female';
        $nameTitle = $isFemale ? 'કુમારી' : 'શ્રી';
        $studentType = $isFemale ? 'વિદ્યાર્થિની' : 'વિદ્યાર્થી';
        $enrollmentNo = trim($admission->enrolment_no ?? '');
        $issueDate = now()->format('d-m-Y');
        $courseName = trim($courseRegistration?->course?->course_name ?? '');
        $examYear = !empty($courseRegistration?->date)
            ? \Carbon\Carbon::parse($courseRegistration->date)->format('Y')
            : (!empty($courseRegistration?->created_at)
                ? $courseRegistration->created_at->format('Y')
                : '');
        $currentClass = trim($courseRegistration?->class?->class ?? $courseName);
        $exemptSubject = $courseName;

        // DOJ = course registration created_at (same as Course Details modal)
        $dojRaw = $courseRegistration?->created_at ?? $courseRegistration?->date ?? null;
        $dojDate = $dojRaw ? \Carbon\Carbon::parse($dojRaw)->startOfDay() : null;
        $todayDate = now()->startOfDay();

        $dojDayMonth = $dojDate ? $dojDate->format('d-m') : '';
        $dojYear = $dojDate ? $dojDate->format('Y') : '';
        $todayDayMonth = $todayDate->format('d-m');
        $todayYear = $todayDate->format('Y');

        $juneSessionDays = '';
        if ($dojDate) {
            $juneStart = \Carbon\Carbon::create($dojDate->year, 6, 1)->startOfDay();
            $octEnd = \Carbon\Carbon::create($dojDate->year, 10, 31)->startOfDay();
            if ($dojDate->between($juneStart, $octEnd)) {
                $juneSessionDays = $dojDate->diffInDays($octEnd) + 1;
            }
        }

        $novSessionDays = '';
        if ($dojDate) {
            $novYear = $dojDate->month >= 11 ? $dojDate->year : $dojDate->year - 1;
            $novStart = \Carbon\Carbon::create($novYear, 11, 1)->startOfDay();
            $periodStart = $dojDate->gt($novStart) ? $dojDate : $novStart;
            $periodEnd = $todayDate;
            if ($periodStart->lte($periodEnd)) {
                $novSessionDays = $periodStart->diffInDays($periodEnd) + 1;
            }
        }
    @endphp

    <div class="certificate">
        <div class="header-center">
            <div>REG. NO. A-663 JND DATED: 26-10-1964</div>
            <div>(Shree Patel Vidhyarthi Ashram – Keshod Sanchalit)</div>
            <div class="college-name">Shree Patel Vidhya Mandir Science College</div>
            <div>(Affiliated to Bhakta Kavi Narsinh Mehta University - Junagadh)</div>
            <div>Behind Maruti Showroom, Veraval Road, Keshod - 362220</div>
        </div>

        <div class="header-contact-row">
            <span>E-mail : pvmsciencecollege@gmail.com</span>
            <span>Mo. 96874 51774</span>
        </div>

        <hr class="header-divider">

        <div class="meta-row">
            <div class="header-meta-box">
                <div>College Code – 3109003</div>
                <div class="date-row">
                    <span>Date -</span>
                    <span class="dynamic-value">{{ $issueDate }}</span>
                </div>
            </div>
        </div>

        <div class="cert-title">કોલેજ છોડવા બાબતનું પ્રમાણપત્ર</div>

        <div class="intro">
            આથી પ્રમાણપત્ર આપવામાં આવે છે કે {{ $nameTitle }}:
            <span class="student-name">{{ $studentName ?: '____________________' }}</span>
            આ કોલેજના {{ $studentType }} હતા.
        </div>

        <div class="points">
            <div class="point">
                <strong>૧)</strong>
                તેમણે
                <span class="dynamic-value">{{ $courseName ?: '________' }}</span>
                ની પરીક્ષા
                <span class="dynamic-value">{{ $examYear ?: '________' }}</span>
                ના વર્ષ માં પસાર કર્યા પછી એમણે કોલેજમાં નીચે મુજબના સત્રો ભર્યા છે.
            </div>
            <div class="sub-point">
                <span class="dynamic-value">{{ $dojDayMonth ?: '________' }}</span>
                જૂન
                <span class="dynamic-value">{{ $dojYear ?: '________' }}</span>
                થી ઓક્ટો,
                <span class="dynamic-value">{{ $dojYear ?: '________' }}</span>
                (<span class="dynamic-value">{{ $dojDayMonth ?: '________' }}</span> માંથી <span class="dynamic-value">{{ $juneSessionDays ?: '________' }}</span> દિવસો)
            </div>
            <div class="sub-point">
                <span class="dynamic-value">{{ $todayDayMonth }}</span>
                નવે
                <span class="dynamic-value">{{ $todayYear }}</span>
                થી માર્ચ,
                <span class="dynamic-value">{{ $todayYear }}</span>
                (<span class="dynamic-value">{{ $todayDayMonth }}</span> માંથી <span class="dynamic-value">{{ $novSessionDays ?: '________' }}</span> દિવસો)
            </div>

            <div class="point">
                <strong>૨)</strong>
                આ કોલેજના વિદ્યાર્થી તરીકે તેમણે
                <span class="dynamic-value">{{ $examYear ?: '________' }}</span>
                માં
                <span class="dynamic-value">{{ $courseName ?: '________' }}</span>
                ની પરીક્ષા આપી તે પસાર કરી છે./નથી
                <span class="dynamic-value">{{ $exemptSubject ?: '________' }}</span>
                પણ વિષયમાં પરીક્ષા મુક્તિ મેળવી છે.
            </div>

            <div class="point">
                <strong>૩)</strong>
                આ કોલેજમાં અભ્યાસ ચાલુ રાખ્યો હોય તો અત્યારે તેઓ
                <span class="dynamic-value">{{ $currentClass ?: '________' }}</span>
                વર્ગમાં હોત.
            </div>

            <div class="point">
                <strong>૪)</strong>
                આ કોલેજનાં પુસ્તકો તેમની પાસે લેણા નથી.
            </div>

            <div class="point">
                <strong>૫)</strong>
                કોલેજનું બીજી કોઈ તેમની પાસે લેણું નથી.
            </div>

            <div class="point">
                <strong>૬)</strong>
                તેમની વર્તણૂક સારી છે.
            </div>

            <div class="point">
                <strong>૭)</strong>
                તેમના શૈક્ષણિક વિષયો નીચે પ્રમાણે હતા.
            </div>

            <div class="point">
                <strong>૮)</strong>
                યુનિવર્સિટીએ નિયત કરેલા વ્યાયામનો અભ્યાસક્રમ સંતોષકારક રીતે પૂરો કર્યો છે. એમને તબીબી કારણસર/એન.સી.સી. ના સભ્ય હોવાના કારણે વ્યાયામમાંથી મુક્તિ આપવામાં આવી છે.
            </div>

            <div class="point">
                <strong>૯)</strong>
                {{ $isFemale ? 'એની' : 'એનો' }} એનરોલમેન્ટ નં.
                <span class="dynamic-value">{{ $enrollmentNo ?: '____________________' }}</span>
                તા. <span class="dynamic-value">{{ $issueDate }}</span> છે.
            </div>

            <div class="point">
                <strong>૧૦)</strong>
                <span class="blank blank-md"></span> પરીક્ષા સીટ નં. <span class="blank blank-md"></span> અને પરિણામ <span class="blank blank-md"></span>
            </div>

            <div class="point">
                <strong>૧૧)</strong>
                તેમણે યુનિવર્સિટી કે કોલેજ તરફથી દંડવર્ક કે રસ્ટિકેટ કરવામાં આવ્યા નથી.
            </div>

            <div class="point">
                <strong>૧૨)</strong>
                નોંધ:- (EBC-BC કે અન્ય સ્કોલરશીપ વિશે જરૂર જણાવવું)
            </div>
        </div>

        <div class="footer-section">
            <div class="footer-row">
                <div class="footer-left">
                    <div>નં.એફ.સી./એસ.સી./ટી.સી./ માઈગ્રેશન/ <span class="blank blank-md"></span> નં. <span class="blank blank-md"></span></div>
                    <div>બિનપગાર ક્લાર્ક / રજીસ્ટ્રાર શ્રી <span class="blank blank-lg"></span></div>
                    <div>કોલેજ/યુનિવર્સિટી <span class="blank blank-lg"></span></div>
                </div>
                <div class="footer-right">પ્રિન્સીપાલ</div>
            </div>
        </div>
    </div>
</body>

</html>
