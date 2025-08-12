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
        Schema::create('cource_registration', function (Blueprint $table) {
            $table->id();
            $table->string('register_id');
            $table->string('course_id');
            $table->string('batch_id');
            $table->string('class_id');
            $table->string('shift_id');
            $table->string('admission_id')->nullable();
            $table->string('university');
            $table->string('department');
            $table->string('fee');
            $table->date('date');
            $table->text('note');

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
        Schema::dropIfExists('cource_registration');
    }
};
