<?php

namespace App\Http\Controllers\api;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\Master\MasterCity;
use App\Models\Master\MasterCountry;
use App\Models\Master\MasterPincode;
use App\Models\Master\MasterState;
use App\Models\User;
use App\Models\UserDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Role;

class AuthenticationController extends Controller
{
    public function __construct(Request $request) {}

    /**
     * Register API
     * @OA\Post(
     *     path="/register",
     *     operationId="register",
     *     tags={"Authentication API"},
     *     description="User register Endpoint",
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 type="object",
     *                 required={"name", "phone", "user_type", "password", "pincode", "country_id", "state_id", "city_id"},
     *                 @OA\Property(property="user_type", type="string",example="1"),
     *                 @OA\Property(property="name", type="string",example="name" ),
     *                 @OA\Property(property="phone", type="string",example="phone"),
     *                 @OA\Property(property="password", type="password",example="password"),
     *                 @OA\Property(property="pincode", type="numeric",example="pincode"),
     *                 @OA\Property(property="add_pincode", type="boolean",example="true", description="If Pincode not found pass this variable"),
     *                 @OA\Property(property="country_id", type="text", example="101", description="Country Id, 101 = India"),
     *                 @OA\Property(property="state_id", type="text", example="4030", description="State Id, 4030 = Gujarat"),
     *                 @OA\Property(property="city_id", type="text", example="133679", description="State Id, 133679 = Rajkot"),
     *                 @OA\Property(property="referral_code", type="text",example="referral_code if exis", description="Invitor Referral Id"),
     *             ),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User register Successfully",
     *         @OA\JsonContent()
     *     ),
     * )
     */
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
            $user = User::create($input);

            $checkPincodeExist = MasterPincode::where('pincode', $request->pincode)->first();

            /** Add user Extra Detail */
            if ($user) {
                $userDetail = new UserDetail();
                $userDetail->user_id = $user?->id;
                $userDetail->applied_referral_code = $request?->referral_code ?? null;
                $userDetail->gender = $request?->gender ?? null;
                $userDetail->date_of_birth = $request?->date_of_birth ?? null;
                $userDetail->country_id = $request?->country_id;
                $userDetail->state_id = $request?->state_id;
                $userDetail->city_id = $request?->city_id;
                $userDetail->pincode = $request?->pincode;
                $userDetail->save();
            }

            // Assign role
            $role = Role::where('id', $request->user_type)->first();
            $user->assignRole($role?->name);

            if ($request?->add_pincode && !$checkPincodeExist) {
                $newPincode = [];
                $newPincode['pincode'] = $request->pincode ?? null;
                $newPincode['city_id'] = $request->city_id ?? null;
                $newPincode['state_id'] = $request->state_id ?? null;
                $newPincode['country_id'] = $request->country_id ?? null;
                $newPincode['status'] = 'inactive';
                $newPincode['created_by'] = $user?->id;
                MasterPincode::create($newPincode);
            }
            DB::commit();
            return $this->sendResponse([], 'User register successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendError($e->getMessage(), [], [], 201);
        }
    }

    /**
     * Login API
     * @OA\Post(
     *     path="/login",
     *     operationId="login",
     *     tags={"Authentication API"},
     *     summary="Login a user",
     *     description="User Login Endpoint",
     *     @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 type="object",
     *                 required={"username","password"},
     *                 @OA\Property(property="username",type="text",example="1234567890" ),
     *                 @OA\Property(property="password",type="password",example="123456"),
     *             ),
     *         ),
     *     ),
     *     @OA\Response(
     *         response="201",
     *         description="User Login Successfully",
     *         @OA\JsonContent()
     *     ),
     * )
     */
    public function login(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'username' => [
                    'required',
                    /*
                    function ($attribute, $value, $fail) {
                    if (!filter_var($value, FILTER_VALIDATE_EMAIL) && !preg_match('/^\+?[0-9]{10,15}$/', $value)) {
                    $fail('The username must be a valid email or phone number.');
                    }
                    },
                    */
                    function ($attribute, $value, $fail) {
                        // Check if the username is a valid email
                        $isEmail = filter_var($value, FILTER_VALIDATE_EMAIL);

                        // Check if the username is a valid phone number
                        $isPhone = preg_match('/^\+?[0-9]{10,15}$/', $value);

                        if (!$isEmail && !$isPhone) {
                            $fail('The username must be a valid email or phone number.');
                            return;
                        }

                        // Check if it exists in the database (either email or phone field)
                        $userExists = User::where('email', $value)->orWhere('phone', $value)->exists();

                        if (!$userExists) {
                            $fail('The username does not exist in our records.');
                        }
                    },
                ],
                'password' => 'required',
            ]);


            if ($validator->fails()) {
                return $this->sendError('Validation Error.', $validator->errors()->first(), $validator->errors(), 403);
            }

            $field = filter_var($request->get('username'), FILTER_VALIDATE_EMAIL)
                ? 'email'
                : 'phone';

            // return [Auth::attempt([$field => $request?->username, 'password' => $request->password]).""];
            if (Auth::attempt([$field => $request?->username, 'password' => $request->password])) {

                $user = Auth::user();

                $success =  $user;

                $newRequest = new Request();
                $newRequest['id'] = $user?->id;
                $success = (array)(new UserController())->getProfileResponse($newRequest);

                $success['token'] =  $user->createToken(env("APP_NAME"))->accessToken;

                return $this->sendResponse($success, 'User login successfully.');
            } else {
                return $this->sendError('login details missmatch. enter correct login details. please try again', ['error' => 'Unauthorised']);
            }
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), [], [], 201);
        }
        return $request->all();
    }
}
