<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Admission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class AuthenticatedController extends Controller
{

    // public function __construct(Request $request) {}

    /**
     * Get logged-in student profile
     *
     * Returns the complete profile of the currently authenticated student, including personal details,
     * contact information and status.
     *
     * @group Student
     * @authenticated
     *
     * @response 200 {
     *   "status": "true",
     *   "message": "Login user profile get successfully.",
     *   "data": {
     *     "id": "123",
     *     "first_name": "Raj",
     *     "last_name": "Patel",
     *     "father_name": "Mahesh",
     *     "mobile_no": "9876543210",
     *     "email_address": "raj@example.com",
     *     "status": "active"
     *   }
     * }
     *
     * @response 401 scenario="Not authenticated" {
     *   "status": "false",
     *   "message": "Unauthorization.",
     *   "data": []
     * }
     */
    public function student_profile_detail(Request $request)
    {
        try {
            // return auth('student-api')->user();
            $student = Auth::guard('student-api')->user();

            if (!$student) {
                return $this->sendError("Unauthorization.", [], [], 401);
            }
            $responseData = [];
            $getProfileRequest = new Request();

            $getProfileRequest->admission_id = $student->id;
            $getProfile = (new AuthenticatedController())->student_profile_response($getProfileRequest);

            return $this->sendResponse($getProfile, 'Login user profile get successfully.');
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), [], $e->getMessage());;
        }
        return $this->sendError('Something went wrong.', [], [], 500);
    }

    /**
     * Build student profile response
     *
     * Internal helper that assembles the student profile object from an admission id.
     *
     * @hideFromAPIDocumentation
     */
    public function student_profile_response(Request $request)
    {
        try {
            $responseData = [];

            if (!$request?->admission_id) {
                return (object)$responseData;
            }

            $studentDetail = Admission::where('id', $request?->admission_id)->first();
            if (!$studentDetail && $request?->directReturn) {
                return (object)$responseData;
            }

            $responseData['id'] = $studentDetail?->id . '';
            $responseData['aadhar_card_no'] = $studentDetail?->aadhar_card_no . '';
            $responseData['first_name'] = $studentDetail?->first_name . '';
            $responseData['last_name'] = $studentDetail?->last_name . '';
            $responseData['father_name'] = $studentDetail?->father_name . '';
            $responseData['mother_name'] = $studentDetail?->mother_name . '';
            $responseData['temporary_address'] = $studentDetail?->temporary_address . '';
            $responseData['permanent_address'] = $studentDetail?->permanent_address . '';
            $responseData['mobile_no'] = $studentDetail?->mobile_no . '';
            $responseData['parent_mobile_no'] = $studentDetail?->parent_mobile_no . '';
            $responseData['other_mobile_no'] = $studentDetail?->other_mobile_no . '';
            $responseData['whatsapp_no'] = $studentDetail?->whatsapp_no . '';
            $responseData['cast'] = $studentDetail?->cast . '';
            $responseData['occupation'] = $studentDetail?->occupation . '';
            $responseData['date_of_birth'] = $studentDetail?->date_of_birth . '';
            $responseData['email_address'] = $studentDetail?->email_address . '';
            $responseData['gender'] = $studentDetail?->gender . '';
            $responseData['category'] = $studentDetail?->category . '';
            $responseData['enrolment_no'] = $studentDetail?->enrolment_no . '';
            $responseData['spid'] = $studentDetail?->spid . '';
            $responseData['apaar_id_abc_id'] = $studentDetail?->apaar_id_abc_id . '';
            $responseData['gdrivefolderurl'] = $studentDetail?->gdrivefolderurl . '';
            $responseData['gdrivefolderid'] = $studentDetail?->gdrivefolderid . '';
            $responseData['gdrivefoldername'] = $studentDetail?->gdrivefoldername . '';
            $responseData['status'] = $studentDetail?->status . '';
            $responseData['profile_url'] = $studentDetail?->profile_pic_url . '';
            return (object)$responseData;
        } catch (\Exception $e) {
            return (object)[$e->getMessage()];
        }
        return (object)[];
    }
    /**
     * Update logged-in student profile
     *
     * Updates key contact and identification fields (mobile numbers, Aadhaar and addresses) for the
     * currently authenticated student. Returns the fresh profile after update.
     *
     * @group Student
     * @authenticated
     *
     * @bodyParam mobile_no string required Primary mobile number (must be unique). Example: 9876543210
     * @bodyParam other_mobile_no string required Alternate mobile number (must be unique). Example: 9123456780
     * @bodyParam whatsapp_no string WhatsApp number (must be unique if provided). Example: 9876543210
     * @bodyParam aadhar_card_no string required 12‑digit Aadhaar number. Example: 234567890123
     * @bodyParam temporary_address string required Current/temporary address. Example: 12, Shree Nagar Society, Surat
     * @bodyParam permanent_address string required Permanent address. Example: 45, Patel Street, Junagadh
     *
     * @response 200 {
     *   "status": "true",
     *   "message": "Update student profile successfully.",
     *   "data": {
     *     "id": "123",
     *     "mobile_no": "9876543210",
     *     "other_mobile_no": "9123456780",
     *     "whatsapp_no": "9876543210",
     *     "temporary_address": "12, Shree Nagar Society, Surat",
     *     "permanent_address": "45, Patel Street, Junagadh"
     *   }
     * }
     *
     * @response 401 scenario="Not authenticated" {
     *   "status": "false",
     *   "message": "Unauthorization",
     *   "data": []
     * }
     */
   public function student_update_profile(Request $request)
{
    try {
        $loginUser = Auth::user();
        if (!$loginUser) {
            return $this->sendError("Unauthorization", [], [], 401);
        }

        $loginUserId = $loginUser->id;

        $validator =  Validator::make($request->all(), [
            'mobile_no' => [
                'required',
                'regex:/^(?:\+91|91)?[6-9]\d{9}$/',
                Rule::unique((new Admission())->getTable(), 'mobile_no')->ignore($loginUserId),
            ],
            'other_mobile_no' => [
                'required',
                'regex:/^(?:\+91|91)?[6-9]\d{9}$/',
                Rule::unique((new Admission())->getTable(), 'other_mobile_no')->ignore($loginUserId),
            ],
            'whatsapp_no' => [ 
                'nullable',
                'regex:/^(?:\+91|91)?[6-9]\d{9}$/',
                Rule::unique((new Admission())->getTable(), 'whatsapp_no')->ignore($loginUserId),
            ],
            'aadhar_card_no' => [
                'required',
                'regex:/^[2-9]{1}[0-9]{11}$/',
                'string',
                Rule::unique((new Admission())->getTable(), 'aadhar_card_no')->ignore($loginUserId),
            ],
            'temporary_address' => [
                'required',
                'string',
            ],
            'permanent_address' => [
                'required',
                'string',
            ],
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->messages()->first(), $validator->messages(), [], 401);
        }

        $updateParams = $request->only([
            'mobile_no',
            'other_mobile_no',
            'whatsapp_no',
            'aadhar_card_no',
            'temporary_address',
            'permanent_address',
        ]);

        $updateTeamPerson = Admission::where('id', $loginUser?->id)->first();
        $updateTeamPerson->update($updateParams);

        $newRequest = new Request();
        $newRequest['admission_id'] = $loginUser?->id;
        $login_profile = (array)self::student_profile_response($newRequest);

        return $this->sendResponse($login_profile, "Update student profile successfully.");
    } catch (\Exception $e) {
        return $this->sendError($e->getMessage());
    }
}

}
