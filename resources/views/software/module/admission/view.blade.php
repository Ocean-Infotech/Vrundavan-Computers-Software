<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>PVM BCA College Admission Form</title>
    <style>
        /* ======= Form CSS ======= */
        .container {
            max-width: 900px;
            margin: 0 auto;
        }

        .card {
            max-width: 900px;
            margin: 0 auto;
        }

        .admission-form {
            border: 2px solid maroon;
            padding: 10px;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            position: relative;
            color: #660000;
            width: 100%;
            box-sizing: border-box;
        }

        hr {
            margin: 0;
            border: 0;
            border-top: 1px solid #000;
        }

        .admission-form h2 {
            color: maroon;
            font-weight: bold;
            text-align: center;
            margin-bottom: 4px;
        }

        .admission-form h4 {
            font-style: italic;
            text-align: center;
            margin-top: 0;
            margin-bottom: 14px;
        }

        .admission-header {
            font-weight: 600;
            font-size: 16px;
            color: maroon;
            margin-bottom: 4px;
            padding-left: 2px;
        }

        .admission-details-table {
            width: 100%;
            font-size: 14px;
            border-collapse: collapse;
            margin-bottom: 6px;
            color: #660000;
        }

        .admission-details-table td {
            padding: 2px 6px;
            vertical-align: middle;
            white-space: nowrap;
            height: 30px;
        }

        .admission-details-table td strong {
            font-weight: 600;
        }

        .photo-box {
            border: 2px solid black;
            width: 120px;
            height: 150px;
            overflow: hidden;
            position: absolute;
            top: 30px;
            right: 30px;
            margin: 0;
        }

        .photo-box img {
            width: 100%;
            height: auto;
        }

        .form-section {
            margin-bottom: 25px;
        }

        .form-section table {
            width: 100%;
            font-size: 16px;
            border-collapse: collapse;
            color: #660000;
        }

        .form-section td {
            padding: 6px;
            vertical-align: top;
        }

        .signature-box {
            text-align: center;
            margin-top: 40px;
            color: #660000;
        }

        .signature-box div {
            display: inline-block;
            width: 30%;
            border-top: 1px solid #000;
            padding-top: 5px;
        }

        .letter-boxes {
            display: flex;
            gap: 2px;
            margin-top: 6px;
        }

        .letter-box,
        .single-box {
            width: 30px;
            height: 36px;
            border: 1px solid #800000;
            text-align: center;
            font-weight: bold;
            font-size: 18px;
            text-transform: uppercase;
            line-height: 36px;
            font-family: 'Courier New', Courier, monospace;
            user-select: none;
        }

        .multi-boxes {
            display: flex;
            gap: 3px;
            margin-top: 4px;
            flex-wrap: wrap;
        }

        .info-section {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #660000;
            font-weight: 600;
            font-size: 14px;
            line-height: 1.4;
            margin-bottom: 12px;
        }

        .info-section label {
            display: inline-block;
            margin-right: 4px;
        }

        .info-section .underline-input {
            border-bottom: 1px solid #660000;
            padding: 0 6px;
            font-weight: 600;
            font-family: monospace;
            font-size: 16px;
            width: 140px;
            display: inline-block;
            vertical-align: middle;
            color: #000;
            user-select: none;
        }

        .info-section .small-underline-input {
            width: 90px;
        }

        .info-section .checkbox-label {
            cursor: pointer;
            margin-right: 18px;
            font-weight: 600;
            vertical-align: middle;
        }

        .education-table {
            width: 100%;
            margin-top: 12px;
            border-collapse: collapse;
            color: #660000;
            font-size: 15px;
        }

        .education-table thead tr {
            background-color: #f8e6e6;
            font-weight: 700;
            text-align: center;
        }

        .education-table th,
        .education-table td {
            border: 1px solid #800000;
            padding: 8px 10px;
        }

        .education-table tbody tr td {
            text-align: center;
        }

        .education-table tbody tr td:nth-child(2),
        .education-table tbody tr td:nth-child(4),
        .education-table tbody tr td:nth-child(6) {
            text-align: left;
            padding-left: 12px;
        }

        .footer-college-info {
            text-align: center;
            color: maroon;
            font-size: 25px;
            padding-top: 6px;
            font-weight: bold;
        }

        @page {
            margin: 0.5;
        }

        body {
            margin: 0.5;
            padding: 0.5;
        }
    </style>
</head>

