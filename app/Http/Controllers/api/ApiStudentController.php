<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Admission;
use App\Models\CourceRegistration;
use App\Models\Master\MasterCourse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ApiStudentController extends Controller
{

    public function academic_details(Request $request)
    {
        try {

            $student = Auth::guard('student-api')->user();

            if (!$student) {
                return $this->sendError("Unauthorization.", [], [], 401);
            }


            $courseRegistered = CourceRegistration::where('register_id', $student->id)
                // ->where('status', 'active')
                // ->where('course_id', $request->course_id)
                ->first();

            if (!$courseRegistered) {
                return $this->sendError('Your Academic Detail not found.', [], [], 422);
            }else if($courseRegistered?->status != "active"){
                return $this->sendError('Academic detail not active.', [], [], 422);
            }else{

                $course = MasterCourse::where('id', $courseRegistered->course_id)->first();

                $data = [
                    // 'first_name'     => $student->first_name ?? '',
                    // 'father_name'    => $student->father_name ?? '',
                    // 'last_name'      => $student->last_name ?? '',
                    // 'full_name'      => trim(($student->first_name ?? '') . ' ' . ($student->father_name ?? '') . ' ' . ($student->last_name ?? '')),
                    'university'     => 'Vrundavan Computers',
                    'department'     => 'C.S. & I.T',
                    'course'         => $course->course_name ?? '',
                    'semester'       => $course->semester ?? '',
                    'gr_no'          => '1234567890',
                    'enrollment_no'  => $student->enrolment_no ?? '',
                    'spid'           => $student->spid ?? '',
                ];

                return $this->sendResponse($data, 'Academic details fetched successfully.');
            }

        } catch (\Exception $e) {
            return $this->sendError('Something went wrong.', $e->getMessage(), [], 500);
        }
    }
}
