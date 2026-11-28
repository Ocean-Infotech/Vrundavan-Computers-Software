<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contact_us_branches', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('address')->nullable();
            $table->string('email')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->string('status')->default('active');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('contact_us_infos', function (Blueprint $table) {
            $table->id();
            $table->string('group_title')->default('Vrundavan Computer');
            $table->string('college_mobile')->nullable();
            $table->string('school_number')->nullable();
            $table->string('phone')->nullable();
            $table->string('distance')->nullable();
            $table->string('principal')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
        });

        DB::table('contact_us_branches')->insert([
            [
                'name' => 'Keshod Branch',
                'address' => 'Amrut nagar road, Keshod',
                'email' => 'vrundavancomputerskeshod@gmail.com',
                'sort_order' => 1,
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'BCA College',
                'address' => 'PVM Campus, Veraval Road, Keshod',
                'email' => 'pvmsciencecollagekeshod@gmail.com',
                'sort_order' => 2,
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        DB::table('contact_us_infos')->insert([
            'group_title' => 'Vrundavan Computer',
            'college_mobile' => '96874 51774',
            'school_number' => '96246 51774',
            'phone' => '02871-231059',
            'distance' => '',
            'principal' => 'Dr. Hitesh Vadalia - 942774119',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('contact_us_infos');
        Schema::dropIfExists('contact_us_branches');
    }
};
