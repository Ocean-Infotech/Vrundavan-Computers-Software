<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\StudentRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class StudentRequestController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
public function store(Request $request)
{
    try {
        $student = Auth::guard('student-api')->user();

        if (!$student) {
            return $this->sendError("Unauthorized.", [], [], 401);
        }

        // Get static subjects from model
        $subject_list = StudentRequest::getSubjects();

        // Manual validation
        $validator = Validator::make($request->all(), [
            'subject' => ['required', Rule::in($subject_list)],
            'detail'  => 'required|string|max:1000'
        ]);

        if ($validator->fails()) {
            $firstError = $validator->errors()->first(); // Get first message
            return $this->sendError(
                $firstError,
                $validator->errors(),
                [],
                422
            );
        }

        // Create new request
        $newRequest = StudentRequest::create([
            'student_id' => $student->id,
            'subject'    => $request->subject,
            'detail'     => $request->detail,
        ]);
        unset($newRequest['created_at']);
        unset($newRequest['updated_at']);

        return $this->sendResponse($newRequest, 'Request submitted successfully.');
    } catch (\Exception $e) {
        return $this->sendError('Something went wrong.', $e->getMessage(), [], 500);
    }
}


    public function subjects()
    {
        try {
            $student = Auth::guard('student-api')->user();

            if (!$student) {
                return $this->sendError("Unauthorized.", [], [], 401);
            }

            $subjects = StudentRequest::getSubjects();

            $data = [
                'subjects' => $subjects,
            ];

            return $this->sendResponse($data, 'Subjects List');
        } catch (\Exception $e) {
            return $this->sendError('Something went wrong.', $e->getMessage(), [], 500);
        }
    }


    /**
     * Display the specified resource.
     */
    public function show(StudentRequest $studentRequest)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(StudentRequest $studentRequest)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, StudentRequest $studentRequest)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(StudentRequest $studentRequest)
    {
        //
    }
}
