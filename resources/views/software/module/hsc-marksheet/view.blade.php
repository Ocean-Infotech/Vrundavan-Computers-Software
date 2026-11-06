@extends('software.layout.app')

@php
    $page_title = 'HSC Marksheet View';
    $studentName = $marksheet->studentName();
    $marks = $marksheet->marks ?? [];
@endphp
@section('title', $page_title)

@section('page_style_file')
    <style>
        .marksheet-actions { margin-bottom: 16px; }

        .a4-sheet {
            width: 210mm;
            min-height: 297mm;
            margin: 0 auto 20px;
            padding: 8mm;
            background: #fff;
            color: #111;
            box-sizing: border-box;
            font-family: "Times New Roman", Times, serif;
            border: 1px solid #cfcfcf;
        }

        .a4-outer {
            border: 2.5px solid #111;
            padding: 2px;
            box-sizing: border-box;
            min-height: calc(297mm - 16mm);
            height: 100%;
        }

        .a4-inner {
            border: 1.2px solid #111;
            padding: 2mm 5mm 8mm;
            box-sizing: border-box;
            min-height: calc(297mm - 16mm - 8px);
            display: flex;
            flex-direction: column;
        }

        .ms-header-img {
            width: 100%;
            height: auto;
            max-width: 100%;
            display: block;
            margin: 0 auto 8px;
            flex: 0 0 auto;
        }

        .ms-info-box {
            border: 1.2px solid #111;
            border-radius: 8px;
            overflow: hidden;
            margin: 2px 0 10px;
            flex: 0 0 auto;
        }
        .ms-info-head {
            background: #e9e9e9;
            border-bottom: 1.2px solid #111;
            text-align: center;
            font-family: "Times New Roman", Times, serif;
            font-size: 16px;
            font-weight: 700;
            padding: 5px 8px;
        }
        .ms-info-body {
            padding: 8px 12px 10px;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 14.5px;
            color: #111;
        }
        .ms-info-row {
            display: flex;
            align-items: flex-end;
            margin-bottom: 8px;
            gap: 6px;
        }
        .ms-info-row:last-child { margin-bottom: 0; }
        .ms-info-row.two-col {
            display: flex !important;
            flex-direction: row !important;
            flex-wrap: nowrap !important;
            align-items: flex-end;
            justify-content: flex-start;
            margin-bottom: 0;
            gap: 0;
        }
        .ms-info-col {
            display: flex !important;
            align-items: flex-end;
            gap: 6px;
            flex: 0 0 50% !important;
            width: 50% !important;
            max-width: 50% !important;
        }
        .ms-label {
            font-weight: 600;
            white-space: nowrap;
        }
        .ms-line {
            display: inline-block;
            border-bottom: 1px solid #111;
            flex: 0 0 auto;
            width: auto;
            max-width: 100%;
            padding: 0 2px 1px;
            line-height: 1.15;
            min-height: 15px;
            white-space: nowrap;
        }
        .ms-line.fixed {
            border-bottom: none;
            padding-left: 0;
        }
        .ms-line.roll,
        .ms-line.name { min-width: 0; }

        .ms-marks {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
            font-size: 15px;
            flex: 0 0 auto;
        }
        .ms-marks th,
        .ms-marks td {
            border: 1px solid #111;
            padding: 9px 10px;
        }
        .ms-marks thead th {
            text-align: center;
            font-weight: 700;
            background: #fff;
        }
        .ms-marks td.sr { width: 10%; text-align: center; }
        .ms-marks td.sub { width: 52%; text-align: left; }
        .ms-marks td.max { width: 19%; text-align: center; }
        .ms-marks td.obt { width: 19%; text-align: center; }
        .ms-marks tr.total td {
            font-weight: 700;
            text-align: center;
        }

        .ms-summary {
            width: 100%;
            border-collapse: collapse;
            margin: 0;
            font-size: 14.5px;
            flex: 0 0 auto;
        }
        .ms-summary td {
            border: 1px solid #111;
            width: 25%;
            padding: 10px 8px;
            text-align: left;
            font-weight: 700;
            vertical-align: middle;
        }
        .ms-summary .val {
            font-weight: 700;
            margin-left: 4px;
        }

        .ms-wish {
            text-align: center;
            font-family: "Times New Roman", Times, serif;
            font-size: 16.5px;
            font-weight: 700;
            font-style: italic;
            margin: 14px 0 0;
            letter-spacing: 0.3px;
            flex: 0 0 auto;
        }

        .ms-sign {
            width: 100%;
            margin-top: auto;
            padding-top: 28px;
            font-size: 14.5px;
            font-weight: 700;
            flex: 0 0 auto;
        }
        .ms-sign td { width: 50%; }
        .ms-sign .left { text-align: left; }
        .ms-sign .right { text-align: right; }

        @media print {
            @page {
                size: A4 portrait;
                margin: 0;
            }
            html, body {
                width: 210mm !important;
                height: 297mm !important;
                margin: 0 !important;
                padding: 0 !important;
                background: #fff !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            body * { visibility: hidden !important; }
            .a4-sheet,
            .a4-sheet * { visibility: visible !important; }

            .no-print,
            .layout-menu,
            .layout-navbar,
            .navbar,
            .content-footer,
            .layout-overlay,
            .drag-target,
            aside,
            .layout-menu-toggle,
            .breadcrumb,
            nav {
                display: none !important;
            }

            .layout-wrapper,
            .layout-container,
            .layout-page,
            .content-wrapper,
            .container-xxl,
            .container-p-y,
            .card,
            .card-body,
            .my-3 {
                all: unset !important;
                display: block !important;
                width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
                background: transparent !important;
                border: 0 !important;
                box-shadow: none !important;
            }

            .a4-sheet {
                position: fixed !important;
                left: 0 !important;
                top: 0 !important;
                width: 210mm !important;
                height: 297mm !important;
                min-height: 297mm !important;
                max-height: 297mm !important;
                margin: 0 !important;
                padding: 8mm !important;
                border: none !important;
                box-sizing: border-box !important;
                overflow: hidden !important;
                page-break-after: avoid !important;
                page-break-inside: avoid !important;
                break-inside: avoid !important;
            }
            .a4-outer {
                width: 100% !important;
                height: 100% !important;
                min-height: 0 !important;
                box-sizing: border-box !important;
            }
            .a4-inner {
                width: 100% !important;
                height: 100% !important;
                min-height: 0 !important;
                padding: 2mm 4mm 5mm !important;
                box-sizing: border-box !important;
                display: flex !important;
                flex-direction: column !important;
            }
            .ms-header-img {
                width: 100% !important;
                max-width: 100% !important;
                height: auto !important;
                max-height: none !important;
                object-fit: unset !important;
                margin: 0 0 6px !important;
            }
            .ms-info-box { margin: 2px 0 8px !important; }
            .ms-info-head {
                padding: 4px 8px !important;
                font-size: 15px !important;
            }
            .ms-info-body {
                padding: 7px 10px 8px !important;
                font-size: 13.5px !important;
            }
            .ms-info-row { margin-bottom: 6px !important; }
            .ms-marks {
                font-size: 14px !important;
                margin-bottom: 8px !important;
            }
            .ms-marks th,
            .ms-marks td { padding: 7px 9px !important; }
            .ms-summary { font-size: 14px !important; }
            .ms-summary td { padding: 8px 8px !important; }
            .ms-wish {
                margin-top: 10px !important;
                font-size: 15px !important;
            }
            .ms-sign {
                margin-top: auto !important;
                padding-top: 16mm !important;
                font-size: 13.5px !important;
            }
        }
    </style>
@endsection

@section('content')
    <div class="d-flex justify-content-lg-between px-1 no-print">
        @include('software.includes.breadcrumb', [
            'breadcrumbArray' => [
                ['title' => 'Other', 'url' => ''],
                ['title' => 'HSC Marksheet', 'url' => route('hsc-marksheet.index')],
                ['title' => 'View', 'url' => ''],
            ],
            'route' => 'hsc-marksheet',
            'show_add_btn' => false,
            'show_filter_btn' => false,
            'show_back_btn' => true,
        ])
    </div>

    <div class="card my-3">
        <div class="card-body">
            <div class="marksheet-actions text-end no-print">
                <a href="{{ route('hsc-marksheet.edit', $marksheet->id) }}" class="btn btn-primary">Edit</a>
                <button type="button" class="btn btn-outline-secondary" onclick="window.print()">Print A4</button>
            </div>

            <div class="a4-sheet">
                <div class="a4-outer">
                    <div class="a4-inner">
                        <img src="{{ asset('admin/assets/img/marksheet/pvm-marksheet-header.png') }}?v={{ @filemtime(public_path('admin/assets/img/marksheet/pvm-marksheet-header.png')) }}"
                            alt="PVM International School"
                            class="ms-header-img">

                        <div class="ms-info-box">
                            <div class="ms-info-head">Student Information</div>
                            <div class="ms-info-body">
                                <div class="ms-info-row">
                                    <span class="ms-label">Student Name :</span>
                                    <span class="ms-line name">{{ $studentName }}</span>
                                </div>
                                <div class="ms-info-row">
                                    <span class="ms-label">Academic Year :</span>
                                    <span class="ms-line fixed">{{ $marksheet->academic_year }}</span>
                                </div>
                                <div class="ms-info-row two-col">
                                    <div class="ms-info-col">
                                        <span class="ms-label">Standard :</span>
                                        <span class="ms-line fixed">{{ $marksheet->standard }}</span>
                                    </div>
                                    <div class="ms-info-col">
                                        <span class="ms-label">Roll Number :</span>
                                        <span class="ms-line roll">{{ $marksheet->roll_number ?: '' }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <table class="ms-marks">
                            <thead>
                                <tr>
                                    <th>Sr. No.</th>
                                    <th>Subject</th>
                                    <th>Maximum Marks</th>
                                    <th>Marks Obtained</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $sr = 0; @endphp
                                @foreach ($subjects as $subject)
                                    @php
                                        $obt = $marks[$subject['key']] ?? '';
                                        $obtUpper = strtoupper(trim((string) $obt));
                                        if ($obtUpper === 'NA') {
                                            continue; // subject not applicable — hide from marksheet
                                        }
                                        if ($marksheet->is_absent || $obtUpper === 'AB') {
                                            $obt = 'AB';
                                        }
                                        $sr++;
                                    @endphp
                                    <tr>
                                        <td class="sr">{{ $sr }}</td>
                                        <td class="sub">{{ $subject['name'] }}</td>
                                        <td class="max">{{ $subject['max'] }}</td>
                                        <td class="obt">{{ $obt !== '' ? $obt : '' }}</td>
                                    </tr>
                                @endforeach
                                <tr class="total">
                                    <td colspan="2">TOTAL</td>
                                    <td>{{ number_format((float) $marksheet->total_maximum, 0) }}</td>
                                    <td>{{ $marksheet->is_absent || strtoupper((string) $marksheet->result) === 'ABSENT' ? 'AB' : number_format((float) $marksheet->total_obtained, 0) }}</td>
                                </tr>
                            </tbody>
                        </table>

                        <table class="ms-summary">
                            <tr>
                                <td>Percentage: <span class="val">{{ $marksheet->is_absent ? 'AB' : number_format((float) $marksheet->percentage, 2) . ' %' }}</span></td>
                                <td>Grade: <span class="val">{{ $marksheet->grade ?: '' }}</span></td>
                                <td>Rank: <span class="val">{{ $marksheet->rank ?: '' }}</span></td>
                                <td>Result: <span class="val">{{ $marksheet->result ?: '' }}</span></td>
                            </tr>
                        </table>

                        <div class="ms-wish">Best Wish to All Student</div>

                        <table class="ms-sign">
                            <tr>
                                <td class="left">Class Teacher Signature</td>
                                <td class="right">Principal Signature</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
