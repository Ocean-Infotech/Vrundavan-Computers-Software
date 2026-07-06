<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('issue_certificate_histories', function (Blueprint $table) {
            $table->id();
            $table->string('certificate_type', 20);
            $table->unsignedBigInteger('register_id');
            $table->string('student_name')->nullable();
            $table->string('issue_date')->nullable();
            $table->string('academic_year')->nullable();
            $table->string('semester_start')->nullable();
            $table->string('semester_end')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('issue_certificate_histories');
    }
};
