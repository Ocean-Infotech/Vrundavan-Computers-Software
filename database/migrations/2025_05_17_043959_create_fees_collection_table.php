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
        Schema::create('fees_collections', function (Blueprint $table) {
            $table->id();
            $table->string('student_id');
            $table->string('admission_id');
            $table->string('course_id');

            $table->string('student_name');
            $table->string('year_semester');
            $table->date('date');
            $table->string('fees');

            $table->string('mode');
            $table->string('upi_id')->nullable();
            $table->string('cheque_no')->nullable();
            $table->string('return_reason')->nullable();
            $table->string('password')->nullable();
             $table->tinyInteger('checked_status')->default(0)->after('password')->comment('0 = unchecked, 1 = checked');
            $table->string('status')->default('active')->comment('active, inactive');
            $table->string('created_by')->nullable();
            $table->string('updated_by')->nullable();
            $table->timestamps();
            $table->string('deleted_by')->nullable();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fees_collections');
    }
};
