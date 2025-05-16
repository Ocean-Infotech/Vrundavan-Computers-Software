<?php

namespace App\Http\Controllers\api;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\Documents;
use App\Models\Admission;
use App\Models\Master\MasterCity;
use App\Models\Master\MasterCountry;
use App\Models\Master\MasterCourse;
use App\Models\Master\MasterBatch;
use App\Models\Master\MasterPincode;
use App\Models\Master\MasterState;
use App\Models\RedemptionItem;
use App\Models\Setting;
use App\Models\Sliders;
use App\Models\WithdrawRequest;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Role;
use Illuminate\Validation\Rule;

class CommonController extends Controller
{
    public function __construct(Request $request) {}



    public function get_admission(Request $request)
    {
        try {
            $data = Admission::where('status', 'completed')->orderBy('id', 'DESC')->get();

            $data = $data->map(function ($row) {
                // $temp = $row;
                $temp['id'] = $row?->id . "";
                $temp['name'] = $row?->first_name . " ".$row?->father_name. " ".$row?->last_name. "";
                return $temp;
            });
            return $this->sendResponse($data, "Course list.");
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage());
        }
        return $this->sendError("Something want to wrong in register API");
    }

    public function get_course(Request $request)
    {
        try {
            $data = MasterCourse::where('status', 'active')->orderBy('course_name', 'ASC')->get();

            $data = $data->map(function ($row) {
                // $temp = $row;
                $temp['id'] = $row?->id . "";
                $temp['name'] = $row?->course_name . "";
                $temp['fees'] = $row?->course_fees . "";
                return $temp;
            });
            return $this->sendResponse($data, "Course list.");
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage());
        }
        return $this->sendError("Something want to wrong in register API");
    }


    public function get_batch(Request $request)
    {
        try {
            $validator =  Validator::make($request->all(), [
                'course_id' => [
                    'required',
                    Rule::exists((new MasterCourse())->getTable(), 'id')
                ]
            ]);
            if ($validator->fails()) {
                return $this->sendError($validator->messages()->first(), $validator->messages(), [], 401);
            }

            if (!$request?->course_id) {
                return $this->sendError("Course id is required.");
            }
            $data = MasterBatch::where('course_id', $request?->course_id,)->where('status', 'active')->get();
            $data = $data->map(function ($row) {
                // $temp = $row;
                $temp['id'] = $row?->id . "";
                $temp['name'] = $row?->batch_name . "";
                $temp['course_id'] = $row?->course_id . "";
                $temp['course_name'] = $row?->course?->course_name . "";
                return $temp;
            });
            return $this->sendResponse($data, "Batch list.");
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage());
        }
        return $this->sendError("Something want to wrong in register API");
    }
    /**
     * @OA\Post(
     *     path="/user_type",
     *     operationId="user_type",
     *     tags={"Open Source"},
     *     description="User Type Endpoint",
     *     @OA\Response(response=200, description="get user type Successfully"),
     * )
     */
    public function user_type(Request $request)
    {
        try {
            /*
            $roels = Role::query();
            // $roels = $roels->select('id','name');
            $roels = $roels->whereNotIn('name', ['developer', 'super-admin']);
            $roels = $roels->get();
            */

            $roels = Helper::getApplicationUserRoles();
            $roels = $roels->map(function ($role) {
                // $team = $role;
                $team['id'] = $role?->id . '';
                $team['name'] = $role?->name . '';
                return $team;
            });

            return $this->sendResponse($roels, 'User type.');
        } catch (\Exception $e) {
            return $e->getMessage();
            return $this->sendError($e->getMessage(), [], [], 201);
        }
    }


    /**
     * @OA\Post(
     *     path="/get-country",
     *     operationId="get_country",
     *     tags={"Open Source"},
     *     @OA\Response(response=200, description="get country Successfully"),
     * )
     */
    public function get_country(Request $request)
    {
        try {
            $data = MasterCountry::select('id', 'name', 'short_name', 'code')->get();
            return $this->sendResponse($data, "Country list.");
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage());
        }
        return $this->sendError("Something want to wrong in register API");
    }


    /**
     * @OA\Post(
     *      path="/get-state",
     *      operationId="get_state",
     *     tags={"Open Source"},
     *      @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="multipart/form-data",
     *              @OA\Schema(
     *                       type="object",
     *                  required={"country_id"},
     *                  @OA\Property(property="country_id", type="string", example="101"),
     *             ),
     *         ),
     *      ),
     *     @OA\Response(response=200, description="get state Successfully"),
     * )
     */
    public function get_state(Request $request)
    {
        try {
            if (!$request?->country_id) {
                return $this->sendError("Country id is required.");
            }
            $data = MasterState::select('id', 'country_id', 'name', 'short_name', 'gst_code')->where('country_id', $request?->country_id)->get();
            return $this->sendResponse($data, "State list.");
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage());
        }
        return $this->sendError("Something want to wrong in register API");
    }

    /**
     * @OA\Post(
     *     path="/get-cities",
     *     operationId="get_cities",
     *     tags={"Open Source"},
     *      @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="multipart/form-data",
     *              @OA\Schema(
     *                  type="object",
     *                  required={"country_id", "state_id"},
     *                  @OA\Property(property="country_id", type="string", example="101"),
     *                  @OA\Property(property="state_id", type="string", example="4030"),
     *             ),
     *         ),
     *      ),
     *     @OA\Response( response=200, description="get cities Successfully"),
     * )
     */
    public function get_cities(Request $request)
    {
        try {
            if (!$request?->country_id) {
                return $this->sendError("Country id is required.");
            }
            if (!$request?->state_id) {
                return $this->sendError("State id is required.");
            }
            $data = MasterCity::select('id', 'country_id', 'state_id', 'name', 'short_name');
            $data = $data->where('country_id', $request?->country_id);
            if ($request?->state_id) {
                $data = $data->where('state_id', $request?->state_id);
            }
            $data = $data->orderBy('name', 'asc');
            $data = $data->get();
            return $this->sendResponse($data, "City list.");
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage());
        }
        return $this->sendError("Something want to wrong in register API");
    }

    /**
     * @OA\Post(
     *      path="/check-pincode",
     *      operationId="check_pincode",
     *      tags={"Open Source"},
     *      @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="multipart/form-data",
     *              @OA\Schema(
     *                  type="object",
     *                  required={"pincode"},
     *                  @OA\Property(property="pincode", type="string", example="101"),
     *             ),
     *         ),
     *      ),
     *      @OA\Response(response=200, description="This Pincode exist."),
     * )
     */
    public function check_pincode(Request $request)
    {
        try {
            if (!$request?->pincode) {
                return $this->sendError("pincode is required.");
            }

            $data = MasterPincode::select('id', 'country_id', 'state_id', 'city_id', 'pincode', 'search_name', 'status');
            $data = $data->where('pincode', $request?->pincode);
            $data = $data->orderBy('id', 'DESC');
            $data = $data->first();
            if ($data) {
                return $this->sendResponse($data, "Pincode exist list.");
            }
            return $this->sendError("Pincode not exist. Select country, state and city.");
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage());
        }
        return $this->sendError("Something want to wrong in register API");
    }



    /**
     * After Login API's Method
     */



    /**
     * @OA\Post(
     *      path="/sliders",
     *      operationId="sliders",
     *      tags={"Authorization API"},
     *      security={{"passport":{}}},
     *      summary="Dashboard slider https://prnt.sc/eVkml1CxSPV8",
     *      @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="multipart/form-data",
     *                  @OA\Schema(
     *                     type="object",
     *                     @OA\Property(property="filter_by", type="string", example="", description="application_dashboard_top, application_dashboard_center" ),
     *             ),
     *         ),
     *     ),
     *      @OA\Response(response=200, description="This Pincode exist.", @OA\JsonContent()),
     * )
     */
    public function sliders(Request $request)
    {
        try {
            $loginUser = Auth::user();
            if (!$loginUser) {
                return $this->sendError("Unauthorization", [], [], 401);
            }
            $data = Sliders::select('*');
            if ($request?->filter_by) {
                $data = $data->where('slider_type', $request?->filter_by);
            } else {
                $data = $data->where('slider_type', "application_dashboard_top");
            }
            $data = $data->orderBy('id', 'DESC');
            $data = $data->get();

            $data = $data->map(function ($record) {
                $temp = [];
                // $temp = $record;

                $temp['id'] = $record?->id . "";
                $temp['slider_type'] = $record?->slider_type . "";
                $temp['media_type'] = $record?->media_type . "";
                $temp['video_url'] = $record?->video_url . "";
                $temp['redirect_url'] = $record?->redirect_url . "";
                $temp['status'] = $record?->status . "";
                $temp['image_url'] = $record?->image_url . "";

                return $temp;
            });
            return $this->sendResponse($data, "Slider list.");
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage());
        }
        return $this->sendError("Something want to wrong in register API");
    }

    /**
     * @OA\Post(
     *      path="/verify-qr-scan",
     *      operationId="verify-qr-scan",
     *      tags={"Authorization API"},
     *      security={{"passport":{}}},
     *      summary="Scanned qr code is valid or not.",
     *      @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="multipart/form-data",
     *                  @OA\Schema(
     *                     type="object",
     *                      required={"code"},
     *                     @OA\Property(property="code", type="string", example="", description="code" ),
     *             ),
     *         ),
     *     ),
     *      @OA\Response(response=200, description="Your scanned QR is valid.", @OA\JsonContent()),
     * )
     */
    public function verifyQrScan(Request $request)
    {
        try {
            $loginUser = Auth::user();
            if (!$loginUser) {
                return $this->sendError("Unauthorization", [], [], 401);
            }
            $validator = Validator::make($request->all(), [
                'code' => 'required|exists:' . ((new RedemptionItem())->getTable()) . ',redemption_code_label',
                'price' => 'nullable|exists:' . ((new RedemptionItem())->getTable()) . ',collect_points',
            ]);

            if ($validator->fails()) {
                return $this->sendError('Validation Error.', $validator->errors()->first(), $validator->errors(), 403);
            }
            $scanedQr = RedemptionItem::where('redemption_code_label', $request?->code)->first();
            if ($scanedQr) {
                if ($scanedQr?->status == "inactive") {
                    return $this->sendError("This QR is Inactive");
                }
                // elseif($scanedQr?->scanned_user_id){
                //     return $this->sendError("This qr is used before.");
                // }
                $scanedQr->scanned_user_id = $loginUser?->id;
                $scanedQr->scanned_date = Carbon::now();
                $scanedQr->save();
                return $this->sendResponse([], "Your scanned QR is valid.");
            }
            return $this->sendError("Scanned qr code not found.");
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage());
        }
        return $this->sendError("Something want to wrong in register API");
    }

    /**
     * @OA\Post(
     *      path="/scan-history",
     *      operationId="scan-history",
     *      tags={"Authorization API"},
     *      security={{"passport":{}}},
     *      summary="Get the login user scanned history.",
     *      @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="multipart/form-data",
     *                  @OA\Schema(
     *                     type="object",
     *                     @OA\Property(property="page", type="string", example="1", description="Which page data you fetch." ),
     *                     @OA\Property(property="per_page", type="string", example="10", description="per page how many records fetch." ),
     *             ),
     *         ),
     *     ),
     *      @OA\Response(response=200, description="Your scanned QR is valid.", @OA\JsonContent()),
     * )
     */
    public function scanHistory(Request $request)
    {
        try {
            $loginUser = Auth::user();
            if (!$loginUser) {
                return $this->sendError("Unauthorization", [], [], 401);
            }

            /* Pagination */
            $pagination = (isset($request?->page)) ? true : false;
            $perPage = (isset($request?->per_page)) ? $request?->per_page : env("API_PER_PAGE");
            $page = (isset($request?->page)) ? $request?->page : 1;

            $scanHistoryByUser = RedemptionItem::where('id', '>', 0)->where('scanned_user_id', $loginUser?->id);

            $scanHistoryByUser = $scanHistoryByUser->orderBy('id', 'desc');

            $tempResponseData = [];
            if ($pagination) {
                $scanHistoryByUser = $scanHistoryByUser->paginate($perPage);

                $tempResponseData['total'] = "" . $scanHistoryByUser->total();
                $tempResponseData['perPage'] = $perPage;
                $tempResponseData['currentPage'] = $page;
                $tempResponseData['totalPage'] = "" . $scanHistoryByUser->lastPage();
                $tempResponseData['items'] = (object)$scanHistoryByUser->items();
                $tempResponseData['items'] = collect($tempResponseData['items']);
            } else {
                $scanHistoryByUser = $scanHistoryByUser->get();
                $tempResponseData['total'] = $scanHistoryByUser->count();
                $tempResponseData['items'] = $scanHistoryByUser;
            }

            // return gettype($tempResponseData['items']);
            if (isset($tempResponseData['items'])) {
                $tempResponseData['items'] = $tempResponseData['items']->map(function ($record) {
                    $temp = [];
                    // $temp = $record;
                    $temp['id'] = $record->id . "";
                    $temp['redemption_code_label'] = $record->redemption_code_label . "";
                    $temp['collect_points'] = $record->collect_points . "";
                    $temp['scanned_date'] = $record->scanned_date . "";
                    return $temp;
                });

                return $this->sendResponse($tempResponseData, "Scan history list.");
            }
            return $this->sendError("Scanned data not found, Something want to wrong.");
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage());
        }
        return $this->sendError("Something want to wrong in register API");
    }

        /**
         * @OA\Post(
         *      path="/my-points",
         *      operationId="my-points",
         *      tags={"Authorization API"},
         *      security={{"passport":{}}},
         *      summary="Get the login user points.",
         *      @OA\Response(response=200, description="My Points.", @OA\JsonContent()),
         *      @OA\Response(response=401, description="Unauthorized")
         * )
         */
        public function myPoints(Request $request)
        {
            try {
                $loginUser = Auth::user();
                if (!$loginUser) {
                    return $this->sendError("Unauthorization", [], [], 401);
                }

                $tempResponseData = [
                    'total_earning' => 0,
                    'total_redeem' => 0,
                    'total_balance' => 0,
                    'current_year_earning' => 0,
                    'current_year_redeem' => 0,
                    'current_year_balance' => 0,
                ];

                $startDate = Carbon::now()->month >= 4
                    ? Carbon::now()->startOfYear()->addMonths(3) // April 1st of the current year
                    : Carbon::now()->subYear()->startOfYear()->addMonths(3); // April 1st of the previous year

                $endDate = $startDate->copy()->addYear()->subDay(); // March 31st of the next year

                $tempResponseData['total_earning'] = RedemptionItem::where('scanned_user_id', $loginUser?->id)
                    ->whereBetween('scanned_date', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()])
                    ->get()
                    ->sum('collect_points');

                $tempResponseData['total_redeem'] = WithdrawRequest::where('user_id', $loginUser?->id)
                    ->whereBetween('withdraw_request_date', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()])
                    ->where('withdraw_request_status', 'Approve')->get()->sum('request_points');

                $tempResponseData['total_balance'] = $tempResponseData['total_earning'] - $tempResponseData['total_redeem'];

                $tempResponseData['current_year_earning'] = RedemptionItem::where('scanned_user_id', $loginUser?->id)
                    ->whereBetween('scanned_date', [$startDate, $endDate])->get()->sum('collect_points');

                $tempResponseData['current_year_redeem'] = WithdrawRequest::where('user_id', $loginUser?->id)
                    ->whereBetween('withdraw_request_date', [$startDate, $endDate])
                    ->where('withdraw_request_status', 'Approve')
                    ->get()->sum('request_points');

                $tempResponseData['current_year_balance'] = $tempResponseData['current_year_earning'] - $tempResponseData['current_year_redeem'];

                $currentMonth = Carbon::now()->month;
                $currentYear = Carbon::now()->year;

                if ($currentMonth >= 4) {
                    // From April to December → current year to next year
                    $financialYear = $currentYear . '-' . ($currentYear + 1);
                } else {
                    // From January to March → previous year to current year
                    $financialYear = ($currentYear - 1) . '-' . $currentYear;
                }

                $tempResponseData['current_year'] = '(' . $financialYear . ')';
                $tempResponseData['current_month'] = Carbon::now()->format('F');

                $setting = Setting::where('key', 'point-to-rupees')->first();
                $point = 0;
                $rupees = 0;

                if ($setting) {
                    $value = $setting->value;

                    if (is_string($value)) {
                        $value = json_decode($value, true);
                    } elseif ($value instanceof \stdClass) {
                        $value = (array)$value;
                    }
                    // {"point":"1","rupees":"10"}
                    $point = isset($value['point']) ? (int)$value['point'] : $point;
                    $rupees = isset($value['rupees']) ? (int)$value['rupees'] : $rupees;
                }

                $tempResponseData['total_earning'] = $tempResponseData['total_earning'].'';
                $tempResponseData['total_redeem'] = $tempResponseData['total_redeem'].'';
                $tempResponseData['total_balance'] = $tempResponseData['total_balance'].'';
                $tempResponseData['current_year_earning'] = $tempResponseData['current_year_earning'].'';
                $tempResponseData['current_year_redeem'] = $tempResponseData['current_year_redeem'].'';
                $tempResponseData['current_year_balance'] = $tempResponseData['current_year_balance'].'';
                $tempResponseData['point_to_rupees'] = $point . ' Point = ' . $rupees . ' Rupees';
                $tempResponseData['point'] = $point."";
                $tempResponseData['point_equval_rs'] = $rupees."";

                $request_amount = Setting::where('key', 'min-withdraw-request-amount')->where('status', 'active')->first();
                $tempResponseData['min_request_amount'] = "";
                $tempResponseData['max_request_amount'] = "";
                if($request_amount){
                    $tempResponseData['min_request_amount'] = $request_amount?->value->min_amount ?? '';
                    $tempResponseData['max_request_amount'] = $request_amount?->value->max_amount ?? '';
                }

                return $this->sendResponse($tempResponseData, "Dashboard Point.");

                return $this->sendError("Scanned data not found, Something want to wrong.");
            } catch (\Exception $e) {
                return $this->sendError($e->getMessage());
            }
            return $this->sendError("Something want to wrong in register API");
        }

    /**
     * @OA\Post(
     *      path="/documents",
     *      operationId="documents",
     *      tags={"Authorization API"},
     *      security={{"passport":{}}},
     *      summary="Get the login user scanned history.",
     *      @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="multipart/form-data",
     *                  @OA\Schema(
     *                     type="object",
     *                     @OA\Property(property="page", type="string", example="1", description="Which page data you fetch." ),
     *                     @OA\Property(property="per_page", type="string", example="10", description="per page how many records fetch." ),
     *             ),
     *         ),
     *     ),
     *      @OA\Response(response=200, description="Your scanned QR is valid.", @OA\JsonContent()),
     * )
     */
    public function documents(Request $request)
    {
        try {
            $loginUser = Auth::user();
            if (!$loginUser) {
                return $this->sendError("Unauthorization", [], [], 401);
            }

            /* Pagination */
            $pagination = (isset($request?->page)) ? true : false;
            $perPage = (isset($request?->per_page)) ? $request?->per_page : env("API_PER_PAGE");
            $page = (isset($request?->page)) ? $request?->page : 1;

            $moduleDocuments = Documents::where('id', '>', 0);
            $moduleDocuments = $moduleDocuments->where('status', 'active');
            $moduleDocuments = $moduleDocuments->orderBy('id', 'desc');

            $tempResponseData = [];
            if ($pagination) {
                $moduleDocuments = $moduleDocuments->paginate($perPage);

                $tempResponseData['total'] = "" . $moduleDocuments->total();
                $tempResponseData['perPage'] = $perPage;
                $tempResponseData['currentPage'] = $page;
                $tempResponseData['totalPage'] = "" . $moduleDocuments->lastPage();
                $tempResponseData['items'] = (object)$moduleDocuments->items();
                $tempResponseData['items'] = collect($tempResponseData['items']);
            } else {
                $moduleDocuments = $moduleDocuments->get();
                $tempResponseData['total'] = $moduleDocuments->count();
                $tempResponseData['items'] = $moduleDocuments;
            }

            // return gettype($tempResponseData['items']);
            if (isset($tempResponseData['items'])) {
                $tempResponseData['items'] = $tempResponseData['items']->map(function ($record) {
                    $temp = [];
                    // $temp = $record;
                    $temp['id'] = $record->id . "";
                    $temp['document_type_id'] = $record?->document_type_id . "";
                    $temp['document_type_name'] = $record?->document_type?->name . "";
                    $temp['name'] = $record?->name . "";
                    $temp['document_file_url'] = $record?->document_file_url . "";
                    $temp['file_mime_type'] = $record?->file_mime_type . "";
                    return $temp;
                });

                return $this->sendResponse($tempResponseData, "Document List.");
            }
            return $this->sendError("Scanned data not found, Something want to wrong.");
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage());
        }
        return $this->sendError("Something want to wrong in register API");
    }
}
