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
        Schema::create('course_register', function (Blueprint $table) {
            $table->id();
            $table->string('admission_id')->comment('Register No.');
            $table->string('course_id');
            $table->string('batch_id');
            $table->string('fee');
            $table->date('date');
            $table->text('note');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.  attedance
     */
    public function down(): void
    {
        Schema::dropIfExists('course_register');
    }
};
