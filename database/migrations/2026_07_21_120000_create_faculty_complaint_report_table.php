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
        Schema::create('faculty_complaint_report', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('admission_id')->nullable();
            $table->string('gr_no')->nullable();
            $table->date('date')->nullable();
            $table->text('complaint')->nullable();
            $table->unsignedBigInteger('faculty_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('faculty_complaint_report');
    }
};
