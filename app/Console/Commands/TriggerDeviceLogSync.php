<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class TriggerDeviceLogSync extends Command
{
    protected $signature = 'attendance:trigger-sync';
    protected $description = 'Triggers AttendanceController to sync device logs';

    public function handle()
    {
        try {
            $response = Http::get(url('/attendance/sync-device-logs'));

            if ($response->successful()) {
                $this->info("Success: " . $response->body());
            } else {
                $this->error("Failed: " . $response->status());
            }
        } catch (\Exception $e) {
            $this->error("Error: " . $e->getMessage());
        }
    }
}
