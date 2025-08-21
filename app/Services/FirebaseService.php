<?php

namespace App\Services;

use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Exception\MessagingException;
use Kreait\Firebase\Messaging\Notification;
use Illuminate\Support\Facades\Log;

class FirebaseService
{
    protected $messaging;

    public function __construct()
    {
        $factory = (new Factory)
            ->withServiceAccount(storage_path('firebase/vrundavan-project-firebase-adminsdk-fbsvc-3d0e6a4ebb.json'));

        $this->messaging = $factory->createMessaging(); // Corrected
    }

    public function sendNotification($tokens, $title, $body)
    {
        $notification = Notification::create($title, $body);
        $message = CloudMessage::new()->withNotification($notification);

        $results = [];

        foreach ($tokens as $token) {
            try {
                $response = $this->messaging->send($message->withChangedTarget('token', $token));
                $results[] = [
                    'token' => $token,
                    'status'=> 'success',
                    'response'=> $response,
                ];
            } catch (MessagingException $e) {
                Log::error('Firebase Messaging Error: ' . $e->getMessage(), ['token'=>$token]);
                $results[] = [
                    'token' => $token,
                    'status'=> 'failed',
                    'error' => $e->getMessage(),
                ];
            }
        }

        return $results;
    }
}
