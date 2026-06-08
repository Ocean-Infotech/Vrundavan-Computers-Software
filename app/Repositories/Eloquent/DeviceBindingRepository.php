<?php

namespace App\Repositories\Eloquent;

use App\Models\DeviceBinding;
use App\Repositories\Contracts\DeviceBindingRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;

class DeviceBindingRepository implements DeviceBindingRepositoryInterface
{
    public function findByHardwareId(string $hardwareId): ?DeviceBinding
    {
        return DeviceBinding::where('hardware_id', $hardwareId)->first();
    }

    public function findByUserAndHardware(int $userId, string $hardwareId): ?DeviceBinding
    {
        return DeviceBinding::where('user_id', $userId)
            ->where('hardware_id', $hardwareId)
            ->first();
    }

    public function getPendingRequests(): Collection
    {
        return DeviceBinding::with('user')
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getApprovedDevices(): Collection
    {
        return DeviceBinding::with(['user', 'approver'])
            ->where('status', 'approved')
            ->orderBy('approved_at', 'desc')
            ->get();
    }

    public function getAllDevices(): Collection
    {
        return DeviceBinding::with(['user', 'approver'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function countByStatus(string $status): int
    {
        return DeviceBinding::where('status', $status)->count();
    }

    public function create(array $data): DeviceBinding
    {
        return DeviceBinding::create($data);
    }

    public function updateStatus(int $id, string $status, ?int $approvedBy = null): bool
    {
        $device = DeviceBinding::find($id);
        if (!$device) {
            return false;
        }

        $device->status = $status;
        if ($status === 'approved') {
            $device->approved_by = $approvedBy;
            $device->approved_at = Carbon::now();
        }
        return $device->save();
    }

    public function recordLogin(int $id): bool
    {
        $device = DeviceBinding::find($id);
        if (!$device) {
            return false;
        }
        $device->last_login_at = Carbon::now();
        return $device->save();
    }

    public function forceRebind(int $id): bool
    {
        $device = DeviceBinding::find($id);
        if (!$device) {
            return false;
        }
        return $device->delete();
    }
}
