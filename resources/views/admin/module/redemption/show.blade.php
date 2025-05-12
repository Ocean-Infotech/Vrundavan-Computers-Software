@extends('admin.layout.app')

@php
$page_title = $modules['title'] ?? null;
$folder_path = $modules['folder_path'] ?? null;
$route = $modules['route'] ?? null;
$permisstion_prefix = $modules['permisstion_prefix'] ?? null;
$i = 0;
@endphp
@section('title', $page_title)

@section('page_style_file')
{{-- <link href="{{ asset('public/assets/admin/plugins/datatable/dataTables.bootstrap4.min.css') }}" rel="stylesheet" type="text/css"> --}}
<!--datatable css-->
<link rel="stylesheet" href="{{ asset('admin/assets/cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css') }}">
<!--datatable responsive css-->
<link rel="stylesheet"
    href="{{ asset('admin/assets/cdn.datatables.net/responsive/2.2.9/css/responsive.bootstrap.min.css') }}">

<link rel="stylesheet" href="{{ asset('admin/assets/cdn.datatables.net/buttons/2.2.2/css/buttons.dataTables.min.css') }}">
@endsection

@section('breadcrumb')
<!-- start page title -->
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            {{-- <h3 class="page-title">{{ $page_title }}</h3> --}}
            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route($route . '.index') }}">View all
                            {{ $page_title }}</a>
                    </li>
                    <li class="breadcrumb-item active">{{ "Show all Redemption Item's" }}</li>
                </ol>
            </div>
        </div>
    </div>
</div>
<!-- end page title -->
@endsection

@section('content')

@include('admin.partials.flash_messages')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <div class="row">
                    <div class="col-md-3 col-sm-12"><b>Label :</b> {{ $show?->label ?? '' }} </div>
                    <div class="col-md-3 col-sm-12"><b>Quantity :</b> {{ $show?->quantity ?? '' }} </div>
                    <div class="col-md-3 col-sm-12"><b>Collect Points :</b> {{ $show?->collect_points ?? '' }} </div>
                    <div class="col-md-3 col-sm-12 text-center">
                        <button class="btn btn-success" id="print-all-btn">Print All</button>
                    </div>
                </div>
            </div>
            <hr class="mb-0" />
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th class="text-center">Sr. No.</th>
                                <th class="">Redemption Code</th>
                                <th class="">Collect Point</th>
                                <th class="">Scanned By</th>
                                <th class="">Scanned Date</th>
                                <th class="text-center">Status</th>
                                <th class="">Print</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if (isset($show?->redemption_items))
                            @foreach ($show?->redemption_items as $index => $item)
                            <tr>
                                <td class="text-center">{{ $index + 1 }}</td>
                                <td class="">{{ $item?->redemption_code_label ?? '-' }}</td>
                                <td class="">{{ $item?->collect_points ?? '-' }}</td>
                                <td class="">
                                    {{-- {{ $item?->scanned_user_id ?? '-' }} --}}
                                    @if ($item?->scanned_user_id)
                                    {!! 'Name : ' . $item?->scanned_user?->name . '<br />' ?? '' !!}
                                    {!! 'Emial : ' . $item?->scanned_user?->email ?? '' !!}
                                    @else
                                    {{ '-' }}
                                    @endif
                                </td>
                                <td class="">
                                    {{ $item?->scanned_date ?? '-' }}
                                    {{-- {{ Carbon::now($item?->scanned_date)->format("d/m/Y") ?? '-' }} --}}
                                </td>
                                <td class="">
                                    @if (!$item?->scanned_user_id)
                                    @if ($item?->status == 'active')
                                    <span
                                        class="w-100 badge bg-success-subtle text-success text-uppercase">Active</span>
                                    @elseif ($item?->status == 'inactive')
                                    <span
                                        class="w-100 badge bg-danger-subtle text-danger text-uppercase">InActive</span>
                                    @else
                                    <span
                                        class="w-100 badge bg-danger-subtle text-info text-uppercase">{{ $item?->status }}</span>
                                    @endif
                                    @if (in_array($item?->status, ['active', 'inactive']))
                                    <select class="status-dropdown form-select"
                                        data-id="{{ $item->id }}">
                                        <option value="active"
                                            {{ $item->status == 'active' ? 'selected' : '' }}>ACTIVE
                                        </option>
                                        <option value="inactive"
                                            {{ $item->status == 'inactive' ? 'selected' : '' }}>
                                            INACTIVE
                                        </option>
                                        {{-- <option value="PENDING" {{ $item->status == 'PENDING' ? 'selected' : '' }}>PENDING</option> --}}
                                    </select>
                                    @else
                                    <span
                                        class="badge bg-danger-subtle text-info text-uppercase">{{ $item?->status }}</span>
                                    @endif
                                    @endif
                                </td>
                                <td>
                                    @if (in_array($item?->status, ['active', 'Active']))
                                    <button class="btn btn-primary print-btn"
                                        data-code="{{ $item?->redemption_code_label }}"
                                        data-points="{{ $item?->collect_points }}">Print</button>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<div id="print-section" style="display: none;"></div>
