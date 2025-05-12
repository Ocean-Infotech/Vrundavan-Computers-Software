<?php

namespace App\Http\Controllers;

use App\Helpers\Helper;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\View;

/**
 * @OA\Info(
 *    title="RM QR Code Application API's",
 *    version="1.0.0",
 * )
 *  @OA\Server(
 *      url="http://127.0.0.1:8000/api/",
 *      description="Local API Server"
 * )
 *  @OA\Server(
 *      url="https://rm-qr-scanner.rmecommerce.in/api/",
 *      description="Live API Server"
 * )
 *  @OA\Tag(
 *     name="Unauthenticated",
 *     description="APIs that do not require authentication (e.g., Register, Login, Get Country List)"
 * )
 */
class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    public function __construct() {}

    public function sendResponse($result, $message, $extraData = [])
    {
        $response = [
            'status' => "true",
            'message' => $message,
            'data' => $result
        ];

        if (count($extraData) > 0) {
            $response['extraData'] = $extraData;
        }
        return response()->json($response, 200);
    }

    public function sendError($error, $errorMessages = [], $extraData = [], $code = 400)
    {
        $response = [
            'status' => "false",
            'message' => $error,
        ];

        if (!empty($errorMessages)) {
            $response['messages'] = $errorMessages;
        }

        if (count($extraData) > 0) {
            $response['extraData'] = $extraData;
        }
        return response()->json($response, $code);
    }
}
