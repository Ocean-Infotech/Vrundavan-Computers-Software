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
        if (Schema::hasTable('device_bindings')) {
            Schema::table('device_bindings', function (Blueprint $table) {
                if (!Schema::hasColumn('device_bindings', 'latitude')) {
                    $table->string('latitude')->nullable()->after('status');
                }
                if (!Schema::hasColumn('device_bindings', 'longitude')) {
                    $table->string('longitude')->nullable()->after('latitude');
                }
            });
        }

        if (Schema::hasTable('device_logs')) {
            Schema::table('device_logs', function (Blueprint $table) {
                if (!Schema::hasColumn('device_logs', 'latitude')) {
                    $table->string('latitude')->nullable()->after('ip_address');
                }
                if (!Schema::hasColumn('device_logs', 'longitude')) {
                    $table->string('longitude')->nullable()->after('latitude');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('device_bindings')) {
            Schema::table('device_bindings', function (Blueprint $table) {
                $table->dropColumn(['latitude', 'longitude']);
            });
        }

        if (Schema::hasTable('device_logs')) {
            Schema::table('device_logs', function (Blueprint $table) {
                $table->dropColumn(['latitude', 'longitude']);
            });
        }
    }
};