<div id="qrcodeview" style="display: none;margin-top:20px"></div>
@endsection

@section('page_script_file')
<!--datatable js-->
<script src="{{ asset('admin/assets/cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('admin/assets/cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js') }}"></script>
<script src="{{ asset('admin/assets/cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js') }}"></script>
<!--<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jquery.qrcode/1.0/jquery.qrcode.min.js">
    </script>-->
<script src="https://cdn.jsdelivr.net/gh/davidshimjs/qrcodejs/qrcode.min.js"></script>
@endsection

@section('page_leavel_script')
<script type="text/javascript">
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    $(document).ready(function() {
        $('.status-dropdown').change(function() {
            var status = $(this).val();
            var itemId = $(this).data('id');

            $.ajax({
                url: "{{ route('redemption-item-status') }}",
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    id: itemId,
                    status: status
                },
                success: function(response) {
                    const resposneData = response;
                    console.log("resposneData 160", resposneData);

                    if (resposneData.status == "true") {
                        Swal.fire({
                            // position: "top-end",
                            icon: 'success',
                            title: "Status updated successfully!",
                            showConfirmButton: false,
                            timer: 1500
                        });
                    } else {
                        if (resposneData?.message == "Validation Error.") {
                            Swal.fire({
                                // position: "top-end",
                                icon: 'error',
                                title: resposneData?.messages,
                                showConfirmButton: false,
                                timer: 3000
                            });
                        } else {
                            Swal.fire({
                                // position: "top-end",
                                icon: 'error',
                                title: "Failed to update status.",
                                showConfirmButton: false,
                                timer: 3000
                            });
                        }
                    }
                    location.reload();
                },
                error: function() {
                    Swal.fire({
                        position: "top-end",
                        icon: 'error',
                        title: "Something went wrong. Please try again.",
                        showConfirmButton: false,
                        timer: 3000
                    });
                }
            });
        });

        let show = $.parseJSON('{!! $show !!}');
        let qrOptions = {
            width: 200,
            height: 200
        };

        $('#print-all-btn').click(function() {
            // Clear previous content
            $("#qrcodeview").html("");

            // Check if show.redemption_items is defined
            if (typeof show !== "undefined" && show.redemption_items) {
                console.log("show 232", show.redemption_items);
                // Filter and process only active items
                $.each(show.redemption_items, function(index, item) {
                    if (typeof item.status !== "undefined" && ["Active", "active"].includes(item
                            .status)) {
                        var qrText = item.redemption_code_label;

                        // Create a container for each QR code
                        var qrContainer = $(
                            '<div style="display: inline-block; text-align: center; margin: 10px;"></div>'
                        );

                        // QR code div
                        var qrDiv = $('<div></div>');
                        qrContainer.append(qrDiv);

                        // Label
                        qrContainer.append(
                            `<div style="margin-top: 10px;">Code: <b>${qrText}</b></div>`);

                        // Append to QR code view
                        $("#qrcodeview").append(qrContainer);

                        // Generate QR code

                        new QRCode(qrDiv[0], {
                            ...qrOptions,
                            text: qrText
                        });
                    }
                });

                // Wait for QR code to be generated before printing
                setTimeout(function() {
                    printQRCode();
                }, 500);
            } else {
                alert("No active redemption items found!");
            }
        });

        $('.print-btn').click(function() {
            var code = $(this).data('code');
            var points = $(this).data('points');
            // var qrText = `Code: ${code}\nPoints: ${points}`;
            var qrText = code;

            // Clear previous content
            $("#qrcodeview").html("");

            // Create container for QR code and text
            var qrContainer = $('<div style="text-align: left;"></div>');

            // Add top text
            // qrContainer.append(`<div style="font-size: 16px; font-weight: bold; margin-bottom: 10px;">Redemption Code</div>`);

            // Add QR code div
            var qrDiv = $('<div id="generatedQRCode"></div>');
            qrContainer.append(qrDiv);

            // Add bottom text
            qrContainer.append(`<div style="font-size: 14px; margin-top: 10px;">Code: ${code}</div>`);
            // qrContainer.append(`<div style="font-size: 14px; margin-top: 10px;">Collect Points: ${points}</div>`);

            // Append to QR code view container
            $("#qrcodeview").append(qrContainer);

            // Generate new QR code
            new QRCode(qrDiv[0], {
                ...qrOptions,
                text: qrText
            });
            // Wait for QR code to be generated before printing
            setTimeout(function() {
                printQRCode();
            }, 500);
        });

        function printQRCode() {
            var printContent = document.getElementById('qrcodeview').innerHTML;
            var originalContent = document.body.innerHTML;

            document.body.innerHTML = printContent;
            window.print();
            document.body.innerHTML = originalContent;
            location.reload(); // Refresh to restore functionality
        }
    });
</script>
@endsection