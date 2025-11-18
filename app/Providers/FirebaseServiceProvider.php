<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Kreait\Firebase\Factory;

class FirebaseServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(\Kreait\Firebase\Contract\Messaging::class, function ($app) {
            $factory = (new Factory)->withServiceAccount(config('services.firebase.credentials'));
            return $factory->createMessaging();
        });
    }
}
