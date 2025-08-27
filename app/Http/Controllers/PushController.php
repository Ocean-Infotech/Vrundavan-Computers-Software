<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Kreait\Firebase\Contract\Messaging;
use Kreait\Firebase\Exception\MessagingException;
use Kreait\Firebase\Exception\FirebaseException;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Kreait\Firebase\Messaging\AndroidConfig;

class PushController extends Controller
{
    public function sendStatic(Request $request, Messaging $messaging)
    {
        // 1) Put your Android device token here (or pass via request)
        $token = $request->input('token', 'PASTE_DEVICE_FCM_TOKEN_HERE');

        // 2) Basic title/body (can pass via request as well)
        $title = $request->input('title', 'Hello from Laravel');
        $body  = $request->input('body',  'This is a static test notification');

        // 3) Optional data payload (handled in onMessageReceived)
        $data = [
            'screen'   => 'Orders',
            'order_id' => '12345',
        ];

        // 4) Android config (channel_id must exist on Android 8+)
        $androidConfig = AndroidConfig::fromArray([
            'ttl' => '3600s',
            'priority' => 'high',
            'notification' => [
                'channel_id'   => 'default',
                'sound'        => 'default',
                'click_action' => 'OPEN_MAIN_ACTIVITY',
            ],
            'fcm_options' => [
                'analytics_label' => 'laravel_test',
            ],
        ]);

        // 5) Build and send
        $message = CloudMessage::withTarget('token', $token)
            ->withNotification(Notification::create($title, $body))
            ->withAndroidConfig($androidConfig)
            ->withData($data);

        try {
            $messaging->send($message);
            return response()->json(['ok' => true, 'sent_to' => $token]);
        } catch (MessagingException|FirebaseException $e) {
            return response()->json([
                'ok' => false,
                'error' => $e->getMessage(),
            ], 422);
        }
    }
}
