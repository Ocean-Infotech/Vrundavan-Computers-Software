<?php

namespace App\Imports;

use App\Models\Admission;
use App\Models\CourceRegistration;
use App\Models\Master\MasterBatch;
use App\Models\Master\MasterClass;
use App\Models\Master\MasterCourse;
use App\Models\Master\MasterDepartment;
use App\Models\Master\MasterShift;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class StudentImport implements ToCollection
{
    public $successCount = 0;
    public $failedCount  = 0;
    public $totalCount   = 0;
    public $errorRows    = [];
    public $duplicateRows = [];

    public function collection(Collection $rows)
    {
        $skipHeader = true;

        foreach ($rows as $key => $row) {

            if ($skipHeader) {
                $skipHeader = false;
                continue;
            }

            if ($row->filter()->isEmpty()) {
                continue;
            }

            // $row = $row->toArray();

            // dd($row);
            $this->totalCount++;





            try {


                $mobile        = $row[13] ?? null;
                $parentMobile  = $row[14] ?? null;
                $otherMobile   = $row[15] ?? null;
                $whatsapp      = $row[16] ?? null;

                $numbers = array_filter([
                    $mobile,
                    $parentMobile,
                    $otherMobile,
                    $whatsapp,
                ], fn($v) => !empty($v) && $v !== "Not Define");

                $numbers = array_unique($numbers);

                $duplicate = Admission::where(function ($q) use ($numbers) {
                    $q->whereIn('mobile_no', $numbers)
                        ->orWhereIn('parent_mobile_no', $numbers)
                        ->orWhereIn('other_mobile_no', $numbers)
                        ->orWhereIn('whatsapp_no', $numbers);
                })->first();

                       
                if ($duplicate) {
                    $this->duplicateRows[] = [
                        'row'     => $key + 1,
                        'numbers' => $numbers,
                    ];
                    $this->failedCount++;
                    continue;
                }

                /**
                 * --------------------------------------------------------
                 * UNIVERSITY VALIDATION
                 * --------------------------------------------------------
                 */
                $excelUniversity = trim($row[22] ?? '');
                $validUniversities = CourceRegistration::$universities;

                if (!in_array($excelUniversity, $validUniversities)) {
                    $this->failedCount++;
                    $this->errorRows[] = [
                        'row'   => $key + 1,
                        'error' => "Invalid University: $excelUniversity",
                    ];
                    continue;
                }

                /**
                 * --------------------------------------------------------
                 * DEPARTMENT VALIDATION
                 * --------------------------------------------------------
                 */
                $departmentName = strtolower(trim($row[23] ?? ''));
                $department = MasterDepartment::whereRaw("LOWER(TRIM(name)) = ?", [$departmentName])->first();

                if (!$department) {
                    $this->failedCount++;
                    $this->errorRows[] = [
                        'row'   => $key + 1,
                        'error' => "Department Not Found: " . ($row[23] ?? ''),
                    ];
                    continue;
                }

                /**
                 * --------------------------------------------------------
                 * DATE OF BIRTH
                 * --------------------------------------------------------
                 */
                $dob = null;

                if (!empty($row[6])) {
                    if (is_numeric($row[6])) {
                        $dob = Carbon::instance(ExcelDate::excelToDateTimeObject($row[6]))
                            ->format('Y-m-d');
                    } else {
                        $dob = Carbon::parse(str_replace('/', '-', trim($row[6])))
                            ->format('Y-m-d');
                    }
                }

                /**
                 * --------------------------------------------------------
                 * CATEGORY CLEANUP
                 * --------------------------------------------------------
                 */
                $category = ($row[9] ?? null);
                if (in_array($category, ['Not Define', 'NotDefine', '-'])) {
                    $category = null;
                }

                /**
                 * --------------------------------------------------------
                 * INSERT ADMISSION
                 * --------------------------------------------------------
                 */
                // dd($row[1]);
                $admission = Admission::create([
                    'gr_no'             => $row[0] ?? null,
                    'aadhar_card_no'    => $row[1] ?? null,
                    'first_name'        => $row[2] ?? null,
                    'last_name'         => $row[3] ?? null,
                    'father_name'       => $row[4] ?? null,
                    'mother_name'       => $row[5] ?? null,
                    'date_of_birth'     => $dob,
                    'gender'            => $row[7] ?? null,
                    'cast'              => $row[8] ?? null,
                    'category'          => $category,
                    'occupation'        => $row[10] ?? null,
                    'temporary_address' => $row[11] ?? null,
                    'permanent_address' => $row[12] ?? null,
                    'mobile_no'         => $mobile,
                    'parent_mobile_no'  => $parentMobile,
                    'other_mobile_no'   => $otherMobile,
                    'whatsapp_no'       => $whatsapp,
                    'email_address'     => $row[17] ?? null,
                    'apaar_id_abc_id'   => $row[18] ?? null,
                    'udise'             => $row[19] ?? null,
                    'enrolment_no'      => $row[19] ?? null,
                    'spid'              => $row[21] ?? null,
                    'status'            => 'Running',
                    'created_by'        => Auth::id(),
                ]);


                /**
                 * --------------------------------------------------------
                 * COURSE / BATCH / CLASS / SHIFT
                 * --------------------------------------------------------
                 */
                $course_name = trim($row[24]);
                $course = MasterCourse::where('course_name', $course_name)->first();
                $course_id = $course?->id;
                $fee       = $course?->course_fees;

                $batch = MasterBatch::where('batch_name', trim($row[25]))
                    ->where('course_id', $course_id)
                    ->first();

                $class = MasterClass::where('class', trim($row[26]))->first();

                $shiftColumn = trim($row[27] ?? '');
                $allowedPattern = '/^\d{1,2}:\d{2}\s?(AM|PM)\s?To\s?\d{1,2}:\d{2}\s?(AM|PM)$/i';

                $shift = null;

                if (preg_match($allowedPattern, $shiftColumn)) {
                    [$from, $to] = array_map('trim', explode('To', $shiftColumn));

                    $from_time = date("H:i:s", strtotime($from));
                    $to_time   = date("H:i:s", strtotime($to));

                    if ($batch && $class) {
                        $shift = MasterShift::where('course_id', $course_id)
                            ->where('batch_id', $batch->id)
                            ->where('class_id', $class->id)
                            ->where('from_time', $from_time)
                            ->where('to_time', $to_time)
                            ->first();
                    }
                }

                /**
                 * --------------------------------------------------------
                 * INSERT COURSE REGISTRATION
                 * --------------------------------------------------------
                 */
                CourceRegistration::create([
                    'register_id'  => $admission->id,
                    'admission_id' => $admission->id,
                    'course_id'    => $course_id,
                    'batch_id'     => $batch?->id,
                    'class_id'     => $class?->id,
                    'university'   => $excelUniversity,
                    'department'   => $department->name,
                    'shift_id'     => $shift?->id,
                    'fee'          => $fee,
                    'date'         => $dob,
                    'note'         => $row[30] ?? null,
                    'status'       => 'Running',
                    'created_by'   => Auth::id(),
                ]);

                $this->successCount++;
            } catch (\Exception $e) {

                $this->failedCount++;

                $this->errorRows[] = [
                    'row'   => $key + 1,
                    'error' => $e->getMessage(),
                ];

                Log::error("Student Import Failed", [
                    'row'   => $key + 1,
                    'data'  => $row->toArray(),
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
                // dd('---------------------------------');
            }
        }
    }
}
