<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Master\MasterSubject;
use App\Models\StudentRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class StudentRequestController extends Controller
{

    // public function request_form(Request $request)
    // {
    //     try {
    //         $student = Auth::guard('student-api')->user();

    //         if (!$student) {
    //             return $this->sendError("Unauthorized.", [], [], 401);
    //         }

    //         $subject_list = StudentRequest::getSubjects();

    //         // Manual validation
    //         $validator = Validator::make($request->all(), [
    //             'subject' => ['required', Rule::in($subject_list)],
    //             'detail'  => 'required|string|max:1000'
    //         ]);

    //         if ($validator->fails()) {
    //             $firstError = $validator->errors()->first(); // Get first message
    //             return $this->sendError(
    //                 $firstError,
    //                 $validator->errors(),
    //                 [],
    //                 422
    //             );
    //         }

    //         // Create new request
    //         $newRequest = StudentRequest::create([
    //             'student_id' => $student->id,
    //             'subject'    => $request->subject,
    //             'detail'     => $request->detail,
    //         ]);
    //         unset($newRequest['created_at']);
    //         unset($newRequest['updated_at']);

    //         return $this->sendResponse($newRequest, 'Request submitted successfully.');
    //     } catch (\Exception $e) {
    //         return $this->sendError('Something went wrong.', $e->getMessage(), [], 500);
    //     }
    // }

    public function request_form(Request $request)
    {
        try {
            $student = Auth::guard('student-api')->user();

            if (!$student) {
                return $this->sendError("Unauthorized.", [], [], 401);
            }

            // Validate input
            $validator = Validator::make($request->all(), [
                'subject_id' => [
                    'required',
                    'exists:' . ((new MasterSubject())->getTable()) . ',id'
                ],
                'detail' => ['required', 'string', 'max:1000'],
            ]);

            if ($validator->fails()) {
                // Return first error as main message
                return $this->sendError(
                    $validator->errors()->first(),
                    $validator->errors(),
                    [],
                    422
                );
            }

            $requestData = new StudentRequest();
            $requestData->student_id = $student->id;
            $requestData->subject_id = $request->subject_id;
            $requestData->detail = $request->detail;
            $requestData->save();

            unset($requestData['created_at']);
            unset($requestData['updated_at']);

            return $this->sendResponse($requestData, 'Request submitted successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Something went wrong.', $e->getMessage(), [], 500);
        }
    }



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
