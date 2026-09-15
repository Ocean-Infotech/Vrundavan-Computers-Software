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
        Schema::table('requests', function (Blueprint $table) {
            if (!Schema::hasColumn('requests', 'subject')) {
                $table->string('subject')->nullable()->after('student_id');
            }
            if (!Schema::hasColumn('requests', 'response')) {
                $table->text('response')->nullable()->after('detail');
            }
            if (!Schema::hasColumn('requests', 'status')) {
                $table->enum('status', ['Pending', 'Completed'])->default('Pending')->after('response');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('requests', function (Blueprint $table) {
            if (Schema::hasColumn('requests', 'response')) {
                $table->dropColumn('response');
            }
            if (Schema::hasColumn('requests', 'status')) {
                $table->dropColumn('status');
            }
        });
    }
};
