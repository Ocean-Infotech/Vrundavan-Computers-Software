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
        Schema::create('other_list_histories', function (Blueprint $table) {
            $table->id();
            $table->string('page_heading')->nullable();
            $table->string('course_id')->nullable();
            $table->string('batch_id')->nullable();
            $table->string('semester_id')->nullable();
            $table->string('action_type')->nullable();
            $table->integer('student_count')->default(0);
            $table->string('created_by')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('other_list_histories');
    }
};
