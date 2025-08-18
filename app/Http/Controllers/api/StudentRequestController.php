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
                    'subject_id' => [
                        'required',
                        Rule::exists((new MasterSubject())->getTable(), 'id')
                            ->where(function ($query) {
                                $query->where('status', 'active');
                            }),
                    ],
                    'detail' => ['required', 'string', 'max:1000'],
                ],
                [
                    'subject_id.exists' => 'The selected subject id  is inactive.',
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
            $requestData->subject_id = $request->subject_id;
            $requestData->detail = $request->detail;
            $requestData->save();

            unset($requestData['created_at'], $requestData['updated_at']);

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
