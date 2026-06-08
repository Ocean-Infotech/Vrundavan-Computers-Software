<?php

namespace App\Repositories\Contracts;

use App\Models\DeviceLog;
use Illuminate\Database\Eloquent\Collection;

interface DeviceLogRepositoryInterface
{
    public function log(array $data): DeviceLog;
    public function getLogs(int $limit = 100): Collection;
    public function getLogsByDevice(string $hardwareId, int $limit = 50): Collection;
    public function getLogsByUser(int $userId, int $limit = 50): Collection;
}
