<?php

namespace App\Repositories\Contracts;

use App\Models\DeviceBinding;
use Illuminate\Database\Eloquent\Collection;

interface DeviceBindingRepositoryInterface
{
    public function findByHardwareId(string $hardwareId): ?DeviceBinding;
    public function findByUserAndHardware(int $userId, string $hardwareId): ?DeviceBinding;
    public function getPendingRequests(): Collection;
    public function getApprovedDevices(): Collection;
    public function getAllDevices(): Collection;
    public function countByStatus(string $status): int;
    public function create(array $data): DeviceBinding;
    public function updateStatus(int $id, string $status, ?int $approvedBy = null): bool;
    public function recordLogin(int $id): bool;
    public function forceRebind(int $id): bool;
}
