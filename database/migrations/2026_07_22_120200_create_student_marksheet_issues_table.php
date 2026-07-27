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
        Schema::create('student_marksheet_issues', function (Blueprint $table) {
            $table->id();
            $table->integer('admission_id');
            $table->string('gr_no')->nullable();
            $table->date('date')->nullable();
            $table->string('series')->nullable();
            $table->text('note')->nullable();
            $table->integer('semester');
            $table->string('created_by')->nullable();
            $table->string('updated_by')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_marksheet_issues');
    }
};
