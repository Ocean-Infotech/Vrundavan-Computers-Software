<?php

namespace App\Http\Controllers;

use App\Repositories\Contracts\DeviceBindingRepositoryInterface;
use App\Repositories\Contracts\DeviceLogRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DeviceBindingController extends Controller
{
    public function __construct(
        protected DeviceBindingRepositoryInterface $bindingRepository,
        protected DeviceLogRepositoryInterface $logRepository
    ) {
        $this->middleware(function ($request, $next) {
            $user = Auth::user();
            if (!$user || !(method_exists($user, 'hasRole') && ($user->hasRole('super-admin') || $user->hasRole('developer')))) {
                abort(403, 'Unauthorized action. Only the Main Admin can manage Device Shield.');
            }
            return $next($request);
        });
    }

    public function dashboard()
    {
        $stats = [
            'total' => array_sum(array_map(fn($s) => $this->bindingRepository->countByStatus($s), ['approved', 'pending', 'suspended', 'rejected'])),
            'approved' => $this->bindingRepository->countByStatus('approved'),
            'pending' => $this->bindingRepository->countByStatus('pending'),
            'suspended' => $this->bindingRepository->countByStatus('suspended'),
            'rejected' => $this->bindingRepository->countByStatus('rejected'),
        ];
        $pendingRequests = $this->bindingRepository->getPendingRequests();
        $recentLogs = $this->logRepository->getLogs(15);
        $devices = $this->bindingRepository->getAllDevices();

        return view('software.device-shield.dashboard', compact('stats', 'pendingRequests', 'recentLogs', 'devices'));
    }

    public function index(Request $request)
    {
        $status = $request->query('status');
        $devices = $this->bindingRepository->getAllDevices();
        if ($status) {
            $devices = $devices->where('status', $status);
        }

        $users = \App\Models\User::orderBy('name')->get();

        return view('software.device-shield.index', compact('devices', 'status', 'users'));
    }

    private function updateAndRespond(int $id, string $status, ?int $approvedBy = null, string $successMsg = 'Done.', string $errorMsg = 'Error.')
    {
        $success = $this->bindingRepository->updateStatus($id, $status, $approvedBy);
        if (request()->ajax() || request()->wantsJson()) {
            return response()->json(['success' => (bool) $success, 'message' => $success ? $successMsg : $errorMsg]);
        }
        return $success
            ? redirect()->back()->with('success', $successMsg)
            : redirect()->back()->with('error', $errorMsg);
    }

    public function approve(int $id)
    {
        return $this->updateAndRespond($id, 'approved', Auth::id(), 'Device approved successfully.', 'Unable to approve device.');
    }

    public function reject(int $id)
    {
        return $this->updateAndRespond($id, 'rejected', null, 'Device rejected.', 'Unable to reject device.');
    }

    public function suspend(int $id)
    {
        return $this->updateAndRespond($id, 'suspended', null, 'Device suspended.', 'Unable to suspend device.');
    }

    public function reactivate(int $id)
    {
        return $this->updateAndRespond($id, 'approved', Auth::id(), 'Device reactivated.', 'Unable to reactivate device.');
    }

    public function forceRebind(int $id)
    {
        $success = $this->bindingRepository->forceRebind($id);
        if (request()->ajax() || request()->wantsJson()) {
            return response()->json(['success' => (bool) $success, 'message' => $success ? 'Device unlinked. User must re-register.' : 'Unable to unlink device.']);
        }
        return $success
            ? redirect()->back()->with('success', 'Device unlinked. User must re-register on next login.')
            : redirect()->back()->with('error', 'Unable to unlink device.');
    }

    public function updateDeviceName(Request $request, int $id)
    {
        $request->validate(['device_name' => 'nullable|string|max:255']);
        $device = \App\Models\DeviceBinding::find($id);
        if (!$device) {
            return $request->ajax()
                ? response()->json(['success' => false, 'message' => 'Device not found.'], 404)
                : redirect()->back()->with('error', 'Device not found.');
        }

        $device->device_name = $request->input('device_name');
        $device->save();
        return $request->ajax()
            ? response()->json(['success' => true, 'message' => 'Device name updated.'])
            : redirect()->back()->with('success', 'Device name updated.');
    }

    public function logs(Request $request)
    {
        $hardwareId = $request->query('hardware_id');
        $userId = $request->query('user_id');
        $logs = $hardwareId
            ? $this->logRepository->getLogsByDevice($hardwareId, 100)
            : ($userId
                ? $this->logRepository->getLogsByUser((int) $userId, 100)
                : $this->logRepository->getLogs(100));

        return view('software.device-shield.logs', compact('logs', 'hardwareId', 'userId'));
    }

    public function downloadAgent(Request $request)
    {
        $user = \Illuminate\Support\Facades\Auth::user();
        if (!$user || (!$user->is_admin && !(method_exists($user, 'hasRole') && $user->hasRole('super-admin')))) {
            abort(403, 'Unauthorized action. Only administrators can download the security agent.');
        }

        $agentDir = base_path('agent');
        $zipFile = storage_path('app/shield-agent.zip');

        if (file_exists($zipFile)) {
            @unlink($zipFile);
        }

        $zip = new \ZipArchive();
        if ($zip->open($zipFile, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === true) {
            if (file_exists($agentDir . '/agent.php')) {
                $zip->addFile($agentDir . '/agent.php', 'agent.php');
            }

            $dynamicConfig = [
                'secret_key' => env('DEVICE_SECRET_KEY', env('APP_KEY')),
                'backend_url' => $request->getSchemeAndHttpHost(),
                'local_port' => 9988,
                'pc_name' => 'Workplace PC',
            ];
            $zip->addFromString('config.json', json_encode($dynamicConfig, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

            foreach (['agent.ps1', 'install_agent.bat', 'run_silent.vbs', 'run_debug.bat', 'stop_agent.bat', 'deploy.bat', 'install_agent_task.bat', 'agent.php', 'router.php'] as $file) {
                if (file_exists($agentDir . '/' . $file)) {
                    $zip->addFile($agentDir . '/' . $file, $file);
                }
            }

            $phpDir = $agentDir . '/php';
            if (is_dir($phpDir)) {
                $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($phpDir, \RecursiveDirectoryIterator::SKIP_DOTS));
                foreach ($files as $file) {
                    if (!$file->isDir()) {
                        $relativePath = 'php/' . str_replace('\\', '/', substr($file->getRealPath(), strlen($phpDir) + 1));
                        $zip->addFile($file->getRealPath(), $relativePath);
                    }
                }
            }

            $zip->close();
            return response()->download($zipFile, 'shield-agent.zip')->deleteFileAfterSend(true);
        }

        return abort(404, 'Agent files not found.');
    }

    public function toggleDeviceCheck(Request $request, int $id)
    {
        $user = \App\Models\User::find($id);
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'User not found.'], 404);
        }

        $user->check_device = !$user->check_device;
        $user->save();

        $statusStr = $user->check_device ? 'enabled' : 'disabled';
        return response()->json([
            'success' => true,
            'message' => "Device verification has been {$statusStr} for user {$user->name}.",
            'check_device' => (bool) $user->check_device
        ]);
    }
}
