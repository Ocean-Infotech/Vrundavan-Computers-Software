<?php

namespace App\Http\Controllers\api;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\Documents;
use App\Models\Admission;
use App\Models\CourceRegistration;
use App\Models\LibraryMasterBook;
use App\Models\Master\MasterCity;
use App\Models\Master\MasterCountry;
use App\Models\Master\MasterCourse;
use App\Models\Master\MasterBatch;
use App\Models\Master\MasterClass;
use App\Models\Master\MasterDepartment;
use App\Models\Master\MasterPincode;
use App\Models\Master\MasterShift;
use App\Models\Master\MasterState;
use App\Models\Master\MasterUniversity;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Role;
use Illuminate\Validation\Rule;

class CommonController extends Controller
{
    public function __construct(Request $request) {}

    public function get_courceregistration(Request $request)
    {
        try {
            $data = DB::table('cource_registration as cr')
                ->join('admission as a', 'cr.register_id', '=', 'a.id')
                ->join('master_course as mc', 'cr.course_id', '=', 'mc.id')
                // ->where('cr.status', 'active')
                ->orderBy('cr.id', 'ASC')
                ->select(
                    'cr.id as course_reg_id',
                    'a.id as admission_id',
                    'a.first_name',
                    'a.father_name',
                    'a.last_name',
                    'mc.course_name',
                )
                ->get();

            $data = $data->map(function ($row) {
                // $temp = $row;
                $temp['id'] = $row?->course_reg_id . "";
                // $temp['course_reg_id'] = $row?->course_reg_id . "";
                $temp['name'] = $row?->first_name . " " . $row?->father_name . " " . $row?->last_name . "";
                $temp['course_name'] = $row?->course_name;
                return $temp;
            });
            return $this->sendResponse($data, "Course list.");
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage());
        }
        return $this->sendError("Something want to wrong in register API");
    }

    public function get_classbybatch(Request $request)
    {
        $validator =  Validator::make($request->all(), [
            'batch_id' => [
                'required',
                Rule::exists((new MasterBatch())->getTable(), 'id')
            ],
        ]);

        try {

            $shift = MasterShift::select('class_id', DB::raw('COUNT(*) as total'))
                ->where('status', 'active')
                ->where('batch_id', $request?->batch_id)
                ->groupBy('class_id')
                ->orderBy('class_id', 'ASC')
                ->get();

            $classIds = $shift->pluck('class_id')->toArray();

            $classes = MasterClass::where('status', 'active')
                ->whereIn('id', $classIds)
                ->orderBy('class', 'ASC')
                ->get()
                ->keyBy('id');

            $data = $shift->map(function ($row) use ($classes) {
                $class = $classes[$row->class_id] ?? null;
                return [
                    'id' => (string) $class?->id,
                    'name' => $class?->class,
                    'total' => $row->total,
                ];
            });
            return $this->sendResponse($data, "Class list.");
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage());
        }
        return $this->sendError("Something want to wrong in register API");
    }

    public function get_shift(Request $request)
    {
        $validator =  Validator::make($request->all(), [
            'course_id' => [
                'required',
                Rule::exists((new MasterCourse())->getTable(), 'id')
            ],
            'batch_id' => [
                'required',
                Rule::exists((new MasterBatch())->getTable(), 'id')
            ],
            'class_id' => [
                'required',
                Rule::exists((new MasterClass())->getTable(), 'id')
            ]
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->messages()->first(), $validator->messages(), [], 401);
        }
        try {
            $data = MasterShift::where('status', 'active')->where('course_id', $request?->course_id)->where('batch_id', $request?->batch_id)->where('class_id', $request?->class_id)->get();

            $data = $data->map(function ($row) {
                // $temp = $row;

                $from_time = Carbon::parse($row?->from_time)->format('h:i A');
                $to_time = Carbon::parse($row?->to_time)->format('h:i A');

                $temp['id'] = $row?->id . "";
                $temp['name'] = $from_time . " To " . $to_time . "";
                return $temp;
            });
            return $this->sendResponse($data, "Shift list.");
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage());
        }
        return $this->sendError("Something want to wrong in register API");
    }

    public function get_class(Request $request)
    {
        try {
            $data = MasterClass::where('status', 'active')->orderBy('class', 'ASC')->get();

            $data = $data->map(function ($row) {
                // $temp = $row;
                $temp['id'] = $row?->id . "";
                $temp['name'] = $row?->class . "";
                return $temp;
            });
            return $this->sendResponse($data, "Class list.");
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage());
        }
        return $this->sendError("Something want to wrong in register API");
    }

    public function get_department(Request $request)
    {
        try {
            $data = MasterDepartment::where('status', 'active')
                ->orderBy('name', 'ASC')
                ->get();

            $data = $data->map(function ($row) {
                return [
                    'id' => $row?->id . "",
                    'name' => $row?->name . "",
                ];
            });

            return $this->sendResponse($data, "Department list.");
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage());
        }

        return $this->sendError("Something went wrong in Department API");
    }
    public function get_university(Request $request)
    {
        try {
            $data = MasterUniversity::where('status', 'active')
                ->orderBy('name', 'ASC')
                ->get();

            $data = $data->map(function ($row) {
                return [
                    'id' => $row?->id . "",
                    'name' => $row?->name . "",
                ];
            });

            return $this->sendResponse($data, "University list.");
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage());
        }

        return $this->sendError("Something went wrong in University API");
    }


    public function get_admission(Request $request)
    {
        try {
            $data = Admission::where('status', 'created')->orderBy('id', 'ASC')->get();

            $data = $data->map(function ($row) {
                // $temp = $row;
                $temp['id'] = $row?->id . "";
                $temp['name'] = $row?->first_name . " " . $row?->father_name . " " . $row?->last_name . "";
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
            $data = MasterCourse::query();
            if ($request?->filter_by_status) {
                if ($request?->filter_by_status != "all") {
                    $data = $data->where('status', trim($request?->filter_by_status));
                }
            } else {
                $data = $data->where('status', 'active');
            }
            $data = $data->orderBy('course_name', 'ASC')->get();

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

    // public function get_semester(Request $request)
    // {
    //     try {
    //         $validator =  Validator::make($request->all(), [
    //             'course_id' => [
    //                 'required',
    //                 Rule::exists((new MasterCourse())->getTable(), 'id')
    //             ]
    //         ]);
    //         if ($validator->fails()) {
    //             return $this->sendError($validator->messages()->first(), $validator->messages(), [], 401);
    //         }

    //         if (!$request?->course_id) {
    //             return $this->sendError("Course id is required.");
    //         }
    //         $MasterCourse = MasterCourse::where('id', $request?->course_id,)->where('status', 'active')->get();
    //         $totalsemester = $MasterCourse?->semester;

    //         $data = [];

    //         for ($sem = 1; $sem <= $totalsemester; $sem++) {
    //             $data[] = [
    //                 'semester' => $sem
    //             ];
    //         }
    //         // $data = $data->map(function ($row) {
    //         //     // $temp = $row;
    //         //     $temp['id'] = $row?->id . "";
    //         //     $temp['semester'] = $row?->semester . "";
    //         //     return $temp;
    //         // });


    //         return $this->sendResponse($data, "Batch list.");
    //     } catch (\Exception $e) {
    //         return $this->sendError($e->getMessage());
    //     }
    //     return $this->sendError("Something want to wrong in register API");
    // }

    public function get_semester(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'course_id' => [
                    'required',
                    Rule::exists((new MasterCourse())->getTable(), 'id')
                ]
            ]);

            if ($validator->fails()) {
                return $this->sendError($validator->messages()->first(), $validator->messages(), [], 401);
            }

            $MasterCourse = MasterCourse::where('id', $request->course_id)
                ->where('status', 'active')
                ->first(); // Changed from get() to first()

            if (!$MasterCourse) {
                return $this->sendError("Course not found or inactive.");
            }

            $totalsemester = $MasterCourse->semester;

            $data = [];
            for ($sem = 1; $sem <= $totalsemester; $sem++) {
                $data[] = [
                    'semester' => $sem
                ];
            }

            return $this->sendResponse($data, "Semester list.");
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage());
        }

        return $this->sendError("Something went wrong in register API");
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
    public function get_books(Request $request)
    {
        $search = $request->get('search', '');

        // ✅ FIXED: removed extra semicolon and chained where() properly
        $query = \App\Models\LibraryMasterBook::query()
            ->where('status', 'active');

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('book_name', 'like', "%{$search}%")
                    ->orWhere('library_book_no', 'like', "%{$search}%");
            });
        }

        $books = $query->orderBy('book_name', 'asc')->limit(10)->get();

        $options = '';
        foreach ($books as $book) {
            $options .= "<option value='{$book->book_name} - {$book->library_book_no}' data-id='{$book->id}'></option>";
        }

        return response($options);
    }
}
