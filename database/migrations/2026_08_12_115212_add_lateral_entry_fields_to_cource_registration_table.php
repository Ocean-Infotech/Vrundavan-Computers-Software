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
            $table->boolean('is_lateral_entry')->default(0)->after('note')->comment('0 = Regular Student, 1 = Lateral Admission (joined in-between)');
            $table->integer('joining_semester')->nullable()->after('is_lateral_entry');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cource_registration', function (Blueprint $table) {
            $table->dropColumn(['is_lateral_entry', 'joining_semester']);
        });
    }
};
