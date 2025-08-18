<?php

namespace App\Http\Controllers\api;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\Master\MasterCity;
use App\Models\Master\MasterCountry;
use App\Models\Master\MasterPincode;
use App\Models\Master\MasterState;
use App\Models\RedemptionItem;
use App\Models\Setting;
use App\Models\User;
use App\Models\UserDetail;
use App\Models\WithdrawRequest;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public $loginUser = [];
    public function __construct() {}

    /**
     * @OA\Post(
     *      path="/get-profile",
     *      operationId="get-profile",
     *      tags={"Authorization API"},
     *      security={{"passport":{}}},
     *      summary="This API required the Bearer Token, tokenn string you can get on login response. https://prnt.sc/cx4OFlz3wYP0",
     *     @OA\Response(response=200, description="User profile fetched successfully", @OA\JsonContent()),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function getProfile(Request $request)
    {
        try {
            $loginUser = Auth::user();
            if (!$loginUser) {
                return $this->sendError("Unauthorization", [], [], 401);
            }
            // $userDetail = User::with(['roles'])->where('id', $loginUser?->id)->first();
            // // return $userDetail;
            // $responseData = [];
            // $responseData['id'] = $userDetail?->id.'';
            // $responseData['name'] = $userDetail?->name.'';
            // $responseData['email'] = $userDetail?->email.'';
            // $responseData['phone'] = $userDetail?->phone.'';
            // $responseData['status'] = $userDetail?->status.'';
            // $responseData['status'] = $userDetail?->status.'';
            // $responseData['referral_code'] = $userDetail?->referral_code.'';
            // $responseData['registered_at'] = $userDetail?->created_at.'';

            $newRequest = new Request();
            $newRequest['id'] = $loginUser?->id;

            $responseData = self::getProfileResponse($newRequest);

            // dd(59, $responseData);

            return $this->sendResponse($responseData, 'Get Profile user profile successfully.');
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), [], [], 201);
        }
        return $this->sendError("Something want to wrong in Get Profile API", [], [], 201);
    }

    public function getProfileResponse(Request $request)
    {
        try {

            $responseData = [];

            $userDetail = User::with(['roles'])->where('id', $request?->id)->first();
            if (!$userDetail && $request?->directReturn) {
                return (object)$responseData;
            }

            // $responseData['userDetail'] = $userDetail;
            // return $userDetail;
            // return $userDetail->toArray();
            $responseData['id'] = $userDetail?->id . '';
            $responseData['name'] = $userDetail?->name . '';
            $responseData['email'] = $userDetail?->email . '';
            $responseData['phone'] = $userDetail?->phone . '';
            $responseData['upi'] = $userDetail?->upi . '';
            $responseData['status'] = $userDetail?->status . '';
            $responseData['referral_code'] = $userDetail?->referral_code . '';
            $responseData['registered_at'] = $userDetail?->created_at . '';
            $responseData['user_id'] = "";
            $responseData['applied_referral_code'] = "";
            $responseData['gender'] = "";
            $responseData['date_of_birth'] = "";
            $responseData['country_id'] = "";
            $responseData['state_id'] = "";
            $responseData['city_id'] = "";
            $responseData['pincode'] = "";
            $responseData['country_name'] = "";
            $responseData['state_name'] = "";
            $responseData['city_name'] = "";

            $userDirectPermissions = $userDetail->getDirectPermissions();
            if (count($userDirectPermissions) == 0) {
                $userDirectPermissions = $userDetail->getPermissionsViaRoles();
            }
            $userDirectPermissions = $userDirectPermissions->map(function ($record) {
                $temp = [];
                // $temp = $record;

                $temp['group'] = $record?->group ?? "";
                $temp['name'] = $record?->name ?? "";
                return $temp;
            });
            $responseData['permissions'] = $userDirectPermissions;

            if ($userDetail?->user_detail) {
                $user_detail = $userDetail?->user_detail->toArray();
                if (gettype($user_detail) == "array") {
                    // $responseData = array_merge($responseData, $user_detail);
                    foreach ($user_detail as $key => $value) {
                        $responseData[$key] = $value . "";
                    }
                }
                // $responseData['user_detail'] = $userDetail?->user_has_detail;
            }
            return (object)$responseData;
        } catch (\Exception $e) {
            return (object)[];
        }
        return (object)[];
    }

}
