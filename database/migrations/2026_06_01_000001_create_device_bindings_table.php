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
        Schema::create('device_bindings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('hardware_id')->index();
            $table->string('pc_name')->nullable();
            $table->string('device_name')->nullable();
            $table->string('bios_serial')->nullable();
            $table->string('motherboard_serial')->nullable();
            $table->string('cpu_id')->nullable();
            $table->string('raw_hardware_string')->nullable();
            $table->string('status')->default('pending')->index(); // pending, approved, rejected, suspended
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('last_login_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'hardware_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('device_bindings');
    }
};
