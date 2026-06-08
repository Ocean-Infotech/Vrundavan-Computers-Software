@extends('software.layout.app')

@section('title', 'Security Audit Trails')

@section('content')
<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-3">
        <h5 class="mb-0"><i class="ti ti-file-text me-1"></i> Shield Security Logs & Audits</h5>
        <div class="d-flex align-items-center gap-2">
            @if($hardwareId || $userId)
                <a href="{{ route('device-shield.logs') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="ti ti-clear-all me-1"></i> Clear Filters
                </a>
            @endif
        </div>
    </div>
    <div class="table-responsive text-nowrap">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>Timestamp</th>
                    <th>User Employee</th>
                    <th>Device Hardware ID</th>
                    <th>Status</th>
                    <th>IP Address</th>
                    <th>Audit Remarks</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $log)
                    <tr>
                        <td>
                            <span class="text-dark fw-medium">{{ $log->created_at->format('Y-m-d H:i:s') }}</span>
                        </td>
                        <td>
                            @if($log->user)
                                <a href="{{ route('device-shield.logs', ['user_id' => $log->user->id]) }}" class="d-flex flex-column text-decoration-none">
                                    <span class="fw-semibold text-dark">{{ $log->user->name }}</span>
                                    <small class="text-muted">{{ $log->user->email }}</small>
                                </a>
                            @else
                                <span class="text-muted">Guest User</span>
                            @endif
                        </td>
                        <td>
                            @if($log->hardware_id)
                                <a href="{{ route('device-shield.logs', ['hardware_id' => $log->hardware_id]) }}" class="text-decoration-none">
                                    <code>{{ substr($log->hardware_id, 0, 16) }}...</code>
                                </a>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td>
                            @if($log->login_status === 'success')
                                <span class="badge bg-success">Authenticated</span>
                            @elseif(str_starts_with($log->login_status, 'failed'))
                                <span class="badge bg-danger">{{ str_replace('failed_', '', $log->login_status) }}</span>
                            @elseif($log->login_status === 'pending_approval')
                                <span class="badge bg-warning">Approval Required</span>
                            @else
                                <span class="badge bg-secondary">{{ ucfirst($log->login_status) }}</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-label-secondary">{{ $log->ip_address ?? 'N/A' }}</span>
                        </td>
                        <td>
                            <span class="text-muted text-wrap d-block" style="max-width: 300px; font-size: 0.85rem;">{{ $log->remarks }}</span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-4 text-muted">No security audit logs found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
