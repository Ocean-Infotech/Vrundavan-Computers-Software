<?php

namespace App\Http\Middleware;

use App\Repositories\Contracts\DeviceBindingRepositoryInterface;
use App\Services\DeviceVerificationService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckAuthorizedDevice
{
    public function __construct(
        protected DeviceBindingRepositoryInterface $bindingRepository,
        protected DeviceVerificationService $verificationService
    ) {
    }

    public function handle(Request $request, Closure $next): Response
    {
        if (!config('device_security.enabled', true)) {
            return $next($request);
        }

        if (!Auth::check()) {
            return $next($request);
        }

        $user = Auth::user();

        // Bypass checking if user is an admin, super-admin, or check_device is not active for them
        if ($user->is_admin || (method_exists($user, 'hasRole') && $user->hasRole('super-admin')) || !$user->check_device) {
            return $next($request);
        }

        if ($request->hasHeader('X-Device-Signature')) {
            $verification = $this->verificationService->verifyDevice([
                'user_id' => $user->id,
                'hardware_id' => $request->header('X-Device-Hardware-Id'),
                'motherboard_serial' => $request->header('X-Device-Motherboard-Serial'),
                'bios_serial' => $request->header('X-Device-Bios-Serial'),
                'cpu_id' => $request->header('X-Device-Cpu-Id'),
                'timestamp' => $request->header('X-Device-Timestamp'),
                'nonce' => $request->header('X-Device-Nonce'),
                'signature' => $request->header('X-Device-Signature'),
                'pc_name' => $request->header('X-Device-PC-Name', 'API Client'),
                'latitude' => $request->header('X-Device-Latitude'),
                'longitude' => $request->header('X-Device-Longitude'),
            ], [
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            if (!$verification['success']) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                if ($request->expectsJson()) {
                    return response()->json([
                        'error' => 'unauthorized_device',
                        'message' => $verification['message'],
                    ], 403);
                }
                return redirect()->route('software.login')->withErrors(['device' => $verification['message']]);
            }
            return $next($request);
        }

        // Web Session Mode
        $authorizedHardwareId = session('authorized_device_id');

        if (!$authorizedHardwareId) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'unauthorized_device',
                    'message' => 'Browser session not bound to a verified hardware agent.',
                ], 403);
            }
            return redirect()->route('software.login')->withErrors([
                'device' => 'No authorized hardware agent detected for this browser session.',
            ]);
        }

        $binding = $this->bindingRepository->findByHardwareId($authorizedHardwareId);

        if (!$binding || $binding->status !== 'approved') {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            $statusMessage = 'Device is no longer authorized.';
            if ($binding) {
                $messages = [
                    'pending' => 'Device is pending admin approval.',
                    'suspended' => 'Device has been suspended by an administrator.',
                    'rejected' => 'Device authorization was rejected.',
                ];
                $statusMessage = $messages[$binding->status] ?? $statusMessage;
            }

            if ($request->expectsJson()) {
                return response()->json(['error' => 'unauthorized_device', 'message' => $statusMessage], 403);
            }
            return redirect()->route('software.login')->withErrors(['device' => $statusMessage]);
        }

        return $next($request);
    }
}
