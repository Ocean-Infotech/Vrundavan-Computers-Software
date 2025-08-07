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
        Schema::create('send_otp', function (Blueprint $table) {
            $table->id();
            $table->string('model');
            $table->string('model_id');
            $table->string('send_to', 25);
            $table->string('verified_content', 10);
            $table->dateTime('expire_time')->nullable();
            $table->text('sms_type')->nullable()->comment("otp");
            $table->text('sms', 10)->nullable();
            $table->dateTime('verified_at')->nullable();
            $table->string('status')->default('send')->comment("send");
            $table->string('created_from')->comment("Where it create");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('send_otp');
    }
};
