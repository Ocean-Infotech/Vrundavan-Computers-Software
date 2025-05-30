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
        Schema::table('attedance', function (Blueprint $table) {
            $table->string('course_id')->after('admission_id');
            $table->integer('batch_id')->after('course_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attedance', function (Blueprint $table) {
            //
        });
    }
};
