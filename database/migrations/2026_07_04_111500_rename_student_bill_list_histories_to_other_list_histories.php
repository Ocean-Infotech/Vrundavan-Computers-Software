<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('student_bill_list_histories') && !Schema::hasTable('other_list_histories')) {
            Schema::rename('student_bill_list_histories', 'other_list_histories');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('other_list_histories') && !Schema::hasTable('student_bill_list_histories')) {
            Schema::rename('other_list_histories', 'student_bill_list_histories');
        }
    }
};
