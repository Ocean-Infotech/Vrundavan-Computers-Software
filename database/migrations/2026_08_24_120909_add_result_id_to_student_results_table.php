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
        Schema::table('student_results', function (Blueprint $table) {
            $table->unsignedBigInteger('result_id')->nullable()->after('admission_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student_results', function (Blueprint $table) {
            $table->dropColumn('result_id');
        });
    }
};
