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
            // $responseData['user_type'] = $userDetail?->roles->first()?->name;

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
            $responseData['user_type'] = $userDetail?->roles->first()?->id . "";
            $responseData['user_type_name'] = $userDetail?->roles->first()?->name;
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

    /**
     * Update Profile API
     * @OA\Post(
     *     path="/update-profile",
     *     operationId="update-profile",
     *     tags={"Authorization API"},
     *     security={{"passport":{}}},
     *     summary="Login in user update profile",
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 type="object",
     *                 required={"name", "phone", "pincode", "country_id", "state_id", "city_id"},
     *                 @OA\Property(property="name", type="string",example="name" ),
     *                 @OA\Property(property="phone", type="string",example="phone"),
     *                 @OA\Property(property="email", type="string",example="email@gmail.com"),
     *                 @OA\Property(property="pincode", type="numeric",example="pincode"),
     *                 @OA\Property(property="add_pincode", type="boolean",example="true", description="If Pincode not found pass this variable"),
     *                 @OA\Property(property="country_id", type="text", example="101", description="Country Id, 101 = India"),
     *                 @OA\Property(property="state_id", type="text", example="4030", description="State Id, 4030 = Gujarat"),
     *                 @OA\Property(property="city_id", type="text", example="133679", description="State Id, 133679 = Rajkot"),
     *                 @OA\Property(property="geneder", type="text", example="male", description="male, female"),
     *                 @OA\Property(property="date_of_birth", type="text", example="19-06-1997", description="d-m-Y format"),
     *             ),
     *         ),
     *     ),
     *     @OA\Response(
     *         response="201",
     *         description="Your profile update successfully.",
     *         @OA\JsonContent()
     *     ),
     * )
     */
    public function updateProfile(Request $request)
    {
        try {
            $loginUser = Auth::user();
            if (!$loginUser) {
                return $this->sendError("Unauthorization", [], [], 401);
            }
            // return $request->all();
            $validator = Validator::make($request->all(), [
                'name' => [
                    'required',
                    'string',
                    'max:255'
                ], // Ensures name is a string and max 255 characters
                'upi' => ['required', 'string', 'max:255'], // Ensures name is a string and max 255 characters
                // 'user_type' => ['required', 'in:' . implode(',', Role::whereNotIn('name', ['developer'])->get()->pluck('id')->toArray())], // Ensure it's one of the expected values
                'email' => ['required', 'email', 'unique:' . ((new User())->getTable()) . ',email,' . $loginUser?->id], // Must be exactly 10 digits and unique in users table
                'phone' => ['required', 'digits:10', 'unique:' . ((new User())->getTable()) . ',phone,' . $loginUser?->id], // Must be exactly 10 digits and unique in users table
                'pincode' => ['required', 'digits:6'], // Ensures pincode is exactly 6 digits
                'add_pincode' => ['nullable', 'in:true,false'],

                // Make these fields required only if add_pincode is true
                'country_id' => [
                    'required:add_pincode,true',
                    'exists:' . ((new MasterCountry())->getTable()) . ',id'
                ], // Checks if country_id exists in states table
                'state_id' => [
                    'required:add_pincode,true',
                    'exists:' . ((new MasterState())->getTable()) . ',id'
                ], // Checks if state_id exists in states table
                'city_id' => [
                    'required:add_pincode,true',
                    'exists:' . ((new MasterCity())->getTable()) . ',id'
                ], // Checks if city_id exists in cities table
                'geneder' => ['required', 'in:male,female'],
                'date_of_birth' => ['required', 'date_format:d-m-Y'],
            ], [
                'city_id.required' => 'The city field is required.',
                'state_id.required' => 'The state field is required.',
                'country_id.required' => 'The state field is required.',
                'date_of_birth.date_format' => 'The date of birth must be in the format DD-MM-YYYY.'
            ]);

            if ($validator->fails()) {
                return $this->sendError('Validation Error.', $validator->errors()->first(), $validator->errors(), 403);
            }

            // return $validator->errors();
            $updateUser = User::where('id', $loginUser?->id)->first();
            if (!$updateUser) {
                return $this->sendResponse('Login user not found in upodate profile.', [], [], 400);
            }

            /** Update Detail */
            if ($request?->phone) {
                $updateUser->phone = $request?->phone ?? $updateUser?->phone;
            }
            $updateUser->name = $request?->name ?? $updateUser?->name;
            $updateUser->email = $request?->email ?? $updateUser?->email;
            $updateUser->upi = $request?->upi ?? $request?->upi;
            $updateUser->updated_by = $loginUser?->id;
            $updateUser->save();

            // $updateUser->user_detail = $loginUser?->user_detail;
            $userDetail = UserDetail::where('user_id', $updateUser?->id)->first();
            if (!$userDetail) {
                $userDetail->user_id = $updateUser?->id;
            }

            $userDetail->gender = $request?->geneder ?? $userDetail?->gender;
            $userDetail->date_of_birth =  $request?->date_of_birth ? Helper::convert_date($request?->date_of_birth, "d-m-Y", "Y/m/d") : $userDetail?->date_of_birth;
            $userDetail->country_id = $request?->country_id ?? $userDetail?->country_id;
            $userDetail->state_id = $request?->state_id ?? $userDetail?->state_id;
            $userDetail->city_id = $request?->city_id ?? $userDetail?->city_id;
            $userDetail->pincode = $request?->pincode ?? $userDetail?->pincode;

            // return $userDetail;
            $getProfileRequest = new Request();
            $getProfileRequest['id'] = $loginUser?->id;

            $responseData = self::getProfileResponse($getProfileRequest);

            return $this->sendResponse($responseData, 'Your profile update successfully.');
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), [], [], 201);
        }
        return $this->sendError("Something want to wrong in Get Profile API", [], [], 201);
    }


    /**
     * @OA\Post(
     *     path="/withdraw-request",
     *     operationId="withdrawRequest",
     *     tags={"Authorization API"},
     *     summary="Submit a withdraw request",
     *     description="Allows an authenticated user to submit a withdraw request.",
     *     security={{"passport":{}}},
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 type="object",
     *                 required={
     *                     "user_id",
     *                     "request_points",
     *                     "request_amount",
     *                     "withdraw_request_status",
     *                     "withdraw_request_date"
     *                 },
     *                 @OA\Property(
     *                     property="user_id",
     *                     type="integer",
     *                     example=1234567890,
     *                     description="User ID (must be exactly 10 digits)"
     *                 ),
     *                 @OA\Property(
     *                     property="request_points",
     *                     type="integer",
     *                     example=1000,
     *                     description="Requested points"
     *                 ),
     *                 @OA\Property(
     *                     property="request_amount",
     *                     type="integer",
     *                     example=500,
     *                     description="Requested amount"
     *                 ),
     *                 @OA\Property(
     *                     property="withdraw_request_status",
     *                     type="string",
     *                     enum={"Pending"},
     *                     example="Pending",
     *                     description="Status must be 'Pending'"
     *                 ),
     *                 @OA\Property(
     *                     property="withdraw_request_date",
     *                     type="string",
     *                     format="date",
     *                     example="30-04-2025",
     *                     description="Date in 'd-m-Y' format"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Withdraw request submitted successfully.",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object"),
     *             @OA\Property(property="message", type="string", example="Your Withdraw Request Add Successfully.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Validation error"
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="General error"
     *     )
     * )
     */
    public function withdrawRequest(Request $request)
    {
        try {
            $loginUser = Auth::user();
            if (!$loginUser) {
                return $this->sendError("Unauthorization", [], [], 401);
            }

            // amount check for min and max validation
            $setting = Setting::where('key', 'min-withdraw-request-amount')->where('status', 'active')->first();
            if (!$setting) {
                return $this->sendError('Request Amount Missmatch, contact to software.', [], [], 403);
            }
            $min = 0;
            $max = 0;

            if ($setting) {
                $min = $setting?->value?->min_amount ? (int)$setting?->value?->min_amount : 0;
                $max = $setting?->value?->max_amount ? $setting?->value?->max_amount : 0;
            }

            // calculation amount to point {"point":"1","rupees":"10"}
            $conversionSetting = Setting::where('key', 'point-to-rupees')->where('status', 'active')->first();

            if (!$conversionSetting) {
                return $this->sendError('Request Amount point missmatch, contact to software.', [], [], 403);
            }

            $validator = Validator::make($request->all(), [
                'request_amount' => ['required', 'numeric', "min:$min", "max:$max"],
            ]);


            if ($validator->fails()) {
                return $this->sendError('Validation Error.', $validator->errors()->first(), $validator->errors(), 403);
            }

            $requestData = $request->all();
            $requestData['withdraw_request_date'] = Carbon::today()->toDateString();
            $requestData['user_id'] = $loginUser->id;
            $requestData['created_by'] = $loginUser->id;

            // Calculate the points based on the amount requested
            $requestedAmount = (int)$request->request_amount;
            $points = 0;

            if ($conversionSetting) {
                if (isset($conversionSetting->value) && is_numeric($conversionSetting?->value?->point)) {
                    $points = $requestedAmount * (int)$conversionSetting->value?->point;
                }
            }

            // redemption_items
            $totalCollectedPoints = RedemptionItem::where('scanned_user_id', $loginUser->id)
                ->where('status', 'active')
                ->sum('collect_points');

            $withdrawRequestPoint = withdrawRequest::where('user_id', $loginUser->id)->sum('request_points');

            // Add the points to the request data
            $requestData['request_points'] = $points;
            $requestData['request_amount'] = $requestedAmount;
            // return $requestData;
            $responseData = WithdrawRequest::create($requestData);

            return $this->sendResponse($responseData, 'Your Withdraw Request Add Successfully.');
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), [], [], 201);
        }
        return $this->sendError("Something want to wrong in Get Profile API", [], [], 201);
    }


    public function PointCheckWithdrawRequestAmount(Request $request)
    {
        try {
            $loginUser = Auth::user();
            if (!$loginUser) {
                return $this->sendError("Unauthorization", [], [], 401);
            }

            $validator = Validator::make($request->all(), [
                // 'request_points' => ['required'],
                'request_amount' => ['required', 'numeric'],
            ]);

            if ($validator->fails()) {
                return $this->sendError('Validation Error.', $validator->errors()->first(), $validator->errors(), 403);
            }


            // redemption_items
            $totalCollectedPoints = RedemptionItem::where('scanned_user_id', $loginUser->id)
                ->where('status', 'active')
                ->sum('collect_points');

            $responseData = [];
            // $responseData = $request?->all();
            $responseData['id'] = $loginUser?->id . "";
            $responseData['maximumn_request_amount'] = "";
            if ($totalCollectedPoints) {
                $responseData['maximumn_request_amount'] = Helper::calculationPoint2Rupees($totalCollectedPoints);
            }
            $responseData['request_point_amount'] = "";
            $responseData['user_request_points'] = "";
            $responseData['user_total_collected_points'] = $totalCollectedPoints . "";

            /** Using Point calculate */
            if ($request?->request_points) {
                $responseData['user_request_points'] = $request->request_points;
                $responseData['request_point_amount'] = Helper::calculationPoint2Rupees($request->request_points);
                if ((int)$request?->request_points >= (int)$totalCollectedPoints) {
                    return $this->sendError("Insufficient points. You have only $totalCollectedPoints points.", [], [], 403);
                }
            }

            if ($request?->request_amount && (int)$request?->request_amount >= $responseData['maximumn_request_amount']) {
                $responseData['request_point_amount'] = Helper::calculationPoint2Rupees($request->request_amount);
                return $this->sendError("Insufficient points. You have maximum " . $responseData['maximumn_request_amount'] . " Rs.", [], [], 403);
            }

            foreach ($responseData as $key => $value) {
                $responseData[$key] = $value . "";
            }
            return $this->sendResponse($responseData, 'Get User Collected Points Withdraw Request successfully.');
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), [], [], 201);
        }
        return $this->sendError("Something want to wrong in Get User Collected Points Withdraw Request API", [], [], 201);
    }

    public function withdrawHistory(Request $request)
    {
        try {

            $loginUser = Auth::user();
            if (!$loginUser) {
                return $this->sendError("Unauthorization", [], [], 401);
            }

            $withdrawHistory = WithdrawRequest::query();
            $withdrawHistory = $withdrawHistory->select(['user_id', 'request_points', 'request_amount', 'withdraw_request_date', 'image', 'reason', 'withdraw_request_status']);
            $withdrawHistory = $withdrawHistory->where('user_id', $loginUser?->id);

            if($request?->from_date && $request?->to_date){
                $withdrawHistory = $withdrawHistory->whereDate('withdraw_request_date', '>=', $request?->from_date);
                $withdrawHistory = $withdrawHistory->whereDate('withdraw_request_date', '<=', $request?->to_date);
            } else if($request?->from_date){
                $withdrawHistory = $withdrawHistory->whereDate('withdraw_request_date', $request?->from_date);
            }
            else if($request?->to_date){
                $withdrawHistory = $withdrawHistory->whereDate('withdraw_request_date', $request?->from_date);
            }


            $withdrawHistory = $withdrawHistory->orderBy('withdraw_request_date', 'DESC');
            $withdrawHistory = $withdrawHistory->get();
            // $withdrawHistory = $withdrawHistory->toSql();

            return $this->sendResponse($withdrawHistory, 'Your Withdraw history.');


        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), [], [], 201);
        }
        return $this->sendError("Something want to wrong in withdraw history api", [], [], 201);
    }
}
