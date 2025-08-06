<?php

namespace App\Http\Controllers\api;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\Admission;
use App\Models\CourceRegistration;
use App\Models\Master\MasterCity;
use App\Models\Master\MasterCountry;
use App\Models\Master\MasterCourse;
use App\Models\Master\MasterPincode;
use App\Models\Master\MasterState;
use App\Models\SendOTP;
use App\Models\User;
use App\Models\UserDetail;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Passport\Token;
use Spatie\Permission\Models\Role;

class AuthenticationController extends Controller
{
    public function __construct(Request $request) {}

    public function register(Request $request)
    {
        DB::beginTransaction();
        try {
            // return implode(',', Role::whereNotIn('name', ['developer'])->get()->pluck('id')->toArray());
            $validator = Validator::make($request->all(), [
                'name' => ['required', 'string', 'max:255'], // Ensures name is a string and max 255 characters
                'user_type' => ['required', 'in:' . implode(',', Role::whereNotIn('name', ['developer'])->get()->pluck('id')->toArray())], // Ensure it's one of the expected values
                'phone' => ['required', 'digits:10', 'unique:' . ((new User())->getTable()) . ',phone'], // Must be exactly 10 digits and unique in users table
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
                'password' => ['required', 'string', 'min:4'], // Min 4 characters and requires password_confirmation field
                // , 'confirmed'
                'referral_code' => ['nullable', 'string', 'exists:' . (new User())->getTable() . ',referral_code'], // Optional, but if given, must be a string max 10 chars
            ], [
                'city_id.required' => 'The city field is required.',
                'state_id.required' => 'The state field is required.',
                'country_id.required' => 'The state field is required.'
            ]);


            if ($validator->fails()) {
                return $this->sendError('Validation Error.', $validator->errors()->first(), $validator->errors(), 403);
            }
            // return $request->all();

            $input = [];
            $input['name'] = $request->name;
            $input['email'] = ($request?->email) ? $request?->email : $request->phone . '@ocean.com';
            $input['phone'] = $request->phone;
            $input['password'] = Hash::make($request->password);
            $input['sp'] = Helper::generateString(4) . $request->password;
            $input['status'] = "active";
            // return $input;
            // $input['pincode'] = $request->pincode;
            $adminUser = User::create($input);

            $checkPincodeExist = MasterPincode::where('pincode', $request->pincode)->first();

            /** Add user Extra Detail */
            if ($adminUser) {
                $adminUserDetail = new UserDetail();
                $adminUserDetail->user_id = $adminUser?->id;
                $adminUserDetail->applied_referral_code = $request?->referral_code ?? null;
                $adminUserDetail->gender = $request?->gender ?? null;
                $adminUserDetail->date_of_birth = $request?->date_of_birth ?? null;
                $adminUserDetail->country_id = $request?->country_id;
                $adminUserDetail->state_id = $request?->state_id;
                $adminUserDetail->city_id = $request?->city_id;
                $adminUserDetail->pincode = $request?->pincode;
                $adminUserDetail->save();
            }

            // Assign role
            $role = Role::where('id', $request->user_type)->first();
            $adminUser->assignRole($role?->name);

            if ($request?->add_pincode && !$checkPincodeExist) {
                $newPincode = [];
                $newPincode['pincode'] = $request->pincode ?? null;
                $newPincode['city_id'] = $request->city_id ?? null;
                $newPincode['state_id'] = $request->state_id ?? null;
                $newPincode['country_id'] = $request->country_id ?? null;
                $newPincode['status'] = 'inactive';
                $newPincode['created_by'] = $adminUser?->id;
                MasterPincode::create($newPincode);
            }
            DB::commit();
            return $this->sendResponse([], 'User register successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendError($e->getMessage(), [], [], 201);
        }
    }

    public function studentSendOtp(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'mobile_no' => [
                    'required',
                    'digits_between:10,15',
                    function ($attribute, $value, $fail) {
                        if (!Admission::where('mobile_no', $value)->exists()) {
                            $fail('This phone number is not registered.');
                        }
                    }
                ],
                'course_id' => [
                    'required',
                    'integer',
                    function ($attribute, $value, $fail) use ($request) {
                        $adminUser = Admission::where('mobile_no', $request->mobile_no)->first();
                        if (!$adminUser) {
                            return;
                        }

                        $exists = CourceRegistration::where('register_id', $adminUser->id)
                            ->where('course_id', $value)
                            ->exists();

                        if (!$exists) {
                            $fail("You are not registered for the selected course.");
                        }
                    }
                ],
            ]);
            if ($validator->fails()) {
                return $this->sendError($validator->errors()->first(), $validator->errors(), [], 422);
            }
            // dd('hello');

            $adminUser = Admission::where('mobile_no', $request->mobile_no)->first();
            if (!$adminUser) {
                return $this->sendError('This student not found. pleasce check the admission mobile number.', [], [], 422);
            }

            $studenExitsInCourse = CourceRegistration::where('register_id', $adminUser?->id)->where('course_id', $request->course_id)->first();
            if (!$studenExitsInCourse) {
                return $this->sendError('Student not register in this course', [], [], 0);
            }

