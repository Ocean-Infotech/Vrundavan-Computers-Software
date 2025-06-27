<?php

namespace App\Traits;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;

trait BioMetricTrait
{

    public $baseUrl = 'http://192.168.1.88:88/api/';

    public function getBioMetricData() {}

    public function getBioMetricEmployees()
    {
        try {
            $apiResponse = Http::withHeaders([
                'Content-Type' => 'application/json',
                // 'Authorization' => 'Bearer ' . $token,
            ])->get($this->baseUrl . 'Employees');

            return $apiResponse;
        } catch (\Throwable $th) {
            //throw $th;
        }
    }

    public function getBioMetricEmployeeById($bioMatricId)
    {
        try {
            if ($bioMatricId) {
                $apiResponse = Http::withHeaders([
                    'Content-Type' => 'application/json',
                    // 'Authorization' => 'Bearer ' . $token,
                ])->get($this->baseUrl . 'Employees/' . $bioMatricId);

                return $apiResponse;
            }
        } catch (\Throwable $th) {
            //throw $th;
        }
    }

    public function storeBioMetricEmployee($loginUserId, $employee)
    {
        // dd("BioMatrix L-46", $loginUserId, $employee?->toArray());
        try {
            if ($employee) {
                $gender = $employee?->gender;
                if ($gender == "Male") {
                    $gender = 0;
                } else if ($gender == "Female") {
                    $gender = 1;
                } else if ($gender == "Other") {
                    $gender = 2;
                }
                $apiResponse = Http::withHeaders([
                    'Content-Type' => 'application/json',
                    // 'Authorization' => 'Bearer ' . $token,
                ])->POST($this->baseUrl . 'Employees', [
                    // "Id" => 0,
                    "Code" => (string) $employee?->id,
                    "DeviceRegisterId" => (string) $employee->aadhar_card_no ?? '',
                    "Name" => $employee?->first_name . ' ' . $employee?->last_name . ' ' . $employee?->father_name ?? '',
                    "Gender" => $gender,
                    "DepartmentId" => 0,
                    "Department" => "",
                    "LocationId" => 0,
                    "Location" => $employee->permanent_address ?? "",
                    "ImagePath" => "",
                    "CreatedBy" => (string) $loginUserId,
                    "CreatedOn" => now()->toIso8601String(),
                    "UpdatedBy" => "",
                    "UpdatedOn" => now()->toIso8601String()
                ]);
                // return $apiResponse;
                // dd("L-77", $apiResponse);
                return json_encode([
                    'status' => true,
                    'data' => $apiResponse
                ]);
            }
        } catch (\Exception $e) {
            return json_encode([
                'status' => false,
                'errors' => $e?->getMessage()
            ]);
        } catch (\Throwable $th) {
            return Redirect::route('software.dashboard')->withErrors($th?->getMessage());
        }
    }

    public function storeBioMetricDepartment($loginUserId, $department, $token)
    {
        // dd("BioMatrix L-96", $loginUserId, $department?->toArray());
        try {
            if ($department) {

                $apiResponse = Http::withHeaders([
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer ' . $token,
                ])->POST($this->baseUrl . 'Department', [
                    "Name" => $department->course_name
                ]);
                // return $apiResponse;
                // dd("L-77", $apiResponse, $token);

                Log::info("Biometric API Response", [
                    'status' => $apiResponse->status(),
                    'body' => $apiResponse->body()
                ]);

                return json_encode([
                    'status' => true,
                    'data' => $apiResponse
                ]);
            }
        } catch (\Exception $e) {
            return json_encode([
                'status' => false,
                'errors' => $e?->getMessage()
            ]);
        } catch (\Throwable $th) {
            return Redirect::route('software.dashboard')->withErrors($th?->getMessage());
        }
    }
}
