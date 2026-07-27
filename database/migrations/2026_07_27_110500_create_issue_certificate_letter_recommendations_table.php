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
        Schema::create('issue_certificate_letter_recommendations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('admission_id');
            $table->string('student_name')->nullable();
            $table->string('recommender_type')->default('principal');
            $table->unsignedBigInteger('faculty_id')->nullable();
            $table->string('recommender_name')->nullable();
            $table->string('recommender_designation')->nullable();
            $table->string('issue_date')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('issue_certificate_letter_recommendations');
    }
};
