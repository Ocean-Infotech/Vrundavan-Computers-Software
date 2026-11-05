<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hsc_marksheets', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('registration_id')->nullable();
            $table->string('exam_title')->default('FIRST TERM EXAMINATION 2026 - 27');
            $table->string('academic_year')->default('2026 - 27');
            $table->string('standard')->default('11th Commerce');
            $table->string('roll_number')->nullable();
            $table->json('marks')->nullable();
            $table->decimal('total_obtained', 8, 2)->default(0);
            $table->decimal('total_maximum', 8, 2)->default(400);
            $table->decimal('percentage', 8, 2)->nullable();
            $table->string('grade')->nullable();
            $table->string('rank')->nullable();
            $table->string('result')->nullable();
            $table->string('status')->default('active');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['student_id', 'exam_title']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hsc_marksheets');
    }
};
