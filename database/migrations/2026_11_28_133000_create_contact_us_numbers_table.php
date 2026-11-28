<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contact_us_numbers', function (Blueprint $table) {
            $table->id();
            $table->string('label');
            $table->string('mobile')->nullable();
            $table->string('status')->default('active');
            $table->unsignedInteger('sort_order')->default(0);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        $info = DB::table('contact_us_infos')->first();
        $now = now();
        $rows = [];

        if ($info) {
            if (trim((string) ($info->college_mobile ?? '')) !== '') {
                $rows[] = [
                    'label' => 'College mobile',
                    'mobile' => $info->college_mobile,
                    'status' => 'active',
                    'sort_order' => 1,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
            if (trim((string) ($info->school_number ?? '')) !== '') {
                $rows[] = [
                    'label' => 'School Mobile',
                    'mobile' => $info->school_number,
                    'status' => 'active',
                    'sort_order' => 2,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
            if (trim((string) ($info->phone ?? '')) !== '') {
                $rows[] = [
                    'label' => 'Phone',
                    'mobile' => $info->phone,
                    'status' => 'active',
                    'sort_order' => 3,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
            if (trim((string) ($info->principal ?? '')) !== '') {
                $rows[] = [
                    'label' => 'Principal',
                    'mobile' => $info->principal,
                    'status' => 'active',
                    'sort_order' => 4,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        if (empty($rows)) {
            $rows = [
                [
                    'label' => 'College mobile',
                    'mobile' => '96874 51774',
                    'status' => 'active',
                    'sort_order' => 1,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'label' => 'School Mobile',
                    'mobile' => '96246 51774',
                    'status' => 'active',
                    'sort_order' => 2,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'label' => 'Phone',
                    'mobile' => '02871-231059',
                    'status' => 'active',
                    'sort_order' => 3,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'label' => 'Principal',
                    'mobile' => 'Dr. Hitesh Vadalia - 942774119',
                    'status' => 'active',
                    'sort_order' => 4,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
            ];
        }

        DB::table('contact_us_numbers')->insert($rows);
    }

    public function down(): void
    {
        Schema::dropIfExists('contact_us_numbers');
    }
};
