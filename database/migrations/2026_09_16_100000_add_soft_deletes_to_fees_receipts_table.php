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
        Schema::table('fees_receipts', function (Blueprint $table) {
            try {
                $table->dropUnique('fees_receipts_receipt_no_unique');
            } catch (\Exception $e) {
                // Ignore if unique index was already dropped
            }
            $table->string('deleted_by')->nullable()->after('receipt_no');
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fees_receipts', function (Blueprint $table) {
            $table->dropColumn('deleted_by');
            $table->dropSoftDeletes();
        });
    }
};