<body>

    <div class="container mt-3">
        <div class="card">
            <div class="card-body admission-form">

                <h2>PVM BCA College</h2>
                <h4>Admission Form</h4>

                {{-- Admission Details Header --}}
                <div class="admission-header">Admission Details :</div>

                {{-- Admission Details with photo --}}
                <table class="admission-details-table">
                    <tr>
                        <td><strong>GR No. :</strong> {{ $admission->id }}</td>

                        <td><strong>Adm. Date :</strong>
                            {{ \Carbon\Carbon::parse($admission->created_at)->format('d/m/Y') }}</td>
                        <td><strong>Course :</strong> {{ $course->course->course_name ?? '-' }}</td>

                        <td rowspan="2" style="width: 130px; padding-left: 12px; vertical-align: middle;">
                            <div class="photo-box">
                                {{-- <img src=""> --}}
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Fees :</strong> ₹{{ number_format($course->course->course_fees, 0) }}</td>


                        <td><strong>Course Duration :</strong> {{ $course->course->course_year ?? '-' }} Years</td>

                        <td></td>
                    </tr>
                </table>

                <hr>

                {{-- Student Full Name --}}
                <div class="">
                    <div class="full-name-label">
                        <strong> Student's Full Name : (In Capital Letters) as per SSC Marksheet </strong>
                    </div>
                    <div class="letter-boxes">

                        @foreach ($full_name_letters as $letter)
                            <div class="letter-box">{{ $letter === ' ' ? '' : $letter }}</div>
                        @endforeach
                    </div>

                </div>

                {{-- Mother's Name --}}
                <div class="">
                    <div><strong>Mother's Name :</strong></div>
                    <div class="multi-boxes" style="max-width: 300px;">
                        @foreach ($mother_letters as $letter)
                            <div class="single-box">{{ $letter }}</div>
                        @endforeach
                        @for ($i = count($mother_letters); $i < 16; $i++)
                        @endfor
                    </div>
                </div>

                {{-- Permanent Address --}}
                <div class="">
                    <div><strong>Permanent Address :</strong></div>
                    <div class="form-section multi-boxes" style="max-width: 900px;">
                        @foreach ($permanent_address_letters as $letter)
                            <div class="single-box">{{ $letter === ' ' ? '' : $letter }}</div>
                        @endforeach
                    </div>
                </div>


                {{-- Contact & Personal Info --}}
                <div class="form-section info-section">
                    <div>
                        <label>Contact No. (Self):</label>
                        <span class="underline-input">{{ $admission->mobile_no ?? '' }}</span>

                        <label>(Father):</label>
                        <span class="underline-input"
                            style="height: 23px">{{ $admission->parent_mobile_no ?? '' }}</span>

                        <label>(Home):</label>
                        <span class="underline-input"
                            style="height: 23px">{{ $admission->other_mobile_no ?? '' }}</span>
                    </div>
                    <div style="margin-top: 6px;">
                        <label>E-mail ID :</label>
                        <span class="underline-input" style="width: 260px;height: 23px">
                            {{ $admission->email_address ?? '' }}
                        </span>
                    </div>

                    <div style="margin-top: 6px;">
                        <label>ABC ID :</label>
                        <span class="underline-input"
                            style="height: 23px">{{ $admission->apaar_id_abc_id ?? '' }}</span>
                        <label style="margin-left: 20px;">Single Girl Child?</label>
                        <label class="checkbox-label"><input type="checkbox" checked>Yes</label>
                        <label class="checkbox-label"><input type="checkbox">No</label>
                    </div>
                    <div style="margin-top: 6px;">
                        <label>Date Of Birth :</label>
                        <span
                            class="underline-input">{{ \Carbon\Carbon::parse($admission->date_of_birth)->format('d/m/Y') ?? '' }}</span>
                        <label style="margin-left: 20px;">Aadhaar No. :</label>
                        <span class="underline-input"
                            style="height: 23px">{{ $admission->aadhar_card_no ?? '' }}</span>
                    </div>
                    <div style="margin-top: 6px;">
                        <label>Sex:</label>
                        <label class="checkbox-label">
                            <input type="checkbox" {{ $admission->gender === 'Male' ? 'checked' : '' }}> Male
                        </label>
                        <label class="checkbox-label">
                            <input type="checkbox" {{ $admission->gender === 'Female' ? 'checked' : '' }}> Female
                        </label>
                        <label style="margin-left: 20px;">Status:</label>
                        <label class="checkbox-label"><input type="checkbox">Married</label>
                        <label class="checkbox-label"><input type="checkbox" checked>Unmarried</label>
                    </div>
                    <div style="margin-top: 6px;">
                        <label>Nationality :</label>
                        <span class="underline-input small-underline-input">INDIAN</span>
                        <label style="margin-left: 20px;">Caste :</label>
                        <label class="checkbox-label"> <input type="checkbox"
                                {{ $admission->category === 'OPEN' ? 'checked' : '' }}> OPEN</label>
                        <label class="checkbox-label"> <input type="checkbox"
                                {{ $admission->category === 'OBC' ? 'checked' : '' }}> OBC</label>
                        <label class="checkbox-label"> <input type="checkbox"
                                {{ $admission->category === 'SC' ? 'checked' : '' }}> SC</label>
                        <label class="checkbox-label"> <input type="checkbox"
                                {{ $admission->category === 'ST' ? 'checked' : '' }}> ST</label>
                        <label class="checkbox-label"> <input type="checkbox"
                                {{ $admission->category === 'EWS' ? 'checked' : '' }}> EWS</label>

                    </div>
                    <div style="margin-top: 6px;">
                        <label>Sub cast :</label>
                        <span class="underline-input small-underline-input" style="height: 23px"></span>
                        <label style="margin-left: 20px;">Religion :</label>
                        <span class="underline-input small-underline-input">HINDU</span>
                    </div>
                </div>

                {{-- Educational Details --}}
                <div class="form-section">
                    <strong style="color: maroon; font-size: 18px;">Educational Details:</strong>
                    <table class="education-table" cellpadding="6" cellspacing="0" border="1">
                        <thead>
                            <tr>
                                <th>No.</th>
                                <th>Exam</th>
                                <th>Year &amp; Month of Passing</th>
                                <th>Board/University</th>
                                <th>Result with %</th>
                                <th>Remark / Seat No.</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($admission->educationDetails as $index => $edu)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $edu->education }}</td>
                                    <td>{{ $edu->passing_year ?? '-' }}</td>
                                    <td>{{ $edu->board_university ?? '-' }}</td>
                                    <td>{{ $edu->percentage_cgpa ?? '-' }}</td>
                                    <td>{{ $edu->seat_no_nrollment_no ?? '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" style="text-align:center;">No records found</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>


                <div class="signature-box">
                    <div>Applicant's Signature</div>
                    <div>Parent's Signature</div>
                    <div>Principal</div>
                </div>
                <div class="footer-college-info">
                    PVM BCA College, Veraval Road, KESHOD. Mo. 96874 51774
                </div>

            </div>
        </div>
    </div>

</body>

</html>
