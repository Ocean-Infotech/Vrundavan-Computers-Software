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
        Schema::create('attedance', function (Blueprint $table) {
            $table->id();
            $table->string('admission_id');
            $table->string('course_id');
            $table->integer('batch_id');
            $table->date('date');
            $table->time('in_time')->nullable();
            $table->time('out_time')->nullable();
            $table->dateTime('leave')->nullable();
            $table->text('DeviceKey')->nullable();
            $table->text('DeviceName')->nullable();
            $table->text('UserId')->nullable();
            $table->text('EmpCode')->nullable();
            $table->string('UserName')->nullable();
            $table->dateTime('IOTime')->nullable();
            $table->text('IOMode')->nullable();
            $table->text('VerifyMode')->nullable();
            $table->text('WorkCode')->nullable();
            $table->text('ImagePath')->nullable();
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
        Schema::dropIfExists('attedance');
    }
};
