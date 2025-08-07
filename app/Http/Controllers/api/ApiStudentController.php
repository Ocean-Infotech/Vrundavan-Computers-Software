<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\admin\StudentsFeesReportController;
use App\Http\Controllers\Controller;
use App\Models\Admission;
use App\Models\CourceRegistration;
use App\Models\FeesCollection;
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
            } else if ($courseRegistered?->status != "active") {
                return $this->sendError('Academic detail not active.', [], [], 422);
            } else {

                $course = MasterCourse::where('id', $courseRegistered->course_id)->first();

                $data = [
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
    public function fee_details(Request $request)
    {
        try {
            $student = Auth::guard('student-api')->user();

            if (!$student) {
                return $this->sendError("Unauthorization.", [], [], 401);
            }

            $feeRecords = FeesCollection::with('course')
                ->where('student_id', $student->id)
                ->get();

            if ($feeRecords->isEmpty()) {
                return $this->sendError('No fee records found.', [], [], 404);
            }

            $courseFees = $feeRecords->first()->course->course_fees;

            $paidFees = $feeRecords->where('status', true)->sum('fees');

            $pendingFees = $courseFees - $paidFees;

            $feesDetails = $feeRecords->map(function ($fee) {
                return [
                    'course'     => $fee->course->course_name,
                    'semester'   => 'Semester ' . $fee->year_semester,
                    'receipt_no' => $fee->id,
                    'amount'     => $fee->fees,
                    'date'       => $fee->date ? \Carbon\Carbon::parse($fee->date)->format('Y-m-d') : null,
                    'status'     => 'Paid',
                ];
            });

            $data = [
                'total'   => $courseFees,
                'paid'    => $paidFees,
                'pending' => $pendingFees,
                'details' => $feesDetails,
            ];

            return $this->sendResponse($data, 'Fee details fetched successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Something went wrong.', $e->getMessage(), [], 500);
        }
    }
}
