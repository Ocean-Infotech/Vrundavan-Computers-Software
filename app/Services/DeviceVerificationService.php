<?php

namespace App\Services;

use App\Repositories\Contracts\DeviceBindingRepositoryInterface;
use App\Repositories\Contracts\DeviceLogRepositoryInterface;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;

class DeviceVerificationService
{
    public function __construct(
        protected DeviceBindingRepositoryInterface $bindingRepository,
        protected DeviceLogRepositoryInterface $logRepository
    ) {
    }

    public function verifyDevice(array $data, array $extraLogs = []): array
    {
        $userId = $data['user_id'] ?? null;
        $hardwareId = $data['hardware_id'] ?? null;
        $motherboardSerial = $data['motherboard_serial'] ?? '';
        $biosSerial = $data['bios_serial'] ?? '';
        $cpuId = $data['cpu_id'] ?? '';
        $timestamp = (int) ($data['timestamp'] ?? 0);
        $nonce = $data['nonce'] ?? '';
        $signature = $data['signature'] ?? '';
        $latitude = $data['latitude'] ?? null;
        $longitude = $data['longitude'] ?? null;

        $logData = array_merge([
            'user_id' => $userId,
            'hardware_id' => $hardwareId,
            'ip_address' => $extraLogs['ip_address'] ?? null,
            'latitude' => $latitude,
            'longitude' => $longitude,
            'user_agent' => $extraLogs['user_agent'] ?? null,
        ], $extraLogs);

        // 1. Basic Parameter Check
        if (empty($hardwareId) || empty($signature) || empty($nonce) || empty($timestamp)) {
            $this->logRepository->log(array_merge($logData, [
                'login_status' => 'failed_invalid_parameters',
                'remarks' => 'Missing security parameters.',
            ]));
            return ['success' => false, 'message' => 'Missing security parameters.'];
        }

        // 2. Timestamp Drift Check (Replay Attack Protection)
        $drift = Config::get('device_security.allowed_time_drift', 300);
        if (abs(time() - $timestamp) > $drift) {
            $this->logRepository->log(array_merge($logData, [
                'login_status' => 'failed_timestamp_drift',
                'remarks' => 'Timestamp drift exceeded.',
            ]));
            return ['success' => false, 'message' => 'Request timestamp invalid or expired. Check PC time sync.'];
        }

        // 3. Nonce Check (Replay Attack Protection)
        $cacheKey = 'device_nonce_' . $nonce;
        if (Cache::has($cacheKey)) {
            $this->logRepository->log(array_merge($logData, [
                'login_status' => 'failed_replay_attack',
                'remarks' => 'Nonce already used. Replay attack blocked.',
            ]));
            return ['success' => false, 'message' => 'Invalid or reused nonce. Replay attack detected.'];
        }
        Cache::put($cacheKey, true, Config::get('device_security.nonce_expiry', 3600));

        // 4. HMAC Signature Validation
        $secretKey = Config::get('device_security.secret_key');
        $payload = $hardwareId . $timestamp . $nonce;
        $expectedSignature = hash_hmac('sha256', $payload, $secretKey);

        if (!hash_equals($expectedSignature, $signature)) {
            $this->logRepository->log(array_merge($logData, [
                'login_status' => 'failed_invalid_signature',
                'remarks' => 'Signature mismatch.',
            ]));
            return ['success' => false, 'message' => 'Invalid device signature. Integrity check failed.'];
        }

        // 5. Hardware ID Hash Integrity Check
        $expectedHardwareId = hash('sha256', trim($motherboardSerial) . trim($biosSerial) . trim($cpuId));
        if (strtolower($expectedHardwareId) !== strtolower($hardwareId)) {
            $this->logRepository->log(array_merge($logData, [
                'login_status' => 'failed_hardware_mismatch',
                'remarks' => 'Hardware ID mismatch. Spoofing detected.',
            ]));
            return ['success' => false, 'message' => 'Device parameters mismatch. Hardware spoofing detected.'];
        }

        // 6. DB Lookup — Find by hardware_id alone (device-level approval, not user-specific)
        $binding = $this->bindingRepository->findByHardwareId($hardwareId);

        if (!$binding) {
            // First time this device is seen — register it as pending (store first user who triggered it)
            $newBinding = $this->bindingRepository->create([
                'user_id'            => $userId,
                'hardware_id'        => $hardwareId,
                'pc_name'            => $data['pc_name'] ?? 'Unknown PC',
                'bios_serial'        => $biosSerial,
                'motherboard_serial' => $motherboardSerial,
                'cpu_id'             => $cpuId,
                'raw_hardware_string'=> trim($motherboardSerial) . trim($biosSerial) . trim($cpuId),
                'status'             => 'pending',
                'latitude'           => $latitude,
                'longitude'          => $longitude,
            ]);

            $this->logRepository->log(array_merge($logData, [
                'login_status' => 'pending_approval',
                'remarks'      => 'New device detected. Registered for admin approval. Any user login blocked until approved.',
            ]));

            return [
                'success' => false,
                'message' => 'New Device Detected! Admin approval is required before any user can login from this device.',
                'binding' => $newBinding,
            ];
        }

        // 7. Status Check
        $statusMessages = [
            'pending' => 'Device Approval Required. Your authorization is still pending.',
            'rejected' => 'Device Registration Rejected. Please contact support.',
            'suspended' => 'Device Suspended. Access from this PC has been blocked.',
        ];

        if (isset($statusMessages[$binding->status])) {
            $this->logRepository->log(array_merge($logData, [
                'login_status' => 'failed_' . $binding->status,
                'remarks' => 'Attempted login from ' . $binding->status . ' device.',
            ]));
            return ['success' => false, 'message' => $statusMessages[$binding->status], 'binding' => $binding];
        }

        // 8. Approved — Grant Access
        if ($binding->status === 'approved') {
            // Update last known location
            $binding->latitude = $latitude;
            $binding->longitude = $longitude;
            $binding->save();

            $this->bindingRepository->recordLogin($binding->id);
            $this->logRepository->log(array_merge($logData, [
                'login_status' => 'success',
                'remarks' => 'Device authenticated successfully.',
            ]));
            return ['success' => true, 'message' => 'Device verification successful.', 'binding' => $binding];
        }

        return ['success' => false, 'message' => 'Unknown device status. Access denied.'];
    }
}
