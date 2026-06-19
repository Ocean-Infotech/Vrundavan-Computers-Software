@extends('software.layout.app')

@section('title', 'Device Shield Dashboard')

@section('content')
    <div class="row">
        <!-- Stats Cards -->
        <div class="col-12 col-sm-6 col-md-3 mb-4">
            <div class="card h-100">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-1 text-muted">Total Devices</h5>
                        <h3 class="mb-0 fw-semibold">{{ $stats['total'] }}</h3>
                    </div>
                    <div class="avatar bg-light-primary rounded p-2">
                        <i class="ti ti-devices text-primary fs-3"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-md-3 mb-4">
            <div class="card h-100">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-1 text-success">Approved</h5>
                        <h3 class="mb-0 fw-semibold">{{ $stats['approved'] }}</h3>
                    </div>
                    <div class="avatar bg-light-success rounded p-2">
                        <i class="ti ti-shield-check text-success fs-3"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-md-3 mb-4">
            <div class="card h-100 bg-light-warning">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-1 text-warning">Pending</h5>
                        <h3 class="mb-0 fw-semibold">{{ $stats['pending'] }}</h3>
                    </div>
                    <div class="avatar bg-white rounded p-2">
                        <i class="ti ti-clock text-warning fs-3"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-md-3 mb-4">
            <div class="card h-100">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-1 text-danger">Suspended</h5>
                        <h3 class="mb-0 fw-semibold">{{ $stats['suspended'] }}</h3>
                    </div>
                    <div class="avatar bg-light-danger rounded p-2">
                        <i class="ti ti-shield-x text-danger fs-3"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Pending Requests -->
        <div class="col-12 mb-4">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="ti ti-alert-circle text-warning me-1"></i> Pending Authorization Requests
                    </h5>
                    <a href="{{ route('device-shield.index', ['status' => 'pending']) }}"
                        class="btn btn-sm btn-outline-primary">View All</a>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>First Login User <small class="text-muted">(Device Registrant)</small></th>
                                <th>PC Name</th>
                                <th>Hardware ID</th>
                                <th>Registered</th>
                                <th>Actions <small class="text-muted">(Approves for ALL users)</small></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($pendingRequests as $request)
                                <tr>
                                    <td>
                                        <div class="d-flex flex-column">
                                            <span class="fw-medium text-dark">{{ $request->user->name ?? 'Unknown' }}</span>
                                            <small class="text-muted">{{ $request->user->email ?? '' }}</small>
                                        </div>
                                    </td>
                                    <td><span class="badge bg-label-info">{{ $request->pc_name ?? 'N/A' }}</span></td>
                                    <td><code>{{ substr($request->hardware_id, 0, 16) }}...</code></td>
                                    <td>{{ $request->created_at->diffForHumans() }}</td>
                                    <td>
                                        <button class="btn btn-sm btn-success btn-action"
                                            data-url="{{ route('device-shield.approve', $request->id) }}">
                                            <i class="ti ti-check me-1"></i> Approve
                                        </button>
                                        <button class="btn btn-sm btn-danger btn-action"
                                            data-url="{{ route('device-shield.reject', $request->id) }}">
                                            <i class="ti ti-x me-1"></i> Reject
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">No pending authorization requests.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Recent Logs -->
        <div class="col-12 col-lg-6 mb-4">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="ti ti-file-text me-1"></i> Security Audit Logs</h5>
                    <a href="{{ route('device-shield.logs') }}" class="btn btn-sm btn-outline-secondary">View Audit
                        Trails</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead>
                                <tr>
                                    <th>Timestamp</th>
                                    <th>User</th>
                                    <th>Status</th>
                                    <th>Remarks</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentLogs as $log)
                                    <tr>
                                        <td><small class="text-muted">{{ $log->created_at->format('M d, H:i') }}</small></td>
                                        <td><small>{{ $log->user->name ?? 'Guest' }}</small></td>
                                        <td>
                                            @if($log->login_status === 'success')
                                                <span class="badge bg-label-success p-1"><i class="ti ti-circle-check"></i></span>
                                            @elseif(str_starts_with($log->login_status, 'failed'))
                                                <span class="badge bg-label-danger p-1"><i class="ti ti-circle-x"></i></span>
                                            @else
                                                <span class="badge bg-label-warning p-1"><i class="ti ti-clock"></i></span>
                                            @endif
                                        </td>
                                        <td><small class="text-truncate d-inline-block"
                                                style="max-width: 200px;">{{ $log->remarks }}</small></td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-3 text-muted">No logs recorded yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Device List -->
        <div class="col-12 col-lg-6 mb-4">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="ti ti-list me-1"></i> Active Device Roster</h5>
                    <a href="{{ route('device-shield.index') }}" class="btn btn-sm btn-outline-primary">Manage All</a>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm table-hover">
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>PC Name</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($devices->take(10) as $dev)
                                <tr>
                                    <td>
                                        <div class="d-flex flex-column">
                                            <span class="fw-semibold">{{ $dev->user->name ?? 'Unknown' }}</span>
                                        </div>
                                    </td>
                                    <td><span class="badge bg-label-secondary">{{ $dev->pc_name ?? 'N/A' }}</span></td>
                                    <td>
                                        @php
                                            $badges = [
                                                'approved' => 'success',
                                                'pending' => 'warning',
                                                'suspended' => 'danger',
                                                'rejected' => 'secondary'
                                            ];
                                            $color = $badges[$dev->status] ?? 'info';
                                        @endphp
                                        <span class="badge bg-{{ $color }}">{{ ucfirst($dev->status) }}</span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center py-3 text-muted">No devices registered.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('page_leavel_script')
    <script>
        $(document).ready(function () {
            $('.btn-action').on('click', function (e) {
                e.preventDefault();
                const btn = $(this);
                const url = btn.data('url');

                btn.prop('disabled', true).html('<i class="ti ti-loader rotate"></i> Processing...');

                $.ajax({
                    url: url,
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function (response) {
                        if (response.success) {
                            toastr.success(response.message || 'Action executed successfully.');
                            setTimeout(() => {
                                window.location.reload();
                            }, 1000);
                        } else {
                            toastr.error(response.message || 'Operation failed.');
                            btn.prop('disabled', false);
                        }
                    },
                    error: function (xhr) {
                        console.error(xhr);
                        toastr.error('A server error occurred. Please check console logs.');
                        btn.prop('disabled', false);
                    }
                });
            });
        });
    </script>
    <style>
        .bg-light-primary {
            background-color: rgba(115, 103, 240, 0.08) !important;
        }

        .bg-light-success {
            background-color: rgba(40, 199, 111, 0.08) !important;
        }

        .bg-light-warning {
            background-color: rgba(255, 159, 67, 0.08) !important;
        }

        .bg-light-danger {
            background-color: rgba(234, 84, 85, 0.08) !important;
        }

        .rotate {
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            100% {
                transform: rotate(360deg);
            }
        }
    </style>
@endsection