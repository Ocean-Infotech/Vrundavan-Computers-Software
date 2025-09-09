<?php

namespace App\Http;

use App\Http\Middleware\CheckRevokedToken;
use App\Http\Middleware\SoftwareAuthMiddleware;
use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    protected $routeMiddleware = [

        'software.auth' => SoftwareAuthMiddleware::class,
        'check.revoked' => CheckRevokedToken::class,
    ];
}
