@extends('software.layout.app')

@section('title', 'Registered Security Devices')

@section('content')
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-3">
            <h5 class="mb-0"><i class="ti ti-devices me-1"></i> Connected Hardware Registry</h5>
            <div class="d-flex align-items-center gap-2">
                <!-- Filter Dropdown -->
                <form method="GET" action="{{ route('device-shield.index') }}" class="d-flex align-items-center gap-2">
                    <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">All Statuses</option>
                        @foreach(['approved', 'pending', 'suspended', 'rejected'] as $s)
                            <option value="{{ $s }}" {{ $status === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                        @endforeach
                    </select>
                </form>
                <a href="{{ route('download.agent') }}" class="btn btn-sm btn-primary">
                    <i class="ti ti-download me-1"></i> Get Shield Agent
                </a>
            </div>
        </div>
        <div class="table-responsive text-nowrap">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>First Registered By <small class="text-muted">(Registrant)</small></th>
                        <th>PC Alias / Hostname</th>
                        <th>Hardware Specs</th>
                        <th>Status Badge</th>
                        <th>Authorized By</th>
                        <th>Last Active</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($devices as $device)
                        <tr>
                            <td>
                                <div class="d-flex flex-column">
                                    <span class="fw-semibold text-dark">{{ $device->user->name ?? 'Unknown' }}</span>
                                    <small class="text-muted">{{ $device->user->email ?? '' }}</small>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <span id="alias-display-{{ $device->id }}"
                                        class="badge bg-label-primary">{{ $device->device_name ?: $device->pc_name ?: 'N/A' }}</span>
                                    <button class="btn btn-xs p-1 text-primary btn-edit-name" data-id="{{ $device->id }}"
                                        data-name="{{ $device->device_name ?: $device->pc_name }}">
                                        <i class="ti ti-edit fs-5"></i>
                                    </button>
                                </div>

                            </td>
                            <td>
                                <div class="d-flex flex-column" style="font-size: 0.75rem;">
                                    <span><strong>MB:</strong> {{ substr($device->motherboard_serial, 0, 15) }}...</span>
                                    <span><strong>BIOS:</strong> {{ substr($device->bios_serial, 0, 15) }}...</span>
                                    <span><strong>CPU:</strong> {{ substr($device->cpu_id, 0, 15) }}...</span>
                                </div>
                            </td>
                            <td>
                                @php
                                    $badges = [
                                        'approved' => 'success',
                                        'pending' => 'warning',
                                        'suspended' => 'danger',
                                        'rejected' => 'secondary'
                                    ];
                                    $color = $badges[$device->status] ?? 'info';
                                @endphp
                                <span class="badge bg-{{ $color }}">{{ ucfirst($device->status) }}</span>
                            </td>
                            <td>
                                @if($device->approver)
                                    <div class="d-flex flex-column" style="font-size: 0.8rem;">
                                        <span class="text-dark">{{ $device->approver->name }}</span>
                                        <small class="text-muted">{{ $device->approved_at?->format('M d, H:i') }}</small>
                                    </div>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                @if($device->last_login_at)
                                    <small class="fw-semibold">{{ $device->last_login_at->diffForHumans() }}</small>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                <div class="dropdown">
                                    <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                        <i class="ti ti-dots-vertical"></i>
                                    </button>
                                    <div class="dropdown-menu">
                                        @if($device->status !== 'approved')
                                            <a class="dropdown-item btn-ajax" href="javascript:void(0);"
                                                data-url="{{ route('device-shield.approve', $device->id) }}">
                                                <i class="ti ti-check me-1 text-success"></i> Approve Device
                                            </a>
                                        @endif
                                        @if($device->status === 'approved')
                                            <a class="dropdown-item btn-ajax" href="javascript:void(0);"
                                                data-url="{{ route('device-shield.suspend', $device->id) }}">
                                                <i class="ti ti-ban me-1 text-warning"></i> Suspend Access
                                            </a>
                                        @endif
                                        @if($device->status === 'suspended')
                                            <a class="dropdown-item btn-ajax" href="javascript:void(0);"
                                                data-url="{{ route('device-shield.reactivate', $device->id) }}">
                                                <i class="ti ti-refresh me-1 text-success"></i> Reactivate
                                            </a>
                                        @endif
                                        @if($device->status !== 'rejected' && $device->status !== 'approved')
                                            <a class="dropdown-item btn-ajax" href="javascript:void(0);"
                                                data-url="{{ route('device-shield.reject', $device->id) }}">
                                                <i class="ti ti-x me-1 text-danger"></i> Reject Request
                                            </a>
                                        @endif
                                        <div class="dropdown-divider"></div>
                                        <a class="dropdown-item btn-ajax" href="javascript:void(0);"
                                            data-url="{{ route('device-shield.force-rebind', $device->id) }}"
                                            style="color: red;">
                                            <i class="ti ti-trash me-1"></i> Force Unlink / Delete
                                        </a>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">No security devices registered matching filters.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="card mb-4 mt-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="ti ti-shield-lock me-1"></i> Employee Shield Enforcement</h5>
            <small class="text-muted">Toggle device lock verification per employee/admin</small>
        </div>
        <div class="table-responsive text-nowrap">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>User / Employee</th>
                        <th>Email Address</th>
                        <th>System Role</th>
                        <th>Main Admin (Bypass)</th>
                        <th>Shield Enforcement Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                        <tr>
                            <td>
                                <span class="fw-semibold text-dark">{{ $user->name }}</span>
                            </td>
                            <td><code>{{ $user->email }}</code></td>
                            <td>
                                @if($user->is_admin || (method_exists($user, 'hasRole') && $user->hasRole('super-admin')))
                                    <span class="badge bg-label-danger">Super Admin</span>
                                @else
                                    <span class="badge bg-label-secondary">Employee</span>
                                @endif
                            </td>
                            <td>
                                @if($user->is_admin || (method_exists($user, 'hasRole') && $user->hasRole('super-admin')))
                                    <span class="badge bg-success"><i class="ti ti-circle-check me-1"></i> Always Bypassed</span>
                                @else
                                    <span class="badge bg-secondary"><i class="ti ti-circle-x me-1"></i> No Bypass</span>
                                @endif
                            </td>
                            <td>
                                @if($user->is_admin || (method_exists($user, 'hasRole') && $user->hasRole('super-admin')))
                                    <div class="form-check form-switch mb-0">
                                        <input class="form-check-input" type="checkbox" checked disabled
                                            style="cursor: not-allowed;">
                                        <label class="form-check-label text-muted">Bypassed</label>
                                    </div>
                                @else
                                    <div class="form-check form-switch mb-0">
                                        <input class="form-check-input user-toggle-check" type="checkbox" data-id="{{ $user->id }}"
                                            {{ $user->check_device ? 'checked' : '' }} style="cursor: pointer;">
                                        <label class="form-check-label text-dark fw-semibold" id="toggle-label-{{ $user->id }}">
                                            {{ $user->check_device ? 'Active Enforcement' : 'Bypassed / Not Checked' }}
                                        </label>
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted">No users found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Edit Name Modal -->
    <div class="modal fade" id="editNameModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form id="editNameForm" class="modal-content">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Rename Device PC</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="device_id" id="modal-device-id">
                    <div class="mb-3">
                        <label for="modal-device-name" class="form-label">PC Device Name / Friendly Alias</label>
                        <input type="text" class="form-control" name="device_name" id="modal-device-name" required
                            placeholder="e.g. Sales PC 1">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="btn-save-name">Save Name</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('page_leavel_script')
    <script>
        $(document).ready(function () {
            // Handle User Toggle Check Switch
            $('.user-toggle-check').on('change', function () {
                const checkbox = $(this);
                const id = checkbox.data('id');
                const label = $(`#toggle-label-${id}`);
                const isChecked = checkbox.is(':checked');

                label.text(isChecked ? 'Activating...' : 'Deactivating...');

                $.ajax({
                    url: `/software/device-shield/users/${id}/toggle-check`,
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function (response) {
                        if (response.success) {
                            toastr.success(response.message);
                            label.text(response.check_device ? 'Active Enforcement' : 'Bypassed / Not Checked');
                            checkbox.prop('checked', response.check_device);
                        } else {
                            toastr.error(response.message || 'Operation failed.');
                            checkbox.prop('checked', !isChecked);
                            label.text(!isChecked ? 'Active Enforcement' : 'Bypassed / Not Checked');
                        }
                    },
                    error: function () {
                        toastr.error('A server error occurred.');
                        checkbox.prop('checked', !isChecked);
                        label.text(!isChecked ? 'Active Enforcement' : 'Bypassed / Not Checked');
                    }
                });
            });

            // Handle Action Dropdown Clicks (Approve/Suspend/Reject/Unlink)
            $('.btn-ajax').on('click', function (e) {
                e.preventDefault();
                const url = $(this).data('url');

                Swal.fire({
                    title: 'Are you sure?',
                    text: "Do you want to run this administrative action on this PC?",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#7367f0',
                    cancelButtonColor: '#808390',
                    confirmButtonText: 'Yes, execute!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: url,
                            type: 'POST',
                            data: {
                                _token: '{{ csrf_token() }}'
                            },
                            success: function (response) {
                                if (response.success) {
                                    toastr.success(response.message);
                                    setTimeout(() => {
                                        window.location.reload();
                                    }, 1000);
                                } else {
                                    toastr.error(response.message || 'Operation failed.');
                                }
                            },
                            error: function () {
                                toastr.error('A server error occurred.');
                            }
                        });
                    }
                });
            });

            // Edit Name Modal Open
            $('.btn-edit-name').on('click', function () {
                const id = $(this).data('id');
                const name = $(this).data('name');
                $('#modal-device-id').val(id);
                $('#modal-device-name').val(name);
                $('#editNameModal').modal('show');
            });

            // Save Name Submission
            $('#editNameForm').on('submit', function (e) {
                e.preventDefault();
                const id = $('#modal-device-id').val();
                const name = $('#modal-device-name').val();
                const saveBtn = $('#btn-save-name');

                saveBtn.prop('disabled', true).text('Saving...');

                $.ajax({
                    url: `/software/device-shield/devices/${id}/update-name`,
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        device_name: name
                    },
                    success: function (response) {
                        saveBtn.prop('disabled', false).text('Save Name');
                        if (response.success) {
                            toastr.success(response.message);
                            $(`#alias-display-${id}`).text(name);
                            $('#editNameModal').modal('hide');
                        } else {
                            toastr.error(response.message);
                        }
                    },
                    error: function () {
                        saveBtn.prop('disabled', false).text('Save Name');
                        toastr.error('A server error occurred.');
                    }
                });
            });
        });
    </script>
@endsection