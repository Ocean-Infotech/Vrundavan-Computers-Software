@extends('software.layout.app')

@php
    $page_title = $modules['title'] ?? 'Fee';
    $route = $modules['route'] ?? 'fees';
@endphp

@section('title', $page_title)

@section('page_style_file')
    <style>
        .dynamic-field {
            display: flex;
            margin-bottom: 10px;
            gap: 10px;
        }

        .dynamic-field input {
            flex: 1;
        }
    </style>
@endsection

@section('content')
    <div class="row">
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5>{{ $page_title }}</h5>
            </div>
            <div class="card-body">

                {{-- Add/Edit Form --}}
                <form id="fee-form" method="POST">
                    @csrf
                    <input type="hidden" id="fee_id">
                    <div class="dynamic-field">
                        <input type="text" name="fee_name" id="fee_name" class="form-control"
                            placeholder="Enter Fee Name" required />
                        <input type="number" name="amount" id="amount" class="form-control" placeholder="Enter Amount"
                            required />
                    </div>
                    <button type="submit" class="btn btn-success mt-2" id="submit-btn">Add Fee</button>
                </form>

                <hr>

                {{-- Fee Table --}}
                <table class="table table-bordered mt-3" id="fee-table">
                    <thead>
                        <tr>
                            <th>Sr no</th>
                            <th>Fee Name</th>
                            <th>Amount</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($fees as $index => $fee)
                            <tr data-id="{{ $fee->id }}">
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $fee->fee_name }}</td>
                                <td>{{ $fee->amount }}</td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-primary edit-fee"
                                        data-id="{{ $fee->id }}" data-fee_name="{{ $fee->fee_name }}"
                                        data-amount="{{ $fee->amount }}">
                                        Edit
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

            </div>
        </div>
    </div>
@endsection

@section('page_leavel_script')
    <script>
        $(document).ready(function() {

            // alert("------")
            // Submit form (Add or Edit)
            $('#fee-form').submit(function(e) {
                e.preventDefault();

                let feeId = $('#fee_id').val();
                let isEdit = feeId !== '';
                let method = isEdit ? 'PUT' : 'POST';
                let url = isEdit ?
                    '{{ route("$route.update", ':id') }}'.replace(':id', feeId) :
                    '{{ route("$route.store") }}';


                let payload = {
                    fee_name: $('#fee_name').val(),
                    amount: $('#amount').val(),
                };

                $.ajax({
                    url: url,
                    type: method,
                    headers: {
                        'X-CSRF-TOKEN': $('input[name="_token"]').val(),
                        'Accept': 'application/json'
                    },
                    contentType: 'application/json',
                    data: JSON.stringify(payload),
                    success: function(data) {
                        if (data.success) {
                            let tbody = $('#fee-table tbody');
                            tbody.empty();

                            $.each(data.fees, function(i, fee) {
                                tbody.append(`
                <tr data-id="${fee.id}">
                    <td>${i + 1}</td>
                    <td>${fee.fee_name}</td>
                    <td>${fee.amount}</td>
                    <td>
                        <button type="button" class="btn btn-sm btn-primary edit-fee"
                                data-id="${fee.id}"
                                data-fee_name="${fee.fee_name}"
                                data-amount="${fee.amount}">
                            Edit
                        </button>
                    </td>
                </tr>
            `);
                            });

                            // Reset form
                            $('#fee-form')[0].reset();
                            $('#fee_id').val('');
                            $('#submit-btn').text('Add Fee');

                            // 🔔 Show toast message
                            if (isEdit) {
                                toastr.success('Fee updated successfully!');
                            } else {
                                toastr.success('Fee added successfully!');
                            }
                        } else {
                            toastr.error('Something went wrong!');
                        }
                    },
                    error: function(xhr) {
                        toastr.error(xhr.responseJSON?.message || 'Server error');
                    },
                    error: function(xhr) {
                        alert(xhr.responseJSON?.message || 'Server error');
                    }
                });
            });

            $(document).on('click', '.edit-fee', function() {
                $('#fee_id').val($(this).data('id'));
                $('#fee_name').val($(this).data('fee_name'));
                $('#amount').val($(this).data('amount'));
                $('#submit-btn').text('Update Fee');
            });
        });
    </script>
@endsection
