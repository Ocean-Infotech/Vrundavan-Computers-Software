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

            return (object)$responseData;
        } catch (\Exception $e) {
            return (object)[$e->getMessage()];
        }
        return (object)[];
    }


    public function student_update_profile(Request $request)
    {
        try {
            $loginUser = Auth::user();
            if (!$loginUser) {
                return $this->sendError("Unauthorization", [], [], 401);
            }

            $validator =  Validator::make($request->all(), [
                'email_address' => [
                    'required',
                    'email',
                    Rule::unique((new Admission())->getTable())->ignore($loginUser->id),
                ],
                'mobile_no' => [
                    'required',
                    'regex:/^(?:\+91|91)?[6-9]\d{9}$/',
                    Rule::unique((new Admission())->getTable(), 'mobile_no')->ignore($loginUser->id),
                ],
                'other_mobile_no' => [
                    'required',
                    'regex:/^(?:\+91|91)?[6-9]\d{9}$/',
                    Rule::unique((new Admission())->getTable())->ignore($loginUser->id),
                ],
                'aadhar_card_no' => [
                    'required',
                    'regex:/^[2-9]{1}[0-9]{11}$/',
                    'string',
                    Rule::unique((new Admission())->getTable(), 'aadhar_card_no')->ignore($loginUser->id),
                ],
                'temporary_address' => [
                    'required',
                    'string',
                    Rule::unique((new Admission())->getTable())->ignore($loginUser->id),
                ],
                'permanent_address' => [
                    'required',
                    'string',
                ],
            ]);

            if ($validator->fails()) {
                return $this->sendError($validator->messages()->first(), $validator->messages(), [], 401);
            }

            $responseData = [];
            $updateParams = $request->all();
            $updateTeamPerson = Admission::where('id', $loginUser?->id)->first();

            // return $updateParams;
            $updateTeamPerson->update($updateParams);

            $newRequest = new Request();
            $newRequest['admission_id'] = $loginUser?->id;
            $login_profile = (array)self::student_profile_response($newRequest);

            return $this->sendResponse($login_profile, "Update student profile successfully.");
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage());
        }
        return $this->sendError("Something want to wrong.");
    }
}
