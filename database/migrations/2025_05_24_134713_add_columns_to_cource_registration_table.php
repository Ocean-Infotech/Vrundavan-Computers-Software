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
        Schema::table('cource_registration', function (Blueprint $table) {
            $table->string('batch_id')->after('course_id');
            $table->string('class_id')->after('batch_id');
            $table->string('shift_id')->after('class_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cource_registration', function (Blueprint $table) {
            //
        });
    }
};
