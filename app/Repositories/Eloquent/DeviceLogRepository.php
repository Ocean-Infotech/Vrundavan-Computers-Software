<?php

namespace App\Repositories\Eloquent;

use App\Models\DeviceLog;
use App\Repositories\Contracts\DeviceLogRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class DeviceLogRepository implements DeviceLogRepositoryInterface
{
    public function log(array $data): DeviceLog
    {
        return DeviceLog::create([
            'user_id' => $data['user_id'] ?? null,
            'hardware_id' => $data['hardware_id'] ?? null,
            'ip_address' => $data['ip_address'] ?? null,
            'user_agent' => $data['user_agent'] ?? null,
            'login_status' => $data['login_status'] ?? 'unknown',
            'remarks' => $data['remarks'] ?? null,
        ]);
    }

    public function getLogs(int $limit = 100): Collection
    {
        return DeviceLog::with('user')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    public function getLogsByDevice(string $hardwareId, int $limit = 50): Collection
    {
        return DeviceLog::with('user')
            ->where('hardware_id', $hardwareId)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    public function getLogsByUser(int $userId, int $limit = 50): Collection
    {
        return DeviceLog::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }
}