            $message = "OTP has been sent successfully.";

            resend_otp:
            $checkSendedOtp = SendOTP::where("model", Admission::class)->where("model_id", $adminUser?->id)->where('sms_type', 'otp')->whereNull('verified_at')->where('status', 'send')->orderBy('id', 'DESC')->first();

            $otp = Helper::generateNumericOTP();

            if (!$checkSendedOtp) {
                $insertArray = [];
                $insertArray['model'] = Admission::class;
                $insertArray['model_id'] = $adminUser?->id;
                $insertArray['send_to'] = $adminUser?->mobile_no;
                $insertArray['verified_content'] = $otp;
                $insertArray['expire_time'] = Carbon::now()->addMinutes(10);
                $insertArray['sms_type'] = 'otp';
                $insertArray['sms'] = 'Your One time OTP is ' . $otp;
                $insertArray['created_from'] = 'AuthenticationController => login';

                $checkSendedOtp = SendOTP::create($insertArray);
                // dd("L-185", $checkSendedOtp, $insertArray);
            }

            if ($checkSendedOtp?->expire_time) {
                // Assume $otpRecord is your OTP record (from DB or elsewhere)
                $expireTime = Carbon::parse($checkSendedOtp->expire_time);

                // Compare with current time
                if (Carbon::now()->greaterThan($expireTime)) {
                    $message = "OTP has expired. send new OTP pleasce check.";
                    $checkSendedOtp->status = 'expire';
                    $checkSendedOtp->save();
                    goto resend_otp;
                    // return response()->json(['message' => 'OTP has expired'], 400);
                }
            }
            $otp = $checkSendedOtp?->verified_content;

            // dd("L-203", $checkSendedOtp->toArray(), $otp);

            $responseData = [
                'admission_id'     => $adminUser->id,
                'mobile_no'   => $adminUser->mobile_no,
                'course_id'   => $request->course_id,
                'course_name' => $studenExitsInCourse?->course?->course_name,
                'otp'         => $otp,
            ];

            $message = $message . ' OTP: ' . $otp;
            return $this->sendResponse($responseData, $message);
        } catch (\Exception $e) {
            return $this->sendError('Something went wrong.', $e->getMessage(), [], 500);
        }
    }


    public function studentVerifyOtp(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'mobile_no' => [
                    'required',
                    'digits_between:10,15',
                    function ($attribute, $value, $fail) {
                        if (!Admission::where('mobile_no', $value)->exists()) {
                            $fail('This phone number is not registered.');
                        }
                    }
                ],
                'otp' => [
                    'required',
                    'digits:4',
                    function ($attribute, $value, $fail) use ($request) {
                        $checlOTP = SendOTP::where('send_to', $request?->mobile_no)->where('verified_content', $value)->where('sms_type', 'otp')->where('status', 'send')->first();
                        if (!$checlOTP) {
                            $fail("OTP not matched.");
                        } else {
                            if ($checlOTP?->verified_at) {
                                $fail("OTP used before. resend and try again.");
                            }
                            // Assume $otpRecord is your OTP record (from DB or elsewhere)
                            $expireTime = Carbon::parse($checlOTP?->expire_time);
                            if (Carbon::now()->greaterThan($expireTime)) {
                                // $fail("Your OTP is expire. resend and login.");
                            }
                            // dd("L-250", !$checlOTP?->verified_at, $checlOTP?->toArray());
                        }
                    }
                ],
            ]);

            if ($validator->fails()) {
                return $this->sendError($validator->errors()->first(), $validator->errors(), [], 422);
            }

            $adminUser = Admission::where('mobile_no', $request->mobile_no)->first();

            if (!$adminUser) {
                return $this->sendError('User not found.', ['mobile_no' => ['User not found.']], [], 404);
            }

            $checlOTP = SendOTP::where('send_to', $request?->mobile_no)->where('sms_type', 'otp')->whereNull('verified_at')->orderBy('id', 'DESC')->first();


            $checlOTP->verified_at = Carbon::now();
            $checlOTP->status = "expire";
            // $checlOTP->save();


            // Revoke all previous tokens for this user
            Token::where('user_id', $adminUser->id)
                // ->where('name', 'StudentToken') // important if using custom guard/model
                ->where('name', get_class($adminUser)) // important if using custom guard/model
                ->update(['revoked' => true]);

            // Generate Passport token
            $tokenResult = $adminUser->createToken(get_class($adminUser));
            $token = $tokenResult->accessToken;
            $expiresAt = $tokenResult->token->expires_at;

            $getProfileRequest = new Request();
            $getProfileRequest->admission_id = $adminUser->id;
            $getProfile = (new AuthenticatedController())->student_profile_response($getProfileRequest);
            $getProfile->token = $token;
            $getProfile->expiresAt = $expiresAt;

            $responseData = $getProfile;

            return $this->sendResponse($responseData, 'OTP verified successfully.');
        } catch (\Exception $e) {
            return $e;
            return $this->sendError('Something went wrong.', $e->getMessage(), [], 500);
        }
    }
}
