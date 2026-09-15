<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Master\MasterSubject;
use App\Models\StudentRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class StudentRequestController extends Controller
{

    /**
     * @OA\Post(
     *     path="/request-form",
     *     tags={"Student Request"},
     *     summary="Create new student request",
     *     security={{"passport":{}}},
     *     @OA\Response(response=200, description="Request submitted"),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function request_form(Request $request)
    {
        try {
            $student = Auth::guard('student-api')->user();

            if (!$student) {
                return $this->sendError("Unauthorized.", [], [], 401);
            }

            // Validate input with custom message
            $validator = Validator::make(
                $request->all(),
                [
                    'subject' => ['required_without:subject_id', 'nullable', 'string', 'max:255'],
                    'subject_id' => ['nullable'],
                    'detail' => ['required', 'string', 'max:1000'],
                ]
            );

            if ($validator->fails()) {
                return $this->sendError(
                    $validator->errors()->first(),
                    $validator->errors(),
                    [],
                    422
                );
            }

            $requestData = new StudentRequest();
            $requestData->student_id = $student->id;
            $requestData->created_by = $student->id;
            if ($request->filled('subject')) {
                $requestData->subject = $request->subject;
            }
            if ($request->filled('subject_id')) {
                $requestData->subject_id = $request->subject_id;
            }
            $requestData->detail = $request->detail;
            $requestData->status = 'Pending';
            $requestData->save();

            unset($requestData['created_at'], $requestData['updated_at'], $requestData['created_by']);

            return $this->sendResponse($requestData, 'Request submitted successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Something went wrong.', $e->getMessage(), [], 500);
        }
    }
    /**
     * @OA\Post(
     *     path="/request-list",
     *     tags={"Student Request"},
     *     summary="Get list of student requests",
     *     security={{"passport":{}}},
     *     @OA\Response(response=200, description="Request list fetched"),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function request_list()
    {
        try {
            $student = Auth::guard('student-api')->user();

            if (!$student) {
                return $this->sendError("Unauthorized.", [], [], 401);
            }

            // Fetch all requests made by the student along with the subject name
            $requests = StudentRequest::where('student_id', $student->id)
                ->with(['subjectRelation:id,name'])
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($item) {
                    $subjectVal = $item->getRawOriginal('subject');
                    if (!$subjectVal && $item->subjectRelation) {
                        $subjectVal = $item->subjectRelation->name;
                    }
                    return [
                        'id' => $item->id,
                        'subject_id' => $item->subject_id,
                        // 'subject' => $subjectVal,
                        'subject_name' => $subjectVal,
                        'detail' => $item->detail,
                        'status' => $item->status ?? 'Pending',
                        'created_at' => $item->created_at ? $item->created_at->format('Y-m-d H:i:s') : null,
                    ];
                });

            return $this->sendResponse(['requests' => $requests], 'Request list fetched successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Something went wrong.', $e->getMessage(), [], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/request-subject-list",
     *     tags={"Student Request"},
     *     summary="Get list of subjects for student requests",
     *     security={{"passport":{}}},
     *     @OA\Response(response=200, description="Subjects list fetched"),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function request_subject_list()
    {
        try {
            $student = Auth::guard('student-api')->user();

            if (!$student) {
                return $this->sendError("Unauthorized.", [], [], 401);
            }

            // Fetch all subjects from master_subjects table (id and name)
            $subjects = MasterSubject::select('id', 'name')->get();

            $data = [
                'subjects' => $subjects,
            ];

            return $this->sendResponse($data, 'Subjects List');
        } catch (\Exception $e) {
            return $this->sendError('Something went wrong.', $e->getMessage(), [], 500);
        }
    }
}
