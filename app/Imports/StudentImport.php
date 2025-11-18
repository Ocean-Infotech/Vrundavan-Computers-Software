<?php

    namespace App\Imports;

    use App\Models\Admission;
    use App\Models\CourceRegistration;
    use App\Models\Master\MasterDepartment;
    use App\Models\Master\MasterCourse;
    use App\Models\Master\MasterBatch;
    use App\Models\Master\MasterClass;
    use App\Models\Master\MasterShift;
    use Illuminate\Support\Collection;
    use Maatwebsite\Excel\Concerns\ToCollection;
    use Maatwebsite\Excel\Concerns\WithHeadingRow;
    use Carbon\Carbon;
    use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

    class StudentImport implements ToCollection, WithHeadingRow
    {
        public $report = [
            'inserted' => [],
            'skipped'  => [],
            'errors'   => [],
        ];

        // required normalized fields (declare at class scope)
        protected $requiredFields = [
            'id',
            'aadhaar',
            'surname',
            'name',
            'father_name',
            'mobile_no',
            // newly required fields (make them required by including here)
            'university',
            'department',
            'course',
            'batch',
            'class',
            'course_fee',
            'joining_date',
            // shift is required by default here; remove if optional
            'shift',
        ];

        /**
         * Normalize numeric fee strings: remove non-digit and non-dot characters, return float or null.
         */
        protected function normalizeFee($value)
        {
            if ($value === null || trim((string)$value) === '') {
                return null;
            }
            $clean = preg_replace('/[^\d.\-]/u', '', (string)$value);
            if ($clean === '' || $clean === '.' || $clean === '-') {
                return null;
            }
            return (float)$clean;
        }

        /**
         * Parse Excel / text date to Y-m-d or return null.
         */
        protected function parseDateToYmd($raw)
        {
            if ($raw === null || trim((string)$raw) === '') {
                return null;
            }
            // If numeric (Excel serial)
            if (is_numeric($raw)) {
                try {
                    $dt = ExcelDate::excelToDateTimeObject($raw);
                    return $dt->format('Y-m-d');
                } catch (\Exception $e) {
                    return null;
                }
            }

            // Try Carbon parse for text dates
            try {
                return Carbon::parse($raw)->format('Y-m-d');
            } catch (\Exception $e) {
                return null;
            }
        }

        /**
         * Parse a shift range string like "07:00 AM To 12:00 PM" to array with from/to in H:i:s
         * returns ['from' => '07:00:00', 'to' => '12:00:00'] or null
         */
        protected function parseShiftRange($raw)
        {
            if ($raw === null) {
                return null;
            }
            $s = trim((string)$raw);
            if ($s === '') {
                return null;
            }

            // normalize separators: allow "to", "–", "-", "—"
            $s = preg_replace('/\s*(to|–|—|-|to)\s*/i', ' to ', $s);
            // split around ' to '
            $parts = preg_split('/\s+to\s+/i', $s);
            if (!$parts || count($parts) < 2) {
                // try other separators
                $parts = preg_split('/\s*[-–—]\s*/u', $s);
            }
            if (!$parts || count($parts) < 2) {
                return null;
            }

            $fromRaw = trim($parts[0]);
            $toRaw = trim($parts[1]);

            // try parse with Carbon using common time formats
            $formats = ['g:i A', 'h:i A', 'G:i', 'H:i', 'g:iA', 'h:iA'];

            $from = null;
            $to = null;
            foreach ($formats as $fmt) {
                try {
                    $dtf = Carbon::createFromFormat($fmt, $fromRaw);
                    $from = $dtf->format('H:i:s');
                    break;
                } catch (\Exception $e) {
                    // continue
                }
            }
            foreach ($formats as $fmt) {
                try {
                    $dtt = Carbon::createFromFormat($fmt, $toRaw);
                    $to = $dtt->format('H:i:s');
                    break;
                } catch (\Exception $e) {
                    // continue
                }
            }

            // fallback: let Carbon try generic parse
            if ($from === null) {
                try {
                    $dtf = Carbon::parse($fromRaw);
                    $from = $dtf->format('H:i:s');
                } catch (\Exception $e) {
                    $from = null;
                }
            }
            if ($to === null) {
                try {
                    $dtt = Carbon::parse($toRaw);
                    $to = $dtt->format('H:i:s');
                } catch (\Exception $e) {
                    $to = null;
                }
            }

            if ($from && $to) {
                return ['from' => $from, 'to' => $to];
            }
            return null;
        }

        public function collection(Collection $rows)
        {
            // Preload allowed departments once
            $allowedDepartmentsRaw = MasterDepartment::pluck('name')->toArray();
            $allowedDepartments = array_map(function ($v) {
                return mb_strtolower(trim((string)$v));
            }, $allowedDepartmentsRaw);

            // Preload allowed universities from CourceRegistration::$universities (if provided)
            // Preload allowed universities from master_universities table
            $allowedUniversitiesRaw = \App\Models\Master\MasterUniversity::pluck('name')->toArray();

            $allowedUniversities = array_map(function ($v) {
                return mb_strtolower(trim((string)$v));
            }, $allowedUniversitiesRaw);


            // Preload courses: normalized name => [id, fee]
            $courses = MasterCourse::select('id', 'course_name', 'course_fees')->get();
            $courseNameToInfo = [];
            foreach ($courses as $c) {
                $norm = mb_strtolower(trim((string)$c->course_name));
                $courseNameToInfo[$norm] = [
                    'id' => $c->id,
                    'fee' => $this->normalizeFee($c->course_fees),
                ];
            }

            // Preload batches grouped by course_id
            $batches = MasterBatch::select('id', 'course_id', 'batch_name')->get();
            $batchesByCourse = [];
            foreach ($batches as $b) {
                $courseId = $b->course_id;
                $normBatch = mb_strtolower(trim((string)$b->batch_name));
                if (!isset($batchesByCourse[$courseId])) {
                    $batchesByCourse[$courseId] = [];
                }
                $batchesByCourse[$courseId][$normBatch] = $b->id;
            }

            // Preload classes: normalized name => id
            $classes = MasterClass::select('id', 'class')->get();
            $classNameToId = [];
            foreach ($classes as $cl) {
                $rawName = $cl->getAttribute('class');
                $norm = mb_strtolower(trim((string)$rawName));
                $classNameToId[$norm] = $cl->id;
            }

            // Preload shifts: map by course->batch->class->"from|to" => id
            $shifts = MasterShift::select('id', 'course_id', 'batch_id', 'class_id', 'from_time', 'to_time')->get();
            $shiftsMap = [];
            foreach ($shifts as $s) {
                $cId = $s->course_id;
                $bId = $s->batch_id;
                $clId = $s->class_id;
                // normalize times to H:i:s (assume stored as time strings)
                $from = null;
                $to = null;
                try {
                    $from = Carbon::parse($s->from_time)->format('H:i:s');
                } catch (\Exception $e) {
                    $from = trim((string)$s->from_time);
                }
                try {
                    $to = Carbon::parse($s->to_time)->format('H:i:s');
                } catch (\Exception $e) {
                    $to = trim((string)$s->to_time);
                }
                $key = $from . '|' . $to;
                if (!isset($shiftsMap[$cId])) {
                    $shiftsMap[$cId] = [];
                }
                if (!isset($shiftsMap[$cId][$bId])) {
                    $shiftsMap[$cId][$bId] = [];
                }
                if (!isset($shiftsMap[$cId][$bId][$clId])) {
                    $shiftsMap[$cId][$bId][$clId] = [];
                }
                $shiftsMap[$cId][$bId][$clId][$key] = $s->id;
            }

            foreach ($rows as $index => $row) {
                $rawRow = $row->toArray();

                // --- Normalize / Map possible heading variants ---
                $id = $row['gr_no'] ?? $row['grno'] ?? $row['id'] ?? null;
                $aadhaar = $row['aadhaar_card_no'] ?? $row['aadhaar'] ?? $row['aadhar_card_no'] ?? null;
                $surname = $row['surname'] ?? $row['last_name'] ?? $row['lastname'] ?? null;
                $name = $row['name'] ?? $row['first_name'] ?? $row['firstname'] ?? null;
                $fatherName = $row["father's_name"] ?? $row['fathers_name'] ?? $row['father_name'] ?? $row['father'] ?? null;
                $motherName = $row["mother's_name"] ?? $row['mothers_name'] ?? $row['mother_name'] ?? null;
                $mobileNo = $row['mobile_no'] ?? $row['mobile'] ?? $row['mobile no'] ?? null;

                // optional fields
                $temporaryAddress = $row['temporary_address'] ?? $row['temp_address'] ?? null;
                $permanentAddress = $row['permanent_address'] ?? $row['per_address'] ?? null;
                $otherMobileNo = $row['other_mobile_no'] ?? $row['other_mobile'] ?? null;
                $whatsappNo = $row['whatsapp_no'] ?? $row['whatsapp'] ?? null;
                $emailAddress = $row['email_address'] ?? $row['email'] ?? null;
                $apaarId = $row['apaar_id_abc_id'] ?? $row['apaar_id'] ?? null;
                $udise = $row['udise'] ?? null;
                $enrolmentNo = $row['enrolment_no'] ?? $row['enrollment_no'] ?? null;
                $spid = $row['spid'] ?? null;
                $dobRaw = $row['date_of_birth'] ?? $row['dob'] ?? null;
                $gender = $row['gender'] ?? null;
                $cast = $row['cast'] ?? $row['caste'] ?? null;
                $category = $row['category'] ?? null;

                // --- Joining Date raw (accept multiple headings) ---
                $joiningDateRaw = $row['joining_date'] ?? $row['join_date'] ?? $row['admission_date'] ?? $row['joining'] ?? null;
                $joiningDate = $this->parseDateToYmd($joiningDateRaw);

                // --- Note / Remarks ---
                $note = $row['note'] ?? $row['notes'] ?? $row['remark'] ?? $row['remarks'] ?? null;

                // --- Course handling (must match MasterCourse) ---
                $courseRaw = $row['course'] ?? $row['course_name'] ?? null;
                $courseId = null;
                $masterCourseFee = null;
                if (!is_null($courseRaw) && trim((string)$courseRaw) !== '') {
                    $normCourse = mb_strtolower(trim((string)$courseRaw));
                    if (isset($courseNameToInfo[$normCourse])) {
                        $courseId = $courseNameToInfo[$normCourse]['id'];
                        $masterCourseFee = $courseNameToInfo[$normCourse]['fee']; // numeric or null
                    } else {
                        $this->report['skipped'][] = [
                            'row' => $index + 1,
                            'id'  => $id,
                            'reason' => 'Invalid course: ' . (string)$courseRaw,
                            'raw' => $rawRow,
                        ];
                        continue;
                    }
                }

                // --- Batch handling: ensure batch exists for the matched course ---
                $batchRaw = $row['batch'] ?? $row['batch_name'] ?? null;
                $batchId = null;
                if (!is_null($batchRaw) && trim((string)$batchRaw) !== '') {
                    if (empty($courseId)) {
                        $this->report['skipped'][] = [
                            'row' => $index + 1,
                            'id'  => $id,
                            'reason' => 'Batch provided but course not resolved; cannot match batch without course.',
                            'raw' => $rawRow,
                        ];
                        continue;
                    }
                    $normBatch = mb_strtolower(trim((string)$batchRaw));
                    if (isset($batchesByCourse[$courseId]) && isset($batchesByCourse[$courseId][$normBatch])) {
                        $batchId = $batchesByCourse[$courseId][$normBatch];
                    } else {
                        $this->report['skipped'][] = [
                            'row' => $index + 1,
                            'id'  => $id,
                            'reason' => 'Invalid batch for course: ' . (string)$batchRaw . ' (course: ' . (string)$courseRaw . ')',
                            'raw' => $rawRow,
                        ];
                        continue;
                    }
                }

                // --- Class handling: ensure class exists in master_class ---
                $classRaw = $row['class'] ?? $row['class_name'] ?? $row['class_id'] ?? null;
                $classId = null;
                if (!is_null($classRaw) && trim((string)$classRaw) !== '') {
                    $normClass = mb_strtolower(trim((string)$classRaw));
                    if (isset($classNameToId[$normClass])) {
                        $classId = $classNameToId[$normClass];
                    } else {
                        $this->report['skipped'][] = [
                            'row' => $index + 1,
                            'id'  => $id,
                            'reason' => 'Invalid class: ' . (string)$classRaw,
                            'raw' => $rawRow,
                        ];
                        continue;
                    }
                }

                // --- Shift handling (parse and resolve MasterShift id) ---
                $shiftRaw = $row['shift'] ?? $row['shift_time'] ?? $row['shift_range'] ?? null;
                $shiftId = null;
                if (!is_null($shiftRaw) && trim((string)$shiftRaw) !== '') {
                    // require course, batch, class to be resolved first
                    if (empty($courseId) || empty($batchId) || empty($classId)) {
                        $this->report['skipped'][] = [
                            'row' => $index + 1,
                            'id'  => $id,
                            'reason' => 'Shift provided but course/batch/class not resolved; cannot match shift.',
                            'raw' => $rawRow,
                        ];
                        continue;
                    }

                    $parsedShift = $this->parseShiftRange($shiftRaw);
                    if ($parsedShift === null) {
                        $this->report['skipped'][] = [
                            'row' => $index + 1,
                            'id'  => $id,
                            'reason' => 'Could not parse shift range: ' . (string)$shiftRaw,
                            'raw' => $rawRow,
                        ];
                        continue;
                    }

                    $key = $parsedShift['from'] . '|' . $parsedShift['to'];
                    if (isset($shiftsMap[$courseId][$batchId][$classId][$key])) {
                        $shiftId = $shiftsMap[$courseId][$batchId][$classId][$key];
                    } else {
                        // not found exact match — try loosening match by ignoring seconds or by matching by hour:minute
                        $tryKey = substr($parsedShift['from'], 0, 5) . '|' . substr($parsedShift['to'], 0, 5);
                        // search keys
                        $found = null;
                        if (isset($shiftsMap[$courseId][$batchId][$classId])) {
                            foreach ($shiftsMap[$courseId][$batchId][$classId] as $k => $sid) {
                                $parts = explode('|', $k);
                                $kf = substr($parts[0], 0, 5);
                                $kt = substr($parts[1], 0, 5);
                                if ($kf . '|' . $kt === $tryKey) {
                                    $found = $sid;
                                    break;
                                }
                            }
                        }
                        if ($found) {
                            $shiftId = $found;
                        } else {
                            $this->report['skipped'][] = [
                                'row' => $index + 1,
                                'id'  => $id,
                                'reason' => 'No matching MasterShift found for: ' . (string)$shiftRaw,
                                'raw' => $rawRow,
                            ];
                            continue;
                        }
                    }
                }

                // --- Course fee validation (if fee provided in Excel) ---
                $excelFeeRaw = $row['course_fees'] ?? $row['course_fee'] ?? $row['fee'] ?? null;
                $excelFee = $this->normalizeFee($excelFeeRaw);
                if (!is_null($excelFee)) {
                    if ($masterCourseFee === null) {
                        $this->report['skipped'][] = [
                            'row' => $index + 1,
                            'id'  => $id,
                            'reason' => 'Master course fee missing for course: ' . (string)$courseRaw,
                            'raw' => $rawRow,
                        ];
                        continue;
                    }
                    if (abs($excelFee - $masterCourseFee) > 0.001) {
                        $this->report['skipped'][] = [
                            'row' => $index + 1,
                            'id'  => $id,
                            'reason' => 'Invalid course fee: provided ' . $excelFee . ' but master fee is ' . $masterCourseFee,
                            'raw' => $rawRow,
                        ];
                        continue;
                    }
                }

                // --- University & Department handling ---
                $universityRaw = $row['university'] ?? $row['university_name'] ?? null;
                $university = null;
                if (!is_null($universityRaw) && trim((string)$universityRaw) !== '') {
                    $normUni = mb_strtolower(trim((string)$universityRaw));

                    $searchList = array_map(function ($v) {
                        return mb_strtolower(trim((string)$v));
                    }, $allowedUniversitiesRaw);

                    $key = array_search($normUni, $searchList, true);

                    if ($key !== false && isset($allowedUniversitiesRaw[$key])) {
                        $university = trim((string)$allowedUniversitiesRaw[$key]);  // correct DB-cased name
                    } else {
                        $university = null;
                    }
                }


                $departmentRaw = $row['department'] ?? $row['dept'] ?? null;
                $department = null;
                if (!is_null($departmentRaw) && trim((string)$departmentRaw) !== '') {
                    $normDept = mb_strtolower(trim((string)$departmentRaw));
                    if (in_array($normDept, $allowedDepartments, true)) {
                        $key = array_search($normDept, array_map(function ($v) {
                            return mb_strtolower(trim((string)$v));
                        }, $allowedDepartmentsRaw), true);
                        if ($key !== false && isset($allowedDepartmentsRaw[$key])) {
                            $department = trim((string)$allowedDepartmentsRaw[$key]);
                        } else {
                            $department = trim((string)$departmentRaw);
                        }
                    } else {
                        $department = null;
                    }
                }

                // --- Required fields check (uses resolved values where appropriate) ---
                $missing = [];

                // Basic required fields (original)
                foreach (['id', 'aadhaar', 'surname', 'name', 'father_name', 'mobile_no'] as $field) {
                    $val = null;
                    switch ($field) {
                        case 'id':
                            $val = $id;
                            break;
                        case 'aadhaar':
                            $val = $aadhaar;
                            break;
                        case 'surname':
                            $val = $surname;
                            break;
                        case 'name':
                            $val = $name;
                            break;
                        case 'father_name':
                            $val = $fatherName;
                            break;
                        case 'mobile_no':
                            $val = $mobileNo;
                            break;
                    }
                    if (is_null($val) || trim((string)$val) === '') {
                        $missing[] = $field;
                    }
                }

                // Domain-specific required checks (use resolved variables)
                // University required check with detailed error
                // University required check with detailed error
                if (in_array('university', $this->requiredFields, true)) {

                    if (!is_null($universityRaw) && trim((string)$universityRaw) !== '') {

                        // If universityRaw given but NOT found in master list
                        $normUni = mb_strtolower(trim((string)$universityRaw));
                        $searchList = array_map(fn($v) => mb_strtolower(trim((string)$v)), $allowedUniversitiesRaw);

                        if (!in_array($normUni, $searchList, true)) {
                            // Specific error for invalid university
                            $this->report['skipped'][] = [
                                'row' => $index + 1,
                                'id'  => $id,
                                'reason' => 'Invalid university: "' . $universityRaw . '" (not found in master_universities)',
                                'raw' => $rawRow,
                            ];
                            continue;
                        }
                    }

                    // University empty
                    if (is_null($university) || trim((string)$university) === '') {
                        $missing[] = 'university';
                    }
                }



                /** -------- Department check (with master validation) -------- */
                if (in_array('department', $this->requiredFields, true)) {

                    if (!is_null($departmentRaw) && trim((string)$departmentRaw) !== '') {

                        $normDept = mb_strtolower(trim((string)$departmentRaw));
                        $searchDept = array_map(fn($v) => mb_strtolower(trim((string)$v)), $allowedDepartmentsRaw);

                        if (!in_array($normDept, $searchDept, true)) {
                            // Department given but invalid
                            $this->report['skipped'][] = [
                                'row'   => $index + 1,
                                'id'    => $id,
                                'reason' => 'Invalid department: "' . $departmentRaw . '" (not found in master_departments)',
                                'raw'   => $rawRow,
                            ];
                            continue;
                        }
                    }

                    if (is_null($department) || trim((string)$department) === '') {
                        $missing[] = 'department';
                    }
                }


                /** -------- Course check -------- */
                if (in_array('course', $this->requiredFields, true)) {

                    if (!is_null($courseRaw) && trim((string)$courseRaw) !== '') {
                        $normCourse = mb_strtolower(trim((string)$courseRaw));

                        if (!isset($courseNameToInfo[$normCourse])) {
                            $this->report['skipped'][] = [
                                'row' => $index + 1,
                                'id'  => $id,
                                'reason' => 'Invalid course: "' . $courseRaw . '" (not found in master_courses)',
                                'raw' => $rawRow,
                            ];
                            continue;
                        }
                    }

                    if (empty($courseId)) {
                        $missing[] = 'course';
                    }
                }


                /** -------- Batch check -------- */
                if (in_array('batch', $this->requiredFields, true)) {

                    if (!is_null($batchRaw) && trim((string)$batchRaw) !== '' && !empty($courseId)) {
                        $normBatch = mb_strtolower(trim((string)$batchRaw));

                        if (!isset($batchesByCourse[$courseId][$normBatch])) {
                            $this->report['skipped'][] = [
                                'row' => $index + 1,
                                'id'  => $id,
                                'reason' => 'Invalid batch: "' . $batchRaw . '" for course "' . $courseRaw . '"',
                                'raw'  => $rawRow,
                            ];
                            continue;
                        }
                    }

                    if (empty($batchId)) {
                        $missing[] = 'batch';
                    }
                }


                /** -------- Class check -------- */
                if (in_array('class', $this->requiredFields, true)) {

                    if (!is_null($classRaw) && trim((string)$classRaw) !== '') {
                        $normClass = mb_strtolower(trim((string)$classRaw));

                        if (!isset($classNameToId[$normClass])) {
                            $this->report['skipped'][] = [
                                'row' => $index + 1,
                                'id'  => $id,
                                'reason' => 'Invalid class: "' . $classRaw . '" (not found in master_classes)',
                                'raw' => $rawRow,
                            ];
                            continue;
                        }
                    }

                    if (empty($classId)) {
                        $missing[] = 'class';
                    }
                }


                /** -------- Course fee -------- */
                if (in_array('course_fee', $this->requiredFields, true)) {
                    if (is_null($excelFee) && is_null($masterCourseFee)) {
                        $missing[] = 'course_fee';
                    }
                }


                /** -------- Joining date -------- */
                if (in_array('joining_date', $this->requiredFields, true)) {
                    if (is_null($joiningDate)) {
                        $missing[] = 'joining_date';
                    }
                }


                /** -------- Shift check -------- */
                if (in_array('shift', $this->requiredFields, true)) {

                    if (!is_null($shiftRaw) && trim((string)$shiftRaw) !== '' && $shiftId === null) {
                        $this->report['skipped'][] = [
                            'row' => $index + 1,
                            'id'  => $id,
                            'reason' => 'Invalid shift: "' . $shiftRaw . '" (no matching MasterShift found)',
                            'raw' => $rawRow,
                        ];
                        continue;
                    }

                    if (is_null($shiftId)) {
                        $missing[] = 'shift';
                    }
                }


                if (!empty($missing)) {
                    $this->report['skipped'][] = [
                        'row' => $index + 1,
                        'id'  => $id,
                        'reason' => 'Missing required fields: ' . implode(', ', $missing),
                        'raw' => $rawRow,
                    ];
                    continue;
                }

                // If record exists → skip
                if (Admission::find($id)) {
                    $this->report['skipped'][] = [
                        'row' => $index + 1,
                        'id'  => $id,
                        'reason' => 'ID already exists',
                        'raw' => $rawRow,
                    ];
                    continue;
                }

                // --- Parse DOB ---
                $date_of_birth = $this->parseDateToYmd($dobRaw);

                // --- Insert Admission + CourceRegistration ---
                try {
                    Admission::create([
                        'id'               => $id,
                        'aadhar_card_no'   => $aadhaar,
                        'last_name'        => $surname,
                        'first_name'       => $name,
                        'father_name'      => $fatherName,
                        'mother_name'      => $motherName,
                        'temporary_address' => $temporaryAddress,
                        'permanent_address' => $permanentAddress,
                        'mobile_no'        => $mobileNo,
                        'other_mobile_no'  => $otherMobileNo,
                        'whatsapp_no'      => $whatsappNo,
                        'email_address'    => $emailAddress,
                        'apaar_id_abc_id'  => $apaarId,
                        'udise'            => $udise,
                        'enrolment_no'     => $enrolmentNo,
                        'spid'             => $spid,
                        'date_of_birth'    => $date_of_birth,
                        'gender'           => $gender,
                        'cast'             => $cast,
                        'category'         => $category,
                    ]);

                    $this->report['inserted'][] = [
                        'row' => $index + 1,
                        'id'  => $id,
                    ];

                    try {
                        // choose fee to store: excelFee if provided else masterCourseFee
                        $feeToStore = !is_null($excelFee) ? $excelFee : $masterCourseFee;

                        CourceRegistration::create([
                            'register_id'  => $id,
                            'admission_id' => $id,
                            'university'   => $university,
                            'department'   => $department,
                            'course_id'    => $courseId,
                            'batch_id'     => $batchId,
                            'class_id'     => $classId,
                            'shift_id'     => $shiftId,
                            'fee'          => $feeToStore,
                            'date'         => $joiningDate, // Joining Date (Y-m-d) or null
                            'note'         => $note,        // Note / Remarks
                        ]);
                    } catch (\Exception $e) {
                        $this->report['errors'][] = [
                            'row' => $index + 1,
                            'id'  => $id,
                            'message' => 'CourceRegistration create failed: ' . $e->getMessage(),
                            'raw' => $rawRow,
                        ];
                    }
                } catch (\Exception $ex) {
                    $this->report['errors'][] = [
                        'row' => $index + 1,
                        'id' => $id,
                        'message' => $ex->getMessage(),
                        'raw' => $rawRow,
                    ];
                }
            }
        }
    }
