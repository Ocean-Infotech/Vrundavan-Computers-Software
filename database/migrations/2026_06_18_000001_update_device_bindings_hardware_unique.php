<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Device-Level Approval Migration
 *
 * Previously: unique(user_id, hardware_id) — each user needed their own device approval.
 * New:        unique(hardware_id) — one device approval allows ALL users on that machine.
 *
 * user_id is now nullable (stores the first registrant for audit only).
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('device_bindings', function (Blueprint $table) {
            // 1. Drop foreign key FIRST (required before dropping the unique index it's part of)
            $table->dropForeign(['user_id']);

            // 2. Drop old composite unique constraint (user_id + hardware_id)
            $table->dropUnique(['user_id', 'hardware_id']);

            // 3. Make user_id nullable (stores first registrant for audit only)
            $table->unsignedBigInteger('user_id')->nullable()->change();

            // 4. Re-add foreign key as nullable
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');

            // 5. hardware_id alone must be unique — one record per physical device
            $table->unique('hardware_id');
        });
    }

    public function down(): void
    {
        Schema::table('device_bindings', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropUnique(['hardware_id']);
            $table->unsignedBigInteger('user_id')->nullable(false)->change();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->unique(['user_id', 'hardware_id']);
        });
    }
};
