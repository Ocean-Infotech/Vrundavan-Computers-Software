<?php

return [
    'secret_key' => (function () {
        $key = env('DEVICE_SECRET_KEY', env('APP_KEY', 'FallbackKey'));
        if (str_starts_with($key, 'base64:')) {
            return base64_decode(substr($key, 7));
        }
        return $key;
    })(),
    'allowed_time_drift' => (int) env('DEVICE_ALLOWED_TIME_DRIFT', 300),
    'nonce_expiry' => (int) env('DEVICE_NONCE_EXPIRY', 3600),
    'enabled' => (bool) env('DEVICE_SECURITY_ENABLED', true),
];
